<?php

class cfs_date extends cfs_field
{

    function __construct() {
        $this->name = 'date';
        $this->label = __( 'Date', 'cfs' );
    }

    function options_html( $key, $field = null ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Output format', 'cfs'); ?></label>
                <p class="description"><?php _e( 'Output format for date.', 'cfs' ); ?></p>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][format]",
                        'value' => $this->get_option( $field, 'format' ),
                    ));
                ?>
                <p class="description">
					<?php _e( 'If you include time format like "YYYY-MM-DD HH:mm", timepicker feature is enabled. Default format is YYYY-MM-DD', 'cfs' ); ?><br>
					<?php _e("See momentjs\' docs for valid formats.",'cfs'); ?><br>
					Doc: <a href="http://momentjs.com/docs/#/displaying/format/" target="_blank">Format</a>
				</p>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Validation', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'required' ),
                        'options' => array( 'message' => __( 'This is a required field', 'cfs' ) ),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function input_head( $field = null ) {
        $this->load_assets();
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/includes/fields/date/datepicker.css" />
    <?php
    }

    function html( $field ) {
        $format = $this->get_option( $field, 'format' );
        if(!$format) $format = 'YYYY-MM-DD';
    ?>
        <input type="text" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
        <script>
        (function($) {
            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_date:not(.ready)').init_date();
                });
                $('[name="<?php echo $field->input_name; ?>"]').datetimepicker({
                    format: '<?php echo esc_attr($format); ?>',
                    showClear: true,
                    //debug: true,
                }).addClass('ready');
            });

        })(jQuery);
        </script>
    <?php
    }


    function load_assets() {
        wp_register_script( 'bootstrap-datepicker-moment', CFS_URL . '/includes/fields/date/moment-with-locales.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'bootstrap-datepicker-moment' );
        wp_register_script( 'bootstrap-datepicker', CFS_URL . '/includes/fields/date/bootstrap-datetimepicker.js', array( 'jquery' ) );
        wp_enqueue_script( 'bootstrap-datepicker' );
    }
}
