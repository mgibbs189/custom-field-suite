<script>
(function($) {
    $(function() {
        $('.tablenav.top, .search-box').hide();
        $('.subsubsub').append($('#attribution').html());
    });
})(jQuery);
</script>

<div id="attribution" class="hidden">
    <li>
         |
        <?php
        printf(
            __( 'If you enjoy CFS, also check out %s', 'cfs' ),
            '<a href="https://facetwp.com/?cfs=1" target="_blank">FacetWP</a>'
        );
        ?>
        <span class="dashicons dashicons-thumbs-up"></span></li>
</div>
