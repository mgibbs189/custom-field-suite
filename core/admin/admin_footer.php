<style type="text/css">
.tablenav.top {
    display: none;
}

p.search-box {
    display: none;
}

.row-actions .inline {
    display: none;
}

#screen-options-link-wrap {
    display: none;
}

#posts-filter {
    margin-right: 300px;
}

.posts-sidebar {
    position: absolute;
    right: 0px;
    width: 275px;
    margin: 28px 20px 0 0;
}

#poststuff .inside {
    margin: 0;
    padding: 0;
}

#poststuff .inside h4,
#poststuff .inside p {
    margin: 3px 0;
    padding: 0;
}

.posts-sidebar .inside .field {
    border-bottom: 1px solid #dfdfdf;
    border-top: 1px solid #fff;
    padding: 6px 10px;
}

.posts-sidebar .inside .field:last-child {
    border-bottom:  none;
}

#icon-edit {
    background: url(<?php echo $this->url; ?>/images/logo.png) no-repeat;
}
</style>

<script>
(function($) {
    $(function() {
        $('.wp-list-table').before($('#posts-sidebar-box').html());
    });
})(jQuery);
</script>

<div id="posts-sidebar-box" class="hidden">
    <div class="posts-sidebar" id="poststuff">
        <div class="postbox">
            <div class="handlediv"><br></div>
            <h3 class="hndle"><span><?php _e('Custom Field Suite', 'cfs'); ?> <?php echo $this->version; ?></span></h3>
            <div class="inside">
                <div class="field">
                    <p>
                        <a href="http://uproot.us/projects/cfs/documentation/" target="_blank"><?php _e('Documentation', 'cfs'); ?></a> &nbsp; | &nbsp;
                        <a href="http://uproot.us/donate/" target="_blank"><?php _e('Donate', 'cfs'); ?></a> &nbsp; | &nbsp;
                        <a href="http://uproot.us/projects/cfs/changelog/" target="_blank"><?php _e('Changelog', 'cfs'); ?></a>
                    </p>
                </div>
                <div class="field">
                    <p>
                        <a class="button" href="http://wordpress.org/extend/plugins/custom-field-suite/" target="_blank"><?php _e('Review CFS on WordPress.org', 'cfs'); ?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
