Komento.module('site/form/form', function($) {

var module = this;

Komento.require()
.library('markitup', 'expanding', 'scrollTo')
.script('site/form/location', 'site/form/attachments')
.done(function($) {

Komento.Controller('Form', {
	defaults: {

		// Inputs
		'{form}': '[data-kt-form-element]',
		'{parentId}': '[data-kt-parent]',
		'{username}': '[data-kt-register-username]',
		'{name}': '[data-kt-name]',
		'{email}': '[data-kt-email]',
		'{website}': '[data-kt-website]',
		'{terms}': '[data-kt-terms]',
		'{ratings}': '[data-kt-ratings-star]',

		// Used in replying to comments
		'{parentId}': '[data-kt-parent]',

		// Editor
		'{editor}': '[data-kt-editor]',

		// Form actions
		'{cancel}': '[data-kt-cancel]',
		'{save}': '[data-kt-submit]',

		// Terms
		'{viewTnc}': '[data-kt-tnc-view]',
		'{tncCheckbox}': '[data-kt-terms]',

		// Location services
		'{location}': '[data-kt-location]',

		// Attachments
		'{attachments}': '[data-kt-attachments]',

		// Alerts
		'{alert}': '[data-kt-alert]',

		// Re-Captcha
		'{recaptchaResponse}': "[data-kt-recaptcha-response]",

		// Standard Builtin Captcha
		'{captchaImage}': '[data-kt-captcha-image]',
		'{captchaResponse}': '[data-kt-captcha-response]',
		'{captchaId}': '[data-kt-captcha-id]',
		'{captchaReload}': '[data-kt-captcha-reload]',

		// Counter
		'{counter}': '[data-kt-text-counter]'
	}
}, function(self, opts) { return {

	init: function() {

		// Initialize the editor
		self.initEditor();

		if (opts.location) {
			self.initLocation();
		}

		if (opts.attachments.enabled) {
			self.initAttachments();
		}

		// Reset the form in case the browser is caching it.
		self.resetForm();
	},

	getLocationController: function() {
		return self.location().controller(Komento.Controller.Location.Form);
	},

	getAttachmentsController: function() {
		var controller = self.attachments().controller(Komento.Controller.Uploader);

		return controller;
	},

	getWrapper: function() {
		var wrapper = self.element.parents('[data-kt-wrapper]');

		return wrapper.controller();
	},

	getCommentsList: function() {
		var wrapper = this.getWrapper();
		var list = wrapper.comments();

		return list;
	},

	initEditor: function() {
		// Initialize bbcode if we need to
		if (opts.bbcode) {
			self.editor().markItUp(opts.markupSet());
		}

		// Implement expanding textarea on the editor
		self.editor().expandingTextarea();
	},

	initAttachments: function() {
		self.attachments().addController(Komento.Controller.Uploader, {
			"upload_max_size": opts.attachments.upload_max_size,
			"upload_max_files": opts.attachments.upload_max_files,
			"extensions": opts.attachments.extensions,
			"{parent}": this
		});
	},

	initLocation: function() {
		self.location().addController(Komento.Controller.Location.Form, {
			"{parent}": this,
			"location_key": opts.location_key
		});
	},

	insertText: function(text, position) {
		var position = position == undefined ? 0 : position;
		var contents = self.editor().val();

		if (position == 0) {
			// Since the position is 0, we can say we are just prepending the text
			contents = text + contents;
		} else {
			contents = contents.substring(0, position) + text + contents.substring(position, contents.length);
		}

		// Focus on the editor
		self.editor().val(contents);
		self.editor().focus();

		// Update the comments length
		self.updateCommentLength();
	},

	// Resets the comment form
	resetForm: function() {

		self.editor().val('');

		var parentId = self.parentId().val();

		// Reset reply
		if (parentId != 0) {
			self.cancel().click();
		}

		// Reset comment length count
		self.counter().text('0');

		// Reset location form
		if (self.location().length > 0) {
			self.getLocationController().removeLocation();
		}

		// Reset attachments
		if (opts.attachments.enabled) {
			self.getAttachmentsController().resetForm();
		}

		// Reset ratings
		if (self.ratings().length > 0) {
			self.ratings().raty('cancel');
		}

		// Reset submit button
		self.save()
			.removeAttr('disabled')
			.removeClass('is-loading');
	},

	updateCommentLength: function() {
		self.counter().text(self.editor().val().length);
	},

	saveComment: function() {

		// Get the form inputs
		var data = self.form().serializeObject();

		// Insert attachment ids
		if (opts.attachments.enabled) {
			data.attachments = self.getAttachmentsController().getUploadedIds();
		}

		// data.parentid =
		data.component = Komento.component;
		data.cid = Komento.cid;
		data.contentLink = Komento.contentLink;
		data.parent_id = self.parentId().val();
		data.tnc = self.tncCheckbox().is(':checked');

		// Recaptcha
		data.recaptchaResponse = self.recaptchaResponse().val();

		Komento
			.ajax('site/views/comments/add', $.extend({}, data))
			.done(function(message, html, state, sorting) {

				if (state == 1) {
					var wrapper = self.getWrapper();
					wrapper.increaseCounter();

					// Increase the count so that the notification doesn't notify
					Komento.loadedCount += 1;
					Komento.totalCount += 1;
				}

				var list = self.getCommentsList();
				var item = $(html);

				// if it is detected as spam, don't append list
				if (state == 3) {
					self.notification(message, 'warning');
				} else {
					list.controller().insertRow(item, data.parent_id, sorting);

					type = (state == 2) ? 'info' : 'success';

					self.notification(message, type);
				}

				self.resetForm();
				self.reloadCaptcha();

				$('[data-kt-comments-container]').removeClass('is-empty');
			})
			.fail(function(message) {
				self.notification(message, 'error');
			})
			.always(function() {
				// Even if it fails, we should restart the submit button
				self.save()
					.removeAttr('disabled')
					.removeClass('is-loading');
			});
	},

	reloadCaptcha: function() {

		if (!opts.showCaptcha) {
			return;
		}

		// Recaptcha
		if (opts.recaptcha) {
            grecaptcha.reset();
			return;
		}

		self.captchaReload().addClass('is-loading');

		// Standard built in captcha
		Komento.ajax('site/views/captcha/reload', {
			"id": self.captchaId().val()
		}).done(function(data) {

			self.captchaReload().removeClass('is-loading');

			self.captchaImage().attr('src', data.image);
			self.captchaId().val(data.id);
			self.captchaResponse().val('');
		});
	},

	// Allows caller to invoke the form to be moved to a specific comment
	reply: function(item) {

		// Reset the form
		self.resetForm();

		var id = item.data('id');
		var depth = parseInt(item.data('depth')) + 1;

		self.parentId().val(id);

		// Move the comment to the item
		self.element
			.addClass('is-replying')
			.appendTo(item)
			.scroll();
	},

	notification: function(message, type) {
		self.alert()
			.removeClass('o-alert--success o-alert--danger o-alert--warning o-alert--info o-alert--error')
			.addClass('o-alert--' + type)
			.html(message)
			.removeClass('t-hidden');
	},

	closeNotification: function() {
		self.alert().addClass('t-hidden');
	},

	clearNotifications: function() {
		self.alert()
			.html('')
			.removeClass('error')
			.addClass('t-hidden');
	},

	// We need to convert this into an ajax call to view terms and conditions
	"{viewTnc} click": function() {
		Komento.dialog({
			content: Komento.ajax('site/views/comments/getTnc')
		});
	},

	"{editor} keydown": function(editor, event) {

		// Bind cmd + enter / ctrl + enter
		if ((event.metaKey || event.ctrlKey) && event.keyCode == 13) {
			self.save().click();

			event.preventDefault();
		}
	},

	"{editor} keyup" :function(editor) {
		self.updateCommentLength();
	},

	// Since there is only 1 form at any given point of time, we will now return the form to it's original state
	'{cancel} click': function(button, event) {

		self.parentid = 0;

		// Ensure parent id is always empty.
		self.parentId().val('');

		self.resetForm();
		self.element.removeClass('is-replying');
		self.element
			.appendTo(self.getWrapper().element);
	},

	"{save} click": function(button) {

		// Add loading indicator
		button
			.attr('disabled', true)
			.addClass('is-loading');

		// Clear all prior notifications
		self.clearNotifications();

		if (opts.attachments.enabled) {
			self.getAttachmentsController().startUpload();

			return;
		}

		self.saveComment();
		return;
	},

	"{captchaReload} click": function() {
		self.reloadCaptcha();
	}
}});

module.resolve();
});
});
