<?php

class cfs_ajax
{
    /**
     * Search posts (in the Placement Rules area)
     * @param array $options
     * @return string A JSON results object
     */
    public function search_posts( $options ) {
        global $wpdb;

        $sql = $wpdb->prepare("
        SELECT ID, post_type, post_title, post_parent
        FROM $wpdb->posts
        WHERE
            post_status IN ('publish', 'private') AND
            post_type NOT IN ('cfs', 'attachment', 'revision', 'nav_menu_item') AND
            post_title LIKE '%s'
        ORDER BY post_type, post_title
        LIMIT 10",
        '%'.$options['q'].'%' );

        $results = $wpdb->get_results( $sql );

        $output = [];
        foreach ( $results as $result ) {
            $parent = '';

            if (
                isset( $result->post_parent ) &&
                absint( $result->post_parent ) > 0 &&
                $parent = get_post( $result->post_parent )
            ) {
                $parent = "$parent->post_title >";
            }

            $output[] = [
                'id' => $result->ID,
                'text' => "($result->post_type) $parent $result->post_title (#$result->ID)"
            ];
        }
        return json_encode( $output );
    }


    /**
     * Remove all traces of CFS
     */
    public function reset() {
        global $wpdb;

        // Drop field groups
        $sql = "
        DELETE p, m FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
        WHERE p.post_type = 'cfs'";
        $wpdb->query( $sql );

        // Drop custom field values
        $sql = "
        DELETE v, m FROM {$wpdb->prefix}cfs_values v
        LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id";
        $wpdb->query( $sql );

        // Drop tables
        $wpdb->query( "DROP TABLE {$wpdb->prefix}cfs_values" );
        $wpdb->query( "DROP TABLE {$wpdb->prefix}cfs_sessions" );
        delete_option( 'cfs_version' );
        delete_option( 'cfs_next_field_id' );
    }
}
