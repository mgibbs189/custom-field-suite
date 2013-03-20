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
                    $(this).addClass('ready').datetimepicker({ stepMinute: 5, dateFormat: 'yy-mm-dd' });
                    if ($('.cfs-ui-date').length < 1) {
                        $('#ui-datepicker-div').wrap('<div class="cfs-ui-date" />');
                    }
                }
            });
        })(jQuery);
        </script>
    <?php
    }

    function load_assets()
    {
        wp_register_script('jquery-ui-timepicker', $this->parent->url . '/includes/fields/date/jquery.ui.timepicker.js',
            array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider')
        );

        wp_enqueue_script('jquery-ui-timepicker');
    }
}
