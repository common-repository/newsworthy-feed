<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_utils.php';
require_once 'nwaif_image.php';

const NWaiF_SETTINGS_KEYNAME = 'nwaif_settings';

class NWaiF_Settings {
    private static $instance;
    private $data = array();

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Settings();
        }

        return self::$instance;
    }

    public function get( $key = false, $feedKey = null ) {
        if ( empty( $this->data ) ) {
            $settings = get_option( NWaiF_SETTINGS_KEYNAME );
            if ( NWaiF_Utils::getInstance()->isJson( $settings ) ) {
                $this->data = json_decode( $settings, 1 );
            }
        }

        if ( $key ) {
            $value = null;

            switch( $key ) {
                case 'frequency':
                    $value = !empty( $this->data['frequency'] )
                        ? $this->data['frequency']
                        : null;
                    break;
                case 'exclude_categories_front':
                    $value = !empty( $this->data['exclude_categories_front'] )
                        ? $this->data['exclude_categories_front']
                        : array();
                    // this is going to be removed in a few iterations, kept it for back compatibility
                    if ( !count( $value ) ) {
                        $value = !empty( $this->data['exclude_category'] )
                            ? array( $this->data['exclude_category'] )
                            : array();
                    }
                    break;
                case 'exclude_categories_feed':
                case 'exclude_categories_archive':
                case 'exclude_categories_search':
                    $value = !empty( $this->data[ $key ])
                        ? $this->data[ $key ]
                        : array();
                    break;
                case 'feeds':
                    $value = !empty( $this->data['feeds'] )
                        ? $this->data['feeds']
                        : array();
                    break;
                case 'name':
                    $feedKey !== null
                        ? $feedKey
                        : 0;
                    $value = !empty( $this->data['feeds'][ $feedKey ][ $key ] )
                        ? $this->data['feeds'][ $feedKey ][ $key ]
                        : 'Feed ' . $feedKey;

                    break;
                case 'categories':
                    $value = !empty( $this->data['feeds'][ $feedKey ]['categories'] )
                        ? $this->data['feeds'][ $feedKey ]['categories']
                        : array();
                    break;
                case 'tags':
                    $value = !empty( $this->data['feeds'][ $feedKey ]['tags'] )
                        ? $this->data['feeds'][ $feedKey ]['tags']
                        : array();
                    break;
                case 'exclude_keywords':
                    $value = !empty( $this->data['feeds'][ $feedKey ]['exclude_keywords'] )
                        ? $this->data['feeds'][ $feedKey ]['exclude_keywords']
                        : array();
                    break;
                case 'limit':
                case 'image_width':
                    $value = isset( $this->data['feeds'][ $feedKey ][ $key ] )
                        ? $this->data['feeds'][ $feedKey ][ $key ] + 0
                        : 0;
                    if ( !$value || $value < 0 ) {
                        $value = $key == 'limit'
                            ? 10
                            : 350;
                    }
                    break;
                default:
                    $value = $feedKey !== null && isset( $this->data['feeds'][ $feedKey ][ $key ] )
                        ? $this->data['feeds'][ $feedKey ][ $key ]
                        : null;
                    break;
            }

            return $value;
        }

        return $this->data;
    }

    public function feedsCount() {
        return count( $this->get( 'feeds' ) );
    }

    public function update( $value, $key, $feedKey = null ) {
        $settings = $this->get();
        if ( $feedKey === null ) {
            $excludeKeys = [
                'frequency',
                'exclude_categories_front',
                'exclude_categories_feed',
                'exclude_categories_archive',
                'exclude_categories_search',

                // this is going to be removed in a few iterations, kept it for back compatibility
                'exclude_category',
            ];
            if ( !in_array( $key, $excludeKeys ) ) { 
                return false;
            }

            $settings[ $key ] = $value;
        } else {
            if ( !$this->get( 'url', $feedKey ) ) {
                return false;
            }

            $settings['feeds'][ $feedKey ][ $key ] = $value;
        }

        $this->set( $settings, false );
    }

    public function set( $settings, $processFeeds = true ) {
        $this->validate( $settings );

        // check if settings were changed
        // the `update_option()` return FALSE if value is unchanged
        $oldSettingsMd5 = md5( json_encode( $this->get() ) );
        $newSettingsMd5 = md5( json_encode( $settings ) );

        if ( $oldSettingsMd5 != $newSettingsMd5 ) {
            if ( !update_option( NWaiF_SETTINGS_KEYNAME, json_encode( $settings ) ) ) {
                die( 'NWaiF: Failed to save settings' );
            }
            $this->data = $settings;
        }

        if ( $processFeeds ) {
            // process feed with updated options
            require_once 'nwaif_feed.php';
            nwaif_process_feed();

            // re-activate cron
            require_once 'nwaif_cron.php';
            NWaiF_Cron::getInstance()->activate();
        }
    }

    public function validate( &$settings ) {
        foreach( $settings['feeds'] as $feedKey => $feedSettings ) {
            $this->fixFeedUrlData( $feedSettings );
            $this->fixVisibilityData( $feedSettings );
            $this->fixCategoriesData( $feedSettings );
            $this->fixTagsData( $feedSettings );
            $this->fixExcludeKeywordsData( $feedSettings );
            $this->fixImageData( $feedSettings );
            $this->fixLimitData( $feedSettings );

            $settings['feeds'][ $feedKey ] = $feedSettings;
        }

        $this->fixExcludeCategoriesData( $settings );
    }

    private function fixFeedUrlData( &$settings ) {
        if ( filter_var( $settings['url'], FILTER_VALIDATE_URL ) === false ) {
            unset( $settings['url'] );
        }

        if ( strpos( $settings['url'], 'app.newsworthy.ai' ) === false && strpos( $settings['url'], 'feeds.newsramp.net' ) === false ) {
            unset( $settings['url'] );
        }
    }

    private function fixVisibilityData( &$settings ) {
        if ( $settings['visibility'] != 'password' ) {
            unset( $settings['password'] );
        }
    }

    private function fixCategoriesData( &$settings ) {
        $settings['categories'] = !empty( $settings['categories'] )
            ? $settings['categories']
            : array();
        $settings['categories'] = is_array( $settings['categories'] )
            ? array_filter( array_unique( $settings['categories'] ) )
            : [ $settings['categories'] ];

        $newCats = array();
        foreach( $settings['categories'] as $key => $category ) {
            if ( strlen( $category ) > 4 && substr( $category, 0, 4 ) == 'new_' ) {
                $newCats[] = substr( $category, 4 );
                unset( $settings['categories'][ $key ] );
            }
        }
        $newCats = array_unique( array_filter( $newCats ) );

        foreach( $newCats as $newCat ) {
            $newCatId = wp_create_category( $newCat );
            if ( !is_wp_error( $newCatId ) && $newCatId > 0 ) {
                $settings['categories'][] = $newCatId;
            }
        }

        if ( !empty( $settings['categories'] ) ) {
            NWaiF_Utils::getInstance()->fixIntArr( $settings['categories'] );
        } else {
            unset( $settings['categories'] );
        }
    }

    private function fixTagsData( &$settings ) {
        $settings['tags'] = !empty( $settings['tags'] )
            ? $settings['tags']
            : array();
        $settings['tags'] = is_array( $settings['tags'] )
            ? array_filter( array_unique( $settings['tags'] ) )
            : [ $settings['tags'] ];

        $newTags = array();
        foreach( $settings['tags'] as $key => $tag ) {
            if ( strlen( $tag ) > 4 && substr( $tag, 0, 4 ) == 'new_' ) {
                $newTags[] = substr( $tag, 4 );
                unset( $settings['tags'][ $key ] );
            }
        }
        $newTags = array_unique( array_filter( $newTags ) );

        foreach( $newTags as $newTag ) {
            $newTagId = wp_create_tag( $newTag );
            if ( !is_wp_error( $newTagId ) && !empty( $newTagId['term_id'] ) ) {
                $settings['tags'][] = $newTagId['term_id'];
            }
        }

        if ( !empty( $settings['tags'] ) ) {
            NWaiF_Utils::getInstance()->fixIntArr( $settings['tags'] );
        } else {
            unset( $settings['tags'] );
        }
    }

    private function fixExcludeKeywordsData( &$settings ) {
        $settings['exclude_keywords'] = !empty( $settings['exclude_keywords'] )
            ? $settings['exclude_keywords']
            : array();
        $settings['exclude_keywords'] = is_array( $settings['exclude_keywords'] )
            ? array_filter( array_unique( $settings['exclude_keywords'] ) )
            : [ $settings['exclude_keywords'] ];

        // we don't create keywords as taxonomy, they are just a list of text
        // but decided to use the same logic we already have for categories/tags
        foreach( $settings['exclude_keywords'] as $key => $kw ) {
            if ( strlen( $kw ) > 4 && substr( $kw, 0, 4 ) == 'new_' ) {
                $settings['exclude_keywords'][ $key ] = substr( $kw, 4 );
            }

            $settings['exclude_keywords'][ $key ] = trim( $settings['exclude_keywords'][ $key ] );
        }
        $settings['exclude_keywords'] = array_unique( array_filter( $settings['exclude_keywords'] ) );

        if ( empty( $settings['exclude_keywords'] ) ) {
            unset( $settings['exclude_keywords'] );
        }
    }

    private function fixImageData( &$settings ) {
        if ( !empty( $settings['attach_images'] ) && !empty( $settings['image_url'] ) ) {
            $attachId = NWaiF_Image::getInstance()->create( $settings['image_url'] );
            if ( !$attachId ) {
                unset( $settings['image_url'] );
            }
        }

        if ( empty( $settings['attach_images'] ) ) {
            unset( $settings['attach_images'] );
            unset( $settings['image_width'] );
            unset( $settings['image_url'] );
        } else {
            if ( empty( $settings['image_width'] ) ) {
                unset( $settings['image_width'] );
            }
            if ( empty( $settings['image_url'] ) ) {
                unset( $settings['image_url'] );
            }
        }
    }

    private function fixLimitData( &$settings ) {
        $settings['limit'] = !empty( $settings['limit'] )
            ? $settings['limit'] + 0
            : 10;
        $settings['limit'] = $settings['limit'] > 0
            ? $settings['limit']
            : 10;
    }

    private function fixExcludeCategoriesData( &$settings ) {
        // this is going to be removed in a few iterations, kept it for back compatibility
        if ( empty( $settings['exclude_category'] ) ) {
            unset( $settings['exclude_category'] );
        } else {
            if ( !empty( $settings['exclude_categories_front'] ) ) {
                $settings['exclue_categories_front'] = is_array( $settings['exclude_categories_front'] )
                    ? $settings['exclude_categories_front']
                    : array( $settings['exclude_categories_front'] );
            }

            $settings['exclude_categories_front'] = !empty( $settings['exclude_categories_front'] )
                ? array_merge( $settings['exclude_categories_front'], $settings['exclude_category'] )
                : array( $settings['exclude_category'] );
            $settings['exclude_categories_front'] = array_unique( $settings['exclude_categories_front'] );

            unset( $settings['exclude_category'] );
        }

        $types = array(
            'front',
            'feed',
            'archive',
            'search',
        );
        $excludeCatCnt = 4;
        foreach( $types as $type ) {
            if ( empty( $settings[ 'exclude_categories_' . $type ] ) ) {
                unset( $settings[ 'exclude_categories_' . $type ] );

                $excludeCatCnt--;
            } else {
                $settings[ 'exclude_categories_' . $type ] = is_array( $settings[ 'exclude_categories_' . $type ] )
                    ? $settings[ 'exclude_categories_' . $type ]
                    : array( $settings[ 'exclude_categories_' . $type ] );
            }
        }

        if ( $excludeCatCnt < 1 ) {
            return;
        }

        $cats = array();
        foreach( $settings['feeds'] as $feed ) {
            if ( !empty( $feed['categories'] ) ) {
                $cats = array_merge( $cats, $feed['categories'] );
            }
        }
        $cats = array_unique( array_filter( $cats ) );

        foreach( $types as $type ) {
            if ( empty( $settings[ 'exclude_categories_' . $type ] ) )  {
                continue;
            }

            foreach( $settings[ 'exclude_categories_' . $type ] as $i => $excludeCat ) {
                $excludeCat = $this->processExcludeCategory( $excludeCat, $cats );
                if ( !$excludeCat ) {
                    unset( $settings[ 'exclude_categories_' . $type ][$i] );
                    continue;
                }
            }

            if ( !count( $settings[ 'exclude_categories_' . $type ] ) ) {
                unset( $settngs[ 'exclude_categories_' . $type ] );
            }
        } 
    }

    private function processExcludeCategory( $excludeCat, $cats ) {
        if ( strlen( $excludeCat ) > 4 && substr( $excludeCat, 0, 4 ) == 'new_' ) {
            $excludeCat = wp_create_category( substr( $excludeCat, 4 ) );
        }

        if ( !in_array( $excludeCat, $cats ) ) {
            $excludeCat = null;
        }

        return $excludeCat;
    }
}
