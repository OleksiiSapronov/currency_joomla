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
        $('div.betterpreview_message, div.betterpreview_error').click(function(e) {
            $(this).fadeOut();
            e.stopPropagation();
        });
        $('html').click(function() {
            $('div.betterpreview_message, div.betterpreview_error').fadeOut();
        });
    });
})(jQuery);
