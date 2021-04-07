<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;
use craft\commerce\Plugin as CommercePlugin;

class FilterSaleTypes extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Filter Sale Types');
    }

        /**
     * @return array|\Twig_Filter[]
     */
    public function getFunctions()
    {
        return [
            $this->addFunction('getWOMSales'),
        ];
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            $this->addFilter('memberships'),
            $this->addFilter('promotions'),
            $this->addFilter('showbadge'),
        ];
    }

    /**
     * @param object $purchaseable sales object
     * @return array
     * Returns Sales of type memberships
     * (membership sales are configured in config/commerce.php)
     */
    public function memberships($purchasableSales) {
        return $this->_seperateSales($purchasableSales)['memberships'];
    }

    /**
     * @param object $purchaseable sales object
     * @return array
     * Returns Sales of type promotions
     */
    public function promotions($purchasableSales) {
        return $this->_seperateSales($purchasableSales)['promotions'];
    }

    private function _seperateSales($purchasableSales) {
		$discountTypes = \Craft::$app->getConfig()->getConfigFromFile('commerce')['member-discount-types'];
		$sales = [
            'memberships' => [],
            'promotions' => [],
        ];

		foreach($purchasableSales as $sale) {
			if( in_array($sale->id, $discountTypes) ) {
                $sales['memberships'][] = $sale;
			} else {
				$sales['promotions'][] = $sale;
			}
		}

		return $sales;
    }

    public function showbadge($purchasableSales) {
		foreach($purchasableSales as $sale) {
            if($sale->allGroups) {
                return true;
            }
		}
    }

    /**
     * Check if a purchasable has a Wine of the Month sale attached to it.
     * Note: This isn't checking if the sale is valid for the user type -
     * we just want to see if this bottle's been awarded wine of the month.
     */
    public function getWOMSales() {
        
        $sales = CommercePlugin::getInstance()->sales->getAllSales();
        $WomSaleId = \Craft::$app->getConfig()->getConfigFromFile('commerce')['wine-of-the-month-sale-id'];

        foreach($sales as $sale) {
            if( $sale->id == $WomSaleId ) {
                return $sale->purchasableIds;
            }
        }

        return false;

    }

}
