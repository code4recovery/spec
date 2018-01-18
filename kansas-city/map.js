//define global variables
var map, 
	infowindow, 
	markers = [], 
	days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

//convert a HH:MM time to hh:mm:a
function formatTime(time) {
	if (time == '12:00') return 'Noon';
	var parts = time.split(':');
	var hours = parts[0] - 0;
	var minutes = parts[1];
	var ampm = (hours > 11) ? 'pm' : 'am';
	if (!hours) hours = 12;
	if (hours > 12) hours -= 12;
	return hours + ':' + minutes + ampm;
}

//get and show markers for a param, eg '' or '?type=women'
function getAndShowMarkers(param, fitBounds) {

	//clear any markers on the map
	if (markers.length) {
		for (var i = 0; i < markers.length; i++) {
			markers[i].setMap(null);
		}
		markers = [];
	}
	
	//fetch data
	getJSON('json.cfm' + param, function(meetings) { 
		
		//define an object for storing locations
		var locations = {};
		
		//loop through json values
		for (var i = 0; i < meetings.length; i++) {
			
			//skip any empty locations
			if (!meetings[i].latitude || !meetings[i].longitude) continue;

			//skip bad locations
			if (meetings[i].latitude < 0) {
				var lat = meetings[i].latitude;
				meetings[i].latitude = meetings[i].longitude;
				meetings[i].longitude = lat;
				console.log(meetings[i].name);
			}
			
			//latitude + longitude are the unique key for this location
			var key = meetings[i].latitude + ',' + meetings[i].longitude;

			//build address string
			var address = meetings[i].address;
			if (meetings[i].city) address += ', ' + meetings[i].city;
			if (meetings[i].state) address += ', ' + meetings[i].state;
			if (meetings[i].postal_code) address += ' ' + meetings[i].postal_code;
			
			//add location to array if doesn't exist			
			if (!(key in locations)) {
				locations[key] = {
					name: meetings[i].location,
					address: address,
					meetings: [],
					position: new google.maps.LatLng(meetings[i].latitude, meetings[i].longitude)
				}
			}
			
			//add this meeting
			locations[key].meetings.push({
				name: meetings[i].name,
				day: meetings[i].day,
				time: meetings[i].time,
				topic: meetings[i].topic == 'none' ? '' : meetings[i].topic
			});
			
		}
		
		//flatten locations into array
		locations = Object.values(locations);
		
		//define map bounds
		var bounds = new google.maps.LatLngBounds();
		
		//loop through locations
		for (var i = 0; i < locations.length; i++) {

			//sort meetings into chronological order
			locations[i].meetings.sort(function(a, b) {
				if (a.day > b.day) return 1;
				if (a.day < b.day) return -1;
				if (a.time > b.time) return 1;
				if (a.time < b.time) return -1;
				if (a.name > b.name) return 1;
				if (a.name < b.name) return -1;
				return 0;
			});
			
			//build title string for pin
			var title = [];
			if (locations[i].meetings[0].name) title.push(locations[i].meetings[0].name);
			if (locations[i].address) title.push(locations[i].address);
			
			//extend bounds
			bounds.extend(locations[i].position);
			
			//add the marker to the map
			var marker = new google.maps.Marker({
				position: locations[i].position,
				map: map,
				title: title.join(', '),
				location: locations[i].name,
				address: locations[i].address,
				meetings: locations[i].meetings
			});

			//set infowindow to open when marker is clicked
			marker.addListener('click', function() {
				//build html content
				var content = '<div class="infowindow">' +
					//'<h2>' + this.meetings[0].name + '</h2>';
					'<h2>' + this.address + '</h2>';					
				if (this.location && this.location != this.address) {
					content += '<h3>' + this.location + '</h3>';
				}

				if (this.address) {
					content += '<address><a href="https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(this.address) + '" target="_blank">Directions</a></address>';
				}
				
				//empty array for each day of the week (to build meeting columns)
				var location_days = [[], [], [], [], [], [], []];
				
				//loop through meetings and put them in buckets
				for (var j = 0; j < this.meetings.length; j++) {
					//todo add type here
					location_days[this.meetings[j].day].push(formatTime(this.meetings[j].time) + ' ' + this.meetings[j].topic);
				}
				
				for (var j = 0; j < 7; j++) {
					content += '<div class="day' + (!location_days[j].length ? ' empty' : '') + '">' +
						'<h4>' + days[j] + '</h4>';
					for (var k = 0; k < location_days[j].length; k++) {
						content += '<div class="meeting">' + location_days[j][k] + '</div>';
					}
					content += '</div>';
				}
				
				content += '</div>';
				
				//set content
				infowindow.setContent(content);
				
				//open marker
				infowindow.open(map, this);
			});
			
			//append to array, so we can clear it later
			markers.push(marker);
		}
		
		//fit the map to marker bounds
		if (fitBounds) map.fitBounds(bounds);

	});	
}

//get a JSON url, return contents
function getJSON(url, callback) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', url, true);
	xhr.responseType = 'json';
	xhr.onload = function() {
		callback(xhr.response);
	};
	xhr.send();
}

//run this when Google Maps has loaded
function initMap() {

	//define map, set to blank
    map = new google.maps.Map(document.getElementById('map'), {
		mapTypeControlOptions: {
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			position: google.maps.ControlPosition.BOTTOM_CENTER
		},
		fullscreenControl: false,
    });

	//define infowindow
	infowindow = new google.maps.InfoWindow();
	
	//get data and show markers with query string
	getAndShowMarkers(window.location.search, true);
	
	//add geolocation button
	if (navigator.geolocation) {
	
		//create and style button
		var geoButton = document.createElement('button');
		geoButton.style.backgroundColor = 'white';
		geoButton.style.backgroundImage = 'url(location-off.png)';
		geoButton.style.backgroundSize = '18px 18px';
		geoButton.style.backgroundPosition = '5px 5px';
		geoButton.style.backgroundRepeat = 'no-repeat';
		geoButton.style.border = 'none';
		geoButton.style.outline = 'none';
		geoButton.style.width = '28px';
		geoButton.style.height = '28px';
		geoButton.style.borderRadius = '2px';
		geoButton.style.boxShadow = '0 1px 4px rgba(0, 0, 0, 0.3)';
		geoButton.style.cursor = 'pointer';
		geoButton.style.marginRight = '10px';
		geoButton.style.padding = '0';
		geoButton.title = 'Your Location';
		
		//add action
		geoButton.addEventListener('click', function() {
			
			geoButton.style.backgroundImage = 'url(spinner.gif)';

			navigator.geolocation.getCurrentPosition(function(position) {
				var geoLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				geoButton.style.backgroundImage = 'url(location-on.png)';
				
				//create user marker
				var geoMarker = new google.maps.Marker({
					position: geoLocation,
					icon: {
						path: google.maps.SymbolPath.CIRCLE,
						scale: 7,
						fillColor: '#3a84df',
						fillOpacity: 1,
						strokeColor: 'white',
						strokeWeight: 3
					},
					map: map
				});
				
				map.setZoom(14);
				map.panTo(geoLocation);
			}, function(error) {
				geoButton.style.backgroundImage = 'url(location-off.png)';
				alert(error.message);
			});
		});
		
		map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(geoButton);
	}	

	//create and style dropdown menu
	var typeSelect = document.createElement('select');
	typeSelect.style.mozAppearance = 'none';
	typeSelect.style.webkitAppearance = 'none';
	typeSelect.style.backgroundImage = 'url(menu.svg)';
	typeSelect.style.backgroundRepeat = 'no-repeat';
	typeSelect.style.backgroundColor = 'white';
	typeSelect.style.backgroundSize = '35px 35px';
	typeSelect.style.backgroundPosition = '2px 5px';
	typeSelect.style.fontSize = '30px';
	typeSelect.style.marginTop = '10px';
	typeSelect.style.textAlign = 'center';
	typeSelect.style.borderRadius = '2px';
	typeSelect.style.boxShadow = '0 1px 4px rgba(0, 0, 0, 0.3)';
	typeSelect.style.border = 'none';
	typeSelect.style.padding = '5px 20px 5px 45px';
	
	//append options (URLs match the cfif/cfelseif where conditions json.cfm)
	var options = {
		'': 'All Meetings',
		'?type=rightnow': 'Upcoming Today',
		'?type=open': 'Open',
		'?type=Sunday': 'Sunday',
		'?type=Monday': 'Monday',
		'?type=Tuesday': 'Tuesday',
		'?type=Wednesday': 'Wednesday',
		'?type=Thursday': 'Thursday',
		'?type=Friday': 'Friday',
		'?type=Saturday': 'Saturday',
		'?type=morning': '6AM to Noon',
		'?type=noon': 'Noon',
		'?type=afternoon': '1PM to 6PM',
		'?type=evening': '6PM to 9PM',
		'?type=night': '9PM to Midnight',
		'?type=midnight': 'Midnight to 6AM',
		'?type=women': 'Women',
		'?type=men': 'Men',
		'?type=yp': 'Young People',
		'?type=gay': 'LGBT',
		'?type=child': 'Child Friendly',
		'?type=smoking': 'Smoking',
		'?type=spanish': 'Spanish',
		'?type=nam': 'Native American',
		'?type=wheelchair': 'Wheelchair Access',
		
	}
	for (var i = 0; i < Object.keys(options).length ; i++) {
		var option = document.createElement('option');
		option.value = Object.keys(options)[i];
		option.text = options[option.value];
		if (window.location.search == option.value) option.selected = true;
		typeSelect.appendChild(option);
	}
	
	//add action
	typeSelect.addEventListener('change', function(){
		getAndShowMarkers(this.value);
		
		//set this query string in the URL, if the browser supports it
		if (history.pushState) {
			history.pushState(null, null, '/map/' + this.value);
		}
		
	});
	
	map.controls[google.maps.ControlPosition.TOP_CENTER].push(typeSelect);
}