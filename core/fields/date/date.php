<?php

class cfs_Date extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'date';
        $this->label = __('Date', 'cfs');
        $this->parent = $parent;
    }

    function input_head($field = null)
    {
    ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/core/fields/date/date.css" />
        <script type="text/javascript" src="<?php echo $this->parent->url; ?>/core/fields/date/jquery.ui.custom.js"></script>
        <script type="text/javascript" src="<?php echo $this->parent->url; ?>/core/fields/date/jquery.ui.timepicker.js"></script>
        <script type="text/javascript">
        (function($) {
            $('.cfs_date input.date').live('focus', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').datetimepicker({ stepMinute: 5, dateFormat: 'yy-mm-dd' });
                }
            });
        })(jQuery);
        </script>
    <?php
    }
}
