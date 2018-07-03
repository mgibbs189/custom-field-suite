<?php

class cfs_third_party
{

    public function __construct() {

        // Post Type Switcher - http://wordpress.org/plugins/post-type-switcher/
        add_filter( 'pts_post_type_filter', array( $this, 'pts_post_type_filter' ) );

        // WPML - http://wpml.org/
        add_action( 'icl_make_duplicate', array( $this, 'wpml_handler' ), 10, 4 );

        // Duplicate Post - http://wordpress.org/plugins/duplicate-post/
        add_action( 'dp_duplicate_post', array( $this, 'duplicate_post' ), 100, 2 );
        add_action( 'dp_duplicate_page', array( $this, 'duplicate_post' ), 100, 2 );
    }


    /**
     * WPML support
     * 
     * Properly copy CFS fields on WPML post duplication (requires WPML 2.6+)
     * 
     * @param int $master_id 
     * @param string $lang 
     * @param array $post_data 
     * @param int $duplicate_id 
     * @since 1.6.8
     */
    function wpml_handler( $master_id, $lang, $post_data, $duplicate_id ) {
        $field_data = CFS()->get( false, $master_id, array( 'format' => 'raw' ) );

        if ( ! empty( $field_data ) ) {
            CFS()->save( $field_data, array( 'ID' => $duplicate_id ) );
        }
    }


    /**
     * Post Type Switcher support
     * @param array $args 
     * @return array
     * @since 1.8.1
     */
    function pts_post_type_filter( $args ) {
        global $current_screen;

        if ( 'cfs' == $current_screen->id ) {
            $args = array( 'public' => false, 'show_ui' => true );
        }

        return $args;
    }


    /**
     * Duplicate Post support
     * @param int $new_post_id
     * @param object $post
     * @since 2.0.0
     */
    function duplicate_post($new_post_id, $post) {
        $field_data = CFS()->get( false, $post->ID, array( 'format' => 'raw' ) );
        
        if ( is_array( $field_data ) ) {
            foreach ( $field_data as $key => $value ) {
                delete_post_meta( $new_post_id, $key, $value );
            }
        }
        
        $post_data = array( 'ID' => $new_post_id );
        CFS()->save( $field_data, $post_data );
    }
}

new cfs_third_party();
