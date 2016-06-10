<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Beelab\PaypalBundle\Entity\Transaction as BaseTransaction;
/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionRepository")
 */
class Transaction extends BaseTransaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;



    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;

    }   


}

