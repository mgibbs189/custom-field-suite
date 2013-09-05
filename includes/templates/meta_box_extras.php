<?php

global $wpdb;

// Post types
$post_types = array();
$types = get_post_types(array('public' => true));
foreach ($types as $post_type)
{
    if (!in_array($post_type, array('cfs', 'attachment')))
    {
        $post_types[] = $post_type;
    }
}

$extras = get_post_meta($post->ID, 'cfs_extras', true);
if (!isset($extras['gforms'])) {
    $extras['gforms'] = array('form_id' => '', 'post_type' => '');
}
if (!isset($extras['hide_editor'])) {
    $extras['hide_editor'] = '';
}
if (!isset($extras['order'])) {
    $extras['order'] = 0;
}
if (!isset($extras['context'])) {
    $extras['context'] = 'normal';
}

$is_gf_active = is_plugin_active('gravityforms/gravityforms.php');

if ($is_gf_active)
{
    $gf_forms = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}rg_form WHERE is_active = 1 ORDER BY title");
}
?>

<table>
    <tr>
        <td class="label">
            <label>
                <?php _e('Order', 'cfs'); ?>
                <div class="cfs_tooltip">
                    <div class="tooltip_inner"><?php _e('The field group with the lowest order will appear first.', 'cfs'); ?></div>
                </div>
            </label>
        </td>
        <td style="vertical-align:top">
            <input type="text" name="cfs[extras][order]" value="<?php echo $extras['order']; ?>" />
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Position', 'cfs'); ?></label>
        </td>
        <td style="vertical-align:top">
            <div><input type="radio" name="cfs[extras][context]" value="normal"<?php echo ($extras['context'] == 'normal') ? ' checked' : ''; ?> /> <?php _e( 'Normal', 'cfs' ); ?></div>
            <div><input type="radio" name="cfs[extras][context]" value="side"<?php echo ($extras['context'] == 'side') ? ' checked' : ''; ?> /> <?php _e( 'Side', 'cfs' ); ?></div>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Display Settings', 'cfs'); ?></label>
        </td>
        <td style="vertical-align:top">
            <div>
                <?php
                    $this->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[extras][hide_editor]",
                        'input_class' => 'true_false',
                        'value' => $extras['hide_editor'],
                        'options' => array('message' => __('Hide the content editor', 'cfs')),
                    ));
                ?>
            </div>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e('Gravity Forms', 'cfs'); ?></label>
        </td>
        <td style="vertical-align:top">
            <?php if ($is_gf_active) : ?>

            <select name="cfs[extras][gforms][form_id]">
                <option value="">-- Gravity Form --</option>
                <?php foreach ($gf_forms as $gf_form) : ?>
                <?php $selected = ($gf_form->id == $extras['gforms']['form_id']) ? ' selected' : ''; ?>
                <option value="<?php echo $gf_form->id; ?>"<?php echo $selected; ?>><?php echo $gf_form->title; ?> (ID#<?php echo $gf_form->id; ?>)</option>
                <?php endforeach; ?>
            </select>

            <select name="cfs[extras][gforms][post_type]">
                <option value="">-- Post Type --</option>
                <?php foreach ($post_types as $post_type) : ?>
                <?php $selected = ($post_type == $extras['gforms']['post_type']) ? ' selected' : ''; ?>
                <option value="<?php echo $post_type; ?>"<?php echo $selected; ?>><?php echo $post_type; ?></option>
                <?php endforeach; ?>
            </select>
            <p><?php _e('Your Gravity Form and CFS field labels must match exactly', 'cfs'); ?></p>

            <?php else : ?>

            <?php $gf_url = '<a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=198410" target="_blank">Gravity Forms</a>'; ?>
            <div><?php printf(__('Please install %s to use this feature.', 'cfs'), $gf_url); ?></div>

            <?php endif; ?>
        </td>
    </tr>
</table>
