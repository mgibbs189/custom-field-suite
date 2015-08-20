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
        global $wpdb;

        // Remove the rules table
        if ( version_compare( $this->last_version, '1.2.0', '<' ) ) {
            $rules = array();
            $results = $wpdb->get_results( "SELECT group_id, rule, value FROM {$wpdb->prefix}cfs_rules" );
            foreach ( $results as $rule ) {
                $rules[ $rule->group_id ]['post_types']['operator'] = '==';
                $rules[ $rule->group_id ]['post_types']['values'][] = $rule->value;
            }

            foreach ( $rules as $post_id => $rule ) {
                update_post_meta( $post_id, 'cfs_rules', $rule );
            }

            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cfs_rules" );
        }

        // Convert relationship values
        if ( version_compare( $this->last_version, '1.4.2', '<' ) ) {
            $sql = "
            SELECT v.field_id, v.meta_id, v.post_id, v.weight, m.meta_key, m.meta_value, f.parent_id
            FROM {$wpdb->prefix}cfs_values v
            INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
            INNER JOIN {$wpdb->prefix}cfs_fields f ON f.id = v.field_id AND f.name = m.meta_key AND f.type = 'relationship'
            WHERE m.meta_value LIKE '%,%'
            ORDER BY v.field_id";
            $results = $wpdb->get_results( $sql );

            foreach ( $results as $result ) {
                $all_values = explode( ',', $result->meta_value );
                $first_value = array_shift( $all_values );

                // Update existing postmeta value
                $wpdb->update(
                    $wpdb->postmeta,
                    array( 'meta_value' => $first_value ),
                    array( 'meta_id' => $result->meta_id )
                );

                foreach ( $all_values as $key => $the_id ) {

                    // Add row into postmeta
                    $wpdb->insert( $wpdb->postmeta, array(
                        'post_id' => $result->post_id,
                        'meta_key' => $result->meta_key,
                        'meta_value' => $the_id,
                    ) );
                    $meta_id = $wpdb->insert_id;

                    // See if relationship field is within a loop
                    $weight = ( 0 < (int) $result->parent_id ) ? $result->weight : ( $key + 1 );
                    $sub_weight = ( 0 < (int) $result->parent_id ) ? ( $key + 1 ) : 0;

                    // Add row into cfs_values
                    $wpdb->insert( $wpdb->prefix . 'cfs_values', array(
                        'field_id' => $result->field_id,
                        'meta_id' => $meta_id,
                        'post_id' => $result->post_id,
                        'weight' => $weight,
                        'sub_weight' => $sub_weight,
                    ) );
                }
            }
        }

        // Handle nested loops
        if ( version_compare( $this->last_version, '1.5.0', '<' ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}cfs_values ADD COLUMN hierarchy TEXT AFTER post_id" );
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}cfs_values ADD COLUMN base_field_id INT unsigned default 0 AFTER post_id" );
            $wpdb->query( "UPDATE {$wpdb->prefix}cfs_values SET hierarchy = '' WHERE hierarchy IS NULL" );

            $sql = "
            SELECT v.id, f.parent_id, v.weight, v.field_id
            FROM {$wpdb->prefix}cfs_values v
            INNER JOIN {$wpdb->prefix}cfs_fields f ON f.id = v.field_id AND f.parent_id > 0";
            $results = $wpdb->get_results( $sql );

            foreach ( $results as $result ) {
                $hierarchy = "{$result->parent_id}:{$result->weight}:{$result->field_id}";
                $sql = "
                UPDATE {$wpdb->prefix}cfs_values
                SET hierarchy = '$hierarchy', base_field_id = '$result->parent_id'
                WHERE id = '$result->id' LIMIT 1";
                $wpdb->query( $sql );
            }
        }

        // Convert select options to arrays
        if ( version_compare( $this->last_version, '1.6.8', '<' ) ) {
            $results = $wpdb->get_results( "SELECT id, options FROM {$wpdb->prefix}cfs_fields WHERE type = 'select'" );
            foreach ( $results as $result ) {
                $field_id = $result->id;
                $options = unserialize( $result->options );

                if ( isset( $options['choices'] ) && ! is_array( $options['choices'] ) ) {
                    $choices = trim( $options['choices'] );
                    $new_choices = array();

                    if ( ! empty( $choices ) ) {
                        $choices = str_replace( "\r\n", "\n", $choices );
                        $choices = str_replace( "\r", "\n", $choices );
                        $choices = ( false !== strpos( $choices, "\n" ) ) ? explode( "\n", $choices ) : (array) $choices;

                        foreach ( $choices as $choice ) {
                            $choice = trim( $choice );
                            if ( false !== ( $pos = strpos( $choice, ' : ' ) ) ) {
                                $array_key = substr( $choice, 0, $pos );
                                $array_value = substr( $choice, $pos + 3 );
                                $new_choices[ $array_key ] = $array_value;
                            }
                            else {
                                $new_choices[ $choice ] = $choice;
                            }
                        }
                    }

                    $options['choices'] = $new_choices;
                    $sql = "UPDATE {$wpdb->prefix}cfs_fields SET options = %s WHERE id = %d LIMIT 1";
                    $wpdb->query( $wpdb->prepare( $sql, serialize( $options ), $field_id ) );
                }
            }
        }

        // Abandon the cfs_fields table
        if ( version_compare( $this->last_version, '1.8.4', '<' ) ) {
            $next_field_id = (int) $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}cfs_fields ORDER BY id DESC LIMIT 1" );
            update_option( 'cfs_next_field_id', $next_field_id + 1 );

            $sql = "
            SELECT id, name, label, type, instructions AS notes, post_id, parent_id, weight, options
            FROM {$wpdb->prefix}cfs_fields
            ORDER BY post_id, parent_id, weight";
            $results = $wpdb->get_results( $sql, ARRAY_A );

            $fields = array();
            foreach ( $results as $result ) {
                $post_id = $result['post_id'];
                unset( $result['post_id'] );
                $result['options'] = unserialize( $result['options'] );

                // Save certain field options as strings
                if ( ! empty( $result['options'] ) ) {
                    foreach ( $result['options'] as $option_name => $option_value ) {
                        if ( in_array( $option_name, array( 'formatting', 'return_value' ) ) ) {
                            $result['options'][ $option_name ] = $option_value[0];
                        }
                    }
                }

                $fields[ $post_id ][] = $result;
            }

            foreach ( $fields as $post_id => $field_data ) {
                update_post_meta( $post_id, 'cfs_fields', $field_data );
            }
        }

        // Add the sessions table
        if ( version_compare( $this->last_version, '1.9.0', '<' ) ) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "
            CREATE TABLE {$wpdb->prefix}cfs_sessions (
                id VARCHAR(32),
                data TEXT,
                expires VARCHAR(10),
                PRIMARY KEY (id)
            ) DEFAULT CHARSET=utf8";
            dbDelta( $sql );
        }

        // Add the "depth" column
        if ( version_compare( $this->last_version, '2.0.1', '<' ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}cfs_values ADD COLUMN depth INT unsigned default 0 AFTER hierarchy" );

            $results = $wpdb->get_results( "SELECT id, hierarchy FROM {$wpdb->prefix}cfs_values WHERE hierarchy != ''" );
            foreach ( $results as $result ) {
                $hierarchy_array = explode( ':', $result->hierarchy );
                $depth = floor( count( $hierarchy_array ) / 2 );
                $wpdb->query( "UPDATE {$wpdb->prefix}cfs_values SET depth = '$depth' WHERE id = '$result->id' LIMIT 1" );
            }
        }

        // Disable add-ons (they're now in core)
        if ( version_compare( $this->last_version, '2.4', '<' ) ) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            deactivate_plugins( 'cfs-hyperlink/index.php' );
            deactivate_plugins( 'cfs-hyperlink-master/index.php' );

            deactivate_plugins( 'cfs-revisions/index.php' );
            deactivate_plugins( 'cfs-revisions-master/index.php' );
        }
    }
}

new cfs_upgrade();
