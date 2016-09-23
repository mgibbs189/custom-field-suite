<?php

class cfs_term extends cfs_field
{

    function __construct() {
        $this->name = 'term';
        $this->label = __( 'Term', 'cfs' );
    }


    function html( $field ) {
        global $wpdb;

        $selected_terms = array();
        $available_terms = array();

        $taxonomies = array();
        if ( ! empty( $field->options['taxonomies'] ) ) {
            foreach ( $field->options['taxonomies'] as $taxonomy ) {
                $taxonomies[] = $taxonomy;
            }
        }
        else {
            $post_types = get_taxonomies( array( 'public' => true ) );
        }

        $args = array(
            'taxonomy'   => $post_types,
            'hide_empty' => false,
            'fields'     => 'ids',
            'orderby'    => 'name',
            'order'      => 'ASC'
        );

        $args = apply_filters( 'cfs_field_term_query_args', $args, array( 'field' => $field ) );
        $query = new WP_Term_Query( $args );

        foreach ( $query->terms as $term_id ) {
            $term = get_term( $term_id );
            $available_terms[] = (object) array(
                'term_id'  => $term->term_id,
                'taxonomy' => $term->taxonomy,
                'name'     => $term->name,
            );
        }

        if ( ! empty( $field->value ) ) {
            $results = $wpdb->get_results( "SELECT term_id, name FROM $wpdb->terms WHERE term_id IN ($field->value) ORDER BY FIELD(term_id,$field->value)" );
            foreach ( $results as $result ) {
                $selected_terms[ $result->term_id ] = $result;
            }
        }
    ?>
        <div class="filter_terms">
            <input type="text" class="cfs_filter_input" autocomplete="off" placeholder="<?php _e( 'Search terms', 'cfs' ); ?>" />
        </div>

        <div class="available_terms term_list">
        <?php foreach ( $available_terms as $term ) : ?>
            <?php $class = ( isset( $selected_terms[ $term->term_id ] ) ) ? ' class="used"' : ''; ?>
            <div rel="<?php echo $term->term_id; ?>"<?php echo $class; ?> title="<?php echo $term->name; ?>"><?php echo apply_filters( 'cfs_term_display', $term->name, $term->term_id, $field ); ?></div>
        <?php endforeach; ?>
        </div>

        <div class="selected_terms term_list">
        <?php foreach ( $selected_terms as $term ) : ?>
            <div rel="<?php echo $term->term_id; ?>"><span class="remove"></span><?php echo apply_filters( 'cfs_term_display', $term->name, $term->term_id, $field ); ?></div>
        <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }


    function options_html( $key, $field ) {
        $args = array( 'public' => true );
        $choices = apply_filters( 'cfs_field_term_taxonomies', get_taxonomies( $args ) );

    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Taxonomies', 'cfs'); ?></label>
                <p class="description"><?php _e('Limit terms to the following taxonomies', 'cfs'); ?></p>
            </td>
            <td>
                <?php
                    CFS()->create_field( array(
                        'type'          => 'select',
                        'input_name'    => "cfs[fields][$key][options][taxonomies]",
                        'options'       => array( 'multiple' => '1', 'choices' => $choices ),
                        'value'         => $this->get_option( $field, 'taxonomies' ),
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
            update_term_values = function(field) {
                var term_ids = [];
                field.find('.selected_terms div').each(function(idx) {
                    term_ids[idx] = $(this).attr('rel');
                });
                field.find('input.terms').val(term_ids.join(','));
            }

            $(function() {
                $(document).on('cfs/ready', '.cfs_add_field', function() {
                    $('.cfs_term:not(.ready)').init_term();
                });
                $('.cfs_term').init_term();

                // add selected post
                $(document).on('click', '.cfs_term .available_terms div', function() {
                    var parent = $(this).closest('.field');
                    var term_id = $(this).attr('rel');
                    var html = $(this).html();
                    $(this).addClass('used');
                    parent.find('.selected_terms').append('<div rel="'+term_id+'"><span class="remove"></span>'+html+'</div>');
                    update_term_values(parent);
                });

                // remove selected post
                $(document).on('click', '.cfs_term .selected_terms .remove', function() {
                    var div = $(this).parent();
                    var parent = div.closest('.field');
                    var term_id = div.attr('rel');
                    parent.find('.available_terms div[rel='+post_id+']').removeClass('used');
                    div.remove();
                    update_term_values(parent);
                });

                // filter posts
                $(document).on('keyup', '.cfs_term .cfs_filter_input', function() {
                    var input = $(this).val();
                    var parent = $(this).closest('.field');
                    var regex = new RegExp(input, 'i');
                    parent.find('.available_terms div:not(.used)').each(function() {
                        if (-1 < $(this).html().search(regex)) {
                            $(this).removeClass('hidden');
                        }
                        else {
                            $(this).addClass('hidden');
                        }
                    });
                });
            });

            $.fn.init_term = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // sortable
                    $this.find('.selected_terms').sortable({
                        axis: 'y',
                        update: function(event, ui) {
                            var parent = $(this).closest('.field');
                            update_term_values(parent);
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

        return array();
    }
}
