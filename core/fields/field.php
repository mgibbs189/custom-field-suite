<?php

class cfs_Field
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
    *    format_value_for_api($value, $field)
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function format_value_for_api($value, $field)
    {
        return $value[0];
    }


    /*--------------------------------------------------------------------------------------
    *
    *    format_value_for_input($value, $field)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function format_value_for_input($value, $field)
    {
        return $value[0];
    }


    /*--------------------------------------------------------------------------------------
    *
    *    pre_save($value, $field)
    *
    *    @author Matt Gibbs
    *    @since 1.4.2
    *
    *-------------------------------------------------------------------------------------*/

    function pre_save($value, $field)
    {
        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    load_value($field)
    *
    *    @author Matt Gibbs
    *    @since 1.0.5
    *
    *-------------------------------------------------------------------------------------*/

    function load_value($field)
    {

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
