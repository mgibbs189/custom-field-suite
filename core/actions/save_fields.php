<?php

global $wpdb;

$field_data = isset($_POST['cfs']['fields']) ? $_POST['cfs']['fields'] : array();
$cfs_rules = isset($_POST['cfs']['rules']) ? $_POST['cfs']['rules'] : array();
$cfs_extras = isset($_POST['cfs']['extras']) ? $_POST['cfs']['extras'] : array();

/*---------------------------------------------------------------------------------------------
    Save fields
---------------------------------------------------------------------------------------------*/

$weight = 0;
$table_name = $wpdb->prefix . 'cfs_fields';

// remove all existing fields
$wpdb->query("DELETE FROM $table_name WHERE post_id = '$post_id'");

foreach ($field_data as $key => $field)
{
    // clean the field
    $field = stripslashes_deep($field);

    // save empty string for fields without options
    $field['options'] = !empty($field['options']) ? serialize($field['options']) : '';

    // allow for field customizations
    $field = $this->fields[$field['type']]->pre_save_field($field);

    $data = array(
        'name' => $field['name'],
        'label' => $field['label'],
        'type' => $field['type'],
        'instructions' => $field['instructions'],
        'post_id' => $post_id,
        'parent_id' => $field['parent_id'],
        'weight' => $weight,
        'options' => $field['options'],
    );

    // use an existing ID if available
    if (0 < (int) $field['id'])
    {
        $data['id'] = (int) $field['id'];
    }

    // insert the field
    $wpdb->insert($table_name, $data);

    $weight++;
}

/*---------------------------------------------------------------------------------------------
    Save rules
---------------------------------------------------------------------------------------------*/

$data = array();
$rule_types = array('post_types', 'user_roles', 'post_ids', 'term_ids', 'page_templates');

foreach ($rule_types as $type)
{
    if (!empty($cfs_rules[$type]))
    {
        $data[$type] = array(
            'operator' => $cfs_rules['operator'][$type],
            'values' => $cfs_rules[$type],
        );
    }
}

update_post_meta($post_id, 'cfs_rules', $data);

/*---------------------------------------------------------------------------------------------
    Save extras
---------------------------------------------------------------------------------------------*/

update_post_meta($post_id, 'cfs_extras', $cfs_extras);
