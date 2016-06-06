<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as FOSUserInterface;

/**
 * Loading and ad-hoc creation of a user by an OAuth sign-in provider account.
 *
 */
class UserProvider extends BaseFOSUBProvider
{
    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(UserManagerInterface $userManager, array $properties, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($userManager, $properties);
        $this->eventDispatcher = $dispatcher;
    }


    /**
     * {@inheritDoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {

            $userData = $response->getEmail();

            $userData = ($userData) ? $userData : $response->getNickname();

            $user = $this->userManager->findUserByUsernameOrEmail($userData);

            if (null === $user || null === $userData) {
                throw new AccountNotLinkedException(sprintf("User '%s' not found.", $userData));
            }
            return $user;
        } catch (UsernameNotFoundException $e) {
            if (null === $user = $this->userManager->findUserByEmail($response->getEmail())) {
                return $this->createUserByOAuthUserResponse($response);
            }

            return $this->updateUserByOAuthUserResponse($user, $response);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function connect(FOSUserInterface $user, UserResponseInterface $response)
    {
        $providerName = $response->getResourceOwner()->getName();
        $uniqueId = $response->getUsername();
        $user->addOAuthAccount($providerName, $uniqueId);

        $this->userManager->updateUser($user);
    }

    /**
     * Ad-hoc creation of user
     *
     * @param UserResponseInterface $response
     *
     * @return User
     */
    protected function createUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $user = $this->userManager->createUser();
        $this->updateUserByOAuthUserResponse($user, $response);

        // set default values taken from OAuth sign-in provider account
        if (null !== $email = $response->getEmail()) {
            $user->setEmail($email);

            $user->setEnabled(true);

            $event = new UserEvent($user, new Request());
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

        } else {
            $user->setEnabled(false);
        }

        if (null === $this->userManager->findUserByUsername($response->getNickname())) {
            $user->setUsername($response->getNickname());
        }

        return $user;
    }

    /**
     * Attach OAuth sign-in provider account to existing user
     *
     * @param FOSUserInterface      $user
     * @param UserResponseInterface $response
     *
     * @return FOSUserInterface
     */
    protected function updateUserByOAuthUserResponse(FOSUserInterface $user, UserResponseInterface $response)
    {
        $providerName = $response->getResourceOwner()->getName();
        $providerNameSetter = 'set'.ucfirst($providerName).'Id';
        $user->$providerNameSetter($response->getUsername());

        if(!$user->getPassword()) {
            // generate unique token
            $secret = md5(uniqid(rand(), true));
            $user->setPassword($secret);
        }

        return $user;
    }
}