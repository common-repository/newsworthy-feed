<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'nwaif_settings.php';
require_once 'nwaif_utils.php';
require_once 'nwaif_image.php';
require_once 'nwaif_logs.php';
require_once 'nwaif_date.php';

class NWaiF_Post {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new NWaiF_Post();
        }

        return self::$instance;
    }

    public function saveArticles( $feedKey, $articles ) {
        if ( !NWaiF_Settings::getInstance()->get( 'url', $feedKey ) ) {
            return false;
        }

        $postIds = array();
        foreach( $articles as $article ) {
            $postId = $this->getExistingPostId( $article['url_md5'] );

            if ( !$postId ) {
                $postStatus = NWaiF_Settings::getInstance()->get( 'status', $feedKey );
                $postStatus = $postStatus
                    ? $postStatus
                    : 'draft';
    
                $postData = array(
                    'post_title' => $article['title'],
                    'post_content' => $this->fixContent( $feedKey, $article ),
                    'post_status' => $postStatus,
                );
                $this->addDates( $postData, $article['date'] );
    
                if ( !empty( $article['summary'] ) ) {
                    $postData['post_excerpt'] = $article['summary'];
                }
                if ( NWaiF_Settings::getInstance()->get( 'author', $feedKey ) ) {
                    $postData['post_author'] = NWaiF_Settings::getInstance()->get( 'author', $feedKey );
                }
                if ( NWaiF_Settings::getInstance()->get( 'status', $feedKey ) == 'password' && NWaiF_Settings::getInstance()->get( 'password', $feedKey ) ) {
                    $postData['post_password'] = NWaiF_Settings::getInstance()->get( 'password', $feedKey );
                }
    
                $postId = wp_insert_post( $postData );
                if ( !is_wp_error( $postId ) && $postId > 0 ) {
                    $postIds[] = $postId;

                    $this->setTemplate( $feedKey, $postId );
                    $this->setImage( $feedKey, $postId, $article );
                }
            }

            if ( $postId > 0 ) {
                $this->setCategories( $feedKey, $postId );
                $this->setTags( $feedKey, $postId );
            }
        }

        $savedPosts = count( $postIds );
        $date = NWaiF_Date::getInstance()->wpDate( 'now', NWaiF_Date::getInstance()->getWpFormat() ) .
            ' (' . NWaiF_Date::getInstance()->utcDate( 'now', NWaiF_Date::getInstance()->getWpFormat() ) . ' UTC)';

        NWaiF_Settings::getInstance()->update(
            $savedPosts > 0
                ? $savedPosts . ' new articles added at ' . $date
                : 'No new articles found at ' . $date,
            'feed_processed_msg',
            $feedKey
        );

        return $postIds;
    }

    private function getExistingPostId( $artMd5Url ) {
        $query = new WP_Query( [
            's' => '_feed_article ' . $artMd5Url,
            'post_type' => 'post',
            'post_status' => 'any',
            'posts_per_page' => 1
        ] );
        if ( !$query->have_posts() ) {
            return false;
        }

        return $query->posts[0]->ID;
    }

    private function fixContent( $feedKey, $article ) {
        $content = '<!-- ' . NWaiF_Utils::getInstance()->getPostContentKeyword( $feedKey ) . ' ' . $article['url_md5'] .  ' -->';
        $content .= "\r\n" . $article['content'];

        return $content;
    }

    private function addDates( &$postData, $utcDate ) {
        if ( NWaiF_Date::getInstance()->compareUtcDates( $utcDate ) > 0 ) {
            $utcDate = NWaiF_Date::getInstance()->utcDate();
        }
        $wpDate = NWaiF_Date::getInstance()->wpDate( $utcDate );

        $postData['post_date'] = $wpDate;
        $postData['post_date_gmt'] = $utcDate;
    }

    private function setCategories( $feedKey, $postId ) {
        $categories = NWaiF_Settings::getInstance()->get( 'categories', $feedKey );
        if ( !empty( $categories ) && is_array( $categories ) ) {
            wp_set_object_terms( $postId, $categories, 'category' );
        }
    }

    private function setTags( $feedKey, $postId ) {
        $tags = NWaiF_Settings::getInstance()->get( 'tags', $feedKey );
        if ( !empty( $tags ) && is_array( $tags ) ) {
            wp_set_post_tags( $postId, $tags );
        }
    }

    private function setTemplate( $feedKey, $postId ) {
        if ( NWaiF_Settings::getInstance()->get( 'template', $feedKey ) ) {
            update_post_meta( $postId, '_wp_page_template', NWaiF_Settings::getInstance()->get( 'template', $feedKey ) );
        }
    }

    private function setImage( $feedKey, $postId, $article ) {
        if ( NWaiF_Image::getInstance()->enabled( $feedKey ) ) {
            $attachId = NWaiF_Image::getInstance()->defaultImgId();
            if ( !empty( $article['image_url'] ) ) {
                $attachId = NWaiF_Image::getInstance()->create( $article['image_url'], $feedKey );
            }

            if ( $attachId ) {
                set_post_thumbnail( $postId, $attachId );
            }
        }
    }
}
