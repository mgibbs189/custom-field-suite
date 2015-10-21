<script>
(function($) {
    $(function() {
        $('.tablenav.top, .search-box').hide();
        $('.subsubsub').append($('#attribution').html());
    });
})(jQuery);
</script>

<div id="attribution" class="hidden">
    <li> | <span class="dashicons dashicons-star-filled"></span> Check out <a href="https://facetwp.com/?cfs=1" target="_blank">FacetWP</a> if you enjoy Custom Field Suite <?php echo CFS_VERSION; ?> <span class="dashicons dashicons-star-filled"></span></li>
</div>
