<?php
namespace verbb\wishlist\elements;

use verbb\wishlist\Wishlist;
use verbb\wishlist\elements\db\ListQuery;
use verbb\wishlist\models\ListTypeModel;
use verbb\wishlist\records\ListRecord;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\db\Query;
use craft\elements\actions\Delete;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\validators\DateTimeValidator;

use yii\base\Exception;
use yii\base\InvalidConfigException;

class ListElement extends Element
{
    // Properties
    // =========================================================================

    public $reference;
    public $lastIp;
    public $typeId;
    public $userId;
    public $sessionId;
    public $default;

    private $_listType;
    private $_owner;
    private $_user;
    private $_fieldLayout;


    // Public Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('wishlist', 'Wishlist List');
    }

    public function __toString(): string
    {
        return (string)$this->title;
    }

    public function getName()
    {
        return $this->title;
    }

    public static function hasContent(): bool
    {
        return true;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public static function defineSources(string $context = null): array
    {
        if ($context === 'index') {
            $listTypes = Wishlist::$plugin->getListTypes()->getEditableListTypes();
            $editable = true;
        } else {
            $listTypes = Wishlist::$plugin->getListTypes()->getAllListTypes();
            $editable = false;
        }

        $listTypeIds = [];

        foreach ($listTypes as $listType) {
            $listTypeIds[] = $listType->id;
        }

        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('wishlist', 'All lists'),
                'criteria' => [
                    'typeId' => $listTypeIds,
                    'editable' => $editable
                ],
                'defaultSort' => ['postDate', 'desc']
            ]
        ];

        $sources[] = ['heading' => Craft::t('wishlist', 'List Types')];

        foreach ($listTypes as $listType) {
            $key = 'listType:'.$listType->id;
            $canEditLists = Craft::$app->getUser()->checkPermission('wishlist-manageListType:'.$listType->id);

            $sources[$key] = [
                'key' => $key,
                'label' => $listType->name,
                'data' => [
                    'handle' => $listType->handle,
                    'editable' => $canEditLists
                ],
                'criteria' => ['typeId' => $listType->id, 'editable' => $editable]
            ];
        }

        return $sources;
    }

    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('wishlist', 'Are you sure you want to delete the selected lists?'),
            'successMessage' => Craft::t('wishlist', 'Lists deleted.'),
        ]);

        return $actions;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = [['typeId'], 'required'];

        return $rules;
    }

    public static function find(): ElementQueryInterface
    {
        return new ListQuery(static::class);
    }

    public function getIsEditable(): bool
    {
        if ($type = $this->getType()) {
            return Craft::$app->getUser()->checkPermission('wishlist-manageListType:' . $type->id);
        }

        return false;
    }

    public function getCpEditUrl()
    {
        if ($listType = $this->getType()) {
            return UrlHelper::cpUrl('wishlist/lists/' . $listType->handle . '/' . $this->id);
        }

        return null;
    }

    public function getFieldLayout()
    {
        if ($this->_fieldLayout !== null) {
            return $this->_fieldLayout;
        }

        $listType = $this->getType();

        if (!$listType) {
            return null;
        }

        return $this->_fieldLayout = $listType->getListFieldLayout();
    }

    public function getType()
    {
        if ($this->_listType !== null) {
            return $this->_listType;
        }

        if ($this->typeId === null) {
            return null;
        }

        return $this->_listType = Wishlist::$plugin->getListTypes()->getListTypeById($this->typeId);
    }

    public function getItems()
    {
        return $this->id ? Item::find()->listId($this->id) : null;
    }

    public function getUser()
    {
        if ($this->_user !== null) {
            return $this->_user;
        }

        if ($this->userId === null) {
            return null;
        }

        return $this->_user = User::find()->id($this->userId)->one();
    }

    public function getOwnerId()
    {
        return $this->userId ?? $this->sessionId ?? null;
    }

    public function getOwner()
    {
        if ($this->_owner !== null) {
            return $this->_owner;
        }

        if ($this->userId === null) {
            return null;
        }

        return $this->_owner = $this->getUser();
    }

    public function setFieldValuesFromRequest(string $paramNamespace = '')
    {
        $this->setFieldParamNamespace($paramNamespace);
        $values = Craft::$app->getRequest()->getParam($paramNamespace, []);

        foreach ($this->fieldLayoutFields() as $field) {
            // Do we have any post data for this field?
            if (isset($values[$field->handle])) {
                $value = $values[$field->handle];
            } else if (!empty($this->_fieldParamNamePrefix) && UploadedFile::getInstancesByName($this->_fieldParamNamePrefix . '.' . $field->handle)) {
                // A file was uploaded for this field
                $value = null;
            } else {
                continue;
            }

            $this->setFieldValue($field->handle, $value);

            // Normalize it now in case the system language changes later
            $this->normalizeFieldValue($field->handle);
        }
    }

    public function getPdfUrl()
    {
        return UrlHelper::actionUrl("wishlist/pdf?listId={$this->id}");
    }

    public static function gqlTypeNameByContext($context): string
    {
        return 'List';
    }

    public function getGqlTypeName(): string
    {
        return static::gqlTypeNameByContext($this);
    }


    // URLs
    // -------------------------------------------------------------------------

    public function getAddToCartUrl()
    {
        return UrlHelper::actionUrl('wishlist/lists/add-to-cart', [ 'listId' => $this->id ]);
    }


    // Events
    // -------------------------------------------------------------------------

    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $listRecord = ListRecord::findOne($this->id);

            if (!$listRecord) {
                throw new Exception('Invalid list id: '.$this->id);
            }
        } else {
            $listRecord = new ListRecord();
            $listRecord->id = $this->id;
        }
        
        $listRecord->typeId = $this->typeId;
        $listRecord->reference = $this->reference;
        $listRecord->lastIp = $this->lastIp;
        $listRecord->userId = $this->userId;
        $listRecord->sessionId = $this->sessionId;
        $listRecord->default = $this->default;

        $listRecord->save(false);

        return parent::afterSave($isNew);
    }


    // Protected methods
    // =========================================================================

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'type' => ['label' => Craft::t('wishlist', 'List Type')],
            'owner' => ['label' => Craft::t('wishlist', 'Owner')],
            'items' => ['label' => Craft::t('wishlist', 'Items')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [];

        if ($source === '*') {
            $attributes[] = 'type';
        }

        return $attributes;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['title'];
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'owner':
                if ($owner = $this->getOwner()) {
                    return '<a href="' . $owner->getCpEditUrl() . '">' . $owner . '</a>';
                }

                return Craft::t('wishlist', 'Guest');
            case 'type':
                if ($listType = $this->getType()) {
                    return Craft::t('site', $listType->name);
                }

                return '';
            case 'items':
                return $this->items->count();
            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            [
                'label' => Craft::t('app', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated'
            ],
            [
                'label' => Craft::t('app', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated'
            ],
        ];
    }
}
