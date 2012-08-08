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
if (empty($extras))
{
    $extras = array(
        'gforms' => array(
            'form_id' => '',
            'post_type' => '',
        ),
    );
}

$is_gf_active = is_plugin_active('gravityforms/gravityforms.php');

if ($is_gf_active)
{
    $gf_forms = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}rg_form WHERE is_active = 1 ORDER BY title");
}
?>

<table style="width:100%">
    <tr>
        <td class="label">
            <img src="http://gravityforms.s3.amazonaws.com/banners/125x125_2.gif" alt="Gravity Forms" />
        </td>
        <td style="vertical-align:top">
            <?php if($is_gf_active) : ?>

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
            <p>Make sure that your Gravity Forms and CFS <strong>field labels</strong> match exactly!</p>

            <?php else : ?>

            <div><a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=198410" target="_blank">Gravity Forms</a> must be installed to use this feature.</div>
            <p>Gravity Forms is a plugin for adding public forms to your site: everything from contact pages, multi-page forms, payment forms, and forms with conditional fields.</p>
            <p>Once activated, you'll be able to save Gravity Forms entries as post items. This includes mapping of fields into Custom Field Suite.</p>
        <?php endif; ?>
        </td>
    </tr>
</table>
