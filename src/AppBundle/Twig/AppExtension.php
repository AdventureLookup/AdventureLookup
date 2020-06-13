<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\User;
use AppBundle\Service\AffiliateLinkHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var AffiliateLinkHandler
     */
    private $affiliateLinkHandler;

    public function __construct(AffiliateLinkHandler $affiliateLinkHandler)
    {
        $this->affiliateLinkHandler = $affiliateLinkHandler;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('bool2str', [$this, 'bool2str']),
            new TwigFilter('add_affiliate_code', [$this, 'addAffiliateCode']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('format_level', [$this, 'formatLevel']),
            new TwigFunction('format_roles', [$this, 'formatRoles']),
        ];
    }

    /**
     * @param Adventure|AdventureDocument $adventure
     *
     * @return string|null
     */
    public function formatLevel($adventure)
    {
        if (null !== $adventure->getMinStartingLevel()) {
            if ($adventure->getMinStartingLevel() === $adventure->getMaxStartingLevel() || null === $adventure->getMaxStartingLevel()) {
                return 'Level '.$adventure->getMinStartingLevel();
            } else {
                return sprintf('Levels %s–%s', $adventure->getMinStartingLevel(), $adventure->getMaxStartingLevel());
            }
        } elseif (null !== $adventure->getStartingLevelRange()) {
            return $adventure->getStartingLevelRange().' Level';
        }

        return null;
    }

    public function formatRoles(User $user)
    {
        $roles = array_map(function ($role) {
            $roleMap = [
                'ROLE_USER' => 'User',
                'ROLE_CURATOR' => 'Curator',
                'ROLE_ADMIN' => 'Admin',
            ];

            return isset($roleMap[$role]) ? $roleMap[$role] : $role;
        }, $user->getRoles());

        return implode(', ', $roles);
    }

    public function bool2str($boolean)
    {
        if (null === $boolean) {
            return 'Unknown';
        }

        return $boolean ? 'Yes' : 'No';
    }

    /**
     * @return [string, bool]
     */
    public function addAffiliateCode(string $url = null): array
    {
        return $this->affiliateLinkHandler->addAffiliateCode($url);
    }
}
