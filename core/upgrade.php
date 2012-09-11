<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$last_version = get_option('cfs_version');

if (version_compare($last_version, $this->version, '<'))
{
    // Add necessary tables
    if (version_compare($last_version, '1.0.0', '<'))
    {
        $sql = "
        CREATE TABLE {$wpdb->prefix}cfs_fields (
            id INT unsigned not null auto_increment,
            name TEXT,
            label TEXT,
            type TEXT,
            instructions TEXT,
            post_id INT unsigned,
            parent_id INT unsigned default 0,
            weight INT unsigned,
            options TEXT,
            PRIMARY KEY (id),
            INDEX post_id_idx (post_id)
        ) DEFAULT CHARSET=utf8";
        dbDelta($sql);

        $sql = "
        CREATE TABLE {$wpdb->prefix}cfs_values (
            id INT unsigned not null auto_increment,
            field_id INT unsigned,
            meta_id INT unsigned,
            post_id INT unsigned,
            base_field_id INT unsigned,
            hierarchy TEXT,
            weight INT unsigned,
            sub_weight INT unsigned,
            PRIMARY KEY (id),
            INDEX field_id_idx (field_id),
            INDEX post_id_idx (post_id)
        ) DEFAULT CHARSET=utf8";
        dbDelta($sql);
    }

    // Replace the rules table
    if (version_compare($last_version, '1.2.0', '<'))
    {
        $rules = array();
        $results = $wpdb->get_results("SELECT group_id, rule, value FROM {$wpdb->prefix}cfs_rules");
        foreach ($results as $rule)
        {
            $rules[$rule->group_id]['post_types']['operator'] = '==';
            $rules[$rule->group_id]['post_types']['values'][] = $rule->value;
        }

        foreach ($rules as $post_id => $rule)
        {
            update_post_meta($post_id, 'cfs_rules', $rule);
        }

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cfs_rules");
    }

    // Convert relationship values
    if (version_compare($last_version, '1.4.2', '<'))
    {
        $sql = "
        SELECT v.field_id, v.meta_id, v.post_id, v.weight, m.meta_key, m.meta_value, f.parent_id
        FROM {$wpdb->prefix}cfs_values v
        INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
        INNER JOIN {$wpdb->prefix}cfs_fields f ON f.id = v.field_id AND f.name = m.meta_key AND f.type = 'relationship'
        WHERE m.meta_value LIKE '%,%'
        ORDER BY v.field_id";
        $results = $wpdb->get_results($sql);

        foreach ($results as $result)
        {
            $all_values = explode(',', $result->meta_value);
            $first_value = array_shift($all_values);

            // Update existing postmeta value
            $wpdb->update(
                $wpdb->postmeta,
                array('meta_value' => $first_value),
                array('meta_id' => $result->meta_id)
            );

            foreach ($all_values as $key => $the_id)
            {
                // Add row into postmeta
                $wpdb->insert($wpdb->postmeta, array(
                    'post_id' => $result->post_id,
                    'meta_key' => $result->meta_key,
                    'meta_value' => $the_id,
                ));
                $meta_id = $wpdb->insert_id;

                // See if relationship field is within a loop
                $weight = (0 < (int) $result->parent_id) ? $result->weight : ($key + 1);
                $sub_weight = (0 < (int) $result->parent_id) ? ($key + 1) : 0;

                // Add row into cfs_values
                $wpdb->insert($wpdb->prefix . 'cfs_values', array(
                    'field_id' => $result->field_id,
                    'meta_id' => $meta_id,
                    'post_id' => $result->post_id,
                    'weight' => $weight,
                    'sub_weight' => $sub_weight,
                ));
            }
        }
    }

    // Add fields to handle nested loops
    if (version_compare($last_version, '1.5.0', '<'))
    {
        if (version_compare($last_version, '1.0.0', '>='))
        {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}cfs_values ADD COLUMN hierarchy TEXT AFTER post_id");
            $wpdb->query("ALTER TABLE {$wpdb->prefix}cfs_values ADD COLUMN base_field_id INT unsigned default 0 AFTER post_id");
            $wpdb->query("UPDATE {$wpdb->prefix}cfs_values SET hierarchy = '' WHERE hierarchy IS NULL");
        }

        $sql = "
        SELECT v.id, f.parent_id, v.weight, v.field_id
        FROM {$wpdb->prefix}cfs_values v
        INNER JOIN {$wpdb->prefix}cfs_fields f ON f.id = v.field_id AND f.parent_id > 0";
        $results = $wpdb->get_results($sql);
        foreach ($results as $result)
        {
            $hierarchy = "{$result->parent_id}:{$result->weight}:{$result->field_id}";
            $sql = "
            UPDATE {$wpdb->prefix}cfs_values
            SET hierarchy = '$hierarchy', base_field_id = '$result->parent_id'
            WHERE id = '$result->id' LIMIT 1";
            $wpdb->query($sql);
        }
    }

    // Convert select options to arrays
    if (version_compare($last_version, '1.6.8', '<'))
    {
        $results = $wpdb->get_results("SELECT id, options FROM {$wpdb->prefix}cfs_fields WHERE type = 'select'");
        foreach ($results as $result)
        {
            $field_id = $result->id;
            $options = unserialize($result->options);

            if (isset($options['choices']) && !is_array($options['choices']))
            {
                $choices = trim($options['choices']);
                $new_choices = array();

                if (!empty($choices))
                {
                    $choices = str_replace("\r\n", "\n", $choices);
                    $choices = str_replace("\r", "\n", $choices);
                    $choices = (false !== strpos($choices, "\n")) ? explode("\n", $choices) : (array) $choices;

                    foreach ($choices as $choice)
                    {
                        $choice = trim($choice);
                        if (false !== ($pos = strpos($choice, ' : ')))
                        {
                            $array_key = substr($choice, 0, $pos);
                            $array_value = substr($choice, $pos + 3);
                            $new_choices[$array_key] = $array_value;
                        }
                        else
                        {
                            $new_choices[$choice] = $choice;
                        }
                    }
                }

                $options['choices'] = $new_choices;
                $sql = "UPDATE {$wpdb->prefix}cfs_fields SET options = %s WHERE id = %d LIMIT 1";
                $wpdb->query($wpdb->prepare($sql, serialize($options), $field_id));
            }
        }
    }

    update_option('cfs_version', $this->version);
}
