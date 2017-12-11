<?php
/*
Plugin Name: Custom Field Suite
Plugin URI: http://customfieldsuite.com/
Description: Visually add custom fields to your WordPress edit pages.
Version: 2.5.12
Author: Matt Gibbs
Text Domain: cfs
Domain Path: /languages/
*/

class Custom_Field_Suite
{

    public $api;
    public $form;
    public $fields;
    public $field_group;
    private static $instance;


    function __construct() {

        // setup variables
        define( 'CFS_VERSION', '2.5.12' );
        define( 'CFS_DIR', dirname( __FILE__ ) );
        define( 'CFS_URL', plugins_url( 'custom-field-suite' ) );

        // get the gears turning
        include( CFS_DIR . '/includes/init.php' );
    }


    /**
     * Singleton
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }


    /**
     * Public API methods
     */
    function get( $field_name = false, $post_id = false, $options = array() ) {
        return CFS()->api->get( $field_name, $post_id, $options );
    }


    function get_field_info( $field_name = false, $post_id = false ) {
        return CFS()->api->get_field_info( $field_name, $post_id );
    }


    function get_reverse_related( $post_id, $options = array() ) {
        return CFS()->api->get_reverse_related( $post_id, $options );
    }


    function save( $field_data = array(), $post_data = array(), $options = array() ) {
        return CFS()->api->save_fields( $field_data, $post_data, $options );
    }


    function find_fields( $params = array() ) {
        return CFS()->api->find_input_fields( $params );
    }


    function form( $params = array() ) {
        ob_start();
        CFS()->form->render( $params );
        return ob_get_clean();
    }


    /**
     * Render a field's admin settings HTML
     */
    function field_html( $field ) {
        include( CFS_DIR . '/templates/field_html.php' );
    }


    /**
     * Trigger the field type "html" method
     */
    function create_field( $field ) {
        $defaults = array(
            'type'          => 'text',
            'input_name'    => '',
            'input_class'   => '',
            'options'       => array(),
            'value'         => '',
        );

        $field = (object) array_merge( $defaults, (array) $field );
        CFS()->fields[ $field->type ]->html( $field );
    }
}


function CFS() {
    return Custom_Field_Suite::instance();
}


$cfs = CFS();
