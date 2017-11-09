<?php

class cfs_third_party
{

    public function __construct() {

        // Post Type Switcher - http://wordpress.org/plugins/post-type-switcher/
        add_filter( 'pts_post_type_filter', array( $this, 'pts_post_type_filter' ) );

        // WPML - http://wpml.org/
        add_action( 'icl_make_duplicate', array( $this, 'wpml_handler' ), 10, 4 );

        // Duplicate Post - http://wordpress.org/plugins/duplicate-post/
        add_action( 'dp_duplicate_post', array( $this, 'duplicate_post' ), 20, 2 );
        add_action( 'dp_duplicate_page', array( $this, 'duplicate_post' ), 20, 2 );

        // Polylang - https://wordpress.org/plugins/polylang/
        add_filter( 'pll_copy_post_metas', array( $this, 'polylang' ), 10, 4 );
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
    function duplicate_post( $new_post_id, $post ) {
        // If the old post's ID is present in any group rules, the new ID has to be added also
        $this->update_rules_with_new_post_id( $post->ID, $new_post_id );

        $field_data = CFS()->get( false, $post->ID, array( 'format' => 'raw' ) );

        if ( is_array( $field_data ) && count( $field_data ) !== 0 ) {
            // Delete meta fields belonging to CFS from the new post
            $this->delete_meta_fields_from_new_post( $field_data, $new_post_id );

            // Copy CFS fields
            $post_data = array( 'ID' => $new_post_id );
            CFS()->save( $field_data, $post_data );
        }
    }


    function polylang( $keys, $sync, $from, $to ) {
        global $polylang;

        // Have to respect polylang sync options
        if ( ! $sync || in_array( 'post_meta', $polylang->options['sync'] ) ) {
            // If the old post's ID is present in any groups' rules, the new ID has to be added also
            $this->update_rules_with_new_post_id( $from, $to );

            $field_data = CFS()->get( false, $from, array( 'format' => 'raw' ) );

            if ( is_array( $field_data ) && count( $field_data ) !== 0 ) {
                // Delete meta fields belonging to CFS from the new post
                // Also get all their names
                $cfs_meta_keys = $this->delete_meta_fields_from_new_post( $field_data, $to );

                // Filter those names out of the array passed from polylang
                $keys = array_diff( $keys, $cfs_meta_keys );

                // Copy CFS fields
                $post_data = array( 'ID' => $to );
                CFS()->save( $field_data, $post_data );
            }
        }

        return $keys;
    }


    private function update_rules_with_new_post_id( $old_post_id, $new_post_id ) {
        $field_groups = CFS()->field_group->load_field_groups();

        foreach ( $field_groups as $id => $group ) {
            if ( $group['rules']['post_ids'] && in_array( $old_post_id, $group['rules']['post_ids']['values'] ) ) {
                $group['rules']['post_ids']['values'][] = (string) $new_post_id;
                update_post_meta( $id, 'cfs_rules', $group['rules'] );
            }
        }

        // Cache has to be cleared after changing group rules
        unset( CFS()->field_group->cache['field_groups'] );
    }


    /**
     * Recursively delete meta fields from duplicated post
     * @param array $field_data
     * @param int $new_post_id
     */
    private function delete_meta_fields_from_new_post( $field_data, $new_post_id ) {
        $meta_keys = array();

        foreach ( $field_data as $key => $value ) {
            if ( ! is_array( $value ) ) { // Simple value
                delete_post_meta( $new_post_id, $key, $value );
                $meta_keys[] = $key;
            }
            elseif ( isset( $value[0] ) ) { // Indexed array
                foreach ( $value as $index => $inner_value ) {
                    if ( ! is_array( $inner_value ) ) {

                        // Simple value inside an indexed array (select, relationship, term, user)
                        delete_post_meta( $new_post_id, $key, $inner_value );
                        $meta_keys[] = $key;
                    } else {

                        // Array inside an indexed array - loop iteration
                        $inner_meta_keys = $this->delete_meta_fields_from_new_post( $inner_value, $new_post_id );
                        $meta_keys = array_merge( $meta_keys, $inner_meta_keys );
                    }
                }
            } else { // Assotiative array - hyperlink field
                delete_post_meta( $new_post_id, $key, $value );
                $meta_keys[] = $key;
            }
        }
        return $meta_keys;
    }
}

new cfs_third_party();
