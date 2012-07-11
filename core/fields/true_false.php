<?php

class cfs_True_false extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'true_false';
        $this->label = __('True / False', 'cfs');
        $this->parent = $parent;
    }

    function html($field)
    {
        $field->value = (0 < (int) $field->value) ? 1 : 0;
    ?>
        <span class="checkbox<?php echo $field->value ? ' active' : ''; ?>"></span>
        <span><?php echo $field->options['message']; ?></span>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Message', 'cfs'); ?></label>
                <p class="description"><?php _e('The text beside the checkbox', 'cfs'); ?></p>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][message]",
                        'input_class' => '',
                        'value' => $this->get_option($field, 'message'),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function input_head()
    {
    ?>
        <script type="text/javascript">
        (function($) {
            $(function() {
                $('.cfs_add_field').click(function() {
                    $('.cfs_true_false:not(.ready)').init_true_false();
                });
                $('.cfs_true_false').init_true_false();
            });

            $.fn.init_true_false = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // handle click
                    $this.find('span.checkbox').click(function() {
                        var val = $(this).hasClass('active') ? 0 : 1;
                        $(this).siblings('.true_false').val(val);
                        $(this).toggleClass('active');
                    });
                });
            }
        })(jQuery);
        </script>
    <?php
    }

    function format_value_for_api($value, $field)
    {
        return (0 < (int) $value[0]) ? 1 : 0;
    }
}
