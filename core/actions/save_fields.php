<?php

global $wpdb;

/*---------------------------------------------------------------------------------------------
    Save fields
---------------------------------------------------------------------------------------------*/

if (isset($_POST['cfs']['fields']))
{
    $weight = 0;
    $field_data = $_POST['cfs']['fields'];
    $table_name = $wpdb->prefix . 'cfs_fields';

    // remove all existing fields
    $wpdb->query("DELETE FROM $table_name WHERE post_id = '$post_id'");

    foreach ($field_data as $key => $field)
    {
        // clean the field
        $field = stripslashes_deep($field);

        $data = array(
            'name' => $field['name'],
            'label' => $field['label'],
            'type' => $field['type'],
            'instructions' => $field['instructions'],
            'post_id' => $post_id,
            'parent_id' => $field['parent_id'],
            'weight' => $weight,
            'options' => serialize($field['options']),
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
}

/*---------------------------------------------------------------------------------------------
    Save rules
---------------------------------------------------------------------------------------------*/

$data = array();
$cfs_rules = $_POST['cfs']['rules'];
$rule_types = array('post_types', 'user_roles', 'post_ids', 'term_ids');

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

$cfs_extras = $_POST['cfs']['extras'];
update_post_meta($post_id, 'cfs_extras', $cfs_extras);
