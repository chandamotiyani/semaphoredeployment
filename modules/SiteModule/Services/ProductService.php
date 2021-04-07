<?php

namespace modules\SiteModule\Services;

use Craft;
use craft\web\Request;
use modules\Memberships\MembershipModule;
use yii\base\Component;
use craft\commerce\services\LineItems;

class ProductService extends Component {

	public function filterBy($productType) {

      $filterParam = [];
		$element =  (Craft::$app->getUrlManager()->getMatchedElement());
        $filterQuery = \craft\commerce\elements\Product::find()->withPermission()->type($productType);

		if( $categoryQuery = $this->filterByCategory($this->categoryFilters()) ) {
			$filterQuery->relatedTo($categoryQuery);
		}

		if( $customFieldQuery = $this->filterByCustomField($this->customFieldFilters()) ) {
			$filterQuery->andWhere($customFieldQuery);
		}

		if( $sortBy = $this->sortBy($this->sortFilters()) ) {

			$filterQuery->orderBy($sortBy);

			if($sortBy == 'DESC') {
				$filterQuery->orderBy(['defaultPrice' => SORT_DESC, 'postDate' => SORT_DESC]);
			} else {
				$filterQuery->orderBy(['defaultPrice' => SORT_ASC, 'postDate' => SORT_DESC]);
			}
		} else {
			$filterQuery->orderBy(['defaultPrice' => SORT_DESC, 'postDate' => SORT_DESC]);
		}

		return $filterQuery;
	}

	public function all($productType) {

			$filterParam = [];
			$element =  (Craft::$app->getUrlManager()->getMatchedElement());

			$filterQuery = \craft\commerce\elements\Product::find()->withPermission()->type($productType)->orderBy(['defaultPrice' => SORT_DESC, 'postDate' => SORT_DESC]);
			return $filterQuery;
	}

	/**
	 * Filter By Categories
	 *
	 * @param Array $categoryFilters: The category names we're allowed to search
	 * @return Array A valid Query we can pass to the Element Critiria Model
	 */
	private function filterByCategory($categoryFilters) {

		$groupFilter = [];


		// Check if it's a category listing
		if( $category = $this->category() ) {
			$params = $category;
		} else {
			// Check if we have any URL params in the $categoryFilters list.
			$params = $this->getParam($categoryFilters);
		}

		foreach($params as $categoryName => $categoryValues) {

			// Set product query with params
			$elementQuery = \craft\elements\Category::find()
				->group($categoryName)
				->id($categoryValues);

			$groupFilter[] = ['targetElement' => $elementQuery];
		}

		// Search each group (a group is a category) using AND logic
		if($groupFilter) {
			array_unshift($groupFilter, 'and');

			return $groupFilter;
		}

		return [];
	}


	public function getGiftOptions($productId, $lineItemId) {

		$purchasableId = Craft::$app->request->getQueryParam('purchasableId');

		$variant = \craft\commerce\elements\Variant::find()
    ->id($purchasableId)
		->one();

		$lineItem = craft\commerce\Plugin::getInstance()->getLineItems()->getLineItemById($lineItemId);

    $giftOptionItems = [];
    foreach($variant->giftOptions as $giftOption) {
     $giftOptionItems[] = [
      'id' => $giftOption->id,
      'title' => $giftOption->title,
      'imageUrl' => $giftOption->image->one() ? $giftOption->image->one()->getUrl() : '',
      'description' => $giftOption->shortDescription ?? '',
      'price' => $giftOption->defaultVariant->price > 0 ? \Craft::$app->getFormatter()->asCurrency($giftOption->defaultVariant->price, 'AUD', [], [], true) : 'FREE',
      'productId' => $giftOption->defaultVariant->id,
      'messageField' => $giftOption->messageField->contains('hasMessageField') ?? '',
     ];
  }

   $productData = [
    'id' => $variant->id,
    'purchasableId' => $variant->id,
    'title' => $variant->getProduct()->title,
    'price' => $variant->getSalePrice(),
    'disabled' => $this->isProductDisabled($variant->getProduct(), $variant),
		'giftOptions' => $giftOptionItems,
		'lineItem' => $lineItem ?? ['options' => []],
   ];

   return $productData;
	}

	public function isProductDisabled($product, $purchasable) {
		return ($purchasable->getIsAvailable() == false) || 
		$product->premiseOnly->contains('true') || 
		($purchasable->stock <= 0 && $purchasable->hasUnlimitedStock == false);
	}

	/**
	 * Filter listing using a Custom field name / values
	 * Builds a 'Search WHERE' query by looping through custom fields.
	 * Searches on 'content' table.
	 *
	 * @param Array $customFieldFilters: The custom fields we're allowed to search
	 * @return Array A valid Query we can pass to the Element Critiria Model
	 */
	private function filterByCustomField($customFieldFilters) {

		// Check if any params for searchable custom fields exist in the URL
		$params = $this->getParam($customFieldFilters);
		$fields = [];

		foreach($params as $fieldName => $fieldValue) {
            $fieldValues = [];

            foreach($fieldValue as $value) {
                $fields[] = "content.field_{$fieldName} LIKE '%{$value}%'";
            }
        }

        if($fields) {
            return implode(" OR ", $fields);
        }

        return false;
	}

	/**
	 * Sorts a Listing
	 *
	 * @param String $sort - Sort value comes from URL and must look like this: 'defaultPrice|DESC' (sortUsing | PIPE Seperator | sortValue)
	 * @return Array A valid Query we can pass to the Element Critiria Model
	 */
	private function sortBy($sort) {

		$param = $this->getParam(['sort']);

		if( empty($param['sort']) )  {
			return 'DESC';
		}

		if(! in_array($param['sort'][0], $sort) ) { // sanitise input (check query param value matches values in our list)
			return;
		}

		$sort = explode('|', $param['sort'][0]);

		if( isset($sort[1]) ) {
			return $sort[1];
		}
	}

	/**
	 * Get URL parameters matching $filters Array
	 * Parameter values in URL are comma seperated.
	 *
	 * @param Array $filters list
	 * @return Array key: the filter name. value: the filter values as Array (can be multipule)
	 */
	private function getParam($filters) {

		$filtersArray = [];

		foreach($filters as $filter) {

			$filterValue = Craft::$app->request->getQueryParam($filter);

			if( empty($filterValue) ) {
				continue;
			}

			$filterValues = explode(',', $filterValue);

			$filtersArray[$filter] = $filterValues;
		}

		return $filtersArray;
	}

	public function category() {

		$element =  (Craft::$app->getUrlManager()->getMatchedElement());
		// check element is a category
		if(! is_a($element, 'craft\elements\Category') ) {
			return [];
		}
		$categoryName = \Craft::$app->request->getSegment(2);

		if(! $categoryName ) {
			return [];
		}

		return [
			$categoryName => $element->id,
		];
	}

	public function categoryFilters() {
		return [
			'Style' => 'wineType',
			'Occasion' => 'occasion',
			'Region' => 'regions',
			'Collection' => 'collections',
			'Food Pairing' => 'foodPairing',
		];
	}

	public function customFieldFilters() {
		return [
			'Special Release' => 'specialReleases',
		];
	}

	public function sortFilters() {
		return [
			'Price Highest' => 'defaultPrice|DESC',
			'Price Lowest' => 'defaultPrice|ASC',
		];
	}

		public function getMembersOnlyProducts($productType) {
			$user = Craft::$app->getUser();

			//check if user is in group
			if(!empty($user) && $user->getIdentity()->getGroups()) {
				$group = $user->getIdentity()->getGroups()[0];

				$filterQuery = \craft\commerce\elements\Product::find()
				->where( "content.field_memberPermission NOT LIKE '%guest%' AND content.field_memberPermission LIKE '%".$group->handle."%'");

				return $filterQuery;
			}

			return [];
		}

		public function getMembersOnlyEvents($productType) {
			$user = Craft::$app->getUser();

			//check if user is in group
			if(!empty($user) && $user->getIdentity()->getGroups()) {
				$group = $user->getIdentity()->getGroups()[0];

				$filterQuery = \craft\commerce\elements\Events::find()
				->where( "content.field_memberPermission NOT LIKE '%guest%' AND content.field_memberPermission LIKE '%".$group->handle."%'");

				return $filterQuery;
			}

			return [];
	}

	public function isAdmin() {
				//get the current user from craft
		$user = Craft::$app->getUser();

		//check if user is in group
		return $user->getIdentity()->isInGroup('YalumbaAdmin');
	}

	public function getImageUrl($element) {
    if(!empty($element->productImageFront)) {
      if($element->productImageFront->one()) {
        return $element->productImageFront->one()->getUrl();
      }
    }
   // item.element.defaultVariant.productImageFront.one().getUrl()
    if(!empty($element->product->productinfogift)) {
      return $element->product->productinfogift->productImageFront->one()->getUrl();
    }
    if(!empty($element->product->productInfoMerchandise)) {
      return $element->product->productInfoMerchandise->productImageFront->one()->getUrl();
    }

    return '';
  }
}