<?php

class cfs_image extends cfs_field
{

    function __construct() {
        $this->name = 'image';
        $this->label = __( 'Image', 'cfs' );
    }


    function html( $field ) {
    ?>
        <span class="cfs_image">
            <div class="image"></div>
            <input type="hidden" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>"/>
            <button class="button"><?php echo __( 'Select image', 'cfs' ); ?></button>
        </span>
    <?php
    }


    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('click', '.cfs_image>button', function(e) {
                    e.preventDefault();
                    var $this = $(this);
                    var $input_group = $this.parent();
                    if(!$input_group.is(".cfs_image-initialized")) {
                        cfs_image_init($input_group);
                        $input_group.addClass("cfs_image-initialized");
                        $input_group.find("button").click();
                    }
                });
            });

            var cfs_image_init = function(input_group_selector)  {

                $(input_group_selector).each(function() {

                    $(this).find("button").click(function (event) {
                        event.preventDefault();

                        var $input_group = $(this).parent();
                        var $button = $($input_group).find("button");
                        var $input = $($input_group).find("input");

                        // check for media manager instance
                        if(wp.media.frames.cfs_frame) {
                            wp.media.frames.cfs_frame.input_group = $input_group;
                            wp.media.frames.cfs_frame.open();
                            return;
                        }

                        // configuration of the media manager new instance
                        wp.media.frames.cfs_frame = wp.media({
                            title: '<?php echo __( 'Select image', 'cfs' ); ?>',
                            multiple: false,
                            library: {
                                type: 'image'
                            },
                            button: {
                                text: '<?php echo __( 'Use selected image', 'cfs' ); ?>'
                            }
                        });

                        // Set a property for reference when setting the input val
                        wp.media.frames.cfs_frame.input_group = $input_group;

                        // Function used for the image selection and media manager closing
                        var gk_media_set_image = function() {
                            var selection = wp.media.frames.cfs_frame.state().get('selection');
                            if (!selection) return;

                            // iterate through selected elements
                            selection.each(function(attachment) {
                                console.log(attachment);
                                $(wp.media.frames.cfs_frame.input_group).find("input").val(attachment.attributes.url);
                                $(wp.media.frames.cfs_frame.input_group).find(".image").css("background-image", "url("+attachment.attributes.sizes.thumbnail.url+")");
                            });
                        };


                        wp.media.frames.cfs_frame.on('close', function() { gk_media_set_image(); }); // Media Manager closed
                        wp.media.frames.cfs_frame.open(); // Show Media Manager
                    });
               });
            };

        })(jQuery);
        </script>
        <link rel="stylesheet" type="text/css" href="<?php echo CFS_URL; ?>/includes/fields/image/image.css" />
    <?php
    }

}
