<?php

global $wpdb;

$sql = "
SELECT ID, post_title
FROM $wpdb->posts
WHERE post_type = 'cfs' AND post_status = 'publish'
ORDER BY post_title";
$results = $wpdb->get_results($sql);
?>

<script>
(function($) {
    $(function() {
        $('#button-export').click(function() {
            var val = $('#field-groups').val();
            if (null !== val) {
                $.post(ajaxurl, {
                    action: 'cfs_ajax_handler',
                    action_type: 'export',
                    field_groups: val
                },
                function(response) {
                    $('#export-output').text(response);
                    $('#export-area').show();
                });
            }
        });
        $('#button-import').click(function() {
            $.post(ajaxurl, {
                action: 'cfs_ajax_handler',
                action_type: 'import',
                input: $('#import').val()
            },
            function(response) {
                $('#status-message').html(response);
            });
        });
    });
})(jQuery);
</script>

<div class="wrap">
    <div id="icon-tools" class="icon32"><br /></div>
    <h2><?php _e('Import / Export', 'cfs'); ?></h2>
</div>

<div>
    <h2>Export</h2>
    <table>
        <tr>
            <td style="width:300px; vertical-align:top">
                <div>Which field groups would you like to export?</div>
                <div>
                    <select id="field-groups" style="width:300px; height:120px" multiple="multiple">
                        <?php foreach ($results as $result) : ?>
                        <option value="<?php echo $result->ID; ?>"><?php echo $result->post_title; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <input type="button" id="button-export" class="button" value="Export" />
                </div>
            </td>
            <td style="width:300px; vertical-align:top">
                <div id="export-area" style="display:none">
                    <div>Export code:</div>
                    <div>
                        <textarea id="export-output" style="width:98%; height:120px"></textarea>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div>
    <h2>Import</h2>
    <table>
        <tr>
            <td style="width:300px; vertical-align:top">
                <div>
                    <textarea id="import" style="width:98%; height:120px"></textarea>
                </div>
                <div>
                    <input type="button" id="button-import" class="button" value="Import" />
                </div>
            </td>
            <td style="width:300px; vertical-align:top">
                <div id="status-message">Paste the import code to continue. Existing field groups will be skipped.</div>
            </td>
        </tr>
    </table>
</div>