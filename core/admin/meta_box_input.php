<input type="hidden" name="cfs[save]" value="<?php echo wp_create_nonce('cfs_save_input'); ?>" />

<?php

// Passed from add_meta_box
$group_id = $metabox['args']['group_id'];

$input_fields = $this->api->get_input_fields($group_id);

// Add any necessary head scripts
foreach ($input_fields as $key => $field)
{
    if (!isset($this->used_types[$field->type]))
    {
        $this->fields[$field->type]->input_head($field);
        $this->used_types[$field->type] = true;
    }

    // Ignore sub-fields
    if (1 > (int) $field->parent_id)
    {
?>
<div class="field">
    <label><?php echo $field->label; ?></label>

    <?php if (!empty($field->instructions)) : ?>
    <p class="instructions"><?php echo $field->instructions; ?></p>
    <?php endif; ?>

    <div class="cfs_<?php echo $field->type; ?>">
    <?php
        $this->create_field(array(
            'id' => $field->id,
            'group_id' => $group_id,
            'post_id' => $field->post_id,
            'type' => $field->type,
            'input_name' => "cfs[input][$field->id][value]",
            'input_class' => $field->type,
            'options' => $field->options,
            'value' => $field->value,
        ));
    ?>
    </div>
</div>
<?php
    }
}
