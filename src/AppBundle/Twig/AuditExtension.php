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
                return '<em>'.($val ? 'true' : 'false').'</em>';
            case is_array($val) && isset($val['fk']):
                return $this->assoc($twig, $val);
            case is_array($val):
                return twig_escape_filter($twig, json_encode($val), 'html');
            case is_string($val):
                $val = mb_strlen($val) > 200 ? mb_substr($val, 0, 200).'...' : $val;

                return twig_escape_filter($twig, $val, 'html');
            case is_null($val):
                return '<em>NULL</em>';
            default:
                return twig_escape_filter($twig, $val, 'html');
        }
    }
}
