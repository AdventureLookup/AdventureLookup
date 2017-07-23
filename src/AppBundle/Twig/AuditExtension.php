<?php

namespace AppBundle\Twig;

use DataDog\AuditBundle\Entity\AuditLog;

class AuditExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('audit', [$this, 'audit'],  [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new \Twig_SimpleFunction('audit_value', [$this, 'value'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new \Twig_SimpleFunction('audit_assoc', [$this, 'assoc'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new \Twig_SimpleFunction('audit_blame', [$this, 'blame'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function audit(\Twig_Environment $twig, AuditLog $log)
    {
        return $twig->render("audit/actions/{$log->getAction()}.html.twig", ['log' => $log]);
    }

    public function assoc(\Twig_Environment $twig, $assoc)
    {
        return $twig->render("audit/assoc.html.twig", compact('assoc'));
    }

    public function blame(\Twig_Environment $twig, $blame)
    {
        return $twig->render("audit/blame.html.twig", compact('blame'));
    }

    public function value(\Twig_Environment $twig, $val)
    {
        switch (true) {
            case is_bool($val):
                return $val ? 'true' : 'false';
            case is_array($val) && isset($val['fk']):
                return $this->assoc($twig, $val);
            case is_array($val):
                return json_encode($val);
            case is_string($val):
                return strlen($val) > 60 ? substr($val, 0, 60) . '...' : $val;
            case is_null($val):
                return 'NULL';
            default:
                return $val;
        }
    }
}
