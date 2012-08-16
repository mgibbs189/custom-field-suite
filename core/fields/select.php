<?php

class cfs_Select extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'select';
        $this->label = __('Select', 'cfs');
        $this->parent = $parent;
    }

    function html($field)
    {
        $multiple = '';
        $choices = array();

        // Handle choice labels (value : label)
        $temp = explode("\n", $field->options['choices']);

        foreach ($temp as $val)
        {
            // Remove all newlines and whitespace
            $val = trim($val);

            if (false !== strpos($val, ' : '))
            {
                $choice = explode(' : ', $val);
                $choices[$choice[0]] = $choice[1];
            }
            else
            {
                $choices[$val] = $val;
            }
        }

        // Multi-select
        if (isset($field->options['multiple']) && '1' == $field->options['multiple'])
        {
            $multiple = ' multiple';

            if (empty($field->input_class))
            {
                $field->input_class = 'multiple';
            }
            else
            {
                $field->input_class .= ' multiple';
            }
        }
        // Single-select
        elseif (!isset($field->input_class))
        {
            $field->input_class = '';
        }

        // Force the select box to return an array
        if ('[]' != substr($field->input_name, -2))
        {
            $field->input_name .= '[]';
        }
    ?>
        <select name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>"<?php echo $multiple; ?>>
        <?php foreach ($choices as $val => $label) : ?>
            <?php $selected = in_array($val, (array) $field->value) ? ' selected' : ''; ?>
            <option value="<?php echo htmlspecialchars($val); ?>"<?php echo $selected; ?>><?php echo htmlspecialchars($label); ?></option>
        <?php endforeach; ?>
        </select>
    <?php
    }

    function input_head()
    {
    ?>
        <script type="text/javascript">
        (function($) {
            $(function() {
                $('.cfs_add_field').click(function() {
                    $('.cfs_select:not(.ready)').init_select();
                });
                $('.cfs_select').init_select();
            });

            $.fn.init_select = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');
                });
            }
        })(jQuery);
        </script>
    <?php
    }

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Choices', 'cfs'); ?></label>
                <p class="description"><?php _e('Enter one choice per line', 'cfs'); ?></p>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'textarea',
                        'input_name' => "cfs[fields][$key][options][choices]",
                        'input_class' => '',
                        'value' => $this->get_option($field, 'choices'),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Select multiple values?', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][multiple]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'multiple'),
                        'options' => array('message' => 'This a multi-select field'),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function format_value_for_input($value, $field)
    {
        return $value;
    }

    function format_value_for_api($value, $field)
    {
        return $value;
    }
}
