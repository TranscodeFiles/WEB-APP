<?php

namespace Common\CephBundle;

use Common\CephBundle\DependencyInjection\CephExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CephBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CephExtension();
    }
}