<?php

global $post, $wpdb;

/*---------------------------------------------------------------------------------------------
    Field management screen
---------------------------------------------------------------------------------------------*/

if ('cfs' == $GLOBALS['post_type'])
{
    foreach ($this->fields as $field_name => $field_data)
    {
        ob_start();
        $this->fields[$field_name]->options_html('clone', $field_data);
        $options_html[$field_name] = ob_get_clean();
    }

    $field_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cfs_fields WHERE post_id = '$post->ID'");

    // Build clone HTML
    $field = (object) array(
        'id' => 0,
        'parent_id' => 0,
        'name' => 'new_field',
        'label' => 'New Field',
        'type' => 'text',
        'instructions' => '',
        'weight' => 'clone',
    );

    ob_start();
    $this->field_html($field);
    $field_clone = ob_get_clean();
?>

<script type="text/javascript">

field_index = <?php echo $field_count; ?>;
field_clone = <?php echo json_encode($field_clone); ?>;
options_html = <?php echo json_encode($options_html); ?>;

</script>

<script type="text/javascript" src="<?php echo $this->url; ?>/js/fields.js"></script>
<script type="text/javascript" src="<?php echo $this->url; ?>/js/select2/select2.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/css/fields.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/js/select2/select2.css" />

<?php

    add_meta_box('cfs_fields', __('Fields', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'fields'));
    add_meta_box('cfs_rules', __('Placement Rules', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'rules'));
    add_meta_box('cfs_extras', __('Extras', 'cfs'), array($this, 'meta_box'), 'cfs', 'normal', 'high', array('box' => 'extras'));
}

/*---------------------------------------------------------------------------------------------
    Field input
---------------------------------------------------------------------------------------------*/

else
{
    $field_group_ids = $this->get_matching_groups($post->ID);

    if (!empty($field_group_ids))
    {
?>

<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/css/input.css" />

<?php
        // Support for multiple metaboxes
        foreach ($field_group_ids as $group_id => $title)
        {
            // Call the init() field method
            $results = $wpdb->get_results("SELECT DISTINCT type FROM {$wpdb->prefix}cfs_fields WHERE post_id = '$group_id' ORDER BY parent_id, weight");
            foreach ($results as $result)
            {
                $this->fields[$result->type]->init(
                    $this->fields[$result->type]
                );
            }

            add_meta_box("cfs_input_$group_id", $title, array($this, 'meta_box'), $post->post_type, 'normal', 'high', array('box' => 'input', 'group_id' => $group_id));

            // Add .cfs_input to the metabox CSS
            add_filter("postbox_classes_{$post->post_type}_cfs_input_{$group_id}", 'cfs_postbox_classes');
        }

        // Force editor support
        $has_editor = post_type_supports($post->post_type, 'editor');
        add_post_type_support($post->post_type, 'editor');

        if (!$has_editor)
        {
?>

<style type="text/css">#poststuff .postarea { display: none; }</style>

<?php
        }
    }
}

/*---------------------------------------------------------------------------------------------
    Helper functions
---------------------------------------------------------------------------------------------*/

function cfs_postbox_classes($classes)
{
    $classes[] = 'cfs_input';
    return $classes;
}
