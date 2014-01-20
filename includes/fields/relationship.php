<?php

class cfs_relationship extends cfs_field
{

    function __construct( $parent ) {
        $this->name = 'relationship';
        $this->label = __( 'Relationship', 'cfs' );
        $this->parent = $parent;
    }




    function html( $field ) {
        global $wpdb;

        $where = '';
        $selected_posts = array();
        $available_posts = array();

        // Limit to chosen post types
        if ( isset( $field->options['post_types'] ) ) {
            $where = array();
            foreach ( $field->options['post_types'] as $type ) {
                $where[] = $type;
            }
            $where = " AND post_type IN ('" . implode( "','", $where ) . "')";
        }

        $results = $wpdb->get_results( "SELECT ID, post_type, post_status, post_title FROM $wpdb->posts WHERE post_status IN ('publish','private') $where ORDER BY post_title" );
        foreach ( $results as $result ) {
            $result->post_title = ( 'private' == $result->post_status ) ? '(Private) ' . $result->post_title : $result->post_title;
            $available_posts[] = $result;
        }

        if ( !empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT ID, post_status, post_title FROM $wpdb->posts WHERE ID IN ($field->value) ORDER BY FIELD(ID,$field->value)" );
            foreach ( $results as $result ) {
                $result->post_title = ('private' == $result->post_status) ? '(Private) ' . $result->post_title : $result->post_title;
                $selected_posts[$result->ID] = $result;
            }
        }
    ?>
        <div class="filter_posts">
            <input type="text" class="cfs_filter_input" autocomplete="off" />
            <div class="cfs_filter_help">
                <div class="cfs_tooltip hidden">
                    <ul>
                        <li style="font-size:15px; font-weight:bold">Sample queries</li>
                        <li>"foobar" (find posts containing "foobar")</li>
                        <li>"type:page" (find pages)</li>
                        <li>"type:page foobar" (find pages containing "foobar")</li>
                        <li>"type:page,post foobar" (find posts or pages with "foobar")</li>
                        <li></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="available_posts post_list">
        <?php foreach ( $available_posts as $post ) : ?>
            <?php $class = ( isset( $selected_posts[$post->ID] ) ) ? ' class="used"' : ''; ?>
            <div rel="<?php echo $post->ID; ?>" post_type="<?php echo $post->post_type; ?>"<?php echo $class; ?> title="<?php echo $post->post_type; ?>"><?php echo $post->post_title; ?></div>
        <?php endforeach; ?>
        </div>

        <div class="selected_posts post_list">
        <?php foreach ( $selected_posts as $post ) : ?>
            <div rel="<?php echo $post->ID; ?>"><span class="remove"></span><?php echo $post->post_title; ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }




    function options_html( $key, $field ) {

        $post_types = isset( $field->options['post_types'] ) ? $field->options['post_types'] : null;

        $params = apply_filters( 'facetwp_field_relationship_post_types', array(
            'exclude_from_search' => false
        ) );

        $choices = get_post_types( $params );
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Post Types', 'cfs'); ?></label>
                <p class="description"><?php _e('Limit posts to the following types', 'cfs'); ?></p>
            </td>
            <td>
                <?php
                    $this->parent->create_field( array(
                        'type'          => 'select',
                        'input_name'    => "cfs[fields][$key][options][post_types]",
                        'options'       => array( 'multiple' => '1', 'choices' => $choices ),
                        'value'         => $this->get_option( $field, 'post_types' ),
                    ));
                ?>
            </td>
        </tr>
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
            update_relationship_values = function(field) {
                var post_ids = [];
                field.find('.selected_posts div').each(function(idx) {
                    post_ids[idx] = $(this).attr('rel');
                });
                field.find('input.relationship').val(post_ids.join(','));
            }

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_relationship:not(.ready)').init_relationship();
                });
                $('.cfs_relationship').init_relationship();
            });

            $.fn.init_relationship = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // tooltip
                    $this.find('.cfs_filter_help').tipTip({
                        maxWidth: '400px',
                        content: $this.find('.cfs_tooltip').html()
                    });

                    // sortable
                    $this.find('.selected_posts').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_relationship_values(parent);
                        }
                    });

                    // add selected post
                    $this.find('.available_posts div').live('click', function() {
                        var parent = $(this).closest('.field');
                        var post_id = $(this).attr('rel');
                        var html = $(this).html();
                        $(this).addClass('used');
                        parent.find('.selected_posts').append('<div rel="'+post_id+'"><span class="remove"></span>'+html+'</div>');
                        update_relationship_values(parent);
                    });

                    // remove selected post
                    $this.find('.selected_posts span.remove').live('click', function() {
                        var div = $(this).parent();
                        var parent = div.closest('.field');
                        var post_id = div.attr('rel');
                        parent.find('.available_posts div[rel='+post_id+']').removeClass('used');
                        div.remove();
                        update_relationship_values(parent);
                    });

                    // filter posts
                    $this.find('.cfs_filter_input').live('keyup', function() {
                        var input = $(this).val();
                        var output = { types: [], keywords: [] };
                        var pieces = output.keywords = input.split(' ');
                        var parent = $(this).closest('.field');
                        for (i in pieces) {
                            var piece = pieces[i];
                            if ('type:' == piece.substr(0, 5)) {
                                output.types = piece.substr(5);
                                if (output.types.indexOf(',') !== -1) {
                                    output.types = output.types.split(',');
                                }
                                else {
                                    output.types = [output.types];
                                }
                                output.keywords.splice(i, 1);
                            }
                        }
                        output.keywords = output.keywords.join(' ');
                        var regex = new RegExp(output.keywords, 'i');
                        parent.find('.available_posts div:not(.used)').each(function() {
                            var post_type = $(this).attr('post_type');
                            if (output.types.length > 0 && $.inArray(post_type, output.types)) {
                                $(this).addClass('hidden');
                                return;
                            }
                            if (-1 < $(this).html().search(regex)) {
                                $(this).removeClass('hidden');
                            }
                            else {
                                $(this).addClass('hidden');
                            }
                        });
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
        if ( !empty( $value ) ) {
            // Inside a loop, the value is $value[0]
            $value = (array) $value;

            // The raw input saves a comma-separated string
            if ( false !== strpos( $value[0], ',' ) ) {
                return explode( ',', $value[0] );
            }

            return $value;
        }

        return array();
    }
}
