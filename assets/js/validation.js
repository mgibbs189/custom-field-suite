jQuery(function($) {
    CFS.validators = {
        'required': {
            'error': 'Please enter a value',
            'validate': function(val) {
                return ('' != val && null != val);
            },
        },
        'valid_date': {
            'error': 'Please enter a valid date (YYYY-MM-DD HH:MM)',
            'validate': function(val) {
                var regex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/;
                return regex.test(val);
            }
        },
        'valid_color': {
            'error': 'Please enter a valid color HEX (#ff0000)',
            'validate': function(val) {
                var regex = /^#[0-9a-zA-Z]{3,}$/;
                return regex.test(val);
            }
        },
    };

    // Get the value for non-standard field types
    CFS.get_field_value = {
        'textarea': function(el) {
            return el.find('textarea').val();
        },
        'select': function(el) {
            return el.find('select').val();
        },
        'relationship': function(el) {
            return el.find('input.relationship').val();
        },
        'user': function(el) {
            return el.find('input.user').val();
        }
    };

    CFS.is_draft = false;
    $(document).on('click', '#save-post', function() {
        CFS.is_draft = true;
    });

    $('form#post').submit(function() {

        // skip validation for drafts
        if (false === CFS.is_draft) {
            var passthru = true;

            $('.cfs_input .field').each(function() {
                var $this = $(this);

                // reset error styling
                $this.find('.error').hide();

                var type = $this.attr('data-type');
                var validator = $this.attr('data-validator');

                // a validator is specified
                if ('' != validator) {

                    // the validator exists
                    if ('object' == typeof CFS.validators[validator]) {

                        // figure out the field value
                        if ('function' == typeof CFS.get_field_value[type]) {
                            var val = CFS.get_field_value[type]($this);
                        }
                        else {
                            var val = $this.find('input').val();
                        }

                        // pass the value through the validator
                        var is_valid = CFS.validators[validator]['validate'](val);

                        if (!is_valid) {
                            passthru = false;

                            if ($this.find('.error').length < 1) {
                                $this.append('<div class="error"></div>');
                            }
                            $this.find('.error').html(CFS.validators[validator]['error']);
                            $this.find('.error').show();
                        }
                    }
                }
            });

            if (!passthru) {
                $('#publish').removeClass('button-primary-disabled');
                $('#save-post').removeClass('button-disabled');
                $('.spinner').hide();
                return false;
            }
        }
    });
});