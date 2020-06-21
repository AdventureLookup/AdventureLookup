<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\User;
use AppBundle\Service\AffiliateLinkHandler;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private AffiliateLinkHandler $affiliateLinkHandler;

    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(AffiliateLinkHandler $affiliateLinkHandler, RoleHierarchyInterface $roleHierarchy)
    {
        $this->affiliateLinkHandler = $affiliateLinkHandler;
        $this->roleHierarchy = $roleHierarchy;
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
        $roleMap = [
            'ROLE_USER' => 'User',
            'ROLE_CURATOR' => 'Curator',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_ALLOWED_TO_SWITCH' => 'Impersonator',
        ];
        // 1. Convert string based roles into Role objects
        // 2. Calculate all reachable roles (e.g., ROLE_ADMIN implies ROLE_USER)
        // 3. Translate role objects using the translations defined above.
        $roles = array_map(fn (string $role): Role => new Role($role), $user->getRoles());
        $roles = $this->roleHierarchy->getReachableRoles($roles);
        $roles = array_map(function (Role $role) use ($roleMap) {
            $role = $role->getRole();

            return isset($roleMap[$role]) ? $roleMap[$role] : $role;
        }, $roles);

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
