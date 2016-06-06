<?php

namespace FileBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractFile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=255, nullable=true)
     */
    private $contentType;

    /**
     * @var string
     *
     * @ORM\Column(name="content_length", type="string", length=255, nullable=true)
     */
    private $contentLength;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="status_percentage", type="integer")
     */
    private $statusPercentage = 0;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     *
     * @return File
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set contentLength
     *
     * @param string $contentLength
     *
     * @return File
     */
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;

        return $this;
    }

    /**
     * Get contentLength
     *
     * @return string
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return File
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getStatusPercentage()
    {
        return $this->statusPercentage;
    }

    /**
     * @param int $statusPercentage
     */
    public function setStatusPercentage($statusPercentage)
    {
        $this->statusPercentage = $statusPercentage;
    }
}