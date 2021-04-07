<?php
/**
 * SEOmatic plugin for Craft CMS 3.x
 *
 * A turnkey SEO implementation for Craft CMS that is comprehensive, powerful,
 * and flexible
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2019 nystudio107
 */

namespace nystudio107\seomatic\gql\resolvers;

use nystudio107\seomatic\gql\interfaces\SeomaticInterface;
use nystudio107\seomatic\helpers\Container as ContainerHelper;

use Craft;
use craft\base\Element;
use craft\gql\base\Resolver;
use craft\helpers\Json;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class SeomaticResolver
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.2.8
 */
class SeomaticResolver extends Resolver
{

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public static function resolve($source, array $arguments, $context, ResolveInfo $resolveInfo)
    {
        // If our source is an Element, extract the URI and siteId from it
        if ($source instanceof Element) {
            /** Element $source */
            $uri = $source->uri;
            $siteId = $source->siteId;
        } else {
            // Otherwise use the passed in arguments, or defaults
            $uri = $arguments['uri'] ?? '/';
            $siteId = $arguments['siteId'] ?? null;
            if (!empty($arguments['site'])) {
                $siteId = self::getSiteIdFromHandle($arguments['site']) ?? $siteId;
            }
        }
        $asArray = $arguments['asArray'] ?? false;
        $uri = trim($uri === '/' ? '__home__' : $uri, '/');

        $result = ContainerHelper::getContainerArrays(
            array_values(SeomaticInterface::GRAPH_QL_FIELDS),
            $uri,
            $siteId,
            $asArray
        );
        foreach ($result as $key => $value) {
            if (isset($value) && is_array($value)) {
                $result[$key] = Json::encode($value);
            }
        }

        return $result;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Return a siteId from a siteHandle
     *
     * @param string $siteHandle
     *
     * @return int|null
     */
    protected static function getSiteIdFromHandle($siteHandle)
    {
        // Get the site to edit
        if ($siteHandle !== null) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            return $site->id ?? null;
        }

        return Craft::$app->getSites()->currentSite->id;
    }
}
