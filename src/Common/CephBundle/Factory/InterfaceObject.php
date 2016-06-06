<?php

namespace Common\CephBundle\Factory;

use Buzz\Message\MessageInterface;

/**
 * Interface InterfaceObject
 * @package Common\CephBundle\Factory
 */
interface InterfaceObject
{
    /**
     * List all objects in containers
     *
     * @return array
     *
     * @throws \ErrorException
     */
    public function listObjects();

    /**
     * Get object in container
     *
     * @param $objectName
     *
     * @return MessageInterface $response
     *
     * @throws \ErrorException
     */
    public function getObject($objectName);

    /**
     * Put object in container
     *
     * @param  string $fileName
     * @param  object $contents
     *
     * @return bool
     *
     * @throws \ErrorException
     */
    public function putObject($fileName, $contents);

    /**
     * Delete object in container
     *
     * @param $objectName
     *
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function deleteObject($objectName);

    /**
     * Return metadata object in container
     * 
     * @param  string $objectName
     * 
     * @return mixed
     * 
     * @throws \ErrorException
     */
    public function getMetaDataObject($objectName);
}