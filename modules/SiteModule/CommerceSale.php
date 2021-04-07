<?php

namespace modules\SiteModule;

use Craft;
use craft\fields\Categories;

class CommerceSale extends Categories {

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string {
		return Craft::t( 'app', 'Wine Type Field' );
	}

	/**
	 * @inheritdoc
	 */
	public function getElementValidationRules(): array {
		return [
			CategoryChildValidator::class,
		];
	}

}