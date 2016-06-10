<?php

namespace FileBundle\Twig\Extension;

class SecondToDateFilter extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'secondtodate_filter';
    }

    public function getFilters()
    {

        return array(
            new \Twig_SimpleFilter('secondtodate', array($this,'formatSecondToDate')),
        );
    }

    public function formatSecondToDate($seconds) {

        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");

        return $dtF->diff($dtT)->format('%a jours, %h heures, %i minutes et %s secondes');
    }

}
