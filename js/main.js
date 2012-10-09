var map;
var results = [];
var marker = null;
			
$(document).ready(function() {
	$('#start_date').datepicker();
	$('#end_date').datepicker();
	$('span.update').on('click',function(){
		activateUpdating($(this));
	});
	
	$('form[name="updateloc"]').submit(function(){
		submitUpdate();
		return(false);
	})

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

//A function to create the marker and set up the event window function 
function createMarker(latlng, name, html)
{
	var contentString = html;
	var marker = new google.maps.Marker({
		position: latlng,
		map: map,
		zIndex: Math.round(latlng.lat()*-100000)<<5
	});

	return marker;
}

function activateUpdating(source){
	var classlist = source.attr("class").replace(" ",".");
	
	jQuery(source).addClass('hidden');
	jQuery('div.' + classlist).removeClass('hidden');
	
	google.maps.event.addListener(map, 'rightclick', function(event) {
		//call function to create marker
		if (marker)
		{
			marker.setMap(null);
			marker = null;
		}
		marker = createMarker(event.latLng, "name", "<b>Location</b><br>"+event.latLng);
		jQuery('input#lat').val(event.latLng.lat());
		jQuery('input#lng').val(event.latLng.lng());
	});
	
}

function submitUpdate(){
	var data = jQuery('form[name="updateloc"]').serialize();
	$.ajax({
		url: 'update.php',
		type: 'POST',
		dataType: 'jsonp', 
		cache: false,
		data: data,
		success: function(data){
			console.log("Success: " + data);
		},
		error: function(data){
			console.log("Error: " + data);
		}
	});
}