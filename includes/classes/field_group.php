<?php

class cfs_field_group
{
    public $parent;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function __construct($parent)
    {
        $this->parent = $parent;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    import
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function import($options)
    {
        global $wpdb;

        if (!empty($options['import_code']))
        {
            // Collect stats
            $stats = array();

            // Get all existing field group names
            $existing_groups = $wpdb->get_col("SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'cfs'");

            // Loop through field groups
            foreach ($options['import_code'] as $group_id => $group)
            {
                // Make sure this field group doesn't exist
                if (!in_array($group['post_name'], $existing_groups))
                {
                    // Insert new post
                    $post_id = wp_insert_post(array(
                        'post_title' => $group['post_title'],
                        'post_name' => $group['post_name'],
                        'post_type' => 'cfs',
                        'post_status' => 'publish',
                        'post_content' => '',
                        'post_content_filtered' => '',
                        'post_excerpt' => '',
                        'to_ping' => '',
                        'pinged' => '',
                    ));

                    // Generate new field IDs
                    $field_id_mapping = array();
                    $next_field_id = (int) get_option('cfs_next_field_id');
                    foreach ($group['cfs_fields'] as $key => $data)
                    {
                        $id = $group['cfs_fields'][$key]['id'];
                        $parent_id = $group['cfs_fields'][$key]['parent_id'];
                        $field_id_mapping[$id] = $next_field_id;
                        $group['cfs_fields'][$key]['id'] = $next_field_id;
                        if (0 < (int) $parent_id)
                        {
                            $group['cfs_fields'][$key]['parent_id'] = $field_id_mapping[$parent_id];
                        }
                        $next_field_id++;
                    }

                    update_option('cfs_next_field_id', $next_field_id);
                    update_post_meta($post_id, 'cfs_fields', $group['cfs_fields']);
                    update_post_meta($post_id, 'cfs_rules', $group['cfs_rules']);
                    update_post_meta($post_id, 'cfs_extras', $group['cfs_extras']);

                    $stats['imported'][] = $group['post_title'];
                }
                else
                {
                    $stats['skipped'][] = $group['post_title'];
                }
            }

            $return = '';
            if (!empty($stats['imported']))
            {
                $return .= '<div>' . __('Imported', 'cfs') . ': ' . implode(', ', $stats['imported']) . '</div>';
            }
            if (!empty($stats['skipped']))
            {
                $return .= '<div>' . __('Skipped', 'cfs') . ': ' . implode(', ', $stats['skipped']) . '</div>';
            }
            return $return;
        }
        else
        {
            return '<div>' . __('Nothing to import', 'cfs') . '</div>';
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    export
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function export($options)
    {
        global $wpdb;

        $post_ids = array();
        $field_groups = array();
        foreach ($options['field_groups'] as $post_id)
        {
            $post_ids[] = (int) $post_id;
        }
        $post_ids = implode(',', $post_ids);

        $post_data = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_type = 'cfs' AND ID IN ($post_ids)");
        foreach ($post_data as $row)
        {
            $field_groups[$row->ID] = array(
                'post_title' => $row->post_title,
                'post_name' => $row->post_name,
            );
        }

        $meta_data = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE 'cfs_%' AND post_id IN ($post_ids)");
        foreach ($meta_data as $row)
        {
            $value = unserialize($row->meta_value);
            $field_groups[$row->post_id][$row->meta_key] = $value;
        }

        return $field_groups;
    }
}
