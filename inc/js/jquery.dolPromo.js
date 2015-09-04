(function($){
    $.fn.dolPromo = function(iInterval, iTransition) {
        
        function moveToCenter($img, bHide) {
            
            if (!$img.is('img') || !$img.width() || !$img.height() || !$img.parent().width() || !$img.parent().height())
                return false;
            
            
            // place the image on the center and crop it (if neccessary) using parent
            $img.parent().css({
                overflow: 'hidden',
                position: 'relative'
            });
            
            $img.css({
                position: 'absolute',
                left: Math.round( ($img.parent().width()  / 2) - ($img.width()  / 2)),
                top:  Math.round( ($img.parent().height() / 2) - ($img.height() / 2))
            });
            
            if (bHide)
                $img.hide();
        }
        
        function switchNextImg($promo, iTransition) {
            var $curImg  = $('img:visible', $promo);
            var $nextImg = $curImg.next();
            
            if (!$nextImg.length)
                var $nextImg = $('img:first', $promo);
            
            $curImg.fadeOut(iTransition);
            $nextImg.fadeIn(iTransition);
        }
        
        var iInterval   = iInterval   || 3000; //switching interval in milliseconds
        var iTransition = iTransition || 1000; //transition (fadeIn|fadeOut) time
        
        var $promo = this;
        
        $('img', $promo)
        .each(function(iIndex) {
            
            var $img = $(this);
            var bHide = (iIndex > 0); // do not hide only first
            
            if($img.width() && $img.height()) {
                moveToCenter($img, bHide);
            } else {
                // attach event on load
                if( document.all ) { //ie
    				$img.ready(function(){
                        moveToCenter($img, bHide);
                    });
    			} else {
    				$img.load(function(){
                        moveToCenter($img, bHide);
                    });
                }
            }
        });
        
        // begin switching
        setInterval(function(){
            switchNextImg($promo, iTransition);
        }, iInterval);
    };
})(jQuery);