var map;
var results = [];
			
$(document).ready(function() {
	$('#start_date').datepicker();
	$('#end_date').datepicker();

	var myOptions = {
		center: new google.maps.LatLng( 53.45752026035937, -7.910118185714736 ),
		zoom: 7,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	map = new google.maps.Map( document.getElementById('map_canvas'), myOptions );

	if (!results.length)
	{
		if(navigator.geolocation) 
		{
	
			navigator.geolocation.getCurrentPosition(function(position) {
				var pos = new google.maps.LatLng(position.coords.latitude,
									 position.coords.longitude);
	
				map.setCenter(pos);
				
				$.get('search.php?lat=' + position.coords.latitude + '&lng=' + position.coords.longitude, function(data) {
					if (data.length)
					{
						processResults(data, true);
					}
				});
			});
		}
	}
	else
	{
		processResults(results, false);
	}
});

function processResults(items, addToTable)
{
	var bounds = new google.maps.LatLngBounds ();
	
	$.each(items, function(i, item) {
		if (item.lat)
		{
			var marker = new google.maps.Marker({
				map: map,
				position: new google.maps.LatLng(item.lat, item.lng)                
			});
			
			google.maps.event.addListener(marker, 'click', function() {
			var infowindow = new google.maps.InfoWindow();
				infowindow.setContent(item.address + ' : ' + '&euro;' + item.price ); 
				infowindow.open(map,marker);
			});
	
			bounds.extend(marker.position);
		}
		
		if (addToTable)
		{
			$('#results-table > tbody:last').append('<tr><td>' + item.date_of_sale + '</td><td><a href="house.php?id=' + item.id + '">' + item.address + ', Co. ' + item.county + '</a><br />' + item.description_of_property + '<br />' + item.property_size_description + '</td><td>&euro;' + item.price + '</td><td>' + ((item.not_full_market_price) ? 'No' : 'Yes') + '</td></tr>');
		}
	});
	
	map.fitBounds (bounds);
	
	zoomChangeBoundsListener = 
		google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
			if (this.getZoom() && items.length == 1)
			{
				this.setZoom(16);
			}
		});
	setTimeout(function(){google.maps.event.removeListener(zoomChangeBoundsListener)}, 2000);
}