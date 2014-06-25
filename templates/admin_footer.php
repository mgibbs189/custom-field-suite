<script>
(function($) {
    $(function() {
        $('.tablenav.top, .search-box').hide();
        $('.subsubsub').append($('#attribution').html());
    });
})(jQuery);
</script>

<div id="attribution" class="hidden">
    <li> | <a href="http://customfieldsuite.com/" target="_blank">Custom Field Suite <span class="count">(v<?php echo CFS_VERSION; ?>)</span></a></li>
</div>