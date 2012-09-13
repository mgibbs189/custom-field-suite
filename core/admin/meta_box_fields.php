<input type="hidden" name="cfs[save]" value="<?php echo wp_create_nonce('cfs_save_fields'); ?>" />

<ul class="fields">
<?php

global $post;

$results = $this->api->get_input_fields($post->ID);

/*---------------------------------------------------------------------------------------------
    Create <ul> based on field structure
---------------------------------------------------------------------------------------------*/

$level = 0;
$levels = array();
$last_level = $diff = 0;

foreach ($results as $field)
{
    $level = 0;
    if (0 < (int) $field->parent_id)
    {
        $level = isset($levels[$field->parent_id]) ? $levels[$field->parent_id] + 1 : 1;
        $levels[$field->id] = (int) $level;
    }
    $diff = ($level - $last_level);
    $last_level = $level;

    if (0 < $diff)
    {
        for ($i = 0; $i < ($diff - 1); $i++)
        {
            echo '<ul><li>';
        }
        echo '<ul>';
    }
    elseif (0 > $diff)
    {
        for ($i = 0; $i < abs($diff); $i++)
        {
            echo '</li></ul>';
        }
    }

    echo ('loop' == $field->type) ? '<li class="loop">' : '<li>';

    $this->field_html($field);
}

for ($i = 0; $i < abs($level); $i++)
{
    echo '</li></ul>';
}

echo '</li>';
?>
</ul>

<div class="table_footer">
    <input type="button" class="button-primary cfs_add_field" value="<?php _e('Add New Field', 'cfs'); ?>" />
</div>
