<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: http://customfieldsuite.com/
Description: Visually add custom fields to your WordPress edit pages.
Version: 2.3.9
Author: Matt Gibbs
Author URI: http://customfieldsuite.com/
Text Domain: cfs
Domain Path: /languages/
*/

class Custom_Field_Suite
{

    public $api;
    public $form;
    public $fields;
    public $field_group;
    public $third_party;
    private static $instance;


    function __construct() {

        // setup variables
        define( 'CFS_VERSION', '2.3.9' );
        define( 'CFS_DIR', dirname( __FILE__ ) );
        define( 'CFS_URL', plugins_url( 'custom-field-suite' ) );

        add_action( 'init', array( $this, 'init' ) );
    }


    /**
     * Initialize the singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Custom_Field_Suite;
        }
        return self::$instance;
    }


    /**
     * Prevent cloning
     */
    function __clone() {

    }


    /**
     * Prevent unserializing
     */
    function __wakeup() {

    }


    function init() {

        // i18n
        $this->load_textdomain();

        add_action( 'admin_head',               array( $this, 'admin_head' ) );
        add_action( 'admin_footer',             array( $this, 'admin_footer' ) );
        add_action( 'admin_menu',               array( $this, 'admin_menu' ) );
        add_action( 'save_post',                array( $this, 'save_post' ) );
        add_action( 'delete_post',              array( $this, 'delete_post' ) );
        add_action( 'add_meta_boxes',           array( $this, 'add_meta_boxes' ) );
        add_action( 'wp_ajax_cfs_ajax_handler', array( $this, 'ajax_handler' ) );

        // Force the $cfs variable
        if ( ! is_admin() ) {
            add_action( 'parse_query', array( $this, 'parse_query' ) );
        }

        foreach ( array( 'api', 'upgrade', 'field', 'field_group', 'session', 'form', 'third_party' ) as $f ) {
            include( CFS_DIR . "/includes/$f.php" );
        }

        $upgrade = new cfs_upgrade();

        // load classes
        $this->api = new cfs_api();
        $this->form = new cfs_form();
        $this->field_group = new cfs_field_group();
        $this->third_party = new cfs_third_party();
        $this->fields = $this->get_field_types();

        register_post_type( 'cfs', array(
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => false,
            'capability_type'   => 'page',
            'hierarchical'      => false,
            'supports'          => array( 'title' ),
            'query_var'         => false,
            'labels'            => array(
                'name'                  => __( 'Field Groups', 'cfs' ),
                'singular_name'         => __( 'Field Group', 'cfs' ),
                'add_new'               => __( 'Add New', 'cfs' ),
                'add_new_item'          => __( 'Add New Field Group', 'cfs' ),
                'edit_item'             => __( 'Edit Field Group', 'cfs' ),
                'new_item'              => __( 'New Field Group', 'cfs' ),
                'view_item'             => __( 'View Field Group', 'cfs' ),
                'search_items'          => __( 'Search Field Groups', 'cfs' ),
                'not_found'             => __( 'No Field Groups found', 'cfs' ),
                'not_found_in_trash'    => __( 'No Field Groups found in Trash', 'cfs' ),
            ),
        ));

        // customize the table header
        add_filter( 'manage_cfs_posts_columns', array( $this, 'cfs_columns' ) );
        add_action( 'manage_cfs_posts_custom_column', array( $this, 'cfs_column_content' ), 10, 2 );

        do_action( 'cfs_init' );
    }


    function load_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'cfs' );
        $mofile = WP_LANG_DIR . '/custom-field-suite/cfs-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            load_textdomain( 'cfs', $mofile );
        }
        else {
            load_plugin_textdomain( 'cfs', false, 'custom-field-suite/languages' );
        }
    }


    /**
     * Register field types
     * @since 1.0.0
     */
    function get_field_types() {

        // support custom field types
        $field_types = apply_filters( 'cfs_field_types', array(
            'text'          => CFS_DIR . '/includes/fields/text.php',
            'textarea'      => CFS_DIR . '/includes/fields/textarea.php',
            'wysiwyg'       => CFS_DIR . '/includes/fields/wysiwyg.php',
            'date'          => CFS_DIR . '/includes/fields/date/date.php',
            'color'         => CFS_DIR . '/includes/fields/color/color.php',
            'true_false'    => CFS_DIR . '/includes/fields/true_false.php',
            'select'        => CFS_DIR . '/includes/fields/select.php',
            'relationship'  => CFS_DIR . '/includes/fields/relationship.php',
            'user'          => CFS_DIR . '/includes/fields/user.php',
            'file'          => CFS_DIR . '/includes/fields/file.php',
            'loop'          => CFS_DIR . '/includes/fields/loop.php',
            'tab'           => CFS_DIR . '/includes/fields/tab.php',
        ) );

        foreach ( $field_types as $type => $path ) {
            $class_name = 'cfs_' . $type;

            // allow for multiple classes per file
            if ( !class_exists( $class_name ) ) {
                include_once( $path );
            }

            $field_types[ $type ] = new $class_name();
        }

        return $field_types;
    }


    /**
     * Generate input field HTML
     * @param object $field 
     * @since 1.0.0
     */
    function create_field( $field ) {
        $defaults = array(
            'type' => 'text',
            'input_name' => '',
            'input_class' => '',
            'options' => array(),
            'value' => '',
        );

        $field = (object) array_merge( $defaults, (array) $field );
        $this->fields[ $field->type ]->html( $field );
    }


    /**
     * Retrieve custom field values
     * @param mixed $field_name 
     * @param mixed $post_id 
     * @param array $options 
     * @return mixed
     * @since 1.0.0
     */
    function get( $field_name = false, $post_id = false, $options = array() ) {
        if ( false !== $field_name ) {
            return $this->api->get_field( $field_name, $post_id, $options );
        }

        return $this->api->get_fields( $post_id, $options );
    }


    /**
     * Get custom field properties (label, name, settings, etc.)
     * @param mixed $field_name 
     * @param mixed $post_id 
     * @return array
     * @since 1.8.3
     */
    function get_field_info( $field_name = false, $post_id = false ) {
        return $this->api->get_field_info( $field_name, $post_id );
    }


    /**
     * Retrieve reverse-related values (using the relationship field type)
     * @param int $post_id 
     * @param array $options 
     * @return array
     * @since 1.4.4
     */
    function get_reverse_related( $post_id, $options = array() ) {
        return $this->api->get_reverse_related( $post_id, $options );
    }


    /**
     * Save field values (and post data)
     * @param array $field_data 
     * @param array $post_data 
     * @param array $options 
     * @return int The post ID
     * @since 1.1.4
     */
    function save( $field_data = array(), $post_data = array(), $options = array() ) {
        return $this->api->save_fields( $field_data, $post_data, $options );
    }


    /**
     * Display a front-end form
     * @param array $params 
     * @return string The form HTML
     * @since 1.8.5
     */
    function form( $params = array() ) {
        ob_start();

        $this->form->render( $params );

        return ob_get_clean();
    }


    /**
     * admin_head
     * @since 1.0.0
     */
    function admin_head() {
        $screen = get_current_screen();

        if ( 'post' == $screen->base ) {
            include( CFS_DIR . '/templates/admin_head.php' );
        }
    }


    /**
     * admin_footer
     * @since 1.0.0
     */
    function admin_footer() {
        $screen = get_current_screen();

        if ( 'edit' == $screen->base && 'cfs' == $screen->post_type ) {
            include( CFS_DIR . '/templates/admin_footer.php' );
        }
    }


    /**
     * add_meta_boxes
     * @since 1.0.0
     */
    function add_meta_boxes() {
        add_meta_box( 'cfs_fields', __('Fields', 'cfs'), array( $this, 'meta_box' ), 'cfs', 'normal', 'high', array( 'box' => 'fields' ) );
        add_meta_box( 'cfs_rules', __('Placement Rules', 'cfs'), array( $this, 'meta_box' ), 'cfs', 'normal', 'high', array( 'box' => 'rules' ) );
        add_meta_box( 'cfs_extras', __('Extras', 'cfs'), array( $this, 'meta_box' ), 'cfs', 'normal', 'high', array( 'box' => 'extras' ) );
    }


    /**
     * admin_menu
     * @since 1.0.0
     */
    function admin_menu() {
        if ( false === apply_filters( 'cfs_disable_admin', false ) ) {
            add_object_page( __( 'Field Groups', 'cfs' ), __( 'Field Groups', 'cfs' ), 'manage_options', 'edit.php?post_type=cfs', null, CFS_URL . '/assets/images/logo-small.png' );
            add_submenu_page( 'edit.php?post_type=cfs', __( 'Add-ons', 'cfs' ), __( 'Add-ons', 'cfs' ), 'manage_options', 'cfs-addons', array( $this, 'page_addons' ) );
            add_submenu_page( 'edit.php?post_type=cfs', __( 'Tools', 'cfs' ), __( 'Tools', 'cfs' ), 'manage_options', 'cfs-tools', array( $this, 'page_tools' ) );
        }
    }


    /**
     * save_post
     * @param int $post_id 
     * @since 1.0.0
     */
    function save_post( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! isset( $_POST['cfs']['save'] ) ) {
            return;
        }

        if ( false !== wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( wp_verify_nonce( $_POST['cfs']['save'], 'cfs_save_fields' ) ) {
            $fields = isset( $_POST['cfs']['fields'] ) ? $_POST['cfs']['fields'] : array();
            $rules = isset( $_POST['cfs']['rules'] ) ? $_POST['cfs']['rules'] : array();
            $extras = isset( $_POST['cfs']['extras'] ) ? $_POST['cfs']['extras'] : array();

            $this->field_group->save( array(
                'post_id'   => $post_id,
                'fields'    => $fields,
                'rules'     => $rules,
                'extras'    => $extras,
            ) );
        }
    }


    /**
     * delete_post
     * @param int $post_id 
     * @return boolean
     * @since 1.0.0
     */
    function delete_post( $post_id ) {
        global $wpdb;

        if ( 'cfs' != get_post_type( $post_id ) ) {
            $post_id = (int) $post_id;
            $wpdb->query( "DELETE FROM {$wpdb->prefix}cfs_values WHERE post_id = $post_id" );
        }

        return true;
    }


    /**
     * meta_box
     * @param object $post 
     * @param array $metabox 
     * @since 1.0.0
     */
    function meta_box( $post, $metabox ) {
        $box = $metabox['args']['box'];
        include( CFS_DIR . "/templates/meta_box_$box.php" );
    }


    /**
     * field_html
     * @param object $field 
     * @since 1.0.3
     */
    function field_html( $field ) {
        include( CFS_DIR . '/templates/field_html.php' );
    }


    /**
     * page_tools
     * @since 1.6.3
     */
    function page_tools() {
        include( CFS_DIR . '/templates/page_tools.php' );
    }


    /**
     * page_addons
     * @since 1.8.0
     */
    function page_addons() {
        include( CFS_DIR . '/templates/page_addons.php' );
    }


    /**
     * ajax_handler
     * @since 1.7.5
     */
    function ajax_handler() {
        global $wpdb;

        $ajax_method = isset( $_POST['action_type'] ) ? $_POST['action_type'] : false;

        if ( $ajax_method && is_admin() ) {
            include( CFS_DIR . '/includes/ajax.php' );
            $ajax = new cfs_ajax();

            if ( 'import' == $ajax_method ) {
                $options = array(
                    'import_code' => json_decode( stripslashes( $_POST['import_code'] ), true ),
                );
                echo $this->field_group->import( $options );
            }
            elseif ('export' == $ajax_method) {
                echo json_encode( $this->field_group->export( $_POST ) );
            }
            elseif ('reset' == $ajax_method) {
                if ( current_user_can( 'manage_options' ) ) {
                    $ajax->reset();
                    deactivate_plugins( plugin_basename( __FILE__ ) );
                    echo admin_url( 'plugins.php' );
                }
            }
            elseif ( method_exists( $ajax, $ajax_method ) ) {
                echo $ajax->$ajax_method( $_POST );
            }
            exit;
        }
    }


    /**
     * Customize table columns on the Field Groups listing page
     * @since 1.0.0
     */
    function cfs_columns() {
        return array(
            'cb'            => '<input type="checkbox" />',
            'title'         => __( 'Title', 'cfs' ),
            'placement'     => __( 'Placement', 'cfs' ),
        );
    }


    /**
     * Populate the "Placement" column on the Field Groups listing page
     * @param string $column_name 
     * @param int $post_id 
     * @since 1.9.5
     */
    function cfs_column_content( $column_name, $post_id ) {
        if ( 'placement' == $column_name ) {
            global $wpdb;

            $labels = array(
                'post_types'        => __( 'Post Types', 'cfs' ),
                'user_roles'        => __( 'User Roles', 'cfs' ),
                'post_ids'          => __( 'Post IDs', 'cfs' ),
                'term_ids'          => __( 'Term IDs', 'cfs' ),
                'page_templates'    => __( 'Page Templates', 'cfs' ),
                'post_formats'      => __( 'Post Formats', 'cfs' )
            );

            $results = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = 'cfs_rules' LIMIT 1" );
            $results = empty( $results ) ? array() : unserialize( $results );

            foreach ( $results as $criteria => $values ) {
                $label = $labels[ $criteria ];
                $operator = ( '==' == $values['operator'] ) ? '=' : '!=';
                echo "<div>$label " . $operator . ' [' . implode( ' or ', $values['values'] ) . ']</div>';
            }
        }
    }


    /**
     * Make sure that $cfs exists for template parts
     * @since 1.8.8
     */
    function parse_query( $wp_query ) {
        $wp_query->query_vars['cfs'] = $this;
    }
}


// Backwards-compatibility
$cfs = CFS();


/**
 * Allow direct access to CFS classes
 */
function CFS() {
    return Custom_Field_Suite::instance();
}
