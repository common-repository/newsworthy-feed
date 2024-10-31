<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_utils.php';
require_once 'nwaif_settings.php';
require_once 'nwaif_cron.php';

function nwaif_options_page_html() {
    include 'nwaif_form.php';
}

function nwaif_options_page() {
    add_submenu_page(
        'options-general.php',
        'Newsworthy Feed Options',
        'Newsworthy Feed',
        'manage_options',
        'Newsworthy Feed',
        'nwaif_options_page_html'
    );
}

function nwaif_settings_set( $settings ) {
    NWaiF_Settings::getInstance()->set( $settings, 1 );
}

function nwaif_options_save() {
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['nwaif_settings'] ) ) {
        $settings = [
            'frequency' => !empty( $_POST['nwaif_settings']['frequency'] )
                ? sanitize_text_field( $_POST['nwaif_settings']['frequency'] )
                : 1,
            'exclude_categories_front' => !empty( $_POST['nwaif_settings']['exclude_categories_front'] )
                ? $_POST['nwaif_settings']['exclude_categories_front']
                : array(),
            'exclude_categories_feed' => !empty( $_POST['nwaif_settings']['exclude_categories_feed'] )
                ? $_POST['nwaif_settings']['exclude_categories_feed']
                : array(),
            'exclude_categories_archive' => !empty( $_POST['nwaif_settings']['exclude_categories_archive'] )
                ? $_POST['nwaif_settings']['exclude_categories_archive']
                : array(),
            'exclude_categories_search' => !empty( $_POST['nwaif_settings']['exclude_categories_search'] )
                ? $_POST['nwaif_settings']['exclude_categories_search']
                : array(),
            'feeds' => !empty( $_POST['nwaif_settings']['feeds'] )
                ? sanitize_text_field( $_POST['nwaif_settings']['feeds'] )
                : '[]',
        ];
        
        NWaiF_Utils::getInstance()->fixFieldDataFromPost( $settings, 'exclude_categories_front', $settings['exclude_categories_front'], 1 );
        NWaiF_Utils::getInstance()->fixFieldDataFromPost( $settings, 'exclude_categories_feed', $settings['exclude_categories_feed'],1  );
        NWaiF_Utils::getInstance()->fixFieldDataFromPost( $settings, 'exclude_categories_archive', $settings['exclude_categories_archive'], 1 );
        NWaiF_Utils::getInstance()->fixFieldDataFromPost( $settings, 'exclude_categories_search', $settings['exclude_categories_search'], 1 );

        $settings['feeds'] = json_decode( stripslashes( $settings['feeds'] ), 1 );
        $settings['feeds'] = is_array( $settings['feeds'] )
            ? $settings['feeds']
            : array();

        foreach( $settings['feeds'] as $i => $feed ) {
            foreach( $feed as $key => $data ) {
                NWaiF_Utils::getInstance()->fixFieldDataFromPost( $feed, $key, $data );
            }

            $settings['feeds'][ $i ] = $feed;
        }

        do_action( 'nwaif_settings_save', $settings );

        header( 'Location: ' . NWaiF_Utils::getInstance()->getSettingsLink() );
        die;
    }
}

function nwaif_add_action_links( $actions ) {
    return array_merge(
        [ '<a href="' . NWaiF_Utils::getInstance()->getSettingsLink() . '">Settings</a>' ],
        $actions
    );
}

function nwaif_activation_redirect( $plugin ) {
    if ( $plugin == NWaiF_Utils::getInstance()->getPluginBasename() ) {
        exit( wp_redirect( NWaiF_Utils::getInstance()->getSettingsLink() ) );
    }
}

function nwaif_exclude_posts( $query ) {
    $excludeCats = NWaiF_Settings::getInstance()->get( 'exclude_categories_front' );
    if ( count( $excludeCats ) && $query->is_home ) {
		$query->set( 'category__not_in', $excludeCats );
    }

    $excludeCats = NWaiF_Settings::getInstance()->get( 'exclude_categories_feed' );
    if ( count( $excludeCats ) && $query->is_feed ) {
		$query->set( 'category__not_in', $excludeCats );
    }

    $excludeCats = NWaiF_Settings::getInstance()->get( 'exclude_categories_archive' );
    if ( count( $excludeCats ) && $query->is_archive ) {
		$query->set( 'category__not_in', $excludeCats );
    }

    $excludeCats = NWaiF_Settings::getInstance()->get( 'exclude_categories_search' );
    if ( count( $excludeCats ) && $query->is_search ) {
		$query->set( 'category__not_in', $excludeCats );
    }

    return $query;
}

function nwaif_enqueue_admin_page_attachments() {
    if ( get_current_screen()->id != 'settings_page_Newsworthy Feed' ) {
        return;
    }

    wp_register_style( 'nwaif-admin-chosen', '/' . NWaiF_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.css' ), false, nwaifPluginVersion );
    wp_register_style( 'nwaif-admin', '/' . NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_style.css' ), [
        'nwaif-admin-chosen',
    ], nwaifPluginVersion );
    wp_enqueue_style( 'nwaif-admin' );

    wp_register_script( 'nwaif-admin-chosen', '/' . NWaiF_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.js' ), [
        'jquery-core'
    ], nwaifPluginVersion );
    wp_register_script( 'nwaif-admin', '/' . NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_script.js' ), [
        'nwaif-admin-chosen',
    ], nwaifPluginVersion );
    wp_enqueue_script( 'nwaif-admin' );
}

function nwaif_enqueue_post_attachments() {
    global $post;
    if ( empty( $post ) || empty( $post->post_content ) ) {
        return;
    }

    // getting postContentKeyword from post_content without `<!-- `
    $postContentStart = substr( $post->post_content, 5, 45 );
    for( $feedKey = 0; $feedKey <= NWaiF_Settings::getInstance()->feedsCount(); $feedKey++ ) {
        if ( $postContentStart != NWaiF_Utils::getInstance()->getPostContentKeyword( $feedKey ) ) {
            continue;
        }

        wp_register_script( 'nwaif-post-verify', '/' . NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_verify.js' ), false, nwaifPluginVersion );
        wp_enqueue_script( 'nwaif-post-verify' );
    }
}

function nwaif_upgrade( $upgrader, $options ) {
    $nwaifPluginPathName = NWaiF_Utils::getInstance()->getPluginBasename();
    if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
        foreach( $options['plugins'] as $pluginPathName ) {
            if ( $pluginPathName == $nwaifPluginPathName ) {
                NWaiF_Settings::getInstance()->backup();
            }
        }
    }
}

function nwaif_admin_init() {
    add_action( 'nwaif_settings_save', 'nwaif_settings_set' );

    add_action('admin_menu', 'nwaif_options_page');
    add_action('admin_init', 'nwaif_options_save');

    add_filter( 'plugin_action_links_' . NWaiF_Utils::getInstance()->getPluginBasename(), 'nwaif_add_action_links' );
    add_action( 'activated_plugin', 'nwaif_activation_redirect' );

    add_filter( 'pre_get_posts', 'nwaif_exclude_posts' );

    add_action( 'admin_enqueue_scripts', 'nwaif_enqueue_admin_page_attachments' );
    add_action( 'wp_enqueue_scripts', 'nwaif_enqueue_post_attachments' );

    NWaiF_Cron::getInstance()->init();
}
