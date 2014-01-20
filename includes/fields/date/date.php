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
        <link rel="stylesheet" type="text/css" href="<?php echo $this->parent->url; ?>/includes/fields/date/datepicker.css" />
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
                    $(this).find('input.date').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                        clearBtn: true
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
        wp_register_script('bootstrap-datepicker', $this->parent->url . '/includes/fields/date/bootstrap-datepicker.js', array('jquery'));
        wp_enqueue_script('bootstrap-datepicker');
    }
}
