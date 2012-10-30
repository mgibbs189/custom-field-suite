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
$current_field_ids = array();
$table_name = $wpdb->prefix . 'cfs_fields';

// Get existing fields (check for renamed or deleted fields)
$results = $wpdb->get_results("SELECT id, name FROM $table_name WHERE post_id = '$post_id'");
foreach ($results as $result)
{
    $prev_fields[$result->id] = $result->name;
}

// Remove all existing fields
$wpdb->query("DELETE FROM $table_name WHERE post_id = '$post_id'");

foreach ($field_data as $key => $field)
{
    // Sanitize the field
    $field = stripslashes_deep($field);

    // Allow for field customizations
    $field = $this->fields[$field['type']]->pre_save_field($field);

    // Save empty string for fields without options
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

    // Use an existing ID if available
    if (0 < (int) $field['id'])
    {
        $data['id'] = (int) $field['id'];

        // We use this variable to check for deleted fields
        $current_field_ids[] = $data['id'];

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

    // Insert the field
    $wpdb->insert($table_name, $data);

    $weight++;
}

// Remove values for deleted fields
$deleted_field_ids = array_diff(array_keys($prev_fields), $current_field_ids);

if (0 < count($deleted_field_ids))
{
    $deleted_field_ids = implode(',', $deleted_field_ids);
    $wpdb->query("
        DELETE v, m
        FROM {$wpdb->prefix}cfs_values v
        INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
        WHERE v.field_id IN ($deleted_field_ids)"
    );
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
        // Break apart the autocomplete string
        if ('post_ids' == $type)
        {
            $cfs_rules[$type] = explode(',', $cfs_rules[$type]);
        }

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
