<?php

class cfs_field
{
    public $name;
    public $label;
    public $parent;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct($parent)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function __construct($parent)
    {
        $this->name = 'text';
        $this->label = __('Text', 'cfs');
        $this->parent = $parent;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    html($field)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function html($field)
    {
    ?>
        <input type="text" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    /*--------------------------------------------------------------------------------------
    *
    *    options_html($key, $field)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Validation', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field(array(
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


    /*--------------------------------------------------------------------------------------
    *
    *    input_head($field = null)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function input_head($field = null)
    {

    }


    /*--------------------------------------------------------------------------------------
    *
    *    prepare_value($value, $field = null)
    *
    *    Values are retrieved from the database as an array, even for field types that
    *    don't expect arrays. For field types that should return array values, make
    *    sure to override this method and return $value.
    *
    *    @author Matt Gibbs
    *    @since 1.6.9
    *
    *-------------------------------------------------------------------------------------*/

    function prepare_value($value, $field = null)
    {
        return $value[0];
    }


    /*--------------------------------------------------------------------------------------
    *
    *    format_value_for_api($value, $field)
    *
    *    This method formats the value for use with $cfs->get().
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function format_value_for_api($value, $field = null)
    {
        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    format_value_for_input($value, $field)
    *
    *    This method formats the value for use with HTML inputs.
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function format_value_for_input($value, $field = null)
    {
        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    pre_save($value, $field)
    *
    *    @author Matt Gibbs
    *    @since 1.4.2
    *
    *-------------------------------------------------------------------------------------*/

    function pre_save($value, $field = null)
    {
        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    pre_save_field($field)
    *
    *    @author Matt Gibbs
    *    @since 1.6.8
    *
    *-------------------------------------------------------------------------------------*/

    function pre_save_field($field)
    {
        return $field;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_option($field, $option_name, $default_value)
    *
    *    @author Matt Gibbs
    *    @since 1.4.3
    *
    *-------------------------------------------------------------------------------------*/

    function get_option($field, $option_name, $default_value = '')
    {
        if (isset($field->options[$option_name]))
        {
            if (is_string($field->options[$option_name]))
            {
                return esc_attr($field->options[$option_name]);
            }
            return $field->options[$option_name];
        }
        return $default_value;
    }
}
