<?php

class cfs_File extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'file';
        $this->label = __('File Upload', 'cfs');
        $this->parent = $parent;

        add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
        add_filter('media_send_to_editor', array($this, 'media_send_to_editor'), 20, 3);
    }

    function html($field)
    {
        $file_url = $field->value;

        if (ctype_digit($field->value))
        {
            if (wp_attachment_is_image($field->value))
            {
                $file_url = wp_get_attachment_image($field->value);
            }
            else
            {
                $file_url = wp_get_attachment_url($field->value);
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
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
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
                    $this->parent->create_field((object) array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][return_value]",
                        'options' => array('choices' => "url : File URL\nid : Attachment ID"),
                        'input_class' => '',
                        'value' => $this->get_option($field, 'return_value', 'url'),
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
        <script type="text/javascript">
        (function($) {
            $(function() {
                $('form#filter').each(function() {
                    $(this).append('<input type="hidden" name="cfs_file" value="1" />');
                });

                $('#media-items').bind('DOMNodeInserted', function() {
                    var $this = $(this);
                    $this.find('tr.image_alt').hide();
                    $this.find('tr.post_excerpt').hide();
                    $this.find('tr.url').hide();
                    $this.find('tr.align').hide();
                    $this.find('tr.image-size').hide();
                    $this.find('tr.submit input.button').val('<?php _e('Attach File', 'cfs'); ?>');
                }).trigger('DOMNodeInserted');
            });
        })(jQuery);
        </script>
    <?php
        }
    }

    function media_send_to_editor($html, $id, $attachment)
    {
        parse_str($_POST['_wp_http_referer'], $postdata);

        if (isset($postdata['cfs_file']))
        {
            $file_url = wp_attachment_is_image($id) ? wp_get_attachment_image($id) : $attachment['url'];
    ?>
        <script type="text/javascript">
        self.parent.cfs_div.hide();
        self.parent.cfs_div.siblings('.media.button.remove').show();
        self.parent.cfs_div.siblings('.file_url').html('<?php echo $file_url; ?>');
        self.parent.cfs_div.siblings('.file').val('<?php echo $id; ?>');
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
        <script type="text/javascript">
        (function($) {
            $(function() {
                $('.cfs_input .media.button.add').live('click', function() {
                    window.cfs_div = $(this);
                    tb_show('Attach file', 'media-upload.php?post_id=<?php echo $post->ID; ?>&cfs_file=1&TB_iframe=1&width=640&height=480');
                    return false;
                });
                $('.cfs_input .media.button.remove').live('click', function() {
                    $(this).siblings('.file_url').html('');
                    $(this).siblings('.file').val('');
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
        if (ctype_digit($value[0]))
        {
            $return_value = $this->get_option($field, 'return_value', 'url');
            return ('id' == $return_value[0]) ? (int) $value[0] : wp_get_attachment_url($value[0]);
        }
        return $value[0];
    }
}
