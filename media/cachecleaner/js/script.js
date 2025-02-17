/**
 * @package         Cache Cleaner
 * @version         8.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

var cachecleaner_delay = false;
var cleanCache         = null;

(function($) {
    "use strict";

    $(document).ready(function() {
        $('a.cachecleaner_link').each(function(i, el) {
            $(el).click(function() {
                cachecleaner_load();
                return false;
            });
        });

        $('<span/>', {
            id   : 'cachecleaner_msg',
            css  : {'opacity': 0},
            click: function() {
                cachecleaner_show_end();
            }
        }).appendTo('body');

        cachecleaner_delay = false;
    });

    cleanCache = function() {
        cachecleaner_load();
    };

    var cachecleaner_load = function() {
        var d       = new Date();
        var url     = cachecleaner_base + '/index.php?cleancache=1&break=1&src=button&time=' + d.toISOString();
        var timeout = 10;

        cachecleaner_show_start();
        $.ajax({
            type   : 'get',
            url    : url,
            success: function(data) {
                if (data.charAt(0) == '+') {
                    timeout = 2;
                    data    = data.substring(1, data.length);
                    $('#cachecleaner_msg').addClass('btn-success');
                } else {
                    if (data.indexOf('<html') > -1) {
                        data = cachecleaner_msg_inactive;
                    }
                    $('#cachecleaner_msg').addClass('btn-danger');
                }
                $('#cachecleaner_msg').html(data);
                cachecleaner_show_end(timeout);
            },
            error  : function(data) {
                $('#cachecleaner_msg').addClass('btn-danger').html(cachecleaner_msg_failure);
                cachecleaner_show_end(timeout);
            }
        });
    };

    var cachecleaner_show_start = function() {
        $('#cachecleaner_msg')
            .html('<img src="' + cachecleaner_root + '/media/cachecleaner/images/loading.gif" /> ' + cachecleaner_msg_clean)
            .removeClass('btn-success').removeClass('btn-warning').removeClass('btn-danger').addClass('visible');

        clearInterval(cachecleaner_delay);
        $('#cachecleaner_msg').fadeTo('fast', 0.8);
    };

    var cachecleaner_show_end = function(delay) {
        if (delay) {
            setTimeout(function() {
                cachecleaner_show_end();
            }, delay * 1000);
        } else {
            clearInterval(cachecleaner_delay);
            $('#cachecleaner_msg').fadeOut();
        }
    };
})(jQuery);
