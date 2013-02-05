<?php

class cfs_api
{
    public $parent;
    public $cache;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    public function __construct($parent)
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

    public function get_field($field_name, $post_id = false, $options = array())
    {
        global $post;

        $defaults = array(
            'format' => 'api', // "api", "input", or "raw"
        );
        $options = (object) array_merge($defaults, $options);

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Trigger get_fields if not in cache
        if (!isset($this->cache[$post_id][$options->format][$field_name]))
        {
            $fields = $this->get_fields($post_id, (array) $options);

            return isset($fields[$field_name]) ? $fields[$field_name] : null;
        }

        return $this->cache[$post_id][$options->format][$field_name];
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_fields
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    public function get_fields($post_id = false, $options = array())
    {
        global $post, $wpdb;

        $defaults = array(
            'format' => 'api', // "api", "input", or "raw"
        );
        $options = (object) array_merge($defaults, $options);

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Return cached results
        if (isset($this->cache[$post_id][$options->format]))
        {
            return $this->cache[$post_id][$options->format];
        }

        $fields = array();
        $field_data = array();

        // Get all field groups for this post
        $group_ids = $this->get_matching_groups($post_id, true);

        if (!empty($group_ids))
        {
            $results = $this->find_input_fields(array('post_id' => array_keys($group_ids)));
            foreach ($results as $result)
            {
                $result = (object) $result;
                $fields[$result->id] = $result;
            }

            if (!empty($fields))
            {
                // Make sure we're using active field groups
                $field_ids = implode(',', array_keys($fields));

                // Get all the field data
                $sql = "
                SELECT m.meta_value, v.field_id, v.hierarchy, v.weight
                FROM {$wpdb->prefix}cfs_values v
                INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
                WHERE v.field_id IN ($field_ids) AND v.post_id IN ($post_id)
                ORDER BY FIELD(v.field_id, $field_ids), v.weight, v.sub_weight";

                $results = $wpdb->get_results($sql);
                $num_rows = $wpdb->num_rows;

                $prev_hierarchy = '';
                $prev_field_id = '';
                $prev_item = '';

                foreach ($results as $row_count => $result)
                {
                    $field = $fields[$result->field_id];
                    $current_item = "{$result->hierarchy}.{$result->weight}.{$result->field_id}";

                    if (!empty($result->hierarchy))
                    {
                        // Format for API (field names)
                        if ('api' == $options->format || 'raw' == $options->format)
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
                        // Get the field name for "api" or "raw" formats
                        if ('api' == $options->format || 'raw' == $options->format)
                        {
                            $hierarchy = $field->name;
                        }
                        else
                        {
                            $hierarchy = $field->id;
                        }

                        $field_data[$hierarchy][] = $result->meta_value;
                    }

                    // Assemble the values
                    if ($current_item != $prev_item && '' != $prev_item) // call apply_value_filters on previous field
                    {
                        $this->assemble_value_array($field_data, $prev_hierarchy, $fields[$prev_field_id], false, $options);
                    }

                    if ($num_rows == ($row_count + 1)) // last row
                    {
                        $this->assemble_value_array($field_data, $hierarchy, $field, false, $options);
                    }

                    $prev_hierarchy = $hierarchy;
                    $prev_field_id = $field->id;
                    $prev_item = $current_item;
                }
            }
        }

        $this->cache[$post_id][$options->format] = $field_data;
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

    private function assemble_value_array(&$field_data, $hierarchy, $field, $value = false, $options = false)
    {
        $data = &$field_data;
        foreach (explode(':', $hierarchy) as $i)
        {
            $data = &$data[$i];
        }

        if (false !== $value)
        {
            $data = (array) $data;
            $data[] = $value;
        }
        else
        {
            $data = $this->apply_value_filters($field, $data, $options);
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    apply_value_filters
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    private function apply_value_filters($field, $value, $options)
    {
        $value = $this->parent->fields[$field->type]->prepare_value($value, $field);

        if ('api' == $options->format)
        {
            $value = $this->parent->fields[$field->type]->format_value_for_api($value, $field);
        }
        elseif ('input' == $options->format)
        {
            $value = $this->parent->fields[$field->type]->format_value_for_input($value, $field);
        }

        return $value;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_reverse_related
    *
    *    @author Matt Gibbs
    *    @since 1.4.4
    *
    *-------------------------------------------------------------------------------------*/

    public function get_reverse_related($post_id, $options = array())
    {
        global $wpdb;

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

        // Limit to relationship fields
        $results = $this->find_input_fields(array('field_type' => 'relationship'));
        if (!empty($results))
        {
            $field_ids = array();
            foreach ($results as $result)
            {
                $field_ids[] = $result['id'];
            }
            $where .= " AND v.field_id IN (" . implode(',', $field_ids) . ")";
        }

        $sql = "
        SELECT DISTINCT p.ID
        FROM {$wpdb->prefix}cfs_values v
        INNER JOIN $wpdb->posts p ON p.ID = v.post_id
        INNER JOIN $wpdb->postmeta m ON m.meta_id = v.meta_id
        WHERE $where";

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
    *    get_field_info
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    public function get_field_info($field_name = false, $post_id = false)
    {
        global $post, $wpdb;

        $post_id = empty($post_id) ? $post->ID : (int) $post_id;

        // Get all field groups for this post
        $group_ids = $this->get_matching_groups($post_id, true);
        $group_ids = array_keys($group_ids);

        $output = array();

        if (!empty($group_ids))
        {
            $results = $this->find_input_fields(array('post_id' => $group_ids));
            foreach ($results as $result)
            {
                if ($result['name'] == $field_name)
                {
                    $output = (array) $result;
                }
                elseif (empty($field_name))
                {
                    $output[$result['name']] = (array) $result;
                }
            }
        }

        return $output;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_input_fields
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    public function get_input_fields($params)
    {
        global $post, $wpdb;

        $defaults = array(
            'group_id' => false,
            'field_id' => false,
            'parent_id' => false,
        );
        $params = (object) array_merge($defaults, $params);
        $values = $this->get_fields($post->ID, array('format' => 'input'));

        $fields = array();

        $results = $this->find_input_fields(array(
            'post_id' => $params->group_id,
            'field_id' => $params->field_id,
            'parent_id' => $params->parent_id,
        ));

        foreach ($results as $field)
        {
            $field = (object) $field;

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
    *    find_input_fields
    *
    *    @author Matt Gibbs
    *    @since 1.8.4
    *
    *-------------------------------------------------------------------------------------*/

    public function find_input_fields($params)
    {
        global $wpdb;

        $defaults = array(
            'post_id' => array(),
            'field_id' => array(),
            'field_type' => array(),
            'field_name' => array(),
            'parent_id' => array(),
        );

        $params = (object) array_merge($defaults, $params);

        $where = '';
        if (!empty($params->post_id))
        {
            $post_ids = implode(',', (array) $params->post_id);
            $where .= " AND post_id IN ($post_ids)";
        }

        $output = array();

        // Cache the query (get fields)
        if (!isset($this->cache['cfs_fields'][$where]))
        {
            $results = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = 'cfs_fields' $where");
            $this->cache['cfs_fields'][$where] = $results;
        }
        else
        {
            $results = $this->cache['cfs_fields'][$where];
        }

        foreach ($results as $result)
        {
            $result = unserialize($result->meta_value);

            if (!empty($result))
            {
                foreach ($result as $field)
                {
                    if (empty($params->field_id) || in_array($field['id'], (array) $params->field_id))
                    {
                        if (empty($params->parent_id) || in_array($field['parent_id'], (array) $params->parent_id))
                        {
                            if (empty($params->field_type) || in_array($field['type'], (array) $params->field_type))
                            {
                                if (empty($params->field_name) || in_array($field['name'], (array) $params->field_name))
                                {
                                    $output[] = $field;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $output;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    array_orderby
    *
    *    @description MySQL "ORDER BY" for PHP associative arrays
    *    @link http://php.net/manual/en/function.array-multisort.php#100534
    *    @since 1.8.4
    *
    *-------------------------------------------------------------------------------------*/

    private function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);

        if (!is_array($data))
        {
            return false;
        }

        $multisort_params = array();
        foreach ($args as $n => $field)
        {
            if (is_string($field))
            {
                ${"tmp_$n"} = array();
                foreach ($data as $key => $row)
                {
                    ${"tmp_$n"}[$key] = $row[$field];
                }
                $multisort_params[$n] = &${"tmp_$n"};
            }
        }

        $multisort_params[] = &$data;
        call_user_func_array('array_multisort', $multisort_params);
        return array_pop($multisort_params);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_matching_groups
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    public function get_matching_groups($post_id, $skip_roles = false)
    {
        global $wpdb, $current_user;

        // Get variables
        $matches = array();
        $post_id = (int) $post_id;
        $post_type = get_post_type($post_id);
        $page_template = get_post_meta($post_id, '_wp_page_template', true);
        $user_roles = $current_user->roles;

        // Cache the query (get rules)
        if (!isset($this->cache['cfs_rules']))
        {
            $sql = "
            SELECT p.ID, p.post_title, m.meta_value AS rules
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = 'cfs_rules'
            WHERE p.post_status = 'publish'";
            $results = $wpdb->get_results($sql);
            $this->cache['cfs_rules'] = $results;
        }
        else
        {
            $results = $this->cache['cfs_rules'];
        }

        $rule_types = array(
            'post_types' => $post_type,
            'user_roles' => $user_roles,
            'term_ids' => array(),
            'post_ids' => $post_id,
            'page_templates' => $page_template,
        );

        // Ignore user_roles if used within get_fields
        if (false !== $skip_roles)
        {
            unset($rule_types['user_roles']);
        }

        foreach ($results as $result)
        {
            $fail = false;
            $rules = unserialize($result->rules);

            foreach ($rule_types as $rule_type => $value)
            {
                if (isset($rules[$rule_type]))
                {
                    // Only lookup a post's term IDs if the rule exists
                    if ('term_ids' == $rule_type)
                    {
                        $sql = "
                        SELECT tt.term_id
                        FROM $wpdb->term_taxonomy tt
                        INNER JOIN $wpdb->term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tr.object_id = %d";
                        $value = $wpdb->get_col($wpdb->prepare($sql, $post_id));
                    }

                    $operator = (array) $rules[$rule_type]['operator'];
                    $in_array = (0 < count(array_intersect((array) $value, $rules[$rule_type]['values'])));
                    if (($in_array && '!=' == $operator[0]) || (!$in_array && '==' == $operator[0]))
                    {
                        $fail = true;
                    }
                }
            }

            if (!$fail)
            {
                $matches[$result->ID] = $result->post_title;
            }
        }

        // Allow for overrides
        return apply_filters('cfs_matching_groups', $matches, $post_id, $post_type);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save_fields
    *
    *    @author Matt Gibbs
    *    @since 1.1.3
    *
    *-------------------------------------------------------------------------------------*/

    public function save_fields($field_data = array(), $post_data = array(), $options = array())
    {
        global $wpdb;

        $defaults = array(
            'format' => 'api', // "api" or "input"
            'field_groups' => array(),
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
                clean_post_cache($post_id);
            }
        }

        // For input forms, get the group IDs from the HTTP POST
        // Otherwise, the field group might not match anymore (e.g. the taxonomy changed)
        if ('input' == $options->format)
        {
            $group_ids = $options->field_groups;
        }
        elseif ('api' == $options->format)
        {
            $group_ids = $this->get_matching_groups($post_id, true);
            $group_ids = array_keys($group_ids);
        }

        if (!empty($group_ids))
        {
            $parent_fields = array();
            $results = $this->find_input_fields(array('post_id' => $group_ids));
            foreach ($results as $result)
            {
                $result = (object) $result;
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
        if ('api' == $options->format)
        {
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
        elseif ('input' == $options->format)
        {
            // If saving raw input, delete existing postdata
            $results = $this->find_input_fields(array('post_id' => $group_ids));
            if (!empty($results))
            {
                $field_ids = array();
                foreach ($results as $result)
                {
                    $field_ids[] = $result['id'];
                }
                $field_ids = implode(',', $field_ids);

                $sql = "
                DELETE v, m
                FROM {$wpdb->prefix}cfs_values v
                LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
                WHERE v.post_id = '$post_id' AND v.field_id IN ($field_ids)";
                $wpdb->query($sql);
            }
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
                    'format' => $options->format,
                    'field_id_lookup' => $field_id_lookup,
                    'weight' => 0,
                    'depth' => 0,
                )
            );
        }

        // Clear the cache
        $this->cache[$post_id] = null;

        return $post_id;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save_fields_recursive
    *
    *    @author Matt Gibbs
    *    @since 1.5.0
    *
    *-------------------------------------------------------------------------------------*/

    private function save_fields_recursive($params)
    {
        global $wpdb;

        $field_type = 'loop';
        $field_id = $params['field_id'];
        $field_array = (array) $params['field_array'];

        if (0 == $params['depth'] % 2)
        {
            // If not raw input, then field_id is actually the field name, and
            // we need to lookup the ID from the "field_id_lookup" array
            if ('input' != $params['format'])
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
            $values = $this->parent->fields[$field_type]->pre_save($values, $params['all_fields'][$field_id]);

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

                // If not raw input, then sub_field_id is actually the field name
                if ('input' != $params['format'])
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


    /*--------------------------------------------------------------------------------------
    *
    *    save_field_group
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    function save_field_group($params = array())
    {
        global $wpdb;

        $post_id = $params['post_id'];

        /*---------------------------------------------------------------------------------------------
            Save fields
        ---------------------------------------------------------------------------------------------*/

        $weight = 0;
        $prev_fields = array();
        $current_field_ids = array();
        $next_field_id = (int) get_option('cfs_next_field_id');
        $existing_fields = get_post_meta($post_id, 'cfs_fields', true);

        if (!empty($existing_fields))
        {
            foreach ($existing_fields as $item)
            {
                $prev_fields[$item['id']] = $item['name'];
            }
        }

        $new_fields = array();

        foreach ($params['fields'] as $key => $field)
        {
            // Sanitize the field
            $field = stripslashes_deep($field);

            // Allow for field customizations
            $field = $this->parent->fields[$field['type']]->pre_save_field($field);

            // Set the parent ID
            $field['parent_id'] = empty($field['parent_id']) ? 0 : (int) $field['parent_id'];

            // Save empty array for fields without options
            $field['options'] = empty($field['options']) ? array() : $field['options'];

            // Use an existing ID if available
            if (0 < (int) $field['id'])
            {
                // We use this variable to check for deleted fields
                $current_field_ids[] = $field['id'];

                // Rename the postmeta key if necessary
                if ($field['name'] != $prev_fields[$field['id']])
                {
                    $wpdb->query(
                        $wpdb->prepare("
                            UPDATE {$wpdb->postmeta} m
                            INNER JOIN {$wpdb->prefix}cfs_values v ON v.meta_id = m.meta_id
                            SET meta_key = %s
                            WHERE v.field_id = %d",
                            $field['name'], $field['id']
                        )
                    );
                }
            }
            else
            {
                $field['id'] = $next_field_id;
                $next_field_id++;
            }

            $data = array(
                'id' => $field['id'],
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'notes' => $field['notes'],
                'parent_id' => $field['parent_id'],
                'weight' => $weight,
                'options' => $field['options'],
            );

            $new_fields[] = $data;

            $weight++;
        }

        // Save the fields
        update_post_meta($post_id, 'cfs_fields', $new_fields);

        // Update the field ID counter
        update_option('cfs_next_field_id', $next_field_id);

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
            if (!empty($params['rules'][$type]))
            {
                // Break apart the autocomplete string
                if ('post_ids' == $type)
                {
                    $params['rules'][$type] = explode(',', $params['rules'][$type]);
                }

                $data[$type] = array(
                    'operator' => $params['rules']['operator'][$type],
                    'values' => $params['rules'][$type],
                );
            }
        }

        update_post_meta($post_id, 'cfs_rules', $data);

        /*---------------------------------------------------------------------------------------------
            Save extras
        ---------------------------------------------------------------------------------------------*/

        update_post_meta($post_id, 'cfs_extras', $params['extras']);
    }
}
