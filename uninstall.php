<?php

if (defined('WP_UNINSTALL_PLUGIN'))
{
    global $wpdb;

    $sql = "DELETE p, m FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
    WHERE p.post_type = 'cfs'";
    $wpdb->query($sql);

    $wpdb->query("DROP TABLE {$wpdb->prefix}cfs_fields");
    $wpdb->query("DROP TABLE {$wpdb->prefix}cfs_values");
    delete_option('cfs_version');
}
