<?php   

namespace FileBundle\Services;

use Buzz\Browser;
use Common\CephBundle\Services\Container;
use Common\CephBundle\Services\Manager;
use FileBundle\Factory\InterfaceFiles;
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
     * @param string $apiCoreHost
     *
     * @throws \ErrorException
     */
    public function __construct(Manager $managerService, TokenStorage $tokenStorage, Browser $buzz, Router $router, $apiCoreHost)
    {
        $this->cephService          = $managerService;
        $this->tokenStorage         = $tokenStorage;
        $this->cephContainerSerivce = $managerService->connection()
            ->getContainer("user" . $this->tokenStorage->getToken()->getUser()->getId());
        $this->buzz                 = $buzz;
        $this->router               = $router;
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
}