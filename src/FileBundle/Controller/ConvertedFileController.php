<?php

namespace FileBundle\Controller;

use FileBundle\Entity\ConvertedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $file    = $request->get('File');
        $code    = $request->get('Code');
        $userId  = str_replace("user", "", $request->get('UserId'));

        switch ($code) {
            case 201:
                $status = "OK";
                break;
            case 500:
                $status = $request->get('Message');
                break;
            default:
                throw new AccessDeniedHttpException();
                break;
        }

        $fosUserManager = $this->get('fos_user.user_manager');
        $user = $fosUserManager->findUserBy(array('id' => $userId));

        if (empty($user) || !$user->isEnabled()) {
            throw new AccessDeniedHttpException("User is not valid");
        }

        if ($code != 500 && empty($file)) {
            throw new  \ErrorException("Converted file must bed specified");
        }

        $em = $this->getDoctrine()->getManager();
        $convertedFile->setStatus($status);
        $em->persist($convertedFile);
        $em->flush();

        return new Response("Object is update !");
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
