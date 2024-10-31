<div class="form-field" >
    <span class="nwaif-label" ><?php echo ucfirst( $excludeType ); if ( $excludeType == 'front' ) { echo ' page'; } ?></span>
    <select id="exclude_categories_<?php echo $excludeType; ?>" name="nwaif_settings[exclude_categories_<?php echo $excludeType; ?>][]"
      multiple="multiple" data-placeholder="Select category to exclude by" >
        <option value=""></option>

        <?php
            $feeds = NWaiF_Settings::getInstance()->get( 'feeds' );
            $selectedCats = array();
            foreach( $feeds as $feedKey => $feed ) {
                $selectedCats = array_merge( $selectedCats, NWaiF_Settings::getInstance()->get( 'categories', $feedKey ) );
            }
            $selectedCats = array_unique( array_filter( $selectedCats ) );

            $cats = NWaiF_Utils::getInstance()->getCategories();
            $excludeCatIds = NWaiF_Settings::getInstance()->get( 'exclude_categories_' . $excludeType );
            foreach( $selectedCats as $catId ) {
                $selected = in_array( $catId, $excludeCatIds )
                    ? 'selected="selected"'
                    : '';
                echo '<option value="' . esc_html( $catId ) . '" ' . esc_html( $selected ) . ' >' .
                    esc_html( $cats[ $catId ] ) .
                "</option>\n";
            }
        ?>
    </select>
</div>
