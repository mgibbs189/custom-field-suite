<div class="field">
    <div class="field_meta">
        <table class="widefat">
            <tr>
                <td class="field_order">

                </td>
                <td class="field_label">
                    <a class="cfs_edit_field row-title"><?php echo esc_html( $field->label ); ?></a>
                </td>
                <td class="field_name">
                    <?php echo esc_html( $field->name ); ?>
                </td>
                <td class="field_type">
                    <a class="cfs_edit_field"><?php echo esc_html( $field->type ); ?></a>
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
                                        <?php _e( 'Label', 'cfs' ); ?>
                                        <div class="cfs_tooltip">?
                                            <div class="tooltip_inner"><?php _e( 'The field label that editors will see.', 'cfs' ); ?></div>
                                        </div>
                                    </label>
                                    <input type="text" name="cfs[fields][<?php echo $field->weight; ?>][label]" value="<?php echo empty( $field->id ) ? '' : esc_attr( $field->label ); ?>" />
                                </td>
                                <td class="field_name">
                                    <label>
                                        <?php _e( 'Name', 'cfs' ); ?>
                                        <div class="cfs_tooltip">?
                                            <div class="tooltip_inner">
                                                <?php _e( 'The field name is passed into get() to retrieve values. Use only lowercase letters, numbers, and underscores.', 'cfs' ); ?>
                                            </div>
                                        </div>
                                    </label>
                                    <input type="text" name="cfs[fields][<?php echo $field->weight; ?>][name]" value="<?php echo empty( $field->id ) ? '' : esc_attr( $field->name ); ?>" />
                                </td>
                                <td class="field_type">
                                    <label><?php _e( 'Field Type', 'cfs' ); ?></label>
                                    <select name="cfs[fields][<?php echo $field->weight; ?>][type]">
                                        <?php foreach ( CFS()->fields as $type ) : ?>
                                        <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                        <option value="<?php echo $type->name; ?>"<?php echo $selected; ?>><?php echo $type->label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php CFS()->fields[ $field->type ]->options_html( $field->weight, $field ); ?>

                <tr class="field_notes">
                    <td class="label">
                        <label>
                            <?php _e( 'Notes', 'cfs' ); ?>
                            <div class="cfs_tooltip">?
                                <div class="tooltip_inner"><?php _e( 'Notes for editors during data entry', 'cfs' ); ?></div>
                            </div>
                        </label>
                    </td>
                    <td>
                        <textarea name="cfs[fields][<?php echo $field->weight; ?>][notes]"><?php echo esc_textarea( $field->notes ); ?></textarea>
                    </td>
                </tr>
                <tr class="field_actions">
                    <td class="label"></td>
                    <td style="vertical-align:middle">
                        <input type="hidden" name="cfs[fields][<?php echo $field->weight; ?>][id]" class="field_id" value="<?php echo $field->id; ?>" />
                        <input type="hidden" name="cfs[fields][<?php echo $field->weight; ?>][parent_id]" class="parent_id" value="<?php echo $field->parent_id; ?>" />
                        <input type="button" value="<?php _e( 'Close', 'cfs' ); ?>" class="button-secondary cfs_edit_field" />
                        &nbsp; -<?php _e( 'or', 'cfs' ); ?>- &nbsp; <span class="cfs_delete_field"><?php _e( 'delete', 'cfs' ); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>