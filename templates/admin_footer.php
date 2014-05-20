<script>
(function($) {
    $(function() {
        $('.tablenav.top, .search-box').hide();
        $('.subsubsub').append($('#attribution').html());
    });
})(jQuery);
</script>

<div id="attribution" class="hidden">
    <li> | <a href="https://uproot.us/" target="_blank">Custom Field Suite <span class="count">(v<?php echo $this->version; ?>)</span></a></li>
</div>