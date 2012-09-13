<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: http://uproot.us/custom-field-suite/
Description: Visually create and manage custom fields.
Version: 1.6.9
Author: Matt Gibbs
Author URI: http://uproot.us/
License: GPL
Copyright: Matt Gibbs
*/

$cfs = new Cfs();
$cfs->version = '1.6.9';

class Cfs
{
    public $dir;
    public $url;
    public $version;
    public $fields;
    public $used_types;
    public $api;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function __construct()
    {
        $this->dir = (string) dirname(__FILE__);
        $this->url = plugins_url('custom-field-suite');
        $this->used_types = array();

        // load the api
        include($this->dir . '/core/api.php');
        $this->api = new cfs_Api($this);

        // add actions
        add_action('init', array($this, 'init'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('save_post', array($this, 'save_post'));
        add_action('delete_post', array($this, 'delete_post'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        // ajax handlers
        add_action('wp_ajax_cfs_ajax_handler', array($this, 'ajax_handler'));

        // 3rd party hooks
        add_action('gform_post_submission', array($this, 'gform_handler'), 10, 2);
        add_action('icl_make_duplicate', array($this, 'wpml_handler'), 10, 4);

        // add translations
        load_plugin_textdomain('cfs', false, 'custom-field-suite/lang');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    init
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function init()
    {
        // perform upgrades
        include($this->dir . '/core/upgrade.php');

        // get all available field types
        $this->fields = $this->get_field_types();

        // customize the table header
        add_filter('manage_edit-cfs_columns', array($this, 'cfs_columns'));

        $labels = array(
            'name' => __('Field Groups', 'cfs'),
            'singular_name' => __('Field Group', 'cfs'),
            'add_new' => __('Add New', 'cfs'),
            'add_new_item' => __('Add New Field Group', 'cfs'),
            'edit_item' =>  __('Edit Field Group', 'cfs'),
            'new_item' => __('New Field Group', 'cfs'),
            'view_item' => __('View Field Group', 'cfs'),
            'search_items' => __('Search Field Groups', 'cfs'),
            'not_found' =>  __('No Field Groups found', 'cfs'),
            'not_found_in_trash' => __('No Field Groups found in Trash', 'cfs'),
        );

        register_post_type('cfs', array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'page',
            'hierarchical' => false,
            'supports' => array('title'),
        ));
    }


    /*--------------------------------------------------------------------------------------
    *
    *    cfs_columns
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function cfs_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'cfs'),
        );
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_field_types
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get_field_types()
    {
        // include the parent field type
        include($this->dir . '/core/fields/field.php');

        $field_types = array(
            'text' => $this->dir . '/core/fields/text.php',
            'textarea' => $this->dir . '/core/fields/textarea.php',
            'wysiwyg' => $this->dir . '/core/fields/wysiwyg.php',
            'date' => $this->dir . '/core/fields/date/date.php',
            'color' => $this->dir . '/core/fields/color/color.php',
            'true_false' => $this->dir . '/core/fields/true_false.php',
            'select' => $this->dir . '/core/fields/select.php',
            'relationship' => $this->dir . '/core/fields/relationship.php',
            'user' => $this->dir . '/core/fields/user.php',
            'file' => $this->dir . '/core/fields/file.php',
            'loop' => $this->dir . '/core/fields/loop.php',
        );

        // support custom field types
        $field_types = apply_filters('cfs_field_types', $field_types);

        foreach ($field_types as $type => $path)
        {
            $class_name = 'cfs_' . ucwords($type);

            // Allow for multiple classes per file
            if (!class_exists($class_name))
            {
                include_once($path);
            }

            $field_types[$type] = new $class_name($this);
        }

        return $field_types;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get_matching_groups
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get_matching_groups($post_id, $skip_roles = false)
    {
        global $wpdb, $current_user;

        // Get variables
        $matches = array();
        $post_id = (int) $post_id;
        $post_type = get_post_type($post_id);
        $user_roles = $current_user->roles;
        $term_ids = array();

        // Get all term ids associated with this post
        $sql = "
        SELECT tt.term_id
        FROM $wpdb->term_taxonomy tt
        INNER JOIN $wpdb->term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tr.object_id = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, $post_id));
        foreach ($results as $result)
        {
            $term_ids[] = $result->term_id;
        }

        // Get all rules
        $sql = "
        SELECT p.ID, p.post_title, m.meta_value AS rules
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = 'cfs_rules'
        WHERE p.post_status = 'publish'";
        $results = $wpdb->get_results($sql);

        $rule_types = array(
            'post_types' => $post_type,
            'user_roles' => $user_roles,
            'term_ids' => $term_ids,
            'post_ids' => $post_id,
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
    *    create_field
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function create_field($field)
    {
        $field = (object) $field;
        $this->fields[$field->type]->html($field);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    get field values from api
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function get($field_name = false, $post_id = false, $options = array())
    {
        if (false !== $field_name)
        {
            return $this->api->get_field($field_name, $post_id, $options);
        }
        return $this->api->get_fields($post_id, $options);
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
        return $this->api->get_labels($field_name, $post_id);
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
        return $this->api->get_reverse_related($post_id, $options, $deprecated);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save field values (and post data)
    *
    *    @author Matt Gibbs
    *    @since 1.1.4
    *
    *-------------------------------------------------------------------------------------*/

    function save($field_data = array(), $post_data = array(), $options = array())
    {
        return $this->api->save_fields($field_data, $post_data, $options);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    admin_head
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function admin_head()
    {
        if (in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php')))
        {
            include($this->dir . '/core/actions/admin_head.php');
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    admin_footer
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function admin_footer()
    {
        if (isset($GLOBALS['post_type']) && 'cfs' == $GLOBALS['post_type'] && 'edit.php' == $GLOBALS['pagenow'])
        {
            include($this->dir . '/core/actions/admin_footer.php');
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    add_meta_boxes
    *
    *    @author Matt Gibbs
    *    @since 1.6.6
    *
    *-------------------------------------------------------------------------------------*/

    function add_meta_boxes()
    {
        add_meta_box('cfs_fields', __('Fields', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'fields'));
        add_meta_box('cfs_rules', __('Placement Rules', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'rules'));
        add_meta_box('cfs_extras', __('Extras', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'extras'));
    }


    /*--------------------------------------------------------------------------------------
    *
    *    admin_menu
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function admin_menu()
    {
        add_object_page(__('Field Groups', 'cfs'), __('Field Groups', 'cfs'), 'manage_options', 'edit.php?post_type=cfs');
        add_submenu_page('edit.php?post_type=cfs', __('Tools', 'cfs'), __('Tools', 'cfs'), 'manage_options', 'cfs-tools', array($this, 'page_tools'));
    }


    /*--------------------------------------------------------------------------------------
    *
    *    save_post
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function save_post($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        {
            return $post_id;
        }

        if (!isset($_POST['cfs']['save']))
        {
            return $post_id;
        }

        if (wp_is_post_revision($post_id))
        {
            $post_id = wp_is_post_revision($post_id);
        }

        if (wp_verify_nonce($_POST['cfs']['save'], 'cfs_save_fields'))
        {
            include($this->dir . '/core/actions/save_fields.php');
        }
        elseif (wp_verify_nonce($_POST['cfs']['save'], 'cfs_save_input'))
        {
            $field_data = isset($_POST['cfs']['input']) ? $_POST['cfs']['input'] : array();
            $post_data = array('ID' => $_POST['ID']);
            $options = array('format' => 'input');
            $this->save($field_data, $post_data, $options);
        }

        return $post_id;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    delete_post
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function delete_post($post_id)
    {
        global $wpdb;

        $post_id = (int) $post_id;
        $table = ('cfs' == get_post_type($post_id)) ? 'cfs_fields' : 'cfs_values';
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}{$table} WHERE post_id = %d", $post_id));

        return true;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    meta_box
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function meta_box($post, $metabox)
    {
        $box = $metabox['args']['box'];
        include($this->dir . "/core/admin/meta_box_$box.php");
    }


    /*--------------------------------------------------------------------------------------
    *
    *    field_html
    *
    *    @author Matt Gibbs
    *    @since 1.0.3
    *
    *-------------------------------------------------------------------------------------*/

    function field_html($field)
    {
        include($this->dir . '/core/admin/field_html.php');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    page_tools
    *
    *    @author Matt Gibbs
    *    @since 1.6.3
    *
    *-------------------------------------------------------------------------------------*/

    function page_tools()
    {
        include($this->dir . '/core/admin/page_tools.php');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    ajax_handler
    *
    *    @author Matt Gibbs
    *    @since 1.6.1
    *
    *-------------------------------------------------------------------------------------*/

    function ajax_handler()
    {
        global $wpdb;

        if (is_admin())
        {
            // Export field groups
            if ('export' == $_POST['action_type'])
            {
                $post_ids = array();
                $field_groups = array();
                foreach ($_POST['field_groups'] as $post_id)
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

                echo json_encode($field_groups);
            }
            // Import field groups
            elseif ('import' == $_POST['action_type'])
            {
                $code = json_decode(stripslashes($_POST['import_code']));

                if (!empty($code))
                {
                    // Collect stats
                    $stats = array();

                    // Get all existing field group names
                    $existing_groups = $wpdb->get_col("SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'cfs'");

                    // Loop through field groups
                    foreach ($code as $group_id => $group)
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

                    if (!empty($stats['imported']))
                    {
                        echo '<div>' . __('Imported', 'cfs') . ': ' . implode(', ', $stats['imported']) . '</div>';
                    }
                    if (!empty($stats['skipped']))
                    {
                        echo '<div>' . __('Skipped', 'cfs') . ': ' . implode(', ', $stats['skipped']) . '</div>';
                    }
                }
                else
                {
                    echo '<div>' . __('Nothing to import', 'cfs') . '</div>';
                }
            }
            // Sync custom fields
            elseif ('sync' == $_POST['action_type'])
            {
                if (isset($_POST['field_groups']))
                {
                    $group_ids = (array) $_POST['field_groups'];
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

                    echo 'Sync successful';
                }
                else
                {
                    echo '<div>' . __('No field groups selected', 'cfs') . '</div>';
                }
            }
        }

        die();
    }


    /*--------------------------------------------------------------------------------------
    *
    *    gform_handler (gravity forms)
    *
    *    @author Matt Gibbs
    *    @since 1.3.0
    *
    *-------------------------------------------------------------------------------------*/

    function gform_handler($entry, $form)
    {
        global $wpdb;

        // get the form id
        $form_id = $entry['form_id'];

        // see if any field groups use this form id
        $field_groups = array();
        $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'cfs_extras'");
        foreach ($results as $result)
        {
            $meta_value = unserialize($result->meta_value);
            $meta_value = $meta_value['gforms'];

            if ($form_id == $meta_value['form_id'])
            {
                $fields = array();
                $all_fields = $wpdb->get_results("SELECT name, label FROM {$wpdb->prefix}cfs_fields WHERE post_id = '{$result->post_id}'");
                foreach ($all_fields as $field)
                {
                    $fields[$field->label] = $field->name;
                }

                $field_groups[$result->post_id] = array(
                    'post_type' => $meta_value['post_type'],
                    'fields' => $fields,
                );
            }
        }

        // If there's some matching groups, parse the GF field data
        if (!empty($field_groups))
        {
            $form_data = array();

            // get submitted fields
            foreach ($form['fields'] as $field)
            {
                $field_id = $field['id'];

                // handle fields with children
                if (!empty($field['inputs']))
                {
                    $values = array();

                    foreach ($field['inputs'] as $sub_field)
                    {
                        $sub_field_value = $entry[$sub_field['id']];

                        if (!empty($sub_field_value))
                        {
                            $values[] = $sub_field_value;
                        }
                    }
                    $value = implode("\n", $values);
                }
                elseif ('multiselect' == $field['type'])
                {
                    $value = explode(',', $entry[$field_id]);
                }
                else
                {
                    $value = $entry[$field_id];
                }

                $form_data[$field['label']] = $value;
            }
        }

        foreach ($field_groups as $post_id => $data)
        {
            $field_data = array();
            $intersect = array_intersect_key($form_data, $data['fields']);
            foreach ($intersect as $key => $field_value)
            {
                $field_name = $data['fields'][$key];
                $field_data[$field_name] = $field_value;
            }

            $post_data = array(
                'post_type' => $data['post_type'],
            );
            if (isset($entry['post_id']))
            {
                $post_data['ID'] = $entry['post_id'];
            }

            // save data
            $this->save($field_data, $post_data);
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    wpml_handler
    *
    *    Properly copy CFS fields on WPML post duplication
    *    Requires WPML 2.6.0+
    *
    *    @author Matt Gibbs
    *    @since 1.6.8
    *
    *-------------------------------------------------------------------------------------*/

    function wpml_handler($master_id, $lang, $post_data, $duplicate_id)
    {
        $field_data = $this->get(false, $master_id, array('format' => 'raw'));
        $this->save($field_data, array('ID' => $duplicate_id));
    }
}
