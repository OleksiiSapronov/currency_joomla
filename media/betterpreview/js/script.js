/**
 * @package         Better Preview
 * @version         6.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

(function($) {
    "use strict";

    $(document).ready(function() {
        $('.betterpreview-dropdown .dropdown-toggle').hover(function() {
            var el   = $(this).parent();
            var menu = el.find('.dropdown-menu');

            menu.stop(true, true).show();
            el.addClass('open');

            var hide = function() {
                menu.stop(true, true).hide();
                el.removeClass('open');
            };

            $('html').click(function() {
                hide();
            });
            menu.hover(function() {
            }, function() {
                hide();
            });
            $('#menu').hover(function() {
                hide();
            });
        });

    });
})(jQuery);

