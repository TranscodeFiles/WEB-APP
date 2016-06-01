<?php

namespace FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConvertedFile
 *
 * @ORM\Table(name="converted_file")
 * @ORM\Entity(repositoryClass="FileBundle\Repository\FileRepository")
 */
class ConvertedFile extends AbstractFile
{
    /**
     * @ORM\ManyToOne(targetEntity="File", inversedBy="convertedFiles")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id")
     */
    public $file;

    public $format;

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }
}
