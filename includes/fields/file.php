<?php

class cfs_file extends cfs_field
{

    function __construct() {
        $this->name = 'file';
        $this->label = __( 'File Upload', 'cfs' );
    }


    function html( $field ) {
        $file_url = $field->value;

        if ( ctype_digit( $field->value ) ) {
            if ( wp_attachment_is_image( $field->value ) ) {
                $file_url = wp_get_attachment_image_src( $field->value );
                $file_url = '<img src="' . $file_url[0] . '" />';
            }
            else
            {
                $file_url = wp_get_attachment_url( $field->value );
                $filename = substr( $file_url, strrpos( $file_url, '/' ) + 1 );
                $file_url = '<a href="'. $file_url .'" target="_blank">'. $filename .'</a>';
            }
        }

        // CSS logic for "Add" / "Remove" buttons
        $css = empty( $field->value ) ? array( '', ' hidden' ) : array( ' hidden', '' );
    ?>
        <span class="file_url"><?php echo $file_url; ?></span>
        <input type="button" class="media button add<?php echo $css[0]; ?>" value="<?php _e( 'Add File', 'cfs' ); ?>" />
        <input type="button" class="media button remove<?php echo $css[1]; ?>" value="<?php _e( 'Remove', 'cfs' ); ?>" />
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="file_value" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'File Type', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][file_type]",
                        'options' => array(
                            'choices' => array(
                                'file'  => __( 'Any', 'cfs' ),
                                'image' => __( 'Image', 'cfs' ),
                                'audio' => __( 'Audio', 'cfs' ),
                                'video' => __( 'Video', 'cfs' )
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option( $field, 'file_type', 'file' ),
                    ) );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Return Value', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][return_value]",
                        'options' => array(
                            'choices' => array(
                                'url' => __( 'File URL', 'cfs' ),
                                'id' => __( 'Attachment ID', 'cfs' )
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option( $field, 'return_value', 'url' ),
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
                    ) );
                ?>
            </td>
        </tr>
    <?php
    }


    function input_head( $field = null ) {
        wp_enqueue_media();
    ?>
        <style>
        .cfs_frame .media-frame-menu {
            display: none;
        }
        
        .cfs_frame .media-frame-title,
        .cfs_frame .media-frame-router,
        .cfs_frame .media-frame-content,
        .cfs_frame .media-frame-toolbar {
            left: 0;
        }
        </style>

        <script>
        (function($) {
            $(function() {

                var cfs_frame;

                $(document).on('click', '.cfs_input .media.button.add', function(e) {
                    $this = $(this);

                    if (cfs_frame) {
                        cfs_frame.open();
                        return;
                    }

                    cfs_frame = wp.media.frames.cfs_frame = wp.media({
                        className: 'media-frame cfs_frame',
                        frame: 'post',
                        multiple: false,
                        library: {
                            type: 'image'
                        }
                    });

                    cfs_frame.on('insert', function() {
                        var attachment = cfs_frame.state().get('selection').first().toJSON();
                        if ('image' == attachment.type && 'undefined' != typeof attachment.sizes) {
                            file_url = attachment.sizes.full.url;
                            if ('undefined' != typeof attachment.sizes.thumbnail) {
                                file_url = attachment.sizes.thumbnail.url;
                            }
                            file_url = '<img src="' + file_url + '" />';
                        }
                        else {
                            file_url = '<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>';
                        }
                        $this.hide();
                        $this.siblings('.media.button.remove').show();
                        $this.siblings('.file_value').val(attachment.id);
                        $this.siblings('.file_url').html(file_url);
                    });

                    cfs_frame.open();
                    cfs_frame.content.mode('upload');
                });

                $(document).on('click', '.cfs_input .media.button.remove', function() {
                    $(this).siblings('.file_url').html('');
                    $(this).siblings('.file_value').val('');
                    $(this).siblings('.media.button.add').show();
                    $(this).hide();
                });
            });
        })(jQuery);
        </script>
    <?php
    }


    function format_value_for_api( $value, $field = null ) {
        if ( ctype_digit( $value ) ) {
            $return_value = $this->get_option( $field, 'return_value', 'url' );
            return ( 'id' == $return_value ) ? (int) $value : wp_get_attachment_url( $value );
        }
        return $value;
    }
}
