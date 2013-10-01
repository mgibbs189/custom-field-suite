<style type="text/css">
.tablenav.top {
    display: none;
}

.search-box {
    display: none;
}

#posts-filter {
    margin-right: 280px;
}

.posts-sidebar {
    position: absolute;
    right: 0px;
    width: 270px;
    margin: 40px 0 0 0;
}

.posts-sidebar h3 {
    font-size: 16px;
    margin: 0 0 10px 0;
}

#icon-edit {
    background: url(<?php echo $this->url; ?>/assets/images/logo.png) no-repeat;
}
</style>

<script>
(function($) {
    $(function() {
        $('.wp-list-table').before($('#posts-sidebar-wrapper').html());
    });
})(jQuery);
</script>

<div id="posts-sidebar-wrapper" class="hidden">
    <div class="posts-sidebar">
        <h3><?php _e('Custom Field Suite', 'cfs'); ?> <?php echo $this->version; ?></h3>
        <ul>
            <li><a href="https://uproot.us/" target="_blank"><?php _e('Homepage', 'cfs'); ?></a></li>
            <li><a href="https://uproot.us/projects/cfs/documentation/" target="_blank"><?php _e('Documentation', 'cfs'); ?></a></li>
            <li><a href="https://uproot.us/forums/" target="_blank"><?php _e('Forums', 'cfs'); ?></a></li>
            <li><a href="http://wordpress.org/support/view/plugin-reviews/custom-field-suite" target="_blank"><?php _e('Write a review', 'cfs'); ?></a></li>
        </ul>
    </div>
</div>
