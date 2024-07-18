RSFirewall.Status = {
    errorContainer: '',

    Error: function (display, type, error) {
        this.errorContainer.empty();
        if (display && typeof error != 'undefined') {
            this.errorContainer.addClass('com-rsfirewall-log-' + type);
            var heading = jQuery('<h3>').append(Joomla.JText._('COM_RSFIREWALL_LOG_' + type.toUpperCase()));
            this.errorContainer.append(heading);
            this.errorContainer.append(error);
            this.errorContainer.show();
        } else {
            this.errorContainer.removeClass('com-rsfirewall-log-error', 'com-rsfirewall-log-warning');
            this.errorContainer.hide();
        }
    },

    Change: function (id, listId, type, element) {
        var data = {
            task: 'logs.' + type,
            id  : id
        };
        if (listId != null) {
            data.listId = listId;
        }

        jQuery.ajax({
            converters: {
                "text json": RSFirewall.parseJSON
            },
            dataType  : 'json',
            type      : 'POST',
            url       : 'index.php?option=com_rsfirewall&task',
            data      : data,
            beforeSend: function () {
                RSFirewall.addLoading(element, 'after');
                jQuery(element).hide();

                // remove previous errors
                RSFirewall.Status.Error(false);
            },
            error     : function (jqXHR, textStatus, errorThrown) {
                RSFirewall.removeLoading();
                jQuery(element).show();

                // set the error
                RSFirewall.Status.Error(true, 'error', jqXHR.status + ' ' + errorThrown);
            },
            success   : function (json) {
                RSFirewall.removeLoading();
                if (json.success) {
                    if (json.data.result) {
                        // all ok
                        if (json.data.type) {
                            // change the buttons for all other rows that contains the same ip
                            RSFirewall.Status.ChangeSameIp(id, null, 0);
                        } else {
                            // change the buttons for all other rows that contains the same ip
                            RSFirewall.Status.ChangeSameIp(id, json.data.listId, 1);
                        }
                        jQuery(element).remove();
                    } else {
                        // errors
                        jQuery(element).show();

                        // set the warning
                        RSFirewall.Status.Error(true, 'warning', json.data.error);
                    }
                }
            }
        });
    },

    MakeButton: function (element, id, listId, type) {
        var button, task, text,
            classes = ['btn'];

        if (type)
        {
            task = 'unblockajax';
            classes.push('btn-secondary');
            text = Joomla.JText._('COM_RSFIREWALL_UNBLOCK');
        }
        else
        {
            task = 'blockajax';
            classes.push('btn-danger');
            text = Joomla.JText._('COM_RSFIREWALL_BLOCK');

            listId = null;
        }

        button = document.createElement('button');
        button.setAttribute('type', 'button');
        button.setAttribute('class', classes.join(' '));
        button.onclick = function() {
            RSFirewall.Status.Change(id, listId, task, this);
        };
        button.innerText = text;

        jQuery(element).after(button);
    },

    ChangeSameIp: function (id, listId, type) {
        // get the ip address that we need to change the button
        var ip = jQuery('#rsf-log-' + id).find('.rsf-ip-address').html().trim();

        // parse the table to find the same ip entries
        jQuery('.rsf-entry').each(function () {
            var ipFound = jQuery(this).find('.rsf-ip-address').html().trim();
            if (ipFound.length > 0 && ipFound === ip) {
                var element = jQuery(this).find('.rsf-status > button');

                RSFirewall.Status.MakeButton(element, id, listId, type);
                jQuery(element).remove();
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    var buttons, i;

    buttons = document.querySelectorAll('.com-rsfirewall-show-log');
    for (i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function(){
            jQuery(this).parent().find('.com-rsfirewall-hidden').removeClass('com-rsfirewall-hidden');
            jQuery(this).remove();
        });
    }

    buttons = document.querySelectorAll('.com-rsfirewall-change-status');
    for (i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function(){
            var id = this.getAttribute('data-id');
            var listid = this.getAttribute('data-listid');
            var task = listid ? 'unblockajax' : 'blockajax';

            RSFirewall.Status.Change(id, listid, task, this);
        });
    }
});