<?php

namespace Common\CephBundle\Factory;

use Buzz\Message\MessageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Interface InterfaceRequest
 * @package Common\CephBundle\Factory
 */
interface InterfaceRequest
{
    /**
     * Put request for ceph
     *
     * @param  string $url
     * @param  string $contents
     *
     * @return MessageInterface
     *
     * @throws NotFoundHttpException
     * @throws \ErrorException
     */
    public function put($url, $contents, array $headers = array());

    /**
     * Delete request for ceph
     *
     * @param  string $url
     * @param  array $headers
     *
     * @return MessageInterface
     *
     * @throws \ErrorException
     */
    public function delete($url, array $headers = array());

    /**
     * Send request for ceph
     *
     * @param  string $url
     * @param  array $headers
     *
     * @return MessageInterface
     *
     * @throws \ErrorException
     */
    public function get($url, array $headers = array());

    /**
     * Get status code request
     *
     * @param  array $headers
     *
     * @return bool|string return false if the header is not valid
     *
     * @throws \ErrorException
     */
    public function getCode(array $headers);

    /**
     * Send request head ti ceph
     * 
     * @param string $url
     * 
     * @param array $headers
     * 
     * @return mixed
     */
    public function head($url, $headers = array());

}