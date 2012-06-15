<?php

global $wpdb;

// Proceed with import
if (isset($_POST['groups']))
{
    $group_ids = $_POST['groups'];
    foreach ($group_ids as $group_id)
    {
        $rules = get_post_meta($group_id, 'cfs_rules', true);
        $post_types = $post_ids = $term_ids = '';
        $fields = array();

        // Get this group's fields
        $sql = "
        SELECT id, name
        FROM {$wpdb->prefix}cfs_fields
        WHERE post_id = '$group_id' AND parent_id = 0";
        $results = $wpdb->get_results($sql);
        foreach ($results as $result)
        {
            $fields[$result->name] = $result->id;
        }

        if (isset($rules['post_types']))
        {
            $post_types = implode("','", $rules['post_types']['values']);
            $operator = ('==' == $rules['post_types']['operator'][0]) ? 'IN' : 'NOT IN';
            $post_types = " AND p.post_type $operator ('$post_types')";
        }
        if (isset($rules['post_ids']))
        {
            $post_ids = implode(',', $rules['post_ids']['values']);
            $operator = ('==' == $rules['post_ids']['operator'][0]) ? 'IN' : 'NOT IN';
            $post_ids = " AND p.ID $operator ($post_ids)";
        }
        if (isset($rules['term_ids']))
        {
            $term_ids = implode(',', $rules['term_ids']['values']);
            $operator = ('==' == $rules['term_ids']['operator'][0]) ? 'IN' : 'NOT IN';
            $term_ids = "
            INNER JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID
            INNER JOIN $wpdb->term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.term_id $operator ($term_ids)";
        }

        $sql = "
        SELECT m.meta_id, m.post_id, m.meta_key
        FROM $wpdb->postmeta m
        INNER JOIN $wpdb->posts p ON p.ID = m.post_id $post_types $post_ids $term_ids
        LEFT JOIN {$wpdb->prefix}cfs_values v ON v.meta_id = m.meta_id
        WHERE v.meta_id IS NULL";
        $results = $wpdb->get_results($sql);

        $tuples = array();
        foreach ($results as $result)
        {
            if (isset($fields[$result->meta_key]))
            {
                $field_id = $fields[$result->meta_key];
                $tuples[] = "($field_id, $result->meta_id, $result->post_id, 0, 0)";
            }
        }

        if (0 < count($tuples))
        {
            $wpdb->query("INSERT INTO {$wpdb->prefix}cfs_values (field_id, meta_id, post_id, weight, sub_weight) VALUES " . implode(',', $tuples));
        }
    }
}

$sql = "
SELECT ID, post_title
FROM $wpdb->posts
WHERE post_type = 'cfs' AND post_status = 'publish'
ORDER BY post_title";
$results = $wpdb->get_results($sql);
?>

<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2><?php _e('Custom Field Import', 'cfs'); ?></h2>

    <?php if (isset($_POST['groups'])) : ?>
    <div id="message" class="updated">
        <p><?php _e('Import Successful', 'cfs'); ?></p>
    </div>
    <?php endif; ?>

    <p><?php _e('This tool will scan your existing custom fields, mapping values into Custom Field Suite.', 'cfs'); ?></p>
    <p><?php _e('Select the field groups to populate:', 'cfs'); ?></p>
    <form method="post" action="">
        <table class="widefat">
            <thead>
                <tr>
                    <th class="check-column"></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $result) : ?>
                <tr>
                    <td><input type="checkbox" name="groups[]" value="<?php echo $result->ID; ?>" /></td>
                    <td><?php echo $result->post_title; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="check-column"></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <div class="tablenav bottom">
            <input type="submit" class="button-secondary" value="<?php _e('Import Field Data', 'cfs'); ?>" />
        </div>
    </form>
</div>
