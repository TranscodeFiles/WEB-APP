<?php

namespace Common\CephBundle\Factory;

use Common\CephBundle\Services\Container;

/**
 * Interface InterfaceManager
 * @package Common\CephBundle\Factory
 */
interface InterfaceManager
{
    /**
     * Make connection with ceph swift client
     *
     * @param bool $force Force connection if already connected
     *
     * @return Container|mixed
     * 
     * @throws \ErrorException
     */
    public function connection($force = false);
}