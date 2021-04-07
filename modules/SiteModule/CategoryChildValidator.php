<?php

namespace modules\SiteModule;

use Craft;
use craft\services\Categories as ServiceCategory;
use yii\validators\Validator;

class CategoryChildValidator extends Validator {
	public function validateValue( $value ) {
		$serviceCategory = new ServiceCategory();

		$sortedCategories = $this->sortByLevel( $value->id, $serviceCategory );

		$childrenParents = $this->getChildrensParents( $sortedCategories['children'], $serviceCategory );

		$singleParents = array_diff( $sortedCategories['parents'], $childrenParents );

		if ( $singleParents ) {
			return [ Craft::t( 'app', 'Looks like you\'ve selected top level categories. Please remove single top level categories' ), [ 'min' => 1 ] ];
		}

		return null;

	}

	private function getChildrensParents( $children, $serviceCategory ) {

		$parents = [];

		foreach ( $children as $child ) {
			$category = $serviceCategory->getCategoryById( $child );

			$parents[] = $category->parent->id;
		}

		return array_unique( $parents );
	}

	private function sortByLevel( $categories, $serviceCategory ) {

		$parents  = [];
		$children = [];

		foreach ( $categories as $category ) {
			if ( $serviceCategory->getCategoryById( $category )->level == 1 ) {
				$parents[] = $category;
			} else {
				$children[] = $category;
			}
		}

		return [
			'parents'  => $parents,
			'children' => $children,
		];
	}
}