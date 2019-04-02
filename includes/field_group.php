<?php

class cfs_field_group
{
    public $cache;


    /*
    ================================================================
        Load all field groups
    ================================================================
    */
    public function load_field_groups() {
        global $wpdb;

        if ( isset( $this->cache['field_groups'] ) ) {
            return $this->cache['field_groups'];
        }

        $sql = "
        SELECT p.ID, p.post_title,
            m1.meta_value as fields,
            m2.meta_value AS rules,
            m3.meta_value AS extras
        FROM $wpdb->posts p
        INNER JOIN $wpdb->postmeta m1 ON m1.post_id = p.ID AND m1.meta_key = 'cfs_fields'
        INNER JOIN $wpdb->postmeta m2 ON m2.post_id = p.ID AND m2.meta_key = 'cfs_rules'
        INNER JOIN $wpdb->postmeta m3 ON m3.post_id = p.ID AND m3.meta_key = 'cfs_extras'
        WHERE p.post_status = 'publish'";
        $results = $wpdb->get_results( $sql );

        $output = array();
        foreach ( $results as $result ) {
            $output[ $result->ID ] = array(
                'title'     => $result->post_title,
                'fields'    => unserialize( $result->fields ),
                'rules'     => unserialize( $result->rules ),
                'extras'    => unserialize( $result->extras )
            );
        }

        $this->cache['field_groups'] = $output;

        return $output;
    }


    /*
    ================================================================
        Import field groups
    ================================================================
    */
    public function import( $options ) {
        global $wpdb;

        if ( ! empty( $options['import_code'] ) ) {

            // Collect stats
            $stats = array();

            // Get all existing field group names
            $existing_groups = $wpdb->get_col( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'cfs'" );

            // Loop through field groups
            foreach ( $options['import_code'] as $group ) {

                // Make sure this field group doesn't exist
                if ( ! in_array( $group['post_name'], $existing_groups ) ) {

                    // Insert new post
                    $post_id = wp_insert_post( array(
                        'post_title' => $group['post_title'],
                        'post_name' => $group['post_name'],
                        'post_type' => 'cfs',
                        'post_status' => 'publish',
                        'post_content' => '',
                        'post_content_filtered' => '',
                        'post_excerpt' => '',
                        'to_ping' => '',
                        'pinged' => '',
                    ) );

                    // Generate new field IDs
                    $field_id_mapping = array();
                    $next_field_id = (int) get_option( 'cfs_next_field_id' );
                    foreach ( $group['cfs_fields'] as $key => $data ) {

                        $id = $group['cfs_fields'][ $key ]['id'];
                        $parent_id = $group['cfs_fields'][ $key ]['parent_id'];
                        $field_id_mapping[ $id ] = $next_field_id;
                        $group['cfs_fields'][ $key ]['id'] = $next_field_id;
                        if ( 0 < (int) $parent_id ) {
                            $group['cfs_fields'][ $key ]['parent_id'] = $field_id_mapping[ $parent_id ];
                        }
                        $next_field_id++;
                    }

                    update_option( 'cfs_next_field_id', $next_field_id );
                    update_post_meta( $post_id, 'cfs_fields', $group['cfs_fields'] );
                    update_post_meta( $post_id, 'cfs_rules', $group['cfs_rules'] );
                    update_post_meta( $post_id, 'cfs_extras', $group['cfs_extras'] );

                    $stats['imported'][] = $group['post_title'];
                }
                else {
                    $stats['skipped'][] = $group['post_title'];
                }
            }

            $return = '';
            if ( ! empty( $stats['imported'] ) ) {
                $return .= '<div>' . __( 'Imported', 'cfs' ) . ': ' . implode( ', ', $stats['imported'] ) . '</div>';
            }
            if ( ! empty( $stats['skipped'] ) ) {
                $return .= '<div>' . __( 'Skipped', 'cfs' ) . ': ' . implode( ', ', $stats['skipped'] ) . '</div>';
            }
            return $return;
        }
        else {
            return '<div>' . __( 'Nothing to import', 'cfs' ) . '</div>';
        }
    }


    /*
    ================================================================
        Export field groups
    ================================================================
    */
    public function export( $options ) {
        global $wpdb;

        $post_ids = array();
        $field_groups = array();
        foreach ( $options['field_groups'] as $post_id ) {
            $post_ids[] = (int) $post_id;
        }

        $post_ids = implode( ',', $post_ids );
        $post_data = $wpdb->get_results( "SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_type = 'cfs' AND ID IN ($post_ids)" );

        foreach ( $post_data as $row ) {
            $field_groups[ $row->ID ] = array(
                'post_title' => $row->post_title,
                'post_name' => $row->post_name,
            );
        }

        $meta_data = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key LIKE 'cfs_%' AND post_id IN ($post_ids)" );
        foreach ( $meta_data as $row ) {
            $value = unserialize( $row->meta_value );
            $field_groups[ $row->post_id ][ $row->meta_key ] = $value;
        }

        // Strip out the field group keys
        $temp = array();
        foreach ( $field_groups as $field_group ) {
            $temp[] = $field_group;
        }
        $field_groups = $temp;

        return $field_groups;
    }


    /**
     * Save field group settings
     * @param array $params
     */
    function save( $params = array() ) {
        global $wpdb;

        $post_id = $params['post_id'];

        /*---------------------------------------------------------------------------------------------
            Save fields
        ---------------------------------------------------------------------------------------------*/

        $weight = 0;
        $prev_fields = array();
        $current_field_ids = array();
        $next_field_id = (int) get_option( 'cfs_next_field_id' );
        $existing_fields = get_post_meta( $post_id, 'cfs_fields', true );

        if ( ! empty( $existing_fields ) ) {
            foreach ( $existing_fields as $item ) {
                $prev_fields[ $item['id'] ] = $item['name'];
            }
        }

        $new_fields = array();

        foreach ( $params['fields'] as $key => $field ) {

            // Sanitize the field
            $field = stripslashes_deep( $field );

            // Allow for field customizations
            $field = CFS()->fields[ $field['type'] ]->pre_save_field( $field );

            // Set the parent ID
            $field['parent_id'] = empty( $field['parent_id'] ) ? 0 : (int) $field['parent_id'];

            // Save empty array for fields without options
            $field['options'] = empty( $field['options'] ) ? array() : $field['options'];

            // Use an existing ID if available
            if ( 0 < (int) $field['id'] ) {

                // We use this variable to check for deleted fields
                $current_field_ids[] = $field['id'];

                // Rename the postmeta key if necessary
                if ( $field['name'] != $prev_fields[ $field['id'] ] ) {
                    $wpdb->query(
                        $wpdb->prepare("
                            UPDATE {$wpdb->postmeta} m
                            INNER JOIN {$wpdb->prefix}cfs_values v ON v.meta_id = m.meta_id
                            SET meta_key = %s
                            WHERE v.field_id = %d",
                            $field['name'], $field['id']
                        )
                    );
                }
            }
            else {
                $field['id'] = $next_field_id;
                $next_field_id++;
            }

            $data = array(
                'id'            => $field['id'],
                'name'          => $field['name'],
                'label'         => $field['label'],
                'type'          => $field['type'],
                'notes'         => $field['notes'],
                'parent_id'     => $field['parent_id'],
                'weight'        => $weight,
                'options'       => $field['options'],
            );

            $new_fields[] = $data;

            $weight++;
        }

        // Save the fields
        update_post_meta( $post_id, 'cfs_fields', $new_fields );

        // Update the field ID counter
        update_option( 'cfs_next_field_id', $next_field_id );

        // Remove values for deleted fields
        $deleted_field_ids = array_diff( array_keys( $prev_fields ), $current_field_ids );

        // Filter deleted field IDs before deleting meta
        $deleted_field_ids = apply_filters( 'cfs_deleted_field_ids', $deleted_field_ids );

        if ( 0 < count( $deleted_field_ids ) ) {
            $deleted_field_ids = implode( ',', $deleted_field_ids );
            $wpdb->query("
                DELETE v, m
                FROM {$wpdb->prefix}cfs_values v
                INNER JOIN {$wpdb->postmeta} m ON m.meta_id = v.meta_id
                WHERE v.field_id IN ($deleted_field_ids)"
            );
        }

        /*---------------------------------------------------------------------------------------------
            Save rules
        ---------------------------------------------------------------------------------------------*/

        $data = array();
        $rule_types = array( 'post_types', 'post_formats', 'user_roles', 'post_ids', 'term_ids', 'page_templates' );

        foreach ( $rule_types as $type ) {
            if ( ! empty( $params['rules'][ $type ] ) ) {

                // Break apart the autocomplete string
                if ( 'post_ids' == $type ) {
                    $params['rules'][ $type ] = explode( ',', $params['rules'][ $type ] );
                }

                $data[ $type ] = array(
                    'operator' => $params['rules']['operator'][ $type ],
                    'values' => $params['rules'][ $type ],
                );
            }
        }

        $data = apply_filters( 'cfs_save_field_group_rules', $data, $post_id );
        update_post_meta( $post_id, 'cfs_rules', $data );

        /*---------------------------------------------------------------------------------------------
            Save extras
        ---------------------------------------------------------------------------------------------*/

        update_post_meta( $post_id, 'cfs_extras', $params['extras'] );
    }
}

CFS()->field_group = new cfs_field_group();
