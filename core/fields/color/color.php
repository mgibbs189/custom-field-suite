<?php

class cfs_color extends cfs_field
{

    function __construct($parent)
    {
        $this->name = 'color';
        $this->label = __('Color', 'cfs');
        $this->parent = $parent;
    }

    function input_head()
    {
        $this->load_assets();
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/core/fields/color/color.css" />
        <script>
        (function($) {
            $(document).on('focus', '.cfs_color input.color', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').miniColors({ letterCase: 'lowercase' });
                }
            });
        })(jQuery);
        </script>
    <?php
    }

    function load_assets()
    {
        wp_register_script('miniColors', $this->parent->url . '/core/fields/color/jquery.miniColors.min.js');
        wp_enqueue_script('miniColors');
    }
}
