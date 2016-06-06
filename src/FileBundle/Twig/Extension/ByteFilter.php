<?php

namespace FileBundle\Twig\Extension;

class ByteFilter extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'byte_filter';
    }

    public function getFilters()
    {

        return array(
            new \Twig_SimpleFilter('bytes', array($this,'formatBytes')),
        );
    }

    public function formatBytes($size, $precision = 2) {

        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

}
