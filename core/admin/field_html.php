<div class="field">
    <div class="field_meta">
        <table class="widefat">
            <tr>
                <td class="field_order">

                </td>
                <td class="field_label">
                    <a class="cfs_edit_field row-title" title="Edit field" href="javascript:;"><?php echo $field->label; ?>&nbsp;</a>
                </td>
                <td class="field_name">
                    <?php echo $field->name; ?>
                </td>
                <td class="field_type">
                    <?php echo $field->type; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="field_form">
        <table class="widefat">
            <tbody>
                <tr class="field_basics">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="field_label">
                                    <label>
                                        <?php _e('Label', 'cfs'); ?>
                                        <span class="cfs_tooltip" title="<?php _e('The field name that editors will see', 'cfs'); ?>"></span>
                                    </label>
                                    <input type="text" name="cfs[fields][<?php echo $field->weight; ?>][label]" value="<?php echo empty($field->id) ? '' : esc_attr($field->label); ?>" />
                                </td>
                                <td class="field_name">
                                    <label>
                                        <?php _e('Name', 'cfs'); ?>
                                        <span class="cfs_tooltip" title="<?php _e('Only lowercase letters and underscores', 'cfs'); ?>"></span>
                                    </label>
                                    <input type="text" name="cfs[fields][<?php echo $field->weight; ?>][name]" value="<?php echo empty($field->id) ? '' : esc_attr($field->name); ?>" />
                                </td>
                                <td class="field_type">
                                    <label><?php _e('Field Type', 'cfs'); ?></label>
                                    <select name="cfs[fields][<?php echo $field->weight; ?>][type]">
                                        <?php foreach ($this->fields as $type) : ?>
                                        <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                        <option value="<?php echo $type->name; ?>"<?php echo $selected; ?>><?php echo $type->label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php $this->fields[$field->type]->options_html($field->weight, $field); ?>

                <tr class="field_instructions">
                    <td class="label">
                        <label>
                            <?php _e('Instructions', 'cfs'); ?>
                            <span class="cfs_tooltip" title="<?php _e('Tips for editors when entering field data', 'cfs'); ?>"></span>
                        </label>
                    </td>
                    <td>
                        <input type="text" name="cfs[fields][<?php echo $field->weight; ?>][instructions]" value="<?php echo esc_attr($field->instructions); ?>" />
                    </td>
                </tr>
                <tr class="field_actions">
                    <td class="label"></td>
                    <td style="vertical-align:middle">
                        <input type="hidden" name="cfs[fields][<?php echo $field->weight; ?>][id]" class="field_id" value="<?php echo $field->id; ?>" />
                        <input type="hidden" name="cfs[fields][<?php echo $field->weight; ?>][parent_id]" class="parent_id" value="<?php echo $field->parent_id; ?>" />
                        <input type="button" value="<?php _e('Close'); ?>" class="button-secondary cfs_edit_field" />
                        &nbsp; -or- &nbsp; <span class="cfs_delete_field">delete</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>