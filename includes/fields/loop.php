<?php

class cfs_loop extends cfs_field
{

    function __construct() {
        $this->name = 'loop';
        $this->label = __( 'Loop', 'cfs' );
        $this->values = [];
    }


    /*
    ================================================================
        html
    ================================================================
    */
    function html( $field ) {
        global $post;

        $this->values = CFS()->api->get_fields( $post->ID, [ 'format' => 'input' ] );
        $this->recursive_clone( $field->group_id, $field->id );
        $this->recursive_html( $field->group_id, $field->id );
    }


    /*
    ================================================================
        options_html
    ================================================================
    */
    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Row Display', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][row_display]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'row_display' ),
                        'options' => [ 'message' => __( 'Show the values by default', 'cfs' ) ],
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Row Label', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][row_label]",
                        'value' => $this->get_option( $field, 'row_label', __( 'Loop Row', 'cfs' ) ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Button Label', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][button_label]",
                        'value' => $this->get_option( $field, 'button_label', __( 'Add Row', 'cfs' ) ),
                    ] );
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


    /*
    ================================================================
        recursive_clone
    ================================================================
    */
    function recursive_clone( $group_id, $field_id ) {
        $loop_field_ids = [];

        // Get loop field
        $loop_field = CFS()->api->get_input_fields( [
            'field_id' => $field_id
        ] );

        // Get sub-fields
        $results = CFS()->api->get_input_fields( [
            'group_id' => $group_id,
            'parent_id' => $field_id
        ] );

        $row_label = $this->dynamic_label(
            $this->get_option( $loop_field[ $field_id ], 'row_label', __( 'Loop Row', 'cfs' ) )
        );

        ob_start();
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head open">
                <a class="cfs_delete_field" href="javascript:;"></a>
                <a class="cfs_toggle_field" href="javascript:;"></a>
                <a class="cfs_insert_field" href="javascript:;"></a>
                <span class="label"><?php echo esc_attr( $row_label ); ?></span>
            </div>
            <div class="cfs_loop_body open">
            <?php foreach ( $results as $field ) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if ( ! empty( $field->notes ) ) : ?>
                <p class="notes"><?php echo $field->notes; ?></p>
                <?php endif; ?>

                <div class="field field-<?php echo $field->name; ?> cfs_<?php echo $field->type; ?>">
                <?php
                if ( 'loop' == $field->type ) :
                    $loop_field_ids[] = $field->id;
                ?>
                    <div class="table_footer">
                        <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr( $this->get_option( $field, 'button_label', __( 'Add Row', 'cfs' ) ) ); ?>" data-loop-tag="[clone][<?php echo $field->id; ?>]" data-rows="0" />
                    </div>
                <?php else : ?>
                <?php
                    CFS()->create_field( [
                        'type' => $field->type,
                        'input_name' => "cfs[input][clone][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                        'value' => $this->get_option( $field, 'default_value' ),
                    ] );
                ?>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    <?php
        $buffer = ob_get_clean();
    ?>

        <script>
        CFS.loop_buffer[<?php echo $field_id; ?>] = <?php echo json_encode( $buffer ); ?>;
        </script>

    <?php
        foreach ( $loop_field_ids as $loop_field_id ) {
            $this->recursive_clone( $group_id, $loop_field_id );
        }
    }


    /*
    ================================================================
        recursive_html
    ================================================================
    */
    function recursive_html( $group_id, $field_id, $parent_tag = '', $parent_weight = 0 ) {

        // Get loop field
        $loop_field = CFS()->api->get_input_fields( [
            'field_id' => $field_id
        ] );

        // Get sub-fields
        $results = CFS()->api->get_input_fields( [
            'group_id' => $group_id,
            'parent_id' => $field_id
        ] );

        // Dynamically build the $values array
        $parent_tag = empty( $parent_tag ) ? "[$field_id]" : $parent_tag;
        eval( "\$values = isset(\$this->values{$parent_tag} ) ? \$this->values{$parent_tag} : false;" );

        // Row options
        $row_display = $this->get_option( $loop_field[ $field_id ], 'row_display', 0 );
        $row_label = $this->get_option( $loop_field[ $field_id ], 'row_label', __( 'Loop Row', 'cfs' ) );
        $button_label = $this->get_option( $loop_field[ $field_id ], 'button_label', __( 'Add Row', 'cfs' ) );
        $css_class = ( 0 < (int) $row_display ) ? ' open' : '';

        // Do the dirty work
        $row_offset = -1;

        if ( $values ) :
            foreach ( $values as $i => $value ) :
                $row_offset = max( $i, $row_offset );
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head<?php echo $css_class; ?>">
                <a class="cfs_delete_field" href="javascript:;"></a>
                <a class="cfs_toggle_field" href="javascript:;"></a>
                <a class="cfs_insert_field" href="javascript:;"></a>
                <span class="label"><?php echo esc_attr( $this->dynamic_label( $row_label, $results, $values[ $i ] ) ); ?>&nbsp;</span>
            </div>
            <div class="cfs_loop_body<?php echo $css_class; ?>">
            <?php foreach ( $results as $field ) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if ( ! empty( $field->notes ) ) : ?>
                <p class="notes"><?php echo $field->notes; ?></p>
                <?php endif; ?>

                <div class="field field-<?php echo $field->name; ?> cfs_<?php echo $field->type; ?>">
                <?php if ( 'loop' == $field->type ) : ?>
                    <?php $this->recursive_html( $group_id, $field->id, "{$parent_tag}[$i][$field->id]", $i ); ?>
                <?php else : ?>
                <?php
                    $args = [
                        'type' => $field->type,
                        'input_name' => "cfs[input]{$parent_tag}[$i][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                    ];

                    if ( isset( $values[ $i ][ $field->id ] ) ) {
                        $args['value'] = $values[ $i ][ $field->id ];
                    }
                    elseif ( isset( $field->options['default_value'] ) ) {
                        $args['value'] = $field->options['default_value'];
                    }

                    CFS()->create_field( $args );
                ?>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>

        <div class="table_footer">
            <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr( $button_label ); ?>" data-loop-tag="<?php echo $parent_tag; ?>" data-rows="<?php echo ( $row_offset + 1 ); ?>" />
        </div>
    <?php
    }


    /*---------------------------------------------------------------------------------------------
        input_head
    ---------------------------------------------------------------------------------------------*/

    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('click', '.cfs_add_field', function() {
                    var num_rows = $(this).attr('data-rows');
                    var loop_tag = $(this).attr('data-loop-tag');
                    var loop_id = loop_tag.match(/.*\[(.*?)\]/)[1];
                    var html = CFS.loop_buffer[loop_id].replace(/\[clone\]/g, loop_tag + '[' + num_rows + ']');
                    $(this).attr('data-rows', parseInt(num_rows)+1);
                    $(html).insertBefore( $(this).closest('.table_footer') ).addClass('loop_wrapper_new');
                    $(this).trigger('cfs/ready');
                });

                $(document).on('click', '.cfs_insert_field', function(event) {
                    event.stopPropagation();
                    var $add_field = $('.cfs_add_field');
                    var num_rows = $add_field.attr('data-rows');
                    var loop_tag = $add_field.attr('data-loop-tag');
                    var loop_id = loop_tag.match(/.*\[(.*?)\]/)[1];
                    var html = CFS.loop_buffer[loop_id].replace(/\[clone\]/g, loop_tag + '[' + num_rows + ']');
                    $add_field.attr('data-rows', parseInt(num_rows)+1);
                    $(html).insertAfter( $(this).closest('.loop_wrapper') ).addClass('loop_wrapper_new');
                    $add_field.trigger('cfs/ready');
                });

                $(document).on('click', '.cfs_delete_field', function(event) {
                    if (confirm('Remove this row?')) {
                        $(this).closest('.loop_wrapper').remove();
                    }
                    event.stopPropagation();
                });

                $(document).on('click', '.cfs_loop_head', function() {
                    $(this).toggleClass('open');
                    $(this).siblings('.cfs_loop_body').toggleClass('open');
                });

                // Hide or show all rows
                // The HTML is located in includes/form.php
                $(document).on('click', '.cfs_loop_toggle', function() {
                    $(this).closest('.field').find('.cfs_loop_head').toggleClass('open');
                    $(this).closest('.field').find('.cfs_loop_body').toggleClass('open');
                });

                $('.cfs_loop').sortable({
                    axis: 'y',
                    containment: 'parent',
                    items: '.loop_wrapper',
                    handle: '.cfs_loop_head',
                    update: function(event, ui) {

                        // To re-order field names:
                        // 1. Get the depth of the dragged element
                        // 2. Loop through each input field within the dragged element
                        // 3. Reset the array index within the name attribute
                        var $container = ui.item.closest('.field');
                        var depth = $container.closest('.cfs_loop').parents('.cfs_loop').length;
                        var array_element = 3 + (depth * 2);

                        var counter = -1;
                        var last_index = -1;
                        $container.find('[name^="cfs[input]"]').each(function() {
                            var name_attr = $(this).attr('name').split('[');
                            var current_index = parseInt( name_attr[array_element] );
                            if (current_index != last_index) {
                                counter += 1;
                            }
                            name_attr[array_element] = counter + ']';
                            last_index = current_index;
                            $(this).attr('name', name_attr.join('['));
                        });
                    }
                });
            });
        })(jQuery);
        </script>
    <?php
    }


    /*
    ================================================================
        dynamic_label
    ================================================================
    */
    function dynamic_label( $row_label, $fields = [], $values = [] ) {

        // Exit stage left
        if ( '{' != substr( $row_label, 0, 1 ) || '}' != substr( $row_label, -1 ) ) {
            return $row_label;
        }

        $field = false;
        $fallback = false;
        $field_name = substr( $row_label, 1, -1 );

        // Check for fallback value
        if ( false !== strpos( $field_name, ':' ) ) {
            list( $field_name, $fallback ) = explode( ':', $field_name );
        }

        // Get all field names and IDs
        foreach ( $fields as $f ) {
            if ( $field_name == $f->name ) {
                $field = $f;
                break;
            }
        }

        if ( ! empty( $field ) && isset( $values[ $field->id ] ) ) {
            if ( 'select' == $field->type ) {
                $select_key = reset( $values[ $field->id ] );
                $row_label = $field->options['choices'][ $select_key ];
            }
            else {
                $row_label = $values[ $field->id ];
            }
        }
        elseif ( false !== $fallback ) {
             $row_label = $fallback;
        }

        return $row_label;
    }


    /*
    ================================================================
        prepare_value
    ================================================================
    */
    function prepare_value( $value, $field = null ) {
        return $value;
    }
}
