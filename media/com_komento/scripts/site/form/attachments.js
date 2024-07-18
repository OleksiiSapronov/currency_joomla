Komento.module('site/form/attachments', function($) {

var module = this;

Komento.require()
.library('plupload')
.done(function($) {

Komento.Controller('Uploader', {
	defaults: {
		uploadUrl: $.indexUrl + '?option=com_komento&controller=file&tmpl=component&task=upload&component=' + Komento.component,
		uploadedId: [],

		'{uploader}': '[data-kt-attachments-form]',
		'{uploadButton}': '[data-kt-attachments-button]',
		'{uploadQueue}': '[data-kt-attachments-queue]',
		'{template}': '[data-kt-attachments-item][data-template]',
		'{item}': '[data-kt-attachments-item]',
		'{counter}': '[data-kt-attachments-counter]',
		'{removeFile}': '[data-kt-attachments-item-remove]'
	}
}, function(self, opts) { return {
	init: function() {

		// Initialize the template
		self.initTemplate();

		// Implement plupload
		self.uploader().implement('plupload', {
			settings: {
				"url": self.options.uploadUrl + '&' + Komento.token() + '=1',
				"max_file_size": opts.upload_max_size,
				"filters": [{
						"title": 'Allowed File Type', 
						"extensions": opts.extensions
				}]
			},
			'{uploader}': self.uploader().selector,
			'{uploadButton}': self.uploadButton().selector
		}, function() {
			self.plupload = this.plupload;
		});
	},

	resetForm: function() {
		opts.uploadedId = [];
	},

	getUploadedIds: function() {
		return opts.uploadedId;
	},

	initTemplate: function() {
		opts.itemTemplate = self.template().clone();

		// Remove the template from the layout
		self.template().remove();
	},

	getItemTemplate: function(file) {
		var item = opts.itemTemplate.clone();

		item.removeAttr('data-template');
		item.attr('id', file.id);
		item.find('[data-size]').html(file.size);
		item.find('[data-title]').html(file.title);
		item.removeClass('t-hidden');

		item.data('file', file);

		return item;
	},

	hasItems: function() {
		return opts.uploadedId.length > 0;
	},

	startUpload: function() {

		if (self.plupload.files.length > 0) {
			self.plupload.start();
			return;
		}

		self.parent.saveComment();
	},

	addFiles: function(files) {
		if (files.length < 1) {
			return;
		}

		// Clear notifications
		self.parent.clearNotifications();

		$.each(files, function(index, item) {

			// If the user tries to upload more than the allowed files, do not add them
			if (self.plupload.files.length > opts.upload_max_files) {
				self.plupload.removeFile(item);
				return true;
			}

			// Check for file status before proceeding
			if (item.status != 1) {
				return true;
			}

			var size = parseInt(item.size / 1024);

			var template = self.getItemTemplate({
				"id": item.id,
				"title": item.name,
				"size": size
			});

			// Append the item to the queue
			self.uploadQueue()
				.removeClass('t-hidden')
				.append(template);
		});
	},

	removeItem: function(item) {

		// Remove the dom
		item.remove();

		// Remove from the plupload queue
		var id = item.attr('id');
		var file = self.plupload.getFile(id);

		self.plupload.removeFile(file);

		// Hide the upload queue when it is empty
		var total = self.item().length;

		if (!total) {
			self.uploadQueue().addClass('t-hidden');
		}
	},

	// When a file is added into the queue
	"{uploader} FilesAdded": function(el, event, uploader, file) {
		self.addFiles(file);
	},

	'{uploader} UploadComplete': function(el, event, uploader, files) {

		self.item().each(function(index, item) {
			var item = $(item);

			self.removeItem(item);
		});

		// Once the upload is completed, we'll need to submit the comment
		self.parent.saveComment();
	},

	'{uploader} FileUploaded': function(el, event, uploader, file, response) {
		
		// Once a file is uploaded, push it into the ids so that other controllers know if there are pending files
		if (response.status == 1) {
			opts.uploadedId.push(response.id);
		}

		if( response.status == 'notallowed' ) {
			self.plupload.stop();
			self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_UPLOAD_NOT_ALLOWED'));
			return;
		}

		if( response.status == 'exceedfilesize' ) {
			self.plupload.stop();
			self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_MAX_FILE_SIZE', Komento.options.config.upload_max_size + 'mb'));
			return;
		}
	},

	'{uploader} QueueChanged': function(el, event, uploader) {
		self.counter().text(uploader.files.length);
	},

	'{uploader} Error': function(el, event, uploader, error) {

		// Clear previous notifications
		self.parent.clearNotifications();
		
		switch (error.code) {
			case -600:
				self.parent.notification(error.message, 'error');

				break;
			case -601:
				// self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_FILE_EXTENSION', Komento.options.config.upload_allowed_extension));
				break;
		}
	},

	'{removeFile} click': function(button) {
		var item = button.parents(self.item().selector);

		self.removeItem(item);
	}
}});

module.resolve();
});
});
