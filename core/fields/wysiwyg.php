<?php

class cfs_Wysiwyg extends cfs_Field
{
    
    static $in_filter = false;

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

    function input_head()
    {
        // Make sure the user has WYSIWYG enabled
        if ('true' == get_user_meta(get_current_user_id(), 'rich_editing', true))
        {
    ?>
        <script type="text/javascript">
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
        $output = $value[ 0 ];
        
        if ( !self::$in_filter ) {
            self::$in_filter = true;
            $output = apply_filters( 'the_content', $output );
            self::$in_filter = false;
        }
        
        return $output;
    }
}
