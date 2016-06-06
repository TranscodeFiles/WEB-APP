<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FileBundle\Entity\File;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    private $facebookAccessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="googleId", type="string", length=255, nullable=true)
     */
    protected $googleId;

    private $googleAccessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterId", type="string", length=255, nullable=true)
     */
    protected $twitterId;

    private $twitterIdAccessToken;

    /**
     * @ORM\OneToMany(targetEntity="FileBundle\Entity\File", mappedBy="user")
     */
    public $files;


    /**
     * @var integer
     * @ORM\Column(name="transcodetime", type="integer")
     */
    public $transcodetime = 0;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->files = new ArrayCollection();
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string 
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookAccessToken
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string 
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Set twitterId
     *
     * @param string $twitterId
     * @return User
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    /**
     * Get twitterId
     *
     * @return string 
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @return mixed
     */
    public function getGoogleAccessToken()
    {
        return $this->googleAccessToken;
    }

    /**
     * @param mixed $googleAccessToken
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->googleAccessToken = $googleAccessToken;
    }

    /**
     * @return mixed
     */
    public function getTwitterIdAccessToken()
    {
        return $this->twitterIdAccessToken;
    }

    /**
     * @param mixed $twitterIdAccessToken
     */
    public function setTwitterIdAccessToken($twitterIdAccessToken)
    {
        $this->twitterIdAccessToken = $twitterIdAccessToken;
    }

    /**
     * Add file
     *
     * @param File $file
     *
     * @return User
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param File $file
     */
    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return int
     */
    public function getTranscodetime()
    {
        return $this->transcodetime;
    }

    /**
     * @param int $transcodetime
     */
    public function setTranscodetime($transcodetime)
    {
        $this->transcodetime = $transcodetime;
    }

    /**
     * @param int $transcodetime
     */
    public function addTranscodetime($transcodetime)
    {
        $this->transcodetime = $this->transcodetime + $transcodetime;
    }

    /**
     * @param int $transcodetime
     */
    public function removeTranscodetime($transcodetime)
    {
        $this->transcodetime = $this->transcodetime - $transcodetime;
    }


    
}
