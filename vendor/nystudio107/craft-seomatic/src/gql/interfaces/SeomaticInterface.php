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

namespace nystudio107\seomatic\gql\interfaces;

use nystudio107\seomatic\gql\types\generators\SeomaticGenerator;

use nystudio107\seomatic\models\MetaJsonLdContainer;
use nystudio107\seomatic\models\MetaLinkContainer;
use nystudio107\seomatic\models\MetaScriptContainer;
use nystudio107\seomatic\models\MetaSiteVars;
use nystudio107\seomatic\models\MetaTagContainer;
use nystudio107\seomatic\models\MetaTitleContainer;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

/**
 * Class SeomaticInterface
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.2.8
 */
class SeomaticInterface extends BaseInterfaceType
{
    // Constants
    // =========================================================================

    const GRAPH_QL_FIELDS = [
        'metaTitleContainer' => MetaTitleContainer::CONTAINER_TYPE,
        'metaTagContainer' => MetaTagContainer::CONTAINER_TYPE,
        'metaLinkContainer' => MetaLinkContainer::CONTAINER_TYPE,
        'metaScriptContainer' => MetaScriptContainer::CONTAINER_TYPE,
        'metaJsonLdContainer' => MetaJsonLdContainer::CONTAINER_TYPE,
        'metaSiteVarsContainer' => MetaSiteVars::CONTAINER_TYPE,
    ];

    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return SeomaticGenerator::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::class, new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class.'::getFieldDefinitions',
            'description' => 'This is the interface implemented by SEOmatic.',
            'resolveType' => function (array $value) {
                return GqlEntityRegistry::getEntity(SeomaticGenerator::getName());
            },
        ]));
        SeomaticGenerator::generateTypes();

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'SeomaticInterface';
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        $fields = [];
        foreach (self::GRAPH_QL_FIELDS as $key => $value) {
            $fields[$key] = [
                'name' => $key,
                'type' => Type::string(),
                'description' => 'The '.$value.' SEOmatic container.',
            ];
        }

        return $fields;
    }
}
