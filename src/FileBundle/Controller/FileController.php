<?php

namespace FileBundle\Controller;

use FileBundle\Entity\ConvertedFile;
use FileBundle\Services\Files;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FileBundle\Entity\File;
use FileBundle\Form\FileType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class FileController
 * @package FileBundle\Controller
 */
class FileController extends Controller
{
    /**
     * Lists all File entities.
     *
     */
    public function indexAction()
    {
        $tokenStorage = $this->get('security.token_storage');
        $user = $tokenStorage->getToken()->getUser();

        return $this->render('file/index.html.twig', array(
            'files' => $user->getFiles(),
        ));
    }

    /**
     * Creates a new File entity.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $file = new File();
        $form = $this->createForm('FileBundle\Form\FileType', $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //========== Get user ==========\\
            $tokenStorage = $this->get('security.token_storage');
            $user = $tokenStorage->getToken()->getUser();

            //========== Upload file to ceph ==========\\
            $attachment = $file->getAttachment();

            //=== Set duration for entity ===== \\
            $getid3 = new \getID3();
            $fileInfo = $getid3->analyze($attachment);
            $duration = $fileInfo["playtime_seconds"];

            //========== Format filename to be accepted by ceph ==========\\
            $fileName = preg_replace('/[ \t]+/', '.', preg_replace('/\s*$^\s*/m', "\n", $attachment->getClientOriginalName()));
            $this->get('file.files')->uploadAction($fileName, file_get_contents($attachment->getRealPath()));

            //========== Configuration file entity ==========\\
            $file->setName($fileName);
            $file->setUser($user);
            $file->setContentLength($attachment->getClientSize());
            $file->setContentType($attachment->getClientMimeType());
            $file->setStatus("Uploaded");
            $file->setDuration($duration);

            //========== Save file in database ==========\\
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();

            //========== Set user acl to object file ==========\\
            $this->get('app.acl')->addObject($file);

            return new JsonResponse(
                array(
                    "url" => $this->generateUrl("files_show", array('id' => $file->getId()))
                )
            );

        }

        return $this->render('file/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a File entity.
     *
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(File $file)
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted('EDIT', $file)) {
            throw new AccessDeniedException();
        }

        $deleteForm = $this->createDeleteForm($file);

        return $this->render('file/show.html.twig', array(
            'file' => $file,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a File entity.
     *
     * @param  Request $request
     * @param  File    $file
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, File $file)
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted('DELETE', $file)) {
            throw new AccessDeniedException();
        }

        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //========== Delete object ceph ==========\\
            $fileService = $this->get('file.files');
            $fileService->deleteAction($file->getName());

            //========== Delete files in database ==========\\
            $em = $this->getDoctrine()->getManager();
            foreach ($file->getConvertedFiles() as $convertedFile) {
                $fileService->deleteAction($convertedFile->getName());
                $em->remove($convertedFile);
            }

            $em->remove($file);
            $em->flush();
        }

        return $this->redirectToRoute('files_index');
    }

    /**
     * Download file to ceph
     *
     * @param File $file
     * @return \Buzz\Message\MessageInterface|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(File $file)
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted('EDIT', $file)) {
            throw new AccessDeniedException();
        }

        return $this->get('file.files')->downloadAction($file->getName());
    }

    /**
     * Transcode file
     *
     * @param Request $request
     * @param File $file
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Responses
     */
    public function transcodeAction(Request $request, File $file)
    {
        /**
         * var User $user
         */
        $user = $this->getUser();
        if($user->gettranscodeTime() < $file->getDuration()){
            $this->addFlash("warning", "Vous n'avez pas assez de temps de transcode");
            return $this->redirectToRoute("app_paypal_paiement");
        }

        $convertedFile = new ConvertedFile();
        $form = $this->createForm('FileBundle\Form\ConvertedFileType', $convertedFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //========== Get format uploaded by form ==========\\
            $format = strtolower($form->get('format')->getData());

            //========== Create converted file name ==========\\
            $convertedFileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getName()) . '.' . $format;

            //========== Set attributes to converted file ==========\\
            $convertedFile->setName($convertedFileName);
            $convertedFile->setStatus("In progress");
            $convertedFile->setStatusPercentage(10);
            $convertedFile->setFile($file);

            //========== Save file in database ==========\\
            $em = $this->getDoctrine()->getManager();
            $em->persist($convertedFile);
            $em->flush();

            //========== Set user acl to object file ==========\\
            $this->get('app.acl')->addObject($convertedFile);

            //========== Transcode file ==========\\
            $this->get('file.files')->transcodeFile($file->getName(), $convertedFile->getId(), $format);
            
            return $this->redirectToRoute('files_show', array('id' => $file->getId()));
        }

        return $this->render('file/transcode.html.twig', array(
            'convertedFile' => $convertedFile,
            'file' => $file,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to delete a File entity.
     *
     * @param File $file The File entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(File $file)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('files_delete', array('id' => $file->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
