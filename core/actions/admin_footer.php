<link rel="stylesheet" type="text/css" href="<?php echo $this->url; ?>/css/screen_extra.css" />
<script type="text/javascript">
jQuery(function() {
    jQuery(".wp-list-table").before(jQuery("#posts-sidebar-box").html());
});
</script>

<div id="posts-sidebar-box" class="hidden">
    <div class="posts-sidebar" id="poststuff">
        <div class="postbox">
            <div class="handlediv"><br></div>
            <h3 class="hndle"><span><?php _e('Custom Field Suite', 'cfs'); ?> <?php echo $this->version; ?></span></h3>
            <div class="inside">
                <div class="field">
                    <h4><?php _e('Changelog', 'cfs'); ?></h4>
                    <p><?php _e('See updates for', 'cfs'); ?> <a class="thickbox" href="<?php echo admin_url('plugin-install.php'); ?>?tab=plugin-information&plugin=custom-field-suite&section=changelog&TB_iframe=1&width=640&height=480">v<?php echo $this->version; ?></a></p>
                </div>
                <div class="field">
                    <h4><?php _e('Getting started?', 'cfs'); ?></h4>
                    <p>
                        <a href="http://uproot.us/custom-field-suite/docs/" target="_blank"><?php _e('Read the user guide', 'cfs'); ?></a>
                    </p>
                    <p>
                        <a href="http://wordpress.org/extend/plugins/custom-field-suite/" target="_blank"><?php _e('Rate the plugin', 'cfs'); ?></a>
                    </p>
                    <p>
                        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JMVGK3L35X6BU" target="_blank"><?php _e('Donate', 'cfs'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
