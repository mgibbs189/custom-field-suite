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
        $selected = ('1' == (string) $field->value) ? ' checked' : '';
    ?>
        <input type="checkbox" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="1"<?php echo $selected; ?> />
        <span><?php echo $field->options['message']; ?></span>
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

    function format_value_for_api($value, $field)
    {
        return ('1' == (string) $value[0]) ? 1 : 0;
    }
}
