<?php

global $post, $wpdb, $wp_roles;

$equals_text = __('equals', 'cfs');
$not_equals_text = __('is not', 'cfs');

$rules = get_post_meta($post->ID, 'cfs_rules', true);

// Populate rules if empty
$rule_types = array('post_types', 'user_roles', 'post_ids', 'term_ids', 'page_templates');

foreach ($rule_types as $type)
{
    if (!isset($rules[$type]))
    {
        $rules[$type] = array('operator' => array('=='), 'values' => array());
    }
}

// Post types
$post_types = array();
$types = get_post_types();
foreach ($types as $post_type)
{
    if (!in_array($post_type, array('cfs', 'attachment', 'revision', 'nav_menu_item')))
    {
        $post_types[$post_type] = $post_type;
    }
}

// User roles
foreach ($wp_roles->roles as $key => $role)
{
    $user_roles[$key] = $key;
}

// Post IDs
$sql = "
SELECT ID, post_type, post_title
FROM $wpdb->posts
WHERE
    post_status IN ('publish', 'private') AND
    post_type NOT IN ('cfs', 'attachment', 'revision', 'nav_menu_item')
ORDER BY post_type, post_title";
$results = $wpdb->get_results($sql);

foreach ($results as $result)
{
    $post_ids[$result->ID] = "($result->post_type) $result->post_title";
}

// Term IDs
$sql = "
SELECT t.term_id, t.name, tt.taxonomy
FROM $wpdb->terms t
INNER JOIN $wpdb->term_taxonomy tt ON tt.term_id = t.term_id AND tt.taxonomy != 'post_tag'
ORDER BY tt.parent, tt.taxonomy, t.name";
$results = $wpdb->get_results($sql);
foreach ($results as $result)
{
    $term_ids[$result->term_id] = "($result->taxonomy) $result->name";
}

// Page templates
$page_templates = array();
$templates = get_page_templates();
foreach ($templates as $template_name => $filename)
{
    $page_templates[$filename] = $template_name;
}
?>

<script>
(function($) {
    $(function() {
        $('.select2').select2({
            placeholder: '<?php _e('Select some options', 'cfs'); ?>'
        });
    });
})(jQuery);
</script>

<table>
    <tr>
        <td class="label">
            <label><?php _e('Post Types', 'cfs'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_name' => "cfs[rules][operator][post_types]",
                    'options' => array(
                        'choices' => array(
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        )
                    ),
                    'value' => $rules['post_types']['operator'],
                ));
            ?>
        </td>
        <td>
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfs[rules][post_types]",
                    'options' => array('multiple' => '1', 'choices' => $post_types),
                    'value' => $rules['post_types']['values'],
                ));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('User Roles', 'cfs'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_name' => "cfs[rules][operator][user_roles]",
                    'options' => array(
                        'choices' => array(
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        )
                    ),
                    'value' => $rules['user_roles']['operator'],
                ));
            ?>
        </td>
        <td>
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfs[rules][user_roles]",
                    'options' => array('multiple' => '1', 'choices' => $user_roles),
                    'value' => $rules['user_roles']['values'],
                ));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Posts', 'cfs'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_name' => "cfs[rules][operator][post_ids]",
                    'options' => array(
                        'choices' => array(
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        )
                    ),
                    'value' => $rules['post_ids']['operator'],
                ));
            ?>
        </td>
        <td>
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfs[rules][post_ids]",
                    'options' => array('multiple' => '1', 'choices' => $post_ids),
                    'value' => $rules['post_ids']['values'],
                ));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Taxonomy Terms', 'cfs'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_name' => "cfs[rules][operator][term_ids]",
                    'options' => array(
                        'choices' => array(
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        )
                    ),
                    'value' => $rules['term_ids']['operator'],
                ));
            ?>
        </td>
        <td>
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfs[rules][term_ids]",
                    'options' => array('multiple' => '1', 'choices' => $term_ids),
                    'value' => $rules['term_ids']['values'],
                ));
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Page Template', 'cfs'); ?></label>
        </td>
        <td style="width:80px; vertical-align:top">
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_name' => "cfs[rules][operator][page_templates]",
                    'options' => array(
                        'choices' => array(
                            '==' => $equals_text,
                            '!=' => $not_equals_text,
                        )
                    ),
                    'value' => $rules['page_templates']['operator'],
                ));
            ?>
        </td>
        <td>
            <?php
                $this->create_field(array(
                    'type' => 'select',
                    'input_class' => 'select2',
                    'input_name' => "cfs[rules][page_templates]",
                    'options' => array('multiple' => '1', 'choices' => $page_templates),
                    'value' => $rules['page_templates']['values'],
                ));
            ?>
        </td>
    </tr>
</table>
