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

        add_action('cfs_init', array($this, 'init'));
        add_action('wp_head', array($this, 'head_scripts'));
        add_action('admin_head', array($this, 'head_scripts'));

        // Start the session
        if ('' == session_id()) {
            session_start();
        }
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
        // Save the form
        if (isset($_POST['cfs']['save']))
        {
            if (wp_verify_nonce($_POST['cfs']['save'], 'cfs_save_input'))
            {
                $field_data = isset($_POST['cfs']['input']) ? $_POST['cfs']['input'] : array();
                $post_data = array();

                if (isset($_POST['cfs']['public']))
                {
                    $post_id = (int) $_SESSION['cfs']['post_id'];
                    $field_groups = isset($_SESSION['cfs']['field_groups']) ? $_SESSION['cfs']['field_groups'] : array();
                }
                else
                {
                    $post_id = (int) $_POST['cfs']['post_id'];
                    $field_groups = isset($_POST['cfs']['field_groups']) ? $_POST['cfs']['field_groups'] : array();
                }

                // Existing post ID
                if (0 < $post_id) {
                    $post_data['ID'] = $post_id;
                }

                // Sanitize field groups
                foreach ($field_groups as $key => $val)
                {
                    $field_groups[$key] = (int) $val;
                }

                // Title
                if (isset($_POST['cfs']['post_title'])) {
                    $post_data['post_title'] = stripslashes($_POST['cfs']['post_title']);
                }

                // Content
                if (isset($_POST['cfs']['post_content'])) {
                    $post_data['post_content'] = stripslashes($_POST['cfs']['post_content']);
                }

                // New posts
                if (-1 == $post_id) {
                    // Post type
                    if (isset($_SESSION['cfs']['post_type'])) {
                        $post_data['post_type'] = $_SESSION['cfs']['post_type'];
                    }

                    // Post status
                    if (isset($_SESSION['cfs']['post_status'])) {
                        $post_data['post_status'] = $_SESSION['cfs']['post_status'];
                    }
                }

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
        wp_enqueue_script('cfs-validation', $this->parent->url . '/assets/js/validation.js');
        wp_enqueue_script('tiptip', $this->parent->url . '/assets/js/tipTip/jquery.tipTip.js');
        wp_enqueue_style('tiptip', $this->parent->url . '/assets/js/tipTip/tipTip.css');
        wp_enqueue_style('cfs-input', $this->parent->url . '/assets/css/input.css');

        // Allow for custom client-side field validators
        do_action('cfs_custom_validation');
    }


    /*--------------------------------------------------------------------------------------
    *
    *    head_scripts
    *
    *    @author Matt Gibbs
    *    @since 1.8.8
    *
    *-------------------------------------------------------------------------------------*/

    function head_scripts()
    {
    ?>

<script>
var CFS = {
    'validators': {},
    'get_field_value': {},
    'loop_buffer': []
};
</script>

    <?php
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
            'group_id' => false,
            'post_id' => false, // set to -1 for new posts
            'post_title' => false,
            'post_content' => false,
            'post_status' => 'draft',
            'post_type' => 'post',
            'front_end' => true,
        );

        $params = array_merge($defaults, $params);
        $input_fields = array();

        $post_id = empty($params['post_id']) ? $post->ID : $params['post_id'];

        if (false !== $params['group_id'])
        {
            $group_id = (int) $params['group_id'];
        }
        else
        {
            $group_id = $this->parent->api->get_matching_groups($post_id, true);
            $group_id = array_keys($group_id);
        }

        if (!empty($group_id))
        {
            $input_fields = $this->parent->api->get_input_fields(array('group_id' => $group_id));
        }

        // Hook to allow for overridden field settings
        $input_fields = apply_filters('cfs_pre_render_fields', $input_fields, $params);

        if (false !== $params['front_end'])
        {
            // Session variables for front-end forms
            $_SESSION['cfs'] = array(
                'post_id' => $post_id,
                'post_type' => $params['post_type'],
                'post_status' => $params['post_status'],
                'field_groups' => $group_id,
            );
    ?>

<div class="cfs_input no_box">
    <form id="post" method="post" action="">

    <?php
        }

        if (false !== $params['post_title'])
        {
    ?>

        <div class="field" data-validator="required">
            <label>Post Title</label>
            <input type="text" name="cfs[post_title]" value="<?php echo esc_attr($post->post_title); ?>" />
        </div>

    <?php
        }

        if (false !== $params['post_content'])
        {
    ?>

        <div class="field">
            <label>Post Content</label>
            <textarea name="cfs[post_content]"><?php echo esc_textarea($post->post_content); ?></textarea>
        </div>

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
            <?php if ('loop' == $field->type) : ?>
            <span class="cfs_loop_toggle" title="Toggle row visibility"></span>
            <?php endif; ?>

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
                    'group_id' => $field->group_id,
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
        <input type="hidden" name="cfs[post_id]" value="<?php echo $post_id; ?>" />

        <?php if (false === $params['front_end']) : ?>

        <input type="hidden" name="cfs[field_groups][]" value="<?php echo $group_id; ?>" />

        <?php else : ?>

        <input type="hidden" name="cfs[public]" value="1" />
        <input type="submit" value="Submit" />
    </form>
</div>

    <?php
        endif;
    }
}
