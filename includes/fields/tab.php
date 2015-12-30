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
    ?>
        <script>
        (function($) {
            $(document).on('click', '.cfs-tab', function() {
                var tab = $(this).attr('rel'),
                    $context = $(this).parents('.cfs_input');
                $context.find('.cfs-tab').removeClass('active');
                $context.find('.cfs-tab-content').removeClass('active');
                $(this).addClass('active');
                $context.find('.cfs-tab-content-' + tab).addClass('active');
            });

            $(function() {
                $('.cfs-tabs').each(function(){
                    $(this).find('.cfs-tab:first').click();
                });
            });
        })(jQuery);
        </script>
    <?php
    }
}
