
/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

const jchIOptimizeApi = (function ($) {
    'use strict'

//Initialize timer object
    const timer = null;
//Array of file objects to optimize
    let files = [];
//Count of current file being optimized initialized to 0
    let current = 0;
//Total amount of files to be optimized
    let cnt = 0;
    let total = 0;
//Amount of files that are actually optimized
    let optimize = 0;
//AMount of files converted to webp
    let webp = 0;
//Path of expanded folder
    let dir = '';
//Path of log file
    let log_path = '';
//Object containing relevant settings saved in the plugin
    let params = {};
//set to fail if request not authenticated
    let status = 'success';
//Message if not authenticated
    let authmessage = '';

    const api_mode = 'auto';

    let intervalID = 0;

//Array of subdirectories under expanded folder in file tree
    const subdirs = [];

    const addProgressBar = function () {
        let modalLoaded = true;
        try {
            const imageModal = new bootstrap.Modal('#optimize-images-modal-container', {
                backdrop: 'static', keyboard: false
            })
            imageModal.show()
        } catch (e) {
            modalLoaded = false;
        }
        //try with jQueru
        if (!modalLoaded) {
            $('#optimize-images-modal-container').modal({
                backdrop: 'static', keyboard: false, show: true
            })
        }
        //Load progress bar with log window
        $('#optimize-images-modal-container .modal-body')
            .html('<div id="progressbar"></div> \
			 <div id="optimize-status">Gathering files to optimize. Please wait...</div> \
                         <div><ul id="optimize-log"></ul></div>')
        $('#progressbar').progressbar({value: 0})
    };

    const processFilePacks = function (page, api_mode) {
        //array to hold ajax objects
        const deferreds = [];
        //Number of ajax requests to send before waiting for Ajax completion
        const loops = 10;
        //Size of packets of files to send for optimization
        const filepacksize = 5;

        if (api_mode === 'manual') {
            for (let i = 0; i < loops && cnt < total; i++) {

                //Packets of files
                const filepack = [];

                for (let j = 0; j < filepacksize && cnt < total; cnt++, j++) {
                    filepack.push(files[cnt])
                }

                deferreds.push(processAjax(page, filepack, params, 'optimize', api_mode))
            }
        } else {
            let k = 0, l = 0;
            for (; k < loops && l < files.length; l++, k++) {
                cnt += files[l].images.length
                deferreds.push(processAjax(page, files[l], params, 'optimize', api_mode))
            }
        }

        //When number of Ajax requests in loop is queued, wait until all Ajax
        //requests are completed before looping in another queue or print
        //completion message
        $.when.apply($, deferreds).then(function () {

            processMoreFilePacks(page, api_mode)
        }, function () {
            //There was a failure in the last loop just move the current count along and continue
            current = cnt

            updateProgressBar()
            updateStatusBar()
            processMoreFilePacks(page, api_mode)
        })
    };

    /**
     * @param success_page
     * @param api_mode
     */
    const processMoreFilePacks = function (success_page, api_mode) {
        if ((api_mode === 'manual' && cnt < total) || (api_mode === 'auto' && cnt < files.length)) {
            processFilePacks(success_page, api_mode)
        } else {
            let log_container = $('ul#optimize-log')
            log_container.append('<li>Adding logs to ' + log_path + '/com_jchoptimize.logs.php...</li>')

            log_container.append('<li>Done! Reloading success_page in <span id="reload-timer">10</span> seconds...</li>')

            let reload_timer = 10;

            const intervalFunc = function () {
                $('span#reload-timer').text(--reload_timer)

                if (reload_timer === 0) {
                    window.clearInterval(intervalID)
                }
            };

            intervalID = window.setInterval(intervalFunc, 1000)

            const reload = function () {
                let dir_msg = '';

                if (dir.path !== undefined) {
                    dir_msg = '&dir=' + encodeURIComponent(dir.path)
                }

                window.location.href = success_page + dir_msg + '&status=success&cnt=' + optimize
            };

            window.setTimeout(reload, 10000)
        }
    };

    /**
     * Communicates with the website server via ajax re the files to be optimized
     *
     * @param page          string    Url of admin settings page
     * @param filepack    array     Package of files to be optimized
     * @param params      object    Array of plugin parameters obtained via javascript from settings page
     * @param task        string    Current task being completed (getfiles|optimize)
     * @param api_mode
     */
    const processAjax = function (page, filepack, params, task, api_mode) {

        //create timestamp to append to ajax call to prevent caching
        const timestamp = getTimeStamp();

        //need to return the jqXHR object to be used as deferreds
        return $.ajax({
            dataType: 'json', url: jchPlatform.jch_ajax_url_optimizeimages + '&_=' + timestamp, data: {
                'filepack': filepack,
                'subdirs': subdirs,
                'params': params,
                'optimize_task': task,
                'api_mode': api_mode
            }, timeout: 0, success: function (response) {

                //If we haven't started optimizing files then get the
                //total amount to be optimized
                if (task === 'getfiles') {
                    //Add the selected files in expanded directory
                    //to the files in selected subdirectories recursively

                    if (api_mode === 'manual') {
                        //convert the data object to an array of objects
                        const dataArray = Object.keys(response.data.files).map(i => response.data.files[i]);
                        files = $.merge(files, dataArray)
                        total = files.length
                    } else {
                        files = response.data.files
                        const images = files.map(function (value, index) {
                            return value['images']
                        });
                        const merged_images = [].concat.apply([], images);
                        total = merged_images.length

                        response.data.messages.forEach(function (data) {
                            logMessage(data.message)
                        });
                    }

                    log_path = response.data.log_path
                } else {
                    if (!response.success) {
                        logMessage(response.message)

                        //If authentication or file upload error, abort with
                        //error message
                        if (response.code === 403 || response.code === 499) {
                            status = 'fail'
                            authmessage = response.message

                            window.location.href = page + '&status=fail&msg=' + encodeURIComponent(response.message)
                        }
                    } else {
                        response.data.forEach(function (item) {

                            //Calculate percentage of files that are currently optimized
                            current++
                            updateProgressBar()

                            if (item[0].success) {
                                //Increment number of files optimized
                                optimize++
                            }

                            if (item[1] !== undefined && item[1].success) {
                                //Increment number of files converted to webp
                                webp++
                            }

                            updateStatusBar()
                            logMessage(item[0].message)

                            if (item[1] !== undefined && item[1].message) {
                                logMessage(item[1].message)
                            }
                        })
                    }
                }
            }, error: function (jqXHR, textStatus, errorThrown) {
                logMessage(textStatus + ': ' + errorThrown)
                logMessage('Response from server:')
                logMessage(jqXHR.responseText)

                //  var html = jqXHR.responseText.replace(/\\([\s\S])|(")/g, "\\$1$2");
                // logMessage('<iframe src="about:blank" width="600" height="200" srcdoc="' + html + '"></iframe>');
            }
        })
    };

    const updateProgressBar = function () {
        const pbvalue = Math.floor((current / total) * 100);

        if (pbvalue > 0) {
            //Update progress bar with new percentage
            $('#progressbar').progressbar({
                value: pbvalue
            })
        }
    };

    const updateStatusBar = function () {
        $('div#optimize-status').html('Processed ' + current.toLocaleString() + ' / ' + total.toLocaleString() + ' files, ' + optimize.toLocaleString() + ' optimized, ' + webp.toLocaleString() + ' converted to webp format...')
    };

    const logMessage = function (message) {
        const logWindow = $('ul#optimize-log');
        //Append message to log window
        logWindow.append('<li>' + message + '</li>')
        //Scroll to bottom
        logWindow.animate({scrollTop: logWindow.prop('scrollHeight')}, 20)
    };

    const getTimeStamp = function () {
        return new Date().getTime()
    };

    const optimizeImages = function (page, api_mode) {

        if (jch_params === undefined || jch_params === null) {
            params.pro_downloadid = $('input[id$=\'pro_downloadid\']').val()
            params.hidden_api_secret = $('input[id$=\'hidden_api_secret\']').val()
            params.ignore_optimized = $('input:radio[name*=\'ignore_optimized\']:checked').val()
            params.recursive = $('input:radio[name*=\'recursive\']:checked').val()
            params.pro_api_resize_mode = $('input:radio[name*=\'pro_api_resize_mode\']:checked').val()
            params.pro_next_gen_images = $('input:radio[name*=\'pro_next_gen_images\']:checked').val()
        } else {
            params = jch_params
        }

        //Ensure Download ID is entered before proceeding
        if (params.pro_downloadid.length === 0) {
            alert(jch_noproid)
            return false
        }

        if (api_mode === 'manual') {

            //Get the root folder in the file tree
            const root = $('#file-tree-container ul.jqueryFileTree li.root > a').data('root');
            //Get the folder in the file tree that is expanded
            const li = $('#file-tree-container ul.jqueryFileTree').find('li.expanded').last();

            //At least one of the subfolder or files in Explorer View needs to be checked
            if ($('#files-container input[type=checkbox]:checked').length) {
                //Save the path of the expanded folder found in the data-url attribute of the anchor tag
                dir = {path: li.find('a').data('url')}

                //Paths of subfolders are saved in the value of each checkbox, push each checked box in subdirs
                $('#files-container li.directory input[type=checkbox]:checked').each(function () {
                    subdirs.push($(this).val())
                })

                //Iterate over each selected file in expanded directory
                $('#files-container li.file input[type=checkbox]:checked').each(function () {
                    //Create file object
                    const file = {};

                    //Save path of file stored in value of checkbox
                    file.path = root + $(this).val()

                    //Get the new width of file if entered
                    if ($(this).parent().find('input[name=width]').val().length) {
                        file.width = $(this).parent().find('input[name=width]').val()
                    }

                    //Get the new height of file if entered
                    if ($(this).parent().find('input[name=height]').val().length) {
                        file.height = $(this).parent().find('input[name=height]').val()
                    }

                    //Push file object in files array.
                    files.push(file)
                })

                addProgressBar()
            } else {
                alert(jch_message)

                return false
            }
        }

        if (api_mode === 'auto') {
            addProgressBar()
        }
        //Call function to get names of all files in selected subdirectories
        $.when(processAjax(page, {}, params, 'getfiles', api_mode)).then(function () {

            let no_files_msg = ' files found.';

            if (total > 0) {
                no_files_msg += ' Uploading files for optimization...'
            }

            $('div#optimize-status').html(total.toLocaleString() + no_files_msg)

            //call function to optimize files in array
            processFilePacks(page, api_mode)
        })
    };

    return {
        optimizeImages: optimizeImages
    }
}(jQuery));
