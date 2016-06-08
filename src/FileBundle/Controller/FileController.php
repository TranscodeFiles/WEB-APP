<?php

namespace FileBundle\Controller;

use FileBundle\Entity\ConvertedFile;
use FileBundle\Form\ConvertedFileType;
use FileBundle\Services\Files;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FileBundle\Entity\File;
use FileBundle\Form\FileType;

/**
 * File controller.
 *
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

            //========== Format filename to be accepted by ceph ==========\\
            $fileName = preg_replace('/[ \t]+/', '.', preg_replace('/\s*$^\s*/m', "\n", $attachment->getClientOriginalName()));
            $this->get('file.files')->uploadAction($fileName, file_get_contents($attachment->getRealPath()));

            //========== Configuration file entity ==========\\
            $file->setName($fileName);
            $file->setUser($user);
            $file->setContentLength($attachment->getClientSize());
            $file->setContentType($attachment->getClientMimeType());
            $file->setStatus("Uploaded");

            //========== Save file in database ==========\\
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();

            //========== Set user acl to object file ==========\\
            $this->get('app.acl')->addObject($file);

            return $this->redirectToRoute('files_show', array('id' => $file->getId()));
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(File $file)
    {
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
        $convertedFile = new ConvertedFile();

        //========== Get extension ==========\\
        $name = $file->getName();
        $extension = pathinfo($name)['extension'];

        $form = $this->createForm(new ConvertedFileType($extension), $convertedFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //========== Get format uploaded by form ==========\\
            $format = strtolower($form->get('format')->getData());

            //========== Create converted file name ==========\\
            $convertedFileName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getName()) . '.' . $format;

            //========== Set attributes to converted file ==========\\
            $convertedFile->setName($convertedFileName);
            $convertedFile->setStatus("In progress");
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
