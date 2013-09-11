<?php

class cfs_wysiwyg extends cfs_field
{

    public $wp_default_editor;




    function __construct($parent)
    {
        $this->name = 'wysiwyg';
        $this->label = __('Wysiwyg Editor', 'cfs');
        $this->parent = $parent;

        // wp_editor() won't work for dynamic-generated wysiwygs
        add_filter('wp_default_editor', array($this, 'wp_default_editor'));

        // force HTML mode for main content editor
        add_action('tiny_mce_before_init', array($this, 'editor_pre_init'));
    }




    function html($field)
    {
    ?>
        <div class="wp-editor-wrap">
            <div class="wp-media-buttons">
                <?php do_action('media_buttons'); ?>
            </div>
            <div class="wp-editor-container">
                <textarea name="<?php echo $field->input_name; ?>" class="wp-editor-area <?php echo $field->input_class; ?>" rows="4"><?php echo $field->value; ?></textarea>
            </div>
        </div>
    <?php
    }




    function options_html($key, $field)
    {
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
                        'options' => array(
                            'choices' => array(
                                'default' => __('Default', 'cfs'),
                                'none' => __('None (bypass filters)', 'cfs')
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option($field, 'formatting', 'default'),
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




    function input_head($field = null)
    {
        // make sure the user has WYSIWYG enabled
        if ('true' == get_user_meta(get_current_user_id(), 'rich_editing', true))
        {
            if (!is_admin())
            {
                // load TinyMCE for front-end forms
                echo '<div class="hidden">';
                wp_editor('', 'cfswysi');
                echo '</div>';
            }
    ?>
        <script>
        (function($) {

            var wpautop;
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

                    // WP 3.5+
                    $(this).find('a.add_media').attr('data-editor', input_id);

                    // create wysiwyg
                    wpautop = tinyMCE.settings.wpautop;

                    tinyMCE.settings.wpautop = false;
                    tinyMCE.settings.theme_advanced_buttons2 += ',code';
                    tinyMCE.execCommand('mceAddControl', false, input_id);
                    tinyMCE.settings.wpautop = wpautop;
                });
            };

            $(document).on('cfs/sortable_start', function(event, ui) {
                tinyMCE.settings.wpautop = false;
                $(ui).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceRemoveControl', false, $(this).attr('id'));
                });
            });

            $(document).on('cfs/sortable_stop', function(event, ui) {
                $(ui).find('.wysiwyg').each(function() {
                    tinyMCE.execCommand('mceAddControl', false, $(this).attr('id'));
                });
                tinyMCE.settings.wpautop = wpautop;
            });
        })(jQuery);
        </script>
    <?php
        }
    }




    function wp_default_editor($default)
    {
        $this->wp_default_editor = $default;

        return 'tinymce'; // html or tinymce
    }




    function editor_pre_init($settings)
    {
        if ('html' == $this->wp_default_editor)
        {
            $settings['oninit'] = "function() { switchEditors.go('content', 'html'); }";
        }

        return $settings;
    }




    function format_value_for_input($value, $field = null)
    {
        return wp_richedit_pre($value);
    }




    function format_value_for_api($value, $field = null)
    {
        $formatting = $this->get_option($field, 'formatting', 'default');
        return ('none' == $formatting) ? $value : apply_filters('the_content', $value);
    }
}
