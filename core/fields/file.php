<?php

class cfs_file extends cfs_field
{

    static $modal;

    function __construct($parent)
    {
        global $post;

        $this->name = 'file';
        $this->label = __('File Upload', 'cfs');
        $this->parent = $parent;
        $this->modal = version_compare( get_bloginfo( 'version' ), '3.5', '>=' );

        if( $this->modal )
        {
            add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
            add_filter('media_send_to_editor', array($this, 'media_send_to_editor'), 20, 3);
        }
        else
        {
            $post_id = isset( $post->ID ) ? $post->ID : null;
            wp_enqueue_media( array( 'post' => $post_id ) );
        }

    }

    function html($field)
    {
        $file_url = $field->value;

        if (ctype_digit($field->value))
        {
            if (wp_attachment_is_image($field->value))
            {
                $file_url = wp_get_attachment_image_src($field->value);
                $file_url = '<img src="' . $file_url[0] . '" />';
            }
            else
            {
                $file_url = wp_get_attachment_url($field->value);
                $filename = substr($file_url, strrpos($file_url, '/') + 1);
                $file_url = '<a href="'. $file_url .'" target="_blank">'. $filename .'</a>';
            }
        }

        if (empty($field->value))
        {
            $css_hide = array('add' => '', 'remove' => ' hidden');
        }
        else
        {
            $css_hide = array('add' => ' hidden', 'remove' => '');
        }
    ?>
        <span class="file_url"><?php echo $file_url; ?></span>
        <input type="button" class="media button add<?php echo $css_hide['add']; ?>" value="<?php _e('Add File', 'cfs'); ?>" />
        <input type="button" class="media button remove<?php echo $css_hide['remove']; ?>" value="<?php _e('Remove', 'cfs'); ?>" />
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="file_value" value="<?php echo $field->value; ?>" />
    <?php
    }

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Return Value', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field(array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][return_value]",
                        'options' => array(
                            'choices' => array(
                                'url' => __('File URL', 'cfs'),
                                'id' => __('Attachment ID', 'cfs')
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option($field, 'return_value', 'url'),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Validation', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'required'),
                        'options' => array('message' => __('This is a required field', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function popup_head()
    {
        // Don't interfere with the default Media popup
        if (isset($_GET['cfs_file']))
        {
            // Ensure that "Insert into Post" appears
            $post_type = get_post_type($_GET['post_id']);
            add_post_type_support($post_type, 'editor');
    ?>
        <script>
        (function($) {
            $(function() {
                $('form#filter').each(function() {
                    $(this).append('<input type="hidden" name="cfs_file" value="1" />');
                });

                // Hide the "From URL" tab
                $('#media-upload-header li#tab-type_url').hide();

                $('#media-items').bind('DOMNodeInserted', function() {
                    var $this = $(this);
                    $this.find('tr.image_alt').hide();
                    $this.find('tr.post_excerpt').hide();
                    $this.find('tr.url').hide();
                    $this.find('tr.align').hide();
                    $this.find('tr.image-size').hide();
                    $this.find('tr.submit input.button').val('<?php _e('Use This File', 'cfs'); ?>');
                }).trigger('DOMNodeInserted');
            });
        })(jQuery);
        </script>
    <?php
        }
    }

    function media_send_to_editor($html, $id, $attachment)
    {
        if (isset($_POST['_wp_http_referer']))
        {
            parse_str($_POST['_wp_http_referer'], $postdata);
        }

        if (isset($postdata['cfs_file']))
        {
            if (wp_attachment_is_image($id))
            {
                $file_url = wp_get_attachment_image_src($id);
                $file_url = '<img src="' . $file_url[0] . '" />';
            }
            else
            {
                $file_url = wp_get_attachment_url($id);
                $filename = substr($file_url, strrpos($file_url, '/') + 1);
                $file_url = '<a href="'. $file_url .'" target="_blank">'. $filename .'</a>';
            }
    ?>
        <script>
        self.parent.cfs_div.hide();
        self.parent.cfs_div.siblings('.media.button.remove').show();
        self.parent.cfs_div.siblings('.file_url').html('<?php echo $file_url; ?>');
        self.parent.cfs_div.siblings('.file_value').val('<?php echo $id; ?>');
        self.parent.cfs_div = null;
        self.parent.tb_remove();
        </script>
    <?php
            exit;
        }
        else
        {
            return $html;
        }
    }

    function input_head($field = null)
    {
        global $post;
    ?>
        <script>
        (function($) {
            $(function() {
                <?php if( !$this->modal ) : ?>
                    $(document).on('click', '.cfs_input .media.button.add', function() {
                        window.cfs_div = $(this);
                        tb_show('<?php _e('Attach file', 'cfs'); ?>', 'media-upload.php?post_id=<?php echo $post->ID; ?>&cfs_file=1&TB_iframe=1&width=640&height=480');
                        return false;
                    });
                <?php else : ?>
                    var $element     = $('input[name="cfs[input][<?php echo $field->id; ?>][value]"]').parent(),
                        title        = '<?php echo esc_attr( __( $field->label, "cfs" ) ); ?>',
                        button       = '<?php echo esc_attr( __( "Select", "cfs" ) ); ?>',
                        frame;

                    $element.on( 'click', '.media.button.add', function( event ) {
                        var options, attachment;

                        event.preventDefault();

                        // if the frame already exists, open it
                        if ( frame ) {
                            frame.open();
                            return;
                        }

                        // set our settings
                        frame = wp.media({
                            title: title,
                            button: {
                                text: button
                            }
                        });

                        // set up our select handler
                        frame.on( 'select', function() {
                            selection = frame.state().get('selection');
                            if ( ! selection )
                                return;

                            selection.each( function( attachment ) {

                                // by default we'll assume it's a file URL
                                attachment_html = attachment.attributes.url;

                                // but we'll check to see if we can use a thumbnail instead
                                if(typeof attachment.attributes.sizes !== 'undefined'){
                                    if(typeof attachment.attributes.sizes.thumbnail !== 'undefined'){
                                        if(typeof attachment.attributes.sizes.thumbnail.url !== 'undefined'){
                                            // use the thumbnail
                                            attachment_html = '<img src="'+attachment.attributes.sizes.thumbnail.url+'"/>';
                                        }
                                    }
                                }

                                $element.find('.media.button.add').hide();
                                $element.find('.media.button.remove').show();
                                $element.find('.file_url').html(attachment_html);
                                $element.find('.file_value').val(attachment.id);
                            });
                        });

                        // open the frame
                        frame.open();

                    });
                <?php endif; ?>
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

    function format_value_for_api($value, $field)
    {
        if (ctype_digit($value))
        {
            $return_value = $this->get_option($field, 'return_value', 'url');
            return ('id' == $return_value) ? (int) $value : wp_get_attachment_url($value);
        }
        return $value;
    }
}
