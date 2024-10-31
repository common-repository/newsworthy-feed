( function( $ ) {
    $( 'document' ).ready( function() {
        $( '#submit-btn' ).click( function() {
            var form = $( '#nwaif-form' ),
                tabListBtns = $( '#nwaif-tabs-list li' ),
                feedsData = [],
                hasErrors = false;

            tabListBtns.removeClass( 'nwaif-error' );
            $( '.form-field' ).removeClass( 'nwaif-error' );

            tabListBtns.each( function() {
                var tabHasError = false,
                    tabBtn = $( this ),
                    tabId = $( this ).find( 'a' ).attr( 'href' ),
                    tab = $( tabId ),
                    urlInp = tab.find( 'input[name="url"]' ),
                    visibilityInp = tab.find( 'select[name="visibility"]' ),
                    passwordInp = tab.find( 'input[name="password"]' );

                var regexp = new RegExp( '^((http|https):\\/\\/)*(www.)*[a-zA-Z0-9-.]+\\.[a-zA-Z]{2,6}(\\/)*[-a-zA-Z0-9@:;\*%_\\+,.~#?&//=]*$' );
                if ( !urlInp.val() || !regexp.test( urlInp.val() ) 
                        || ( urlInp.val().indexOf( 'app.newsworthy.ai' ) < 0 && urlInp.val().indexOf( 'feeds.newsramp.net' ) < 0 )
                ) {
                    urlInp.parents( '.form-field' ).addClass( 'nwaif-error' );
                    tabHasError = true;
                }

                if ( visibilityInp.val() == 'password' && !passwordInp.val() ) {
                    visibilityInp.parents( '.form-field' ).addClass( 'nwaif-error' );
                    passwordInp.parents( '.form-field' ).addClass( 'nwaif-error' );
                    tabHasError = true;
                }

                if ( tabHasError ) {
                    tabBtn.addClass( 'nwaif-error' );
                    hasErrors = true;
                } else {
                    feedsData[ feedsData.length ] = collectTabData( tab );
                }
            } );

            if ( !feedsData.length ) {
                tabListBtns.addClass( 'nwaif-error' );
                hasErrors = true;
            }

            if ( hasErrors ) {
                return false;
            }
            form.find( '#feeds' ).val( JSON.stringify( feedsData ) );

            form.submit();
        } );

        $( '#frequency' ).chosen();
        $( '#exclude_categories_front, #exclude_categories_feed, #exclude_categories_archive, #exclude_categories_search' ).chosen( {
            allow_single_deselect: true
        } );

        $( '#nwaif-tabs-list' ).on( 'click', 'li', function() {
            var tab = $( this ),
                tabLink = $( this ).find( 'a' ),
                tabId = tabLink.attr( 'href' );

            tab.parent().find( 'li' ).removeClass( 'active' );
            $( '.nwaif-tabs-tab' ).removeClass( 'active' );

            tab.addClass( 'active' );
            $( tabId ).addClass( 'active' );

            enableTab( $( tabId ) );
            tabLink.blur();

            return false;
        } );
        $( '#nwaif-tabs-list li:first' ).click();

        function uniqid( prefix ) {
            prefix = prefix != undefined
                ? prefix
                : '';

            var sec = Date.now() * 1000 + Math.random() * 1000,
                id = sec.toString( 16 ).replace( /\./g, '' ).padEnd( 14, '0' );

            return prefix + id + Math.trunc( Math.random() * 100000000 );
        }

        $( '#feed_add_btn' ).click( function() {
            var tabsList = $( '#nwaif-tabs-list' ),
                tabsCount = tabsList.find( 'li' ).length,
                tabsContainer = $( '#nwaif-tabs-container' ),
                newTabContent = $( $( '#feed_add_content' ).html() ),
                newTabId = 'feed-' + uniqid();

            tabsList.append(
                $( '<li class="nwaif-tabs-btn" />' ).append(
                    $( '<a />' ).attr( 'href', '#' + newTabId )
                        .html( 'Feed ' + tabsCount )
                )
            );

            newTabContent.attr( 'id', newTabId );

            $.when( tabsContainer.append( newTabContent ) ).done( function() {
                tabsList.find( 'li:last' ).click();
            } );
        } );

        function collectTabData( tab ) {
            var tabData = {};
            tab.find( '.nwaif-tab-inp' ).each( function() {
                var name = $( this ).attr( 'name' );
                if ( name.slice( -2 ) == '[]' ) {
                    name = name.slice( 0, name.length - 2 );
                }

                tabData[ name ] = $( this ).val();
                if ( $( this ).attr( 'type' ) == 'checkbox' ) {
                    tabData[ name ] = $( this ).is( ':checked' )
                        ? 1
                        : 0;
                }
            } );

            return tabData;
        }

        function enableTab( tab ) {
            tab.find( '.feed-remove-btn' ).click( function() {
                var tab = $( this ).parents( '.nwaif-tabs-tab' ),
                    tabId = tab.attr( 'id' ),
                    tabList = $( '#nwaif-tabs-list' ),
                    tabListBtn = tabList.find( 'a[href="#' + tabId + '"]' ).parent();

                tab.remove();
                tabListBtn.remove();

                tabList.find( 'li:first' ).click();

                return false;
            } );

            tab.find(
                'select[name="visibility"],select[name="status"],select[name="author"],select[name="template"]'
            ).chosen();

            tab.find( 'select[name="categories[]"]' ).chosen( {
                no_results_text: 'No such category, please create new',
                allow_single_deselect: true,
            } ).css( {
                minWidth: '100%',
                width: 'auto'
            } );

            tab.find( 'select[name="tags[]"]' ).chosen( {
                no_results_text: 'No such tag, please create new',
                allow_single_deselect: true,
            } ).css( {
                minWidth: '100%',
                width: 'auto'
            } );

            tab.find( 'select[name="exclude_keywords[]"]' ).chosen( {
                no_results_text: 'No such keyword, please create new',
                allow_single_deselect: true,
            } ).css( {
                minWidth: '100%',
                width: 'auto'
            } );

            tab.find( 'select[name="visibility"]' ).change( function() {
                var inp = tab.find( 'input[name="password"]' ),
                    inpContainer = inp.parent();

                if ( $( this ).val() == 'password' ) {
                    inpContainer.show();
                }
                else {
                    inp.val( '' );
                    inpContainer.hide();
                }
            } ).change();

            tab.find( '.categories_add_btn, .tags_add_btn, .exclude_keywords_add_btn' ).click( function() {
                var btn = $( this ),
                    type = 'categories';
                if ( btn.hasClass( 'tags_add_btn' ) ) {
                    type = 'tags';
                } else if ( btn.hasClass( 'exclude_keywords_add_btn' ) ) {
                    type = 'exclude_keywords';
                }

                var inp = tab.find( 'input[name="' + type + '_add"]' ),
                    select = tab.find( 'select[name="' + type + '[]"]' );
                if ( !select.length || !inp.length ) {
                    return false;
                }

                var newVal = inp.val();
                if ( !newVal ) {
                    return false;
                }
                newVal.trim();

                var existingVals = [];
                select.find( 'option' ).each( function( key, val ) {
                    existingVals[existingVals.length] = $( val ).html().trim();
                } );

                var optKey = existingVals.indexOf( newVal );
                if ( optKey > -1 ) {
                    $( select.find( 'option' )[optKey] ).attr( 'selected', true );
                } else {
                    var optVal = 'new_' + newVal;
                    if ( !select.find( 'option[value="' + optVal + '"]' ).length ) {
                        var option = $( '<option />' );
                        option.val( optVal ).html( newVal );

                        select.prepend( option );
                    }

                    select.find( 'option[value="' + optVal + '"]' ).attr( 'selected', true );
                }

                select.change();
                select.trigger( 'chosen:updated' );

                inp.val( '' );

                return false;
            } );

            tab.find( 'select[name="categories[]"]' ).change( function() {
                var excludeCatInpsData = {
                    'exclude_categories_front': {
                        val: $( '#exclude_categories_front' ).val(),
                        inp: $( '#exclude_categories_front' ),
                    },
                    'exclude_categories_feed': {
                        val: $( '#exclude_categories_feed' ).val(),
                        inp: $( '#exclude_categories_feed' ),
                    },
                    'exclude_categories_archive': {
                        val: $( '#exclude_categories_archive' ).val(),
                        inp: $( '#exclude_categories_archive' ),
                    },
                    'exclude_categories_search': {
                        val: $( '#exclude_categories_search' ).val(),
                        inp: $( '#exclude_categories_search' ),
                    },
                    },
                    selectedCats = {};
                for ( const [id, data] of Object.entries( excludeCatInpsData ) ) {
                    data['inp'].find( 'option:not([value=""])' ).remove();
                }

                $( 'select[name="categories[]"]' ).each( function() {
                    $( this ).find( 'option:selected' ).each( function() {
                        selectedCats[ $( this ).val() ] = $( this ).html().trim();
                    } );
                } );

                for( var catId in selectedCats ) {
                    var catName = selectedCats[ catId ],
                        option = $( '<option />' );
                    option.attr( 'value', catId );
                    option.html( catName );

                    for ( const [id, data] of Object.entries( excludeCatInpsData ) ) {
                        data['inp'].append( option.clone() );
                    }
                }

                for ( const [id, data] of Object.entries( excludeCatInpsData ) ) {
                    for ( const[i, selCat] of Object.entries( data['val'] ) ) {
                        if ( data['inp'].find( 'option[value="' + selCat + '"]' ).length > 0 ) {
                            data['inp'].find( 'option[value="' + selCat + '"]' ).attr( 'selected', true );
                        }
                    }

                    data['inp'].change();
                    data['inp'].trigger( 'chosen:updated' );
                }
            } ).change();

            tab.find( 'input[name="attach_images"]' ).change( function() {
                var urlInp = tab.find( 'input[name="image_url"]' ),
                    urlInpContainer = urlInp.parent(),
                    widthInpContainer = tab.find( 'input[name="image_width"]' ).parent();

                if ( $( this ).prop( 'checked' ) ) {
                    urlInpContainer.show();
                    widthInpContainer.show();
                }
                else {
                    urlInp.val( '' ).change();
                    urlInpContainer.hide();

                    widthInpContainer.hide();
                }
            } ).change();

            tab.find( 'input[name="image_url"]' ).change( function() {
                var imgObj = $( this ).parents( '.nwaif-form-row-img' ).find( 'img.image_url_preview' ),
                    imgContainer = imgObj.parent(),
                    imgUrlContainer = $( this ).parents( '.nwaif-form-row-img' ).find( 'input[name="image_url"]' ).parent();
                imgObj.hide();
                imgUrlContainer.removeClass( 'nwaif-image-active' );

                imgObj.attr( 'src', $( this ).val() );
                if ( $( this ).val() ) {
                    imgObj.show();
                    imgUrlContainer.addClass( 'nwaif-image-active' );
                }
            } ).change();
        }
    } );
} )( jQuery );
