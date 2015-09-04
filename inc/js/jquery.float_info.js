/*
* Floating description plugin.
* Just add float_info attribute to your element.
*/

$(document).ready(function() {
    
    // if float_info element doesn't exist
    if (!$('#float_info').length) {
        
        //create the element in the root of body
        $('body').prepend(
            $('<div id="float_info"></div>').css({
                display: 'none',
                position: 'absolute',
                zIndex: 1010
            })
        );
    }
    
    var $tip = $('#float_info');
    var funcShowTooltip = function ($t) {
        $tip       
            .html($t.attr('float_info'))
            .position({
                my: "left center",
                at: "right center",
                of: $t 
            })
            .show();    
    };

    // for touch events, since there is no mousemove in touch devices
    $('.bx-form-info .sys-icon[float_info],.bx-form-error .sys-icon[float_info]').on('click', function (e) {
        funcShowTooltip($(this));
    });

    // passive listen of mouse moves
    $('body').mousemove(function(e) {
        var $t = $(e.target);
        if ('undefined' == typeof $t.attr('float_info') || !$t.attr('float_info').length) 
            $tip.css({'top':'-9999px'}); // hide
        else
            funcShowTooltip($t);
    });    
    
});
