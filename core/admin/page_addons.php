<?php
function cfs_load_addons() {
    if (false === ($cache = get_transient('cfs_addons'))) {
        $feed = wp_remote_get('http://uproot.us/add-ons/');
        if (!is_wp_error($feed)) {
            if (isset($feed['body']) && 0 < strlen($feed['body'])) {
                $cache = wp_remote_retrieve_body($feed);
                set_transient('cfs_addons', $cache, 3600);
            }
        }
    }
    return $cache;
}

$json = cfs_load_addons();
$json = json_decode($json);
?>

<style type="text/css">
#icon-edit { background: url(<?php echo $this->url; ?>/images/logo.png) no-repeat; }
</style>

<div class="wrap">
    <div id="icon-edit" class="icon32"><br></div>
    <h2>Add-ons</h2>
    <?php foreach ($json as $addon) : ?>
    <div class="list-item" style="float:left; width:240px; height:320px; margin:15px 15px 0 0">
        <div style="background:#21759b; color:#fff; padding:5px">
            <?php echo $addon->title; ?>
            <?php if (!empty($addon->version)) : ?>
            <span style="font-size:10px">v<?php echo $addon->version; ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($addon->thumbnail)) : ?>
        <img src="<?php echo $addon->thumbnail; ?>" style="display:block" alt="" />
        <?php endif; ?>
        <div style="padding:5px">
            <div style="margin-bottom:10px"><?php echo $addon->summary; ?></div>
            <div>
                <?php if (!empty($addon->learn_more_url)) : ?>
                <a class="button-secondary" href="<?php echo $addon->learn_more_url; ?>" target="_blank">Learn More</a>
                <?php endif; ?>
                <?php if (!empty($addon->purchase_price) && !empty($addon->learn_more_url)) : ?>
                <a class="button-secondary" href="<?php echo $addon->learn_more_url; ?>" target="_blank">Buy - $<?php echo $addon->purchase_price; ?></a>
                <?php endif; ?>
                <?php if (!empty($addon->download_url)) : ?>
                <a class="button-secondary" href="<?php echo $addon->download_url; ?>" target="_blank" title="<?php echo $addon->last_updated; ?>">Download</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
