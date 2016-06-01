<?php

namespace FileBundle\Factory;

use Buzz\Message\Response;

/**
 * Interface InterfaceFiles
 * @package FileBundle\Factory
 */
interface InterfaceFiles
{
    /**
     * List all files in user container ceph
     *
     * @return array
     */
    public function listAction();

    /**
     * Upload file to ceph
     *
     * @param  string $fileName
     * @param  string $contents
     *
     * @return integer  $code
     * @return Response $responseCeph
     */
    public function uploadAction($fileName, $contents);

    /**
     * Download file in ceph
     *
     * @param   string $fileName
     *
     * @return  \Symfony\Component\HttpFoundation\Response $response
     */
    public function downloadAction($fileName);

    /**
     * Delete file into ceph
     *
     * @param $fileName
     *
     * @return integer  $code
     * @return Response $responseCeph
     */
    public function deleteAction($fileName);
}