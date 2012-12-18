<?php

class cfs_ajax
{
    /*--------------------------------------------------------------------------------------
    *
    *    AJAX/search_posts
    *
    *    @author Matt Gibbs
    *    @since 1.7.5
    *
    *-------------------------------------------------------------------------------------*/

    public function search_posts($options)
    {
        global $wpdb;

        $keywords = $wpdb->escape($options['q']);

        $sql = "
        SELECT ID, post_type, post_title
        FROM $wpdb->posts
        WHERE
            post_status IN ('publish', 'private') AND
            post_type NOT IN ('cfs', 'attachment', 'revision', 'nav_menu_item') AND
            post_title LIKE '%$keywords%'
        ORDER BY post_type, post_title
        LIMIT 10";
        $results = $wpdb->get_results($sql);

        $output = array();
        foreach ($results as $result)
        {
            $output[] = array(
                'id' => $result->ID,
                'text' => "($result->post_type) $result->post_title"
            );
        }
        return json_encode($output);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    AJAX/export
    *
    *    @author Matt Gibbs
    *    @since 1.7.5
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
            $data = (array) $row;
            unset($data['ID']);
            $field_groups[$row->ID] = $data;
        }

        $meta_data = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE 'cfs_%' AND post_id IN ($post_ids)");
        foreach ($meta_data as $row)
        {
            $data = (array) $row;
            unset($data['meta_id']);
            unset($data['post_id']);
            $field_groups[$row->post_id]['meta'][] = $data;
        }

        $field_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cfs_fields WHERE post_id IN ($post_ids) ORDER BY post_id, parent_id, weight");
        foreach ($field_data as $row)
        {
            $data = (array) $row;
            unset($data['id']);
            unset($data['post_id']);
            $field_groups[$row->post_id]['fields'][$row->id] = $data;
        }

        return $field_groups;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    AJAX/import
    *
    *    @author Matt Gibbs
    *    @since 1.7.5
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
                if (!in_array($group->post_name, $existing_groups))
                {
                    // Insert new post
                    $post_id = wp_insert_post(array(
                        'post_title' => $group->post_title,
                        'post_name' => $group->post_name,
                        'post_type' => 'cfs',
                        'post_status' => 'publish',
                        'post_content' => '',
                        'post_content_filtered' => '',
                        'post_excerpt' => '',
                        'to_ping' => '',
                        'pinged' => '',
                    ));

                    // Loop through meta_data
                    foreach ($group->meta as $row)
                    {
                        // add_post_meta serializes the meta_value (bad!)
                        $wpdb->insert($wpdb->postmeta, array(
                            'post_id' => $post_id,
                            'meta_key' => $row->meta_key,
                            'meta_value' => $row->meta_value
                        ));
                    }

                    // Loop through field_data
                    $field_id_mapping = array();
                    foreach ($group->fields as $old_id => $row)
                    {
                        $row_array = (array) $row;
                        $row_array['post_id'] = $post_id;

                        $wpdb->insert($wpdb->prefix . 'cfs_fields', $row_array);
                        $field_id_mapping[$old_id] = $wpdb->insert_id;
                    }

                    // Update the parent_ids
                    foreach ($field_id_mapping as $old_id => $new_id)
                    {
                        $new_field_ids = implode(',', $field_id_mapping);
                        $wpdb->query("UPDATE {$wpdb->prefix}cfs_fields SET parent_id = '$new_id' WHERE parent_id = '$old_id' AND id IN ($new_field_ids)");
                    }

                    $stats['imported'][] = $group->post_title;
                }
                else
                {
                    $stats['skipped'][] = $group->post_title;
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
    *    AJAX/reset
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    public function reset()
    {
        global $wpdb;

        // Drop field groups
        $sql = "
        DELETE p, m FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
        WHERE p.post_type = 'cfs'";
        $wpdb->query($sql);

        // Drop custom field values
        $sql = "
        DELETE v, m FROM {$wpdb->prefix}cfs_values v
        LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id";
        $wpdb->query($sql);

        // Drop tables
        $wpdb->query("DROP TABLE {$wpdb->prefix}cfs_fields");
        $wpdb->query("DROP TABLE {$wpdb->prefix}cfs_values");
        delete_option('cfs_version');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    AJAX/map_values
    *
    *    @author Matt Gibbs
    *    @since 1.7.5
    *
    *-------------------------------------------------------------------------------------*/

    public function map_values($options)
    {
        global $wpdb;

        if (isset($options['field_groups']))
        {
            $group_ids = (array) $options['field_groups'];
            foreach ($group_ids as $group_id)
            {
                $rules = get_post_meta($group_id, 'cfs_rules', true);
                $post_types = $post_ids = $term_ids = '';
                $fields = array();

                // Get this group's fields
                $sql = "
                SELECT id, name
                FROM {$wpdb->prefix}cfs_fields
                WHERE post_id = '$group_id' AND parent_id = 0";
                $results = $wpdb->get_results($sql);
                foreach ($results as $result)
                {
                    $fields[$result->name] = $result->id;
                }

                if (isset($rules['post_types']))
                {
                    $post_types = implode("','", $rules['post_types']['values']);
                    $operator = ('==' == $rules['post_types']['operator'][0]) ? 'IN' : 'NOT IN';
                    $post_types = " AND p.post_type $operator ('$post_types')";
                }
                if (isset($rules['post_ids']))
                {
                    $post_ids = implode(',', $rules['post_ids']['values']);
                    $operator = ('==' == $rules['post_ids']['operator'][0]) ? 'IN' : 'NOT IN';
                    $post_ids = " AND p.ID $operator ($post_ids)";
                }
                if (isset($rules['term_ids']))
                {
                    $term_ids = implode(',', $rules['term_ids']['values']);
                    $operator = ('==' == $rules['term_ids']['operator'][0]) ? 'IN' : 'NOT IN';
                    $term_ids = "
                    INNER JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID
                    INNER JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.term_id $operator ($term_ids)";
                }

                $sql = "
                SELECT m.meta_id, m.post_id, m.meta_key
                FROM $wpdb->postmeta m
                INNER JOIN $wpdb->posts p ON p.ID = m.post_id $post_types $post_ids $term_ids
                LEFT JOIN {$wpdb->prefix}cfs_values v ON v.meta_id = m.meta_id
                WHERE v.meta_id IS NULL";
                $results = $wpdb->get_results($sql);

                $tuples = array();
                foreach ($results as $result)
                {
                    if (isset($fields[$result->meta_key]))
                    {
                        $field_id = $fields[$result->meta_key];
                        $tuples[] = "($field_id, $result->meta_id, $result->post_id, 0, 0)";
                    }
                }

                if (0 < count($tuples))
                {
                    $wpdb->query("INSERT INTO {$wpdb->prefix}cfs_values (field_id, meta_id, post_id, weight, sub_weight) VALUES " . implode(',', $tuples));
                }
            }

            return 'Sync successful';
        }
        else
        {
            return '<div>' . __('No field groups selected', 'cfs') . '</div>';
        }
    }
}
