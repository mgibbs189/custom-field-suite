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
        $this->values = $this->parent->api->get_fields($post->ID, array('for_input' => true));
        $this->recursive_clone($field->group_id, $field->id);
        $this->recursive_html($field->group_id, $field->id);
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
            <a class="cfs_delete_field"></a>
        <?php foreach ($results as $field) : ?>
            <label><?php echo $field->label; ?></label>

            <?php if (!empty($field->instructions)) : ?>
            <p class="instructions"><?php echo $field->instructions; ?></p>
            <?php endif; ?>

            <div class="field cfs_<?php echo $field->type; ?>">
            <?php if ('loop' == $field->type) : ?>
                <div class="table_footer">
                    <input type="button" class="button-primary cfs_add_field" value="Add Row" data-loop-tag="[clone][<?php echo $field->id; ?>]" data-num-rows="0" />
                </div>
            <?php else : ?>
            <?php
                $this->parent->create_field((object) array(
                    'type' => $field->type,
                    'input_name' => "cfs[input][clone][$field->id][value][]",
                    'input_class' => $field->type,
                    'options' => $field->options,
                ));
            ?>
            <?php endif; ?>
            </div>
        <?php endforeach; ?>
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
            $this->recursive_clone($group_id, $field->id, $fields);
        }
    }

    /*---------------------------------------------------------------------------------------------
        recursive_html
    ---------------------------------------------------------------------------------------------*/

    function recursive_html($group_id, $field_id, $parent_tag = '', $parent_weight = 0)
    {
        $results = $this->parent->api->get_input_fields($group_id, $field_id);
        $parent_tag = empty($parent_tag) ? "[$field_id]" : $parent_tag;
        eval("\$values = \$this->values{$parent_tag};");

        $offset = 0;

        if ($values) :
        foreach ($values as $i => $value) :
            $offset = ($i + 1);
    ?>
        <div class="loop_wrapper">
            <a class="cfs_delete_field"></a>
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

        <?php endforeach; endif; ?>

        <div class="table_footer">
            <input type="button" class="button-primary cfs_add_field" value="Add Row" data-loop-tag="<?php echo $parent_tag; ?>" data-num-rows="<?php echo $offset; ?>" />
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
                $('.cfs_add_field').unbind('click');
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
