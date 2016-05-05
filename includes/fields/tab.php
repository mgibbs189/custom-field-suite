<?php

class cfs_tab extends cfs_field
{

    function __construct() {
        $this->name = 'tab';
        $this->label = __( 'Tab', 'cfs' );
    }


    // Prevent tabs from inheriting the parent field HTML
    function html( $field ) {

    }


    // Prevent tabs from inheriting the parent options HTML
    function options_html( $key, $field ) {

    }


    // Tab handling javascript
    function input_head( $field = null ) {
        $sessionStoreKey = "wp.plugin.custom-field-suite.group-id.{$field->group_id}";
    ?>
        <script>
        (function($) {

            var makeTabActive = function (tabEl, contextEl) {
                tabEl.addClass('active');
                contextEl.find('.cfs-tab-content-' + tabEl.attr('rel')).addClass('active');
            };

            var makeInactive = function (contextEl) {
                contextEl.find('.cfs-tab').removeClass('active');
                contextEl.find('.cfs-tab-content').removeClass('active');
            };

            $(document).on('click', '.cfs-tab', function() {

                var $context = $(this).parents('.cfs_input');

                makeInactive($context);
                makeTabActive($(this), $context);

                sessionStorage.setItem('<?=$sessionStoreKey;?>', $(this).attr('rel'));
            });

            $(function() {

                var lastActiveTabRel = sessionStorage.getItem('<?=$sessionStoreKey;?>'),
                    el = $('.cfs-tabs [rel="' + lastActiveTabRel +'"]');

                if (lastActiveTabRel && el.length > 0) {
                    makeTabActive(el, el.parents('.cfs_input'));
                } else {
                    var firstTab = $('.cfs-tab:first');
                    makeTabActive(firstTab, firstTab.parents('.cfs_input'));
                }

            });

        })(jQuery);
        </script>
    <?php
}
