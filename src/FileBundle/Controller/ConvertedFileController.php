<?php

namespace FileBundle\Controller;

use FileBundle\Entity\ConvertedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * Retourne in JSON the state of the file
     *
     * @param ConvertedFile $convertedFile
     *
     * @return JsonResponse
     */
    public function getStateAction(ConvertedFile $convertedFile){

        $percentage = $convertedFile->getStatusPercentage();
        $status = $convertedFile->getStatus();

        $responseJson = new JsonResponse(array(
            "percentage" => $percentage,
            "state" => $status
        ));

        return $responseJson;
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

    /**
     * Delete converted file
     *
     * @param ConvertedFile $convertedFile
     * 
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(ConvertedFile $convertedFile)
    {
        //========== Get parent file for redirection ==========\\
        $parenteId = $convertedFile->getFile()->getId();

        //========== Delete object ceph ==========\\
        $fileService = $this->get('file.files');
        $fileService->deleteAction($convertedFile->getName());

        //========== Delete files in database ==========\\
        $em = $this->getDoctrine()->getManager();
        $em->remove($convertedFile);
        $em->flush();

        return $this->redirectToRoute('files_show', array(
            "id" => $parenteId
        ));
    }

}
