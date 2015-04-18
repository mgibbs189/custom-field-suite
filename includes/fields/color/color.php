<?php

class cfs_color extends cfs_field
{

    function __construct() {
        $this->name = 'color';
        $this->label = __( 'Color', 'cfs' );
    }


    function input_head( $field = null ) {
        wp_enqueue_style( 'wp-color-picker' ); 
        wp_enqueue_script( 'wp-color-picker' );
    ?>
        <script>
        (function($) {
            $(function() {
                $('.cfs_color input.color').wpColorPicker();
            });
        })(jQuery);
        </script>
    <?php
    }
}
