<?php

if ( defined( 'CFS_REVISIONS' ) && CFS_REVISIONS ) {
    new cfs_revision();
}

class cfs_revision
{

    function __construct() {
        add_action( 'save_post', [ $this, 'save_post' ] );
        add_action( 'wp_restore_post_revision', [ $this, 'wp_restore_post_revision' ], 10, 2 );
        add_action( 'wp_delete_post_revision', [ $this, 'wp_delete_post_revision' ] );

        add_filter( '_wp_post_revision_fields', [ $this, '_wp_post_revision_fields' ] );
        add_filter( '_wp_post_revision_field_cfs_postmeta', [ $this, '_wp_post_revision_field_postmeta' ], 10, 3 );
        add_filter( 'wp_save_post_revision_check_for_changes', [ $this, 'check_for_changes' ], 10, 3 );
    }


    /**
     * Register the revision variable
     * @see wp-includes/revision.php - wp_save_post_revision()
     */
    function _wp_post_revision_fields( $fields ) {
        $fields[ 'cfs_postmeta' ] = __( 'Post Meta' );
        return $fields;
    }


    /**
     * Generate the data for the "cfs_postmeta" variable
     * @see wp-admin/includes/ajax-actions - wp_ajax_revisions_data()
     */
    function _wp_post_revision_field_postmeta( $value = '', $column = 'cfs_postmeta', $post ) {
        $output = '';
        $fields = CFS()->get( false, $post->ID );
        $field_info = CFS()->get_field_info( false, $post->ID );

        foreach ( $fields as $field_name => $field_data ) {
            $output .= '[' . $field_name . "]\n";

            if ( is_array( $field_data ) ) {
                $props = $field_info[ $field_name ];
                if ( 'relationship' == $props['type'] ) {
                    $values = [];
                    if ( ! empty( $field_data ) ) {
                        foreach ( $field_data as $item_id ) {
                            $values[] = get_post( $item_id )->post_title;
                        }
                    }
                    $output .= json_encode( $values ) . "\n";
                }
                else {
                    $output .= json_encode( $field_data ) . "\n";
                }
            }
            else {
                $output .= $field_data . "\n";
            }
        }
        return $output;
    }


    /**
     * Determine whether the data changed
     * @see wp-includes/revision.php -> wp_save_post_revision()
     */
    function check_for_changes( $default = true, $last_revision, $post ) {
        $revision_data = CFS()->get( false, $last_revision->ID );
        $post_data = CFS()->get( false, $post->ID );

        if ( serialize( $revision_data ) != serialize( $post_data ) ) {
            return false;
        }

        return true;
    }


    /**
     * Save revision custom fields
     */
    function save_post( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $original_post_id = wp_is_post_revision( $post_id );

        if ( $original_post_id ) {
            $field_data = CFS()->get( false, $original_post_id, [ 'format' => 'raw' ] );
            CFS()->save( $field_data, [ 'ID' => $post_id ] );
        }
    }


    /**
     * Restore revision custom fields
     * @see wp-includes/revision.php -> wp_restore_post_revision()
     */
    function wp_restore_post_revision( $post_id, $revision_id ) {
        $field_data = CFS()->get( false, $revision_id, [ 'format' => 'raw' ] );
        CFS()->save( $field_data, [ 'ID' => $post_id ] );
    }


    /**
     * Delete revision custom fields
     * @see wp-includes/revision.php -> wp_delete_post_revision()
     */
    function wp_delete_post_revision( $revision_id ) {
        global $wpdb;

        $revision_id = (int) $revision_id;
        $wpdb->query( "DELETE FROM {$wpdb->prefix}cfs_values WHERE post_id = $revision_id" );
    }
}
