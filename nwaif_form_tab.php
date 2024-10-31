<?php $feedKey = isset( $feedKey ) ? $feedKey : 0; ?>

<div id="feed-<?php echo esc_html( $feedKey ); ?>" class="nwaif-tabs-tab" >
    <?php $status = NWaiF_Settings::getInstance()->get( 'feed_processed', $feedKey ); ?>
    <?php if ( $status !== null ) { ?>
        <div class="form-wrap" >
            <div class="form-field <?php echo !$status ? 'nwaif-error' : ''; ?>" >
               <span class="nwaif-label" >Status</span>
                <p><?php echo esc_html( NWaiF_Settings::getInstance()->get( 'feed_processed_msg', $feedKey ) ); ?></p>
            </div>
        </div>
    <?php } ?>

    <div class="form-wrap">
        <div class="form-field">
            <span class="nwaif-label" >Feed Url</span>
            <input type="text" name="url" class="nwaif-tab-inp" value="<?php echo esc_url_raw( NWaiF_Settings::getInstance()->get( 'url', $feedKey ) ); ?>" />
            <button class="button button-small feed-remove-btn">Remove Feed</button>
            <p>Newsworthy.ai will provide you with a unique RSS feed. Only Newsworthy.ai feeds will work properly for this plugin</p>
        </div>

        <input name="name" type="hidden" value="<?php echo esc_html( NWaiF_Settings::getInstance()->get( 'name', $feedKey ) ); ?>" />
    </div>

    <div class="nwaif-form-row nwaif-form-row-vis">
        <div class="form-field">
            <span class="nwaif-label" >Visibility</span>
            <select name="visibility" class="nwaif-tab-inp" >
                <?php
                $visOptions = array(
                        'public',
                        'private',
                        'password',
                );
                foreach( $visOptions as $vis ) {
                    $selected = NWaiF_Settings::getInstance()->get( 'visibility', $feedKey ) == $vis
                        ? 'selected'
                        : '';
                    echo '<option value="' . esc_html( $vis) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $vis ) .
                    "</option>\n";
                }
                ?>
            </select>
            <p>Recommended setting: Public</p>
        </div>

        <div class="form-field">
            <span class="nwaif-label" >Password</span>
            <input type="text" name="password" class="nwaif-tab-inp" value="<?php echo esc_html( NWaiF_Settings::getInstance()->get( 'password', $feedKey ) ); ?>" />
            <p>Password to open post</p>
        </div>

        <div class="nwaif-clearfix"></div>
    </div>

    <div class="nwaif-form-row nwaif-form-row-stat-lim">
        <div class="form-field">
            <span class="nwaif-label" >Status</span>
            <select name="status" class="nwaif-tab-inp" >
                <?php
                $statuses = array(
                        'publish',
                        'draft',
                        'private',
                );
                foreach( $statuses as $status ) {
                    $selected = NWaiF_Settings::getInstance()->get( 'status', $feedKey ) == $status
                        ? 'selected'
                        : '';
                    echo '<option value="' . esc_html( $status ) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $status ) .
                    "</option>\n";
                }
                ?>
            </select>
            <p>
                Recommended setting: Publish. Press Releases will go live at the scheduled time.
                Draft mode requires manual publishing
            </p>
        </div>

        <div class="form-field">
            <span class="nwaif-label" >Limit</span>
            <input type="text" name="limit" class="nwaif-tab-inp" value="<?php echo esc_html( NWaiF_Settings::getInstance()->get( 'limit', $feedKey ) ); ?>" />
            <p>Number of articles to fetch backwards. Recommended setting: 10</p>
        </div>

        <div class="nwaif-clearfix"></div>
    </div>

    <div class="nwaif-form-row nwaif-form-row-tem-aut" >
        <div class="form-field">
            <span class="nwaif-label" >Author</span>
            <select name="author" class="nwaif-tab-inp" >
                <?php
                $selectedAuthor = NWaiF_Settings::getInstance()->get( 'author', $feedKey );
 
                $users = get_users( array(
                        'orderby' => 'display_name',
                        'order' => 'ASC',
                        'fields' => array( 'ID', 'display_name' ),
                ) );
                foreach ( $users as $user ) {
                    $selected = '';
                    if ( $selectedAuthor == $user->ID ) {
                        $selected = 'selected';
                    }

                    echo '<option value="' . esc_html( $user->ID ) . '" ' . esc_html( $selected ) . ' >' .
                        esc_html( $user->display_name ) .
                    "</option>\n";
                }
                ?>
            </select>

            <p>
                The author that you want assigned to press releases. We recommend creating a new author (e.g. Newsworthy or Press Releases)
                You may also edit your template to remove remove author tags from posts
            </p>
        </div>

        <div class="form-field">
            <span class="nwaif-label" >Template</span>
            <select name="template" class="nwaif-tab-inp" >
                <?php
                $template = NWaiF_Settings::getInstance()->get( 'template', $feedKey ) . '';
                page_template_dropdown( $template, 'post' );
                ?>
            </select>
            <p>Template For Posts</p>
        </div>

        <div class="nwaif-clearfix"></div>
    </div>

    <div class="nwaif-form-row nwaif-form-row-cat-tag" >
        <div class="form-field">
            <div>
                <span class="nwaif-label" >New Category</span>
                <input type="text" name="categories_add" />
                <button class="button button-small categories_add_btn">Add Category</button>
            </div>

            <div>
                <span class="nwaif-label" >Categories</span>
                <select name="categories[]" multiple="multiple" class="nwaif-tab-inp" data-placeholder="Select categories" >
                    <?php
                    $cats = NWaiF_Utils::getInstance()->getCategories();
                    foreach( $cats as $catId => $catName ) {
                        $selected = '';
                        if ( in_array( $catId, NWaiF_Settings::getInstance()->get( 'categories', $feedKey ) ) ) {
                            $selected = 'selected';
                        }

                        echo '<option value="' . esc_html( $catId ) . '" ' . esc_html( $selected ) . ' >' .
                            esc_html( $catName ) .
                        "</option>\n";
                    }
                    ?>
                </select>
            </div>

            <p>Posts Categories. We recommend creating a new category for Press Releases</p>
        </div>

        <div class="form-field">
            <div>
                <span class="nwaif-label" >New Tag</span>
                <input type="text" name="tags_add" />
                <button class="button button-small tags_add_btn">Add Tag</button>
            </div>

            <div>
                <span class="nwaif-label" >Tags</span>
                <select name="tags[]" multiple="multiple" class="nwaif-tab-inp" data-placeholder="Select tags" >
                    <?php
                    $tags = get_tags( array(
                        'hide_empty' => 0
                    ) );
                    foreach( $tags as $tag ) {
                        $selected = '';
                        if ( in_array( $tag->term_id, NWaiF_Settings::getInstance()->get( 'tags', $feedKey ) ) ) {
                            $selected = 'selected';
                        }

                        echo '<option value="' . esc_html( $tag->term_id ) . '" ' . esc_html( $selected ) . ' >' .
                            esc_html( $tag->name ) .
                        "</option>\n";
                    }
                    ?>
                </select>
            </div>

            <p>Posts Tags</p>
        </div>

        <div class="nwaif-clearfix" ></div>
    </div>

    <div class="nwaif-form-row nwaif-form-row-cat-tag" >
        <div class="form-field">
            <div>
                <span class="nwaif-label" >New Keyword</span>
                <input type="text" name="exclude_keywords_add" />
                <button class="button button-small exclude_keywords_add_btn">Add Keyword</button>
            </div>

            <div>
                <span class="nwaif-label" >Keywords to exclude by</span>
                <select name="exclude_keywords[]" multiple="multiple" class="nwaif-tab-inp" data-placeholder="Select keywords to exclude by" >
                    <?php
                    $exclude_keywords = NWaiF_Settings::getInstance()->get( 'exclude_keywords', $feedKey );
                    foreach( $exclude_keywords as $kw ) {
                        echo '<option value="' . esc_html( $kw ) . '" selected="selected" >' .
                            esc_html( $kw ) .
                        "</option>\n";
                    }
                    ?>
                </select>
            </div>

            <p>Keywords to exclude posts by. We use exact text match to find keywords in post title/content</p>
        </div>

        <div class="nwaif-clearfix" ></div>
    </div>

    <div class="nwaif-form-row nwaif-form-row-img">
        <div class="form-field">
            <span class="nwaif-label" >Attach images</span>
            <input type="checkbox" name="attach_images" value="1" class="nwaif-tab-inp"
              <?php echo NWaiF_Settings::getInstance()->get( 'attach_images', $feedKey ) ? 'checked="checked"' : ''; ?> />
            <p>Attach Images</p>
        </div>

        <div class="form-field">
            <span class="nwaif-label" >Fixed Image Width</span>
            <input type="text" name="image_width" class="nwaif-tab-inp"
              value="<?php echo esc_html( NWaiF_Settings::getInstance()->get( 'image_width', $feedKey ) ); ?>" />
            <p>Set fixed image width</p>
        </div>

        <div class="form-field">
            <span class="nwaif-label" >Default Image Url</span>
            <input type="text" name="image_url" class="nwaif-tab-inp"
              value="<?php echo esc_url_raw( NWaiF_Settings::getInstance()->get( 'image_url', $feedKey ) ); ?>" />
            <p>Image Url To Use When No Image Was Provided At Feed (Leave empty for none)</p>
        </div>

        <div class="form-field">
            <img src="<?php echo esc_url_raw( NWaiF_Settings::getInstance()->get( 'image_url', $feedKey ) ); ?>" class="image_url_preview" />
        </div>

        <div class="nwaif-clearfix"></div>
    </div>

    <div class="nwaif-clearfix" ></div>
</div>
