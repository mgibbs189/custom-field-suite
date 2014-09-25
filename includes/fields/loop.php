<?php

class cfs_loop extends cfs_field
{

    function __construct()
    {
        $this->name = 'loop';
        $this->label = __('Loop', 'cfs');
        $this->values = array();
    }




    /*---------------------------------------------------------------------------------------------
        html
    ---------------------------------------------------------------------------------------------*/

    function html($field)
    {
        global $post;

        $this->values = CFS()->api->get_fields($post->ID, array('format' => 'input'));
        $this->recursive_clone($field->group_id, $field->id);
        $this->recursive_html($field->group_id, $field->id);
    }




    /*---------------------------------------------------------------------------------------------
        options_html
    ---------------------------------------------------------------------------------------------*/

    function options_html($key, $field)
    {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Row Display', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][row_display]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'row_display'),
                        'options' => array('message' => __('Show the values by default', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Row Label', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][row_label]",
                        'value' => $this->get_option($field, 'row_label', __('Loop Row', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e('Button Label', 'cfs'); ?></label>
            </td>
            <td>
                <?php
                    CFS()->create_field(array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][button_label]",
                        'value' => $this->get_option($field, 'button_label', __('Add Row', 'cfs')),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }




    /*---------------------------------------------------------------------------------------------
        recursive_clone
    ---------------------------------------------------------------------------------------------*/

    function recursive_clone($group_id, $field_id)
    {
        $loop_field_ids = array();
        $loop_field = CFS()->api->get_input_fields(array('field_id' => $field_id));
        $row_label = $this->get_option($loop_field[$field_id], 'row_label', __('Loop Row', 'cfs'));

        // Get the sub-fields
        $results = CFS()->api->get_input_fields(array('group_id' => $group_id, 'parent_id' => $field_id));

        ob_start();
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head">
                <a class="cfs_delete_field"></a>
                <a class="cfs_toggle_field"></a>
                <span class="label"><?php echo esc_attr($row_label); ?></span>
            </div>
            <div class="cfs_loop_body open">
            <?php foreach ($results as $field) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if (!empty($field->notes)) : ?>
                <p class="notes"><?php echo $field->notes; ?></p>
                <?php endif; ?>

                <div class="field cfs_<?php echo $field->type; ?>">
                <?php
                if ('loop' == $field->type) :
                    $loop_field_ids[] = $field->id;
                ?>
                    <div class="table_footer">
                        <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr($this->get_option($field, 'button_label', __('Add Row', 'cfs'))); ?>" data-loop-tag="[clone][<?php echo $field->id; ?>]" data-rows="0" />
                    </div>
                <?php else : ?>
                <?php
                    CFS()->create_field(array(
                        'type' => $field->type,
                        'input_name' => "cfs[input][clone][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                        'value' => $this->get_option($field, 'default_value'),
                    ));
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
        CFS.loop_buffer[<?php echo $field_id; ?>] = <?php echo json_encode($buffer); ?>;
        </script>

    <?php
        foreach ($loop_field_ids as $loop_field_id)
        {
            $this->recursive_clone($group_id, $loop_field_id);
        }
    }




    /*---------------------------------------------------------------------------------------------
        dynamic_label
    ---------------------------------------------------------------------------------------------*/

    function dynamic_label($row_label, $fields, $values)
    {
        preg_match_all("/({(.*?)})/", $row_label, $matches);
        if (!empty($matches))
        {
            // Get all field names and IDs
            $all_fields = array();
            foreach ($fields as $field)
            {
                $all_fields[$field->name] = (int) $field->id;
            }

            foreach ($matches[2] as $field_name)
            {
	            // facilitate more advanced label customization by allowing the definition
	            // of a filter by appending ':my_hook_name' to the dynamic field name
	            //     {my_field:my_custom_hook_to_filter_the_row_label}
	            if ( false !== strpos( $field_name, ':' ) )
	            {
					$field_reference = explode( ':', $field_name );
		            $field_name = $field_reference[0];
		            $field_hook = $field_reference[1];
	            }

                $field_id = isset($all_fields[$field_name]) ? $all_fields[$field_name] : false;
                if (isset($values[$field_id]))
                {
	                if ( ! empty( $field_hook ) )
	                {
		                // allow developer to use their custom hook
		                $value = apply_filters( $field_hook, $values[$field_id] );

		                // we modified $field_name for our purpose, but need to put it back for replacement
		                $field_name = $field_name . ':' . $field_hook;
	                }
	                else
	                {
		                $value = $values[$field_id];
	                }

                    $row_label = str_replace('{' . $field_name . '}', $value, $row_label);
                }
            }
        }

        return $row_label;
    }




    /*---------------------------------------------------------------------------------------------
        recursive_html
    ---------------------------------------------------------------------------------------------*/

    function recursive_html($group_id, $field_id, $parent_tag = '', $parent_weight = 0)
    {
        $results = CFS()->api->get_input_fields(array('group_id' => $group_id, 'parent_id' => $field_id));
        $parent_tag = empty($parent_tag) ? "[$field_id]" : $parent_tag;
        eval("\$values = isset(\$this->values{$parent_tag}) ? \$this->values{$parent_tag} : false;");

        // Get field options
        $loop_field = CFS()->api->get_input_fields(array('field_id' => $field_id));
        $row_display = $this->get_option($loop_field[$field_id], 'row_display', 0);
        $row_label = $this->get_option($loop_field[$field_id], 'row_label', __('Loop Row', 'cfs'));
        $button_label = $this->get_option($loop_field[$field_id], 'button_label', __('Add Row', 'cfs'));
        $css_class = (0 < (int) $row_display) ? ' open' : '';

        // Do the dirty work
        $row_offset = -1;

        if ($values) :
            foreach ($values as $i => $value) :
                $row_offset = max($i, $row_offset);
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head">
                <a class="cfs_delete_field"></a>
                <a class="cfs_toggle_field"></a>
                <span class="label"><?php echo esc_attr($this->dynamic_label($row_label, $results, $values[$i])); ?>&nbsp;</span>
            </div>
            <div class="cfs_loop_body<?php echo $css_class; ?>">
            <?php foreach ($results as $field) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if (!empty($field->notes)) : ?>
                <p class="notes"><?php echo $field->notes; ?></p>
                <?php endif; ?>

                <div class="field cfs_<?php echo $field->type; ?>">
                <?php if ('loop' == $field->type) : ?>
                    <?php $this->recursive_html($group_id, $field->id, "{$parent_tag}[$i][$field->id]", $i); ?>
                <?php else : ?>
                <?php
                    $args = array(
                        'type' => $field->type,
                        'input_name' => "cfs[input]{$parent_tag}[$i][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                    );
                    if ( isset( $values[$i][$field->id] ) ) {
                        $args['value'] = $values[$i][$field->id];
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
            <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr($button_label); ?>" data-loop-tag="<?php echo $parent_tag; ?>" data-rows="<?php echo ($row_offset + 1); ?>" />
        </div>
    <?php
    }




    /*---------------------------------------------------------------------------------------------
        input_head
    ---------------------------------------------------------------------------------------------*/

    function input_head($field = null)
    {
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
                    $(this).closest('.table_footer').before(html);
                    $(this).trigger('cfs/ready');
                });

                $(document).on('click', '.cfs_delete_field', function(event) {
                    if (confirm('Remove this row?')) {
                        $(this).closest('.loop_wrapper').remove();
                    }
                    event.stopPropagation();
                });

                $(document).on('click', '.cfs_loop_head', function() {
                    $(this).siblings('.cfs_loop_body').toggleClass('open');
                });

                // Hide or show all rows
                // The HTML is located in includes/form.php
                $(document).on('click', '.cfs_loop_toggle', function() {
                    $(this).closest('.field').find('.cfs_loop_body').toggleClass('open');
                });

                $('.cfs_loop').sortable({
                    axis: 'y',
                    containment: 'parent',
                    items: '.loop_wrapper',
                    handle: '.cfs_loop_head',
                    start: function(event, ui) {
                        $(document).trigger('cfs/sortable_start', ui.item);
                    },
                    stop: function(event, ui) {
                        $(document).trigger('cfs/sortable_stop', ui.item);
                    },
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




    function prepare_value($value, $field = null)
    {
        return $value;
    }
}
