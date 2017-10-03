//define global variables
var map, infowindow, days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

//convert a HH:MM time to hh:mm:a
function formatTime(time) {
	if (time == '12:00') return 'Noon';
	var parts = time.split(':');
	var hours = parts[0] - 0;
	var minutes = parts[1];
	var ampm = hours > 11 ? 'pm' : 'am';
	if (!hours) hours = 12;
	if (hours > 12) hours -= 12;
	return hours + ':' + minutes + ampm;
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

//run this when map has loaded
function initMap() {

	//define map, set to blank
    map = new google.maps.Map(document.getElementById('map'));

	//define infowindow
	infowindow = new google.maps.InfoWindow();

	//fetch data
	getJSON('data-sample.json', function(meetings) { 
		
		//define an object for storing locations
		var locations = {};
		
		//define map bounds
		var bounds = new google.maps.LatLngBounds();
		
		//loop through json values
		for (var i = 0; i < meetings.length; i++) {
			
			//skip any empty locations
			if (!meetings[i].latitude || !meetings[i].longitude) continue;
			
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

				//extend bounds
				bounds.extend(locations[key].position);
			}
			
			//add this meeting
			locations[key].meetings[locations[key].meetings.length] = {
				name: meetings[i].name,
				day: meetings[i].day,
				time: meetings[i].time
			}
			
		}
		
		//fit the map to marker bounds
		map.fitBounds(bounds);
		
		//flatten locations into array
		locations = Object.values(locations);
		
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
			
			//add the marker to the map
			var marker = new google.maps.Marker({
				position: locations[i].position,
				map: map,
				title: locations[i].name,
				address: locations[i].address,
				meetings: locations[i].meetings
			});
			
			//set infowindow to open when marker is clicked
			marker.addListener('click', function() {
				//build html content
				var content = '<div class="infowindow">' +
					'<h2>' + this.title + '</h2>' +
					'<address>' + this.address + '</address>';
				for (var j = 0; j < this.meetings.length; j++) {
					content += '<div class="meeting"><time>' + 
						days[this.meetings[j].day] + ' ' +
						formatTime(this.meetings[j].time) + '</time>' +
						this.meetings[j].name +
					'</div>';
				}
				content += '</div>';
				
				//set content
				infowindow.setContent(content);
				
				//open marker
				infowindow.open(map, this);
			});
			
		}
		
	});
	
}