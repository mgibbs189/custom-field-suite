<script>
(function($) {
    $(function() {
        $('.tablenav.top, .search-box').hide();
        $('.subsubsub').append($('#attribution').html());
    });
})(jQuery);
</script>

<div id="attribution" class="hidden">
    <li> | <?php _e('If you enjoy CFS, also check out','cfs'); ?> <a href="https://facetwp.com/?cfs=1" target="_blank">FacetWP</a> <span class="dashicons dashicons-thumbs-up"></span></li>
</div>
