<?php

class cfs_color extends cfs_field
{

    function __construct() {
        $this->name = 'color';
        $this->label = __( 'Color', 'cfs' );
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Default Value', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][default_value]",
                        'value' => $this->get_option( $field, 'default_value' ),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }


    function input_head( $field = null ) {
        wp_register_script( 'miniColors', CFS_URL . '/includes/fields/color/jquery.miniColors.min.js' );
        wp_enqueue_script( 'miniColors' );
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/includes/fields/color/color.css" />
        <script>
        (function($) {
            $(document).on('focus', '.cfs_color input.color', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').minicolors();
                }
            });

            $(function() {
                $('.cfs_color input.color').addClass('ready').minicolors();
            });
        })(jQuery);
        </script>
    <?php
    }
}
