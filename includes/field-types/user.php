<?php

class cfs_user extends cfs_field
{

    function __construct() {
        $this->name = 'user';
        $this->label = __( 'User', 'cfs' );
    }


    function html( $field ) {
        global $wpdb;

        $selected_users = [];
        $available_users = [];

        $results = $wpdb->get_results( "SELECT ID, user_login FROM $wpdb->users ORDER BY user_login" );
        foreach ( $results as $result ) {
            $available_users[] = $result;
        }

        if ( ! empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT ID, user_login FROM $wpdb->users WHERE ID IN ($field->value) ORDER BY FIELD(ID,$field->value)" );
            foreach ( $results as $result ) {
                $selected_users[ $result->ID ] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfs_filter_input" autocomplete="off" placeholder="<?php _e( 'Search users', 'cfs' ); ?>" />
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_users as $user ) : ?>
            <?php $class = ( isset( $selected_users[ $user->ID ] ) ) ? ' class="used"' : ''; ?>
            <div rel="<?php echo $user->ID; ?>"<?php echo $class; ?>><?php echo apply_filters( 'cfs_user_display', $user->user_login, $user->ID, $field ); ?></div>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_users as $user ) : ?>
            <div rel="<?php echo $user->ID; ?>"><span class="remove"></span><?php echo apply_filters( 'cfs_user_display', $user->user_login, $user->ID, $field ); ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Limits', 'cfs' ); ?></label>
            </td>
            <td>
                <input type="text" name="cfs[fields][<?php echo $key; ?>][options][limit_min]" value="<?php echo $this->get_option( $field, 'limit_min' ); ?>" placeholder="min" style="width:60px" />
                <input type="text" name="cfs[fields][<?php echo $key; ?>][options][limit_max]" value="<?php echo $this->get_option( $field, 'limit_max' ); ?>" placeholder="max" style="width:60px" />
            </td>
        </tr>
    <?php
    }


    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            update_user_values = function(field) {
                var post_ids = [];
                field.find('.selected_posts div').each(function(idx) {
                    post_ids[idx] = $(this).attr('rel');
                });
                field.find('input.user').val(post_ids.join(','));
            }

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_user:not(.ready)').init_user();
                });
                $('.cfs_user').init_user();

                // add selected post
                $(document).on('click', '.cfs_user .available_posts div', function() {
                    var parent = $(this).closest('.field');
                    var post_id = $(this).attr('rel');
                    var html = $(this).html();
                    $(this).addClass('used');
                    parent.find('.selected_posts').append('<div rel="'+post_id+'"><span class="remove"></span>'+html+'</div>');
                    update_user_values(parent);
                });

                // remove selected post
                $(document).on('click', '.cfs_user .selected_posts .remove', function() {
                    var div = $(this).parent();
                    var parent = div.closest('.field');
                    var post_id = div.attr('rel');
                    parent.find('.available_posts div[rel='+post_id+']').removeClass('used');
                    div.remove();
                    update_user_values(parent);
                });

                // filter posts
                $(document).on('keyup', '.cfs_user .cfs_filter_input', function() {
                    var input = $(this).val();
                    var parent = $(this).closest('.field');
                    var regex = new RegExp(input, 'i');
                    parent.find('.available_posts div:not(.used)').each(function() {
                        if (-1 < $(this).html().search(regex)) {
                            $(this).removeClass('hidden');
                        }
                        else {
                            $(this).addClass('hidden');
                        }
                    });
                });
            });

            $.fn.init_user = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // sortable
                    $this.find('.selected_posts').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_user_values(parent);
                        }
                    });
                });
            }
        })(jQuery);
        </script>
    <?php
    }


    function prepare_value( $value, $field = null ) {
        return $value;
    }


    function format_value_for_input( $value, $field = null ) {
        return empty( $value ) ? '' : implode( ',', $value );
    }


    function pre_save( $value, $field = null ) {
        if ( ! empty( $value ) ) {

            // Inside a loop, the value is $value[0]
            $value = (array) $value;

            // The raw input saves a comma-separated string
            if ( false !== strpos( $value[0], ',' ) ) {
                return explode( ',', $value[0] );
            }

            return $value;
        }

        return [];
    }
}
