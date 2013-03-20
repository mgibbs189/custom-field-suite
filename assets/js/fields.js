(function($) {
    $(function() {
        function update_order() {
            $('.fields .field_meta').removeClass('even');
            $('.fields .field_meta:even').addClass('even');
        }

        function init_tooltip() {
            $('.cfs_tooltip:not(.ready)').each(function() {
                $(this).addClass('ready');
                $(this).tipTip({
                    content: $(this).html()
                });
            });
        }

        update_order();
        init_tooltip();

        // Setup checkboxes
        $(document).on('click', 'span.checkbox', function() {
            var val = $(this).hasClass('active') ? 0 : 1;
            $(this).siblings('input').val(val);
            $(this).toggleClass('active');
        });

        // Drag-and-drop support
        $('ul.fields').sortable({
            items: 'ul, li',
            connectWith: 'ul.fields',
            placeholder: 'ui-sortable-placeholder',
            handle: '.field_order',
            create: function(event, ui) {
                // Append <ul> to empty loop fields
                $('ul.fields li.loop').filter(function(idx) {
                    return $(this).children('ul').length < 1;
                }).append('<ul></ul>');
            },
            update: function(event, ui) {
                update_order();
                var parent_id = ui.item.parent('li').find('.field_id').val() || 0;
                ui.item.find('.parent_id').first().val(parent_id);
            }
        });

        // Add a new field
        $(document).on('click', '.cfs_add_field', function() {
            var html = CFS.field_clone.replace(/\[clone\]/g, '['+CFS.field_index+']');
            $('.fields').append('<li>' + html + '</li>');
            $('.fields li:last .field_label a').click();
            $('.fields li:last .field_type select').change();
            CFS.field_index = CFS.field_index + 1;
            init_tooltip();
        });

        // Delete a field
        $(document).on('click', '.cfs_delete_field', function() {
            $(this).closest('li').remove();
        });

        // Pop open the edit fields
        $(document).on('click', '.cfs_edit_field', function() {
            var field = $(this).closest('.field');
            field.toggleClass('form_open');
            field.find('.field_form').slideToggle();
        });

        // Add or replace field_type options
        $(document).on('change', '.field_form .field_type select', function() {
            var type = $(this).val();
            var input_name = $(this).attr('name').replace('[type]', '');
            var html = CFS.options_html[type].replace(/cfs\[fields\]\[clone\]/g, input_name);
            $(this).closest('.field').find('.field_meta .field_type').html(type);
            $(this).closest('.field').find('.field_option').remove();
            $(this).closest('.field_basics').after(html);
            init_tooltip();
        });

        // Auto-populate the field name
        $(document).on('blur', '.field_form .field_label input', function() {
            var val = $(this).val();

            // browser autofill support
            $(this).closest('.field').find('.field_meta .field_label a').html(val);

            var name = $(this).closest('tr').find('.field_name input');
            if ('' == name.val()) {
                val = $.trim(val).toLowerCase();
                val = val.replace(/[^\w- ]/g, ''); // strip invalid characters
                val = val.replace(/[- ]/g, '_'); // replace space and hyphen with underscore
                val = val.replace(/[_]{2,}/g, '_'); // strip consecutive underscores
                name.val(val);
                name.trigger('keyup');
            }
        });

        $(document).on('keyup paste', '.field_form .field_label input', function() {
            var $this = $(this);
            setTimeout(function() {
                $this.closest('.field').find('.field_meta .field_label a').html($this.val());
            }, 1);
        });

        $(document).on('keyup', '.field_form .field_name input', function() {
            var val = jQuery(this).val();
            $(this).closest('.field').find('.field_meta .field_name').html(val);
        });
    });
})(jQuery);
