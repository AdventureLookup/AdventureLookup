<?php

namespace AppBundle\Twig;

use DataDog\AuditBundle\Entity\AuditLog;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Adds new functions to use in the audit Twig templates.
 *
 * https://symfony.com/doc/current/templating/twig_extension.html
 */
class AuditExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('audit', [$this, 'audit'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('audit_value', [$this, 'value'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('audit_assoc', [$this, 'assoc'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
            new TwigFunction('audit_blame', [$this, 'blame'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function audit(Environment $twig, AuditLog $log)
    {
        return $twig->render("audit/actions/{$log->getAction()}.html.twig", ['log' => $log]);
    }

    public function assoc(Environment $twig, $assoc)
    {
        return $twig->render('audit/assoc.html.twig', ['assoc' => $assoc]);
    }

    public function blame(Environment $twig, $blame)
    {
        return $twig->render('audit/blame.html.twig', ['blame' => $blame]);
    }

    public function value(Environment $twig, $val)
    {
        switch (true) {
            case is_bool($val):
                return $val ? 'true' : 'false';
            case is_array($val) && isset($val['fk']):
                return $this->assoc($twig, $val);
            case is_array($val):
                return json_encode($val);
            case is_string($val):
                return strlen($val) > 60 ? substr($val, 0, 60).'...' : $val;
            case is_null($val):
                return 'NULL';
            default:
                return $val;
        }
    }
}
