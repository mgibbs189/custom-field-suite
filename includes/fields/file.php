<?php

class cfs_file extends cfs_field
{

    public $new_media;




    function __construct()
    {
        $this->name = 'file';
        $this->label = __('File Upload', 'cfs');
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

        // CSS logic for "Add" / "Remove" buttons
        $css = empty($field->value) ? array('', ' hidden') : array(' hidden', '');
    ?>
        <span class="file_url"><?php echo $file_url; ?></span>
        <input type="button" class="media button add<?php echo $css[0]; ?>" value="<?php _e('Add File', 'cfs'); ?>" />
        <input type="button" class="media button remove<?php echo $css[1]; ?>" value="<?php _e('Remove', 'cfs'); ?>" />
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
                    CFS()->create_field(array(
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
                    CFS()->create_field(array(
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




    /**
     * @deprecated for WP < 3.5
     */
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




    /**
     * @deprecated for WP < 3.5
     */
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
        wp_enqueue_media();
    ?>
        <script>
        (function($) {
            $(function() {

                var cfs_media_frame;

                $(document).on('click', '.cfs_input .media.button.add', function(e) {
                    $this = $(this);

                    if (cfs_media_frame) {
                        cfs_media_frame.open();
                        return;
                    }

                    cfs_media_frame = wp.media.frames.cfs_media_frame = wp.media({
                        multiple: false
                    });

                    cfs_media_frame.on('select', function() {
                        var attachment = cfs_media_frame.state().get('selection').first().toJSON();
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

                    cfs_media_frame.open();
                    cfs_media_frame.content.mode('upload');
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




    function format_value_for_api($value, $field = null)
    {
        if (ctype_digit($value))
        {
            $return_value = $this->get_option($field, 'return_value', 'url');
            return ('id' == $return_value) ? (int) $value : wp_get_attachment_url($value);
        }
        return $value;
    }
}
