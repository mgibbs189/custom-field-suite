<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: http://uproot.us/
Description: Visually add custom fields to your WordPress edit pages.
Version: 1.9.0
Author: Matt Gibbs
Author URI: http://uproot.us/

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

$cfs = new cfs();

class cfs
{
    public $dir;
    public $url;
    public $version;
    public $field_group;
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
        $this->version = '1.9.0';
        $this->dir = dirname(__FILE__);
        $this->url = plugins_url('custom-field-suite');

        include($this->dir . '/includes/classes/api.php');
        include($this->dir . '/includes/classes/upgrade.php');
        include($this->dir . '/includes/classes/field.php');
        include($this->dir . '/includes/classes/field_group.php');
        include($this->dir . '/includes/classes/session.php');
        include($this->dir . '/includes/classes/form.php');
        include($this->dir . '/includes/classes/third_party.php');

        // load classes
        $this->api = new cfs_api($this);
        $this->form = new cfs_form($this);
        $this->field_group = new cfs_field_group($this);
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

        if (!is_admin())
        {
            add_action('parse_query', array($this, 'parse_query'));
        }

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
            'text' =>               $this->dir . '/includes/fields/text.php',
            'textarea' =>           $this->dir . '/includes/fields/textarea.php',
            'wysiwyg' =>            $this->dir . '/includes/fields/wysiwyg.php',
            'date' =>               $this->dir . '/includes/fields/date/date.php',
            'color' =>              $this->dir . '/includes/fields/color/color.php',
            'true_false' =>         $this->dir . '/includes/fields/true_false.php',
            'select' =>             $this->dir . '/includes/fields/select.php',
            'relationship' =>       $this->dir . '/includes/fields/relationship.php',
            'user' =>               $this->dir . '/includes/fields/user.php',
            'file' =>               $this->dir . '/includes/fields/file.php',
            'loop' =>               $this->dir . '/includes/fields/loop.php',
        );

        // support custom field types
        $field_types = apply_filters('cfs_field_types', $field_types);

        foreach ($field_types as $type => $path)
        {
            $class_name = 'cfs_' . $type;

            // allow for multiple classes per file
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
        $defaults = array(
            'type' => 'text',
            'input_name' => '',
            'input_class' => '',
            'options' => array(),
            'value' => '',
        );

        $field = (object) array_merge($defaults, (array) $field);
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
    *    get_field_info
    *
    *    @author Matt Gibbs
    *    @since 1.8.3
    *
    *-------------------------------------------------------------------------------------*/

    function get_field_info($field_name = false, $post_id = false)
    {
        return $this->api->get_field_info($field_name, $post_id);
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

        if (!empty($field_name))
        {
            return $field_info['label'];
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
    *    display a front-end form
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    function form($params = array())
    {
        ob_start();

        $this->form->render($params);

        return ob_get_clean();
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
        $screen = get_current_screen();

        if ('post' == $screen->base)
        {
            include($this->dir . '/includes/admin/admin_head.php');
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
        $screen = get_current_screen();

        if ('edit' == $screen->base && 'cfs' == $screen->post_type)
        {
            include($this->dir . '/includes/admin/admin_footer.php');
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
        add_object_page(__('Field Groups', 'cfs'), __('Field Groups', 'cfs'), 'manage_options', 'edit.php?post_type=cfs', null, $this->url . '/assets/images/logo-small.png');
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
            return;
        }

        if (!isset($_POST['cfs']['save']))
        {
            return;
        }

        if (false !== wp_is_post_revision($post_id))
        {
            return;
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

        if ('cfs' != get_post_type($post_id))
        {
            $post_id = (int) $post_id;
            $wpdb->query("DELETE FROM {$wpdb->prefix}cfs_values WHERE post_id = $post_id");
        }

        return true;
    }


    /*--------------------------------------------------------------------------------------
    *
    *    parse_query
    *
    *    Make sure that $cfs is defined for template parts
    *    get_template_part() -> locate_template() -> load_template()
    *    load_template() extracts the $wp_query->query_vars array into variables,
    *        so we want to force it to create $cfs too.
    *
    *    @author Matt Gibbs
    *    @since 1.8.8
    *
    *-------------------------------------------------------------------------------------*/

    function parse_query($wp_query)
    {
        $wp_query->query_vars['cfs'] = $this;
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
        include($this->dir . "/includes/admin/meta_box_$box.php");
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
        include($this->dir . '/includes/admin/field_html.php');
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
        include($this->dir . '/includes/admin/page_tools.php');
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
        include($this->dir . '/includes/admin/page_addons.php');
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
            include($this->dir . '/includes/classes/ajax.php');
            $ajax = new cfs_ajax();

            if ('import' == $ajax_method)
            {
                $options = array(
                    'import_code' => json_decode(stripslashes($_POST['import_code']), true),
                );
                echo $this->field_group->import($options);
            }
            elseif ('export' == $ajax_method)
            {
                echo json_encode($this->field_group->export($_POST));
            }
            elseif ('reset' == $ajax_method)
            {
                if (current_user_can('manage_options'))
                {
                    $ajax->reset();

                    deactivate_plugins(plugin_basename(__FILE__));

                    echo admin_url('plugins.php');
                }
            }
            elseif (method_exists($ajax, $ajax_method))
            {
                echo $ajax->$ajax_method($_POST);
            }
            exit;
        }
    }
}
