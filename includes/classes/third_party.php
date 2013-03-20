<?php

class cfs_third_party
{
    public $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;

        // Post Type Switcher - http://wordpress.org/extend/plugins/post-type-switcher/
        add_filter('pts_post_type_filter', array($this, 'pts_post_type_filter'));

        // Gravity Forms - http://www.gravityforms.com/
        add_action('gform_post_submission', array($this, 'gform_handler'), 10, 2);

        // WPML - http://wpml.org/
        add_action('icl_make_duplicate', array($this, 'wpml_handler'), 10, 4);
    }

    /*--------------------------------------------------------------------------------------
    *
    *    gform_handler (gravity forms)
    *
    *    @author Matt Gibbs
    *    @since 1.3.0
    *
    *-------------------------------------------------------------------------------------*/

    function gform_handler($entry, $form)
    {
        global $wpdb;

        // get the form id
        $form_id = $entry['form_id'];

        // see if any field groups use this form id
        $field_groups = array();
        $results = $wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'cfs_extras'");
        foreach ($results as $result)
        {
            $meta_value = unserialize($result->meta_value);

            if (isset($meta_value['gforms']))
            {
                if ($form_id == $meta_value['gforms']['form_id'])
                {
                    $fields = array();
                    $all_fields = $this->parent->api->find_input_fields(array('post_id' => $result->post_id));
                    foreach ($all_fields as $field)
                    {
                        $fields[$field['label']] = $field['name'];
                    }

                    $field_groups[$result->post_id] = array(
                        'post_type' => $meta_value['gforms']['post_type'],
                        'fields' => $fields,
                    );
                }
            }
        }

        // If there's some matching groups, parse the GF field data
        if (!empty($field_groups))
        {
            $form_data = array();

            // get submitted fields
            foreach ($form['fields'] as $field)
            {
                $field_id = $field['id'];

                // handle fields with children
                if (!empty($field['inputs']))
                {
                    $values = array();

                    foreach ($field['inputs'] as $sub_field)
                    {
                        $sub_field_value = $entry[$sub_field['id']];

                        if (!empty($sub_field_value))
                        {
                            $values[] = $sub_field_value;
                        }
                    }
                    $value = implode("\n", $values);
                }
                elseif ('multiselect' == $field['type'])
                {
                    $value = explode(',', $entry[$field_id]);
                }
                else
                {
                    $value = $entry[$field_id];
                }

                $form_data[$field['label']] = $value;
            }
        }

        foreach ($field_groups as $post_id => $data)
        {
            $field_data = array();
            $intersect = array_intersect_key($form_data, $data['fields']);
            foreach ($intersect as $key => $field_value)
            {
                $field_name = $data['fields'][$key];
                $field_data[$field_name] = $field_value;
            }

            $post_data = array(
                'post_type' => $data['post_type'],
            );
            if (isset($entry['post_id']))
            {
                $post_data['ID'] = $entry['post_id'];
            }

            // save data
            $this->parent->save($field_data, $post_data);
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    wpml_handler
    *
    *    Properly copy CFS fields on WPML post duplication
    *    Requires WPML 2.6.0+
    *
    *    @author Matt Gibbs
    *    @since 1.6.8
    *
    *-------------------------------------------------------------------------------------*/

    function wpml_handler($master_id, $lang, $post_data, $duplicate_id)
    {
        $field_data = $this->parent->get(false, $master_id, array('format' => 'raw'));

        if (!empty($field_data))
        {
            $this->parent->save($field_data, array('ID' => $duplicate_id));
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    pts_post_type_filter
    *
    *    @author Matt Gibbs
    *    @since 1.8.1
    *
    *-------------------------------------------------------------------------------------*/

    function pts_post_type_filter($args)
    {
        global $current_screen;

        if ('cfs' == $current_screen->id)
        {
            $args = array('public' => false, 'show_ui' => true);
        }

        return $args;
    }
}
