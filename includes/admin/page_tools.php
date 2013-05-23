<?php

global $wpdb;

$sql = "
SELECT ID, post_title
FROM $wpdb->posts
WHERE post_type = 'cfs' AND post_status = 'publish'
ORDER BY post_title";
$results = $wpdb->get_results($sql);
?>

<style type="text/css">
.nav-tab { cursor: pointer; }
.nav-tab:first-child { margin-left: 15px; }
.tab-content { display: none; }
.tab-content.active { display: block; }
#button-export, #button-sync { margin-top: 4px; }
#icon-edit { background: url(<?php echo $this->url; ?>/assets/images/logo.png) no-repeat; }
</style>

<script>
(function($) {
    $(function() {
        $('.nav-tab').click(function() {
            $('.tab-content').removeClass('active');
            $('.nav-tab').removeClass('nav-tab-active');
            $('.tab-content.' + $(this).attr('rel')).addClass('active');
            $(this).addClass('nav-tab-active');
        });

        $('#button-export').click(function() {
            var groups = $('#export-field-groups').val();
            if (null != groups) {
                $.post(ajaxurl, {
                    action: 'cfs_ajax_handler',
                    action_type: 'export',
                    field_groups: $('#export-field-groups').val()
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
                import_code: $('#import-code').val()
            },
            function(response) {
                $('#import-message').html(response);
            });
        });

        $('#button-reset').click(function() {
            if (confirm('This will delete all CFS data. Are you sure?')) {
                $.post(ajaxurl, {
                    action: 'cfs_ajax_handler',
                    action_type: 'reset'
                },
                function(response) {
                    window.location.replace(response);
                });
            }
        });
    });
})(jQuery);
</script>

<div class="wrap">
    <div id="icon-edit" class="icon32"><br></div>
    <h2>Tools</h2>

    <h3 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active" rel="export"><?php _e('Export', 'cfs'); ?></a>
        <a class="nav-tab" rel="import"><?php _e('Import', 'cfs'); ?></a>
        <a class="nav-tab" rel="debug"><?php _e('Debug', 'cfs'); ?></a>
        <a class="nav-tab" rel="reset"><?php _e('Reset', 'cfs'); ?></a>
    </h3>

    <div class="content-container">

        <!-- Export -->

        <div class="tab-content export active">
            <h2><?php _e('Which field groups would you like to export?', 'cfs'); ?></h2>
            <table>
                <tr>
                    <td style="width:300px; vertical-align:top">
                        <div>
                            <select id="export-field-groups" style="width:300px; height:200px" multiple="multiple">
                                <?php foreach ($results as $result) : ?>
                                <option value="<?php echo $result->ID; ?>"><?php echo $result->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input type="button" id="button-export" class="button" value="<?php _e('Export', 'cfs'); ?>" />
                        </div>
                    </td>
                    <td style="width:300px; vertical-align:top">
                        <div id="export-area" style="display:none">
                            <div>
                                <textarea id="export-output" style="width:98%; height:200px"></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Import -->

        <div class="tab-content import">
            <h2><?php _e('Paste the import code below.', 'cfs'); ?></h2>
            <table>
                <tr>
                    <td style="width:300px; vertical-align:top">
                        <div>
                            <textarea id="import-code" style="width:98%; height:200px"></textarea>
                        </div>
                        <div>
                            <input type="button" id="button-import" class="button" value="<?php _e('Import', 'cfs'); ?>" />
                        </div>
                    </td>
                    <td style="width:300px; vertical-align:top">
                        <div id="import-message"></div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Debug Information -->

        <div class="tab-content debug">
            <h2><?php _e('Debug information', 'cfs'); ?></h2>
<?php
global $wp_version;

echo '<textarea style="width:600px; height:200px">';
echo 'WordPress ' . $wp_version . "\n";
echo 'PHP ' . phpversion() . "\n";
echo $_SERVER['SERVER_SOFTWARE'] . "\n";
echo $_SERVER['HTTP_USER_AGENT'] . "\n";
echo "\n--- Active Plugins ---\n";

$all_plugins = get_plugins();
foreach ($all_plugins as $plugin_file => $plugin_data) {
    if (is_plugin_active($plugin_file)) {
        echo $plugin_data['Name'] . ' ' . $plugin_data['Version'] . "\n";
    }
}

echo '</textarea>';
?>
        </div>

        <!-- Reset -->

        <div class="tab-content reset">
            <h2><?php _e('Reset and deactivate.', 'cfs'); ?></h2>
            <p><?php _e('This will delete all CFS data and deactivate the plugin.', 'cfs'); ?></p>
            <input type="button" id="button-reset" class="button" value="<?php _e('Delete everything', 'cfs'); ?>" />
        </div>
    </div>
</div>
