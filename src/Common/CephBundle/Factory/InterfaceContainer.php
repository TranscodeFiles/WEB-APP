<?php

namespace Common\CephBundle\Factory;

/**
 * Interface IntergaceContainer
 * @package Common\CephBundle\Factory
 */
interface InterfaceContainer
{
    /**
     * Return all containers
     * 
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function listContainers();

    /**
     * Return container by name
     *
     * @param $containerName
     *
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function getContainer($containerName);

    /**
     * Add container
     *
     * @param $containerName
     *
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function putContainer($containerName);

    /**
     * Remove container
     *
     * @param $containerName
     * 
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function deleteContainer($containerName);
}