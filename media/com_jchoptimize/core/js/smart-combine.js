
/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

const jchSmartCombine = (function ($) {
    $(document).ready(function () {
        const smart_combine_button = $('button#btn-pro_smart_combine')

        if ($('.jch-smart-combine-radios-wrapper input:radio:checked').val() === '1') {
            smart_combine_button.css('display', 'inline')
        }

        $('.jch-smart-combine-radios-wrapper label[for$="1"]').click(function () {
            processSmartCombine()
        })

        $('.jch-smart-combine-radios-wrapper label[for$="0"]').click(function () {
            $('.jch-smart-combine-values').val('')
        })

        smart_combine_button.click(function () {
            reprocessSmartCombine()
        })
    })

    function processSmartCombine(reprocess = false) {
        $('img#img-pro_smart_combine').css('display', 'inline')
        $('button#btn-pro_smart_combine').css('display', 'none')

        let xhr = $.ajax({
            dataType: 'json',
            url: jchPlatform.jch_ajax_url_smartcombine + '&_=' + getTimeStamp(),
            method: 'POST',
            success: function (response) {
                const smart_combine_values = $('.jch-smart-combine-values')

                smart_combine_values.val('')

                const values = [];

                $.each(response.data.css, function (index, value) {
                    values.push(value)
                })

                $.each(response.data.js, function (index, value) {
                    values.push(value)
                })

                let value_text = encodeURIComponent(JSON.stringify(values))
                smart_combine_values.val(value_text)

                $('img#img-pro_smart_combine').css('display', 'none')
                $('button#btn-pro_smart_combine').css('display', 'inline')
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error returned from ajax function \'getmultiselect\'')
                console.error('textStatus: ' + textStatus)
                console.error('errorThrown: ' + errorThrown)
                console.warn('response: ' + jqXHR.responseText)
            },
            complete: function () {
                if (reprocess) {
                    jchPlatform.submitForm()
                }
            }
        })
    }

    function reprocessSmartCombine() {
        processSmartCombine(true)
    }

    return {
        processSmartCombine: processSmartCombine,
        reprocessSmartCombine: reprocessSmartCombine
    }
})(jQuery);
