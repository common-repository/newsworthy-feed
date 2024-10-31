<?php
/**
 * @package Newsworthy_Feed
 * @version 1.6
 */
/**
 * Plugin Name: Newsworthy Feed
 * Plugin URI: https://wordpress.org/plugins/newsworthy-feed
 * Description: Newsworthy Feed enables you to get content from Newsworthy RSS feeds & save them as WP Posts. Check <a href="https://newsworthy.ai/en/docs/newsworthy-feed-plugin-for-wordpress" target="_blank" >Documentation</a>
 * Author: NewsWorthy.ai
 * Author URI: http://newsworthy.ai/
 * Version: 1.6
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

define( 'nwaifPluginVersion', '1.6' );

require_once 'nwaif_utils.php';
require_once 'nwaif_settings.php';
require_once 'nwaif_admin.php';
require_once 'nwaif_feed.php';
require_once 'nwaif_cron.php';
require_once 'nwaif_image.php';

nwaif_admin_init();

function nwaif_activate() {
    NWaiF_Image::getInstance()->createDir();
    NWaiF_Cron::getInstance()->activate();
}
register_activation_hook( NWaiF_Utils::getInstance()->getPluginBaseFile(), 'nwaif_activate' );

function nwaif_deactivate() {
    NWaiF_Cron::getInstance()->deactivate();
}
register_deactivation_hook( NWaiF_Utils::getInstance()->getPluginBaseFile(), 'nwaif_deactivate' );
