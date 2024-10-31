<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_utils.php';

class NWaiF_Image {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Image();
        }

        return self::$instance;
    }

    public function enabled( $feedKey ) {
        return !empty( NWaiF_Settings::getInstance()->get( 'attach_images', $feedKey ) );
    }

    public function create( $imageUrl, $feedKey = 0 ) {
        $this->adjustImgWidth( $imageUrl, $feedKey );

        $imageName = md5( $imageUrl ) . '.png';
        $imagePath = NWaiF_Utils::getInstance()->getImagePath( $imageName );
        if ( !file_exists( $imagePath ) ) {
            $imageFile = fopen( $imagePath, 'w+' );

            $ch = curl_init( $imageUrl );
            curl_setopt( $ch, CURLOPT_FILE, $imageFile );
            curl_exec( $ch );
            
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            fclose( $imageFile );
            if ( $status != 200 ) {
                return 0;
            }
        }

        $filetype = wp_check_filetype( $imagePath, null );
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name( $imageName ),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachId = wp_insert_attachment( $attachment, $imagePath );
        if ( is_wp_error( $attachId ) || $attachId < 1 ) {
            return 0;
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attachData = wp_generate_attachment_metadata( $attachId, $imagePath );
        wp_update_attachment_metadata( $attachId, $attachData );

        return $attachId;
    }

    private function adjustImgWidth( &$imgUrl, $feedKey ) {
        $pattern = '/(\/resize\=\w\:){1}(\d+)\//';

        $matches = [];
        $res = preg_match_all( $pattern, $imgUrl, $matches );
        if ( !$res ) {
            return;
        }

        $imgWidth = !empty( $matches )
            ? end( $matches )[0]
            : 0;
        if ( !$imgWidth ) {
            return;
        }

        $requiredWidth = NWaiF_Settings::getInstance()->get( 'image_width', $feedKey );
        if ( $imgWidth != $requiredWidth ) {
            $imgUrl = preg_replace( $pattern, '${1}' . $requiredWidth . '/', $imgUrl );
        }
    }

    public function defaultImgId() {
        $imgUrl = NWaiF_Settings::getInstance()->get( 'image_url' );
        if ( !$imgUrl ) {
            return 0;
        }

        return $this->create( $imgUrl );
    }

    public function createDir() {
        $imgDirPath = NWaiF_Utils::getInstance()->getImagePath();
        if ( file_exists( $imgDirPath ) ) {
            return true;
        }

        return mkdir( $imgDirPath );
    }
}
