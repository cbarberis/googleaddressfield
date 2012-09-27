(function($) {

	$('#findAddressButton').entwine({
		onclick: function() {
			var HouseNoName = $('input[name=HouseNoName]').val();
			var PostCode = $('input[name=Postcode]').val();
			if(HouseNoName == '' || Postcode == '') {
				alert('Please enter House No./Name and postcode so we can find your address');
				return false;
			}
			$.ajax({
				url: 'AddressHelper/get_address',
				type: 'POST',
				data: 'postcode=' + PostCode + '&building=' + HouseNoName, 
				success: function(data){
					if(data){
						var result = JSON.parse(data);
						if(result.address != '') {
							$('#Address').show();
							$('input[name=Address]').val(result.address);
						} else {
							alert(result.errorMessage);
						}
					}
				},
				complete: function() {
					
					var self = this;
					$.ajax({
						url: 'googleapi/getLocationFromAddress',
						type: 'POST',
						data: 'address=' + $('input[name=Address]').val(), 
						success: function(data){
							if(data){
								var obj = jQuery.parseJSON(data);
								
								if($('input[name=ActivityLat]').length && $('input[name=ActivityLong]').length) {
									$('input[name=ActivityLat]').val(obj.lat);
									$('input[name=ActivityLong]').val(obj.lng);
								} else if($('input[name=ProviderLat]').length && $('input[name=ProviderLong]').length) {
									$('input[name=ProviderLat]').val(obj.lat);
									$('input[name=ProviderLong]').val(obj.lng);
								}
								
								if(obj.postcode) $('input[name=Postcode]').val(obj.postcode);
							}
						}
					});
				}
			});
		}
	});

})(jQuery);