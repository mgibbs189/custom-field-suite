<?php

class cfs_date extends cfs_field
{

    function __construct($parent)
    {
        $this->name = 'date';
        $this->label = __('Date', 'cfs');
        $this->parent = $parent;
    }




    function input_head($field = null)
    {
        $this->load_assets();
    ?>
	    <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/includes/fields/date/bootstrap/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/includes/fields/date/bootstrap-datetimepicker.min.css" />
        <script>
        (function($) {
            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_date:not(.ready)').init_date();
                });
                $('.cfs_date').init_date();
            });

            $.fn.init_date = function() {
                this.each(function() {
                    //$(this).find('input.date').datetime();
                    $(this).find('input.date').datetimepicker({
						format: 'YYYY-MM-DD HH:mm'
                    });
                    $(this).addClass('ready');
                });
            };
        })(jQuery);
        </script>
    <?php
    }




    function load_assets()
    {
        wp_register_script('bootstrap', $this->parent->url . '/includes/fields/date/bootstrap/js/bootstrap.min.js', array('jquery'));
        wp_enqueue_script('bootstrap');
        wp_register_script('moment', $this->parent->url . '/includes/fields/date/moment.min.js', array('jquery'));
        wp_enqueue_script('moment');
        wp_register_script('bootstrap-datepicker', $this->parent->url . '/includes/fields/date/bootstrap-datetimepicker.min.js', array('jquery'));
        wp_enqueue_script('bootstrap-datepicker');

    }
}
