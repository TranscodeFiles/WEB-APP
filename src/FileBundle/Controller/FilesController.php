<?php

namespace FileBundle\Controller;

use FileBundle\Services\Files;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class FilesController
 * @package FileBundle\Controller
 */
class FilesController extends Controller
{
    /**
     * @var Files $fileService
     */
    private $fileService;

    /**
     * FilesController constructor.
     * @param Files $fileService
     */
    public function __construct(Files $fileService)
    {
        $this->fileService = $fileService;
    }

    public function listAction()
    {

    }

    public function uploadAction()
    {
        
    }

    public function downloadAction($fileName)
    {
    }

    public function stateAction($fileName)
    {

    }

    public function deleteAction()
    {

    }
}
