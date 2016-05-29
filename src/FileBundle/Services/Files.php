<?php   

namespace FileBundle\Services;

use Common\CephBundle\Services\Manager;

/**
 * Class Files
 * @package FileBundle\Services
 */
class Files
{
    /**
     * @var Manager $ceph
     */
    private $ceph;

    /**
     * Files constructor.
     * @param Manager $ceph
     */
    public function __construct(Manager $ceph)
    {
        $this->ceph = $ceph;
    }
}