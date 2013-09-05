<?php

class cfs_form
{
    public $parent;
    public $used_types;
    public $assets_loaded;
    public $session;




    /**
     * Constructor
     * @param object $parent 
     * @since 1.8.5
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->used_types = array();
        $this->assets_loaded = false;

        add_action('cfs_init', array($this, 'init'));
        add_action('admin_head', array($this, 'head_scripts'));
        add_action('admin_print_footer_scripts', array($this, 'footer_scripts'));
    }




    /**
     * Initialize the session and save the form
     * @since 1.8.5
     */
    public function init()
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $this->session = new cfs_session();

        // Save the form
        if (isset($_POST['cfs']['save']))
        {
            if (wp_verify_nonce($_POST['cfs']['save'], 'cfs_save_input'))
            {
                $session = $this->session->get();

                if (empty($session))
                {
                    die('Your session has expired.');
                }

                $field_data = isset($_POST['cfs']['input']) ? $_POST['cfs']['input'] : array();
                $post_data = array();

                // Form settings are session-based for added security
                $post_id = (int) $session['post_id'];
                $field_groups = isset($session['field_groups']) ? $session['field_groups'] : array();

                // Sanitize field groups
                foreach ($field_groups as $key => $val) {
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
                if ($post_id < 1) {
                    // Post type
                    if (isset($session['post_type'])) {
                        $post_data['post_type'] = $session['post_type'];
                    }

                    // Post status
                    if (isset($session['post_status'])) {
                        $post_data['post_status'] = $session['post_status'];
                    }
                }
                else {
                    $post_data['ID'] = $post_id;
                }

                $options = array('format' => 'input', 'field_groups' => $field_groups);

                // Hook parameters
                $hook_params = array(
                    'field_data' => $field_data,
                    'post_data' => $post_data,
                    'options' => $options,
                );

                // Pre-save hook
                do_action('cfs_pre_save_input', $hook_params);

                // Save the input values
                $hook_params['post_data']['ID'] = $this->parent->save(
                    $field_data,
                    $post_data,
                    $options
                );

                // After-save hook
                do_action('cfs_after_save_input', $hook_params);

                // Delete expired sessions
                $this->session->cleanup();

                // Redirect public forms
                if (true === $session['front_end']) {
                    $redirect_url = $_SERVER['REQUEST_URI'];
                    if (!empty($session['confirmation_url'])) {
                        $redirect_url = $session['confirmation_url'];
                    }

                    header('Location: ' . $redirect_url);
                    exit;
                }
            }
        }
    }




    /**
     * Load form dependencies
     * @since 1.8.5
     */
    public function load_assets()
    {
        if ($this->assets_loaded)
        {
            return;
        }

        $this->assets_loaded = true;

        add_action('wp_head', array($this, 'head_scripts'));
        add_action('wp_footer', array($this, 'footer_scripts'), 25);

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('cfs-validation', $this->parent->url . '/assets/js/validation.js', array('jquery'));
        wp_enqueue_script('tiptip', $this->parent->url . '/assets/js/tipTip/jquery.tipTip.js', array('jquery'));
        wp_enqueue_style('tiptip', $this->parent->url . '/assets/js/tipTip/tipTip.css');
        wp_enqueue_style('cfs-input', $this->parent->url . '/assets/css/input.css');
    }




    /**
     * Handle front-end validation
     * @since 1.8.8
     */
    function head_scripts()
    {
    ?>

<script>
var CFS = CFS || {};
CFS['validators'] = {};
CFS['get_field_value'] = {};
CFS['loop_buffer'] = [];
</script>

    <?php
    }




    /**
     * Allow for custom client-side validators
     * @since 1.9.5
     */
    function footer_scripts()
    {
        do_action('cfs_custom_validation');
    }




    /**
     * Render the HTML input form
     * @param array $params 
     * @return string form HTML code
     * @since 1.8.5
     */
    public function render($params)
    {
        global $post;

        $defaults = array(
            'post_id' => $post->ID, // set to false for new entries
            'field_groups' => array(), // group IDs, required for new entries
            'post_title' => false,
            'post_content' => false,
            'post_status' => 'draft',
            'post_type' => 'post',
            'confirmation_message' => '',
            'confirmation_url' => '',
            'submit_label' => __('Submit', 'cfs'),
            'front_end' => true,
        );

        $params = array_merge($defaults, $params);
        $input_fields = array();

        $post_id = (int) $params['post_id'];

        if (empty($params['field_groups']))
        {
            $field_groups = $this->parent->api->get_matching_groups($post_id, true);
            $field_groups = array_keys($field_groups);
        }
        else
        {
            $field_groups = $params['field_groups'];
        }

        if (!empty($field_groups))
        {
            $input_fields = $this->parent->api->get_input_fields(array('group_id' => $field_groups));
        }

        // Hook to allow for overridden field settings
        $input_fields = apply_filters('cfs_pre_render_fields', $input_fields, $params);

        // The SESSION should contain all applicable field group IDs. Since add_meta_box only
        // passes 1 field group at a time, we use $cfs->group_ids from admin_head.php
        // to store all group IDs needed for the SESSION.
        $all_group_ids = (false === $params['front_end']) ? $this->parent->group_ids : $field_groups;

        $session_data = array(
            'post_id' => $post_id,
            'post_type' => $params['post_type'],
            'post_status' => $params['post_status'],
            'field_groups' => $all_group_ids,
            'confirmation_message' => $params['confirmation_message'],
            'confirmation_url' => $params['confirmation_url'],
            'front_end' => $params['front_end'],
        );

        // Set the SESSION
        $this->session->set($session_data);

        if (false !== $params['front_end'])
        {
    ?>

<div class="cfs_input no_box">
    <form id="post" method="post" action="">

    <?php
        }

        if (false !== $params['post_title'])
        {
    ?>

        <div class="field" data-validator="required">
            <label><?php _e('Post Title', 'cfs'); ?></label>
            <input type="text" name="cfs[post_title]" value="<?php echo empty($post_id) ? '' : esc_attr($post->post_title); ?>" />
        </div>

    <?php
        }

        if (false !== $params['post_content'])
        {
    ?>

        <div class="field">
            <label><?php _e('Post Content', 'cfs'); ?></label>
            <textarea name="cfs[post_content]"><?php echo empty($post_id) ? '' : esc_textarea($post->post_content); ?></textarea>
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

                if ('relationship' == $field->type)
                {
                    $min = empty($field->options['limit_min']) ? 0 : (int) $field->options['limit_min'];
                    $max = empty($field->options['limit_max']) ? 0 : (int) $field->options['limit_max'];
                    $validator = "limit|$min,$max";
                }

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
            <span class="cfs_loop_toggle" title="<?php esc_html_e( 'Toggle row visibility', 'cfs' ); ?>"></span>
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
        <input type="hidden" name="cfs[session_id]" value="<?php echo $this->session->session_id; ?>" />

        <?php if (false !== $params['front_end']) : ?>

        <input type="submit" value="<?php echo esc_attr($params['submit_label']); ?>" />
    </form>
</div>

    <?php
        endif;
    }
}
