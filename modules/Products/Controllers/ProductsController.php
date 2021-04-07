<?php
namespace modules\Products\Controllers;


use Craft;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as Commerce;
use craft\web\Controller;
use modules\Orders\Models\Records\ProductRecord;
use yii\helpers\VarDumper;

class ProductsController extends Controller {


    public function actionExport($productId = NULL){
        $variants = Variant::find()->all();
        //TODO: look up the discounts manually for dabbler, adventurer, insider and apply their percentages manually to the rrp.
        foreach($variants as $variant){

        }
        //TODO: this is where the products export will go.
    }

	public function actionIndex($productId = NULL){
		$filter = Craft::$app->request->getQueryParams();
		$products = ProductRecord::find();
		if($productId){
			$products = $products->where(['id'=>$productId]);
		}

		if($filter){
			$products = $products->andFilterWhere($filter);
		}

		if($productId){
			$products = $products->one();
		}
		else{
			$products = $products->all();
		}
		if(count($products) == 1){
			$products = reset($products);
		}
		Craft::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $products;
	}
}