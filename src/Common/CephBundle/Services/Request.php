<?php

namespace Common\CephBundle\Services;

use Buzz\Browser;
use Buzz\Message\MessageInterface;
use Common\CephBundle\Factory\InterfaceRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Service manage request to ceph server
 * @package Common\CephBundle\Services
 */
class Request implements InterfaceRequest
{

    /**
     * @var Browser $buzz
     */
    private $buzz;

    /**
     * @var Manager $managerService
     */
    private $managerService;

    /**
     * Object constructor.
     * @param Browser $buzz
     */
    public function __construct(Browser $buzz)
    {
        $this->buzz = $buzz;
    }

    /**
     * @param Manager $managerService
     */
    public function setManagerService($managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @inheritdoc
     */
    public function put($url, $contents, array $headers = array())
    {
        $responseCeph = $this->generateRequest("put", $url, $headers, $contents);
        $code = $this->getCode($responseCeph->getHeaders());
        $successCodes = array(
            "200" => "OK",
            "201" => "Created",
            "204" => "No Content"
        );
        if (array_key_exists($code, $successCodes)) {
            return array(
                $code,
                $responseCeph
            );
        }
        $failedCodes = array(
            "400" => "Bad Request",
            "409" => "Conflict"
        );
        if (array_key_exists($code, $failedCodes)) {
            throw new NotFoundHttpException($failedCodes[$code], null, $code);
        }
        throw new \ErrorException("An error occurred on put " . $url);
    }

    /**
     * @inheritdoc
     */
    public function delete($url, array $headers = array())
    {
        $responseCeph = $this->generateRequest("delete", $url, $headers);
        $code = $this->getCode($responseCeph->getHeaders());
        $successCodes = array(
            "202" => "Accepted",
            "204" => "No Content"
        );
        if (array_key_exists($code, $successCodes)) {
            return array(
                $code,
                $responseCeph
            );
        }
        throw new \ErrorException("An error occurred on delete " . $url);
    }

    /**
     * @inheritdoc
     */
    public function get($url, array $headers = array())
    {
        $responseCeph = $this->generateRequest("get", $url, $headers);
        $code = $this->getCode($responseCeph->getHeaders());
        $successCodes = array(
            "200" => "OK",
            "202" => "Accepted",
            "204" => "No Content"
        );
        if (array_key_exists($code, $successCodes)) {
            return array(
                $code,
                $responseCeph
            );
        }
        throw new \ErrorException("An error occurred on get " . $url);
    }

    /**
     * @inheritdoc
     */
    public function getCode(array $headers)
    {
        try {
            return substr($headers[0], 9, 3);
        } catch (\Exception $e) {
            throw new \ErrorException("An error append on extract request code : " . $e->getMessage());
        }
    }

    /**
     * Add token in headers
     *
     * @param  array $headers
     *
     * @return array $headers
     */
    private function mergeHeader(array $headers)
    {
        return array_merge(
            $headers,
            array(
                "X-Auth-Token" => $this->managerService->getAuthToken()
            )
        );
    }

    /**
     * Construct and execute request
     *
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param string $contents
     *
     * @return MessageInterface
     *
     * @throws \ErrorException
     */
    private function generateRequest($method, $url, $headers, $contents = null)
    {
        try {
            if ($contents) {
                return $this->buzz->$method(
                    $url,
                    $this->mergeHeader($headers),
                    $contents
                );
            } else {
                return $this->buzz->$method(
                    $url,
                    $this->mergeHeader($headers)
                );
            }
        } catch (\Exception $e) {
            throw new \ErrorException("An error occurred on generate request : " . $e->getMessage());
        }
    }
}