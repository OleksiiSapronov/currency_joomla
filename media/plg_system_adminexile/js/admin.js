(function ($) {
    $(document).ready(function () {
        var AdminExile = function (options) {
            var root = this;

            var vars = {
                invalidChars: {
                    32: 'SPACE', // Space
                    34: 'QUOTE', // "
                    35: 'POUND', // #
                    36: 'DOLLAR', // $
                    37: 'PERCENT', // %
                    38: 'AMPERSAND', // &
                    43: 'PLUS', // +
                    44: 'COMMA', // ,
                    46: 'PERIOD', // .
                    47: 'FORWARDSLASH', // /
                    58: 'COLON', // :
                    59: 'SEMICOLON', // ;
                    60: 'LESSTHAN', // <
                    61: 'EQUALS', // =
                    62: 'GREATERTHAN', // >
                    63: 'QUESTION', // ?
                    64: 'AT', // @
                    91: 'LEFTBRACKET', // [
                    92: 'BACKSLASH', // \
                    93: 'RIGHTBRACKET', // ]
                    94: 'CARAT', // ^
                    96: 'GRAVE', // `
                    123: 'LEFTCURLY', // {
                    125: 'RIGHTCURLY', // }
                    124: 'PIPE', // |
                    126: 'TILDE'        // ~
                }
            };

            this.construct = function (options) {
                $.extend(vars, options);
                Joomla.JText.load();

                $.each(['#jform_params_key', '#jform_params_keyvalue'], function (i, item) {
                    $(item).keyup(function () {
                        testInput(item, $(item).val());
                    });
                });
                $('#jform_params_twofactor0').click(function() { displayURL(); });
                $('#jform_params_twofactor1').click(function() { displayURL(); });
                displayURL();
                
                
            };

            var testInput = function (type, str) {
                var self = this;
                if (type === '#jform_params_key' && (/^[0-9]+$/.test(str))) {
                    $(type).val('');
                    alert(Joomla.JText._('PLG_SYS_ADMINEXILE_MESSAGE_NOTNUMERIC'));
                    return;
                }
                if (!(/^[\040-\177]*$/.test(str))) {
                    while (!(/^[\040-\177]*$/.test(str)))
                        for (i = 0; i <= (str.length - 1); i++)
                            if (!(/^[\040-\177]*$/.test(str.charAt(i))))
                                $(type).val(str.replace(str.charAt(i), ''));
                    alert(Joomla.JText._('PLG_SYS_ADMINEXILE_MESSAGE_INVALIDASCII'));
                    return;
                }
                for (i = 0; i <= (str.length - 1); i++) {
                    if (vars.invalidChars.hasOwnProperty(str.charCodeAt(i))) {
                        $(type).val(str.replace(str.charAt(i), ''));
                        alert(Joomla.JText._('PLG_SYS_ADMINEXILE_MESSAGE_INVALIDCHAR') + "\n\n" + validCharsMesssage());
                        return;
                    }
                }
                displayURL();
            };

            var validCharsMesssage = function () {
                var str = [], name, char;
                $.each(vars.invalidChars, function (key,value) {
                    char = String.fromCharCode(key);
                    name = Joomla.JText._('PLG_SYS_ADMINEXILE_CHAR_' + value)
                    str.push(char + '\t\t:\t\t' + name);
                });
                str = str.join('\n');
                return str;
            };

            var displayURL = function () {
                var adminurl = vars.uri.replace(/\/$/,'')+'/administrator';
                if ($('#jform_params_twofactor0')[0].checked) {
                    adminurl+=("?"+$('#jform_params_key').val());
                } else {
                    var data = {};
                    data[$('#jform_params_key').val()] = $('#jform_params_keyvalue').val();
                    adminurl+=('?'+$.param(data));
                }
                var target = $('#jform_params_url-lbl').parent('span').next('span')[0];
                $(target).empty();
                $(target).append($('<a href="'+adminurl+'">'+adminurl+'</a>'));
            };
            
            
            
            
            this.construct(options);
        };
        
        var validIPv46 = function(ip) {
            var regex;
            regex = new RegExp('(^\s*((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(/(3[012]|[12]?[0-9]))?)\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$)','i');
            return regex.test(ip);
        };
        
        window.plg_sys_adminexile = new AdminExile(window.plg_sys_adminexile_config);
        
        
    });
})(jQuery);
