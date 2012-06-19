<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: http://uproot.us/custom-field-suite/
Description: Visually create and manage custom fields. CFS is a fork of the Advanced Custom Fields plugin.
Version: 1.5.5
Author: Matt Gibbs
Author URI: http://uproot.us/
License: GPL
Copyright: Matt Gibbs
*/

$cfs = new Cfs();
$cfs->version = '1.5.5';

class Cfs
{
    public $dir;
    public $url;
    public $siteurl;
    public $version;
    public $fields;
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
        $this->siteurl = get_bloginfo('url');

        // load the api
        include($this->dir . '/core/api.php');
        $this->api = new cfs_Api($this);

        // add actions
        add_action('init', array($this, 'init'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_print_scripts', array($this, 'admin_print_scripts'));
        add_action('save_post', array($this, 'save_post'));
        add_action('delete_post', array($this, 'delete_post'));

        // 3rd party hooks
        add_action('gform_post_submission', array($this, 'gform_handler'), 10, 2);

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

        include($this->dir . '/core/actions/init.php');
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
            include_once($path);
            $class_name = 'cfs_' . ucwords($type);
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

    function get_matching_groups($post_id, $is_public = false)
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
        if (false !== $is_public)
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

        return $matches;
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

    function get($field_name = false, $post_id = false)
    {
        if (false !== $field_name)
        {
            return $this->api->get_field($field_name, $post_id);
        }
        return $this->api->get_fields($post_id);
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

    function get_reverse_related($field_name, $post_id, $options = array())
    {
        return $this->api->get_reverse_related($field_name, $post_id, $options);
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
    *    admin_menu
    *
    *    @author Matt Gibbs
    *    @since 1.0.0
    *
    *-------------------------------------------------------------------------------------*/

    function admin_menu()
    {
        add_object_page('Field Groups', 'Field Groups', 'manage_options', 'edit.php?post_type=cfs');
        add_submenu_page('edit.php?post_type=cfs', 'Import', 'Import', 'manage_options', 'cfs-import', array($this, 'page_import'));
    }



    /*--------------------------------------------------------------------------------------
    *
    *    admin_print_scripts
    *
    *    @author Matt Gibbs
    *    @since 1.4.5
    *
    *-------------------------------------------------------------------------------------*/

    function admin_print_scripts()
    {
        if (isset($GLOBALS['post_type']) && 'cfs' == $GLOBALS['post_type'] && 'edit.php' == $GLOBALS['pagenow'])
        {
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
        }
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
            if (isset($_POST['cfs']['input']))
            {
                $field_data = $_POST['cfs']['input'];
                $post_data = array('ID' => $_POST['ID']);
                $options = array('raw_input' => true);
                $this->api->save_fields($field_data, $post_data, $options);
            }
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
    *    page_import
    *
    *    @author Matt Gibbs
    *    @since 1.3.4
    *
    *-------------------------------------------------------------------------------------*/

    function page_import()
    {
        include($this->dir . '/core/admin/page_import.php');
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
                if (null !== $field['inputs'])
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
}
