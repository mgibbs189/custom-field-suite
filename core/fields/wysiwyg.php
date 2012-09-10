<?php

class cfs_Wysiwyg extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'wysiwyg';
        $this->label = __('Wysiwyg Editor', 'cfs');
        $this->parent = $parent;

        add_filter('wp_default_editor', array($this, 'wp_default_editor'));
    }

    function html($field)
    {
    ?>
        <textarea name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" rows="4"><?php echo $field->value; ?></textarea>
    <?php
    }

    function options_html($key, $field)
    {
        $default_text = __('Default', 'cfs');
        $none_text = __('None (bypass filters)', 'cfs');
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Formatting', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field(array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][formatting]",
                        'options' => array('choices' => "default : $default_text\nnone : $none_text"),
                        'input_class' => '',
                        'value' => $this->get_option($field, 'formatting', 'default'),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function input_head()
    {
        // Make sure the user has WYSIWYG enabled
        if ('true' == get_user_meta(get_current_user_id(), 'rich_editing', true))
        {
    ?>
        <script>
        (function($) {
            var wysiwyg_count = 0;

            $(function() {
                $('.cfs_add_field').bind('go', function() {
                    $('.cfs_wysiwyg:not(.ready)').init_wysiwyg();
                });
                $('.cfs_wysiwyg').init_wysiwyg();
            });

            $.fn.init_wysiwyg = function() {
                this.each(function() {
                    $(this).addClass('ready');

                    // generate css id
                    wysiwyg_count = wysiwyg_count + 1;
                    var input_id = 'cfs_wysiwyg_' + wysiwyg_count;
                    $(this).find('.wysiwyg').attr('id', input_id);

                    // create wysiwyg
                    tinyMCE.settings.theme_advanced_buttons2 += ',code';
                    tinyMCE.execCommand('mceAddControl', false, input_id);
                });
            };
        })(jQuery);
        </script>
    <?php
        }
    }

    function wp_default_editor()
    {
        return 'tinymce'; // html or tinymce
    }

    function format_value_for_input($value, $field)
    {
        return wp_richedit_pre($value[0]);
    }

    function format_value_for_api($value, $field)
    {
        $formatting = $this->get_option($field, 'formatting', 'default');
        return ('none' == $formatting[0]) ? $value[0] : apply_filters('the_content', $value[0]);
    }
}
