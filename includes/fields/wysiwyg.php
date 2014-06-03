<?php

class cfs_wysiwyg extends cfs_field
{

    public $wp_default_editor;


    function __construct( $parent ) {
        $this->name = 'wysiwyg';
        $this->label = __( 'Wysiwyg Editor', 'cfs' );
        $this->parent = $parent;
    }


    function html( $field ) {
        $field_id =  str_replace(array("[", "]", "(", ")"), "",$field->input_name );
     wp_editor(html_entity_decode(stripcslashes($field->value)), 'editor'.$field_id, array(
            'wpautop'           => true,
            'media_buttons'     => true,
            'default_editor'    => '',
            'drag_drop_upload'  => false,
            'textarea_name'     => $field->input_name,
            'textarea_rows'     => 20,
            'tabindex'          => '',
            'tabfocus_elements' => ':prev,:next',
            'editor_css'        => '',
            'editor_class'      => $field->input_class,
            'teeny'             => false,
            'dfw'               => false,
            'tinymce'           => true,
            'quicktags'         => true
        ) );
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Formatting', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field( array(
                        'type' => 'select',
                        'input_name' => "cfs[fields][$key][options][formatting]",
                        'options' => array(
                            'choices' => array(
                                'default' => __( 'Default', 'cfs' ),
                                'none' => __( 'None (bypass filters)', 'cfs' )
                            ),
                            'force_single' => true,
                        ),
                        'value' => $this->get_option( $field, 'formatting', 'default' ),
                    ) );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Validation', 'cfs' ); ?></label>
            </td>
            <td>
                <?php
                    $this->parent->create_field( array(
                        'type' => 'true_false',
                        'input_name' => "cfs[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'required' ),
                        'options' => array( 'message' => __( 'This is a required field', 'cfs' ) ),
                    ));
                ?>
            </td>
        </tr>
    <?php
    }


    function input_head( $field = null ) {

        // make sure the user has WYSIWYG enabled
        if ( 'true' == get_user_meta( get_current_user_id(), 'rich_editing', true ) ) {
            if ( !is_admin() ) {
                // load TinyMCE for front-end forms
                echo '<div class="hidden">';
                wp_editor( '', 'cfswysi' );
                echo '</div>';
            }
        }
    }

    function wp_default_editor( $default ) {
        $this->wp_default_editor = $default;
        return 'tinymce'; // html or tinymce
    }


    function editor_pre_init( $settings ) {
        if ( 'html' == $this->wp_default_editor ) {
            $settings['oninit'] = "function() { switchEditors.go('content', 'html'); }";
        }

        return $settings;
    }


    function format_value_for_input( $value, $field = null ) {
        return wp_richedit_pre( $value );
    }


    function format_value_for_api( $value, $field = null ) {
        $formatting = $this->get_option( $field, 'formatting', 'default' );
        return ( 'none' == $formatting ) ? $value : apply_filters( 'the_content', $value );
    }
}
