Komento.module('site/dashboard/default', function($) {
	
var module = this;

Komento.Controller('Dashboard', {
	defaults: {
		// Actions bar
		'{checkAll}': '[data-kt-dashbaord-checkall]',
		'{actions}': '[data-kt-dashboard-actions]',

		// Notice
		'{notice}': '[data-kt-dashboard-notice]',

		// Item
		'{item}': '[data-kt-dashboard-item]',
		'{checkbox}': '[data-kt-dashboard-item-checkbox]',
		'{delete}': '[data-kt-dashboard-delete]',
		'{unpublish}': '[data-kt-dashboard-unpublish]',
		'{publish}': '[data-kt-dashboard-publish]',
		'{spam}': '[data-kt-dashboard-spam]',
		'{notspam}': '[data-kt-dashboard-notspam]',
		'{moderate}': '[data-kt-dashboard-moderate]',
		'{clearReports}': '[data-kt-dashboard-reports-clear]'
	}
}, function(self, opts) { return {
	init: function() {
	},
	
	updateActions: function() {
		var checked = self.checkbox().is(':checked');

		self.actions().toggleClass('is-checked', checked);
	},

	getItems: function(value) {
		var items = self.checkbox(':checked');

		if (value === undefined) {
			var selected = items.map(function() {
				return this.value;
			}).get();

			return selected;
		}

		return items;
	},

	setNotice: function(message, type) {

		if (type == undefined) {
			type = 'success';
		}

		self.notice()
			.html(message)
			.removeClass('t-hidden o-alert--success o-alert--danger o-alert--warning o-alert--info')
			.addClass('o-alert--' + type);
	},

	updateItemState: function(checkboxes, state) {
		checkboxes.each(function(i) {
			var item = $(this).parents(self.item.selector);

			item
				.removeClass('is-published is-unpublished')
				.addClass(state);
		});
	},

	'{checkbox} change': function(checkbox, event) {
		var checked = checkbox.is(':checked');
		var parent = checkbox.parents(self.item.selector);

		if (checked) {
			parent.addClass('is-selected');
		} else {
			parent.removeClass('is-selected');
		}

		self.updateActions();
	},

	'{checkAll} change': function(checkbox, event) {
		var checked = checkbox.is(':checked');

		self.checkbox().prop('checked', checked);
		self.checkbox().trigger('change');
	},
	
	'{publish} click': function(button, event) {
		var items = this.getItems();

		// Since this is a non destructive operation, no point asking for cofirmation
		Komento.ajax('site/views/dashboard/publish', {
			"id": items
		}).done(function(message) {
			self.setNotice(message, 'success');

			var checkboxes = self.getItems('object');

			self.updateItemState(checkboxes, 'is-published');
		});
	},

	'{unpublish} click': function(button, event) {
		var items = this.getItems();

		// Since this is a non destructive operation, no point asking for cofirmation
		Komento.ajax('site/views/dashboard/unpublish', {
			"id": items
		}).done(function(message) {
			self.setNotice(message, 'success');

			var checkboxes = self.getItems('object');

			self.updateItemState(checkboxes, 'is-unpublished');
		});
	},

	'{delete} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmDelete', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{spam} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmSpam', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{notspam} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmRemoveSpam', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{moderate} click': function(button, event) {
		var items = this.getItems();
		var action = button.data('action');

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmModerate', {"items": items, "return": opts.return, "action": action}),
			bindings: {
			}
		});
	},

	'{clearReports} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmClearReports', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	}
}});

module.resolve();

});
