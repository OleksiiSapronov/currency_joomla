Komento.module('site/common', function($) {

var module = this;

var originalHide = $.fn.hide;
var originalShow = $.fn.show;

var originalScrollTo = function( element ) {
	$.scrollTo(element, 500);
};

$.fn.hide = function() {
	originalHide.apply(this, arguments);
	this.addClass('hidden');
	return this;
}

$.fn.show = function() {
	originalShow.apply(this, arguments);
	this.removeClass('hidden');
	return this;
}

$.fn.scroll = function() {
	originalScrollTo(this);
};

$.fn.highlight = function() {
	this.effect("highlight", {color: '#FDFFE0'}, 2000);
	return this;
};

$.fn.enable = function() {
	this.removeClass('disabled');
	return this;
};

$.fn.disable = function() {
	this.addClass('disabled');
	return this;
};

$.fn.switchOn = function() {
	this.removeClass('cancel');
	return this;
};

$.fn.switchOff = function() {
	this.addClass('cancel');
	return this;
};

$.fn.checkSwitch = function() {
	if(this.hasClass('cancel')) {
		return false;
	} else {
		return true;
	}
};

$.fn.checkClick = function() {

	if (this.hasClass('disabled')) {
		return false;
	}
	
	this.addClass('disabled');
	return true;
};

$.fn.exists = function() {
	return this.length > 0 ? true : false;
};


module.resolve();

});
