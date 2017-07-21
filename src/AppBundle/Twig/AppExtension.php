<?php

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('bool2str', array($this, 'bool2str')),
        );
    }

    public function bool2str($boolean)
    {
        if ($boolean === null) {
            return 'Unknown';
        }

        return $boolean ? 'Yes' : 'No';
    }
}
