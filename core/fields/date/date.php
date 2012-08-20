<?php

class cfs_Date extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'date';
        $this->label = __('Date', 'cfs');
        $this->parent = $parent;

        // Include necessary scripts
        if (in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
        {
            wp_register_script('jquery-ui-timepicker', $this->parent->url . '/core/fields/date/jquery.ui.timepicker.js',
                array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider')
            );

            wp_enqueue_script('jquery-ui-timepicker');
        }
    }

    function input_head($field = null)
    {
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/core/fields/date/date.css" />
        <script type="text/javascript">
        (function($) {
            $('.cfs_date input.date').live('focus', function() {
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
}
