Komento.module('shared/elements', function($){

var module = this;
var tooltipLoaded = false;

Komento.isMobile = function() {
	try {
		document.createEvent('TouchEvent');
		return true;
	} catch(e) {
		return false;
	}
}

$(document).on('change.form.toggler', '[data-toggler-checkbox]', function() {
	var checkbox = $(this);
	var checked = checkbox.is(':checked');
	var parent = checkbox.parents('[data-bs-toggler]');

	if (parent.length > 0) {

		var input = parent.find('input[type=hidden]');
		input.val(checked ? 1 : 0).trigger('change');
	}
});


// Initialize yes/no buttons.
$(document).on('click.button.data-kt-api', '[data-kt-toggle-value]', function() {

	var button = $(this);
	var siblings = button.siblings("[data-kt-toggle-value]");
	var parent = button.parents('[data-kt-toggle="radio-buttons"]');

	if (parent.hasClass('disabled')) {
		return;
	}

	// This means that this toggle value belongs to a radio button
	if (parent.length > 0) {

		// Get the current button that's clicked.
		var value = $(this).data('kt-toggle-value');

		button.addClass("active");
		siblings.removeClass("active");

		// Set the value here.
		// Have to manually trigger the change event on the input
		parent.find('input[type=hidden]').val(value).trigger('change');
		return;
	}
});

// String truncater
// Used when there is a read more of a truncated content.
var selector = '[data-kt-truncater] > [data-readmore]';

$(document)
	.on('click.kt.strings.truncater', selector, function() {
		
		var section = $(this).parent();
		var original = section.find('[data-original]');
		var text = section.find('[data-text]');

		// Hide the link
		$(this).addClass('t-hidden');

		// Show the full contents
		text.addClass('t-hidden');
		original.removeClass('t-hidden');
	});

// Tooltips
// detect if mouse is being used or not.
var mouseCount = 0;
window.onmousemove = function() {

	mouseCount++;

	addTooltip();
};

var addTooltip = $.debounce(function(){

    if (!tooltipLoaded && mouseCount > 10) {

		tooltipLoaded = true;
		mouseCount = 0;

		$(document).on('mouseover.tooltip.data-kt-api', '[data-kt-provide=tooltip]', function() {

			$(this)
				.tooltip({
					delay: {
						show: 200,
						hide: 100
					},
					animation: false,
					template: '<div id="kt" class="tooltip tooltip-kt"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
					container: 'body'
				})
				.tooltip("show");
		});
    } else {
    	mouseCount = 0;
    }
}, 500);


if (!Komento.isMobile()) {
	$(document).on('mouseover.tooltip.data-kt-api', '[data-kt-provide=tooltip]', function() {

		$(this)
			.tooltip({
				delay: {
					show: 200,
					hide: 100
				},
				animation: false,
				template: '<div id="kt" class="tooltip tooltip-kt"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
				container: 'body'
			})
			.tooltip("show");
	});
}

// Popovers
$(document).on('mouseover.popover.data-kt-api', '[data-kt-provide=popover]', function() {
	$(this)
		.popover({
			delay: {
				show: 200,
				hide: 100
			},
			animation: false,
			trigger: 'hover',
			container: 'body'
		})
		.popover("show");
});

module.resolve();

});
