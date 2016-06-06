<?php   

namespace FileBundle\Services;

use Buzz\Browser;
use Common\CephBundle\Services\Container;
use Common\CephBundle\Services\Manager;
use Doctrine\ORM\EntityManager;
use FileBundle\Factory\InterfaceFiles;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class Files
 * @package FileBundle\Services
 */
class Files implements InterfaceFiles
{
    /**
     * @var Manager $cephService
     */
    private $cephService;

    /**
     * @var TokenStorage $tokenStorage
     */
    private $tokenStorage;

    /**
     * @var Container $cephContainerSerivce
     */
    private $cephContainerSerivce;

    /**
     * @var Browser $buzz
     */
    private $buzz;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var UserManagerInterface $fosUserManager
     */
    private $fosUserManager;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
         * @var \Twig_Environment $template
     */
    private $template;

    /**
     * @var string $apiCoreHost
     */
    private $apiCoreHost;

    /**
     * Files constructor.
     *
     * @param Manager $managerService
     * @param TokenStorage $tokenStorage
     * @param Browser $buzz
     * @param Router $router
     * @param UserManagerInterface $userManager
     * @param EntityManager $entityManager
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $template
     * @param string $apiCoreHost
     *
     * @throws \ErrorException
     */
    public function __construct(Manager $managerService, TokenStorage $tokenStorage, Browser $buzz, Router $router, UserManagerInterface $userManager, EntityManager $entityManager, \Swift_Mailer $mailer, \Twig_Environment $template, $apiCoreHost)
    {
        $this->cephService          = $managerService;
        $this->tokenStorage         = $tokenStorage;
        if ($this->tokenStorage->getToken()->getUser() != "anon.") {
            $this->cephContainerSerivce = $managerService->connection()
                ->getContainer("user" . $this->tokenStorage->getToken()->getUser()->getId());
        } else {
            $this->cephContainerSerivce = $managerService;
        }
        $this->buzz                 = $buzz;
        $this->router               = $router;
        $this->fosUserManager       = $userManager;
        $this->em                   = $entityManager;
        $this->mailer               = $mailer;
        $this->template             = $template;
        $this->apiCoreHost          = $apiCoreHost;
    }

    /**
     * @inheritdoc
     */
    public function listAction()
    {
        return $this->cephContainerSerivce->listObjects();
    }

    /**
     * @inheritdoc
     */
    public function uploadAction($fileName, $contents)
    {
        return $this->cephContainerSerivce->putObject($fileName, $contents);
    }

    /**
     * @inheritdoc
     */
    public function downloadAction($fileName)
    {
        return $this->cephContainerSerivce->getObject($fileName);
    }

    /**
     * @inheritdoc
     */
    public function transcodeFile($fileName, $fileId, $format)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $url = $this->apiCoreHost . '/transcode/name=' . $fileName . '&type_file=' . $format . '&id_file=' . $fileId .  '&id=user' . $user->getId();
        $this->buzz->get($url);
    }

    /**
     * @inheritdoc
     */
    public function deleteAction($fileName)
    {
        return $this->cephContainerSerivce->deleteObject($fileName);
    }

    /**
     * @inheritdoc
     */
    public function updateStatusAction($userId, $code, $statusPercentage, $convertedFile, $file, $message)
    {
        $authorizedCodes = ["201", "500"];
        if (!in_array($code, $authorizedCodes)) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->fosUserManager->findUserBy(array('id' => $userId));

        if (empty($user) || !$user->isEnabled()) {
            throw new AccessDeniedHttpException("User is not valid");
        }

        if ($code != 500 && empty($file)) {
            throw new  \ErrorException("Converted file must bed specified");
        }

        //========== Update status user ==========\\
        $convertedFile->setStatus($message);
        $convertedFile->setStatusPercentage($statusPercentage);


        if($message == 'Transcoded' && $statusPercentage == 100){
            $message = \Swift_Message::newInstance()
                ->setSubject('Transcoding.com: Your file is ready!')
                ->setFrom(array('transcode.contact@gmail.com' => 'Transcoding.com'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->template->render(
                        ':emails:registration.html.twig',
                        array('file' => $convertedFile)
                    ),
                    'text/html'
                );
            $this->mailer->send($message);
        }

        $this->em->persist($convertedFile);
        $this->em->flush();

        return new Response("Object is update !");
    }
}