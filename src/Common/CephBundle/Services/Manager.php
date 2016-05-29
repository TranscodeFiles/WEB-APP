<?php

namespace Common\CephBundle\Services;

use Common\CephBundle\Factory\InterfaceManager;

/**
 * Class Manager
 * @package Common\CephBundle\Services
 */
class Manager implements InterfaceManager
{
    /**
     * @var array $cephSwiftSettings
     */
    private $cephSwiftSettings;

    /**
     * @var Container $containerService
     */
    private $containerService;

    /**
     * @var boolean $conn
     */
    private $conn;

    /**
     * @var string $authToken
     */
    private $authToken;

    /**
     * @var string $storageUrl
     */
    private $storageUrl;

    /**
     * @var string $storageToken
     */
    private $storageToken;

    /**
     * @var Request $requestService
     */
    private $requestService;

    /**
     * @var integer ACCESS_AUTH
     */
    const ACCESS_AUTH    = 204;

    /**
     * @var  integer ACCESS_SUCCESS
     */
    const ACCESS_SUCCESS = 200;

    /**
     * Ceph constructor.
     * @param array $cephSwiftSettings
     */
    public function __construct(array $cephSwiftSettings)
    {
        $this->cephSwiftSettings = $cephSwiftSettings;
    }

    /**
     * @param Container $containerService
     */
    public function setContainerService(Container $containerService)
    {
        $this->containerService = $containerService;
    }

    /**
     * @return Container
     */
    public function getContainerService()
    {
        return $this->containerService;
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @return string
     */
    public function getStorageUrl()
    {
        return $this->storageUrl;
    }

    /**
     * @return string
     */
    public function getStorageToken()
    {
        return $this->storageToken;
    }

    /**
     * @return array
     */
    public function getCephSwiftSettings()
    {
        return $this->cephSwiftSettings;
    }

    /**
     * @param Request $requestService
     */
    public function setRequestService($requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * @inheritdoc
     */
    public function connection($force = false)
    {
        //========== If is already connected return container service
        //           else log user ceph ==========\\
        if ($this->conn && !$force) {
            return $this->containerService;
        }
        //========== Make request for log user ceph ==========\\
        try {
            list($code, $responseCeph) = $this->requestService->get(
                $this->cephSwiftSettings['authurl'],
                array(
                    "X-Auth-User" => $this->cephSwiftSettings['user'],
                    "X-Auth-Key" => $this->cephSwiftSettings['key']
                )
            );
        } catch (\Exception $e) {
            throw new \ErrorException("Could not connect to ceph server");
        }
        //========== If user is connected set token and connection
        //           else set connection to false ==========\\
        if ($code == self::ACCESS_AUTH) {
            $this->authToken    = substr($responseCeph->getHeaders()[3], 14);
            $this->storageToken = substr($responseCeph->getHeaders()[2], 17);
            $this->storageUrl   = substr($responseCeph->getHeaders()[1], 15);
            $this->conn         = true;
            return $this->containerService;
        }
        $this->conn = false;
        throw new \ErrorException("An error append on connection to ceph server");
    }
}