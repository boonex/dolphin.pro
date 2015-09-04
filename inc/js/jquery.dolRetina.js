(function( $ ){
	$.fn.dolRetina = function(sRetinaPart) {
		var oSettings = {'retina_part': '-2x'};
		if(sRetinaPart) 
			jQuery.extend(oSettings, { 'retina_part': sRetinaPart });

		if(window.devicePixelRatio >= 2) {
			this.each(function(index, oElement) {
				if(!$(oElement).attr('src')) 
					return;

				var sSrcNew = '';
				if(!$(oElement).attr('src-2x')) {
					var oCheckForRetina = new RegExp("(.+)(" + oSettings['retina_part']+"\\.\\w{3,4})");
					if(oCheckForRetina.test($(oElement).attr('src'))) 
						return;

					var sSrcNew = $(oElement).attr('src').replace(/(.+)(\.\w{3,4})$/, "$1" + oSettings['retina_part'] + "$2");
				}
				else
					sSrcNew = $(oElement).attr('src-2x');

				if(!sSrcNew)
					return;

				$.ajax({url: sSrcNew, type: "HEAD", success: function() {
					$(oElement).attr('src', sSrcNew);
				}});
			});
		}
		return this;
	};
})( jQuery );