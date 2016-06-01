<?php

namespace FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File
 *
 * @ORM\Table(name="file")
 * @ORM\Entity(repositoryClass="FileBundle\Repository\FileRepository")
 */
class File extends AbstractFile
{
    /**
     * @ORM\OneToMany(targetEntity="ConvertedFile", mappedBy="file")
     */
    public $convertedFiles;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    public $user;

    /**
     * @var UploadedFile $attachment
     */
    public $attachment;

    /**
     * File constructor.
     */
    public function __construct()
    {
        $this->convertedFiles = new ArrayCollection();
    }

    /**
     * @return UploadedFile
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param UploadedFile $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Add convertedFile
     *
     * @param ConvertedFile $convertedFile
     *
     * @return File
     */
    public function addConvertedFile(ConvertedFile $convertedFile)
    {
        $this->convertedFiles[] = $convertedFile;

        return $this;
    }

    /**
     * Remove convertedFile
     *
     * @param ConvertedFile $convertedFile
     */
    public function removeConvertedFile(ConvertedFile $convertedFile)
    {
        $this->convertedFiles->removeElement($convertedFile);
    }

    /**
     * Get convertedFiles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConvertedFiles()
    {
        return $this->convertedFiles;
    }
}
