<?php

class cfs_Textarea extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'textarea';
        $this->label = __('Textarea', 'cfs');
        $this->parent = $parent;
    }

    function html($field)
    {
    ?>
        <textarea name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" rows="4"><?php echo $field->value; ?></textarea>
    <?php
    }

    function options_html($key, $field)
    {
        $none_text = __('None', 'cfs');
        $newlines_text = __('Convert newlines to', 'cfs');
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Default Value', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'textarea',
                        'input_name' => "cfs[fields][$key][options][default_value]",
                        'input_class' => '',
                        'value' => $this->get_option($field, 'default_value'),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Formatting', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][formatting]",
                        'options' => array('choices' => "none : $none_text\nauto_br : $newlines_text <br />"),
                        'input_class' => '',
                        'value' => $this->get_option($field, 'formatting', 'auto_br'),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function format_value_for_input($value, $field)
    {
        return htmlspecialchars($value[0], ENT_QUOTES);
    }

    function format_value_for_api($value, $field)
    {
        $formatting = $this->get_option($field, 'formatting', 'none');
        return ('none' == $formatting[0]) ? $value[0] : nl2br($value[0]);
    }
}
