<?php
/**
 * SEOmatic plugin for Craft CMS 3.x
 *
 * A turnkey SEO implementation for Craft CMS that is comprehensive, powerful,
 * and flexible
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\seomatic\models\jsonld;

use nystudio107\seomatic\models\jsonld\Intangible;

/**
 * Demand - A demand entity represents the public, not necessarily binding,
 * not necessarily exclusive, announcement by an organization or person to
 * seek a certain type of goods or services. For describing demand using this
 * type, the very same properties used for Offer apply.
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.0.0
 * @see       http://schema.org/Demand
 */
class Demand extends Intangible
{
    // Static Public Properties
    // =========================================================================

    /**
     * The Schema.org Type Name
     *
     * @var string
     */
    static public $schemaTypeName = 'Demand';

    /**
     * The Schema.org Type Scope
     *
     * @var string
     */
    static public $schemaTypeScope = 'https://schema.org/Demand';

    /**
     * The Schema.org Type Description
     *
     * @var string
     */
    static public $schemaTypeDescription = 'A demand entity represents the public, not necessarily binding, not necessarily exclusive, announcement by an organization or person to seek a certain type of goods or services. For describing demand using this type, the very same properties used for Offer apply.';

    /**
     * The Schema.org Type Extends
     *
     * @var string
     */
    static public $schemaTypeExtends = 'Intangible';

    /**
     * The Schema.org composed Property Names
     *
     * @var array
     */
    static public $schemaPropertyNames = [];

    /**
     * The Schema.org composed Property Expected Types
     *
     * @var array
     */
    static public $schemaPropertyExpectedTypes = [];

    /**
     * The Schema.org composed Property Descriptions
     *
     * @var array
     */
    static public $schemaPropertyDescriptions = [];

    /**
     * The Schema.org composed Google Required Schema for this type
     *
     * @var array
     */
    static public $googleRequiredSchema = [];

    /**
     * The Schema.org composed Google Recommended Schema for this type
     *
     * @var array
     */
    static public $googleRecommendedSchema = [];

    // Public Properties
    // =========================================================================

    /**
     * The payment method(s) accepted by seller for this offer.
     *
     * @var mixed|LoanOrCredit|PaymentMethod [schema.org types: LoanOrCredit, PaymentMethod]
     */
    public $acceptedPaymentMethod;

    /**
     * The amount of time that is required between accepting the offer and the
     * actual usage of the resource or service.
     *
     * @var QuantitativeValue [schema.org types: QuantitativeValue]
     */
    public $advanceBookingRequirement;

    /**
     * The geographic area where a service or offered item is provided. Supersedes
     * serviceArea.
     *
     * @var mixed|AdministrativeArea|GeoShape|Place|string [schema.org types: AdministrativeArea, GeoShape, Place, Text]
     */
    public $areaServed;

    /**
     * The availability of this item???for example In stock, Out of stock,
     * Pre-order, etc.
     *
     * @var ItemAvailability [schema.org types: ItemAvailability]
     */
    public $availability;

    /**
     * The end of the availability of the product or service included in the
     * offer.
     *
     * @var mixed|Date|DateTime|Time [schema.org types: Date, DateTime, Time]
     */
    public $availabilityEnds;

    /**
     * The beginning of the availability of the product or service included in the
     * offer.
     *
     * @var mixed|Date|DateTime|Time [schema.org types: Date, DateTime, Time]
     */
    public $availabilityStarts;

    /**
     * The place(s) from which the offer can be obtained (e.g. store locations).
     *
     * @var Place [schema.org types: Place]
     */
    public $availableAtOrFrom;

    /**
     * The delivery method(s) available for this offer.
     *
     * @var DeliveryMethod [schema.org types: DeliveryMethod]
     */
    public $availableDeliveryMethod;

    /**
     * The business function (e.g. sell, lease, repair, dispose) of the offer or
     * component of a bundle (TypeAndQuantityNode). The default is
     * http://purl.org/goodrelations/v1#Sell.
     *
     * @var BusinessFunction [schema.org types: BusinessFunction]
     */
    public $businessFunction;

    /**
     * The typical delay between the receipt of the order and the goods either
     * leaving the warehouse or being prepared for pickup, in case the delivery
     * method is on site pickup.
     *
     * @var QuantitativeValue [schema.org types: QuantitativeValue]
     */
    public $deliveryLeadTime;

    /**
     * The type(s) of customers for which the given offer is valid.
     *
     * @var BusinessEntityType [schema.org types: BusinessEntityType]
     */
    public $eligibleCustomerType;

    /**
     * The duration for which the given offer is valid.
     *
     * @var QuantitativeValue [schema.org types: QuantitativeValue]
     */
    public $eligibleDuration;

    /**
     * The interval and unit of measurement of ordering quantities for which the
     * offer or price specification is valid. This allows e.g. specifying that a
     * certain freight charge is valid only for a certain quantity.
     *
     * @var QuantitativeValue [schema.org types: QuantitativeValue]
     */
    public $eligibleQuantity;

    /**
     * The ISO 3166-1 (ISO 3166-1 alpha-2) or ISO 3166-2 code, the place, or the
     * GeoShape for the geo-political region(s) for which the offer or delivery
     * charge specification is valid. See also ineligibleRegion.
     *
     * @var mixed|GeoShape|Place|string [schema.org types: GeoShape, Place, Text]
     */
    public $eligibleRegion;

    /**
     * The transaction volume, in a monetary unit, for which the offer or price
     * specification is valid, e.g. for indicating a minimal purchasing volume, to
     * express free shipping above a certain order volume, or to limit the
     * acceptance of credit cards to purchases to a certain minimal amount.
     *
     * @var PriceSpecification [schema.org types: PriceSpecification]
     */
    public $eligibleTransactionVolume;

    /**
     * A Global Trade Item Number (GTIN). GTINs identify trade items, including
     * products and services, using numeric identification codes. The gtin
     * property generalizes the earlier gtin8, gtin12, gtin13, and gtin14
     * properties. The GS1 digital link specifications express GTINs as URLs. A
     * correct gtin value should be a valid GTIN, which means that it should be an
     * all-numeric string of either 8, 12, 13 or 14 digits, or a "GS1 Digital
     * Link" URL based on such a string. The numeric component should also have a
     * valid GS1 check digit and meet the other rules for valid GTINs. See also
     * GS1's GTIN Summary and Wikipedia for more details. Left-padding of the gtin
     * values is not required or encouraged.
     *
     * @var string [schema.org types: Text]
     */
    public $gtin;

    /**
     * The GTIN-12 code of the product, or the product to which the offer refers.
     * The GTIN-12 is the 12-digit GS1 Identification Key composed of a U.P.C.
     * Company Prefix, Item Reference, and Check Digit used to identify trade
     * items. See GS1 GTIN Summary for more details.
     *
     * @var string [schema.org types: Text]
     */
    public $gtin12;

    /**
     * The GTIN-13 code of the product, or the product to which the offer refers.
     * This is equivalent to 13-digit ISBN codes and EAN UCC-13. Former 12-digit
     * UPC codes can be converted into a GTIN-13 code by simply adding a
     * preceeding zero. See GS1 GTIN Summary for more details.
     *
     * @var string [schema.org types: Text]
     */
    public $gtin13;

    /**
     * The GTIN-14 code of the product, or the product to which the offer refers.
     * See GS1 GTIN Summary for more details.
     *
     * @var string [schema.org types: Text]
     */
    public $gtin14;

    /**
     * The GTIN-8 code of the product, or the product to which the offer refers.
     * This code is also known as EAN/UCC-8 or 8-digit EAN. See GS1 GTIN Summary
     * for more details.
     *
     * @var string [schema.org types: Text]
     */
    public $gtin8;

    /**
     * This links to a node or nodes indicating the exact quantity of the products
     * included in the offer.
     *
     * @var TypeAndQuantityNode [schema.org types: TypeAndQuantityNode]
     */
    public $includesObject;

    /**
     * The ISO 3166-1 (ISO 3166-1 alpha-2) or ISO 3166-2 code, the place, or the
     * GeoShape for the geo-political region(s) for which the offer or delivery
     * charge specification is not valid, e.g. a region where the transaction is
     * not allowed. See also eligibleRegion.
     *
     * @var mixed|GeoShape|Place|string [schema.org types: GeoShape, Place, Text]
     */
    public $ineligibleRegion;

    /**
     * The current approximate inventory level for the item or items.
     *
     * @var QuantitativeValue [schema.org types: QuantitativeValue]
     */
    public $inventoryLevel;

    /**
     * A predefined value from OfferItemCondition or a textual description of the
     * condition of the product or service, or the products or services included
     * in the offer.
     *
     * @var OfferItemCondition [schema.org types: OfferItemCondition]
     */
    public $itemCondition;

    /**
     * An item being offered (or demanded). The transactional nature of the offer
     * or demand is documented using businessFunction, e.g. sell, lease etc. While
     * several common expected types are listed explicitly in this definition,
     * others can be used. Using a second type, such as Product or a subtype of
     * Product, can clarify the nature of the offer. Inverse property: offers.
     *
     * @var mixed|AggregateOffer|CreativeWork|Event|MenuItem|Product|Service|Trip [schema.org types: AggregateOffer, CreativeWork, Event, MenuItem, Product, Service, Trip]
     */
    public $itemOffered;

    /**
     * The Manufacturer Part Number (MPN) of the product, or the product to which
     * the offer refers.
     *
     * @var string [schema.org types: Text]
     */
    public $mpn;

    /**
     * One or more detailed price specifications, indicating the unit price and
     * delivery or payment charges.
     *
     * @var PriceSpecification [schema.org types: PriceSpecification]
     */
    public $priceSpecification;

    /**
     * An entity which offers (sells / leases / lends / loans) the services /
     * goods. A seller may also be a provider. Supersedes merchant, vendor.
     *
     * @var mixed|Organization|Person [schema.org types: Organization, Person]
     */
    public $seller;

    /**
     * The serial number or any alphanumeric identifier of a particular product.
     * When attached to an offer, it is a shortcut for the serial number of the
     * product included in the offer.
     *
     * @var string [schema.org types: Text]
     */
    public $serialNumber;

    /**
     * The Stock Keeping Unit (SKU), i.e. a merchant-specific identifier for a
     * product or service, or the product to which the offer refers.
     *
     * @var string [schema.org types: Text]
     */
    public $sku;

    /**
     * The date when the item becomes valid.
     *
     * @var mixed|Date|DateTime [schema.org types: Date, DateTime]
     */
    public $validFrom;

    /**
     * The date after when the item is not valid. For example the end of an offer,
     * salary period, or a period of opening hours.
     *
     * @var mixed|Date|DateTime [schema.org types: Date, DateTime]
     */
    public $validThrough;

    /**
     * The warranty promise(s) included in the offer. Supersedes warrantyPromise.
     *
     * @var WarrantyPromise [schema.org types: WarrantyPromise]
     */
    public $warranty;

    // Static Protected Properties
    // =========================================================================

    /**
     * The Schema.org Property Names
     *
     * @var array
     */
    static protected $_schemaPropertyNames = [
        'acceptedPaymentMethod',
        'advanceBookingRequirement',
        'areaServed',
        'availability',
        'availabilityEnds',
        'availabilityStarts',
        'availableAtOrFrom',
        'availableDeliveryMethod',
        'businessFunction',
        'deliveryLeadTime',
        'eligibleCustomerType',
        'eligibleDuration',
        'eligibleQuantity',
        'eligibleRegion',
        'eligibleTransactionVolume',
        'gtin',
        'gtin12',
        'gtin13',
        'gtin14',
        'gtin8',
        'includesObject',
        'ineligibleRegion',
        'inventoryLevel',
        'itemCondition',
        'itemOffered',
        'mpn',
        'priceSpecification',
        'seller',
        'serialNumber',
        'sku',
        'validFrom',
        'validThrough',
        'warranty'
    ];

    /**
     * The Schema.org Property Expected Types
     *
     * @var array
     */
    static protected $_schemaPropertyExpectedTypes = [
        'acceptedPaymentMethod' => ['LoanOrCredit','PaymentMethod'],
        'advanceBookingRequirement' => ['QuantitativeValue'],
        'areaServed' => ['AdministrativeArea','GeoShape','Place','Text'],
        'availability' => ['ItemAvailability'],
        'availabilityEnds' => ['Date','DateTime','Time'],
        'availabilityStarts' => ['Date','DateTime','Time'],
        'availableAtOrFrom' => ['Place'],
        'availableDeliveryMethod' => ['DeliveryMethod'],
        'businessFunction' => ['BusinessFunction'],
        'deliveryLeadTime' => ['QuantitativeValue'],
        'eligibleCustomerType' => ['BusinessEntityType'],
        'eligibleDuration' => ['QuantitativeValue'],
        'eligibleQuantity' => ['QuantitativeValue'],
        'eligibleRegion' => ['GeoShape','Place','Text'],
        'eligibleTransactionVolume' => ['PriceSpecification'],
        'gtin' => ['Text'],
        'gtin12' => ['Text'],
        'gtin13' => ['Text'],
        'gtin14' => ['Text'],
        'gtin8' => ['Text'],
        'includesObject' => ['TypeAndQuantityNode'],
        'ineligibleRegion' => ['GeoShape','Place','Text'],
        'inventoryLevel' => ['QuantitativeValue'],
        'itemCondition' => ['OfferItemCondition'],
        'itemOffered' => ['AggregateOffer','CreativeWork','Event','MenuItem','Product','Service','Trip'],
        'mpn' => ['Text'],
        'priceSpecification' => ['PriceSpecification'],
        'seller' => ['Organization','Person'],
        'serialNumber' => ['Text'],
        'sku' => ['Text'],
        'validFrom' => ['Date','DateTime'],
        'validThrough' => ['Date','DateTime'],
        'warranty' => ['WarrantyPromise']
    ];

    /**
     * The Schema.org Property Descriptions
     *
     * @var array
     */
    static protected $_schemaPropertyDescriptions = [
        'acceptedPaymentMethod' => 'The payment method(s) accepted by seller for this offer.',
        'advanceBookingRequirement' => 'The amount of time that is required between accepting the offer and the actual usage of the resource or service.',
        'areaServed' => 'The geographic area where a service or offered item is provided. Supersedes serviceArea.',
        'availability' => 'The availability of this item???for example In stock, Out of stock, Pre-order, etc.',
        'availabilityEnds' => 'The end of the availability of the product or service included in the offer.',
        'availabilityStarts' => 'The beginning of the availability of the product or service included in the offer.',
        'availableAtOrFrom' => 'The place(s) from which the offer can be obtained (e.g. store locations).',
        'availableDeliveryMethod' => 'The delivery method(s) available for this offer.',
        'businessFunction' => 'The business function (e.g. sell, lease, repair, dispose) of the offer or component of a bundle (TypeAndQuantityNode). The default is http://purl.org/goodrelations/v1#Sell.',
        'deliveryLeadTime' => 'The typical delay between the receipt of the order and the goods either leaving the warehouse or being prepared for pickup, in case the delivery method is on site pickup.',
        'eligibleCustomerType' => 'The type(s) of customers for which the given offer is valid.',
        'eligibleDuration' => 'The duration for which the given offer is valid.',
        'eligibleQuantity' => 'The interval and unit of measurement of ordering quantities for which the offer or price specification is valid. This allows e.g. specifying that a certain freight charge is valid only for a certain quantity.',
        'eligibleRegion' => 'The ISO 3166-1 (ISO 3166-1 alpha-2) or ISO 3166-2 code, the place, or the GeoShape for the geo-political region(s) for which the offer or delivery charge specification is valid. See also ineligibleRegion.',
        'eligibleTransactionVolume' => 'The transaction volume, in a monetary unit, for which the offer or price specification is valid, e.g. for indicating a minimal purchasing volume, to express free shipping above a certain order volume, or to limit the acceptance of credit cards to purchases to a certain minimal amount.',
        'gtin' => 'A Global Trade Item Number (GTIN). GTINs identify trade items, including products and services, using numeric identification codes. The gtin property generalizes the earlier gtin8, gtin12, gtin13, and gtin14 properties. The GS1 digital link specifications express GTINs as URLs. A correct gtin value should be a valid GTIN, which means that it should be an all-numeric string of either 8, 12, 13 or 14 digits, or a "GS1 Digital Link" URL based on such a string. The numeric component should also have a valid GS1 check digit and meet the other rules for valid GTINs. See also GS1\'s GTIN Summary and Wikipedia for more details. Left-padding of the gtin values is not required or encouraged.',
        'gtin12' => 'The GTIN-12 code of the product, or the product to which the offer refers. The GTIN-12 is the 12-digit GS1 Identification Key composed of a U.P.C. Company Prefix, Item Reference, and Check Digit used to identify trade items. See GS1 GTIN Summary for more details.',
        'gtin13' => 'The GTIN-13 code of the product, or the product to which the offer refers. This is equivalent to 13-digit ISBN codes and EAN UCC-13. Former 12-digit UPC codes can be converted into a GTIN-13 code by simply adding a preceeding zero. See GS1 GTIN Summary for more details.',
        'gtin14' => 'The GTIN-14 code of the product, or the product to which the offer refers. See GS1 GTIN Summary for more details.',
        'gtin8' => 'The GTIN-8 code of the product, or the product to which the offer refers. This code is also known as EAN/UCC-8 or 8-digit EAN. See GS1 GTIN Summary for more details.',
        'includesObject' => 'This links to a node or nodes indicating the exact quantity of the products included in the offer.',
        'ineligibleRegion' => 'The ISO 3166-1 (ISO 3166-1 alpha-2) or ISO 3166-2 code, the place, or the GeoShape for the geo-political region(s) for which the offer or delivery charge specification is not valid, e.g. a region where the transaction is not allowed. See also eligibleRegion.',
        'inventoryLevel' => 'The current approximate inventory level for the item or items.',
        'itemCondition' => 'A predefined value from OfferItemCondition or a textual description of the condition of the product or service, or the products or services included in the offer.',
        'itemOffered' => 'An item being offered (or demanded). The transactional nature of the offer or demand is documented using businessFunction, e.g. sell, lease etc. While several common expected types are listed explicitly in this definition, others can be used. Using a second type, such as Product or a subtype of Product, can clarify the nature of the offer. Inverse property: offers.',
        'mpn' => 'The Manufacturer Part Number (MPN) of the product, or the product to which the offer refers.',
        'priceSpecification' => 'One or more detailed price specifications, indicating the unit price and delivery or payment charges.',
        'seller' => 'An entity which offers (sells / leases / lends / loans) the services / goods. A seller may also be a provider. Supersedes merchant, vendor.',
        'serialNumber' => 'The serial number or any alphanumeric identifier of a particular product. When attached to an offer, it is a shortcut for the serial number of the product included in the offer.',
        'sku' => 'The Stock Keeping Unit (SKU), i.e. a merchant-specific identifier for a product or service, or the product to which the offer refers.',
        'validFrom' => 'The date when the item becomes valid.',
        'validThrough' => 'The date after when the item is not valid. For example the end of an offer, salary period, or a period of opening hours.',
        'warranty' => 'The warranty promise(s) included in the offer. Supersedes warrantyPromise.'
    ];

    /**
     * The Schema.org Google Required Schema for this type
     *
     * @var array
     */
    static protected $_googleRequiredSchema = [
    ];

    /**
     * The Schema.org composed Google Recommended Schema for this type
     *
     * @var array
     */
    static protected $_googleRecommendedSchema = [
    ];

    // Public Methods
    // =========================================================================

    /**
    * @inheritdoc
    */
    public function init()
    {
        parent::init();
        self::$schemaPropertyNames = array_merge(
            parent::$schemaPropertyNames,
            self::$_schemaPropertyNames
        );

        self::$schemaPropertyExpectedTypes = array_merge(
            parent::$schemaPropertyExpectedTypes,
            self::$_schemaPropertyExpectedTypes
        );

        self::$schemaPropertyDescriptions = array_merge(
            parent::$schemaPropertyDescriptions,
            self::$_schemaPropertyDescriptions
        );

        self::$googleRequiredSchema = array_merge(
            parent::$googleRequiredSchema,
            self::$_googleRequiredSchema
        );

        self::$googleRecommendedSchema = array_merge(
            parent::$googleRecommendedSchema,
            self::$_googleRecommendedSchema
        );
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            [['acceptedPaymentMethod','advanceBookingRequirement','areaServed','availability','availabilityEnds','availabilityStarts','availableAtOrFrom','availableDeliveryMethod','businessFunction','deliveryLeadTime','eligibleCustomerType','eligibleDuration','eligibleQuantity','eligibleRegion','eligibleTransactionVolume','gtin','gtin12','gtin13','gtin14','gtin8','includesObject','ineligibleRegion','inventoryLevel','itemCondition','itemOffered','mpn','priceSpecification','seller','serialNumber','sku','validFrom','validThrough','warranty'], 'validateJsonSchema'],
            [self::$_googleRequiredSchema, 'required', 'on' => ['google'], 'message' => 'This property is required by Google.'],
            [self::$_googleRecommendedSchema, 'required', 'on' => ['google'], 'message' => 'This property is recommended by Google.']
        ]);

        return $rules;
    }
}
