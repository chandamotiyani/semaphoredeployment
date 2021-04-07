<?php


namespace modules\Memberships;


use craft\commerce\elements\Product;
use yii\base\Behavior;

class MembershipPermissionBehavior extends Behavior
{
    function withPermission(){

        $user = \Craft::$app->user;
        if($user->getId()){
            $userPermissions = \Craft::$app->getUserPermissions()->getPermissionsByUserId($user->getId());
        }
        else{
            $userPermissions = [];
        }
        //if this element has the permission field, apply the filter for this user.
        $userPermissionCategories = [];
        $wheres = [];
        foreach ( $userPermissions as $userPermission ) {
            $permName = str_replace( 'view-', '', $userPermission );
            $wheres[] = ["LIKE", "field_memberPermission", "$permName"];
        }
        if($user->getIsGuest()){
            $wheres[] = ["LIKE", "field_memberPermission", "guest"];
        }
        $wheres = array_merge(["OR"], $wheres);
        $wheres[] = ['=', 'field_memberPermissionVisible', true];
        $this->owner->andWhere(['OR', $wheres]);

        return $this->owner;
    }
}