<?php

global $wpdb;

$field_data = isset($_POST['cfs']['fields']) ? $_POST['cfs']['fields'] : array();
$cfs_rules = isset($_POST['cfs']['rules']) ? $_POST['cfs']['rules'] : array();
$cfs_extras = isset($_POST['cfs']['extras']) ? $_POST['cfs']['extras'] : array();

/*---------------------------------------------------------------------------------------------
    Save fields
---------------------------------------------------------------------------------------------*/

$weight = 0;
$prev_fields = array();
$table_name = $wpdb->prefix . 'cfs_fields';

// get existing fields (check for renamed or deleted fields)
$results = $wpdb->query("SELECT id, name FROM $table_name WHERE post_id = '$post_id'");
foreach ($results as $result)
{
    $prev_fields[$result->id] = $result->name;
}

// remove all existing fields
$wpdb->query("DELETE FROM $table_name WHERE post_id = '$post_id'");

foreach ($field_data as $key => $field)
{
    // clean the field
    $field = stripslashes_deep($field);

    // allow for field customizations
    $field = $this->fields[$field['type']]->pre_save_field($field);

    // save empty string for fields without options
    $field['options'] = !empty($field['options']) ? serialize($field['options']) : '';

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

        // Rename the postmeta key if necessary
        if ($field['name'] != $prev_fields[$data['id']])
        {
            $wpdb->query(
                $wpdb->prepare("
                    UPDATE {$wpdb->postmeta} m
                    INNER JOIN {$wpdb->prefix}cfs_values v ON v.meta_id = m.meta_id
                    SET meta_key = %s
                    WHERE v.field_id = %d",
                    $field['name'], $data['id']
                )
            );
        }
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
