<?php

class cfs_Api
{
    public $parent;
    public $data;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function __construct($parent)
    {
        $this->parent = $parent;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_field
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get_field($field_name, $post_id = false)
    {
        global $post;

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Trigger get_fields if not in cache
        if (!isset($this->data[$post_id][$field_name]))
        {
            $fields = $this->get_fields($post_id);
            return $fields[$field_name];
        }

        return $this->data[$post_id][$field_name];
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_fields
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get_fields($post_id = false, $options = array())
    {
        global $post, $wpdb;

        $defaults = array(
            'for_input' => false,
        );
        $options = (object) array_merge($defaults, $options);

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Return cached results
        if (isset($this->data[$post_id]))
        {
            return $this->data[$post_id];
        }

        $field_data = array();

        // Get all field groups for this post
        $group_ids = $this->parent->get_matching_groups($post_id, true);

        if (!empty($group_ids))
        {
            $group_ids = implode(',', array_keys($group_ids));
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cfs_fields WHERE post_id IN ($group_ids) ORDER BY weight");
            foreach ($results as $result)
            {
                $result->options = unserialize($result->options);
                $fields[$result->id] = $result;
            }

            // Get all the field data
            $sql = "
            SELECT m.meta_value, v.field_id, f.parent_id, v.hierarchy, v.weight, v.sub_weight
            FROM {$wpdb->prefix}cfs_values v
            INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
            INNER JOIN {$wpdb->prefix}cfs_fields f ON f.id = v.field_id
            WHERE v.post_id IN ($post_id)
            ORDER BY f.weight, v.field_id, v.weight, v.sub_weight";

            $results = $wpdb->get_results($sql);
            $num_rows = $wpdb->num_rows;

            $prev_hierarchy = '';
            $prev_field_id = '';
            $prev_item = '';

            foreach ($results as $order_num => $result)
            {
                $field = $fields[$result->field_id];
                $current_item = "{$result->hierarchy}.{$result->weight}.{$result->field_id}";

                if (!empty($result->hierarchy))
                {
                    // Format for API (field names)
                    if (false === $options->for_input)
                    {
                        $tmp = explode(':', $result->hierarchy);
                        foreach ($tmp as $key => $val)
                        {
                            if (0 == ($key % 2))
                            {
                                $tmp[$key] = $fields[$val]->name;
                            }
                        }
                        $hierarchy = implode(':', $tmp);
                    }
                    // Format for input (field IDs)
                    else
                    {
                        $hierarchy = $result->hierarchy;
                    }

                    $this->assemble_value_array($field_data, $hierarchy, $field, $result->meta_value);
                }
                else
                {
                    // If "for_input" is false, get the field name
                    $hierarchy = (false === $options->for_input) ? $field->name : $field->id;
                    $field_data[$hierarchy][] = $result->meta_value;
                }

                // Assemble the values
                if ($current_item != $prev_item && '' != $prev_item) // call apply_value_filters on previous field
                {
                    $this->assemble_value_array($field_data, $prev_hierarchy, $fields[$prev_field_id], false, $options);
                }

                if ($num_rows == ($order_num + 1)) // last row
                {
                    $this->assemble_value_array($field_data, $hierarchy, $field, false, $options);
                }

                $prev_hierarchy = $hierarchy;
                $prev_field_id = $field->id;
                $prev_item = $current_item;
            }
        }

        $this->data[$post_id] = $field_data;
        return $field_data;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    assemble_value_array
    *
    *    Replace a value within a multidimensional array without using eval()
    *
    *    @param array $field_data The value array
    *    @param string $hierarchy The array element to target
    *    @param object $field The field object passed into apply_value_filters
    *    @param mixed $value The replacement value; bypass apply_value_filters
    *    @param mixed $options The options passed into apply_value_filters
    *    @author Matt Gibbs
    *    @since 1.5.7
    *
    *-------------------------------------------------------------------------------------*/

    function assemble_value_array(&$field_data, $hierarchy, $field, $value = false, $options = false)
    {
        $data = &$field_data;
        foreach (explode(':', $hierarchy) as $i)
        {
            $data = &$data[$i];
        }

        if (false !== $value)
        {
            $data[] = $value;
        }
        else
        {
            $data = $this->apply_value_filters($field, $data, $options);
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_reverse_related
    *
    *    @author Matt Gibbs
    *    @since 1.4.4
    *
    *-------------------------------------------------------------------------------------*/

    function get_reverse_related($post_id, $options = array(), $deprecated = array())
    {
        global $wpdb;

        // Handle function signature change
        if (!ctype_digit($post_id))
        {
            // old signature: $field_name, $post_id, $options
            $old_post_id = $options;
            $options = $deprecated;
            $options['field_name'] = $post_id;
            $post_id = $old_post_id;
        }

        $where = "m.meta_value = '$post_id'";

        if (isset($options['field_name']))
        {
            $field_name = implode("','", (array) $options['field_name']);
            $where .= " AND m.meta_key IN ('$field_name')";
        }
        if (isset($options['post_type']))
        {
            $post_type = implode("','", (array) $options['post_type']);
            $where .= " AND p.post_type IN ('$post_type')";
        }
        if (isset($options['post_status']))
        {
            $post_status = implode("','", (array) $options['post_status']);
            $where .= " AND p.post_status IN ('$post_status')";
        }

        $sql = "
        SELECT DISTINCT p.ID
        FROM {$wpdb->prefix}cfs_fields f
        INNER JOIN {$wpdb->prefix}cfs_values v ON v.field_id = f.id
        INNER JOIN $wpdb->posts p ON p.ID = v.post_id
        INNER JOIN $wpdb->postmeta m ON m.meta_id = v.meta_id
        WHERE f.type IN ('relationship') AND $where";

        $results = $wpdb->get_results($sql);
        $output = array();

        foreach ($results as $result)
        {
            $output[] = $result->ID;
        }
        return $output;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_labels
    *
    *    @author Matt Gibbs
    *    @since 1.3.3
    *
    *-------------------------------------------------------------------------------------*/

    function get_labels($field_name = false, $post_id = false)
    {
        global $post, $wpdb;

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Get all field groups for this post
        $group_ids = $this->parent->get_matching_groups($post_id, true);

        $labels = array();

        if (!empty($group_ids))
        {
            $group_ids = implode(',', array_keys($group_ids));
            $results = $wpdb->get_results("SELECT name, label FROM {$wpdb->prefix}cfs_fields WHERE post_id IN ($group_ids) ORDER BY weight");
            foreach ($results as $result)
            {
                if (empty($field_name))
                {
                    $labels[$result->name] = $result->label;
                }
                elseif ($result->name == $field_name)
                {
                    $labels = $result->label;
                }
            }
        }
        return $labels;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    apply_value_filters
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function apply_value_filters($field, $value, $options)
    {
        if (false !== $options->for_input)
        {
            $value = $this->parent->fields[$field->type]->format_value_for_input($value, $field);
        }
        else
        {
            $value = $this->parent->fields[$field->type]->format_value_for_api($value, $field);
        }

        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_input_fields
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get_input_fields($group_id = false, $parent_id = false, $field_id = false)
    {
        global $post, $wpdb;

        $values = $this->get_fields($post->ID, array('for_input' => true));

        $where = 'WHERE 1';
        $where .= (false !== $group_id) ? " AND post_id = $group_id" : '';
        $where .= (false !== $parent_id) ? " AND parent_id = $parent_id" : '';
        $where .= (false !== $field_id) ? " AND id = $field_id" : '';

        $fields = array();

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cfs_fields $where ORDER BY weight");

        foreach ($results as $field)
        {
            // Unserialize the options
            $field->options = (@unserialize($field->options)) ? unserialize($field->options) : array();

            // If no field value exists, set it to NULL
            $field->value = isset($values[$field->id]) ? $values[$field->id] : null;

            if (isset($field->options['default_value']) && empty($field->value))
            {
                $field->value = $field->options['default_value'];
            }

            $fields[$field->id] = $field;
        }

        return $fields;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save_fields
    *
    *    @author Matt Gibbs
    *    @since 1.1.3
    *
    *-------------------------------------------------------------------------------------*/

    function save_fields($field_data = array(), $post_data = array(), $options = array())
    {
        global $wpdb;

        $defaults = array(
            'raw_input' => false,
        );
        $options = (object) array_merge($defaults, $options);

        // create post if the ID is missing
        if (empty($post_data['ID']))
        {
            $post_defaults = array(
                'post_title' => 'Untitled',
                'post_content' => '',
                'post_content_filtered' => '',
                'post_excerpt' => '',
                'to_ping' => '',
                'pinged' => '',
            );
            $post_data = array_merge($post_defaults, $post_data);
            $post_id = wp_insert_post($post_data);
        }
        else
        {
            $post_id = $post_data['ID'];

            if (1 < count($post_data))
            {
                $wpdb->update($wpdb->posts, $post_data, array('ID' => $post_id));
            }
        }

        // Get all field groups for this post
        $group_ids = $this->parent->get_matching_groups($post_id, true);

        if (!empty($group_ids))
        {
            $parent_fields = array();
            $group_ids = implode(',', array_keys($group_ids));
            $results = $wpdb->get_results("SELECT id, type, parent_id, name FROM {$wpdb->prefix}cfs_fields WHERE post_id IN ($group_ids) ORDER BY weight");
            foreach ($results as $result)
            {
                $fields[$result->id] = $result;

                // Store lookup values for the recursion
                $field_id_lookup[$result->parent_id . ':' . $result->name] = $result->id;

                // Store parent fields separately
                if (0 == (int) $result->parent_id)
                {
                    $parent_fields[$result->name] = $result->id;
                }
            }
        }

        // If this is an API call, flatten the data!
        if (false === $options->raw_input)
        {
            // Remove the parent field data if using $cfs->save()
            $field_ids = array();

            foreach ($field_data as $field_name => $junk)
            {
                $field_ids[] = (int) $parent_fields[$field_name];
            }

            $field_ids = implode(',', $field_ids);

            $sql = "
            DELETE v, m
            FROM {$wpdb->prefix}cfs_values v
            LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
            WHERE v.post_id = '$post_id' AND (v.field_id IN ($field_ids) OR v.base_field_id IN ($field_ids))";
            $wpdb->query($sql);
        }
        else
        {
            // If saving raw input, delete existing postdata
            $sql = "
            DELETE v, m
            FROM {$wpdb->prefix}cfs_values v
            LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
            WHERE v.post_id = '$post_id'";
            $wpdb->query($sql);
        }

        // Save recursively
        $field_data = stripslashes_deep($field_data);

        foreach ($field_data as $field_id => $field_array)
        {
            $this->save_fields_recursive(
                array(
                    'field_id' => $field_id,
                    'field_array' => $field_array,
                    'post_id' => $post_id,
                    'parent_id' => 0,
                    'all_fields' => $fields,
                    'hierarchy' => array(),
                    'raw_input' => $options->raw_input,
                    'field_id_lookup' => $field_id_lookup,
                    'weight' => 0,
                    'depth' => 0,
                )
            );
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save_fields_recursive
    *
    *    @author Matt Gibbs
    *    @since 1.5.0
    *
    *-------------------------------------------------------------------------------------*/

    function save_fields_recursive($params)
    {
        global $wpdb;

        $field_type = 'loop';
        $field_id = $params['field_id'];
        $field_array = (array) $params['field_array'];

        if (0 == $params['depth'] % 2)
        {
            // If not raw_input, then field_id is actually the field name, and
            // we need to lookup the ID from the "field_id_lookup" array
            if (false === $params['raw_input'])
            {
                $field_name = $field_id;
                $field_id = (int) $params['field_id_lookup'][$params['parent_id'] . ':' . $field_name];
            }

            $field_type = $params['all_fields'][$field_id]->type;
        }

        // We've found the values
        if (isset($field_array['value']) || 'loop' != $field_type)
        {
            $values = isset($field_array['value']) ? $field_array['value'] : $field_array;

            // Trigger the pre_save hook
            $values = $this->parent->fields[$field_type]->pre_save($values);

            $sub_weight = 0;

            foreach ((array) $values as $value)
            {
                // Insert into postmeta
                $data = array(
                    'post_id' => $params['post_id'],
                    'meta_key' => $params['all_fields'][$field_id]->name,
                    'meta_value' => $value,
                );

                $wpdb->insert($wpdb->postmeta, $data);
                $meta_id = $wpdb->insert_id;

                // Get the top-level field ID from the hierarchy array
                $base_field_id = empty($params['hierarchy']) ? 0 : $params['hierarchy'][0];

                // Insert into cfs_values
                $data = array(
                    'field_id' => $field_id,
                    'meta_id' => $meta_id,
                    'post_id' => $params['post_id'],
                    'base_field_id' => $base_field_id,
                    'hierarchy' => implode(':', $params['hierarchy']),
                    'weight' => $params['weight'],
                    'sub_weight' => $sub_weight,
                );

                $wpdb->insert($wpdb->prefix . 'cfs_values', $data);
                $sub_weight++;
            }
        }
        // Keep recursing
        else
        {
            foreach ($field_array as $sub_field_id => $sub_field_array)
            {
                $new_params = $params;
                $new_params['field_array'] = $sub_field_array;
                $new_params['field_id'] = $sub_field_id;
                $new_params['weight'] = $field_id;
                $new_params['depth']++;

                // If not raw_input, then sub_field_id is actually the field name
                if (false === $params['raw_input'])
                {
                    if (0 == $new_params['depth'] % 2)
                    {
                        $sub_field_id = $params['field_id_lookup'][$new_params['parent_id'] . ':' . $sub_field_id];
                    }
                    else
                    {
                        $new_params['parent_id'] = $field_id;
                    }
                }

                if (empty($new_params['hierarchy']))
                {
                    $new_params['hierarchy'][] = $field_id;
                }

                $new_params['hierarchy'][] = $sub_field_id;
                $this->save_fields_recursive($new_params);
            }
        }
    }
}
