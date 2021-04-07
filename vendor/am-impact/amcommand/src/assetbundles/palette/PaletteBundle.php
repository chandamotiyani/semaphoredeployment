<?php
/**
 * Command palette for Craft.
 *
 * @author    a&m impact
 * @copyright Copyright (c) 2017 a&m impact
 * @link      http://www.am-impact.nl
 */

namespace amimpact\commandpalette\assetbundles\palette;

use amimpact\commandpalette\CommandPalette;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PaletteBundle extends AssetBundle
{
    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // Define the path that your publishable resources live
        $this->sourcePath = '@amimpact/commandpalette/assetbundles/palette/dist';

        // Define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // Gather resources
        $this->js = [
            'js/Palette.min.js',
            'js/fuzzysort.min.js',
        ];

        $this->css = [
            'css/Palette.css',
        ];

        // Did we select a different theme?
        $settings = CommandPalette::$plugin->getSettings();
        $themeFile = $settings->getSelectedTheme();
        if ($themeFile) {
            $this->css[] = 'css/' . $themeFile;
        }

        parent::init();
    }
}
