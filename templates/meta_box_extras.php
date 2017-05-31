<?php

global $wpdb;

// Post types
$post_types = array();
$types = get_post_types( array( 'public' => true ) );

foreach ( $types as $post_type ) {
    if ( ! in_array( $post_type, array( 'cfs', 'attachment' ) ) ) {
        $post_types[] = $post_type;
    }
}

$extras = (array) get_post_meta( $post->ID, 'cfs_extras', true );

if ( ! isset( $extras['hide_editor'] ) ) {
    $extras['hide_editor'] = '';
}
if ( ! isset( $extras['order'] ) ) {
    $extras['order'] = 0;
}
if ( ! isset( $extras['context'] ) ) {
    $extras['context'] = 'normal';
}

?>

<table>
    <tr>
        <td class="label">
            <label>
                <?php _e( 'Order', 'cfs' ); ?>
                <div class="cfs_tooltip">?
                    <div class="tooltip_inner"><?php _e( 'The field group with the lowest order will appear first.', 'cfs' ); ?></div>
                </div>
            </label>
        </td>
        <td style="vertical-align:top">
            <input type="text" name="cfs[extras][order]" value="<?php echo $extras['order']; ?>" style="width:80px" />
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Position', 'cfs' ); ?></label>
        </td>
        <td style="vertical-align:top">
            <input type="radio" name="cfs[extras][context]" value="normal"<?php echo ( $extras['context'] == 'normal' ) ? ' checked' : ''; ?> /> <?php _e( 'Normal', 'cfs' ); ?> &nbsp; &nbsp;
            <input type="radio" name="cfs[extras][context]" value="side"<?php echo ( $extras['context'] == 'side' ) ? ' checked' : ''; ?> /> <?php _e( 'Side', 'cfs' ); ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            <label><?php _e( 'Display Settings', 'cfs' ); ?></label>
        </td>
        <td style="vertical-align:top">
            <div>
                <?php
                    CFS()->create_field(array(
                        'type'          => 'true_false',
                        'input_name'    => "cfs[extras][hide_editor]",
                        'input_class'   => 'true_false',
                        'value'         => $extras['hide_editor'],
                        'options'       => array( 'message' => __( 'Hide the content editor', 'cfs' ) ),
                    ));
                ?>
            </div>
        </td>
    </tr>

</table>
