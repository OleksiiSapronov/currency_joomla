Komento.module('site/comments/wrapper', function($) {

var module = this;

Komento.require()
.library('scrollTo')
.script('site/comments/list')
.done(function($) {

	Komento.Controller('Wrapper', {
		defaults: {
			"component": null,
			"cid": null,
			"currentUrl": null,

			// Form
			'{form}': '[data-kt-form]',

			// Comments list
			"{comments}": "[data-kt-comments]",

			// Comments sorting
			"{sorting}": "[data-kt-sorting]",

			// ratings
			'{ratings}': '[data-kt-ratings-item]',

			// // comments loadmore button
			// '{loadmoreButton}': '[data-kt-loadmore]',

			//loadmore
			'{loadMore}': '[data-kt-loadmore]',

			// Counter
			"{counter}": "[data-kt-counter]",

			// Subscriptions
			"{subscribe}": "[data-kt-subscribe]",
			"{unsubscribe}": "[data-kt-unsubscribe]"

		}
	}, function(self, opts) { return {

		init: function() {
			opts.component = self.element.data('component');
			opts.cid = self.element.data('cid');
			opts.currentUrl = self.element.data('url');
			opts.live = {
				"enabled": self.element.data('live') == 1 ? true : false,
				"interval": parseInt(self.element.data('live-interval'))
			};

			if (opts.live.enabled) {
				opts.timer = opts.live.interval * 1000;
				self.monitorNewComments();
			}

			// Initialize the list
			if (opts.initList) {
				var list = $('[data-kt-comments]');

				list.implement(Komento.Controller.Comments.List, {
					showRatings: opts.ratings,
					prism: opts.prism,
					cleanGist: true
				});
			}

			// lets see if we have any fragments to process or not.
			if (window.location.hash) {
				var hash = window.location.hash.substring(1);
				self.processFragments(hash);
			}

			// Implement social sharing
		},

		processFragments: function(hash) {
			var fragments = hash.split('=');

			if (fragments[0] == '!kmt-start') {
				// okay we are doing pagnation load

				var currentLimit = fragments[1];

				self.getComments(currentLimit);

			} else if (fragments[0].indexOf('comment-') >= 0) {
				// okay we are doing comment permalink

				// lets base64 decode
				var data = fragments[0].split('comment-');

				// data = atob(data[1]);

				// below is debug code.
				data = data[1];

				var commentFragments = data.split(',');

				// get comment id
				var commentId = commentFragments[0];

				// get parent id
				var parentId = commentFragments[1];

				// get page start
				var currentLimit = 0;
				if (commentFragments[2] != undefined) {
					currentLimit = commentFragments[2];
				}

				// sorting
				var sorting = '';
				if (commentFragments[3] != undefined) {
					sorting = commentFragments[3];
				}

				self.getComments(currentLimit, commentId, parentId, sorting);
			}

			// nothing to process here.
			return;
		},

		// Checks for new comments
		monitorNewComments: function() {

			setTimeout(function() {
				Komento.ajax('site/views/comments/check', {
					"component": opts.component,
					"cid": opts.cid,
					"lastchecktime": opts.lastchecktime
				}).done(function(hasChanges, totalNew, html, nextchecktime) {

					// update the next cycle datetime
					opts.lastchecktime = nextchecktime;

					if (!hasChanges) {
						return;
					}

					// Remove any previously added notification
					$('[data-kt-notifications]').remove();

					// Append the new notification output
					$('body').append(html);

					// Bind the event on the notification object
					$('body').one('click.notifications', '[data-kt-notifications]', function() {
						var wrapper = $(this);

						wrapper.remove();

						// Update the comments list
						window.location.reload();
					});

					// Bind the close button
					$('body').one('click.notifications.close', '[data-kt-notifications] [data-kt-notifications-close]', function(event) {
						event.stopPropagation();
						event.preventDefault();

						var wrapper = $(this).parents('[data-kt-notifications]');

						// Remove the element
						wrapper.remove();
					});
				})
				.always(function() {
					self.monitorNewComments();
				})

			}, opts.timer);
		},

		getCurrentUrl: function() {
			return opts.currentUrl;
		},

		getTotal: function() {
			var total = parseInt(self.counter().text());

			return total;
		},

		setCounter: function(count) {
			self.counter().text(count);
		},

		increaseCounter: function(count) {

			if (count === undefined) {
				count = 1;
			}

			var total = self.getTotal() + count;
			self.setCounter(total);
		},

		decreaseCounter: function(count) {

			if (count === undefined) {
				count = 1;
			}

			var total = self.getTotal() - count;
			self.setCounter(total);
		},

		getForm: function() {
			return self.form().controller();
		},


		getComments: function(start, commentId, parentId, sort) {

			var overrideSorting = false;

			if (sort == undefined || sort == '') {
				var sort = Komento.sort;
			} else {
				Komento.sort = sort;
				overrideSorting = true;
			}

			var task = $.Deferred();

			if (start > 0 || overrideSorting) {

				// disable the loadmore so that user can no longer click.
				self.loadMore().addClass('disabled');

				Komento.ajax('site/views/comments/loadComments', {
					component: Komento.component,
					cid: Komento.cid,
					endlimit: start,
					sort: sort,
					contentLink: Komento.contentLink,
				})
				.done(function(html, nextstart) {

					// remove the comment items.
					// TODO: we should just load the comment that is not being loaded yet.
					self.comments().find("[data-kt-comment-item]").remove();

					// now insert the comemtns before the 'empty div'.
					if (self.comments().find("[data-kt-comment-item]").length > 0) {
						$(html).insertBefore(self.comments().find("[data-kt-comment-item]").last());
					} else {
						$(html).appendTo(self.comments());
					}


					var listController = self.comments().controller();

					listController.initRatings($(html).find(listController.ratings.selector));

					// Reload syntax highlighter
					if (opts.prism) {
						Prism.highlightAll();
					}

					// Try to find for gist embeds
					listController.initGist(self.comments());

					if(nextstart != '-1') {
						self.loadMore().show();
						self.loadMore().removeClass('disabled');

						// update loadmore bar
						var nextStartCount;
						self.loadMore().attr('href', '#!kmt-start=' + nextstart);
						self.loadMore().data('nextstart', nextstart);

					} else {
						// this could be the last page. hide loadmore.
						self.loadMore().hide();
					}

					if (overrideSorting) {
						self.sorting()
							.removeClass('is-active');

						$('[data-kt-sorting][data-type="' + sort +  '"]').addClass('is-active');
					}


					task.resolve(commentId, parentId);

				})
				.fail(function() {
					task.resolve(commentId, parentId);
				});

			} else {
				task.resolve(commentId, parentId);
			}

			task.done(function(commentId, parentId) {

				if (commentId != undefined && commentId) {

					// lets check if the comment is there or not.
					if ($('#comment-' + commentId).length > 0) {
						// TODO: scroll the page down to the anchor
						$(document).scrollTo('#comment-' + commentId);
					} else {

						if (parentId != undefined && parentId) {
							// okay we need to further retrieve the replies as the reply might not loaded yet.
							var button = $('[data-kt-view-reply][data-id="' + parentId + '"]');

							if (button.length > 0) {

								var parentId = button.data('id');
								var rownumber = button.data('rownumber');

								var listController = self.comments().controller();

								if (listController !== undefined) {

									listController.loadReplies(parentId, rownumber)
									.done(function(html){

										$(html).insertAfter(button);

										var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

										if (hasRatings) {
											listController.initRatings($(html).find(listController.ratings.selector));
										}

										// Reload syntax highlighter
										if (opts.prism) {
											Prism.highlightAll();
										}

										// Try to find for gist embeds
										listController.initGist(self.comments());

										// hide the button.
										button.find('a').removeClass('is-loading');
										button.find('a > span').removeClass('t-hidden');
										button.addClass('t-hidden');

										$(document).scrollTo('#comment-' + commentId);
									});
								}
							}
						}
					}
				}
			});


		},

		'{loadMore} click': function(el, event) {

	        // event.preventDefault();
			self.loadMore()
				.addClass('is-loading')
				.attr('disabled', true);

			var startCount;

			startCount = el.data('nextstart');

			Komento.ajax('site/views/comments/loadmore', {
				component: Komento.component,
				cid: Komento.cid,
				start: startCount,
				sort: Komento.sort,
				contentLink: Komento.contentLink,
			})
			.done(function(html, nextstart) {

				var listController = self.comments().controller();


				$(html).insertAfter(self.comments().find("[data-kt-comment-item]").last());

				var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

				if (hasRatings) {
					listController.initRatings($(html).find(self.ratings.selector));
				}

				// Reload syntax highlighter
				if (opts.prism) {
					Prism.highlightAll();
				}

				// Try to find for gist embeds
				listController.initGist(self.comments());

				if (nextstart != '-1') {

					self.loadMore()
						.removeClass('is-loading')
						.removeAttr('disabled');


					var nextStartCount;

					self.loadMore().attr('href', '#!kmt-start=' + nextstart);
					self.loadMore().data('nextstart', nextstart);

				} else {
					self.loadMore().hide();
				}

				// callback && callback();
			})
			.fail(function(limit, limitstart, sort) {

			});

		},


		"{sorting} click": function(link, event) {
			var type = link.data('type');

			self.sorting()
				.removeClass('is-active');

			link.addClass('is-active');

			self.getComments(0, 0, 0, type);
		},

		"{subscribe} click": function(button, event) {
			Komento.dialog({
				"content": Komento.ajax('site/views/subscriptions/subscribe', {"component": opts.component, "cid": opts.cid, "currentUrl": opts.currentUrl}),
				"bindings": {
					"{submit} click": function() {

						// Check if the name is empty
						var name = this.name().val();

						if (name == '') {
							this.name().parents('.o-form-group').addClass('has-error');
							return false;
						}

						// Check if the email is empty
						var email = this.email().val();

						if (email == '') {
							this.email().parents('.o-form-group').addClass('has-error');
							return false;
						}

						this.form().submit();
					}
				}
			});
		},

		"{unsubscribe} click": function(button, event) {
			Komento.dialog({
				"content": Komento.ajax('site/views/subscriptions/confirmUnsubscribe', {"component": opts.component, "cid": opts.cid, "currentUrl": opts.currentUrl}),
				"bindings": {
					"{submit} click": function() {
						this.form().submit();
					}
				}
			});
		}

	}});

	module.resolve();
	});

});
