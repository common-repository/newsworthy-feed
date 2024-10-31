<?php
    require_once 'nwaif_utils.php';
    require_once 'nwaif_settings.php';
?>

<div class="wrap">
    <h1>
        <img src="../<?php echo esc_html( NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_logo.jpg' ) ); ?>" width="30" height="30" />
        Newsworthy Feed Settings
    </h1>

    <form action="options.php" method="post" id="nwaif-form" >
        <div class="nwaif-form-row" >
            <span class="nwaif-label">Frequency</span>
            <select name="nwaif_settings[frequency]" id="frequency" >
                <?php
                    for ( $i = 1; $i <= 24; $i++ ) {
                        $selected = NWaiF_Settings::getInstance()->get( 'frequency' ) == $i
                            ? 'selected'
                            : '';
                        echo '<option value="' . esc_html( $i ) . '" ' . esc_html( $selected ) . ' >' .
                            esc_html( $i ). ' hour(s)' .
                        "</option>\n";
                    }
                ?>
            </select>
            <p>How often to check feed for updates (in hours). Recommended setting: 1 hour(s)</p>
        </div>

        <div>
            <span class="nwaif-label" >Exclude categories</span>
            <p>
                <b>IMPORTANT</b>.
                Exclude the category assigned to Press Releases if you do NOT want Press Releases posted to your home page/blog, feed, archive, search.
                This is useful when you want post the Press Releases to a specific page on your website - separate from your other `posts`
            </p>
        </div> 

        <div class="nwaif-form-row nwaif-form-row-exclude-cat">
            <?php 
                $excludeType = 'front';
                include 'nwaif_form_exclude_categories.php'; 

                $excludeType = 'archive';
                include 'nwaif_form_exclude_categories.php'; 
            ?>

            <div class="nwaif-clearfix"></div>
        </div>

        <div class="nwaif-form-row nwaif-form-row-exclude-cat">
            <?php 
                $excludeType = 'feed';
                include 'nwaif_form_exclude_categories.php'; 

                $excludeType = 'search';
                include 'nwaif_form_exclude_categories.php'; 
            ?>

            <div class="nwaif-clearfix"></div>
        </div>

        <div class="nwaif-clearfix"></div>

        <input type="hidden" name="nwaif_settings[exclude_category]" id="exclude_category" value="<?php echo esc_html( NWaiF_Settings::getInstance()->get( 'exclude_category' ) ); ?>" />
        <input type="hidden" name="nwaif_settings[feeds]" id="feeds" />
    </form>

    <button id="feed_add_btn" class="button button-small">Add Feed</button>
    <div id="feed_add_content" style="display: none;" >
        <?php
            $feedKey = uniqid();
            $feedName = 'Feed 0';

            include 'nwaif_form_tab.php';
        ?>
    </div>

    <div id="nwaif-tabs" >
        <ul id="nwaif-tabs-list" >
            <?php
                $feeds = NWaiF_Settings::getInstance()->get( 'feeds' );
                foreach( $feeds as $feedKey => $feed ) {
                    echo '<li class="nwaif-tabs-btn" ><a href="#feed-' . esc_html( $feedKey ) . '">' .
                        esc_html( NWaiF_Settings::getInstance()->get( 'name', $feedKey ) ) .
                    "</a></li>\n";
                }

                if ( !count( $feeds ) ) {
                    $feedKey = uniqid();
                    echo '<li class="nwaif-tabs-btn" ><a href="#feed-' . esc_html( $feedKey ) . "\">Feed 0</a></li>\n";
                }
            ?>
        </ul>

        <div id="nwaif-tabs-container">
            <?php
                if ( !count( $feeds ) ) {
                    $feedName = 'Feed 0';

                    include 'nwaif_form_tab.php';
                } else {
                    foreach( $feeds as $feedKey => $feed ) {
                        include 'nwaif_form_tab.php';
                    }
                }
            ?>
        </div>

        <div class="nwaif-clearfix"></div>
    </div>
</div>

<button id="submit-btn" class="button button-primary" >Save</button>

<?php $attachmentGetParam = NWaiF_Utils::getInstance()->getWebAttachmentGetParam(); ?>

<!--<link href="../--><?php //echo NWaiF_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.css' ) . $attachmentGetParam; ?><!--" rel="stylesheet" type="text/css" />-->
<!--<link href="../--><?php //echo NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_style.css' ) . $attachmentGetParam; ?><!--" rel="stylesheet" type="text/css" />-->

<!--<script src="../--><?php //echo NWaiF_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.js' ) . $attachmentGetParam; ?><!--" ></script>-->
<!--<script src="../--><?php //echo NWaiF_Utils::getInstance()->getPluginPath( 'attachments/nwaif_script.js' ) . $attachmentGetParam; ?><!--" type="text/javascript" ></script>-->
</div>
