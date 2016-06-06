<?php

namespace FileBundle\Controller;

use FileBundle\Entity\ConvertedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConvertedFileController
 * @package FileBundle\Controller
 */
class ConvertedFileController extends Controller
{
    /**
     * Update states
     *
     * @param Request $request
     * @param ConvertedFile $convertedFile
     *
     * @return Response
     *
     * @throws \ErrorException
     */
    public function updateStateAction(Request $request, ConvertedFile $convertedFile)
    {
        $file             = $request->get('File');
        $code             = $request->get('Code');
        $statusPercentage = $request->get('Percentage');
        $message          = $request->get('Message');
        $userId           = str_replace("user", "", $request->get('UserId'));

        return $this->get('file.files')->updateStatusAction($userId, $code, $statusPercentage, $convertedFile, $file, $message);
    }

    /**
     * Download file in ceph
     *
     * @param ConvertedFile $convertedFile
     *
     * @return \Buzz\Message\MessageInterface
     */
    public function downloadAction(ConvertedFile $convertedFile)
    {
        return $this->get('file.files')->downloadAction($convertedFile->getName());
    }

    /**
     * Retry transcode if he failed
     *
     * @param ConvertedFile $convertedFile
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function retryTranscodeAction(ConvertedFile $convertedFile)
    {
        $fileName = strtolower($convertedFile->getName());
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $this->get('file.files')->transcodeFile($convertedFile->file->getName(), $convertedFile->getId(), $ext);
        return $this->redirectToRoute('files_show', array('id' => $convertedFile->getFile()->getId()));
    }
}
