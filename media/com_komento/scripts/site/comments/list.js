Komento.module('site/comments/list', function($) {

var module = this;

Komento.require()
.library('markitup')
.script('site/common', 'site/vendors/lightbox')
.done(function($) {


Komento.Controller('Comments.List', {
	defaults: {

		// Comment
		'{item}': '[data-kt-comment-item]',

		// comments list
		// '{commentList}': '[data-kt-comments]',

		// Attachments
		'{attachmentWrapper}': '[data-kt-attachment-wrapper]',
		'{attachmentItem}': '[data-kt-attachments-item]',
		'{deleteAttachment}': '[data-kt-attachment-item-delete]',

		// Ratings
		'{ratings}': '[data-kt-ratings-item]',

		// Comment management tools
		'{delete}': '[data-kt-manage-delete]',
		'{unpublish}': '[data-kt-manage-unpublish]',
		'{pin}': '[data-kt-manage-pin]',
		'{unpin}': '[data-kt-manage-unpin]',
		'{submitSpam}': '[data-kt-submit-spam]',

		// Replying
		'{reply}': '[data-kt-reply]',

		// Editing
		'{edit}': '[data-kt-manage-edit]',
		'{editCancel}': '[data-kt-edit-cancel]',
		'{editSave}': '[data-kt-edit-save]',
		'{editForm}': '[data-kt-edit-form]',

		// Comment actions
		'{like}': '[data-kt-likes-action]',
		'{likeBrowser}': '[data-kt-likes-browser]',
		'{likeBrowserContents}': '[data-kt-likes-browser-contents]',
		'{likeWrapper}': '[data-kt-likes-wrapper]',
		'{likeCounter}': '[data-kt-likes-counter]',
		'{likeViewAll}': '[data-kt-likes-view-all]',

		// Reporting
		'{report}': '[data-kt-report]',

		// Sharing
		'{sharingWrapper}': '[data-kt-sharing]',
		'{sharing}': '[data-kt-sharing] [data-link]',

		// view all replies
		'{viewreplies}' : '[data-kt-view-reply]'
	}
}, function(self, opts) { return {

	init: function() {

		// Initialize sharing options
		opts.sharing = [];

		// Initialize ratings
		if (opts.showRatings) {
			self.initRatings();
		}

		if (opts.cleanGist) {
			self.cleanGist(self.item());
		}
	},

	initRatings: function() {

		self.ratings().each(function() {
			var item = $(this);

			self.initRating(item);
		});
	},

	initRating: function(element) {

		element.raty({
			starType: 'i',
			half: true,
			readOnly: true,
			score: element.data('score')
		});
	},

	insertRow: function(item, parentId, sorting) {
		var item = $(item);

		// Since a new item is added, we should not have an empty class
		self.element.removeClass('is-empty');

		// We also need to apply the ratings on the comment item
		var hasRatings = item.find('[data-kt-ratings-item]').length > 0;

		if (parentId != undefined && parentId != '' && parentId != '0') {

			var elementToInject = self.fintInsertRowPosition(parentId, sorting);

			console.log(elementToInject);

			if (elementToInject === false) {
				elementToInject = self.element.find("[data-id=" + parentId + "]");
				item.insertAfter(elementToInject);
			} else {

				if (sorting == 'latest') {
					item.insertBefore(elementToInject);
				} else {
					item.insertAfter(elementToInject);
				}
			}

		} else {
			if (sorting == 'latest') {
				// Prepend output to the list
				item.prependTo(self.element);
			} else {
				// Append output to the list
				item.appendTo(self.element);
			}
		}


		if (hasRatings) {
			self.initRating(item.find(self.ratings.selector));
		}

		// Reload syntax highlighter
		if (opts.prism) {
			Prism.highlightAll();
		}

		// Try to find for gist embeds
		this.initGist(item);
	},


	fintInsertRowPosition: function(parentId, sorting) {

		if (self.element.find("[data-parentid=kmt-" + parentId + "]").length > 0) {

			var lastItem = null;

			if (sorting == 'latest') {
				lastItem = self.element.find("[data-parentid=kmt-" + parentId + "]").first();
			} else {
				lastItem = self.element.find("[data-parentid=kmt-" + parentId + "]").last();
			}

			lastItemParentId = $(lastItem).data('id');

			if (self.element.find("[data-parentid=kmt-" + lastItemParentId + "]").length > 0) {
				return self.fintInsertRowPosition(lastItemParentId, sorting);
			} else {
				return lastItem;
			}

		} else {
			// lastItem = self.element.find("[data-id=" + parentId + "]");
			// return lastItem;
			return false;
		}
	},

	cleanGist: function(item) {

		var gists = item.find('script[src^="https://gist.github.com/"]');

		if (!gists.length) {
			return;
		}

		gists.each(function(idx, el) {
			$(el).remove();
		});

	},

	initGist: function(item) {

		var gists = item.find('script[src^="https://gist.github.com/"]');

		if (!gists.length) {
			return;
		}

		gists.each(function(idx, el) {

			var embed = $(el);

			// $.getJSON(embed.attr('src') + 'on?callback=?', function(data) {
			// 	embed.replaceWith($(data.div));

			// 	self.insertStylesheet(data.stylesheet);
			// });

			var link = embed.attr('src') + 'on?callback=?';
			var jqxhr = $.getJSON(link, function() {

			})
			.done(function(data) {
				embed.replaceWith($(data.div));
				self.insertStylesheet(data.stylesheet);
			})
			.fail(function() {
				console.log("error");
			});
		});
	},

	insertStylesheet: function(url) {
		var head = $('head');

		if (head.find('link[rel="stylesheet"][href="'+url+'"]').length < 1) {
			head.append('<link rel="stylesheet" href="'+ url +'" type="text/css" />');

			// console.log('appended ' + url);
		}
	},

	getWrapper: function() {
		var wrapper = $('[data-kt-wrapper]').controller();

		return wrapper;
	},

	getItem: function(element) {
		var item = element.parents(self.item().selector);

		return item;
	},

	'{sharing} click': function(link) {
		var item = self.getItem(link);
		var id = item.data('id');

		if (opts.sharing[id] == undefined) {

			var wrapper = item.find(self.sharingWrapper.selector);

			opts.sharing[id] = {
				title: wrapper.data('title'),
				summary: wrapper.data('summary'),
				permalink: wrapper.data('permalink'),
				width: wrapper.data('width'),
				height: wrapper.data('height')
			};
		}

		var sharing = opts.sharing[id];

		// We need to replace the url with the appropriate attributes
		var url = link.data('link');
		url = url.replace(/SUMMARY/, sharing.summary)
				.replace(/TITLE/, sharing.title)
				.replace(/PERMALINK/, sharing.permalink);

		var width = sharing.width;
		var height = sharing.height;
		var left = (screen.width/2) - (width / 2);
		var top = (screen.height/2) - (height / 2);


		window.open(url , "" , 'scrollbars=no,resizable=no,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
	},

	'{deleteAttachment} click': function(button, event) {

		var item = self.getItem(button);
		var id = item.data('id');
		var attachmentId = button.data('id');
		var attachmentItem = button.parents(self.attachmentItem().selector);

		Komento.dialog({
			"content": Komento.ajax('site/views/attachments/confirmDelete', {"id" : attachmentId}),
			"bindings": {

				"{submit} click": function() {

					Komento.ajax('site/views/attachments/delete', {
						"id": attachmentId
					}).done(function() {

						// Hide dialog
						Komento.dialog().close();

						// Remove file
						attachmentItem.remove();

						var totalAttachments = item.find(self.attachmentItem.selector).length;

						if (totalAttachments <= 0) {
							self.attachmentWrapper().removeClass('has-attachments');
						}
					});
				}
			}
		});

	},

	'{delete} click': function(button, event) {
		var item = self.getItem(button);
		var wrapper = self.getWrapper();
		var id = item.data('id');

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmDelete', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/delete', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	clearNotifications: function() {
		$('[data-kt-alert]')
		.html('')
		.removeClass('error')
		.addClass('t-hidden');
	},

	'{reply} click': function(button, event) {
		var wrapper = self.getWrapper();
		var form = wrapper.getForm();
		var item = self.getItem(button);

		// Clear any message alert on the komento area
		self.clearNotifications();

		// Request the form controller to move itself to this location
		form.reply(item);
	},

	'{edit} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');

		// State to indicate we are now editing the comment
		item.addClass('is-editing');

		// If there is already a form then we shouldn't need to fire another ajax call
		var form = item.find(self.editForm.selector);
		var content = item.find('[data-kt-comment-content]');

		// If the script executes in here, it means that the edit form is already available
		if (form.length > 0) {
			form.removeClass('t-hidden');
			content.addClass('t-hidden');

			return;
		}

		// Get the raw comment object
		Komento.ajax('site/views/comments/edit', {
			"id": id
		}).done(function(form, data) {

			var form = $(form);
			var content = item.find('[data-kt-comment-content]');

			// Add the form after the content
			content.after(form);
			content.addClass('t-hidden');

			if (Komento.bbcode) {
				form.find('[data-kt-editor]').markItUp(Komento.bbcodeButtons());
			}

			// Bind the cancel button
			form.find(self.editCancel.selector)
				.off('click')
				.on('click', function() {
					form.addClass('t-hidden');
					content.removeClass('t-hidden');
				});

			// Bind the save button
			form.find(self.editSave.selector)
				.off('click')
				.on('click', function() {
					var comment = form.find('[data-kt-editor]').val();

					Komento.ajax('site/views/comments/save', {
						"id": id,
						"comment": comment
					}).done(function(message, contents) {

						// Update the contents
						content.html(contents);

						// Update the edited message
						var edited = item.find('[data-kt-comment-edited]');

						if (edited.length > 0) {
							item.addClass('is-edited');
							edited.html(message);
						}


						// Simulate the cancel click button
						form.find(self.editCancel.selector).click();
					});
				});
		});
	},

	'{viewreplies} click': function(button, event) {

		var parentId = button.data('id');
		var rownumber = button.data('rownumber');

		button.find('a').addClass('is-loading');
		button.find('a > span').addClass('t-hidden');

		self.loadReplies(parentId, rownumber)
		.done(function(html){
			// $(html).insertAfter(self.element.find("[data-id=" + parentId + "]"));
			$(html).insertAfter(button);

			var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

			if (hasRatings) {
				self.initRatings($(html).find(self.ratings.selector));
			}

			// Reload syntax highlighter
			if (opts.prism) {
				Prism.highlightAll();
			}

			// Try to find for gist embeds
			self.initGist($(html));

			// hide the button.
			button.find('a').removeClass('is-loading');
			button.find('a > span').removeClass('t-hidden');
			button.addClass('t-hidden');
		});

	},

	loadReplies: function(parentId, rownumber) {
		// var task = $.Deferred();

		return Komento.ajax('site/views/comments/loadReplies', {
			component: Komento.component,
			cid: Komento.cid,
			sort: Komento.sort,
			parentid: parentId,
			rownumber: rownumber,
			contentLink: Komento.contentLink,
		});
		// .done(function(html) {
		// 	task.resolve(html);
		// })
		// .fail(function() {
		// 	task.reject;
		// });

		// return task;
	},



	'{report} click': function(button, event) {
		var item = self.getItem(button);

		Komento.dialog({
			"content": Komento.ajax('site/views/reports/report', { "id": item.data('id')})
		});
	},

	'{publishButton} click': function(el, event) {
		// propagation hack to solve reply form issues sharing the same function name with famelist.js
		event.stopPropagation();

		if(self.item.childs > 0) {
			self.showPublishDialog(el);
		} else {
			self.publishComment(el);
		}
	},

	'{unpublish} click': function(button, event) {

		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmUnpublish', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/unpublish', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	'{unpin} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.ajax('site/views/comments/unpin', {
			"id": id
		}).done(function() {
			item.removeClass('is-featured');
		});
	},

	'{pin} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.ajax('site/views/comments/pin', {
			"id": id
		}).done(function() {
			item.addClass('is-featured');
		});
	},

	'{submitSpam} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmSubmitSpam', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/submitSpam', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	// Renders the dialog to display people who likes the comment
	'{likeBrowser} click': function(counter, event) {
		var item = self.getItem(counter);
		var id = item.data('id');

		// Get total likes
		var wrapper = item.find(self.likeWrapper().selector);
		var counter = item.find(self.likeCounter().selector);
		var total = parseInt(counter.text());

		Komento.ajax('site/views/likes/browse', {
			"id": id,
			"total": total
		}).done(function(contents) {
			item.find(self.likeBrowserContents.selector).html(contents);
		});
	},

	'{like} click': function(button, event) {
		var type = button.data('type');
		var item = self.getItem(button);

		Komento.ajax('site/views/likes/action', {
			"type": type,
			"id": item.data('id')
		}).done(function() {

			var wrapper = item.find(self.likeWrapper().selector);
			var counter = item.find(self.likeCounter().selector);
			var count = parseInt(counter.text());

			// Increment counter
			if (type == 'unlike') {
				wrapper.removeClass('is-liked');
				counter.text(count - 1);
				return;
			}

			// Increment likes counter
			wrapper.addClass('is-liked');
			counter.text(count + 1);
		});
	},

	'{likeViewAll} click': function(button) {
		var id = button.data('id');

		Komento.dialog({
			'content': Komento.ajax('site/views/likes/browseAll', {"id" : id})
		})
	}
}});
module.resolve();

});

});
