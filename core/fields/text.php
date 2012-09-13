<?php

class cfs_Text extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'text';
        $this->label = __('Text', 'cfs');
        $this->parent = $parent;
    }

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Default Value', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field(array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][default_value]",
                        'input_class' => '',
                        'value' => $this->get_option($field, 'default_value'),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }

    function format_value_for_input($value, $field)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }
}
