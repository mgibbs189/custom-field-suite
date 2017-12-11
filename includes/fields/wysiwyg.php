<?php

class cfs_wysiwyg extends cfs_field
{

    function __construct() {
        $this->name = 'wysiwyg';
        $this->label = __( 'Wysiwyg Editor', 'cfs' );

        // add the "code" button
        add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ), 20 );
    }


    function html( $field ) {
    ?>
        <div class="wp-editor-wrap">
            <div class="wp-media-buttons">
                <?php do_action( 'media_buttons' ); ?>
            </div>
            <div class="wp-editor-container">
                <textarea name="<?php echo $field->input_name; ?>" class="wp-editor-area <?php echo $field->input_class; ?>" style="height:300px"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Formatting', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][formatting]",
                        'options' => array(
                            'choices' => array(
                                'default' => __( 'Default', 'cfs' ),
                                'none' => __( 'None (bypass filters)', 'cfs' )
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option( $field, 'formatting', 'default' ),
                    ) );
                ?>
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

        // make sure the user has WYSIWYG enabled
        if ( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) {
            if ( ! is_admin() ) {
    ?>
        <div class="hidden"><?php wp_editor( '', 'cfswysi' ); ?></div>
    <?php
            }
    ?>
        <script>
        (function($) {

            var wpautop;
            var resize;
            var wysiwyg_count = 0;

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_wysiwyg:not(.ready)').init_wysiwyg();
                });
                $('.cfs_wysiwyg').init_wysiwyg();

                // set the active editor
                $(document).on('click', 'a.add_media', function() {
                    var editor_id = $(this).closest('.wp-editor-wrap').find('.wp-editor-area').attr('id');
                    wpActiveEditor = editor_id;
                });
            });

            $.fn.init_wysiwyg = function() {
                this.each(function() {
                    $(this).addClass('ready');

                    // generate css id
                    wysiwyg_count = wysiwyg_count + 1;
                    var input_id = 'cfs_wysiwyg_' + wysiwyg_count;

                    // set the wysiwyg css id
                    $(this).find('.wysiwyg').attr('id', input_id);
                    $(this).find('a.add_media').attr('data-editor', input_id);
                    
                    // if all editors on page are in 'text' tab, tinyMCE.settings will not be set
                    if ('undefined' == typeof tinyMCE.settings) {

                        // let's pull from tinyMCEPreInit for main content area (if it's set)
                        if ('undefined' != typeof tinyMCEPreInit && 'undefined' != typeof tinyMCEPreInit.mceInit.content) {
                            tinyMCE.settings = tinyMCEPreInit.mceInit.content;
                        }
                        // otherwise, setup basic settings object
                        else {
                            tinymce.settings = {
                                wpautop : true,
                                resize : 'vertical',
                                toolbar2 : 'code'
                            };  
                        }
                    }
                    
                    // add the "code" button
                    if ('undefined' !== typeof tinyMCE.settings.toolbar2) {
                        if (tinyMCE.settings.toolbar2.indexOf('code') < 0) {
                            tinyMCE.settings.toolbar2 += ',code';
                        }
                    }

                    // create wysiwyg
                    wpautop = tinyMCE.settings.wpautop;
                    resize = tinyMCE.settings.resize;

                    tinyMCE.settings.wpautop = false;
                    tinyMCE.settings.resize = 'vertical';
                    tinyMCE.execCommand('mceAddEditor', false, input_id);
                    tinyMCE.settings.wpautop = wpautop;
                    tinyMCE.settings.resize = resize;
                });
            };

            $('.meta-box-sortables, .cfs_loop').on('sortstart', function(event, ui) {
                tinyMCE.settings.wpautop = false;
                tinyMCE.settings.resize = 'vertical';
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceRemoveEditor', false, $(this).attr('id'));
                });
            });

            $('.meta-box-sortables, .cfs_loop').on('sortstop', function(event, ui) {
                $(this).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceAddEditor', false, $(this).attr('id'));
                });
                tinyMCE.settings.wpautop = wpautop;
                tinyMCE.settings.resize = resize;
            });
        })(jQuery);
        </script>
    <?php
        }
    }


    function mce_external_plugins( $plugins ) {
        if ( version_compare( get_bloginfo( 'version' ), '3.9', '>=' ) ) {
            $plugins['code'] = CFS_URL . '/assets/js/tinymce/code.min.js';
        }
        return $plugins;
    }


    function format_value_for_input( $value, $field = null ) {
        if ( version_compare( get_bloginfo( 'version' ), '4.3', '>=' ) ) {
            return format_for_editor( $value );
        }
        else {
            return wp_richedit_pre( $value );
        }
    }


    function format_value_for_api( $value, $field = null ) {
        $formatting = $this->get_option( $field, 'formatting', 'default' );
        return ( 'none' == $formatting ) ? $value : apply_filters( 'the_content', $value );
    }
}
