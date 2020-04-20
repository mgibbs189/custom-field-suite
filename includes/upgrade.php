<?php

class cfs_upgrade
{

    public $version;
    public $last_version;


    public function __construct() {
        $this->version = CFS_VERSION;
        $this->last_version = get_option('cfs_version');

        if ( version_compare( $this->last_version, $this->version, '<' ) ) {
            if ( version_compare( $this->last_version, '1.0.0', '<' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                $this->clean_install();
            }
            else {
                $this->run_upgrade();
            }

            update_option( 'cfs_version', $this->version );
        }
    }

    private function clean_install() {
        global $wpdb;

        $sql = "
        CREATE TABLE {$wpdb->prefix}cfs_values (
            id INT unsigned not null auto_increment,
            field_id INT unsigned,
            meta_id INT unsigned,
            post_id INT unsigned,
            base_field_id INT unsigned default 0,
            hierarchy TEXT,
            depth INT unsigned default 0,
            weight INT unsigned default 0,
            sub_weight INT unsigned default 0,
            PRIMARY KEY (id),
            INDEX field_id_idx (field_id),
            INDEX post_id_idx (post_id)
        ) DEFAULT CHARSET=utf8";
        dbDelta( $sql );

        $sql = "
        CREATE TABLE {$wpdb->prefix}cfs_sessions (
            id VARCHAR(32),
            data TEXT,
            expires VARCHAR(10),
            PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8";
        dbDelta( $sql );

        // Set the field counter
        update_option( 'cfs_next_field_id', 1 );
    }

    private function run_upgrade() {
    }
}

new cfs_upgrade();
