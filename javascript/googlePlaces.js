(function($) {

	$('input.googleaddresssuggestion').entwine({
		onkeyup: function() {
			if(this.val().length > 3 && (this.val().length % 2 == 0)) $('#suggestionsAddress').loadSuggestions(this);
		}
	});


	var xhr;
	$('#suggestionsAddress').entwine({
		loadSuggestions: function(el) {
			var self = this;
			if(typeof xhr == 'object') xhr.abort();
			this.html('loading...');
			xhr = $.ajax({
				url: 'googleapi/getSuggestions',
				type: 'POST',
				data: 'address=' + el.val(), 
				success: function(data){
					if(data){
						self.html(data);
					}
				}
			});
		}
	});

	$('#suggestionsAddress ul li a').entwine({
		onclick: function() {

			$('input[name=Address]').val(this.text());
			var self = this;
			$.ajax({
				url: 'googleapi/getCoordinates',
				type: 'POST',
				data: 'reference=' + self.attr('id'), 
				success: function(data){
					if(data){
						var obj = jQuery.parseJSON(data);
						
						$('input[name=Lat]').val(obj.lat);
						$('input[name=Lon]').val(obj.lng);
			
						if(obj.postcode && $('input[name=Postcode]').length) $('input[name=Postcode]').val(obj.postcode);
					}
				}
			});
			$('#suggestionsAddress').addClass('pointAdded').html('Point has been added.');
			return false;
		}
	});



})(jQuery);