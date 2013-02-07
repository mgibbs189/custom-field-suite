<?php

class cfs_form
{
    public $parent;
    public $used_types;
    public $assets_loaded;

    /*--------------------------------------------------------------------------------------
    *
    *    __construct
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->used_types = array();
        $this->assets_loaded = false;

        add_action('init', array($this, 'init'), 12); // load after CFS
    }


    /*--------------------------------------------------------------------------------------
    *
    *    init
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function init()
    {
        if (isset($_POST['cfs']['save']))
        {
            if (wp_verify_nonce($_POST['cfs']['save'], 'cfs_save_input'))
            {
                $field_groups = isset($_POST['cfs']['field_groups']) ? $_POST['cfs']['field_groups'] : array();
                $field_data = isset($_POST['cfs']['input']) ? $_POST['cfs']['input'] : array();
                $post_data = array('ID' => $_POST['cfs']['post_id']);
                $options = array('format' => 'input', 'field_groups' => $field_groups);
                $this->parent->save($field_data, $post_data, $options);

                if (isset($_POST['cfs']['public']))
                {
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    }


    /*--------------------------------------------------------------------------------------
    *
    *    load_assets
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function load_assets()
    {
        if ($this->assets_loaded)
        {
            return;
        }

        $this->assets_loaded = true;

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('cfs-validation', $this->parent->url . '/js/validation.js');
        wp_enqueue_script('tiptip', $this->parent->url . '/js/tipTip/jquery.tipTip.js');
        wp_enqueue_style('tiptip', $this->parent->url . '/js/tipTip/tipTip.css');
        wp_enqueue_style('cfs-input', $this->parent->url . '/css/input.css');
    ?>

<script>
var CFS = {
    'validators': {},
    'get_field_value': {},
    'loop_buffer': []
};
</script>

    <?php

        do_action('cfs_custom_validation');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    render
    *
    *    @author Matt Gibbs
    *    @since 1.8.5
    *
    *-------------------------------------------------------------------------------------*/

    public function render($params)
    {
        global $post;

        $defaults = array(
            'group_id' => false, // add
            //'group_title' => false, // add
            'front_end' => true,
            //'post_id' => false, // edit
            //'post_type' => 'post', // add
            //'post_status' => 'draft', // add
        );

        $params = array_merge($defaults, $params);

        $group_id = (int) $params['group_id'];
        $post_id = empty($params['post_id']) ? $post->ID : $params['post_id'];
        $input_fields = $this->parent->api->get_input_fields(array('group_id' => $group_id));

        if (false !== $params['front_end'])
        {
    ?>

<div class="cfs_input no_box">
    <form id="post" method="post" action="">

    <?php
        }

        // Add any necessary head scripts
        foreach ($input_fields as $key => $field)
        {
            // Skip missing field types
            if (!isset($this->parent->fields[$field->type]))
            {
                continue;
            }

            if (!isset($this->used_types[$field->type]))
            {
                $this->parent->fields[$field->type]->input_head($field);
                $this->used_types[$field->type] = true;
            }

            // Ignore sub-fields
            if (1 > (int) $field->parent_id)
            {
                $validator = '';

                if (isset($field->options['required']) && 0 < (int) $field->options['required'])
                {
                    if ('date' == $field->type)
                    {
                        $validator = 'valid_date';
                    }
                    elseif ('color' == $field->type)
                    {
                        $validator = 'valid_color';
                    }
                    else
                    {
                        $validator = 'required';
                    }
                }
    ?>

        <div class="field" data-type="<?php echo $field->type; ?>" data-name="<?php echo $field->name; ?>" data-validator="<?php echo $validator; ?>">
            <?php if (!empty($field->label)) : ?>
            <label><?php echo $field->label; ?></label>
            <?php endif; ?>

            <?php if (!empty($field->notes)) : ?>
            <p class="notes"><?php echo $field->notes; ?></p>
            <?php endif; ?>

            <div class="cfs_<?php echo $field->type; ?>">

    <?php
                $this->parent->create_field(array(
                    'id' => $field->id,
                    'group_id' => $group_id,
                    'type' => $field->type,
                    'input_name' => "cfs[input][$field->id][value]",
                    'input_class' => $field->type,
                    'options' => $field->options,
                    'value' => $field->value,
                ));
    ?>

            </div>
        </div>

    <?php
            }
        }
    ?>

        <input type="hidden" name="cfs[save]" value="<?php echo wp_create_nonce('cfs_save_input'); ?>" />
        <input type="hidden" name="cfs[field_groups][]" value="<?php echo $group_id; ?>" />
        <input type="hidden" name="cfs[post_id]" value="<?php echo $post_id; ?>" />

    <?php
        if (false !== $params['front_end'])
        {
    ?>

        <input type="hidden" name="cfs[public]" value="1" />
        <input type="submit" value="Submit" />
    </form>
</div>

    <?php
        }
    }
}
