<?php

namespace AppBundle\Services;

use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Service manage Acl
 * @package AppBundle\Services
 */
class Acl
{
    /**
     * @var MutableAclProvider $aclProvider
     */
    private $aclProvider;

    /**
     * @var TokenStorage $tokenStorage
     */
    private $tokenStorage;

    /**
     * Acl constructor.
     * @param MutableAclProvider $aclProvider
     * @param TokenStorage $tokenStorage
     */
    public function __construct(MutableAclProvider $aclProvider, TokenStorage $tokenStorage)
    {
        $this->aclProvider = $aclProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Add acl object in ceph
     *
     * @param $object
     *
     * @throws \Exception
     */
    public function addObject($object)
    {
        // Get user
        $user = $this->tokenStorage->getToken()->getUser();
        // creating the ACL
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        $acl = $this->aclProvider->createAcl($objectIdentity);
        // retrieving the security identity of the currently logged-in user
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        // grant owner access
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $this->aclProvider->updateAcl($acl);
    }
}