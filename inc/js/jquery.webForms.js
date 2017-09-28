
$(document).ready(function() {
	$(this).addWebForms();

	$('.bx-form-block-header.collapsable', this).each(function() {
	    var $oBlockHeader = $(this);

	    $oBlockHeader.find('.bx-form-block-header-collapse i').click(function() {
            if ($oBlockHeader.hasClass('collapsed')) {
                $(this).removeClass('chevron-down').addClass('chevron-up');
                $oBlockHeader.removeClass('collapsed').next('.bx-form-fields-wrapper').fadeIn(400);
            } 
            else {
                $(this).removeClass('chevron-up').addClass('chevron-down');
                $oBlockHeader.addClass('collapsed').next('.bx-form-fields-wrapper').fadeOut(400);
            }
        });        
	});
});

(function($){
    $.fn.addWebForms = function() {
		$("input,select,textarea", this).each(function() {

			// Date/Time pickers
			if (this.getAttribute("type") == "date" || this.getAttribute("type") == "date_calendar" || this.getAttribute("type") == "datetime" || this.getAttribute("type") == "date_time" ) {
                var iYearMin = '1900';
                var iYearMax = '2100';
                var a;

                if ($(this).attr('min') && (a = $(this).attr('min').split('-')) && a.length)
                    iYearMin = a[0];
                if ($(this).attr('max') && (a = $(this).attr('max').split('-')) && a.length)
                    iYearMax = a[0];

                if (this.getAttribute("type") == "date" || this.getAttribute("type") == "date_calendar") { // Date picker

                    $(this).datepicker({
                        changeYear: true,
                        changeMonth: true,
                        dateFormat: 'yy-mm-dd',
                        defaultDate: '-22y',
                        yearRange: iYearMin + ':' + iYearMax 
                    });

                } else if(this.getAttribute("type") == "datetime" || this.getAttribute("type") == "date_time") { // DateTime picker

                    $(this).datetimepicker({
                        changeYear: true,
                        changeMonth: true,
                        dateFormat: 'yy-mm-dd'
                    });
                }

                if (window.navigator.appVersion.search(/Chrome\/(.*?) /) == -1 || parseInt(window.navigator.appVersion.match(/Chrome\/(\d+)\./)[1], 10) < 24)
                    if( this.getAttribute("allow_input") == null)
                        $(this).attr('readonly', 'readonly');

			}

			// Single file selector
			if($(this).is(".form_input_file.bx-def-font-inputs[type = 'file'][multiplyable != 'true']")) {
				$('<div class="bx-btn form_input_multiply_path"></div>').insertAfter($(this).parents('.bx-btn:first'));

				$(this).change(function() {
	            	$(this).parents('.bx-btn:first').nextAll('.form_input_multiply_path:first').html($(this).val());
	            });
			}

			// Multiplyable
			if (this.getAttribute('multiplyable') == 'true') {
			   $(this).multiplyable();
			}
			
			if (this.getAttribute('deletable') == 'true') {
			    $(this).inputDeletable();
			}
			
			// Counter for textareas
            if (this.getAttribute('counter') == 'true') {
                
                function updateCounter() {
                    if( $area.val() )
                        $counter.show( 300 );
                    else
                        $counter.hide( 300 );
                    
                    $counterCont.html( $area.val().length );
                }
                
                var $area = $(this);
                $area
                .parents('td:first')
                    .append(
                        '<div class="counter" style="display:none;">' + _t('_Counter') + ': <b></b></div>' +
                        '<div class="clear_both"></div>'
                    );
                
                var $counter     = $area.parent().parent().children('div.counter');
                
                var $counterCont = $counter.children('b');
                
                updateCounter();
                $area.change( updateCounter ).keyup( updateCounter );
            }
			
            // DoubleRange
            if(this.getAttribute("type") == "doublerange" || this.getAttribute("type") == "slider" || this.getAttribute("type") == "range") {
			    
				var cur = $(this);
				
				var $slider = $("<div></div>").insertAfter(cur);
				
				$slider.addClass(cur.attr('class'));
				
				cur.css({ position: "absolute", opacity: 0, top: "-1000px", left: "-1000px" });
				
				var iMin = cur.attr("min") ? parseInt(cur.attr("min"), 10) : 0;
				var iMax = cur.attr("max") ? parseInt(cur.attr("max"), 10) : 100;
				var sRangeDv = cur.attr("range-divider") ? cur.attr("range-divider") : '-';

                var oOptions = {
                    range: cur.attr("type") == "doublerange" ? true : false,
					min: iMin,
					max: iMax,
					step: parseInt(cur.attr("step")) | 1,
					change: function(e, ui) {
                        if (cur.attr("type") == "doublerange")
					        cur.val( ui.values[0] + sRangeDv + ui.values[1] );
                        else
                            cur.val( ui.value );

					    if(typeof(cur.attr("onchange")) != 'undefined' && cur.attr("onchange").length)
					    	eval(cur.attr("onchange"));
					},
					slide: function(e, ui) {
					    $(ui.handle).html(ui.value);
					}
				};

                var values;
                if (cur.attr("type") == "doublerange") {
                    values = cur.val().split(sRangeDv, 2); // get values
                    
                    if (typeof(values[0]) != 'undefined' && values[0].length)
                        values[0] = parseInt(values[0]);
                    else
                        values[0] = iMin;
                    
                    if (typeof(values[1]) != 'undefined' && values[1].length)
                        values[1] = parseInt(values[1]);
                    else
                        values[1] = iMax;

                    oOptions['values'] = values;
                } else {
                    oOptions['value'] = cur.val();
                }

				$slider.slider(oOptions);
				                
                $('.ui-slider-handle', $slider).each(function(i){
                    if (cur.attr("type") == "doublerange")
                        $(this).html(values[i]);
                    else
                        $(this).html(cur.val());
                });

			}
		});
        return this;
    };
    
    $.fn.inputDeletable = function(oDeleteAlso) {        
        return $(this).each( function() {
            var $eInput = $(this);

            // insert "Remove" button
            var $minusImg = $('<button class="bx-btn form_input_multiply_remove" title="' + _t('_Remove') + '" type="button"><i class="multiply_remove_button sys-icon minus-circle"></i></button>')
            .click( function() {
                var eParent = $eInput.parent(':not(td)');
                
                $(this).remove(); // remove button
                $eInput.remove(); // remove input
                eParent.remove(); // remove parent (if present)
                
                if (typeof oDeleteAlso != 'undefined')
                    $(oDeleteAlso).remove();
                
                // Note: Do not delete parent only. It is present not everytime
            })
            .insertAfter($eInput.parent());
        });
        
        return this;
    };

    $.fn.multiplyable = function() {
        $(this).each(function() {
            var $input = $(this);
            var $inputParent = $input.parent();
            var $wrapper = $inputParent.clone().children().remove().end();
            var $fOnChange = function() {
            	$(this).parents('.bx-btn:first').nextAll('.form_input_multiply_path:first').html($(this).val());
            };

            $input.change($fOnChange);

            // insert "Other" button
            if ($input.attr('add_other') == 'true' && !$inputParent.parent().find('.multiply_other_button').length) {
                var $otherImg = $('<button class="bx-btn form_input_multiply_other" title="' + _t('_add_other') + '" type="button"><i class="multiply_other_button sys-icon folder"></i></button>')
                .insertAfter($inputParent)
                .click(function(){
                    var $trc = $($inputParent).nextAll('div.clear_both:last');
                    $trc = $trc.length ? $trc : $inputParent; // just if div.clear_both doesn't exist
                    
                    var $clearBoth = $('<div class="clear_both"></div>').insertAfter($trc);
                    
                    $wrapper
                    .clone()
                        .append('<input type="text" class="form_input_text" name="' + $input.attr('name') + '" />')
                        .insertAfter($trc)
                        .children(':first')
                            .inputDeletable($clearBoth)
                            .get(0)
                                .focus();
                });
            }

            // check if "Add" button is already added
            if ($inputParent.parent().find('.multiply_add_button').length)
                return;

            // insert "Add" button
            var $plusImg = $('<button class="bx-btn form_input_multiply_add" title="' + _t('_add') + '" type="button"><i class="multiply_add_button sys-icon plus-circle"></i></button>')
            .insertAfter($inputParent)
            .click(function(){
                var $trc = $($inputParent).nextAll('div.clear_both:last');
                $trc = $trc.length ? $trc : $inputParent; // just if div.clear_both doesn't exist
                
                var $clearBoth = $('<div class="bx-btn form_input_multiply_path"></div><div class="clear_both"></div>').insertAfter($trc);
                
                $inputParent
                .clone()
                    .children()
                        .removeAttr('id') // TODO: set unique id
                    .end()
                    .each(function(){
                        var $input = $('input', this);
                        if ($input.length && ($input.attr('type') == 'file' || $input.attr('type') == 'text'))
                            $input.val('').change($fOnChange);
                    })
                    .insertAfter($trc)
                    .children(':first')
                        .inputDeletable($clearBoth)
                        .get(0)
                            .focus();
            });

            if($input.attr('type') == 'file')
            	$('<div class="bx-btn form_input_multiply_path"></div>').insertAfter($inputParent.nextAll('.bx-btn.form_input_multiply_add:first, .bx-btn.form_input_multiply_remove:first'));

            // check if "clear_both" is needed after nely added button(s)
            if ($inputParent.parent().find('.bx-btn > .multiply_add_button, .bx-btn > .multiply_other_button').length)
            	$('<div class="clear_both"></div>').insertAfter($inputParent.parent().find('.bx-btn:last'));
        });
        
        return this;
    };
})(jQuery);



(function($){
    $.fn.addEmojiInput = function(o) {
        var aDefaults = {
            icon: 'smile-o',
            width: 'undefined' != typeof(o) && 'undefined' != typeof(o.wrapper) ? $(o.wrapper).innerWidth() : 280,
            height: 'undefined' != typeof(o) && 'undefined' != typeof(o.wrapper) ? $(o.wrapper).innerHeight() - 10 : 250,
            button: false
        };
        var aOptions = $.extend({}, aDefaults, o);

		$(this).filter('input').each(function() {

            var e = $(this);
            var eWrapper = e.parents('.input_wrapper');
        
            // don't enable emoji input method twice
            if (eWrapper.find('.bx-form-input-emoji').size())
                return;

            // enable emoji for the input element
            e.emojiPicker(aOptions);

            // don't process for the emoji enabled devices
            if (!eWrapper.find('.emojiPickerIconWrap').size())
                return;

            // add "smile" icon to the input
            eWrapper.append('<a href="javascript:void(0);"><div class="bx-form-input-emoji bx-def-font-grayed"><i class="sys-icon ' + aOptions.icon + '"></i></div></a>')

            // attach show emoji popup input for the added "smile" icon
            eWrapper.find('.bx-form-input-emoji').on('click', function () {
                e.emojiPicker('toggle');

                // move emoji picker to the wrapper if it is specified in the options
                if ('undefined' != typeof(aOptions.wrapper)) {
                    setTimeout(function () {
                        var ePicker = e.data('emojiPicker').$picker;
                        if (!ePicker.is(':hidden')) {
                            ePicker.detach().appendTo($(aOptions.wrapper));
                            ePicker.css({
                                position: 'absolute',              
                                top: 0, 
                                left: 0, 
                                width: '100%', 
                                height: $(o.wrapper).innerHeight() - 10,
                                'z-index': 1
                            });
                            $(aOptions.wrapper).css({'overflow-y':'hidden'});
                            glBxAddEmojiInputScrollPos = $(aOptions.wrapper).scrollTop();
                            $(aOptions.wrapper).scrollTop(0);
                            if ('function' === typeof(aOptions.onshow))
                                aOptions.onshow.apply(e, [ePicker]);
                        }
                        else {
                            $(aOptions.wrapper).css({'overflow-y':'scroll'});
                            $(aOptions.wrapper).scrollTop('undefined' != typeof(glBxAddEmojiInputScrollPos) ? glBxAddEmojiInputScrollPos : 0);
                            if ('function' === typeof(aOptions.onhide))
                                aOptions.onhide.apply(e, [ePicker]);
                        }
                    }, 110);
                }
            });

        });
        
        return this;
    };
})(jQuery);


(function($){
    $.fn.formsToggleHtmlEditor = function() {
		$(this).each(function() {
			if(!tinyMCE)
				return;

			var sCookieKey = 'bx_mce_editor_disabled';
            if ($(this).is(':hidden')) {
                tinyMCE.execCommand('mceRemoveEditor', false, this.id);

                $.cookie(sCookieKey, 1, {path: '/', expires: 999});
            }
            else {
            	jQuery('#' + this.id).tinymce(tinyMCE.activeEditor["settings"]);

                $.removeCookie(sCookieKey, {path: '/'});
            }
                
        });
        return this;
    };
})(jQuery);
