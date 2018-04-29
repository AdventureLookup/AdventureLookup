<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use League\Uri\Components\Host;
use League\Uri\Components\Query;
use League\Uri\Modifiers\Formatter;
use League\Uri\Schemes\Http;

class AppExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $affiliateMappings = [];

    public function __construct(array $affiliateMappings)
    {
        $this->affiliateMappings = $affiliateMappings;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('bool2str', [$this, 'bool2str']),
            new \Twig_SimpleFilter('add_affiliate_code', [$this, 'addAffiliateCode'])
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('format_level', [$this, 'formatLevel']),
        ];
    }

    /**
     * @param Adventure|AdventureDocument $adventure
     * @return null|string
     */
    public function formatLevel($adventure)
    {
        if ($adventure->getMinStartingLevel() !== null) {
            if ($adventure->getMinStartingLevel() === $adventure->getMaxStartingLevel() || $adventure->getMaxStartingLevel() === null) {
                return "Level " . $adventure->getMinStartingLevel();
            } else {
                return sprintf("Levels %s–%s", $adventure->getMinStartingLevel(), $adventure->getMaxStartingLevel());
            }
        } else if ($adventure->getStartingLevelRange() !== null) {
            return $adventure->getStartingLevelRange() . " Level";
        }

        return null;
    }

    public function bool2str($boolean)
    {
        if ($boolean === null) {
            return 'Unknown';
        }

        return $boolean ? 'Yes' : 'No';
    }

    /**
     * @param string|null $url
     * @return null
     */
    public function addAffiliateCode(string $url = null)
    {
        if ($url === null) {
            return null;
        }

        $uri = Http::createFromString($url);
        $domain = (new Host($uri->getHost()))->getRegisterableDomain();

        foreach ($this->affiliateMappings as $affiliateMapping) {
            foreach ($affiliateMapping['domains'] as $affiliateDomain) {
                if ($affiliateDomain === $domain) {
                    $queryParameters = Query::extract($uri->getQuery());
                    $queryParameters[$affiliateMapping['param']] = $affiliateMapping['code'];

                    $uri = $uri->withQuery(Query::createFromPairs($queryParameters)->getContent());
                    break 2;
                }
            }
        }

        $formatter = new Formatter();
        $formatter->setEncoding(Formatter::RFC3987_ENCODING);

        return $formatter($uri);
    }
}
