/**
 * @package         Regular Labs Extension Manager
 * @version         8.5.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

(function($) {
    $(document).ready(function() {
        RegularLabsManagerProcess.resizeModal();
    });
    $(window.parent).resize(function() {
        RegularLabsManagerProcess.resizeModal();
    });

    if (typeof RegularLabsManagerProcess !== 'undefined') {
        return;
    }

    RegularLabsManagerProcess = {
        params             : Joomla.getOptions('rl_extensionmanager'),
        ids                : Joomla.getOptions('rl_extensionmanager').ids,
        failed_ids         : [],
        task               : 'install',
        messages           : {'error': [], 'warning': [], 'info': []},
        is_extensionmanager: false,

        process: function(task, retry) {
            this.hide('title');
            this.show('processing', $('.titles'));

            if (retry) {
                this.processNextStep(0);
                return;
            }

            this.task = task;

            var sb = window.parent.SqueezeBox;
            sb && sb.overlay['removeEvent']('click', sb.bound.close);

            if (this.ids[0] == 'extensionmanager') {
                this.is_extensionmanager = true;
            }

            sb && sb.setOptions({
                onClose: function() {
                    window.parent.location.href = window.parent.location;
                }
            });

            this.processNextStep(0);
        },

        processNextStep: function(step) {
            var id = this.ids[step];

            if (id) {
                this.install(step);
                this.resizeModal();

                return;
            }

            var sb = window.parent.SqueezeBox;
            this.hide('title');

            this.showMessages('error');
            this.showMessages('warning');
            this.showMessages('info');

            if (this.failed_ids.length) {
                this.show('failed', $('.titles'));
                this.ids        = this.failed_ids;
                this.failed_ids = [];
            } else {
                this.hide('processlist');
                this.show('done', $('.titles'));
                if ( ! this.is_extensionmanager && window.parent.RegularLabsManager) {
                    window.parent.RegularLabsManager.refreshData(1);
                    sb && sb.removeEvents();
                }
            }

            sb && sb.overlay['addEvent']('click', sb.bound.close);

            // Make all divs and pre's full height to get correct new modal sizes
            $('#rlem divx,#rlem pre').each(function() {
                if ($(this).css('max-height') != 'none') {
                    $(this).prop('data-max-height', $(this).css('max-height'));
                    $(this).css('max-height', 'none');
                }
            });

            this.resizeModal();

            $('#rlem divx,#rlem pre').each(function() {
                if ($(this).prop('data-max-height')) {
                    $(this).css('max-height', $(this).prop('data-max-height'));
                }
            });

            this.resizeModal();
        },

        install: function(step, retry_once) {
            var id = this.ids[step];

            this.hide('status', $('tr#row_' + id));
            this.show('processing_' + id);

            var url = this.getInstallUrl(id);

            RegularLabsScripts.loadajax(url,
                'RegularLabsManagerProcess.processResult( data, ' + step + ', ' + retry_once + ' )',
                'RegularLabsManagerProcess.processResult( data, ' + step + ', ' + retry_once + ' )',
                this.params.token + '=1'
            );
        },

        getInstallUrl: function(id) {
            var url = 'index.php?option=com_regularlabsmanager&view=process&tmpl=component&id=' + id;

            if (this.task == 'uninstall') {
                return url + '&action=uninstall';
            }

            var ext_url = $('#url_' + id).val() + '&action=' + this.task + '&host=' + window.location.hostname;

            if (ext_url.indexOf('&dev=1') > 0) {
                var d = new Date();
                ext_url += '&' + d.getTime();
            }

            return url + '&action=install&url=' + encodeURIComponent(ext_url);

        },

        processResult: function(data, step, retry_once) {
            data = ! data || typeof data !== 'string' ? '' : data.trim();

            if ( ! data && ! retry_once) {
                this.processNextStep(step, 1);

                return;
            }

            var id = this.ids[step];

            this.hide('status', $('tr#row_' + id));

            var messages = this.getMessages(data);

            if ( ! messages.success.length) {
                this.failed_ids.push(id);
                this.enqueueMessages('error', id, messages);
                this.show('failed_' + id);
                this.processNextStep(++step);

                return;
            }

            this.enqueueMessages('warning', id, messages);
            this.enqueueMessages('info', id, messages);

            this.show('success_' + id);
            this.processNextStep(++step);
        },

        show: function(classes, parent) {
            if ( ! parent) {
                parent = $('div#rlem');
            } else {
                parent.addClass(classes.replace(',', ''));
            }

            classes = '.' + classes.replace(', ', ', .');
            parent.find(classes).removeClass('hide');
        },

        hide: function(classes, parent) {
            if ( ! parent) {
                parent = $('div#rlem');
            } else {
                parent.removeClass(classes.replace(',', ''));
            }

            classes = '.' + classes.replace(', ', ', .');
            parent.find(classes).addClass('hide');
        },

        showMessages: function(type) {

            if ( ! this.messages[type].length) {
                return;
            }

            $('#process-' + type + ' > div.alert-message').html(this.messages[type].join('</div><div class="alert-message">'));
            $('#process-' + type).show();

            this.messages[type] = [];
        },

        getMessages: function(data) {
            var messages = {'error': [], 'success': [], 'warning': [], 'info': []};

            if (data == '1') {
                messages.success.push('succes');
                return messages;
            }

            var html = $.parseHTML(data);

            var container = '';

            // Gather the parsed HTML's node names
            $.each(html, function(i, el) {
                if (el.id != 'system-message-container') {
                    return true;
                }

                container = $(el);

                return false;
            });

            if ( ! container.length) {
                return messages;
            }

            messages.error   = container.find('.alert-error .alert-message').toArray();
            messages.success = container.find('.alert-success .alert-message').toArray();
            messages.info    = container.find('.alert-info .alert-message').toArray();
            // warning messages don't have a specific alert-warning class in J3
            messages.warning = container.find('.alert:not(.alert-error):not(.alert-success):not(.alert-info) .alert-message').toArray();

            return messages;
        },

        enqueueMessages: function(type, id, messages) {

            var alerts         = [];
            var extension_name = $('#ext_name_' + id).html();
            var title          = type == 'error' ? '<strong>' + extension_name + '</strong><br>' : '';

            $.each(messages[type], function(i, el) {
                var message = typeof el == 'string' ? el : $(el).html();

                if (message.indexOf('JFolder: :delete') > -1) {
                    return true;
                }

                // Correct some old messages not containing the extension name
                message = message.replace('<h3>Latest changes since v', '<h3>Latest changes since ' + extension_name + ' v');
                message = message.replace('<h3>Latest changes for :', '<h3>Latest changes for ' + extension_name + ':');
                message = message.replace('<h3>Latest changes:', '<h3>Latest changes for ' + extension_name + ':');

                alerts.push(message);
            });

            if ( ! alerts.length) {
                if (type == 'error') {
                    this.messages[type].push(title + Joomla.JText._('RLEM_INSTALLATION_FAILED'));
                }

                return;
            }

            this.messages[type].push(title + alerts.join('<br>'));
        },

        resizeModal: function() {
            if ( ! window.parent.SqueezeBox) {
                return;
            }

            var iframe  = $('.sbox-content-iframe > iframe', window.parent.document);
            var content = $('#rlem');

            var orig_outer_height = iframe.height();
            var max_outer_height  = $(window.parent).height() - 100;

            var orig_inner_width  = content.width();
            var orig_inner_height = content.height();

            content.width(800).height('auto');

            // set back width to original if new height is not smaller because of larger width
            if (content.height() >= orig_inner_height) {
                // for some reason a scroll bar shows if we don't set the width back minus a few pixels :S
                content.width(orig_inner_width - 5);
            }

            var padding_width    = 26;
            var padding_height   = 38;
            var new_outer_width  = content.width() + padding_width;
            var new_outer_height = content.height() + padding_height;

            if (new_outer_height < orig_outer_height && new_outer_height > orig_outer_height - 20) {
                new_outer_height = orig_outer_height;
            }
            if (new_outer_height > max_outer_height) {
                new_outer_height = max_outer_height;
            }

            if (new_outer_height == orig_outer_height) {
                return;
            }

            window.parent.SqueezeBox.resize({x: new_outer_width, y: new_outer_height});

            $('.sbox-content-iframe > iframe', window.parent.document).width(new_outer_width).height(new_outer_height);
        }
    };
})(jQuery);
