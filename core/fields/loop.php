<?php

class cfs_Loop extends cfs_Field
{

    function __construct($parent)
    {
        $this->name = 'loop';
        $this->label = __('Loop', 'cfs');
        $this->parent = $parent;
        $this->values = array();
    }

    /*---------------------------------------------------------------------------------------------
        html
    ---------------------------------------------------------------------------------------------*/

    function html($field)
    {
        global $post;

        $this->values = $this->parent->api->get_fields($post->ID, array('for_input' => true));
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
                <label>
                    <?php _e('Button Label', 'cfs'); ?>
                    <span class="cfs_tooltip" title="<?php _e('Default: Add Row', 'cfs'); ?>"></span>
                </label>
            </td>
            <td>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => 'text',
                        'input_name' => "cfs[fields][$key][options][button_label]",
                        'input_class' => '',
                        'value' => $this->get_option($field, 'button_label', 'Add Row'),
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
        $results = $this->parent->api->get_input_fields($group_id, $field_id);

        ob_start();
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head">
                <a class="cfs_delete_field"></a>
                <a class="cfs_toggle_field"></a>
                <span class="label">Loop Row</span> -
                <span class="notes">drag and drop to re-order</span>
            </div>
            <div class="cfs_loop_body">
            <?php foreach ($results as $field) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if (!empty($field->instructions)) : ?>
                <p class="instructions"><?php echo $field->instructions; ?></p>
                <?php endif; ?>

                <div class="field cfs_<?php echo $field->type; ?>">
                <?php if ('loop' == $field->type) : ?>
                    <div class="table_footer">
                        <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr($this->get_option($field, 'button_label', 'Add Row')); ?>" data-loop-tag="[clone][<?php echo $field->id; ?>]" data-num-rows="0" />
                    </div>
                <?php else : ?>
                <?php
                    $this->parent->create_field((object) array(
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

        <script type="text/javascript">
        CFS.loop_buffer[<?php echo $field_id; ?>] = <?php echo json_encode($buffer); ?>;
        </script>

    <?php
        if ('loop' == $field->type)
        {
            $this->recursive_clone($group_id, $field->id);
        }
    }

    /*---------------------------------------------------------------------------------------------
        recursive_html
    ---------------------------------------------------------------------------------------------*/

    function recursive_html($group_id, $field_id, $parent_tag = '', $parent_weight = 0)
    {
        $results = $this->parent->api->get_input_fields($group_id, $field_id);
        $parent_tag = empty($parent_tag) ? "[$field_id]" : $parent_tag;
        eval("\$values = isset(\$this->values{$parent_tag}) ? \$this->values{$parent_tag} : false;");

        $offset = 0;

        if ($values) :
            foreach ($values as $i => $value) :
                $offset = ($i + 1);
    ?>
        <div class="loop_wrapper">
            <div class="cfs_loop_head">
                <a class="cfs_delete_field"></a>
                <a class="cfs_toggle_field"></a>
                <span class="label">Loop Row</span> -
                <span class="notes">drag and drop to re-order</span>
            </div>
            <div class="cfs_loop_body">
            <?php foreach ($results as $field) : ?>
                <label><?php echo $field->label; ?></label>

                <?php if (!empty($field->instructions)) : ?>
                <p class="instructions"><?php echo $field->instructions; ?></p>
                <?php endif; ?>

                <div class="field cfs_<?php echo $field->type; ?>">
                <?php if ('loop' == $field->type) : ?>
                    <?php $this->recursive_html($group_id, $field->id, "{$parent_tag}[$i][$field->id]", $i); ?>
                <?php else : ?>
                <?php
                    $this->parent->create_field((object) array(
                        'type' => $field->type,
                        'input_name' => "cfs[input]{$parent_tag}[$i][$field->id][value][]",
                        'input_class' => $field->type,
                        'options' => $field->options,
                        'value' => $values[$i][$field->id],
                    ));
                ?>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>

        <?php $loop_field = $this->parent->api->get_input_fields(false, false, $field_id); ?>

        <div class="table_footer">
            <input type="button" class="button-primary cfs_add_field" value="<?php echo esc_attr($this->get_option($loop_field[$field_id], 'button_label', 'Add Row')); ?>" data-loop-tag="<?php echo $parent_tag; ?>" data-num-rows="<?php echo $offset; ?>" />
        </div>
    <?php
    }

    /*---------------------------------------------------------------------------------------------
        input_head
    ---------------------------------------------------------------------------------------------*/

    function input_head($field = null)
    {
    ?>
        <script type="text/javascript">
        var CFS = CFS || { loop_buffer: [] };

        (function($) {
            $(function() {
                $('.cfs_add_field').live('click', function() {
                    var num_rows = $(this).attr('data-num-rows');
                    var loop_tag = $(this).attr('data-loop-tag');
                    var loop_id = loop_tag.match(/.*\[(.*?)\]/)[1];
                    var html = CFS.loop_buffer[loop_id].replace(/\[clone\]/g, loop_tag + '[' + num_rows + ']');
                    $(this).attr('data-num-rows', parseInt(num_rows)+1);
                    $(this).closest('.table_footer').before(html);
                    $(this).trigger('go');
                });

                $('.cfs_delete_field').live('click', function() {
                    $(this).closest('.loop_wrapper').remove();
                });

                $('.cfs_loop_head').live('click', function() {
                    $(this).siblings('.cfs_loop_body').toggle();
                    $(this).toggleClass('open');
                });

                $('.cfs_loop').sortable({
                    axis: 'y',
                    containment: 'parent',
                    items: '.loop_wrapper',
                    handle: '.cfs_loop_head',
                    update: function(event, ui) {
                        var counter = {};
                        var last_depth = -1;
                        var loop = ui.item.closest('.cfs_loop');
                        loop.find('[name^="cfs[input]"]').each(function() {
                            // get the loop depth, used to find the correct array element
                            var depth = $(this).closest('.cfs_loop').parents('.cfs_loop').size();
                            var array_index = 3 + (depth * 2);

                            // If depth increases, set counter[depth] = 0
                            // Otherwise, set counter[depth] = counter[depth] + 1
                            counter[depth] = (depth > last_depth) ? 0 : counter[depth] + 1;
                            last_depth = depth;

                            // Update the current input, as well as any children
                            $(this).closest('.loop_wrapper').find('[name^="cfs[input]"]').each(function() {
                                var new_name = $(this).attr('name').split('[');
                                new_name[array_index] = counter[depth] + ']';
                                new_name = new_name.join('[');
                                $(this).attr('name', new_name);
                            });
                        });
                    }
                });
            });
        })(jQuery);
        </script>
    <?php
    }

    function format_value_for_api($value, $field)
    {
        return $value;
    }

    function format_value_for_input($value, $field)
    {
        return $value;
    }
}
