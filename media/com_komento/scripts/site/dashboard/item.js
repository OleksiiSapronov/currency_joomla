Komento.module('site/dashboard/item', function($) {
	
var module = this;

// .view('dialogs/delete.affectchild', 'comment/edit.form')
// view: {
// 	editForm: 'comment/edit.form',
// 	affectChild: 'dialogs/delete.affectchild'
// }
Komento.Controller('Dashboard.Item', {
	defaults: {
		commentId: 0,
	}
}, function(self) { return {
	
	unpublishComment: function() {
		var commentId = self.options.commentId;
		var id = commentId.split('-')[1];

		Komento.ajax('site/views/comments/unpublish', {
			"id": id
		}).done(function() {
			self.closeDialog();

			self.unpublishChild(self.element.attr('id'));

			self.statusButton().text($.language('COM_KOMENTO_UNPUBLISHED'));
			self.publishButton().show();
			self.unpublishButton().hide();
			self.statusOptions().hide();
		});
	},

	unpublishChild: function(id) {
		var text = $.language('COM_KOMENTO_UNPUBLISHED');
		$('li[parentid="' + id + '"]').each(function() {
			$(this).find('.kmt-status').text(text);
			$(this).find('.kmt-unpublish').hide();
			$(this).find('.kmt-publish').show();
			self.unpublishChild($(this).attr('id'));
		})
	}
}});

module.resolve();

});
