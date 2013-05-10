<?php

class cfs_date extends cfs_field
{

    function __construct($parent)
    {
        $this->name = 'date';
        $this->label = __('Date', 'cfs');
        $this->parent = $parent;
    }




    function input_head()
    {
        $this->load_assets();
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/includes/fields/date/date.css" />
        <script>
        (function($) {
            $(document).on('focus', '.cfs_date input.date', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').pikaday();
                }
            });
        })(jQuery);
        </script>
    <?php
    }




    function load_assets()
    {
        wp_register_script('moment', $this->parent->url . '/includes/fields/date/moment.js');
        wp_register_script('pikaday', $this->parent->url . '/includes/fields/date/pikaday.js', array('jquery'));
        wp_enqueue_script('moment');
        wp_enqueue_script('pikaday');
    }
}
