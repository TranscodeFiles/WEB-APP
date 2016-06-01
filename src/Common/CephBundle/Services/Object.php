<?php

namespace Common\CephBundle\Services;

use Common\CephBundle\Factory\InterfaceObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service manage ceph container objects
 * @package Common\CephBundle\Services
 */
class Object implements InterfaceObject
{
    /**
     * @var Container $containerService
     */
    private $containerService;

    /**
     * @var Manager $managerService
     */
    private $managerService;

    /**
     * @var Request $buzz
     */
    private $requestService;

    /**
     * Object constructor.
     * @param Request $requestService
     */
    public function __construct(Request $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * @param Manager $managerService
     */
    public function setManagerService($managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @param Container $containerService
     */
    public function setContainerService($containerService)
    {
        $this->containerService = $containerService;
    }

    /**
     * @inheritdoc
     */
    public function listObjects()
    {
        try {
            list($code, $responseCeph) = $this->requestService->get(
                $this->containerService->getContainerUrl()
            );
            return explode("\n", $responseCeph->getContent());
        } catch (\Exception $e) {
            throw new \ErrorException("Could not list objects an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getObject($objectName)
    {
        try {
            list($code, $responseCeph) = $this->requestService->get(
                $this->containerService->getContainerUrl() . "/" . $objectName
            );

            $headers = $responseCeph->getHeaders();

            // Generate response
            $response = new Response();

            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', substr($headers[7], 14));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $objectName . '";');
            $response->headers->set('Content-length', substr($headers[1], 16));

            // Send headers before outputting anything
            $response->sendHeaders();

            $response->setContent($responseCeph->getContent());

            return $response;
        } catch (\Exception $e) {
            throw new \ErrorException("Could not get object " . $objectName . " an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function putObject($fileName, $contents)
    {
        try {
            return $this->requestService->put(
                $this->containerService->getContainerUrl() . "/" . $fileName,
                $contents
            );
        } catch (NotFoundHttpException $e) {
            throw new NotFoundHttpException("Could not put object " . $fileName . " an error append : " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \ErrorException("Could not put object " . $fileName . " an error append : " . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteObject($objectName)
    {
        try {
            return $this->requestService->delete(
                $this->containerService->getContainerUrl() . "/" . $objectName
            );
        } catch (\Exception $e) {
            throw new \ErrorException("Could not delete object " . $objectName . " an error append : " . $e->getMessage());
        }
    }
}