Komento.module('site/form/location', function($) {

var module = this;

Komento.require()
.library("ui/autocomplete")
.done(function($){

Komento.Controller('Location.Form', {
	defaultOptions: {
		language: 'en',
		initialLocation: null,

		"{button}": "[data-kt-location-button]",
		"{form}": '[data-kt-location-form]',
		"{address}": "[data-kt-location-address]",
		"{latitude}": "[data-kt-location-lat]",
		"{longitude}": '[data-kt-location-lng]',
		"{detect}": "[data-kt-location-detect]"
	}
}, function(self, opts) { return {

	init: function() {

		self.resetForm();

		var mapReady = $.uid("ext");

		window[mapReady] = function() {
			$.___GoogleMaps.resolve();
		}

		if (!$.___GoogleMaps) {

			$.___GoogleMaps = $.Deferred();

			if (window.google === undefined || window.google.maps === undefined) {
				Komento.require()
					.script(
						{prefetch: false},
						"https://maps.googleapis.com/maps/api/js?sensor=true&language=" + self.options.language + "&callback=" + mapReady + '&key=' + opts.location_key
					);
			} else {
				$.___GoogleMaps.resolve();
			}
		}

		// Defer instantiation of controller until Google Maps library is loaded.
		$.___GoogleMaps.done(function() {
			self._init();
		});
	},

	resetForm: function() {

		self.locationResolved = false;

		self.latitude().val('');
		self.longitude().val('');
		self.address().val('');
	},

	_init: function() {

		self.geocoder = new google.maps.Geocoder();

		self.hasGeolocation = navigator.geolocation!==undefined;

		if (!self.hasGeolocation) {
			self.detect().remove();
		} else {
			self.detect().show();
		}

		self.address()
			.autocomplete({

				delay: 300,
				minLength: 0,
				source: self.retrieveSuggestions,
				select: function(event, ui) {

					self.address()
						.autocomplete("close");

					self.setLocation(ui.item.location);
				}
			})
			.prop("disabled", false);

		self.address().addClass('location-suggestion');

		var initialLocation = $.trim(self.options.initialLocation);

		if (initialLocation) {

			self.getLocationByAddress(initialLocation, function(location) {
					self.setLocation(location[0]);
			});
		}

		self.busy(false);
	},

	busy: function(isBusy) {
		self.address().toggleClass("is-loading", isBusy);
		self.detect().removeClass('is-loading');
	},

	getUserLocations: function(callback) {
		self.getLocationAutomatically(function(locations) {
				self.userLocations = self.buildDataset(locations);
				callback && callback(locations);
		});
	},

	getLocationByAddress: function(address, callback) {

		self.geocoder.geocode({
			"address": address
		}, callback);
	},

	getLocationByCoords: function(latitude, longitude, callback) {

		self.geocoder.geocode({
			"location": new google.maps.LatLng(latitude, longitude)
		}, callback);
	},

	getLocationAutomatically: function(success, failCallback) {

		if (!navigator.geolocation) {
			return fail("ERRCODE", "Browser does not support geolocation or do not have permission to retrieve location data.")
		}

		navigator.geolocation.getCurrentPosition(function(position) {
			self.getLocationByCoords(position.coords.latitude, position.coords.longitude, success)
		}, failCallback);
	},

	setLocation: function(location) {

		if (!location) {
			return;
		}

		self.locationResolved = true;
		self.lastResolvedLocation = location;

		self.address().val(location.formatted_address);

		self.latitude().val(location.geometry.location.lat());

		self.longitude().val(location.geometry.location.lng());
	},

	removeLocation: function() {
		self.resetForm();
	},

	buildDataset: function(locations) {

		var dataset = $.map(locations, function(location){
			return {
				"label": location.formatted_address,
				"value": location.formatted_address,
				"location": location
			};
		});

		return dataset;
	},

	retrieveSuggestions: function(request, response) {

		self.busy(true);

		var address = request.term,

			respondWith = function(locations) {
				response(locations);
				self.busy(false);
			};

		// User location
		if (address=="") {
			respondWith(self.userLocations || []);
		} else {
			// Keyword search
			self.getLocationByAddress(address, function(locations) {
				respondWith(self.buildDataset(locations));
			});
		}
	},

	suggestUserLocations: function() {

		if (self.hasGeolocation && self.userLocations) {
			self.resetForm();
			
			self.address()
				.autocomplete("search", "");
		}

		self.busy(false);
	},

	"{button} click": function(button, event) {
		button.toggleClass('is-active');
		
		self.form().toggleClass('t-hidden');
	},

	"{address} blur": function() {

		// Give way to autocomplete
		setTimeout(function(){

			var address = $.trim(self.address().val());

			// Location removal
			if (address=="") {
				self.resetForm();
			} else if (self.locationResolved) {

				// Unresolved location, reset to last resolved location
				if (address != self.lastResolvedLocation.formatted_address) {
					self.setLocation(self.lastResolvedLocation);
				}
			} else {
				self.resetForm();
			}

		}, 250);
	},

	"{detect} click": function() {

		self.busy(true);

		self.detect().addClass('is-loading');

		if (self.hasGeolocation && !self.userLocations) {
			self.getUserLocations(self.suggestUserLocations);
		} else {
			self.suggestUserLocations();
		}
	},

	"{address} keypress": function(input) {
		input.keypress(function(event) {
			if (event.which == 13) {
				return false;
			}
		});
	}

}});

module.resolve();

});
});
