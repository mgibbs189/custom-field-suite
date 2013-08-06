/*!
 * jquery.datetime.js v1.1 - 06 August, 2013
 * By Matt Gibbs (https://uproot.us)
 * Hosted on https://github.com/mgibbs189/jquery-datetime
 * Licensed under MIT ("expat" flavour) license.
 */

(function($) {

    $.fn.datetime = function(options) {


        var settings = $.extend({
            'i18n': {
                'months': ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                'empty': 'Choose a date',
                'year': 'Year',
                'month': 'Month',
                'day': 'Day',
                'hour': 'Hour',
                'minute': 'Minute',
                'today': 'Today',
                'clear': 'Clear'
            }
        }, options);


        var $INPUT;
        var $PICKER;


        var params = {
            'pieces': { 'y': '', 'm': '', 'd': '', 'h': '', 'minute': '' }
        };


        var methods = {
            'init': function($this) {
                $this.hide();
                $this.addClass('dt-input');
                $this.wrap('<span class="dt-wrapper"></span>');
                var formatted_date = methods.formatDate($this);
                var html = '<span class="dt-button">' + formatted_date + '</span>';
                $this.after(html);

                if (0 < $('.dt-picker').length) {
                    return;
                }

                html = '' +
                    '<div class="dt-picker">' +
                    '<select class="dt-y"></select>' +
                    '<select class="dt-m"></select>' +
                    '<select class="dt-d"></select>' +
                    '<select class="dt-h"></select>' +
                    '<select class="dt-minute"></select>' +
                    '<input type="button" class="dt-today" value="' + settings.i18n.today + '" />' +
                    '<input type="button" class="dt-clear" value="' + settings.i18n.clear + '" />' +
                    '</div>';
                $('body').append(html);

                $PICKER = $('.dt-picker');

                // hide popup
                $(document).click(function(e) {
                    if ('undefined' != typeof $INPUT) {
                        var is_picker = $(e.target).closest('.dt-picker').length;
                        if (is_picker < 1 && 'dt-button' != $(e.target).attr('class')) {
                            $INPUT.siblings('.dt-button').css('visibility', 'visible');
                            $PICKER.hide();
                        }
                    }
                });

                // button click
                $(document).on('click', '.dt-button', function() {
                    $INPUT = $(this).siblings('.dt-input');
                    methods.populateSelect();

                    var offset = $(this).offset();
                    $PICKER.css(offset);
                    $PICKER.show();
                    $('.dt-button').css('visibility', 'visible');
                    $INPUT.siblings('.dt-button').css('visibility', 'hidden');
                });

                // "clear" button click
                $(document).on('click', '.dt-clear', function() {
                    $INPUT.val('');
                    methods.populateSelect();
                    methods.saveDateToInput();
                });

                // "today" button click
                $(document).on('click', '.dt-today', function() {
                    var today = new Date();
                    var dd = today.getDate();
                    var mm = today.getMonth()+1;
                    var value = today.getFullYear() +
                        '-' + ((mm < 10) ? '0' + mm : mm) +
                        '-' + ((dd < 10) ? '0' + dd : dd);
                    $INPUT.val(value);

                    methods.populateSelect();
                    methods.saveDateToInput();
                });

                // <select> change
                $('.dt-picker select').change(function() {
                    methods.saveDateToInput();

                    if ($(this).hasClass('dt-y')) {
                        var year = $('.dt-y').val();
                        methods.updateYearSelect(year);
                    }
                });
            },


            'saveDateToInput': function() {
                var y = $('.dt-y').val();
                var m = $('.dt-m').val();
                var d = $('.dt-d').val();
                var h = $('.dt-h').val();
                var minute = $('.dt-minute').val();
                var value = '';

                if ('' !== y && '' !== m && '' !== d) {
                    value += y + '-' + m + '-' + d;

                    if ('' !== h && '' !== minute) {
                        value += ' ' + h + ':' + minute;
                    }
                }

                $INPUT.val(value);

                $INPUT.siblings('.dt-button').text(
                    methods.formatDate($INPUT)
                );
            },


            'formatDate': function($this) {
                var value = methods.loadDateFromInput($this);

                if ('' === value) {
                    value = settings.i18n.empty;
                }
                else {
                    value = '';

                    // month
                    var m = params.pieces.m;
                    if ('0' == m.substr(0, 1)) m = m.substr(1);
                    value += settings.i18n.months[parseInt(m, 10) - 1];

                    // day
                    var d = params.pieces.d;
                    if ('0' == d.substr(0, 1)) d = d.substr(1);
                    value += ' ' + d;

                    // year
                    value += ', ' + params.pieces.y;

                    // hour
                    if ('' !== params.pieces.h) {
                        value += ', ' + params.pieces.h + ':';

                        // minute
                        value += params.pieces.minute;
                    }
                }
                return value;
            },


            'loadDateFromInput': function($this) {
                var value = $this.val();
                var valid_date = /^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/;
                if (valid_date.test(value)) {
                    params.pieces = {
                        'y': value.substr(0, 4),
                        'm': value.substr(5, 2),
                        'd': value.substr(8, 2),
                        'h': '',
                        'minute': ''
                    };

                    if (16 <= value.length) {
                        params.pieces.h = value.substr(11, 2);
                        params.pieces.minute = value.substr(14, 2);
                    }
                }
                else {
                    value = '';
                    params.pieces = {
                        'y': '',
                        'm': '',
                        'd': '',
                        'h': '',
                        'minute': ''
                    };
                }

                return value;
            },


            'updateYearSelect': function(year) {

                var isSelected = true;

                if ('undefined' == typeof year) {
                    if ('' === params.pieces.y) {
                        var year = new Date().getFullYear();
                        isSelected = false;
                    }
                    else {
                        var year = parseInt(params.pieces.y, 10);
                    }
                }

                var opts = '<option value="">' + settings.i18n.year + '</option>';
                for (var i = 20; i >= 0; i--) {
                    opts += '<option value="'+ (year-(10-i)) + '">' + (year-(10-i)) + '</option>';
                }

                $PICKER.find('.dt-y').html(opts);

                if (isSelected) {
                    $PICKER.find('.dt-y').val(year);
                }
            },


            'populateSelect': function() {
                methods.loadDateFromInput($INPUT);

                // month
                var opts = '<option value="">' + settings.i18n.month + '</option>';
                $.each(settings.i18n.months, function(idx, val) {
                    var month_num = (idx + 1);
                    if (month_num < 10) month_num = '0' + month_num;
                    opts += '<option value="'+ month_num + '">' + val + '</option>';
                });
                $PICKER.find('.dt-m').html(opts);

                // day
                var opts = '<option value="">' + settings.i18n.day + '</option>';
                for (var i = 1; i <= 31; i++) {
                    var day = (i < 10) ? '0' + i : i;
                    opts += '<option value="'+ day + '">' + i + '</option>';
                }
                $PICKER.find('.dt-d').html(opts);

                // hour
                var opts = '<option value="">' + settings.i18n.hour + '</option>';
                for (var i = 0; i < 24; i++) {
                    var hour = (i < 10) ? '0' + i : i;
                    if (0 === i) hourLabel = '12 am';
                    else if (i < 12) hourLabel = i + ' am';
                    else if (i == 12) hourLabel = i + ' pm';
                    else hourLabel = (i - 12) + ' pm';
                    opts += '<option value="'+ hour + '">' + hourLabel + '</option>';
                }
                $PICKER.find('.dt-h').html(opts);

                // minute
                var opts = '<option value="">' + settings.i18n.minute + '</option>';
                for (var i = 0; i < 60; i += 5) {
                    min = (i < 10) ? '0' + i : i;
                    opts += '<option value="'+ min + '">' + min + '</option>';
                }
                $PICKER.find('.dt-minute').html(opts);


                // set active values
                methods.updateYearSelect();
                $PICKER.find('.dt-m').val(params.pieces.m);
                $PICKER.find('.dt-d').val(params.pieces.d);
                $PICKER.find('.dt-h').val(params.pieces.h);
                $PICKER.find('.dt-minute').val(params.pieces.minute);
            }
        };


        return this.each(function(idx) {
            methods.init($(this));
        });


    };


})(jQuery);