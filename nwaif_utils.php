<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

const NWaiF_PLUGIN_NAME = 'newsworthy-feed';

class NWaiF_Utils {
    private static $instance;

    private $categories;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Utils();
        }

        return self::$instance;
    }

    public function getPluginPath( $filename = false, $absolute = false ) {
        $path = WP_PLUGIN_DIR . '/' . NWaiF_PLUGIN_NAME;

        if ( !$absolute ) {
            $path = explode( 'wp-content/', $path );
            $path = 'wp-content/' . $path[1];
        }

        return $filename
            ? $path . '/' . $filename
            : $path;
    }

    public function getPluginBaseFile() {
        return $this->getPluginPath( 'newsworthy_feed.php', true );
    }

    public function getPluginBasename() {
        return plugin_basename( $this->getPluginBasefile() );
    }

    public function getImagePath( $imagename = false ) {
        $path = wp_upload_dir();
        $path = $path['basedir'] . '/nwaif_imgs';

        return $imagename
            ? $path . '/' . $imagename
            : $path;
    }

    public function getSettingsLink() {
        return admin_url( 'options-general.php?page=Newsworthy+Feed' );
    }

    public function fixIntArr( &$arr ) {
        $arr = is_array( $arr )
            ? $arr
            : array(
                $arr
            );
        $arr = array_unique( array_filter( $arr ) );
        $arr = array_map( function( $el ) {
            return (int) $el;
        }, $arr );

        $arr = array_values( $arr );
        asort( $arr );
        $arr = array_values( $arr );
    }

    public function getCategories() {
        if ( empty( $this->categories ) ) {
            $cats = get_categories( [
                'hide_empty' => 0
            ] );

            foreach( $cats as $cat ) {
                $this->categories[ $cat->term_id ] = $cat->name;
            }
        }

        return $this->categories;
    }

    public function stripHtmlTags( $text ) {
        return html_entity_decode(
            strip_tags(
                $text
            )
        );
    }

    public function enableLogs() {
        require_once 'nwaif_logs.php';
        NWaiF_Logs::getInstance()->enable();
    }

    public function getWebAttachmentGetParam() {
        return '?v=' . nwaifPluginVersion;
    }

    public function getPostContentKeyword( $feedKey ) {
        return md5( NWaiF_Settings::getInstance()->get( 'url', $feedKey ) ) . '_feed_article';
    }

    public function isJson( $string ) {
        if ( is_array( $string ) ) {
            return false;
        }

        json_decode( $string );

        return ( json_last_error() == JSON_ERROR_NONE );
    }

    public function fixFieldDataFromPost( &$dataSet, $key, $fieldData, $alwaysArray = false ) {
        if ( empty( $fieldData ) ) {
            $dataSet[ $key ] = $alwaysArray 
                ? $fieldData 
                : null;
            return;
        }
        if ( !is_array( $fieldData ) ) {
            $dataSet[ $key ] = $alwaysArray 
                ? array( sanitize_text_field( $fieldData ) )
                : sanitize_text_field( $fieldData );
            return;
        }

        foreach( $fieldData as $k => $d ) {
            $dataSet[ $key ][ $k ] = sanitize_text_field( $d );
        }
    }
}
