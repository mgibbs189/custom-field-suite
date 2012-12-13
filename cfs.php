<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: https://uproot.us/
Description: Visually add custom fields to your WordPress edit pages.
Version: 1.7.9
Author: Matt Gibbs
Author URI: https://uproot.us/
License: GPL2
*/

$cfs = new cfs();

class cfs
{
    public $dir;
    public $url;
    public $version;
    public $used_types;
    public $fields;
    public $form;
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
        $this->version = '1.7.9';
        $this->dir = (string) dirname(__FILE__);
        $this->url = plugins_url('custom-field-suite');
        $this->used_types = array();

        include($this->dir . '/core/classes/api.php');
        include($this->dir . '/core/classes/upgrade.php');
        include($this->dir . '/core/classes/field.php');
        include($this->dir . '/core/classes/form.php');
        include($this->dir . '/core/classes/third_party.php');

        // load classes
        $this->api = new cfs_api($this);
        $this->form = new cfs_form($this);
        $this->third_party = new cfs_third_party($this);

        // add actions
        add_action('init', array($this, 'init'));
        add_action('admin_head', array($this, 'admin_head'));
        add_action('admin_footer', array($this, 'admin_footer'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('save_post', array($this, 'save_post'));
        add_action('delete_post', array($this, 'delete_post'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('wp_ajax_cfs_ajax_handler', array($this, 'ajax_handler'));

        // add translations
        load_plugin_textdomain('cfs', false, 'custom-field-suite/languages');
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
        $upgrade = new cfs_upgrade($this->version);

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

        do_action('cfs_init');
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
            $class_name = 'cfs_' . $type;

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
        $field_info = $this->api->get_field_info($field_name, $post_id);

        if (false !== $field_name)
        {
            return $field_info[$field_name]['label'];
        }
        else
        {
            $output = array();

            foreach ($field_info as $name => $field_data)
            {
                $output[$name] = $field_data['label'];
            }

            return $output;
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

    function get_reverse_related($post_id, $options = array())
    {
        return $this->api->get_reverse_related($post_id, $options);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    form
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    function form($options = array())
    {
        return $this->form->create_form($options);
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
            include($this->dir . '/core/admin/admin_head.php');
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
            include($this->dir . '/core/admin/admin_footer.php');
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
        add_submenu_page('edit.php?post_type=cfs', __('Add-ons', 'cfs'), __('Add-ons', 'cfs'), 'manage_options', 'cfs-addons', array($this, 'page_addons'));
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
            $fields = isset($_POST['cfs']['fields']) ? $_POST['cfs']['fields'] : array();
            $rules = isset($_POST['cfs']['rules']) ? $_POST['cfs']['rules'] : array();
            $extras = isset($_POST['cfs']['extras']) ? $_POST['cfs']['extras'] : array();

            $this->api->save_field_group(array(
                'post_id' => $post_id,
                'fields' => $fields,
                'rules' => $rules,
                'extras' => $extras,
            ));
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
    *    page_addons
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    function page_addons()
    {
        include($this->dir . '/core/admin/page_addons.php');
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
            $ajax = new cfs_ajax();

            if ('import' == $ajax_method)
            {
                $options = array(
                    'import_code' => json_decode(stripslashes($_POST['import_code'])),
                );
                echo $ajax->import($options);
            }
            elseif ('export' == $ajax_method)
            {
                echo json_encode($ajax->export($_POST));
            }
            elseif (method_exists($ajax, $ajax_method))
            {
                echo $ajax->$ajax_method($_POST);
            }
            exit;
        }
    }
}
