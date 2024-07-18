/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
const jchPlatform = (
    function () {

        let jch_ajax_url_optimizeimages = 'index.php?option=com_jchoptimize&view=OptimizeImage&task=optimizeimage';
        let jch_ajax_url_multiselect = 'index.php?option=com_jchoptimize&view=Ajax&task=multiselect';
        let jch_ajax_url_smartcombine = 'index.php?option=com_jchoptimize&view=Ajax&task=smartcombine';
        let jch_loader_image_url = '../media/com_jchoptimize/core/images/loader.gif';

        const setting_prefix = 'jform';

        /**
         *
         * @param int
         * @param id
         */
        const applyAutoSettings = function (int, id) {
            const auto_settings = document.querySelectorAll("figure.icon.auto-setting");
            const wrappers = document.querySelectorAll("figure.icon.auto-setting span.toggle-wrapper");
            let image = document.createElement("img");
            image.src = jch_loader_image_url;

            for (const wrapper of wrappers) {
                wrapper.replaceChild(image.cloneNode(true), wrapper.firstChild);
            }

            let url = "index.php?option=com_jchoptimize&view=ApplyAutoSetting&autosetting=s" + int;

            postData(url)
                .then(data => {
                    for (const auto_setting of auto_settings) {
                        auto_setting.className = "icon auto-setting disabled";
                    }

                    //if the response returned without error then the setting is applied
                    if (data.success) {
                        const current_setting = document.getElementById(id);
                        current_setting.className = "icon auto-setting enabled";
                        const enable_combine = document.getElementById("combine-files-enable")
                        enable_combine.className = "icon enabled";
                    }

                    for (const wrapper of wrappers) {
                        let toggle = document.createElement("i");
                        toggle.className = "toggle fa";
                        wrapper.replaceChild(toggle, wrapper.firstChild);
                    }
                })
                .catch(err => console.log(err));
        };

        /**
         *
         * @param setting
         * @param id
         */
        const toggleSetting = function (setting, id) {
            let figure = document.getElementById(id);
            let wrapper = document.querySelector("#" + id + " span.toggle-wrapper");
            let toggle = wrapper.firstChild;
            const image = document.createElement("img");
            image.src = jch_loader_image_url;
            wrapper.replaceChild(image, toggle);

            if (setting === 'combine_files_enable') {
                const auto_settings = document.querySelectorAll("figure.icon.auto-setting");
                for (const auto_setting of auto_settings) {
                    auto_setting.className = "icon auto-setting disabled";
                }
            }

            let url = "index.php?option=com_jchoptimize&view=ToggleSetting&setting=" + setting;

            postData(url)
                .then(data => {
                    figure.classList.remove("enabled", "disabled");
                    figure.classList.add(data.class);

                    if (id === 'optimize-css-delivery') {
                        let unused_css = document.getElementById("reduce-unused-css");
                        unused_css.classList.remove("enabled", "disabled");
                        unused_css.classList.add(data.class2);
                    }

                    if (id === 'reduce-unused-css') {
                        let optimize_css = document.getElementById("optimize-css-delivery");
                        optimize_css.classList.remove("enabled", 'disabled');
                        optimize_css.classList.add(data.class2);
                    }

                    if (setting === 'combine_files_enable') {
                        if (data.auto !== false) {
                            enabled_auto_setting = document.getElementById(data.auto);
                            enabled_auto_setting.classList.remove("disabled");
                            enabled_auto_setting.classList.add("enabled");
                        }
                    }

                    if (setting === 'integrated_page_cache_enable') {
                        let mode_switcher_indicator = document.getElementById("mode-switcher-indicator");
                        if (mode_switcher_indicator !== null) {
                            mode_switcher_indicator.classList.remove(
                                "production",
                                "development",
                                "page-cache-only",
                                "page-cache-disabled"
                            );
                            mode_switcher_indicator.classList.add(data.status_class);
                        }

                        let page_cache_status = document.getElementById("page-cache-status");
                        if (page_cache_status !== null) {
                            page_cache_status.innerHTML = data.page_cache_status;
                        }
                    }
                    wrapper.replaceChild(toggle, image);
                })
                .catch(err => console.log(err));
        };

        const getCacheInfo = function () {
            let url = 'index.php?option=com_jchoptimize&view=CacheInfo';

            postData(url)
                .then(data => {
                    let numFiles = document.querySelectorAll('.numFiles-container');
                    let fileSize = document.querySelectorAll('.fileSize-container');

                    numFiles.forEach((container) => {
                        container.innerHTML = data.numFiles;
                    });

                    fileSize.forEach((container) => {
                        container.innerHTML = data.size;
                    });
                })
        };

        const loadBulkSettingsModal = function () {
            let modalLoaded = true;

            try {
                const modal = new bootstrap.Modal('#bulk-settings-modal-container', {
                    backdrop: 'static',
                    keyboard: false
                })
                modal.show();
            } catch (e) {
                modalLoaded = false;
            }

            if (!modalLoaded) {
                //Try with jQuery for joomla3
                jQuery('#bulk-settings-modal-container').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            }
        };

        const submitForm = function () {
            Joomla.submitbutton('config.save.component.apply');
        };

        async function postData(url) {
            const response = await fetch(url, {
                method: 'GET',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                redirect: 'follow',
                referrerPolicy: 'no-referrer',
            });

            return response.json();
        }

        return {
            jch_ajax_url_multiselect: jch_ajax_url_multiselect,
            jch_ajax_url_optimizeimages: jch_ajax_url_optimizeimages,
            jch_ajax_url_smartcombine: jch_ajax_url_smartcombine,
            setting_prefix: setting_prefix,
            applyAutoSettings: applyAutoSettings,
            toggleSetting: toggleSetting,
            submitForm: submitForm,
            getCacheInfo: getCacheInfo,
            loadBulkSettingsModal: loadBulkSettingsModal
        }
    }
)();
