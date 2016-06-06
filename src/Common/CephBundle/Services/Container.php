<?php

namespace Common\CephBundle\Services;

use Common\CephBundle\Factory\InterfaceContainer;
use Common\CephBundle\Factory\InterfaceObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service manage ceph containers
 * @package Common\CephBundle\Services
 */
class Container implements InterfaceContainer
{
    /**
     * @var Request $requestService
     */
    private $requestService;

    /**
     * @var InterfaceObject $objectService
     */
    private $objectService;

    /**
     * @var Manager $managerService
     */
    private $managerService;

    /**
     * @var string $containerUrl
     */
    private $containerUrl;

    /**
     * Container constructor.
     * @param Request $requestService
     */
    public function __construct(Request $requestService)
    {
        $this->requestService           = $requestService;
    }

    /**
     * @param Manager $managerService
     */
    public function setManagerService(Manager $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @param InterfaceObject $objectService
     */
    public function setObjectService($objectService)
    {
        $this->objectService = $objectService;
    }

    /**
     * @return string
     */
    public function getContainerUrl()
    {
        return $this->containerUrl;
    }

    /**
     * @inheritdoc
     */
    public function listContainers()
    {
        try {
            return $this->requestService->get(
                $this->managerService->getStorageUrl()
            );
        } catch (\Exception $e) {
            throw new \ErrorException("Could list containers an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function putContainer($containerName)
    {
        try {
            return $this->requestService->put(
                $this->managerService->getStorageUrl() . "/" . $containerName,
                null,
                array(
                    "X-Container-Write" => $this->managerService->getCephSwiftSettings()['user'],
                    "X-Container-Read" => $this->managerService->getCephSwiftSettings()['user']
                )
            );
        } catch (NotFoundHttpException $e) {
            throw new NotFoundHttpException("Could not put container " . $containerName . " an error append : " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \ErrorException("Could not put container " . $containerName . " an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteContainer($containerName)
    {
        try {
            return $this->requestService->delete(
                $this->managerService->getStorageUrl() . "/" . $containerName
            );
        } catch (\Exception $e) {
            throw new \ErrorException("Could not delete container " . $containerName . " an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getContainer($containerName)
    {
        $containerUrl = $this->managerService->getStorageUrl() . "/" . $containerName;
        try {
            $this->requestService->get(
                $containerUrl
            );
        } catch (\Exception $e) {
            throw new \ErrorException("Could not get container " . $containerName . " an error append : " . $e->getMessage());
        }
        $this->containerUrl = $containerUrl;
        return $this->objectService;
    }
}