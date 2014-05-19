<?php

class cfs_tab extends cfs_field
{

    function __construct( $parent ) {
        $this->name = 'tab';
        $this->label = __( 'Tab', 'cfs' );
        $this->parent = $parent;
    }


    function html( $field ) {

    }


    function options_html( $key, $field ) {

    }


    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(document).on('click', '.cfs-tab', function() {
                var tab = $(this).attr('rel');
                $('.cfs-tab').removeClass('active');
                $('.cfs-tab-content').removeClass('active');
                $(this).addClass('active');
                $('.cfs-tab-content-' + tab).addClass('active');
            });

            $(function() {
                $('.cfs-tab:first').click();
            });
        })(jQuery);
        </script>
    <?php
    }
}
