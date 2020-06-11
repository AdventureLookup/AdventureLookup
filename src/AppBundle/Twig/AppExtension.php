<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\User;
use League\Uri\Components\Host;
use League\Uri\Components\Query;
use League\Uri\Modifiers\Formatter;
use League\Uri\Http;
use League\Uri\PublicSuffix\CurlHttpClient;
use League\Uri\PublicSuffix\ICANNSectionManager;
use League\Uri\QueryParser;
use Psr\SimpleCache\CacheInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $affiliateMappings = [];

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(array $affiliateMappings, CacheInterface $cache)
    {
        $this->affiliateMappings = $affiliateMappings;
        $this->cache = $cache;
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
     * @return null
     */
    public function addAffiliateCode(string $url = null)
    {
        if (null === $url) {
            return null;
        }

        $uri = Http::createFromString($url);

        if (!empty($this->affiliateMappings)) {
            $queryParser = new QueryParser();
            $rules = (new ICANNSectionManager($this->cache, new CurlHttpClient()))->getRules();

            $domain = (new Host($uri->getHost(), $rules))->getRegistrableDomain();

            foreach ($this->affiliateMappings as $affiliateMapping) {
                foreach ($affiliateMapping['domains'] as $affiliateDomain) {
                    if ($affiliateDomain === $domain) {
                        $queryParameters = $queryParser->extract($uri->getQuery());
                        $queryParameters[$affiliateMapping['param']] = $affiliateMapping['code'];

                        $uri = $uri->withQuery(Query::createFromPairs($queryParameters)->getContent());
                        break 2;
                    }
                }
            }
        }

        $formatter = new Formatter();
        $formatter->setEncoding(Formatter::RFC3987_ENCODING);

        return $formatter($uri);
    }
}
