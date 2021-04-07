<?php
namespace verbb\wishlist\controllers;

use verbb\wishlist\Wishlist;

use Craft;
use craft\web\Controller;

class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $settings = Wishlist::$plugin->getSettings();

        return $this->renderTemplate('wishlist/settings/general', compact('settings'));
    }

    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        /* @var Settings $settings */
        $settings = Wishlist::$plugin->getSettings();
        $settings->setAttributes($request->getParam('settings'), false);

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('wishlist', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings,
            ]);

            return null;
        }

        $pluginSettingsSaved = Craft::$app->getPlugins()->savePluginSettings(Wishlist::$plugin, $settings->toArray());

        if (!$pluginSettingsSaved) {
            Craft::$app->getSession()->setError(Craft::t('wishlist', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('wishlist', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

}