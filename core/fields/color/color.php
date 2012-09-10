<?php

class cfs_Color extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'color';
        $this->label = __('Color', 'cfs');
        $this->parent = $parent;

        // Include necessary scripts
        if (in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
        {
            wp_register_script('miniColors', $this->parent->url . '/core/fields/color/jquery.miniColors.min.js', array('jquery'));
            wp_enqueue_script('miniColors');
        }
    }

    function input_head($field = null)
    {
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/core/fields/color/color.css" />
        <script>
        (function($) {
            $('.cfs_color input.color').live('focus', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').miniColors({ letterCase: 'lowercase' });
                }
            });
        })(jQuery);
        </script>
    <?php
    }
}
