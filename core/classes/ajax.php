<?php

class cfs_ajax
{

    /*--------------------------------------------------------------------------------------
    *
    *    search_posts
    *
    *    @author Matt Gibbs
    *    @since 1.7.5
    *
    *-------------------------------------------------------------------------------------*/

    public function search_posts($options)
    {
        global $wpdb;

        $keywords = $wpdb->escape($options['q']);

        $sql = "
        SELECT ID, post_type, post_title
        FROM $wpdb->posts
        WHERE
            post_status IN ('publish', 'private') AND
            post_type NOT IN ('cfs', 'attachment', 'revision', 'nav_menu_item') AND
            post_title LIKE '%$keywords%'
        ORDER BY post_type, post_title
        LIMIT 10";
        $results = $wpdb->get_results($sql);

        $output = array();
        foreach ($results as $result)
        {
            $output[] = array(
                'id' => $result->ID,
                'text' => "($result->post_type) $result->post_title"
            );
        }
        return json_encode($output);
    }


    /*--------------------------------------------------------------------------------------
    *
    *    reset
    *
    *    @author Matt Gibbs
    *    @since 1.8.0
    *
    *-------------------------------------------------------------------------------------*/

    public function reset()
    {
        global $wpdb;

        // Drop field groups
        $sql = "
        DELETE p, m FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
        WHERE p.post_type = 'cfs'";
        $wpdb->query($sql);

        // Drop custom field values
        $sql = "
        DELETE v, m FROM {$wpdb->prefix}cfs_values v
        LEFT JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id";
        $wpdb->query($sql);

        // Drop tables
        $wpdb->query("DROP TABLE {$wpdb->prefix}cfs_values");
        delete_option('cfs_version');
        delete_option('cfs_next_field_id');
    }
}
