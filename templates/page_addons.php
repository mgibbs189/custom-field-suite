<?php
function cfs_load_addons() {
    if ( false === ( $cache = get_transient( 'cfs_addons' ) ) ) {
        $feed = wp_remote_get( 'http://customfieldsuite.com/add-ons/' );
        if ( ! is_wp_error( $feed ) ) {
            if ( isset( $feed['body'] ) && 0 < strlen( $feed['body'] ) ) {
                $cache = wp_remote_retrieve_body( $feed );
                set_transient( 'cfs_addons', $cache, 3600 );
            }
        }
    }
    return $cache;
}

$json = cfs_load_addons();
$json = json_decode( $json );
?>

<style type="text/css">
.addon {
  float: left;
  width: 220px;
  margin: 15px 15px 0 0;
}

.addon-container {
  text-align: center;
  border: 1px solid #e1e1e1;
  box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
}

.addon-thumbnail {
  padding: 10px 0;
  background: #fcfcfc;
  border-bottom: 1px solid #e1e1e1;
  height: 128px;
}

.addon-main {
  padding: 10px 15px;
  height: 100px;
}

.addon-title {
  font-size: 16px;
  margin-bottom: 10px;
}

.addon-purpose {
  color: #888;
}

.addon-learn-more {
  border-top: 1px solid #e1e1e1;
  background: #fcfcfc;
  padding: 10px 0;
}
</style>

<div class="wrap">
    <h2><?php _e( 'Add-ons', 'cfs' ); ?></h2>
    <?php foreach ( $json as $addon ) : ?>
    <div class="addon">
        <div class="addon-container">
            <div class="addon-thumbnail">
                <a href="<?php echo $addon->learn_more_url; ?>" target="_blank">
                    <img src="<?php echo $addon->thumbnail; ?>" alt="" />
                </a>
            </div>
            <div class="addon-main">
                <div class="addon-title">
                    <a href="<?php echo $addon->learn_more_url; ?>" target="_blank"><?php echo $addon->title; ?></a>
                </div>
                <div class="addon-purpose"><?php echo $addon->purpose; ?></div>
            </div>
            <div class="addon-learn-more">
                <a class="button-primary" href="<?php echo $addon->learn_more_url; ?>" target="_blank"><?php _e( 'Learn more', 'cfs' ); ?></a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
