<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: https://uproot.us/
Description: Really simple custom field management.
Version: 1.7.5
Author: Matt Gibbs
Author URI: https://uproot.us/
License: GPL2
*/

$cfs = new Cfs();
$cfs->version = '1.7.5';

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
        include($this->dir . '/core/classes/api.php');
        $this->api = new cfs_Api($this);

        // add actions
        add_action('init', array($this, 'init'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('save_post', array($this, 'save_post'));
        add_action('delete_post', array($this, 'delete_post'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('wp_ajax_cfs_ajax_handler', array($this, 'ajax_handler'));
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
        include($this->dir . '/core/classes/upgrade.php');
        $upgrade = new cfs_Upgrade($this->version);

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
        include($this->dir . '/core/classes/field.php');

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
        $page_template = get_post_meta($post_id, '_wp_page_template', true);
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
        add_object_page(__('Field Groups', 'cfs'), __('Field Groups', 'cfs'), 'manage_options', 'edit.php?post_type=cfs', null, $this->url . '/images/logo-small.png');
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
            $field_groups = isset($_POST['cfs']['field_groups']) ? $_POST['cfs']['field_groups'] : array();
            $field_data = isset($_POST['cfs']['input']) ? $_POST['cfs']['input'] : array();
            $post_data = array('ID' => $_POST['ID']);
            $options = array('format' => 'input', 'field_groups' => $field_groups);
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
    *    @since 1.7.5
    *
    *-------------------------------------------------------------------------------------*/

    function ajax_handler()
    {
        global $wpdb;

        $ajax_method = isset($_POST['action_type']) ? $_POST['action_type'] : false;

        if ($ajax_method && is_admin())
        {
            include($this->dir . '/core/classes/ajax.php');
            $ajax = new cfs_Ajax();

            if (method_exists($ajax, $ajax_method))
            {
                $ajax->$ajax_method();
            }
            exit;
        }
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

        if (!empty($field_data))
        {
            $this->save($field_data, array('ID' => $duplicate_id));
        }
    }
}
