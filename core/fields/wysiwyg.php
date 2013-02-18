<?php

class cfs_wysiwyg extends cfs_field
{

    function __construct($parent)
    {
        $this->name = 'wysiwyg';
        $this->label = __('Wysiwyg Editor', 'cfs');
        $this->parent = $parent;

        if (version_compare(get_bloginfo('version'), '3.5', '<'))
        {
            add_filter('wp_default_editor', array($this, 'wp_default_editor'));
        }
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
    <?php
    }

    function input_head()
    {
        // Make sure the user has WYSIWYG enabled
        if ('true' == get_user_meta(get_current_user_id(), 'rich_editing', true))
        {
            // Load TinyMCE for front-end forms
            echo '<div class="hidden">';
            wp_editor('', 'cfswysi');
            echo '</div>';
    ?>
        <script>
        (function($) {
            var wysiwyg_count = 0;

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_wysiwyg:not(.ready)').init_wysiwyg();
                });
                $('.cfs_wysiwyg').init_wysiwyg();

                // TinyMCE hates hidden containers
                $(document).on('click', '.cfs_loop_head', function() {
                    $(this).siblings('.cfs_loop_body.open').find('.wysiwyg').each(function() {
                        var id = $(this).attr('id');
                        tinyMCE.execCommand('mceRemoveControl', false, id);
                        tinyMCE.execCommand('mceAddControl', false, id);
                    });
                });

                // Set the active editor
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
                    $(this).find('.wysiwyg').attr('id', input_id);

                    // WP 3.5+
                    $(this).find('a.add_media').attr('data-editor', input_id);

                    // create wysiwyg
                    tinyMCE.settings.theme_advanced_buttons2 += ',code';
                    tinyMCE.execCommand('mceAddControl', false, input_id);
                });
            };

            $(document).on('cfs/sortable_start', function(event, ui) {
                $(ui).find('.wysiwyg').each(function() {
                    var id = $(this).attr('id');
                    tinyMCE.execCommand('mceRemoveControl', false, id);
                });
            });

            $(document).on('cfs/sortable_stop', function(event, ui) {
                $(ui).find('.wysiwyg').each(function() {
                    var id = $(this).attr('id');
                    tinyMCE.execCommand('mceAddControl', false, id);
                });
            });
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
        return wp_richedit_pre($value);
    }

    function format_value_for_api($value, $field)
    {
        $formatting = $this->get_option($field, 'formatting', 'default');
        return ('none' == $formatting) ? $value : apply_filters('the_content', $value);
    }
}
