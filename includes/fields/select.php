<?php

class cfs_select extends cfs_field
{

    function __construct()
    {
        $this->name = 'select';
        $this->label = __('Select', 'cfs');
    }




    function html($field)
    {
        $multiple = '';

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

        // Select2
        if (isset($field->options['select2']) && '1' == $field->options['select2'])
        {
            if (empty($field->input_class))
            {
                $field->input_class = 'select2';
            }
            else
            {
                $field->input_class .= ' select2';
            }
        }

        // Select boxes should return arrays (unless "force_single" is true)
        if ('[]' != substr($field->input_name, -2) && empty($field->options['force_single']))
        {
            $field->input_name .= '[]';
        }
    ?>
        <select name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>"<?php echo $multiple; ?>>
        <?php foreach ($field->options['choices'] as $val => $label) : ?>
            <?php $val = ('{empty}' == $val) ? '' : $val; ?>
            <?php $selected = in_array($val, (array) $field->value) ? ' selected' : ''; ?>
            <option value="<?php echo esc_attr($val); ?>"<?php echo $selected; ?>><?php echo esc_attr($label); ?></option>
        <?php endforeach; ?>
        </select>
    <?php
    }




    function input_head($field = null)
    {
    ?>
        <script src="<?php echo CFS_URL; ?>/assets/js/select2/select2.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/assets/js/select2/select2.css" />
        <script>
        (function($) {
            $(function() {
                $('.select2').select2();

                $(document).on('cfs/ready', '.cfs_add_field', function() {
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
        // Convert choices to textarea-friendly format
        if (isset($field->options['choices']) && is_array($field->options['choices']))
        {
            foreach ($field->options['choices'] as $choice_key => $choice_val)
            {
                $field->options['choices'][$choice_key] = "$choice_key : $choice_val";
            }
            $field->options['choices'] = implode("\n", $field->options['choices']);
        }
        else
        {
            $field->options['choices'] = '';
        }
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Choices', 'cfs'); ?></label>
                <p class="description"><?php _e('Enter one choice per line', 'cfs'); ?></p>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'textarea',
                        'input_name' => "cfs[fields][$key][options][choices]",
                        'value' => $this->get_option($field, 'choices'),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Multi-select?', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][multiple]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'multiple'),
                        'options' => array('message' => __('This is a multi-select field', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Select2', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][select2]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'select2'),
                        'options' => array('message' => __('Render this field with Select2', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Validation', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'required'),
                        'options' => array('message' => __('This is a required field', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }




    function format_value_for_api( $value, $field = null ) {
        $value_array = array();
        $choices = $field->options['choices'];

        // Return an associative array (value, label)
        if ( is_array( $value ) ) {
            foreach ( $value as $val ) {
                $value_array[ $val ] = isset( $choices[ $val ] ) ? $choices[ $val ] : $val;
            }
        }

        return $value_array;
    }




    function prepare_value($value, $field = null)
    {
        return $value;
    }




    function pre_save_field($field)
    {
        $new_choices = array();
        $choices = trim($field['options']['choices']);

        if (!empty($choices))
        {
            $choices = str_replace("\r\n", "\n", $choices);
            $choices = str_replace("\r", "\n", $choices);
            $choices = (false !== strpos($choices, "\n")) ? explode("\n", $choices) : (array) $choices;

            foreach ($choices as $choice)
            {
                $choice = trim($choice);
                if (false !== ($pos = strpos($choice, ' : ')))
                {
                    $array_key = substr($choice, 0, $pos);
                    $array_value = substr($choice, $pos + 3);
                    $new_choices[$array_key] = $array_value;
                }
                else
                {
                    $new_choices[$choice] = $choice;
                }
            }
        }

        $field['options']['choices'] = $new_choices;

        return $field;
    }
}
