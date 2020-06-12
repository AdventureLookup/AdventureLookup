<?php

namespace AppBundle\Service;

use League\Uri\Components\Host;
use League\Uri\Components\Query;
use League\Uri\Http;
use League\Uri\Modifiers\Formatter;
use League\Uri\PublicSuffix\CurlHttpClient;
use League\Uri\PublicSuffix\ICANNSectionManager;
use League\Uri\QueryParser;
use Psr\SimpleCache\CacheInterface;

class AffiliateLinkHandler
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

    public function getDomains()
    {
        $domains = [];
        foreach ($this->affiliateMappings as $affiliateMapping) {
            foreach ($affiliateMapping['domains'] as $domain) {
                $domains[] = $domain;
            }
        }

        return array_unique($domains);
    }

    public function addAffiliateCode(?string $url)
    {
        if (null === $url || empty($url)) {
            return [null, false];
        }

        $uri = Http::createFromString($url);

        $affiliateCodeAdded = false;
        if (!empty($this->affiliateMappings)) {
            $rules = (new ICANNSectionManager($this->cache, new CurlHttpClient()))->getRules();
            $domain = (new Host($uri->getHost(), $rules))->getRegistrableDomain();
            $queryParser = new QueryParser();

            foreach ($this->affiliateMappings as $affiliateMapping) {
                foreach ($affiliateMapping['domains'] as $affiliateDomain) {
                    if ($affiliateDomain === $domain) {
                        $queryParameters = $queryParser->extract($uri->getQuery());
                        $queryParameters[$affiliateMapping['param']] = $affiliateMapping['code'];

                        $affiliateCodeAdded = true;
                        $uri = $uri->withQuery(Query::createFromPairs($queryParameters)->getContent());
                        break 2;
                    }
                }
            }
        }

        $formatter = new Formatter();
        $formatter->setEncoding(Formatter::RFC3987_ENCODING);

        return [$formatter($uri), $affiliateCodeAdded];
    }
}
