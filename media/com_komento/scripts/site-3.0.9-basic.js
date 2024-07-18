(function(ns){
// Enqueue class
var enqueue = function(fn) {
	var queue = [], locked = 1, working = 0, fn = fn,
		instance = function(){
			queue.push([this, arguments]);
			if (!locked) instance.execute();
		};
		instance.execute = function(){
			if (working) return;
			working = 1; locked = 0;
			var q; while(q = queue.shift()) { fn.apply(q[0], q[1]) };
			working = 0;
		};
		instance.lock = function(){
			locked = 0;
		};
	return instance;
};

// Private variables
var $, options, components = {}, initialized = 0, installers = [];

var self = window[ns] = {

	setup: function(o) {
		options = o; // Keep a copy of the options
		self.init(); // Try to initialize.
	},

	jquery: function(jquery) {
		if ($) return; // If jquery is already available, stop.
		$ = jquery; // Set self.$ to jquery object
		self.init(); // Try to initialize.
	},

	init: function() {
		if (initialized) {
			return; // If initialized, stop.
		}

		if ($ && options) { // If options & jquery is available,
			self.$ = $.initialize(options); // Initialize jquery
			self.plugin.execute(); // Execute any pending plugins
			initialized = 1;
		}
	},

	plugin: enqueue(function(name, factory) {
		factory.apply(self, [$]);
	}),

	module: enqueue(function(name, factory) {
		$.module(name, factory);
	}),

	installer: function(recipient, name, factory) {
		if (!installers[recipient]) installers[recipient] = []; // Create package array if this is the first time
		if (!name) return installers[recipient];
		var component = components[recipient]; // Get component
		if (component.registered) return component.install(name, factory); // If component exist, install straight away
		installers[recipient].push([name, factory]); // Keep the package to install later
	},

	component: function(name, options) {

		// Getter
		if (!name) {
			return components; // return list of components
		}

		if (!options) {
			return components[name]; // return component
		}

		// Registering
		if (typeof options === "function") {
			var component = options;
			component.registered = true;
			return components[name] = component;
		}

		// Setter
		var queue = [];

		var abstractQueue = function(method, context, args) {
			return {method: method, context: this, args: args};
		};

		var abstractMethod = function(method, parent, chain) {
			return function(){
				(chain || queue).push(abstractQueue(method, this, arguments));
				return parent;
			};
		};

		var abstractInstance = function(instance, methods, chain) {
			var i = 0;
			for (; i < methods.length; i++) {
				var method = methods[i];
				instance[method] = abstractMethod(method, instance, chain);
			};
			return instance;
		};

		var abstractChain = function(name, methods) {
			return function(){
				var chain = [abstractQueue(name, this, arguments)];
					queue.push(chain);
				return abstractInstance({}, methods, chain);
			};
		};

		queue.execute = function(){
			var component = components[name], i = 0;
			for (; i < queue.length; i++) {
				var fn = queue[i];
				if (Object.prototype.toString.call(fn)==='[object Array]') {
					var chain = fn, context = component, j = 0;
					for (; j < chain.length; j++) {
						context = context[chain[j].method].apply(context, chain[j].args);
					}
				} else {
					component[fn.method].apply(component, fn.args)
				}
			}
		};

		// Create abstract component
		var component = abstractInstance(
				function(){component.run.apply(this.arguments)},
				["run","ready","template","dialog"]
			);

			// Set reference to options & queue
			component.className = name;
			component.options = options;
			component.queue = queue;

			// Create abstract module method
			component.module = abstractChain(
				"module",
				["done","always","fail","progress"]
			);

			// Create abstract require method
			component.require = abstractChain(
				"require",
				["library","script","stylesheet","language","template","app","view","done","always","fail","progress"]
			);

		// Register component in global namespace
		window[name] = components[name] = component;

		if (initialized) {
			$.Component.register(component);
		}

		return component;
	}
};

})("KTVendors");

// Setup foundry
KTVendors.setup({
	"environment": window.kt.environment,
	"source": "local",
	"mode": window.kt.environment == "production" ? "compressed" : "uncompressed",
	"path": window.kt.rootUrl + "/media/com_komento/scripts/vendors",
	"cdn": "",
	"extension":".js",
	"cdnPath": "",
	"rootPath": window.kt.rootUrl,
	"basePath": window.kt.rootUrl,
	"indexUrl": window.kt.rootUrl + '/index.php',
	"token": window.kt.token,
	"joomla":{
		"appendTitle": window.kt.appendTitle,
		"sitename": window.kt.siteName
	},
	"locale":{
		"lang": window.kt.locale
	}
});

KTVendors.component("Komento", {
	"environment": window.kt.environment,
	"source":"local",
	"mode": window.kt.environment == "production" ? "compressed" : "uncompressed",
	"mode": "compressed",
	"baseUrl": window.kt.baseUrl,
	"version":"3.0",
	"momentLang": window.kt.momentLang,
	"ajaxUrl": window.kt.ajaxUrl
});
jQuery.version = "4.0";
jQuery.long_version = "4.0.37";


// duplicate from media/com_easydiscuss/scripts/vendors/edjquery.js
var support = {};
(function() {
    // Minified: var b,c,d,e,f,g, h,i
    var div, style, a, pixelPositionVal, boxSizingReliableVal,
        reliableHiddenOffsetsVal, reliableMarginRightVal;

    // Setup
    div = document.createElement( "div" );
    div.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>";
    a = div.getElementsByTagName( "a" )[ 0 ];
    style = a && a.style;

    // Finish early in limited (non-browser) environments
    if ( !style ) {
        return;
    }

    style.cssText = "float:left;opacity:.5";

    // Support: IE<9
    // Make sure that element opacity exists (as opposed to filter)
    support.opacity = style.opacity === "0.5";

    // Verify style float existence
    // (IE uses styleFloat instead of cssFloat)
    support.cssFloat = !!style.cssFloat;

    div.style.backgroundClip = "content-box";
    div.cloneNode( true ).style.backgroundClip = "";
    support.clearCloneStyle = div.style.backgroundClip === "content-box";

    // Support: Firefox<29, Android 2.3
    // Vendor-prefix box-sizing
    support.boxSizing = style.boxSizing === "" || style.MozBoxSizing === "" ||
        style.WebkitBoxSizing === "";

    jQuery.extend(support, {
        reliableHiddenOffsets: function() {
            if ( reliableHiddenOffsetsVal == null ) {
                computeStyleTests();
            }
            return reliableHiddenOffsetsVal;
        },

        boxSizingReliable: function() {
            if ( boxSizingReliableVal == null ) {
                computeStyleTests();
            }
            return boxSizingReliableVal;
        },

        pixelPosition: function() {
            if ( pixelPositionVal == null ) {
                computeStyleTests();
            }
            return pixelPositionVal;
        },

        // Support: Android 2.3
        reliableMarginRight: function() {
            if ( reliableMarginRightVal == null ) {
                computeStyleTests();
            }
            return reliableMarginRightVal;
        }
    });

    function computeStyleTests() {
        // Minified: var b,c,d,j
        var div, body, container, contents;

        body = document.getElementsByTagName( "body" )[ 0 ];
        if ( !body || !body.style ) {
            // Test fired too early or in an unsupported environment, exit.
            return;
        }

        // Setup
        div = document.createElement( "div" );
        container = document.createElement( "div" );
        container.style.cssText = "position:absolute;border:0;width:0;height:0;top:0;left:-9999px";
        body.appendChild( container ).appendChild( div );

        div.style.cssText =
            // Support: Firefox<29, Android 2.3
            // Vendor-prefix box-sizing
            "-webkit-box-sizing:border-box;-moz-box-sizing:border-box;" +
            "box-sizing:border-box;display:block;margin-top:1%;top:1%;" +
            "border:1px;padding:1px;width:4px;position:absolute";

        // Support: IE<9
        // Assume reasonable values in the absence of getComputedStyle
        pixelPositionVal = boxSizingReliableVal = false;
        reliableMarginRightVal = true;

        // Check for getComputedStyle so that this code is not run in IE<9.
        if ( window.getComputedStyle ) {
            pixelPositionVal = ( window.getComputedStyle( div, null ) || {} ).top !== "1%";
            boxSizingReliableVal =
                ( window.getComputedStyle( div, null ) || { width: "4px" } ).width === "4px";

            // Support: Android 2.3
            // Div with explicit width and no margin-right incorrectly
            // gets computed margin-right based on width of container (#3333)
            // WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
            contents = div.appendChild( document.createElement( "div" ) );

            // Reset CSS: box-sizing; display; margin; border; padding
            contents.style.cssText = div.style.cssText =
                // Support: Firefox<29, Android 2.3
                // Vendor-prefix box-sizing
                "-webkit-box-sizing:content-box;-moz-box-sizing:content-box;" +
                "box-sizing:content-box;display:block;margin:0;border:0;padding:0";
            contents.style.marginRight = contents.style.width = "0";
            div.style.width = "1px";

            reliableMarginRightVal =
                !parseFloat( ( window.getComputedStyle( contents, null ) || {} ).marginRight );

            div.removeChild( contents );
        }

        // Support: IE8
        // Check if table cells still have offsetWidth/Height when they are set
        // to display:none and there are still other visible table cells in a
        // table row; if so, offsetWidth/Height are not reliable for use when
        // determining if an element has been hidden directly using
        // display:none (it is still safe to use offsets if a parent element is
        // hidden; don safety goggles and see bug #4512 for more information).
        div.innerHTML = "<table><tr><td></td><td>t</td></tr></table>";
        contents = div.getElementsByTagName( "td" );
        contents[ 0 ].style.cssText = "margin:0;border:0;padding:0;display:none";
        reliableHiddenOffsetsVal = contents[ 0 ].offsetHeight === 0;
        if ( reliableHiddenOffsetsVal ) {
            contents[ 0 ].style.display = "";
            contents[ 1 ].style.display = "none";
            reliableHiddenOffsetsVal = contents[ 0 ].offsetHeight === 0;
        }

        body.removeChild( container );
    }

})();

// duplicate variables from media/com_easydiscuss/scripts/vendors/edjquery.js
var strundefined = typeof undefined;
var rcheckableType = (/^(?:checkbox|radio)$/i);
var nodeNames = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video";
var rinlinejQuery = / jQuery\d+="(?:null|\d+)"/g;
var rnoshimcache = new RegExp("<(?:" + nodeNames + ")[\\s/>]", "i");
var rleadingWhitespace = /^\s+/;
var rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi;
var rtagName = /<([\w:]+)/;
var rtbody = /<tbody/i;
var rhtml = /<|&#?\w+;/;
var rnoInnerhtml = /<(?:script|style|link)/i;
var rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i;
var rscriptType = /^$|\/(?:java|ecma)script/i;
var rscriptTypeMasked = /^true\/(.*)/;
var rcleanScript = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;
// We have to close these tags to support XHTML (#13200)
var wrapMap = {
        option: [ 1, "<select multiple='multiple'>", "</select>" ],
        legend: [ 1, "<fieldset>", "</fieldset>" ],
        area: [ 1, "<map>", "</map>" ],
        param: [ 1, "<object>", "</object>" ],
        thead: [ 1, "<table>", "</table>" ],
        tr: [ 2, "<table><tbody>", "</tbody></table>" ],
        col: [ 2, "<table><tbody></tbody><colgroup>", "</colgroup></table>" ],
        td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],

        // IE6-8 can't serialize link, script, style, or any html5 (NoScope) tags,
        // unless wrapped in a div with non-breaking characters in front of it.
        _default: [ 0, "", "" ]
    };
var safeFragment = createSafeFragment( document );
var fragmentDiv = safeFragment.appendChild( document.createElement("div") );

wrapMap.optgroup = wrapMap.option;
wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
wrapMap.th = wrapMap.td;


// duplicate createSafeFragment from media/com_easydiscuss/scripts/vendors/edjquery.js
function createSafeFragment( document ) {
    var list = nodeNames.split( "|" ),
        safeFrag = document.createDocumentFragment();

    if ( safeFrag.createElement ) {
        while ( list.length ) {
            safeFrag.createElement(
                list.pop()
            );
        }
    }
    return safeFrag;
};

// Used in buildFragment, fixes the defaultChecked property
function fixDefaultChecked( elem ) {
    if ( rcheckableType.test( elem.type ) ) {
        elem.defaultChecked = elem.checked;
    }
}

// duplicate getAll from media/com_easydiscuss/scripts/vendors/edjquery.js
function getAll( context, tag ) {
    var elems, elem,
        i = 0,
        found = typeof context.getElementsByTagName !== strundefined ? context.getElementsByTagName( tag || "*" ) :
            typeof context.querySelectorAll !== strundefined ? context.querySelectorAll( tag || "*" ) :
            undefined;

    if ( !found ) {
        for ( found = [], elems = context.childNodes || context; (elem = elems[i]) != null; i++ ) {
            if ( !tag || jQuery.nodeName( elem, tag ) ) {
                found.push( elem );
            } else {
                jQuery.merge( found, getAll( elem, tag ) );
            }
        }
    }

    return tag === undefined || tag && jQuery.nodeName( context, tag ) ?
        jQuery.merge( [ context ], found ) :
        found;
};

// duplicate setGlobalEval from media/com_easydiscuss/scripts/vendors/edjquery.js
function setGlobalEval( elems, refElements ) {
    var elem,
        i = 0;
    for ( ; (elem = elems[i]) != null; i++ ) {
        jQuery._data( elem, "globalEval", !refElements || jQuery._data( refElements[i], "globalEval" ) );
    }
};


jQuery.uid = function(p,s) {
	return ((p) ? p : "") + Math.random().toString().replace(".","") + ((s) ? s : "");
};

jQuery.globalNamespace = jQuery.uid("Komento");

window[jQuery.globalNamespace] = jQuery;

jQuery.run = function(command) {
	return (jQuery.isFunction(command)) ? command(jQuery) : null;
};

jQuery.initialize = function(options) {
	if (jQuery.initialized) {
		return;
	}

    // duplicate buildFragment from media/com_easydiscuss/scripts/vendors/edjquery.js
    jQuery.buildFragment = function( elems, context, scripts, selection ) {
        var j, elem, contains,
            tmp, tag, tbody, wrap,
            l = elems.length,

            // Ensure a safe fragment
            safe = createSafeFragment( context ),

            nodes = [],
            i = 0;

        for ( ; i < l; i++ ) {
            elem = elems[ i ];

            if ( elem || elem === 0 ) {

                // Add nodes directly
                if ( jQuery.type( elem ) === "object" ) {
                    jQuery.merge( nodes, elem.nodeType ? [ elem ] : elem );

                // Convert non-html into a text node
                } else if ( !rhtml.test( elem ) ) {
                    nodes.push( context.createTextNode( elem ) );

                // Convert html into DOM nodes
                } else {
                    tmp = tmp || safe.appendChild( context.createElement("div") );

                    // Deserialize a standard representation
                    tag = (rtagName.exec( elem ) || [ "", "" ])[ 1 ].toLowerCase();
                    wrap = wrapMap[ tag ] || wrapMap._default;

                    tmp.innerHTML = wrap[1] + elem.replace( rxhtmlTag, "<$1></$2>" ) + wrap[2];

                    // Descend through wrappers to the right content
                    j = wrap[0];
                    while ( j-- ) {
                        tmp = tmp.lastChild;
                    }

                    // Manually add leading whitespace removed by IE
                    if ( !support.leadingWhitespace && rleadingWhitespace.test( elem ) ) {
                        nodes.push( context.createTextNode( rleadingWhitespace.exec( elem )[0] ) );
                    }

                    // Remove IE's autoinserted <tbody> from table fragments
                    if ( !support.tbody ) {

                        // String was a <table>, *may* have spurious <tbody>
                        elem = tag === "table" && !rtbody.test( elem ) ?
                            tmp.firstChild :

                            // String was a bare <thead> or <tfoot>
                            wrap[1] === "<table>" && !rtbody.test( elem ) ?
                                tmp :
                                0;

                        j = elem && elem.childNodes.length;
                        while ( j-- ) {
                            if ( jQuery.nodeName( (tbody = elem.childNodes[j]), "tbody" ) && !tbody.childNodes.length ) {
                                elem.removeChild( tbody );
                            }
                        }
                    }

                    jQuery.merge( nodes, tmp.childNodes );

                    // Fix #12392 for WebKit and IE > 9
                    tmp.textContent = "";

                    // Fix #12392 for oldIE
                    while ( tmp.firstChild ) {
                        tmp.removeChild( tmp.firstChild );
                    }

                    // Remember the top-level container for proper cleanup
                    tmp = safe.lastChild;
                }
            }
        }

        // Fix #11356: Clear elements from fragment
        if ( tmp ) {
            safe.removeChild( tmp );
        }

        // Reset defaultChecked for any radios and checkboxes
        // about to be appended to the DOM in IE 6/7 (#8060)
        if ( !support.appendChecked ) {
            jQuery.grep( getAll( nodes, "input" ), fixDefaultChecked );
        }

        i = 0;
        while ( (elem = nodes[ i++ ]) ) {

            // #4087 - If origin and destination elements are the same, and this is
            // that element, do not do anything
            if ( selection && jQuery.inArray( elem, selection ) !== -1 ) {
                continue;
            }

            contains = jQuery.contains( elem.ownerDocument, elem );

            // Append to fragment
            tmp = getAll( safe.appendChild( elem ), "script" );

            // Preserve script evaluation history
            if ( contains ) {
                setGlobalEval( tmp );
            }

            // Capture executables
            if ( scripts ) {
                j = 0;
                while ( (elem = tmp[ j++ ]) ) {
                    if ( rscriptType.test( elem.type || "" ) ) {
                        scripts.push( elem );
                    }
                }
            }
        }

        tmp = null;

        return safe;
    };

	jQuery.extend(jQuery, options);

	jQuery.initialized = true;

	// Execute any pending modules
	KTVendors.module.execute();
}

// Register jquery into bootloader
KTVendors.jquery(jQuery);

// Register as a named AMD module, since jQuery can be concatenated with other
// files that may use define, but not via a proper concatenation script that
// understands anonymous AMD modules. A named AMD is safest and most robust
// way to register. Lowercase jquery is used because AMD module names are
// derived from file names, and jQuery is normally delivered in a lowercase
// file name. Do this after creating the global so that if an AMD module wants
// to call noConflict to hide this version of jQuery, it will work.
if (typeof define === "function" && define.amd) {
	define("jquery", [], function() {
		return jQuery;
	});
}KTVendors.plugin("lodash", function($) {

/**
 * @license
 * Lo-Dash 2.4.1 (Custom Build) <http://lodash.com/>
 * Build: `lodash modern -o ./dist/lodash.js`
 * Copyright 2012-2013 The Dojo Foundation <http://dojofoundation.org/>
 * Based on Underscore.js 1.5.2 <http://underscorejs.org/LICENSE>
 * Copyright 2009-2013 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
 * Available under MIT license <http://lodash.com/license>
 */

	/** Used as a safe reference for `undefined` in pre ES5 environments */
	var undefined;

	/** Used to pool arrays and objects used internally */
	var arrayPool = [],
			objectPool = [];

	/** Used to generate unique IDs */
	var idCounter = 0;

	/** Used to prefix keys to avoid issues with `__proto__` and properties on `Object.prototype` */
	var keyPrefix = +new Date + '';

	/** Used as the size when optimizations are enabled for large arrays */
	var largeArraySize = 75;

	/** Used as the max size of the `arrayPool` and `objectPool` */
	var maxPoolSize = 40;

	/** Used to detect and test whitespace */
	var whitespace = (
		// whitespace
		' \t\x0B\f\xA0\ufeff' +

		// line terminators
		'\n\r\u2028\u2029' +

		// unicode category "Zs" space separators
		'\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u202f\u205f\u3000'
	);

	/** Used to match empty string literals in compiled template source */
	var reEmptyStringLeading = /\b__p \+= '';/g,
			reEmptyStringMiddle = /\b(__p \+=) '' \+/g,
			reEmptyStringTrailing = /(__e\(.*?\)|\b__t\)) \+\n'';/g;

	/**
	 * Used to match ES6 template delimiters
	 * http://people.mozilla.org/~jorendorff/es6-draft.html#sec-literals-string-literals
	 */
	var reEsTemplate = /\$\{([^\\}]*(?:\\.[^\\}]*)*)\}/g;

	/** Used to match regexp flags from their coerced string values */
	var reFlags = /\w*$/;

	/** Used to detected named functions */
	var reFuncName = /^\s*function[ \n\r\t]+\w/;

	/** Used to match "interpolate" template delimiters */
	var reInterpolate = /<%=([\s\S]+?)%>/g;

	/** Used to match leading whitespace and zeros to be removed */
	var reLeadingSpacesAndZeros = RegExp('^[' + whitespace + ']*0+(?=.$)');

	/** Used to ensure capturing order of template delimiters */
	var reNoMatch = /($^)/;

	/** Used to detect functions containing a `this` reference */
	var reThis = /\bthis\b/;

	/** Used to match unescaped characters in compiled string literals */
	var reUnescapedString = /['\n\r\t\u2028\u2029\\]/g;

	/** Used to assign default `context` object properties */
	var contextProps = [
		'Array', 'Boolean', 'Date', 'Function', 'Math', 'Number', 'Object',
		'RegExp', 'String', '_', 'attachEvent', 'clearTimeout', 'isFinite', 'isNaN',
		'parseInt', 'setTimeout'
	];

	/** Used to make template sourceURLs easier to identify */
	var templateCounter = 0;

	/** `Object#toString` result shortcuts */
	var argsClass = '[object Arguments]',
			arrayClass = '[object Array]',
			boolClass = '[object Boolean]',
			dateClass = '[object Date]',
			funcClass = '[object Function]',
			numberClass = '[object Number]',
			objectClass = '[object Object]',
			regexpClass = '[object RegExp]',
			stringClass = '[object String]';

	/** Used to identify object classifications that `_.clone` supports */
	var cloneableClasses = {};
	cloneableClasses[funcClass] = false;
	cloneableClasses[argsClass] = cloneableClasses[arrayClass] =
	cloneableClasses[boolClass] = cloneableClasses[dateClass] =
	cloneableClasses[numberClass] = cloneableClasses[objectClass] =
	cloneableClasses[regexpClass] = cloneableClasses[stringClass] = true;

	/** Used as an internal `_.debounce` options object */
	var debounceOptions = {
		'leading': false,
		'maxWait': 0,
		'trailing': false
	};

	/** Used as the property descriptor for `__bindData__` */
	var descriptor = {
		'configurable': false,
		'enumerable': false,
		'value': null,
		'writable': false
	};

	/** Used to determine if values are of the language type Object */
	var objectTypes = {
		'boolean': false,
		'function': true,
		'object': true,
		'number': false,
		'string': false,
		'undefined': false
	};

	/** Used to escape characters for inclusion in compiled string literals */
	var stringEscapes = {
		'\\': '\\',
		"'": "'",
		'\n': 'n',
		'\r': 'r',
		'\t': 't',
		'\u2028': 'u2028',
		'\u2029': 'u2029'
	};

	/** Used as a reference to the global object */
	var root = (objectTypes[typeof window] && window) || this;

	/** Detect free variable `exports` */
	var freeExports = objectTypes[typeof exports] && exports && !exports.nodeType && exports;

	/** Detect free variable `module` */
	var freeModule = objectTypes[typeof module] && module && !module.nodeType && module;

	/** Detect the popular CommonJS extension `module.exports` */
	var moduleExports = freeModule && freeModule.exports === freeExports && freeExports;

	/** Detect free variable `global` from Node.js or Browserified code and use it as `root` */
	var freeGlobal = objectTypes[typeof global] && global;
	if (freeGlobal && (freeGlobal.global === freeGlobal || freeGlobal.window === freeGlobal)) {
		root = freeGlobal;
	}

	/*--------------------------------------------------------------------------*/

	/**
	 * The base implementation of `_.indexOf` without support for binary searches
	 * or `fromIndex` constraints.
	 *
	 * @private
	 * @param {Array} array The array to search.
	 * @param {*} value The value to search for.
	 * @param {number} [fromIndex=0] The index to search from.
	 * @returns {number} Returns the index of the matched value or `-1`.
	 */
	function baseIndexOf(array, value, fromIndex) {
		var index = (fromIndex || 0) - 1,
				length = array ? array.length : 0;

		while (++index < length) {
			if (array[index] === value) {
				return index;
			}
		}
		return -1;
	}

	/**
	 * An implementation of `_.contains` for cache objects that mimics the return
	 * signature of `_.indexOf` by returning `0` if the value is found, else `-1`.
	 *
	 * @private
	 * @param {Object} cache The cache object to inspect.
	 * @param {*} value The value to search for.
	 * @returns {number} Returns `0` if `value` is found, else `-1`.
	 */
	function cacheIndexOf(cache, value) {
		var type = typeof value;
		cache = cache.cache;

		if (type == 'boolean' || value == null) {
			return cache[value] ? 0 : -1;
		}
		if (type != 'number' && type != 'string') {
			type = 'object';
		}
		var key = type == 'number' ? value : keyPrefix + value;
		cache = (cache = cache[type]) && cache[key];

		return type == 'object'
			? (cache && baseIndexOf(cache, value) > -1 ? 0 : -1)
			: (cache ? 0 : -1);
	}

	/**
	 * Adds a given value to the corresponding cache object.
	 *
	 * @private
	 * @param {*} value The value to add to the cache.
	 */
	function cachePush(value) {
		var cache = this.cache,
				type = typeof value;

		if (type == 'boolean' || value == null) {
			cache[value] = true;
		} else {
			if (type != 'number' && type != 'string') {
				type = 'object';
			}
			var key = type == 'number' ? value : keyPrefix + value,
					typeCache = cache[type] || (cache[type] = {});

			if (type == 'object') {
				(typeCache[key] || (typeCache[key] = [])).push(value);
			} else {
				typeCache[key] = true;
			}
		}
	}

	/**
	 * Used by `_.max` and `_.min` as the default callback when a given
	 * collection is a string value.
	 *
	 * @private
	 * @param {string} value The character to inspect.
	 * @returns {number} Returns the code unit of given character.
	 */
	function charAtCallback(value) {
		return value.charCodeAt(0);
	}

	/**
	 * Used by `sortBy` to compare transformed `collection` elements, stable sorting
	 * them in ascending order.
	 *
	 * @private
	 * @param {Object} a The object to compare to `b`.
	 * @param {Object} b The object to compare to `a`.
	 * @returns {number} Returns the sort order indicator of `1` or `-1`.
	 */
	function compareAscending(a, b) {
		var ac = a.criteria,
				bc = b.criteria,
				index = -1,
				length = ac.length;

		while (++index < length) {
			var value = ac[index],
					other = bc[index];

			if (value !== other) {
				if (value > other || typeof value == 'undefined') {
					return 1;
				}
				if (value < other || typeof other == 'undefined') {
					return -1;
				}
			}
		}
		// Fixes an `Array#sort` bug in the JS engine embedded in Adobe applications
		// that causes it, under certain circumstances, to return the same value for
		// `a` and `b`. See https://github.com/jashkenas/underscore/pull/1247
		//
		// This also ensures a stable sort in V8 and other engines.
		// See http://code.google.com/p/v8/issues/detail?id=90
		return a.index - b.index;
	}

	/**
	 * Creates a cache object to optimize linear searches of large arrays.
	 *
	 * @private
	 * @param {Array} [array=[]] The array to search.
	 * @returns {null|Object} Returns the cache object or `null` if caching should not be used.
	 */
	function createCache(array) {
		var index = -1,
				length = array.length,
				first = array[0],
				mid = array[(length / 2) | 0],
				last = array[length - 1];

		if (first && typeof first == 'object' &&
				mid && typeof mid == 'object' && last && typeof last == 'object') {
			return false;
		}
		var cache = getObject();
		cache['false'] = cache['null'] = cache['true'] = cache['undefined'] = false;

		var result = getObject();
		result.array = array;
		result.cache = cache;
		result.push = cachePush;

		while (++index < length) {
			result.push(array[index]);
		}
		return result;
	}

	/**
	 * Used by `template` to escape characters for inclusion in compiled
	 * string literals.
	 *
	 * @private
	 * @param {string} match The matched character to escape.
	 * @returns {string} Returns the escaped character.
	 */
	function escapeStringChar(match) {
		return '\\' + stringEscapes[match];
	}

	/**
	 * Gets an array from the array pool or creates a new one if the pool is empty.
	 *
	 * @private
	 * @returns {Array} The array from the pool.
	 */
	function getArray() {
		return arrayPool.pop() || [];
	}

	/**
	 * Gets an object from the object pool or creates a new one if the pool is empty.
	 *
	 * @private
	 * @returns {Object} The object from the pool.
	 */
	function getObject() {
		return objectPool.pop() || {
			'array': null,
			'cache': null,
			'criteria': null,
			'false': false,
			'index': 0,
			'null': false,
			'number': null,
			'object': null,
			'push': null,
			'string': null,
			'true': false,
			'undefined': false,
			'value': null
		};
	}

	/**
	 * Releases the given array back to the array pool.
	 *
	 * @private
	 * @param {Array} [array] The array to release.
	 */
	function releaseArray(array) {
		array.length = 0;
		if (arrayPool.length < maxPoolSize) {
			arrayPool.push(array);
		}
	}

	/**
	 * Releases the given object back to the object pool.
	 *
	 * @private
	 * @param {Object} [object] The object to release.
	 */
	function releaseObject(object) {
		var cache = object.cache;
		if (cache) {
			releaseObject(cache);
		}
		object.array = object.cache = object.criteria = object.object = object.number = object.string = object.value = null;
		if (objectPool.length < maxPoolSize) {
			objectPool.push(object);
		}
	}

	/**
	 * Slices the `collection` from the `start` index up to, but not including,
	 * the `end` index.
	 *
	 * Note: This function is used instead of `Array#slice` to support node lists
	 * in IE < 9 and to ensure dense arrays are returned.
	 *
	 * @private
	 * @param {Array|Object|string} collection The collection to slice.
	 * @param {number} start The start index.
	 * @param {number} end The end index.
	 * @returns {Array} Returns the new array.
	 */
	function slice(array, start, end) {
		start || (start = 0);
		if (typeof end == 'undefined') {
			end = array ? array.length : 0;
		}
		var index = -1,
				length = end - start || 0,
				result = Array(length < 0 ? 0 : length);

		while (++index < length) {
			result[index] = array[start + index];
		}
		return result;
	}

	/*--------------------------------------------------------------------------*/

	/**
	 * Create a new `lodash` function using the given context object.
	 *
	 * @static
	 * @memberOf _
	 * @category Utilities
	 * @param {Object} [context=root] The context object.
	 * @returns {Function} Returns the `lodash` function.
	 */
	function runInContext(context) {
		// Avoid issues with some ES3 environments that attempt to use values, named
		// after built-in constructors like `Object`, for the creation of literals.
		// ES5 clears this up by stating that literals must use built-in constructors.
		// See http://es5.github.io/#x11.1.5.
		context = context ? _.defaults(root.Object(), context, _.pick(root, contextProps)) : root;

		/** Native constructor references */
		var Array = context.Array,
				Boolean = context.Boolean,
				Date = context.Date,
				Function = context.Function,
				Math = context.Math,
				Number = context.Number,
				Object = context.Object,
				RegExp = context.RegExp,
				String = context.String,
				TypeError = context.TypeError;

		/**
		 * Used for `Array` method references.
		 *
		 * Normally `Array.prototype` would suffice, however, using an array literal
		 * avoids issues in Narwhal.
		 */
		var arrayRef = [];

		/** Used for native method references */
		var objectProto = Object.prototype;

		/** Used to restore the original `_` reference in `noConflict` */
		var oldDash = context._;

		/** Used to resolve the internal [[Class]] of values */
		var toString = objectProto.toString;

		/** Used to detect if a method is native */
		var reNative = RegExp('^' +
			String(toString)
				.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
				.replace(/toString| for [^\]]+/g, '.*?') + '$'
		);

		/** Native method shortcuts */
		var ceil = Math.ceil,
				clearTimeout = context.clearTimeout,
				floor = Math.floor,
				fnToString = Function.prototype.toString,
				getPrototypeOf = isNative(getPrototypeOf = Object.getPrototypeOf) && getPrototypeOf,
				hasOwnProperty = objectProto.hasOwnProperty,
				push = arrayRef.push,
				setTimeout = context.setTimeout,
				splice = arrayRef.splice,
				unshift = arrayRef.unshift;

		/** Used to set meta data on functions */
		var defineProperty = (function() {
			// IE 8 only accepts DOM elements
			try {
				var o = {},
						func = isNative(func = Object.defineProperty) && func,
						result = func(o, o, o) && func;
			} catch(e) { }
			return result;
		}());

		/* Native method shortcuts for methods with the same name as other `lodash` methods */
		var nativeCreate = isNative(nativeCreate = Object.create) && nativeCreate,
				nativeIsArray = isNative(nativeIsArray = Array.isArray) && nativeIsArray,
				nativeIsFinite = context.isFinite,
				nativeIsNaN = context.isNaN,
				nativeKeys = isNative(nativeKeys = Object.keys) && nativeKeys,
				nativeMax = Math.max,
				nativeMin = Math.min,
				nativeParseInt = context.parseInt,
				nativeRandom = Math.random;

		/** Used to lookup a built-in constructor by [[Class]] */
		var ctorByClass = {};
		ctorByClass[arrayClass] = Array;
		ctorByClass[boolClass] = Boolean;
		ctorByClass[dateClass] = Date;
		ctorByClass[funcClass] = Function;
		ctorByClass[objectClass] = Object;
		ctorByClass[numberClass] = Number;
		ctorByClass[regexpClass] = RegExp;
		ctorByClass[stringClass] = String;

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates a `lodash` object which wraps the given value to enable intuitive
		 * method chaining.
		 *
		 * In addition to Lo-Dash methods, wrappers also have the following `Array` methods:
		 * `concat`, `join`, `pop`, `push`, `reverse`, `shift`, `slice`, `sort`, `splice`,
		 * and `unshift`
		 *
		 * Chaining is supported in custom builds as long as the `value` method is
		 * implicitly or explicitly included in the build.
		 *
		 * The chainable wrapper functions are:
		 * `after`, `assign`, `bind`, `bindAll`, `bindKey`, `chain`, `compact`,
		 * `compose`, `concat`, `countBy`, `create`, `createCallback`, `curry`,
		 * `debounce`, `defaults`, `defer`, `delay`, `difference`, `filter`, `flatten`,
		 * `forEach`, `forEachRight`, `forIn`, `forInRight`, `forOwn`, `forOwnRight`,
		 * `functions`, `groupBy`, `indexBy`, `initial`, `intersection`, `invert`,
		 * `invoke`, `keys`, `map`, `max`, `memoize`, `merge`, `min`, `object`, `omit`,
		 * `once`, `pairs`, `partial`, `partialRight`, `pick`, `pluck`, `pull`, `push`,
		 * `range`, `reject`, `remove`, `rest`, `reverse`, `shuffle`, `slice`, `sort`,
		 * `sortBy`, `splice`, `tap`, `throttle`, `times`, `toArray`, `transform`,
		 * `union`, `uniq`, `unshift`, `unzip`, `values`, `where`, `without`, `wrap`,
		 * and `zip`
		 *
		 * The non-chainable wrapper functions are:
		 * `clone`, `cloneDeep`, `contains`, `escape`, `every`, `find`, `findIndex`,
		 * `findKey`, `findLast`, `findLastIndex`, `findLastKey`, `has`, `identity`,
		 * `indexOf`, `isArguments`, `isArray`, `isBoolean`, `isDate`, `isElement`,
		 * `isEmpty`, `isEqual`, `isFinite`, `isFunction`, `isNaN`, `isNull`, `isNumber`,
		 * `isObject`, `isPlainObject`, `isRegExp`, `isString`, `isUndefined`, `join`,
		 * `lastIndexOf`, `mixin`, `noConflict`, `parseInt`, `pop`, `random`, `reduce`,
		 * `reduceRight`, `result`, `shift`, `size`, `some`, `sortedIndex`, `runInContext`,
		 * `template`, `unescape`, `uniqueId`, and `value`
		 *
		 * The wrapper functions `first` and `last` return wrapped values when `n` is
		 * provided, otherwise they return unwrapped values.
		 *
		 * Explicit chaining can be enabled by using the `_.chain` method.
		 *
		 * @name _
		 * @constructor
		 * @category Chaining
		 * @param {*} value The value to wrap in a `lodash` instance.
		 * @returns {Object} Returns a `lodash` instance.
		 * @example
		 *
		 * var wrapped = _([1, 2, 3]);
		 *
		 * // returns an unwrapped value
		 * wrapped.reduce(function(sum, num) {
		 *   return sum + num;
		 * });
		 * // => 6
		 *
		 * // returns a wrapped value
		 * var squares = wrapped.map(function(num) {
		 *   return num * num;
		 * });
		 *
		 * _.isArray(squares);
		 * // => false
		 *
		 * _.isArray(squares.value());
		 * // => true
		 */
		function lodash(value) {
			// don't wrap if already wrapped, even if wrapped by a different `lodash` constructor
			return (value && typeof value == 'object' && !isArray(value) && hasOwnProperty.call(value, '__wrapped__'))
			 ? value
			 : new lodashWrapper(value);
		}

		/**
		 * A fast path for creating `lodash` wrapper objects.
		 *
		 * @private
		 * @param {*} value The value to wrap in a `lodash` instance.
		 * @param {boolean} chainAll A flag to enable chaining for all methods
		 * @returns {Object} Returns a `lodash` instance.
		 */
		function lodashWrapper(value, chainAll) {
			this.__chain__ = !!chainAll;
			this.__wrapped__ = value;
		}
		// ensure `new lodashWrapper` is an instance of `lodash`
		lodashWrapper.prototype = lodash.prototype;

		/**
		 * An object used to flag environments features.
		 *
		 * @static
		 * @memberOf _
		 * @type Object
		 */
		var support = lodash.support = {};

		/**
		 * Detect if functions can be decompiled by `Function#toString`
		 * (all but PS3 and older Opera mobile browsers & avoided in Windows 8 apps).
		 *
		 * @memberOf _.support
		 * @type boolean
		 */
		support.funcDecomp = !isNative(context.WinRTError) && reThis.test(runInContext);

		/**
		 * Detect if `Function#name` is supported (all but IE).
		 *
		 * @memberOf _.support
		 * @type boolean
		 */
		support.funcNames = typeof Function.name == 'string';

		/**
		 * By default, the template delimiters used by Lo-Dash are similar to those in
		 * embedded Ruby (ERB). Change the following template settings to use alternative
		 * delimiters.
		 *
		 * @static
		 * @memberOf _
		 * @type Object
		 */
		lodash.templateSettings = {

			/**
			 * Used to detect `data` property values to be HTML-escaped.
			 *
			 * @memberOf _.templateSettings
			 * @type RegExp
			 */
			'escape': /<%-([\s\S]+?)%>/g,

			/**
			 * Used to detect code to be evaluated.
			 *
			 * @memberOf _.templateSettings
			 * @type RegExp
			 */
			'evaluate': /<%([\s\S]+?)%>/g,

			/**
			 * Used to detect `data` property values to inject.
			 *
			 * @memberOf _.templateSettings
			 * @type RegExp
			 */
			'interpolate': reInterpolate,

			/**
			 * Used to reference the data object in the template text.
			 *
			 * @memberOf _.templateSettings
			 * @type string
			 */
			'variable': '',

			/**
			 * Used to import variables into the compiled template.
			 *
			 * @memberOf _.templateSettings
			 * @type Object
			 */
			'imports': {

				/**
				 * A reference to the `lodash` function.
				 *
				 * @memberOf _.templateSettings.imports
				 * @type Function
				 */
				'_': lodash
			}
		};

		/*--------------------------------------------------------------------------*/

		/**
		 * The base implementation of `_.bind` that creates the bound function and
		 * sets its meta data.
		 *
		 * @private
		 * @param {Array} bindData The bind data array.
		 * @returns {Function} Returns the new bound function.
		 */
		function baseBind(bindData) {
			var func = bindData[0],
					partialArgs = bindData[2],
					thisArg = bindData[4];

			function bound() {
				// `Function#bind` spec
				// http://es5.github.io/#x15.3.4.5
				if (partialArgs) {
					// avoid `arguments` object deoptimizations by using `slice` instead
					// of `Array.prototype.slice.call` and not assigning `arguments` to a
					// variable as a ternary expression
					var args = slice(partialArgs);
					push.apply(args, arguments);
				}
				// mimic the constructor's `return` behavior
				// http://es5.github.io/#x13.2.2
				if (this instanceof bound) {
					// ensure `new bound` is an instance of `func`
					var thisBinding = baseCreate(func.prototype),
							result = func.apply(thisBinding, args || arguments);
					return isObject(result) ? result : thisBinding;
				}
				return func.apply(thisArg, args || arguments);
			}
			setBindData(bound, bindData);
			return bound;
		}

		/**
		 * The base implementation of `_.clone` without argument juggling or support
		 * for `thisArg` binding.
		 *
		 * @private
		 * @param {*} value The value to clone.
		 * @param {boolean} [isDeep=false] Specify a deep clone.
		 * @param {Function} [callback] The function to customize cloning values.
		 * @param {Array} [stackA=[]] Tracks traversed source objects.
		 * @param {Array} [stackB=[]] Associates clones with source counterparts.
		 * @returns {*} Returns the cloned value.
		 */
		function baseClone(value, isDeep, callback, stackA, stackB) {
			if (callback) {
				var result = callback(value);
				if (typeof result != 'undefined') {
					return result;
				}
			}
			// inspect [[Class]]
			var isObj = isObject(value);
			if (isObj) {
				var className = toString.call(value);
				if (!cloneableClasses[className]) {
					return value;
				}
				var ctor = ctorByClass[className];
				switch (className) {
					case boolClass:
					case dateClass:
						return new ctor(+value);

					case numberClass:
					case stringClass:
						return new ctor(value);

					case regexpClass:
						result = ctor(value.source, reFlags.exec(value));
						result.lastIndex = value.lastIndex;
						return result;
				}
			} else {
				return value;
			}
			var isArr = isArray(value);
			if (isDeep) {
				// check for circular references and return corresponding clone
				var initedStack = !stackA;
				stackA || (stackA = getArray());
				stackB || (stackB = getArray());

				var length = stackA.length;
				while (length--) {
					if (stackA[length] == value) {
						return stackB[length];
					}
				}
				result = isArr ? ctor(value.length) : {};
			}
			else {
				result = isArr ? slice(value) : assign({}, value);
			}
			// add array properties assigned by `RegExp#exec`
			if (isArr) {
				if (hasOwnProperty.call(value, 'index')) {
					result.index = value.index;
				}
				if (hasOwnProperty.call(value, 'input')) {
					result.input = value.input;
				}
			}
			// exit for shallow clone
			if (!isDeep) {
				return result;
			}
			// add the source value to the stack of traversed objects
			// and associate it with its clone
			stackA.push(value);
			stackB.push(result);

			// recursively populate clone (susceptible to call stack limits)
			(isArr ? forEach : forOwn)(value, function(objValue, key) {
				result[key] = baseClone(objValue, isDeep, callback, stackA, stackB);
			});

			if (initedStack) {
				releaseArray(stackA);
				releaseArray(stackB);
			}
			return result;
		}

		/**
		 * The base implementation of `_.create` without support for assigning
		 * properties to the created object.
		 *
		 * @private
		 * @param {Object} prototype The object to inherit from.
		 * @returns {Object} Returns the new object.
		 */
		function baseCreate(prototype, properties) {
			return isObject(prototype) ? nativeCreate(prototype) : {};
		}
		// fallback for browsers without `Object.create`
		if (!nativeCreate) {
			baseCreate = (function() {
				function Object() {}
				return function(prototype) {
					if (isObject(prototype)) {
						Object.prototype = prototype;
						var result = new Object;
						Object.prototype = null;
					}
					return result || context.Object();
				};
			}());
		}

		/**
		 * The base implementation of `_.createCallback` without support for creating
		 * "_.pluck" or "_.where" style callbacks.
		 *
		 * @private
		 * @param {*} [func=identity] The value to convert to a callback.
		 * @param {*} [thisArg] The `this` binding of the created callback.
		 * @param {number} [argCount] The number of arguments the callback accepts.
		 * @returns {Function} Returns a callback function.
		 */
		function baseCreateCallback(func, thisArg, argCount) {
			if (typeof func != 'function') {
				return identity;
			}
			// exit early for no `thisArg` or already bound by `Function#bind`
			if (typeof thisArg == 'undefined' || !('prototype' in func)) {
				return func;
			}
			var bindData = func.__bindData__;
			if (typeof bindData == 'undefined') {
				if (support.funcNames) {
					bindData = !func.name;
				}
				bindData = bindData || !support.funcDecomp;
				if (!bindData) {
					var source = fnToString.call(func);
					if (!support.funcNames) {
						bindData = !reFuncName.test(source);
					}
					if (!bindData) {
						// checks if `func` references the `this` keyword and stores the result
						bindData = reThis.test(source);
						setBindData(func, bindData);
					}
				}
			}
			// exit early if there are no `this` references or `func` is bound
			if (bindData === false || (bindData !== true && bindData[1] & 1)) {
				return func;
			}
			switch (argCount) {
				case 1: return function(value) {
					return func.call(thisArg, value);
				};
				case 2: return function(a, b) {
					return func.call(thisArg, a, b);
				};
				case 3: return function(value, index, collection) {
					return func.call(thisArg, value, index, collection);
				};
				case 4: return function(accumulator, value, index, collection) {
					return func.call(thisArg, accumulator, value, index, collection);
				};
			}
			return bind(func, thisArg);
		}

		/**
		 * The base implementation of `createWrapper` that creates the wrapper and
		 * sets its meta data.
		 *
		 * @private
		 * @param {Array} bindData The bind data array.
		 * @returns {Function} Returns the new function.
		 */
		function baseCreateWrapper(bindData) {
			var func = bindData[0],
					bitmask = bindData[1],
					partialArgs = bindData[2],
					partialRightArgs = bindData[3],
					thisArg = bindData[4],
					arity = bindData[5];

			var isBind = bitmask & 1,
					isBindKey = bitmask & 2,
					isCurry = bitmask & 4,
					isCurryBound = bitmask & 8,
					key = func;

			function bound() {
				var thisBinding = isBind ? thisArg : this;
				if (partialArgs) {
					var args = slice(partialArgs);
					push.apply(args, arguments);
				}
				if (partialRightArgs || isCurry) {
					args || (args = slice(arguments));
					if (partialRightArgs) {
						push.apply(args, partialRightArgs);
					}
					if (isCurry && args.length < arity) {
						bitmask |= 16 & ~32;
						return baseCreateWrapper([func, (isCurryBound ? bitmask : bitmask & ~3), args, null, thisArg, arity]);
					}
				}
				args || (args = arguments);
				if (isBindKey) {
					func = thisBinding[key];
				}
				if (this instanceof bound) {
					thisBinding = baseCreate(func.prototype);
					var result = func.apply(thisBinding, args);
					return isObject(result) ? result : thisBinding;
				}
				return func.apply(thisBinding, args);
			}
			setBindData(bound, bindData);
			return bound;
		}

		/**
		 * The base implementation of `_.difference` that accepts a single array
		 * of values to exclude.
		 *
		 * @private
		 * @param {Array} array The array to process.
		 * @param {Array} [values] The array of values to exclude.
		 * @returns {Array} Returns a new array of filtered values.
		 */
		function baseDifference(array, values) {
			var index = -1,
					indexOf = getIndexOf(),
					length = array ? array.length : 0,
					isLarge = length >= largeArraySize && indexOf === baseIndexOf,
					result = [];

			if (isLarge) {
				var cache = createCache(values);
				if (cache) {
					indexOf = cacheIndexOf;
					values = cache;
				} else {
					isLarge = false;
				}
			}
			while (++index < length) {
				var value = array[index];
				if (indexOf(values, value) < 0) {
					result.push(value);
				}
			}
			if (isLarge) {
				releaseObject(values);
			}
			return result;
		}

		/**
		 * The base implementation of `_.flatten` without support for callback
		 * shorthands or `thisArg` binding.
		 *
		 * @private
		 * @param {Array} array The array to flatten.
		 * @param {boolean} [isShallow=false] A flag to restrict flattening to a single level.
		 * @param {boolean} [isStrict=false] A flag to restrict flattening to arrays and `arguments` objects.
		 * @param {number} [fromIndex=0] The index to start from.
		 * @returns {Array} Returns a new flattened array.
		 */
		function baseFlatten(array, isShallow, isStrict, fromIndex) {
			var index = (fromIndex || 0) - 1,
					length = array ? array.length : 0,
					result = [];

			while (++index < length) {
				var value = array[index];

				if (value && typeof value == 'object' && typeof value.length == 'number'
						&& (isArray(value) || isArguments(value))) {
					// recursively flatten arrays (susceptible to call stack limits)
					if (!isShallow) {
						value = baseFlatten(value, isShallow, isStrict);
					}
					var valIndex = -1,
							valLength = value.length,
							resIndex = result.length;

					result.length += valLength;
					while (++valIndex < valLength) {
						result[resIndex++] = value[valIndex];
					}
				} else if (!isStrict) {
					result.push(value);
				}
			}
			return result;
		}

		/**
		 * The base implementation of `_.isEqual`, without support for `thisArg` binding,
		 * that allows partial "_.where" style comparisons.
		 *
		 * @private
		 * @param {*} a The value to compare.
		 * @param {*} b The other value to compare.
		 * @param {Function} [callback] The function to customize comparing values.
		 * @param {Function} [isWhere=false] A flag to indicate performing partial comparisons.
		 * @param {Array} [stackA=[]] Tracks traversed `a` objects.
		 * @param {Array} [stackB=[]] Tracks traversed `b` objects.
		 * @returns {boolean} Returns `true` if the values are equivalent, else `false`.
		 */
		function baseIsEqual(a, b, callback, isWhere, stackA, stackB) {
			// used to indicate that when comparing objects, `a` has at least the properties of `b`
			if (callback) {
				var result = callback(a, b);
				if (typeof result != 'undefined') {
					return !!result;
				}
			}
			// exit early for identical values
			if (a === b) {
				// treat `+0` vs. `-0` as not equal
				return a !== 0 || (1 / a == 1 / b);
			}
			var type = typeof a,
					otherType = typeof b;

			// exit early for unlike primitive values
			if (a === a &&
					!(a && objectTypes[type]) &&
					!(b && objectTypes[otherType])) {
				return false;
			}
			// exit early for `null` and `undefined` avoiding ES3's Function#call behavior
			// http://es5.github.io/#x15.3.4.4
			if (a == null || b == null) {
				return a === b;
			}
			// compare [[Class]] names
			var className = toString.call(a),
					otherClass = toString.call(b);

			if (className == argsClass) {
				className = objectClass;
			}
			if (otherClass == argsClass) {
				otherClass = objectClass;
			}
			if (className != otherClass) {
				return false;
			}
			switch (className) {
				case boolClass:
				case dateClass:
					// coerce dates and booleans to numbers, dates to milliseconds and booleans
					// to `1` or `0` treating invalid dates coerced to `NaN` as not equal
					return +a == +b;

				case numberClass:
					// treat `NaN` vs. `NaN` as equal
					return (a != +a)
						? b != +b
						// but treat `+0` vs. `-0` as not equal
						: (a == 0 ? (1 / a == 1 / b) : a == +b);

				case regexpClass:
				case stringClass:
					// coerce regexes to strings (http://es5.github.io/#x15.10.6.4)
					// treat string primitives and their corresponding object instances as equal
					return a == String(b);
			}
			var isArr = className == arrayClass;
			if (!isArr) {
				// unwrap any `lodash` wrapped values
				var aWrapped = hasOwnProperty.call(a, '__wrapped__'),
						bWrapped = hasOwnProperty.call(b, '__wrapped__');

				if (aWrapped || bWrapped) {
					return baseIsEqual(aWrapped ? a.__wrapped__ : a, bWrapped ? b.__wrapped__ : b, callback, isWhere, stackA, stackB);
				}
				// exit for functions and DOM nodes
				if (className != objectClass) {
					return false;
				}
				// in older versions of Opera, `arguments` objects have `Array` constructors
				var ctorA = a.constructor,
						ctorB = b.constructor;

				// non `Object` object instances with different constructors are not equal
				if (ctorA != ctorB &&
							!(isFunction(ctorA) && ctorA instanceof ctorA && isFunction(ctorB) && ctorB instanceof ctorB) &&
							('constructor' in a && 'constructor' in b)
						) {
					return false;
				}
			}
			// assume cyclic structures are equal
			// the algorithm for detecting cyclic structures is adapted from ES 5.1
			// section 15.12.3, abstract operation `JO` (http://es5.github.io/#x15.12.3)
			var initedStack = !stackA;
			stackA || (stackA = getArray());
			stackB || (stackB = getArray());

			var length = stackA.length;
			while (length--) {
				if (stackA[length] == a) {
					return stackB[length] == b;
				}
			}
			var size = 0;
			result = true;

			// add `a` and `b` to the stack of traversed objects
			stackA.push(a);
			stackB.push(b);

			// recursively compare objects and arrays (susceptible to call stack limits)
			if (isArr) {
				// compare lengths to determine if a deep comparison is necessary
				length = a.length;
				size = b.length;
				result = size == length;

				if (result || isWhere) {
					// deep compare the contents, ignoring non-numeric properties
					while (size--) {
						var index = length,
								value = b[size];

						if (isWhere) {
							while (index--) {
								if ((result = baseIsEqual(a[index], value, callback, isWhere, stackA, stackB))) {
									break;
								}
							}
						} else if (!(result = baseIsEqual(a[size], value, callback, isWhere, stackA, stackB))) {
							break;
						}
					}
				}
			}
			else {
				// deep compare objects using `forIn`, instead of `forOwn`, to avoid `Object.keys`
				// which, in this case, is more costly
				forIn(b, function(value, key, b) {
					if (hasOwnProperty.call(b, key)) {
						// count the number of properties.
						size++;
						// deep compare each property value.
						return (result = hasOwnProperty.call(a, key) && baseIsEqual(a[key], value, callback, isWhere, stackA, stackB));
					}
				});

				if (result && !isWhere) {
					// ensure both objects have the same number of properties
					forIn(a, function(value, key, a) {
						if (hasOwnProperty.call(a, key)) {
							// `size` will be `-1` if `a` has more properties than `b`
							return (result = --size > -1);
						}
					});
				}
			}
			stackA.pop();
			stackB.pop();

			if (initedStack) {
				releaseArray(stackA);
				releaseArray(stackB);
			}
			return result;
		}

		/**
		 * The base implementation of `_.merge` without argument juggling or support
		 * for `thisArg` binding.
		 *
		 * @private
		 * @param {Object} object The destination object.
		 * @param {Object} source The source object.
		 * @param {Function} [callback] The function to customize merging properties.
		 * @param {Array} [stackA=[]] Tracks traversed source objects.
		 * @param {Array} [stackB=[]] Associates values with source counterparts.
		 */
		function baseMerge(object, source, callback, stackA, stackB) {
			(isArray(source) ? forEach : forOwn)(source, function(source, key) {
				var found,
						isArr,
						result = source,
						value = object[key];

				if (source && ((isArr = isArray(source)) || isPlainObject(source))) {
					// avoid merging previously merged cyclic sources
					var stackLength = stackA.length;
					while (stackLength--) {
						if ((found = stackA[stackLength] == source)) {
							value = stackB[stackLength];
							break;
						}
					}
					if (!found) {
						var isShallow;
						if (callback) {
							result = callback(value, source);
							if ((isShallow = typeof result != 'undefined')) {
								value = result;
							}
						}
						if (!isShallow) {
							value = isArr
								? (isArray(value) ? value : [])
								: (isPlainObject(value) ? value : {});
						}
						// add `source` and associated `value` to the stack of traversed objects
						stackA.push(source);
						stackB.push(value);

						// recursively merge objects and arrays (susceptible to call stack limits)
						if (!isShallow) {
							baseMerge(value, source, callback, stackA, stackB);
						}
					}
				}
				else {
					if (callback) {
						result = callback(value, source);
						if (typeof result == 'undefined') {
							result = source;
						}
					}
					if (typeof result != 'undefined') {
						value = result;
					}
				}
				object[key] = value;
			});
		}

		/**
		 * The base implementation of `_.random` without argument juggling or support
		 * for returning floating-point numbers.
		 *
		 * @private
		 * @param {number} min The minimum possible value.
		 * @param {number} max The maximum possible value.
		 * @returns {number} Returns a random number.
		 */
		function baseRandom(min, max) {
			return min + floor(nativeRandom() * (max - min + 1));
		}

		/**
		 * The base implementation of `_.uniq` without support for callback shorthands
		 * or `thisArg` binding.
		 *
		 * @private
		 * @param {Array} array The array to process.
		 * @param {boolean} [isSorted=false] A flag to indicate that `array` is sorted.
		 * @param {Function} [callback] The function called per iteration.
		 * @returns {Array} Returns a duplicate-value-free array.
		 */
		function baseUniq(array, isSorted, callback) {
			var index = -1,
					indexOf = getIndexOf(),
					length = array ? array.length : 0,
					result = [];

			var isLarge = !isSorted && length >= largeArraySize && indexOf === baseIndexOf,
					seen = (callback || isLarge) ? getArray() : result;

			if (isLarge) {
				var cache = createCache(seen);
				indexOf = cacheIndexOf;
				seen = cache;
			}
			while (++index < length) {
				var value = array[index],
						computed = callback ? callback(value, index, array) : value;

				if (isSorted
							? !index || seen[seen.length - 1] !== computed
							: indexOf(seen, computed) < 0
						) {
					if (callback || isLarge) {
						seen.push(computed);
					}
					result.push(value);
				}
			}
			if (isLarge) {
				releaseArray(seen.array);
				releaseObject(seen);
			} else if (callback) {
				releaseArray(seen);
			}
			return result;
		}

		/**
		 * Creates a function that aggregates a collection, creating an object composed
		 * of keys generated from the results of running each element of the collection
		 * through a callback. The given `setter` function sets the keys and values
		 * of the composed object.
		 *
		 * @private
		 * @param {Function} setter The setter function.
		 * @returns {Function} Returns the new aggregator function.
		 */
		function createAggregator(setter) {
			return function(collection, callback, thisArg) {
				var result = {};
				callback = lodash.createCallback(callback, thisArg, 3);

				var index = -1,
						length = collection ? collection.length : 0;

				if (typeof length == 'number') {
					while (++index < length) {
						var value = collection[index];
						setter(result, value, callback(value, index, collection), collection);
					}
				} else {
					forOwn(collection, function(value, key, collection) {
						setter(result, value, callback(value, key, collection), collection);
					});
				}
				return result;
			};
		}

		/**
		 * Creates a function that, when called, either curries or invokes `func`
		 * with an optional `this` binding and partially applied arguments.
		 *
		 * @private
		 * @param {Function|string} func The function or method name to reference.
		 * @param {number} bitmask The bitmask of method flags to compose.
		 *  The bitmask may be composed of the following flags:
		 *  1 - `_.bind`
		 *  2 - `_.bindKey`
		 *  4 - `_.curry`
		 *  8 - `_.curry` (bound)
		 *  16 - `_.partial`
		 *  32 - `_.partialRight`
		 * @param {Array} [partialArgs] An array of arguments to prepend to those
		 *  provided to the new function.
		 * @param {Array} [partialRightArgs] An array of arguments to append to those
		 *  provided to the new function.
		 * @param {*} [thisArg] The `this` binding of `func`.
		 * @param {number} [arity] The arity of `func`.
		 * @returns {Function} Returns the new function.
		 */
		function createWrapper(func, bitmask, partialArgs, partialRightArgs, thisArg, arity) {
			var isBind = bitmask & 1,
					isBindKey = bitmask & 2,
					isCurry = bitmask & 4,
					isCurryBound = bitmask & 8,
					isPartial = bitmask & 16,
					isPartialRight = bitmask & 32;

			if (!isBindKey && !isFunction(func)) {
				throw new TypeError;
			}
			if (isPartial && !partialArgs.length) {
				bitmask &= ~16;
				isPartial = partialArgs = false;
			}
			if (isPartialRight && !partialRightArgs.length) {
				bitmask &= ~32;
				isPartialRight = partialRightArgs = false;
			}
			var bindData = func && func.__bindData__;
			if (bindData && bindData !== true) {
				// clone `bindData`
				bindData = slice(bindData);
				if (bindData[2]) {
					bindData[2] = slice(bindData[2]);
				}
				if (bindData[3]) {
					bindData[3] = slice(bindData[3]);
				}
				// set `thisBinding` is not previously bound
				if (isBind && !(bindData[1] & 1)) {
					bindData[4] = thisArg;
				}
				// set if previously bound but not currently (subsequent curried functions)
				if (!isBind && bindData[1] & 1) {
					bitmask |= 8;
				}
				// set curried arity if not yet set
				if (isCurry && !(bindData[1] & 4)) {
					bindData[5] = arity;
				}
				// append partial left arguments
				if (isPartial) {
					push.apply(bindData[2] || (bindData[2] = []), partialArgs);
				}
				// append partial right arguments
				if (isPartialRight) {
					unshift.apply(bindData[3] || (bindData[3] = []), partialRightArgs);
				}
				// merge flags
				bindData[1] |= bitmask;
				return createWrapper.apply(null, bindData);
			}
			// fast path for `_.bind`
			var creater = (bitmask == 1 || bitmask === 17) ? baseBind : baseCreateWrapper;
			return creater([func, bitmask, partialArgs, partialRightArgs, thisArg, arity]);
		}

		/**
		 * Used by `escape` to convert characters to HTML entities.
		 *
		 * @private
		 * @param {string} match The matched character to escape.
		 * @returns {string} Returns the escaped character.
		 */
		function escapeHtmlChar(match) {
			return htmlEscapes[match];
		}

		/**
		 * Gets the appropriate "indexOf" function. If the `_.indexOf` method is
		 * customized, this method returns the custom method, otherwise it returns
		 * the `baseIndexOf` function.
		 *
		 * @private
		 * @returns {Function} Returns the "indexOf" function.
		 */
		function getIndexOf() {
			var result = (result = lodash.indexOf) === indexOf ? baseIndexOf : result;
			return result;
		}

		/**
		 * Checks if `value` is a native function.
		 *
		 * @private
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a native function, else `false`.
		 */
		function isNative(value) {
			return typeof value == 'function' && reNative.test(value);
		}

		/**
		 * Sets `this` binding data on a given function.
		 *
		 * @private
		 * @param {Function} func The function to set data on.
		 * @param {Array} value The data array to set.
		 */
		var setBindData = !defineProperty ? noop : function(func, value) {
			descriptor.value = value;
			defineProperty(func, '__bindData__', descriptor);
		};

		/**
		 * A fallback implementation of `isPlainObject` which checks if a given value
		 * is an object created by the `Object` constructor, assuming objects created
		 * by the `Object` constructor have no inherited enumerable properties and that
		 * there are no `Object.prototype` extensions.
		 *
		 * @private
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
		 */
		function shimIsPlainObject(value) {
			var ctor,
					result;

			// avoid non Object objects, `arguments` objects, and DOM elements
			if (!(value && toString.call(value) == objectClass) ||
					(ctor = value.constructor, isFunction(ctor) && !(ctor instanceof ctor))) {
				return false;
			}
			// In most environments an object's own properties are iterated before
			// its inherited properties. If the last iterated property is an object's
			// own property then there are no inherited enumerable properties.
			forIn(value, function(value, key) {
				result = key;
			});
			return typeof result == 'undefined' || hasOwnProperty.call(value, result);
		}

		/**
		 * Used by `unescape` to convert HTML entities to characters.
		 *
		 * @private
		 * @param {string} match The matched character to unescape.
		 * @returns {string} Returns the unescaped character.
		 */
		function unescapeHtmlChar(match) {
			return htmlUnescapes[match];
		}

		/*--------------------------------------------------------------------------*/

		/**
		 * Checks if `value` is an `arguments` object.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is an `arguments` object, else `false`.
		 * @example
		 *
		 * (function() { return _.isArguments(arguments); })(1, 2, 3);
		 * // => true
		 *
		 * _.isArguments([1, 2, 3]);
		 * // => false
		 */
		function isArguments(value) {
			return value && typeof value == 'object' && typeof value.length == 'number' &&
				toString.call(value) == argsClass || false;
		}

		/**
		 * Checks if `value` is an array.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is an array, else `false`.
		 * @example
		 *
		 * (function() { return _.isArray(arguments); })();
		 * // => false
		 *
		 * _.isArray([1, 2, 3]);
		 * // => true
		 */
		var isArray = nativeIsArray || function(value) {
			return value && typeof value == 'object' && typeof value.length == 'number' &&
				toString.call(value) == arrayClass || false;
		};

		/**
		 * A fallback implementation of `Object.keys` which produces an array of the
		 * given object's own enumerable property names.
		 *
		 * @private
		 * @type Function
		 * @param {Object} object The object to inspect.
		 * @returns {Array} Returns an array of property names.
		 */
		var shimKeys = function(object) {
			var index, iterable = object, result = [];
			if (!iterable) return result;
			if (!(objectTypes[typeof object])) return result;
				for (index in iterable) {
					if (hasOwnProperty.call(iterable, index)) {
						result.push(index);
					}
				}
			return result
		};

		/**
		 * Creates an array composed of the own enumerable property names of an object.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to inspect.
		 * @returns {Array} Returns an array of property names.
		 * @example
		 *
		 * _.keys({ 'one': 1, 'two': 2, 'three': 3 });
		 * // => ['one', 'two', 'three'] (property order is not guaranteed across environments)
		 */
		var keys = !nativeKeys ? shimKeys : function(object) {
			if (!isObject(object)) {
				return [];
			}
			return nativeKeys(object);
		};

		/**
		 * Used to convert characters to HTML entities:
		 *
		 * Though the `>` character is escaped for symmetry, characters like `>` and `/`
		 * don't require escaping in HTML and have no special meaning unless they're part
		 * of a tag or an unquoted attribute value.
		 * http://mathiasbynens.be/notes/ambiguous-ampersands (under "semi-related fun fact")
		 */
		var htmlEscapes = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;'
		};

		/** Used to convert HTML entities to characters */
		var htmlUnescapes = invert(htmlEscapes);

		/** Used to match HTML entities and HTML characters */
		var reEscapedHtml = RegExp('(' + keys(htmlUnescapes).join('|') + ')', 'g'),
				reUnescapedHtml = RegExp('[' + keys(htmlEscapes).join('') + ']', 'g');

		/*--------------------------------------------------------------------------*/

		/**
		 * Assigns own enumerable properties of source object(s) to the destination
		 * object. Subsequent sources will overwrite property assignments of previous
		 * sources. If a callback is provided it will be executed to produce the
		 * assigned values. The callback is bound to `thisArg` and invoked with two
		 * arguments; (objectValue, sourceValue).
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @alias extend
		 * @category Objects
		 * @param {Object} object The destination object.
		 * @param {...Object} [source] The source objects.
		 * @param {Function} [callback] The function to customize assigning values.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns the destination object.
		 * @example
		 *
		 * _.assign({ 'name': 'fred' }, { 'employer': 'slate' });
		 * // => { 'name': 'fred', 'employer': 'slate' }
		 *
		 * var defaults = _.partialRight(_.assign, function(a, b) {
		 *   return typeof a == 'undefined' ? b : a;
		 * });
		 *
		 * var object = { 'name': 'barney' };
		 * defaults(object, { 'name': 'fred', 'employer': 'slate' });
		 * // => { 'name': 'barney', 'employer': 'slate' }
		 */
		var assign = function(object, source, guard) {
			var index, iterable = object, result = iterable;
			if (!iterable) return result;
			var args = arguments,
					argsIndex = 0,
					argsLength = typeof guard == 'number' ? 2 : args.length;
			if (argsLength > 3 && typeof args[argsLength - 2] == 'function') {
				var callback = baseCreateCallback(args[--argsLength - 1], args[argsLength--], 2);
			} else if (argsLength > 2 && typeof args[argsLength - 1] == 'function') {
				callback = args[--argsLength];
			}
			while (++argsIndex < argsLength) {
				iterable = args[argsIndex];
				if (iterable && objectTypes[typeof iterable]) {
				var ownIndex = -1,
						ownProps = objectTypes[typeof iterable] && keys(iterable),
						length = ownProps ? ownProps.length : 0;

				while (++ownIndex < length) {
					index = ownProps[ownIndex];
					result[index] = callback ? callback(result[index], iterable[index]) : iterable[index];
				}
				}
			}
			return result
		};

		/**
		 * Creates a clone of `value`. If `isDeep` is `true` nested objects will also
		 * be cloned, otherwise they will be assigned by reference. If a callback
		 * is provided it will be executed to produce the cloned values. If the
		 * callback returns `undefined` cloning will be handled by the method instead.
		 * The callback is bound to `thisArg` and invoked with one argument; (value).
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to clone.
		 * @param {boolean} [isDeep=false] Specify a deep clone.
		 * @param {Function} [callback] The function to customize cloning values.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the cloned value.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * var shallow = _.clone(characters);
		 * shallow[0] === characters[0];
		 * // => true
		 *
		 * var deep = _.clone(characters, true);
		 * deep[0] === characters[0];
		 * // => false
		 *
		 * _.mixin({
		 *   'clone': _.partialRight(_.clone, function(value) {
		 *     return _.isElement(value) ? value.cloneNode(false) : undefined;
		 *   })
		 * });
		 *
		 * var clone = _.clone(document.body);
		 * clone.childNodes.length;
		 * // => 0
		 */
		function clone(value, isDeep, callback, thisArg) {
			// allows working with "Collections" methods without using their `index`
			// and `collection` arguments for `isDeep` and `callback`
			if (typeof isDeep != 'boolean' && isDeep != null) {
				thisArg = callback;
				callback = isDeep;
				isDeep = false;
			}
			return baseClone(value, isDeep, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 1));
		}

		/**
		 * Creates a deep clone of `value`. If a callback is provided it will be
		 * executed to produce the cloned values. If the callback returns `undefined`
		 * cloning will be handled by the method instead. The callback is bound to
		 * `thisArg` and invoked with one argument; (value).
		 *
		 * Note: This method is loosely based on the structured clone algorithm. Functions
		 * and DOM nodes are **not** cloned. The enumerable properties of `arguments` objects and
		 * objects created by constructors other than `Object` are cloned to plain `Object` objects.
		 * See http://www.w3.org/TR/html5/infrastructure.html#internal-structured-cloning-algorithm.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to deep clone.
		 * @param {Function} [callback] The function to customize cloning values.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the deep cloned value.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * var deep = _.cloneDeep(characters);
		 * deep[0] === characters[0];
		 * // => false
		 *
		 * var view = {
		 *   'label': 'docs',
		 *   'node': element
		 * };
		 *
		 * var clone = _.cloneDeep(view, function(value) {
		 *   return _.isElement(value) ? value.cloneNode(true) : undefined;
		 * });
		 *
		 * clone.node == view.node;
		 * // => false
		 */
		function cloneDeep(value, callback, thisArg) {
			return baseClone(value, true, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 1));
		}

		/**
		 * Creates an object that inherits from the given `prototype` object. If a
		 * `properties` object is provided its own enumerable properties are assigned
		 * to the created object.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} prototype The object to inherit from.
		 * @param {Object} [properties] The properties to assign to the object.
		 * @returns {Object} Returns the new object.
		 * @example
		 *
		 * function Shape() {
		 *   this.x = 0;
		 *   this.y = 0;
		 * }
		 *
		 * function Circle() {
		 *   Shape.call(this);
		 * }
		 *
		 * Circle.prototype = _.create(Shape.prototype, { 'constructor': Circle });
		 *
		 * var circle = new Circle;
		 * circle instanceof Circle;
		 * // => true
		 *
		 * circle instanceof Shape;
		 * // => true
		 */
		function create(prototype, properties) {
			var result = baseCreate(prototype);
			return properties ? assign(result, properties) : result;
		}

		/**
		 * Assigns own enumerable properties of source object(s) to the destination
		 * object for all destination properties that resolve to `undefined`. Once a
		 * property is set, additional defaults of the same property will be ignored.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Objects
		 * @param {Object} object The destination object.
		 * @param {...Object} [source] The source objects.
		 * @param- {Object} [guard] Allows working with `_.reduce` without using its
		 *  `key` and `object` arguments as sources.
		 * @returns {Object} Returns the destination object.
		 * @example
		 *
		 * var object = { 'name': 'barney' };
		 * _.defaults(object, { 'name': 'fred', 'employer': 'slate' });
		 * // => { 'name': 'barney', 'employer': 'slate' }
		 */
		var defaults = function(object, source, guard) {
			var index, iterable = object, result = iterable;
			if (!iterable) return result;
			var args = arguments,
					argsIndex = 0,
					argsLength = typeof guard == 'number' ? 2 : args.length;
			while (++argsIndex < argsLength) {
				iterable = args[argsIndex];
				if (iterable && objectTypes[typeof iterable]) {
				var ownIndex = -1,
						ownProps = objectTypes[typeof iterable] && keys(iterable),
						length = ownProps ? ownProps.length : 0;

				while (++ownIndex < length) {
					index = ownProps[ownIndex];
					if (typeof result[index] == 'undefined') result[index] = iterable[index];
				}
				}
			}
			return result
		};

		/**
		 * This method is like `_.findIndex` except that it returns the key of the
		 * first element that passes the callback check, instead of the element itself.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to search.
		 * @param {Function|Object|string} [callback=identity] The function called per
		 *  iteration. If a property name or object is provided it will be used to
		 *  create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {string|undefined} Returns the key of the found element, else `undefined`.
		 * @example
		 *
		 * var characters = {
		 *   'barney': {  'age': 36, 'blocked': false },
		 *   'fred': {    'age': 40, 'blocked': true },
		 *   'pebbles': { 'age': 1,  'blocked': false }
		 * };
		 *
		 * _.findKey(characters, function(chr) {
		 *   return chr.age < 40;
		 * });
		 * // => 'barney' (property order is not guaranteed across environments)
		 *
		 * // using "_.where" callback shorthand
		 * _.findKey(characters, { 'age': 1 });
		 * // => 'pebbles'
		 *
		 * // using "_.pluck" callback shorthand
		 * _.findKey(characters, 'blocked');
		 * // => 'fred'
		 */
		function findKey(object, callback, thisArg) {
			var result;
			callback = lodash.createCallback(callback, thisArg, 3);
			forOwn(object, function(value, key, object) {
				if (callback(value, key, object)) {
					result = key;
					return false;
				}
			});
			return result;
		}

		/**
		 * This method is like `_.findKey` except that it iterates over elements
		 * of a `collection` in the opposite order.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to search.
		 * @param {Function|Object|string} [callback=identity] The function called per
		 *  iteration. If a property name or object is provided it will be used to
		 *  create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {string|undefined} Returns the key of the found element, else `undefined`.
		 * @example
		 *
		 * var characters = {
		 *   'barney': {  'age': 36, 'blocked': true },
		 *   'fred': {    'age': 40, 'blocked': false },
		 *   'pebbles': { 'age': 1,  'blocked': true }
		 * };
		 *
		 * _.findLastKey(characters, function(chr) {
		 *   return chr.age < 40;
		 * });
		 * // => returns `pebbles`, assuming `_.findKey` returns `barney`
		 *
		 * // using "_.where" callback shorthand
		 * _.findLastKey(characters, { 'age': 40 });
		 * // => 'fred'
		 *
		 * // using "_.pluck" callback shorthand
		 * _.findLastKey(characters, 'blocked');
		 * // => 'pebbles'
		 */
		function findLastKey(object, callback, thisArg) {
			var result;
			callback = lodash.createCallback(callback, thisArg, 3);
			forOwnRight(object, function(value, key, object) {
				if (callback(value, key, object)) {
					result = key;
					return false;
				}
			});
			return result;
		}

		/**
		 * Iterates over own and inherited enumerable properties of an object,
		 * executing the callback for each property. The callback is bound to `thisArg`
		 * and invoked with three arguments; (value, key, object). Callbacks may exit
		 * iteration early by explicitly returning `false`.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Objects
		 * @param {Object} object The object to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns `object`.
		 * @example
		 *
		 * function Shape() {
		 *   this.x = 0;
		 *   this.y = 0;
		 * }
		 *
		 * Shape.prototype.move = function(x, y) {
		 *   this.x += x;
		 *   this.y += y;
		 * };
		 *
		 * _.forIn(new Shape, function(value, key) {
		 *   console.log(key);
		 * });
		 * // => logs 'x', 'y', and 'move' (property order is not guaranteed across environments)
		 */
		var forIn = function(collection, callback, thisArg) {
			var index, iterable = collection, result = iterable;
			if (!iterable) return result;
			if (!objectTypes[typeof iterable]) return result;
			callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
				for (index in iterable) {
					if (callback(iterable[index], index, collection) === false) return result;
				}
			return result
		};

		/**
		 * This method is like `_.forIn` except that it iterates over elements
		 * of a `collection` in the opposite order.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns `object`.
		 * @example
		 *
		 * function Shape() {
		 *   this.x = 0;
		 *   this.y = 0;
		 * }
		 *
		 * Shape.prototype.move = function(x, y) {
		 *   this.x += x;
		 *   this.y += y;
		 * };
		 *
		 * _.forInRight(new Shape, function(value, key) {
		 *   console.log(key);
		 * });
		 * // => logs 'move', 'y', and 'x' assuming `_.forIn ` logs 'x', 'y', and 'move'
		 */
		function forInRight(object, callback, thisArg) {
			var pairs = [];

			forIn(object, function(value, key) {
				pairs.push(key, value);
			});

			var length = pairs.length;
			callback = baseCreateCallback(callback, thisArg, 3);
			while (length--) {
				if (callback(pairs[length--], pairs[length], object) === false) {
					break;
				}
			}
			return object;
		}

		/**
		 * Iterates over own enumerable properties of an object, executing the callback
		 * for each property. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, key, object). Callbacks may exit iteration early by
		 * explicitly returning `false`.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Objects
		 * @param {Object} object The object to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns `object`.
		 * @example
		 *
		 * _.forOwn({ '0': 'zero', '1': 'one', 'length': 2 }, function(num, key) {
		 *   console.log(key);
		 * });
		 * // => logs '0', '1', and 'length' (property order is not guaranteed across environments)
		 */
		var forOwn = function(collection, callback, thisArg) {
			var index, iterable = collection, result = iterable;
			if (!iterable) return result;
			if (!objectTypes[typeof iterable]) return result;
			callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
				var ownIndex = -1,
						ownProps = objectTypes[typeof iterable] && keys(iterable),
						length = ownProps ? ownProps.length : 0;

				while (++ownIndex < length) {
					index = ownProps[ownIndex];
					if (callback(iterable[index], index, collection) === false) return result;
				}
			return result
		};

		/**
		 * This method is like `_.forOwn` except that it iterates over elements
		 * of a `collection` in the opposite order.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns `object`.
		 * @example
		 *
		 * _.forOwnRight({ '0': 'zero', '1': 'one', 'length': 2 }, function(num, key) {
		 *   console.log(key);
		 * });
		 * // => logs 'length', '1', and '0' assuming `_.forOwn` logs '0', '1', and 'length'
		 */
		function forOwnRight(object, callback, thisArg) {
			var props = keys(object),
					length = props.length;

			callback = baseCreateCallback(callback, thisArg, 3);
			while (length--) {
				var key = props[length];
				if (callback(object[key], key, object) === false) {
					break;
				}
			}
			return object;
		}

		/**
		 * Creates a sorted array of property names of all enumerable properties,
		 * own and inherited, of `object` that have function values.
		 *
		 * @static
		 * @memberOf _
		 * @alias methods
		 * @category Objects
		 * @param {Object} object The object to inspect.
		 * @returns {Array} Returns an array of property names that have function values.
		 * @example
		 *
		 * _.functions(_);
		 * // => ['all', 'any', 'bind', 'bindAll', 'clone', 'compact', 'compose', ...]
		 */
		function functions(object) {
			var result = [];
			forIn(object, function(value, key) {
				if (isFunction(value)) {
					result.push(key);
				}
			});
			return result.sort();
		}

		/**
		 * Checks if the specified property name exists as a direct property of `object`,
		 * instead of an inherited property.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to inspect.
		 * @param {string} key The name of the property to check.
		 * @returns {boolean} Returns `true` if key is a direct property, else `false`.
		 * @example
		 *
		 * _.has({ 'a': 1, 'b': 2, 'c': 3 }, 'b');
		 * // => true
		 */
		function has(object, key) {
			return object ? hasOwnProperty.call(object, key) : false;
		}

		/**
		 * Creates an object composed of the inverted keys and values of the given object.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to invert.
		 * @returns {Object} Returns the created inverted object.
		 * @example
		 *
		 * _.invert({ 'first': 'fred', 'second': 'barney' });
		 * // => { 'fred': 'first', 'barney': 'second' }
		 */
		function invert(object) {
			var index = -1,
					props = keys(object),
					length = props.length,
					result = {};

			while (++index < length) {
				var key = props[index];
				result[object[key]] = key;
			}
			return result;
		}

		/**
		 * Checks if `value` is a boolean value.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a boolean value, else `false`.
		 * @example
		 *
		 * _.isBoolean(null);
		 * // => false
		 */
		function isBoolean(value) {
			return value === true || value === false ||
				value && typeof value == 'object' && toString.call(value) == boolClass || false;
		}

		/**
		 * Checks if `value` is a date.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a date, else `false`.
		 * @example
		 *
		 * _.isDate(new Date);
		 * // => true
		 */
		function isDate(value) {
			return value && typeof value == 'object' && toString.call(value) == dateClass || false;
		}

		/**
		 * Checks if `value` is a DOM element.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a DOM element, else `false`.
		 * @example
		 *
		 * _.isElement(document.body);
		 * // => true
		 */
		function isElement(value) {
			return value && value.nodeType === 1 || false;
		}

		/**
		 * Checks if `value` is empty. Arrays, strings, or `arguments` objects with a
		 * length of `0` and objects with no own enumerable properties are considered
		 * "empty".
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Array|Object|string} value The value to inspect.
		 * @returns {boolean} Returns `true` if the `value` is empty, else `false`.
		 * @example
		 *
		 * _.isEmpty([1, 2, 3]);
		 * // => false
		 *
		 * _.isEmpty({});
		 * // => true
		 *
		 * _.isEmpty('');
		 * // => true
		 */
		function isEmpty(value) {
			var result = true;
			if (!value) {
				return result;
			}
			var className = toString.call(value),
					length = value.length;

			if ((className == arrayClass || className == stringClass || className == argsClass ) ||
					(className == objectClass && typeof length == 'number' && isFunction(value.splice))) {
				return !length;
			}
			forOwn(value, function() {
				return (result = false);
			});
			return result;
		}

		/**
		 * Performs a deep comparison between two values to determine if they are
		 * equivalent to each other. If a callback is provided it will be executed
		 * to compare values. If the callback returns `undefined` comparisons will
		 * be handled by the method instead. The callback is bound to `thisArg` and
		 * invoked with two arguments; (a, b).
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} a The value to compare.
		 * @param {*} b The other value to compare.
		 * @param {Function} [callback] The function to customize comparing values.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {boolean} Returns `true` if the values are equivalent, else `false`.
		 * @example
		 *
		 * var object = { 'name': 'fred' };
		 * var copy = { 'name': 'fred' };
		 *
		 * object == copy;
		 * // => false
		 *
		 * _.isEqual(object, copy);
		 * // => true
		 *
		 * var words = ['hello', 'goodbye'];
		 * var otherWords = ['hi', 'goodbye'];
		 *
		 * _.isEqual(words, otherWords, function(a, b) {
		 *   var reGreet = /^(?:hello|hi)$/i,
		 *       aGreet = _.isString(a) && reGreet.test(a),
		 *       bGreet = _.isString(b) && reGreet.test(b);
		 *
		 *   return (aGreet || bGreet) ? (aGreet == bGreet) : undefined;
		 * });
		 * // => true
		 */
		function isEqual(a, b, callback, thisArg) {
			return baseIsEqual(a, b, typeof callback == 'function' && baseCreateCallback(callback, thisArg, 2));
		}

		/**
		 * Checks if `value` is, or can be coerced to, a finite number.
		 *
		 * Note: This is not the same as native `isFinite` which will return true for
		 * booleans and empty strings. See http://es5.github.io/#x15.1.2.5.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is finite, else `false`.
		 * @example
		 *
		 * _.isFinite(-101);
		 * // => true
		 *
		 * _.isFinite('10');
		 * // => true
		 *
		 * _.isFinite(true);
		 * // => false
		 *
		 * _.isFinite('');
		 * // => false
		 *
		 * _.isFinite(Infinity);
		 * // => false
		 */
		function isFinite(value) {
			return nativeIsFinite(value) && !nativeIsNaN(parseFloat(value));
		}

		/**
		 * Checks if `value` is a function.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a function, else `false`.
		 * @example
		 *
		 * _.isFunction(_);
		 * // => true
		 */
		function isFunction(value) {
			return typeof value == 'function';
		}

		/**
		 * Checks if `value` is the language type of Object.
		 * (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is an object, else `false`.
		 * @example
		 *
		 * _.isObject({});
		 * // => true
		 *
		 * _.isObject([1, 2, 3]);
		 * // => true
		 *
		 * _.isObject(1);
		 * // => false
		 */
		function isObject(value) {
			// check if the value is the ECMAScript language type of Object
			// http://es5.github.io/#x8
			// and avoid a V8 bug
			// http://code.google.com/p/v8/issues/detail?id=2291
			return !!(value && objectTypes[typeof value]);
		}

		/**
		 * Checks if `value` is `NaN`.
		 *
		 * Note: This is not the same as native `isNaN` which will return `true` for
		 * `undefined` and other non-numeric values. See http://es5.github.io/#x15.1.2.4.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is `NaN`, else `false`.
		 * @example
		 *
		 * _.isNaN(NaN);
		 * // => true
		 *
		 * _.isNaN(new Number(NaN));
		 * // => true
		 *
		 * isNaN(undefined);
		 * // => true
		 *
		 * _.isNaN(undefined);
		 * // => false
		 */
		function isNaN(value) {
			// `NaN` as a primitive is the only value that is not equal to itself
			// (perform the [[Class]] check first to avoid errors with some host objects in IE)
			return isNumber(value) && value != +value;
		}

		/**
		 * Checks if `value` is `null`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is `null`, else `false`.
		 * @example
		 *
		 * _.isNull(null);
		 * // => true
		 *
		 * _.isNull(undefined);
		 * // => false
		 */
		function isNull(value) {
			return value === null;
		}

		/**
		 * Checks if `value` is a number.
		 *
		 * Note: `NaN` is considered a number. See http://es5.github.io/#x8.5.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a number, else `false`.
		 * @example
		 *
		 * _.isNumber(8.4 * 5);
		 * // => true
		 */
		function isNumber(value) {
			return typeof value == 'number' ||
				value && typeof value == 'object' && toString.call(value) == numberClass || false;
		}

		/**
		 * Checks if `value` is an object created by the `Object` constructor.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if `value` is a plain object, else `false`.
		 * @example
		 *
		 * function Shape() {
		 *   this.x = 0;
		 *   this.y = 0;
		 * }
		 *
		 * _.isPlainObject(new Shape);
		 * // => false
		 *
		 * _.isPlainObject([1, 2, 3]);
		 * // => false
		 *
		 * _.isPlainObject({ 'x': 0, 'y': 0 });
		 * // => true
		 */
		var isPlainObject = !getPrototypeOf ? shimIsPlainObject : function(value) {
			if (!(value && toString.call(value) == objectClass)) {
				return false;
			}
			var valueOf = value.valueOf,
					objProto = isNative(valueOf) && (objProto = getPrototypeOf(valueOf)) && getPrototypeOf(objProto);

			return objProto
				? (value == objProto || getPrototypeOf(value) == objProto)
				: shimIsPlainObject(value);
		};

		/**
		 * Checks if `value` is a regular expression.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a regular expression, else `false`.
		 * @example
		 *
		 * _.isRegExp(/fred/);
		 * // => true
		 */
		function isRegExp(value) {
			return value && typeof value == 'object' && toString.call(value) == regexpClass || false;
		}

		/**
		 * Checks if `value` is a string.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is a string, else `false`.
		 * @example
		 *
		 * _.isString('fred');
		 * // => true
		 */
		function isString(value) {
			return typeof value == 'string' ||
				value && typeof value == 'object' && toString.call(value) == stringClass || false;
		}

		/**
		 * Checks if `value` is `undefined`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {*} value The value to check.
		 * @returns {boolean} Returns `true` if the `value` is `undefined`, else `false`.
		 * @example
		 *
		 * _.isUndefined(void 0);
		 * // => true
		 */
		function isUndefined(value) {
			return typeof value == 'undefined';
		}

		/**
		 * Creates an object with the same keys as `object` and values generated by
		 * running each own enumerable property of `object` through the callback.
		 * The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, key, object).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new object with values of the results of each `callback` execution.
		 * @example
		 *
		 * _.mapValues({ 'a': 1, 'b': 2, 'c': 3} , function(num) { return num * 3; });
		 * // => { 'a': 3, 'b': 6, 'c': 9 }
		 *
		 * var characters = {
		 *   'fred': { 'name': 'fred', 'age': 40 },
		 *   'pebbles': { 'name': 'pebbles', 'age': 1 }
		 * };
		 *
		 * // using "_.pluck" callback shorthand
		 * _.mapValues(characters, 'age');
		 * // => { 'fred': 40, 'pebbles': 1 }
		 */
		function mapValues(object, callback, thisArg) {
			var result = {};
			callback = lodash.createCallback(callback, thisArg, 3);

			forOwn(object, function(value, key, object) {
				result[key] = callback(value, key, object);
			});
			return result;
		}

		/**
		 * Recursively merges own enumerable properties of the source object(s), that
		 * don't resolve to `undefined` into the destination object. Subsequent sources
		 * will overwrite property assignments of previous sources. If a callback is
		 * provided it will be executed to produce the merged values of the destination
		 * and source properties. If the callback returns `undefined` merging will
		 * be handled by the method instead. The callback is bound to `thisArg` and
		 * invoked with two arguments; (objectValue, sourceValue).
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The destination object.
		 * @param {...Object} [source] The source objects.
		 * @param {Function} [callback] The function to customize merging properties.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns the destination object.
		 * @example
		 *
		 * var names = {
		 *   'characters': [
		 *     { 'name': 'barney' },
		 *     { 'name': 'fred' }
		 *   ]
		 * };
		 *
		 * var ages = {
		 *   'characters': [
		 *     { 'age': 36 },
		 *     { 'age': 40 }
		 *   ]
		 * };
		 *
		 * _.merge(names, ages);
		 * // => { 'characters': [{ 'name': 'barney', 'age': 36 }, { 'name': 'fred', 'age': 40 }] }
		 *
		 * var food = {
		 *   'fruits': ['apple'],
		 *   'vegetables': ['beet']
		 * };
		 *
		 * var otherFood = {
		 *   'fruits': ['banana'],
		 *   'vegetables': ['carrot']
		 * };
		 *
		 * _.merge(food, otherFood, function(a, b) {
		 *   return _.isArray(a) ? a.concat(b) : undefined;
		 * });
		 * // => { 'fruits': ['apple', 'banana'], 'vegetables': ['beet', 'carrot] }
		 */
		function merge(object) {
			var args = arguments,
					length = 2;

			if (!isObject(object)) {
				return object;
			}
			// allows working with `_.reduce` and `_.reduceRight` without using
			// their `index` and `collection` arguments
			if (typeof args[2] != 'number') {
				length = args.length;
			}
			if (length > 3 && typeof args[length - 2] == 'function') {
				var callback = baseCreateCallback(args[--length - 1], args[length--], 2);
			} else if (length > 2 && typeof args[length - 1] == 'function') {
				callback = args[--length];
			}
			var sources = slice(arguments, 1, length),
					index = -1,
					stackA = getArray(),
					stackB = getArray();

			while (++index < length) {
				baseMerge(object, sources[index], callback, stackA, stackB);
			}
			releaseArray(stackA);
			releaseArray(stackB);
			return object;
		}

		/**
		 * Creates a shallow clone of `object` excluding the specified properties.
		 * Property names may be specified as individual arguments or as arrays of
		 * property names. If a callback is provided it will be executed for each
		 * property of `object` omitting the properties the callback returns truey
		 * for. The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, key, object).
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The source object.
		 * @param {Function|...string|string[]} [callback] The properties to omit or the
		 *  function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns an object without the omitted properties.
		 * @example
		 *
		 * _.omit({ 'name': 'fred', 'age': 40 }, 'age');
		 * // => { 'name': 'fred' }
		 *
		 * _.omit({ 'name': 'fred', 'age': 40 }, function(value) {
		 *   return typeof value == 'number';
		 * });
		 * // => { 'name': 'fred' }
		 */
		function omit(object, callback, thisArg) {
			var result = {};
			if (typeof callback != 'function') {
				var props = [];
				forIn(object, function(value, key) {
					props.push(key);
				});
				props = baseDifference(props, baseFlatten(arguments, true, false, 1));

				var index = -1,
						length = props.length;

				while (++index < length) {
					var key = props[index];
					result[key] = object[key];
				}
			} else {
				callback = lodash.createCallback(callback, thisArg, 3);
				forIn(object, function(value, key, object) {
					if (!callback(value, key, object)) {
						result[key] = value;
					}
				});
			}
			return result;
		}

		/**
		 * Creates a two dimensional array of an object's key-value pairs,
		 * i.e. `[[key1, value1], [key2, value2]]`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to inspect.
		 * @returns {Array} Returns new array of key-value pairs.
		 * @example
		 *
		 * _.pairs({ 'barney': 36, 'fred': 40 });
		 * // => [['barney', 36], ['fred', 40]] (property order is not guaranteed across environments)
		 */
		function pairs(object) {
			var index = -1,
					props = keys(object),
					length = props.length,
					result = Array(length);

			while (++index < length) {
				var key = props[index];
				result[index] = [key, object[key]];
			}
			return result;
		}

		/**
		 * Creates a shallow clone of `object` composed of the specified properties.
		 * Property names may be specified as individual arguments or as arrays of
		 * property names. If a callback is provided it will be executed for each
		 * property of `object` picking the properties the callback returns truey
		 * for. The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, key, object).
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The source object.
		 * @param {Function|...string|string[]} [callback] The function called per
		 *  iteration or property names to pick, specified as individual property
		 *  names or arrays of property names.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns an object composed of the picked properties.
		 * @example
		 *
		 * _.pick({ 'name': 'fred', '_userid': 'fred1' }, 'name');
		 * // => { 'name': 'fred' }
		 *
		 * _.pick({ 'name': 'fred', '_userid': 'fred1' }, function(value, key) {
		 *   return key.charAt(0) != '_';
		 * });
		 * // => { 'name': 'fred' }
		 */
		function pick(object, callback, thisArg) {
			var result = {};
			if (typeof callback != 'function') {
				var index = -1,
						props = baseFlatten(arguments, true, false, 1),
						length = isObject(object) ? props.length : 0;

				while (++index < length) {
					var key = props[index];
					if (key in object) {
						result[key] = object[key];
					}
				}
			} else {
				callback = lodash.createCallback(callback, thisArg, 3);
				forIn(object, function(value, key, object) {
					if (callback(value, key, object)) {
						result[key] = value;
					}
				});
			}
			return result;
		}

		/**
		 * An alternative to `_.reduce` this method transforms `object` to a new
		 * `accumulator` object which is the result of running each of its own
		 * enumerable properties through a callback, with each callback execution
		 * potentially mutating the `accumulator` object. The callback is bound to
		 * `thisArg` and invoked with four arguments; (accumulator, value, key, object).
		 * Callbacks may exit iteration early by explicitly returning `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Array|Object} object The object to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [accumulator] The custom accumulator value.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the accumulated value.
		 * @example
		 *
		 * var squares = _.transform([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], function(result, num) {
		 *   num *= num;
		 *   if (num % 2) {
		 *     return result.push(num) < 3;
		 *   }
		 * });
		 * // => [1, 9, 25]
		 *
		 * var mapped = _.transform({ 'a': 1, 'b': 2, 'c': 3 }, function(result, num, key) {
		 *   result[key] = num * 3;
		 * });
		 * // => { 'a': 3, 'b': 6, 'c': 9 }
		 */
		function transform(object, callback, accumulator, thisArg) {
			var isArr = isArray(object);
			if (accumulator == null) {
				if (isArr) {
					accumulator = [];
				} else {
					var ctor = object && object.constructor,
							proto = ctor && ctor.prototype;

					accumulator = baseCreate(proto);
				}
			}
			if (callback) {
				callback = lodash.createCallback(callback, thisArg, 4);
				(isArr ? forEach : forOwn)(object, function(value, index, object) {
					return callback(accumulator, value, index, object);
				});
			}
			return accumulator;
		}

		/**
		 * Creates an array composed of the own enumerable property values of `object`.
		 *
		 * @static
		 * @memberOf _
		 * @category Objects
		 * @param {Object} object The object to inspect.
		 * @returns {Array} Returns an array of property values.
		 * @example
		 *
		 * _.values({ 'one': 1, 'two': 2, 'three': 3 });
		 * // => [1, 2, 3] (property order is not guaranteed across environments)
		 */
		function values(object) {
			var index = -1,
					props = keys(object),
					length = props.length,
					result = Array(length);

			while (++index < length) {
				result[index] = object[props[index]];
			}
			return result;
		}

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates an array of elements from the specified indexes, or keys, of the
		 * `collection`. Indexes may be specified as individual arguments or as arrays
		 * of indexes.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {...(number|number[]|string|string[])} [index] The indexes of `collection`
		 *   to retrieve, specified as individual indexes or arrays of indexes.
		 * @returns {Array} Returns a new array of elements corresponding to the
		 *  provided indexes.
		 * @example
		 *
		 * _.at(['a', 'b', 'c', 'd', 'e'], [0, 2, 4]);
		 * // => ['a', 'c', 'e']
		 *
		 * _.at(['fred', 'barney', 'pebbles'], 0, 2);
		 * // => ['fred', 'pebbles']
		 */
		function at(collection) {
			var args = arguments,
					index = -1,
					props = baseFlatten(args, true, false, 1),
					length = (args[2] && args[2][args[1]] === collection) ? 1 : props.length,
					result = Array(length);

			while(++index < length) {
				result[index] = collection[props[index]];
			}
			return result;
		}

		/**
		 * Checks if a given value is present in a collection using strict equality
		 * for comparisons, i.e. `===`. If `fromIndex` is negative, it is used as the
		 * offset from the end of the collection.
		 *
		 * @static
		 * @memberOf _
		 * @alias include
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {*} target The value to check for.
		 * @param {number} [fromIndex=0] The index to search from.
		 * @returns {boolean} Returns `true` if the `target` element is found, else `false`.
		 * @example
		 *
		 * _.contains([1, 2, 3], 1);
		 * // => true
		 *
		 * _.contains([1, 2, 3], 1, 2);
		 * // => false
		 *
		 * _.contains({ 'name': 'fred', 'age': 40 }, 'fred');
		 * // => true
		 *
		 * _.contains('pebbles', 'eb');
		 * // => true
		 */
		function contains(collection, target, fromIndex) {
			var index = -1,
					indexOf = getIndexOf(),
					length = collection ? collection.length : 0,
					result = false;

			fromIndex = (fromIndex < 0 ? nativeMax(0, length + fromIndex) : fromIndex) || 0;
			if (isArray(collection)) {
				result = indexOf(collection, target, fromIndex) > -1;
			} else if (typeof length == 'number') {
				result = (isString(collection) ? collection.indexOf(target, fromIndex) : indexOf(collection, target, fromIndex)) > -1;
			} else {
				forOwn(collection, function(value) {
					if (++index >= fromIndex) {
						return !(result = value === target);
					}
				});
			}
			return result;
		}

		/**
		 * Creates an object composed of keys generated from the results of running
		 * each element of `collection` through the callback. The corresponding value
		 * of each key is the number of times the key was returned by the callback.
		 * The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns the composed aggregate object.
		 * @example
		 *
		 * _.countBy([4.3, 6.1, 6.4], function(num) { return Math.floor(num); });
		 * // => { '4': 1, '6': 2 }
		 *
		 * _.countBy([4.3, 6.1, 6.4], function(num) { return this.floor(num); }, Math);
		 * // => { '4': 1, '6': 2 }
		 *
		 * _.countBy(['one', 'two', 'three'], 'length');
		 * // => { '3': 2, '5': 1 }
		 */
		var countBy = createAggregator(function(result, value, key) {
			(hasOwnProperty.call(result, key) ? result[key]++ : result[key] = 1);
		});

		/**
		 * Checks if the given callback returns truey value for **all** elements of
		 * a collection. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias all
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {boolean} Returns `true` if all elements passed the callback check,
		 *  else `false`.
		 * @example
		 *
		 * _.every([true, 1, null, 'yes']);
		 * // => false
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.every(characters, 'age');
		 * // => true
		 *
		 * // using "_.where" callback shorthand
		 * _.every(characters, { 'age': 36 });
		 * // => false
		 */
		function every(collection, callback, thisArg) {
			var result = true;
			callback = lodash.createCallback(callback, thisArg, 3);

			var index = -1,
					length = collection ? collection.length : 0;

			if (typeof length == 'number') {
				while (++index < length) {
					if (!(result = !!callback(collection[index], index, collection))) {
						break;
					}
				}
			} else {
				forOwn(collection, function(value, index, collection) {
					return (result = !!callback(value, index, collection));
				});
			}
			return result;
		}

		/**
		 * Iterates over elements of a collection, returning an array of all elements
		 * the callback returns truey for. The callback is bound to `thisArg` and
		 * invoked with three arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias select
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new array of elements that passed the callback check.
		 * @example
		 *
		 * var evens = _.filter([1, 2, 3, 4, 5, 6], function(num) { return num % 2 == 0; });
		 * // => [2, 4, 6]
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36, 'blocked': false },
		 *   { 'name': 'fred',   'age': 40, 'blocked': true }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.filter(characters, 'blocked');
		 * // => [{ 'name': 'fred', 'age': 40, 'blocked': true }]
		 *
		 * // using "_.where" callback shorthand
		 * _.filter(characters, { 'age': 36 });
		 * // => [{ 'name': 'barney', 'age': 36, 'blocked': false }]
		 */
		function filter(collection, callback, thisArg) {
			var result = [];
			callback = lodash.createCallback(callback, thisArg, 3);

			var index = -1,
					length = collection ? collection.length : 0;

			if (typeof length == 'number') {
				while (++index < length) {
					var value = collection[index];
					if (callback(value, index, collection)) {
						result.push(value);
					}
				}
			} else {
				forOwn(collection, function(value, index, collection) {
					if (callback(value, index, collection)) {
						result.push(value);
					}
				});
			}
			return result;
		}

		/**
		 * Iterates over elements of a collection, returning the first element that
		 * the callback returns truey for. The callback is bound to `thisArg` and
		 * invoked with three arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias detect, findWhere
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the found element, else `undefined`.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'age': 36, 'blocked': false },
		 *   { 'name': 'fred',    'age': 40, 'blocked': true },
		 *   { 'name': 'pebbles', 'age': 1,  'blocked': false }
		 * ];
		 *
		 * _.find(characters, function(chr) {
		 *   return chr.age < 40;
		 * });
		 * // => { 'name': 'barney', 'age': 36, 'blocked': false }
		 *
		 * // using "_.where" callback shorthand
		 * _.find(characters, { 'age': 1 });
		 * // =>  { 'name': 'pebbles', 'age': 1, 'blocked': false }
		 *
		 * // using "_.pluck" callback shorthand
		 * _.find(characters, 'blocked');
		 * // => { 'name': 'fred', 'age': 40, 'blocked': true }
		 */
		function find(collection, callback, thisArg) {
			callback = lodash.createCallback(callback, thisArg, 3);

			var index = -1,
					length = collection ? collection.length : 0;

			if (typeof length == 'number') {
				while (++index < length) {
					var value = collection[index];
					if (callback(value, index, collection)) {
						return value;
					}
				}
			} else {
				var result;
				forOwn(collection, function(value, index, collection) {
					if (callback(value, index, collection)) {
						result = value;
						return false;
					}
				});
				return result;
			}
		}

		/**
		 * This method is like `_.find` except that it iterates over elements
		 * of a `collection` from right to left.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the found element, else `undefined`.
		 * @example
		 *
		 * _.findLast([1, 2, 3, 4], function(num) {
		 *   return num % 2 == 1;
		 * });
		 * // => 3
		 */
		function findLast(collection, callback, thisArg) {
			var result;
			callback = lodash.createCallback(callback, thisArg, 3);
			forEachRight(collection, function(value, index, collection) {
				if (callback(value, index, collection)) {
					result = value;
					return false;
				}
			});
			return result;
		}

		/**
		 * Iterates over elements of a collection, executing the callback for each
		 * element. The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, index|key, collection). Callbacks may exit iteration early by
		 * explicitly returning `false`.
		 *
		 * Note: As with other "Collections" methods, objects with a `length` property
		 * are iterated like arrays. To avoid this behavior `_.forIn` or `_.forOwn`
		 * may be used for object iteration.
		 *
		 * @static
		 * @memberOf _
		 * @alias each
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array|Object|string} Returns `collection`.
		 * @example
		 *
		 * _([1, 2, 3]).forEach(function(num) { console.log(num); }).join(',');
		 * // => logs each number and returns '1,2,3'
		 *
		 * _.forEach({ 'one': 1, 'two': 2, 'three': 3 }, function(num) { console.log(num); });
		 * // => logs each number and returns the object (property order is not guaranteed across environments)
		 */
		function forEach(collection, callback, thisArg) {
			var index = -1,
					length = collection ? collection.length : 0;

			callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
			if (typeof length == 'number') {
				while (++index < length) {
					if (callback(collection[index], index, collection) === false) {
						break;
					}
				}
			} else {
				forOwn(collection, callback);
			}
			return collection;
		}

		/**
		 * This method is like `_.forEach` except that it iterates over elements
		 * of a `collection` from right to left.
		 *
		 * @static
		 * @memberOf _
		 * @alias eachRight
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array|Object|string} Returns `collection`.
		 * @example
		 *
		 * _([1, 2, 3]).forEachRight(function(num) { console.log(num); }).join(',');
		 * // => logs each number from right to left and returns '3,2,1'
		 */
		function forEachRight(collection, callback, thisArg) {
			var length = collection ? collection.length : 0;
			callback = callback && typeof thisArg == 'undefined' ? callback : baseCreateCallback(callback, thisArg, 3);
			if (typeof length == 'number') {
				while (length--) {
					if (callback(collection[length], length, collection) === false) {
						break;
					}
				}
			} else {
				var props = keys(collection);
				length = props.length;
				forOwn(collection, function(value, key, collection) {
					key = props ? props[--length] : --length;
					return callback(collection[key], key, collection);
				});
			}
			return collection;
		}

		/**
		 * Creates an object composed of keys generated from the results of running
		 * each element of a collection through the callback. The corresponding value
		 * of each key is an array of the elements responsible for generating the key.
		 * The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns the composed aggregate object.
		 * @example
		 *
		 * _.groupBy([4.2, 6.1, 6.4], function(num) { return Math.floor(num); });
		 * // => { '4': [4.2], '6': [6.1, 6.4] }
		 *
		 * _.groupBy([4.2, 6.1, 6.4], function(num) { return this.floor(num); }, Math);
		 * // => { '4': [4.2], '6': [6.1, 6.4] }
		 *
		 * // using "_.pluck" callback shorthand
		 * _.groupBy(['one', 'two', 'three'], 'length');
		 * // => { '3': ['one', 'two'], '5': ['three'] }
		 */
		var groupBy = createAggregator(function(result, value, key) {
			(hasOwnProperty.call(result, key) ? result[key] : result[key] = []).push(value);
		});

		/**
		 * Creates an object composed of keys generated from the results of running
		 * each element of the collection through the given callback. The corresponding
		 * value of each key is the last element responsible for generating the key.
		 * The callback is bound to `thisArg` and invoked with three arguments;
		 * (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Object} Returns the composed aggregate object.
		 * @example
		 *
		 * var keys = [
		 *   { 'dir': 'left', 'code': 97 },
		 *   { 'dir': 'right', 'code': 100 }
		 * ];
		 *
		 * _.indexBy(keys, 'dir');
		 * // => { 'left': { 'dir': 'left', 'code': 97 }, 'right': { 'dir': 'right', 'code': 100 } }
		 *
		 * _.indexBy(keys, function(key) { return String.fromCharCode(key.code); });
		 * // => { 'a': { 'dir': 'left', 'code': 97 }, 'd': { 'dir': 'right', 'code': 100 } }
		 *
		 * _.indexBy(characters, function(key) { this.fromCharCode(key.code); }, String);
		 * // => { 'a': { 'dir': 'left', 'code': 97 }, 'd': { 'dir': 'right', 'code': 100 } }
		 */
		var indexBy = createAggregator(function(result, value, key) {
			result[key] = value;
		});

		/**
		 * Invokes the method named by `methodName` on each element in the `collection`
		 * returning an array of the results of each invoked method. Additional arguments
		 * will be provided to each invoked method. If `methodName` is a function it
		 * will be invoked for, and `this` bound to, each element in the `collection`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|string} methodName The name of the method to invoke or
		 *  the function invoked per iteration.
		 * @param {...*} [arg] Arguments to invoke the method with.
		 * @returns {Array} Returns a new array of the results of each invoked method.
		 * @example
		 *
		 * _.invoke([[5, 1, 7], [3, 2, 1]], 'sort');
		 * // => [[1, 5, 7], [1, 2, 3]]
		 *
		 * _.invoke([123, 456], String.prototype.split, '');
		 * // => [['1', '2', '3'], ['4', '5', '6']]
		 */
		function invoke(collection, methodName) {
			var args = slice(arguments, 2),
					index = -1,
					isFunc = typeof methodName == 'function',
					length = collection ? collection.length : 0,
					result = Array(typeof length == 'number' ? length : 0);

			forEach(collection, function(value) {
				result[++index] = (isFunc ? methodName : value[methodName]).apply(value, args);
			});
			return result;
		}

		/**
		 * Creates an array of values by running each element in the collection
		 * through the callback. The callback is bound to `thisArg` and invoked with
		 * three arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias collect
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new array of the results of each `callback` execution.
		 * @example
		 *
		 * _.map([1, 2, 3], function(num) { return num * 3; });
		 * // => [3, 6, 9]
		 *
		 * _.map({ 'one': 1, 'two': 2, 'three': 3 }, function(num) { return num * 3; });
		 * // => [3, 6, 9] (property order is not guaranteed across environments)
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.map(characters, 'name');
		 * // => ['barney', 'fred']
		 */
		function map(collection, callback, thisArg) {
			var index = -1,
					length = collection ? collection.length : 0;

			callback = lodash.createCallback(callback, thisArg, 3);
			if (typeof length == 'number') {
				var result = Array(length);
				while (++index < length) {
					result[index] = callback(collection[index], index, collection);
				}
			} else {
				result = [];
				forOwn(collection, function(value, key, collection) {
					result[++index] = callback(value, key, collection);
				});
			}
			return result;
		}

		/**
		 * Retrieves the maximum value of a collection. If the collection is empty or
		 * falsey `-Infinity` is returned. If a callback is provided it will be executed
		 * for each value in the collection to generate the criterion by which the value
		 * is ranked. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, index, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the maximum value.
		 * @example
		 *
		 * _.max([4, 2, 8, 6]);
		 * // => 8
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * _.max(characters, function(chr) { return chr.age; });
		 * // => { 'name': 'fred', 'age': 40 };
		 *
		 * // using "_.pluck" callback shorthand
		 * _.max(characters, 'age');
		 * // => { 'name': 'fred', 'age': 40 };
		 */
		function max(collection, callback, thisArg) {
			var computed = -Infinity,
					result = computed;

			// allows working with functions like `_.map` without using
			// their `index` argument as a callback
			if (typeof callback != 'function' && thisArg && thisArg[callback] === collection) {
				callback = null;
			}
			if (callback == null && isArray(collection)) {
				var index = -1,
						length = collection.length;

				while (++index < length) {
					var value = collection[index];
					if (value > result) {
						result = value;
					}
				}
			} else {
				callback = (callback == null && isString(collection))
					? charAtCallback
					: lodash.createCallback(callback, thisArg, 3);

				forEach(collection, function(value, index, collection) {
					var current = callback(value, index, collection);
					if (current > computed) {
						computed = current;
						result = value;
					}
				});
			}
			return result;
		}

		/**
		 * Retrieves the minimum value of a collection. If the collection is empty or
		 * falsey `Infinity` is returned. If a callback is provided it will be executed
		 * for each value in the collection to generate the criterion by which the value
		 * is ranked. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, index, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the minimum value.
		 * @example
		 *
		 * _.min([4, 2, 8, 6]);
		 * // => 2
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * _.min(characters, function(chr) { return chr.age; });
		 * // => { 'name': 'barney', 'age': 36 };
		 *
		 * // using "_.pluck" callback shorthand
		 * _.min(characters, 'age');
		 * // => { 'name': 'barney', 'age': 36 };
		 */
		function min(collection, callback, thisArg) {
			var computed = Infinity,
					result = computed;

			// allows working with functions like `_.map` without using
			// their `index` argument as a callback
			if (typeof callback != 'function' && thisArg && thisArg[callback] === collection) {
				callback = null;
			}
			if (callback == null && isArray(collection)) {
				var index = -1,
						length = collection.length;

				while (++index < length) {
					var value = collection[index];
					if (value < result) {
						result = value;
					}
				}
			} else {
				callback = (callback == null && isString(collection))
					? charAtCallback
					: lodash.createCallback(callback, thisArg, 3);

				forEach(collection, function(value, index, collection) {
					var current = callback(value, index, collection);
					if (current < computed) {
						computed = current;
						result = value;
					}
				});
			}
			return result;
		}

		/**
		 * Retrieves the value of a specified property from all elements in the collection.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {string} property The name of the property to pluck.
		 * @returns {Array} Returns a new array of property values.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * _.pluck(characters, 'name');
		 * // => ['barney', 'fred']
		 */
		var pluck = map;

		/**
		 * Reduces a collection to a value which is the accumulated result of running
		 * each element in the collection through the callback, where each successive
		 * callback execution consumes the return value of the previous execution. If
		 * `accumulator` is not provided the first element of the collection will be
		 * used as the initial `accumulator` value. The callback is bound to `thisArg`
		 * and invoked with four arguments; (accumulator, value, index|key, collection).
		 *
		 * @static
		 * @memberOf _
		 * @alias foldl, inject
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [accumulator] Initial value of the accumulator.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the accumulated value.
		 * @example
		 *
		 * var sum = _.reduce([1, 2, 3], function(sum, num) {
		 *   return sum + num;
		 * });
		 * // => 6
		 *
		 * var mapped = _.reduce({ 'a': 1, 'b': 2, 'c': 3 }, function(result, num, key) {
		 *   result[key] = num * 3;
		 *   return result;
		 * }, {});
		 * // => { 'a': 3, 'b': 6, 'c': 9 }
		 */
		function reduce(collection, callback, accumulator, thisArg) {
			if (!collection) return accumulator;
			var noaccum = arguments.length < 3;
			callback = lodash.createCallback(callback, thisArg, 4);

			var index = -1,
					length = collection.length;

			if (typeof length == 'number') {
				if (noaccum) {
					accumulator = collection[++index];
				}
				while (++index < length) {
					accumulator = callback(accumulator, collection[index], index, collection);
				}
			} else {
				forOwn(collection, function(value, index, collection) {
					accumulator = noaccum
						? (noaccum = false, value)
						: callback(accumulator, value, index, collection)
				});
			}
			return accumulator;
		}

		/**
		 * This method is like `_.reduce` except that it iterates over elements
		 * of a `collection` from right to left.
		 *
		 * @static
		 * @memberOf _
		 * @alias foldr
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function} [callback=identity] The function called per iteration.
		 * @param {*} [accumulator] Initial value of the accumulator.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the accumulated value.
		 * @example
		 *
		 * var list = [[0, 1], [2, 3], [4, 5]];
		 * var flat = _.reduceRight(list, function(a, b) { return a.concat(b); }, []);
		 * // => [4, 5, 2, 3, 0, 1]
		 */
		function reduceRight(collection, callback, accumulator, thisArg) {
			var noaccum = arguments.length < 3;
			callback = lodash.createCallback(callback, thisArg, 4);
			forEachRight(collection, function(value, index, collection) {
				accumulator = noaccum
					? (noaccum = false, value)
					: callback(accumulator, value, index, collection);
			});
			return accumulator;
		}

		/**
		 * The opposite of `_.filter` this method returns the elements of a
		 * collection that the callback does **not** return truey for.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new array of elements that failed the callback check.
		 * @example
		 *
		 * var odds = _.reject([1, 2, 3, 4, 5, 6], function(num) { return num % 2 == 0; });
		 * // => [1, 3, 5]
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36, 'blocked': false },
		 *   { 'name': 'fred',   'age': 40, 'blocked': true }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.reject(characters, 'blocked');
		 * // => [{ 'name': 'barney', 'age': 36, 'blocked': false }]
		 *
		 * // using "_.where" callback shorthand
		 * _.reject(characters, { 'age': 36 });
		 * // => [{ 'name': 'fred', 'age': 40, 'blocked': true }]
		 */
		function reject(collection, callback, thisArg) {
			callback = lodash.createCallback(callback, thisArg, 3);
			return filter(collection, function(value, index, collection) {
				return !callback(value, index, collection);
			});
		}

		/**
		 * Retrieves a random element or `n` random elements from a collection.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to sample.
		 * @param {number} [n] The number of elements to sample.
		 * @param- {Object} [guard] Allows working with functions like `_.map`
		 *  without using their `index` arguments as `n`.
		 * @returns {Array} Returns the random sample(s) of `collection`.
		 * @example
		 *
		 * _.sample([1, 2, 3, 4]);
		 * // => 2
		 *
		 * _.sample([1, 2, 3, 4], 2);
		 * // => [3, 1]
		 */
		function sample(collection, n, guard) {
			if (collection && typeof collection.length != 'number') {
				collection = values(collection);
			}
			if (n == null || guard) {
				return collection ? collection[baseRandom(0, collection.length - 1)] : undefined;
			}
			var result = shuffle(collection);
			result.length = nativeMin(nativeMax(0, n), result.length);
			return result;
		}

		/**
		 * Creates an array of shuffled values, using a version of the Fisher-Yates
		 * shuffle. See http://en.wikipedia.org/wiki/Fisher-Yates_shuffle.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to shuffle.
		 * @returns {Array} Returns a new shuffled collection.
		 * @example
		 *
		 * _.shuffle([1, 2, 3, 4, 5, 6]);
		 * // => [4, 1, 6, 3, 5, 2]
		 */
		function shuffle(collection) {
			var index = -1,
					length = collection ? collection.length : 0,
					result = Array(typeof length == 'number' ? length : 0);

			forEach(collection, function(value) {
				var rand = baseRandom(0, ++index);
				result[index] = result[rand];
				result[rand] = value;
			});
			return result;
		}

		/**
		 * Gets the size of the `collection` by returning `collection.length` for arrays
		 * and array-like objects or the number of own enumerable properties for objects.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to inspect.
		 * @returns {number} Returns `collection.length` or number of own enumerable properties.
		 * @example
		 *
		 * _.size([1, 2]);
		 * // => 2
		 *
		 * _.size({ 'one': 1, 'two': 2, 'three': 3 });
		 * // => 3
		 *
		 * _.size('pebbles');
		 * // => 7
		 */
		function size(collection) {
			var length = collection ? collection.length : 0;
			return typeof length == 'number' ? length : keys(collection).length;
		}

		/**
		 * Checks if the callback returns a truey value for **any** element of a
		 * collection. The function returns as soon as it finds a passing value and
		 * does not iterate over the entire collection. The callback is bound to
		 * `thisArg` and invoked with three arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias any
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {boolean} Returns `true` if any element passed the callback check,
		 *  else `false`.
		 * @example
		 *
		 * _.some([null, 0, 'yes', false], Boolean);
		 * // => true
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36, 'blocked': false },
		 *   { 'name': 'fred',   'age': 40, 'blocked': true }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.some(characters, 'blocked');
		 * // => true
		 *
		 * // using "_.where" callback shorthand
		 * _.some(characters, { 'age': 1 });
		 * // => false
		 */
		function some(collection, callback, thisArg) {
			var result;
			callback = lodash.createCallback(callback, thisArg, 3);

			var index = -1,
					length = collection ? collection.length : 0;

			if (typeof length == 'number') {
				while (++index < length) {
					if ((result = callback(collection[index], index, collection))) {
						break;
					}
				}
			} else {
				forOwn(collection, function(value, index, collection) {
					return !(result = callback(value, index, collection));
				});
			}
			return !!result;
		}

		/**
		 * Creates an array of elements, sorted in ascending order by the results of
		 * running each element in a collection through the callback. This method
		 * performs a stable sort, that is, it will preserve the original sort order
		 * of equal elements. The callback is bound to `thisArg` and invoked with
		 * three arguments; (value, index|key, collection).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an array of property names is provided for `callback` the collection
		 * will be sorted by each property value.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Array|Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new array of sorted elements.
		 * @example
		 *
		 * _.sortBy([1, 2, 3], function(num) { return Math.sin(num); });
		 * // => [3, 1, 2]
		 *
		 * _.sortBy([1, 2, 3], function(num) { return this.sin(num); }, Math);
		 * // => [3, 1, 2]
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'age': 36 },
		 *   { 'name': 'fred',    'age': 40 },
		 *   { 'name': 'barney',  'age': 26 },
		 *   { 'name': 'fred',    'age': 30 }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.map(_.sortBy(characters, 'age'), _.values);
		 * // => [['barney', 26], ['fred', 30], ['barney', 36], ['fred', 40]]
		 *
		 * // sorting by multiple properties
		 * _.map(_.sortBy(characters, ['name', 'age']), _.values);
		 * // = > [['barney', 26], ['barney', 36], ['fred', 30], ['fred', 40]]
		 */
		function sortBy(collection, callback, thisArg) {
			var index = -1,
					isArr = isArray(callback),
					length = collection ? collection.length : 0,
					result = Array(typeof length == 'number' ? length : 0);

			if (!isArr) {
				callback = lodash.createCallback(callback, thisArg, 3);
			}
			forEach(collection, function(value, key, collection) {
				var object = result[++index] = getObject();
				if (isArr) {
					object.criteria = map(callback, function(key) { return value[key]; });
				} else {
					(object.criteria = getArray())[0] = callback(value, key, collection);
				}
				object.index = index;
				object.value = value;
			});

			length = result.length;
			result.sort(compareAscending);
			while (length--) {
				var object = result[length];
				result[length] = object.value;
				if (!isArr) {
					releaseArray(object.criteria);
				}
				releaseObject(object);
			}
			return result;
		}

		/**
		 * Converts the `collection` to an array.
		 *
		 * @static
		 * @memberOf _
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to convert.
		 * @returns {Array} Returns the new converted array.
		 * @example
		 *
		 * (function() { return _.toArray(arguments).slice(1); })(1, 2, 3, 4);
		 * // => [2, 3, 4]
		 */
		function toArray(collection) {
			if (collection && typeof collection.length == 'number') {
				return slice(collection);
			}
			return values(collection);
		}

		/**
		 * Performs a deep comparison of each element in a `collection` to the given
		 * `properties` object, returning an array of all elements that have equivalent
		 * property values.
		 *
		 * @static
		 * @memberOf _
		 * @type Function
		 * @category Collections
		 * @param {Array|Object|string} collection The collection to iterate over.
		 * @param {Object} props The object of property values to filter by.
		 * @returns {Array} Returns a new array of elements that have the given properties.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36, 'pets': ['hoppy'] },
		 *   { 'name': 'fred',   'age': 40, 'pets': ['baby puss', 'dino'] }
		 * ];
		 *
		 * _.where(characters, { 'age': 36 });
		 * // => [{ 'name': 'barney', 'age': 36, 'pets': ['hoppy'] }]
		 *
		 * _.where(characters, { 'pets': ['dino'] });
		 * // => [{ 'name': 'fred', 'age': 40, 'pets': ['baby puss', 'dino'] }]
		 */
		var where = filter;

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates an array with all falsey values removed. The values `false`, `null`,
		 * `0`, `""`, `undefined`, and `NaN` are all falsey.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to compact.
		 * @returns {Array} Returns a new array of filtered values.
		 * @example
		 *
		 * _.compact([0, 1, false, 2, '', 3]);
		 * // => [1, 2, 3]
		 */
		function compact(array) {
			var index = -1,
					length = array ? array.length : 0,
					result = [];

			while (++index < length) {
				var value = array[index];
				if (value) {
					result.push(value);
				}
			}
			return result;
		}

		/**
		 * Creates an array excluding all values of the provided arrays using strict
		 * equality for comparisons, i.e. `===`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to process.
		 * @param {...Array} [values] The arrays of values to exclude.
		 * @returns {Array} Returns a new array of filtered values.
		 * @example
		 *
		 * _.difference([1, 2, 3, 4, 5], [5, 2, 10]);
		 * // => [1, 3, 4]
		 */
		function difference(array) {
			return baseDifference(array, baseFlatten(arguments, true, true, 1));
		}

		/**
		 * This method is like `_.find` except that it returns the index of the first
		 * element that passes the callback check, instead of the element itself.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to search.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {number} Returns the index of the found element, else `-1`.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'age': 36, 'blocked': false },
		 *   { 'name': 'fred',    'age': 40, 'blocked': true },
		 *   { 'name': 'pebbles', 'age': 1,  'blocked': false }
		 * ];
		 *
		 * _.findIndex(characters, function(chr) {
		 *   return chr.age < 20;
		 * });
		 * // => 2
		 *
		 * // using "_.where" callback shorthand
		 * _.findIndex(characters, { 'age': 36 });
		 * // => 0
		 *
		 * // using "_.pluck" callback shorthand
		 * _.findIndex(characters, 'blocked');
		 * // => 1
		 */
		function findIndex(array, callback, thisArg) {
			var index = -1,
					length = array ? array.length : 0;

			callback = lodash.createCallback(callback, thisArg, 3);
			while (++index < length) {
				if (callback(array[index], index, array)) {
					return index;
				}
			}
			return -1;
		}

		/**
		 * This method is like `_.findIndex` except that it iterates over elements
		 * of a `collection` from right to left.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to search.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {number} Returns the index of the found element, else `-1`.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'age': 36, 'blocked': true },
		 *   { 'name': 'fred',    'age': 40, 'blocked': false },
		 *   { 'name': 'pebbles', 'age': 1,  'blocked': true }
		 * ];
		 *
		 * _.findLastIndex(characters, function(chr) {
		 *   return chr.age > 30;
		 * });
		 * // => 1
		 *
		 * // using "_.where" callback shorthand
		 * _.findLastIndex(characters, { 'age': 36 });
		 * // => 0
		 *
		 * // using "_.pluck" callback shorthand
		 * _.findLastIndex(characters, 'blocked');
		 * // => 2
		 */
		function findLastIndex(array, callback, thisArg) {
			var length = array ? array.length : 0;
			callback = lodash.createCallback(callback, thisArg, 3);
			while (length--) {
				if (callback(array[length], length, array)) {
					return length;
				}
			}
			return -1;
		}

		/**
		 * Gets the first element or first `n` elements of an array. If a callback
		 * is provided elements at the beginning of the array are returned as long
		 * as the callback returns truey. The callback is bound to `thisArg` and
		 * invoked with three arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias head, take
		 * @category Arrays
		 * @param {Array} array The array to query.
		 * @param {Function|Object|number|string} [callback] The function called
		 *  per element or the number of elements to return. If a property name or
		 *  object is provided it will be used to create a "_.pluck" or "_.where"
		 *  style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the first element(s) of `array`.
		 * @example
		 *
		 * _.first([1, 2, 3]);
		 * // => 1
		 *
		 * _.first([1, 2, 3], 2);
		 * // => [1, 2]
		 *
		 * _.first([1, 2, 3], function(num) {
		 *   return num < 3;
		 * });
		 * // => [1, 2]
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'blocked': true,  'employer': 'slate' },
		 *   { 'name': 'fred',    'blocked': false, 'employer': 'slate' },
		 *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.first(characters, 'blocked');
		 * // => [{ 'name': 'barney', 'blocked': true, 'employer': 'slate' }]
		 *
		 * // using "_.where" callback shorthand
		 * _.pluck(_.first(characters, { 'employer': 'slate' }), 'name');
		 * // => ['barney', 'fred']
		 */
		function first(array, callback, thisArg) {
			var n = 0,
					length = array ? array.length : 0;

			if (typeof callback != 'number' && callback != null) {
				var index = -1;
				callback = lodash.createCallback(callback, thisArg, 3);
				while (++index < length && callback(array[index], index, array)) {
					n++;
				}
			} else {
				n = callback;
				if (n == null || thisArg) {
					return array ? array[0] : undefined;
				}
			}
			return slice(array, 0, nativeMin(nativeMax(0, n), length));
		}

		/**
		 * Flattens a nested array (the nesting can be to any depth). If `isShallow`
		 * is truey, the array will only be flattened a single level. If a callback
		 * is provided each element of the array is passed through the callback before
		 * flattening. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to flatten.
		 * @param {boolean} [isShallow=false] A flag to restrict flattening to a single level.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new flattened array.
		 * @example
		 *
		 * _.flatten([1, [2], [3, [[4]]]]);
		 * // => [1, 2, 3, 4];
		 *
		 * _.flatten([1, [2], [3, [[4]]]], true);
		 * // => [1, 2, 3, [[4]]];
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 30, 'pets': ['hoppy'] },
		 *   { 'name': 'fred',   'age': 40, 'pets': ['baby puss', 'dino'] }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.flatten(characters, 'pets');
		 * // => ['hoppy', 'baby puss', 'dino']
		 */
		function flatten(array, isShallow, callback, thisArg) {
			// juggle arguments
			if (typeof isShallow != 'boolean' && isShallow != null) {
				thisArg = callback;
				callback = (typeof isShallow != 'function' && thisArg && thisArg[isShallow] === array) ? null : isShallow;
				isShallow = false;
			}
			if (callback != null) {
				array = map(array, callback, thisArg);
			}
			return baseFlatten(array, isShallow);
		}

		/**
		 * Gets the index at which the first occurrence of `value` is found using
		 * strict equality for comparisons, i.e. `===`. If the array is already sorted
		 * providing `true` for `fromIndex` will run a faster binary search.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to search.
		 * @param {*} value The value to search for.
		 * @param {boolean|number} [fromIndex=0] The index to search from or `true`
		 *  to perform a binary search on a sorted array.
		 * @returns {number} Returns the index of the matched value or `-1`.
		 * @example
		 *
		 * _.indexOf([1, 2, 3, 1, 2, 3], 2);
		 * // => 1
		 *
		 * _.indexOf([1, 2, 3, 1, 2, 3], 2, 3);
		 * // => 4
		 *
		 * _.indexOf([1, 1, 2, 2, 3, 3], 2, true);
		 * // => 2
		 */
		function indexOf(array, value, fromIndex) {
			if (typeof fromIndex == 'number') {
				var length = array ? array.length : 0;
				fromIndex = (fromIndex < 0 ? nativeMax(0, length + fromIndex) : fromIndex || 0);
			} else if (fromIndex) {
				var index = sortedIndex(array, value);
				return array[index] === value ? index : -1;
			}
			return baseIndexOf(array, value, fromIndex);
		}

		/**
		 * Gets all but the last element or last `n` elements of an array. If a
		 * callback is provided elements at the end of the array are excluded from
		 * the result as long as the callback returns truey. The callback is bound
		 * to `thisArg` and invoked with three arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to query.
		 * @param {Function|Object|number|string} [callback=1] The function called
		 *  per element or the number of elements to exclude. If a property name or
		 *  object is provided it will be used to create a "_.pluck" or "_.where"
		 *  style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a slice of `array`.
		 * @example
		 *
		 * _.initial([1, 2, 3]);
		 * // => [1, 2]
		 *
		 * _.initial([1, 2, 3], 2);
		 * // => [1]
		 *
		 * _.initial([1, 2, 3], function(num) {
		 *   return num > 1;
		 * });
		 * // => [1]
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'blocked': false, 'employer': 'slate' },
		 *   { 'name': 'fred',    'blocked': true,  'employer': 'slate' },
		 *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.initial(characters, 'blocked');
		 * // => [{ 'name': 'barney',  'blocked': false, 'employer': 'slate' }]
		 *
		 * // using "_.where" callback shorthand
		 * _.pluck(_.initial(characters, { 'employer': 'na' }), 'name');
		 * // => ['barney', 'fred']
		 */
		function initial(array, callback, thisArg) {
			var n = 0,
					length = array ? array.length : 0;

			if (typeof callback != 'number' && callback != null) {
				var index = length;
				callback = lodash.createCallback(callback, thisArg, 3);
				while (index-- && callback(array[index], index, array)) {
					n++;
				}
			} else {
				n = (callback == null || thisArg) ? 1 : callback || n;
			}
			return slice(array, 0, nativeMin(nativeMax(0, length - n), length));
		}

		/**
		 * Creates an array of unique values present in all provided arrays using
		 * strict equality for comparisons, i.e. `===`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {...Array} [array] The arrays to inspect.
		 * @returns {Array} Returns an array of shared values.
		 * @example
		 *
		 * _.intersection([1, 2, 3], [5, 2, 1, 4], [2, 1]);
		 * // => [1, 2]
		 */
		function intersection() {
			var args = [],
					argsIndex = -1,
					argsLength = arguments.length,
					caches = getArray(),
					indexOf = getIndexOf(),
					trustIndexOf = indexOf === baseIndexOf,
					seen = getArray();

			while (++argsIndex < argsLength) {
				var value = arguments[argsIndex];
				if (isArray(value) || isArguments(value)) {
					args.push(value);
					caches.push(trustIndexOf && value.length >= largeArraySize &&
						createCache(argsIndex ? args[argsIndex] : seen));
				}
			}
			var array = args[0],
					index = -1,
					length = array ? array.length : 0,
					result = [];

			outer:
			while (++index < length) {
				var cache = caches[0];
				value = array[index];

				if ((cache ? cacheIndexOf(cache, value) : indexOf(seen, value)) < 0) {
					argsIndex = argsLength;
					(cache || seen).push(value);
					while (--argsIndex) {
						cache = caches[argsIndex];
						if ((cache ? cacheIndexOf(cache, value) : indexOf(args[argsIndex], value)) < 0) {
							continue outer;
						}
					}
					result.push(value);
				}
			}
			while (argsLength--) {
				cache = caches[argsLength];
				if (cache) {
					releaseObject(cache);
				}
			}
			releaseArray(caches);
			releaseArray(seen);
			return result;
		}

		/**
		 * Gets the last element or last `n` elements of an array. If a callback is
		 * provided elements at the end of the array are returned as long as the
		 * callback returns truey. The callback is bound to `thisArg` and invoked
		 * with three arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to query.
		 * @param {Function|Object|number|string} [callback] The function called
		 *  per element or the number of elements to return. If a property name or
		 *  object is provided it will be used to create a "_.pluck" or "_.where"
		 *  style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {*} Returns the last element(s) of `array`.
		 * @example
		 *
		 * _.last([1, 2, 3]);
		 * // => 3
		 *
		 * _.last([1, 2, 3], 2);
		 * // => [2, 3]
		 *
		 * _.last([1, 2, 3], function(num) {
		 *   return num > 1;
		 * });
		 * // => [2, 3]
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'blocked': false, 'employer': 'slate' },
		 *   { 'name': 'fred',    'blocked': true,  'employer': 'slate' },
		 *   { 'name': 'pebbles', 'blocked': true,  'employer': 'na' }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.pluck(_.last(characters, 'blocked'), 'name');
		 * // => ['fred', 'pebbles']
		 *
		 * // using "_.where" callback shorthand
		 * _.last(characters, { 'employer': 'na' });
		 * // => [{ 'name': 'pebbles', 'blocked': true, 'employer': 'na' }]
		 */
		function last(array, callback, thisArg) {
			var n = 0,
					length = array ? array.length : 0;

			if (typeof callback != 'number' && callback != null) {
				var index = length;
				callback = lodash.createCallback(callback, thisArg, 3);
				while (index-- && callback(array[index], index, array)) {
					n++;
				}
			} else {
				n = callback;
				if (n == null || thisArg) {
					return array ? array[length - 1] : undefined;
				}
			}
			return slice(array, nativeMax(0, length - n));
		}

		/**
		 * Gets the index at which the last occurrence of `value` is found using strict
		 * equality for comparisons, i.e. `===`. If `fromIndex` is negative, it is used
		 * as the offset from the end of the collection.
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to search.
		 * @param {*} value The value to search for.
		 * @param {number} [fromIndex=array.length-1] The index to search from.
		 * @returns {number} Returns the index of the matched value or `-1`.
		 * @example
		 *
		 * _.lastIndexOf([1, 2, 3, 1, 2, 3], 2);
		 * // => 4
		 *
		 * _.lastIndexOf([1, 2, 3, 1, 2, 3], 2, 3);
		 * // => 1
		 */
		function lastIndexOf(array, value, fromIndex) {
			var index = array ? array.length : 0;
			if (typeof fromIndex == 'number') {
				index = (fromIndex < 0 ? nativeMax(0, index + fromIndex) : nativeMin(fromIndex, index - 1)) + 1;
			}
			while (index--) {
				if (array[index] === value) {
					return index;
				}
			}
			return -1;
		}

		/**
		 * Removes all provided values from the given array using strict equality for
		 * comparisons, i.e. `===`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to modify.
		 * @param {...*} [value] The values to remove.
		 * @returns {Array} Returns `array`.
		 * @example
		 *
		 * var array = [1, 2, 3, 1, 2, 3];
		 * _.pull(array, 2, 3);
		 * console.log(array);
		 * // => [1, 1]
		 */
		function pull(array) {
			var args = arguments,
					argsIndex = 0,
					argsLength = args.length,
					length = array ? array.length : 0;

			while (++argsIndex < argsLength) {
				var index = -1,
						value = args[argsIndex];
				while (++index < length) {
					if (array[index] === value) {
						splice.call(array, index--, 1);
						length--;
					}
				}
			}
			return array;
		}

		/**
		 * Creates an array of numbers (positive and/or negative) progressing from
		 * `start` up to but not including `end`. If `start` is less than `stop` a
		 * zero-length range is created unless a negative `step` is specified.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {number} [start=0] The start of the range.
		 * @param {number} end The end of the range.
		 * @param {number} [step=1] The value to increment or decrement by.
		 * @returns {Array} Returns a new range array.
		 * @example
		 *
		 * _.range(4);
		 * // => [0, 1, 2, 3]
		 *
		 * _.range(1, 5);
		 * // => [1, 2, 3, 4]
		 *
		 * _.range(0, 20, 5);
		 * // => [0, 5, 10, 15]
		 *
		 * _.range(0, -4, -1);
		 * // => [0, -1, -2, -3]
		 *
		 * _.range(1, 4, 0);
		 * // => [1, 1, 1]
		 *
		 * _.range(0);
		 * // => []
		 */
		function range(start, end, step) {
			start = +start || 0;
			step = typeof step == 'number' ? step : (+step || 1);

			if (end == null) {
				end = start;
				start = 0;
			}
			// use `Array(length)` so engines like Chakra and V8 avoid slower modes
			// http://youtu.be/XAqIpGU8ZZk#t=17m25s
			var index = -1,
					length = nativeMax(0, ceil((end - start) / (step || 1))),
					result = Array(length);

			while (++index < length) {
				result[index] = start;
				start += step;
			}
			return result;
		}

		/**
		 * Removes all elements from an array that the callback returns truey for
		 * and returns an array of removed elements. The callback is bound to `thisArg`
		 * and invoked with three arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to modify.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a new array of removed elements.
		 * @example
		 *
		 * var array = [1, 2, 3, 4, 5, 6];
		 * var evens = _.remove(array, function(num) { return num % 2 == 0; });
		 *
		 * console.log(array);
		 * // => [1, 3, 5]
		 *
		 * console.log(evens);
		 * // => [2, 4, 6]
		 */
		function remove(array, callback, thisArg) {
			var index = -1,
					length = array ? array.length : 0,
					result = [];

			callback = lodash.createCallback(callback, thisArg, 3);
			while (++index < length) {
				var value = array[index];
				if (callback(value, index, array)) {
					result.push(value);
					splice.call(array, index--, 1);
					length--;
				}
			}
			return result;
		}

		/**
		 * The opposite of `_.initial` this method gets all but the first element or
		 * first `n` elements of an array. If a callback function is provided elements
		 * at the beginning of the array are excluded from the result as long as the
		 * callback returns truey. The callback is bound to `thisArg` and invoked
		 * with three arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias drop, tail
		 * @category Arrays
		 * @param {Array} array The array to query.
		 * @param {Function|Object|number|string} [callback=1] The function called
		 *  per element or the number of elements to exclude. If a property name or
		 *  object is provided it will be used to create a "_.pluck" or "_.where"
		 *  style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a slice of `array`.
		 * @example
		 *
		 * _.rest([1, 2, 3]);
		 * // => [2, 3]
		 *
		 * _.rest([1, 2, 3], 2);
		 * // => [3]
		 *
		 * _.rest([1, 2, 3], function(num) {
		 *   return num < 3;
		 * });
		 * // => [3]
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'blocked': true,  'employer': 'slate' },
		 *   { 'name': 'fred',    'blocked': false,  'employer': 'slate' },
		 *   { 'name': 'pebbles', 'blocked': true, 'employer': 'na' }
		 * ];
		 *
		 * // using "_.pluck" callback shorthand
		 * _.pluck(_.rest(characters, 'blocked'), 'name');
		 * // => ['fred', 'pebbles']
		 *
		 * // using "_.where" callback shorthand
		 * _.rest(characters, { 'employer': 'slate' });
		 * // => [{ 'name': 'pebbles', 'blocked': true, 'employer': 'na' }]
		 */
		function rest(array, callback, thisArg) {
			if (typeof callback != 'number' && callback != null) {
				var n = 0,
						index = -1,
						length = array ? array.length : 0;

				callback = lodash.createCallback(callback, thisArg, 3);
				while (++index < length && callback(array[index], index, array)) {
					n++;
				}
			} else {
				n = (callback == null || thisArg) ? 1 : nativeMax(0, callback);
			}
			return slice(array, n);
		}

		/**
		 * Uses a binary search to determine the smallest index at which a value
		 * should be inserted into a given sorted array in order to maintain the sort
		 * order of the array. If a callback is provided it will be executed for
		 * `value` and each element of `array` to compute their sort ranking. The
		 * callback is bound to `thisArg` and invoked with one argument; (value).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to inspect.
		 * @param {*} value The value to evaluate.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {number} Returns the index at which `value` should be inserted
		 *  into `array`.
		 * @example
		 *
		 * _.sortedIndex([20, 30, 50], 40);
		 * // => 2
		 *
		 * // using "_.pluck" callback shorthand
		 * _.sortedIndex([{ 'x': 20 }, { 'x': 30 }, { 'x': 50 }], { 'x': 40 }, 'x');
		 * // => 2
		 *
		 * var dict = {
		 *   'wordToNumber': { 'twenty': 20, 'thirty': 30, 'fourty': 40, 'fifty': 50 }
		 * };
		 *
		 * _.sortedIndex(['twenty', 'thirty', 'fifty'], 'fourty', function(word) {
		 *   return dict.wordToNumber[word];
		 * });
		 * // => 2
		 *
		 * _.sortedIndex(['twenty', 'thirty', 'fifty'], 'fourty', function(word) {
		 *   return this.wordToNumber[word];
		 * }, dict);
		 * // => 2
		 */
		function sortedIndex(array, value, callback, thisArg) {
			var low = 0,
					high = array ? array.length : low;

			// explicitly reference `identity` for better inlining in Firefox
			callback = callback ? lodash.createCallback(callback, thisArg, 1) : identity;
			value = callback(value);

			while (low < high) {
				var mid = (low + high) >>> 1;
				(callback(array[mid]) < value)
					? low = mid + 1
					: high = mid;
			}
			return low;
		}

		/**
		 * Creates an array of unique values, in order, of the provided arrays using
		 * strict equality for comparisons, i.e. `===`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {...Array} [array] The arrays to inspect.
		 * @returns {Array} Returns an array of combined values.
		 * @example
		 *
		 * _.union([1, 2, 3], [5, 2, 1, 4], [2, 1]);
		 * // => [1, 2, 3, 5, 4]
		 */
		function union() {
			return baseUniq(baseFlatten(arguments, true, true));
		}

		/**
		 * Creates a duplicate-value-free version of an array using strict equality
		 * for comparisons, i.e. `===`. If the array is sorted, providing
		 * `true` for `isSorted` will use a faster algorithm. If a callback is provided
		 * each element of `array` is passed through the callback before uniqueness
		 * is computed. The callback is bound to `thisArg` and invoked with three
		 * arguments; (value, index, array).
		 *
		 * If a property name is provided for `callback` the created "_.pluck" style
		 * callback will return the property value of the given element.
		 *
		 * If an object is provided for `callback` the created "_.where" style callback
		 * will return `true` for elements that have the properties of the given object,
		 * else `false`.
		 *
		 * @static
		 * @memberOf _
		 * @alias unique
		 * @category Arrays
		 * @param {Array} array The array to process.
		 * @param {boolean} [isSorted=false] A flag to indicate that `array` is sorted.
		 * @param {Function|Object|string} [callback=identity] The function called
		 *  per iteration. If a property name or object is provided it will be used
		 *  to create a "_.pluck" or "_.where" style callback, respectively.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns a duplicate-value-free array.
		 * @example
		 *
		 * _.uniq([1, 2, 1, 3, 1]);
		 * // => [1, 2, 3]
		 *
		 * _.uniq([1, 1, 2, 2, 3], true);
		 * // => [1, 2, 3]
		 *
		 * _.uniq(['A', 'b', 'C', 'a', 'B', 'c'], function(letter) { return letter.toLowerCase(); });
		 * // => ['A', 'b', 'C']
		 *
		 * _.uniq([1, 2.5, 3, 1.5, 2, 3.5], function(num) { return this.floor(num); }, Math);
		 * // => [1, 2.5, 3]
		 *
		 * // using "_.pluck" callback shorthand
		 * _.uniq([{ 'x': 1 }, { 'x': 2 }, { 'x': 1 }], 'x');
		 * // => [{ 'x': 1 }, { 'x': 2 }]
		 */
		function uniq(array, isSorted, callback, thisArg) {
			// juggle arguments
			if (typeof isSorted != 'boolean' && isSorted != null) {
				thisArg = callback;
				callback = (typeof isSorted != 'function' && thisArg && thisArg[isSorted] === array) ? null : isSorted;
				isSorted = false;
			}
			if (callback != null) {
				callback = lodash.createCallback(callback, thisArg, 3);
			}
			return baseUniq(array, isSorted, callback);
		}

		/**
		 * Creates an array excluding all provided values using strict equality for
		 * comparisons, i.e. `===`.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {Array} array The array to filter.
		 * @param {...*} [value] The values to exclude.
		 * @returns {Array} Returns a new array of filtered values.
		 * @example
		 *
		 * _.without([1, 2, 1, 0, 3, 1, 4], 0, 1);
		 * // => [2, 3, 4]
		 */
		function without(array) {
			return baseDifference(array, slice(arguments, 1));
		}

		/**
		 * Creates an array that is the symmetric difference of the provided arrays.
		 * See http://en.wikipedia.org/wiki/Symmetric_difference.
		 *
		 * @static
		 * @memberOf _
		 * @category Arrays
		 * @param {...Array} [array] The arrays to inspect.
		 * @returns {Array} Returns an array of values.
		 * @example
		 *
		 * _.xor([1, 2, 3], [5, 2, 1, 4]);
		 * // => [3, 5, 4]
		 *
		 * _.xor([1, 2, 5], [2, 3, 5], [3, 4, 5]);
		 * // => [1, 4, 5]
		 */
		function xor() {
			var index = -1,
					length = arguments.length;

			while (++index < length) {
				var array = arguments[index];
				if (isArray(array) || isArguments(array)) {
					var result = result
						? baseUniq(baseDifference(result, array).concat(baseDifference(array, result)))
						: array;
				}
			}
			return result || [];
		}

		/**
		 * Creates an array of grouped elements, the first of which contains the first
		 * elements of the given arrays, the second of which contains the second
		 * elements of the given arrays, and so on.
		 *
		 * @static
		 * @memberOf _
		 * @alias unzip
		 * @category Arrays
		 * @param {...Array} [array] Arrays to process.
		 * @returns {Array} Returns a new array of grouped elements.
		 * @example
		 *
		 * _.zip(['fred', 'barney'], [30, 40], [true, false]);
		 * // => [['fred', 30, true], ['barney', 40, false]]
		 */
		function zip() {
			var array = arguments.length > 1 ? arguments : arguments[0],
					index = -1,
					length = array ? max(pluck(array, 'length')) : 0,
					result = Array(length < 0 ? 0 : length);

			while (++index < length) {
				result[index] = pluck(array, index);
			}
			return result;
		}

		/**
		 * Creates an object composed from arrays of `keys` and `values`. Provide
		 * either a single two dimensional array, i.e. `[[key1, value1], [key2, value2]]`
		 * or two arrays, one of `keys` and one of corresponding `values`.
		 *
		 * @static
		 * @memberOf _
		 * @alias object
		 * @category Arrays
		 * @param {Array} keys The array of keys.
		 * @param {Array} [values=[]] The array of values.
		 * @returns {Object} Returns an object composed of the given keys and
		 *  corresponding values.
		 * @example
		 *
		 * _.zipObject(['fred', 'barney'], [30, 40]);
		 * // => { 'fred': 30, 'barney': 40 }
		 */
		function zipObject(keys, values) {
			var index = -1,
					length = keys ? keys.length : 0,
					result = {};

			if (!values && length && !isArray(keys[0])) {
				values = [];
			}
			while (++index < length) {
				var key = keys[index];
				if (values) {
					result[key] = values[index];
				} else if (key) {
					result[key[0]] = key[1];
				}
			}
			return result;
		}

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates a function that executes `func`, with  the `this` binding and
		 * arguments of the created function, only after being called `n` times.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {number} n The number of times the function must be called before
		 *  `func` is executed.
		 * @param {Function} func The function to restrict.
		 * @returns {Function} Returns the new restricted function.
		 * @example
		 *
		 * var saves = ['profile', 'settings'];
		 *
		 * var done = _.after(saves.length, function() {
		 *   console.log('Done saving!');
		 * });
		 *
		 * _.forEach(saves, function(type) {
		 *   asyncSave({ 'type': type, 'complete': done });
		 * });
		 * // => logs 'Done saving!', after all saves have completed
		 */
		function after(n, func) {
			if (!isFunction(func)) {
				throw new TypeError;
			}
			return function() {
				if (--n < 1) {
					return func.apply(this, arguments);
				}
			};
		}

		/**
		 * Creates a function that, when called, invokes `func` with the `this`
		 * binding of `thisArg` and prepends any additional `bind` arguments to those
		 * provided to the bound function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to bind.
		 * @param {*} [thisArg] The `this` binding of `func`.
		 * @param {...*} [arg] Arguments to be partially applied.
		 * @returns {Function} Returns the new bound function.
		 * @example
		 *
		 * var func = function(greeting) {
		 *   return greeting + ' ' + this.name;
		 * };
		 *
		 * func = _.bind(func, { 'name': 'fred' }, 'hi');
		 * func();
		 * // => 'hi fred'
		 */
		function bind(func, thisArg) {
			return arguments.length > 2
				? createWrapper(func, 17, slice(arguments, 2), null, thisArg)
				: createWrapper(func, 1, null, null, thisArg);
		}

		/**
		 * Binds methods of an object to the object itself, overwriting the existing
		 * method. Method names may be specified as individual arguments or as arrays
		 * of method names. If no method names are provided all the function properties
		 * of `object` will be bound.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Object} object The object to bind and assign the bound methods to.
		 * @param {...string} [methodName] The object method names to
		 *  bind, specified as individual method names or arrays of method names.
		 * @returns {Object} Returns `object`.
		 * @example
		 *
		 * var view = {
		 *   'label': 'docs',
		 *   'onClick': function() { console.log('clicked ' + this.label); }
		 * };
		 *
		 * _.bindAll(view);
		 * jQuery('#docs').on('click', view.onClick);
		 * // => logs 'clicked docs', when the button is clicked
		 */
		function bindAll(object) {
			var funcs = arguments.length > 1 ? baseFlatten(arguments, true, false, 1) : functions(object),
					index = -1,
					length = funcs.length;

			while (++index < length) {
				var key = funcs[index];
				object[key] = createWrapper(object[key], 1, null, null, object);
			}
			return object;
		}

		/**
		 * Creates a function that, when called, invokes the method at `object[key]`
		 * and prepends any additional `bindKey` arguments to those provided to the bound
		 * function. This method differs from `_.bind` by allowing bound functions to
		 * reference methods that will be redefined or don't yet exist.
		 * See http://michaux.ca/articles/lazy-function-definition-pattern.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Object} object The object the method belongs to.
		 * @param {string} key The key of the method.
		 * @param {...*} [arg] Arguments to be partially applied.
		 * @returns {Function} Returns the new bound function.
		 * @example
		 *
		 * var object = {
		 *   'name': 'fred',
		 *   'greet': function(greeting) {
		 *     return greeting + ' ' + this.name;
		 *   }
		 * };
		 *
		 * var func = _.bindKey(object, 'greet', 'hi');
		 * func();
		 * // => 'hi fred'
		 *
		 * object.greet = function(greeting) {
		 *   return greeting + 'ya ' + this.name + '!';
		 * };
		 *
		 * func();
		 * // => 'hiya fred!'
		 */
		function bindKey(object, key) {
			return arguments.length > 2
				? createWrapper(key, 19, slice(arguments, 2), null, object)
				: createWrapper(key, 3, null, null, object);
		}

		/**
		 * Creates a function that is the composition of the provided functions,
		 * where each function consumes the return value of the function that follows.
		 * For example, composing the functions `f()`, `g()`, and `h()` produces `f(g(h()))`.
		 * Each function is executed with the `this` binding of the composed function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {...Function} [func] Functions to compose.
		 * @returns {Function} Returns the new composed function.
		 * @example
		 *
		 * var realNameMap = {
		 *   'pebbles': 'penelope'
		 * };
		 *
		 * var format = function(name) {
		 *   name = realNameMap[name.toLowerCase()] || name;
		 *   return name.charAt(0).toUpperCase() + name.slice(1).toLowerCase();
		 * };
		 *
		 * var greet = function(formatted) {
		 *   return 'Hiya ' + formatted + '!';
		 * };
		 *
		 * var welcome = _.compose(greet, format);
		 * welcome('pebbles');
		 * // => 'Hiya Penelope!'
		 */
		function compose() {
			var funcs = arguments,
					length = funcs.length;

			while (length--) {
				if (!isFunction(funcs[length])) {
					throw new TypeError;
				}
			}
			return function() {
				var args = arguments,
						length = funcs.length;

				while (length--) {
					args = [funcs[length].apply(this, args)];
				}
				return args[0];
			};
		}

		/**
		 * Creates a function which accepts one or more arguments of `func` that when
		 * invoked either executes `func` returning its result, if all `func` arguments
		 * have been provided, or returns a function that accepts one or more of the
		 * remaining `func` arguments, and so on. The arity of `func` can be specified
		 * if `func.length` is not sufficient.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to curry.
		 * @param {number} [arity=func.length] The arity of `func`.
		 * @returns {Function} Returns the new curried function.
		 * @example
		 *
		 * var curried = _.curry(function(a, b, c) {
		 *   console.log(a + b + c);
		 * });
		 *
		 * curried(1)(2)(3);
		 * // => 6
		 *
		 * curried(1, 2)(3);
		 * // => 6
		 *
		 * curried(1, 2, 3);
		 * // => 6
		 */
		function curry(func, arity) {
			arity = typeof arity == 'number' ? arity : (+arity || func.length);
			return createWrapper(func, 4, null, null, null, arity);
		}

		/**
		 * Creates a function that will delay the execution of `func` until after
		 * `wait` milliseconds have elapsed since the last time it was invoked.
		 * Provide an options object to indicate that `func` should be invoked on
		 * the leading and/or trailing edge of the `wait` timeout. Subsequent calls
		 * to the debounced function will return the result of the last `func` call.
		 *
		 * Note: If `leading` and `trailing` options are `true` `func` will be called
		 * on the trailing edge of the timeout only if the the debounced function is
		 * invoked more than once during the `wait` timeout.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to debounce.
		 * @param {number} wait The number of milliseconds to delay.
		 * @param {Object} [options] The options object.
		 * @param {boolean} [options.leading=false] Specify execution on the leading edge of the timeout.
		 * @param {number} [options.maxWait] The maximum time `func` is allowed to be delayed before it's called.
		 * @param {boolean} [options.trailing=true] Specify execution on the trailing edge of the timeout.
		 * @returns {Function} Returns the new debounced function.
		 * @example
		 *
		 * // avoid costly calculations while the window size is in flux
		 * var lazyLayout = _.debounce(calculateLayout, 150);
		 * jQuery(window).on('resize', lazyLayout);
		 *
		 * // execute `sendMail` when the click event is fired, debouncing subsequent calls
		 * jQuery('#postbox').on('click', _.debounce(sendMail, 300, {
		 *   'leading': true,
		 *   'trailing': false
		 * });
		 *
		 * // ensure `batchLog` is executed once after 1 second of debounced calls
		 * var source = new EventSource('/stream');
		 * source.addEventListener('message', _.debounce(batchLog, 250, {
		 *   'maxWait': 1000
		 * }, false);
		 */
		function debounce(func, wait, options) {
			var args,
					maxTimeoutId,
					result,
					stamp,
					thisArg,
					timeoutId,
					trailingCall,
					lastCalled = 0,
					maxWait = false,
					trailing = true;

			if (!isFunction(func)) {
				throw new TypeError;
			}
			wait = nativeMax(0, wait) || 0;
			if (options === true) {
				var leading = true;
				trailing = false;
			} else if (isObject(options)) {
				leading = options.leading;
				maxWait = 'maxWait' in options && (nativeMax(wait, options.maxWait) || 0);
				trailing = 'trailing' in options ? options.trailing : trailing;
			}
			var delayed = function() {
				var remaining = wait - (now() - stamp);
				if (remaining <= 0) {
					if (maxTimeoutId) {
						clearTimeout(maxTimeoutId);
					}
					var isCalled = trailingCall;
					maxTimeoutId = timeoutId = trailingCall = undefined;
					if (isCalled) {
						lastCalled = now();
						result = func.apply(thisArg, args);
						if (!timeoutId && !maxTimeoutId) {
							args = thisArg = null;
						}
					}
				} else {
					timeoutId = setTimeout(delayed, remaining);
				}
			};

			var maxDelayed = function() {
				if (timeoutId) {
					clearTimeout(timeoutId);
				}
				maxTimeoutId = timeoutId = trailingCall = undefined;
				if (trailing || (maxWait !== wait)) {
					lastCalled = now();
					result = func.apply(thisArg, args);
					if (!timeoutId && !maxTimeoutId) {
						args = thisArg = null;
					}
				}
			};

			return function() {
				args = arguments;
				stamp = now();
				thisArg = this;
				trailingCall = trailing && (timeoutId || !leading);

				if (maxWait === false) {
					var leadingCall = leading && !timeoutId;
				} else {
					if (!maxTimeoutId && !leading) {
						lastCalled = stamp;
					}
					var remaining = maxWait - (stamp - lastCalled),
							isCalled = remaining <= 0;

					if (isCalled) {
						if (maxTimeoutId) {
							maxTimeoutId = clearTimeout(maxTimeoutId);
						}
						lastCalled = stamp;
						result = func.apply(thisArg, args);
					}
					else if (!maxTimeoutId) {
						maxTimeoutId = setTimeout(maxDelayed, remaining);
					}
				}
				if (isCalled && timeoutId) {
					timeoutId = clearTimeout(timeoutId);
				}
				else if (!timeoutId && wait !== maxWait) {
					timeoutId = setTimeout(delayed, wait);
				}
				if (leadingCall) {
					isCalled = true;
					result = func.apply(thisArg, args);
				}
				if (isCalled && !timeoutId && !maxTimeoutId) {
					args = thisArg = null;
				}
				return result;
			};
		}

		/**
		 * Defers executing the `func` function until the current call stack has cleared.
		 * Additional arguments will be provided to `func` when it is invoked.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to defer.
		 * @param {...*} [arg] Arguments to invoke the function with.
		 * @returns {number} Returns the timer id.
		 * @example
		 *
		 * _.defer(function(text) { console.log(text); }, 'deferred');
		 * // logs 'deferred' after one or more milliseconds
		 */
		function defer(func) {
			if (!isFunction(func)) {
				throw new TypeError;
			}
			var args = slice(arguments, 1);
			return setTimeout(function() { func.apply(undefined, args); }, 1);
		}

		/**
		 * Executes the `func` function after `wait` milliseconds. Additional arguments
		 * will be provided to `func` when it is invoked.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to delay.
		 * @param {number} wait The number of milliseconds to delay execution.
		 * @param {...*} [arg] Arguments to invoke the function with.
		 * @returns {number} Returns the timer id.
		 * @example
		 *
		 * _.delay(function(text) { console.log(text); }, 1000, 'later');
		 * // => logs 'later' after one second
		 */
		function delay(func, wait) {
			if (!isFunction(func)) {
				throw new TypeError;
			}
			var args = slice(arguments, 2);
			return setTimeout(function() { func.apply(undefined, args); }, wait);
		}

		/**
		 * Creates a function that memoizes the result of `func`. If `resolver` is
		 * provided it will be used to determine the cache key for storing the result
		 * based on the arguments provided to the memoized function. By default, the
		 * first argument provided to the memoized function is used as the cache key.
		 * The `func` is executed with the `this` binding of the memoized function.
		 * The result cache is exposed as the `cache` property on the memoized function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to have its output memoized.
		 * @param {Function} [resolver] A function used to resolve the cache key.
		 * @returns {Function} Returns the new memoizing function.
		 * @example
		 *
		 * var fibonacci = _.memoize(function(n) {
		 *   return n < 2 ? n : fibonacci(n - 1) + fibonacci(n - 2);
		 * });
		 *
		 * fibonacci(9)
		 * // => 34
		 *
		 * var data = {
		 *   'fred': { 'name': 'fred', 'age': 40 },
		 *   'pebbles': { 'name': 'pebbles', 'age': 1 }
		 * };
		 *
		 * // modifying the result cache
		 * var get = _.memoize(function(name) { return data[name]; }, _.identity);
		 * get('pebbles');
		 * // => { 'name': 'pebbles', 'age': 1 }
		 *
		 * get.cache.pebbles.name = 'penelope';
		 * get('pebbles');
		 * // => { 'name': 'penelope', 'age': 1 }
		 */
		function memoize(func, resolver) {
			if (!isFunction(func)) {
				throw new TypeError;
			}
			var memoized = function() {
				var cache = memoized.cache,
						key = resolver ? resolver.apply(this, arguments) : keyPrefix + arguments[0];

				return hasOwnProperty.call(cache, key)
					? cache[key]
					: (cache[key] = func.apply(this, arguments));
			}
			memoized.cache = {};
			// hack: Add ability to reset memoized values
			memoized.reset = function() {
				var cache = memoized.cache,
						key = resolver ? resolver.apply(this, arguments) : keyPrefix + arguments[0];

				if (hasOwnProperty.call(cache, key)) {
					delete cache[key];
				}
			}
			return memoized;
		}

		/**
		 * Creates a function that is restricted to execute `func` once. Repeat calls to
		 * the function will return the value of the first call. The `func` is executed
		 * with the `this` binding of the created function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to restrict.
		 * @returns {Function} Returns the new restricted function.
		 * @example
		 *
		 * var initialize = _.once(createApplication);
		 * initialize();
		 * initialize();
		 * // `initialize` executes `createApplication` once
		 */
		function once(func) {
			var ran,
					result;

			if (!isFunction(func)) {
				throw new TypeError;
			}
			return function() {
				if (ran) {
					return result;
				}
				ran = true;
				result = func.apply(this, arguments);

				// clear the `func` variable so the function may be garbage collected
				func = null;
				return result;
			};
		}

		/**
		 * Creates a function that, when called, invokes `func` with any additional
		 * `partial` arguments prepended to those provided to the new function. This
		 * method is similar to `_.bind` except it does **not** alter the `this` binding.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to partially apply arguments to.
		 * @param {...*} [arg] Arguments to be partially applied.
		 * @returns {Function} Returns the new partially applied function.
		 * @example
		 *
		 * var greet = function(greeting, name) { return greeting + ' ' + name; };
		 * var hi = _.partial(greet, 'hi');
		 * hi('fred');
		 * // => 'hi fred'
		 */
		function partial(func) {
			return createWrapper(func, 16, slice(arguments, 1));
		}

		/**
		 * This method is like `_.partial` except that `partial` arguments are
		 * appended to those provided to the new function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to partially apply arguments to.
		 * @param {...*} [arg] Arguments to be partially applied.
		 * @returns {Function} Returns the new partially applied function.
		 * @example
		 *
		 * var defaultsDeep = _.partialRight(_.merge, _.defaults);
		 *
		 * var options = {
		 *   'variable': 'data',
		 *   'imports': { 'jq': $ }
		 * };
		 *
		 * defaultsDeep(options, _.templateSettings);
		 *
		 * options.variable
		 * // => 'data'
		 *
		 * options.imports
		 * // => { '_': _, 'jq': $ }
		 */
		function partialRight(func) {
			return createWrapper(func, 32, null, slice(arguments, 1));
		}

		/**
		 * Creates a function that, when executed, will only call the `func` function
		 * at most once per every `wait` milliseconds. Provide an options object to
		 * indicate that `func` should be invoked on the leading and/or trailing edge
		 * of the `wait` timeout. Subsequent calls to the throttled function will
		 * return the result of the last `func` call.
		 *
		 * Note: If `leading` and `trailing` options are `true` `func` will be called
		 * on the trailing edge of the timeout only if the the throttled function is
		 * invoked more than once during the `wait` timeout.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {Function} func The function to throttle.
		 * @param {number} wait The number of milliseconds to throttle executions to.
		 * @param {Object} [options] The options object.
		 * @param {boolean} [options.leading=true] Specify execution on the leading edge of the timeout.
		 * @param {boolean} [options.trailing=true] Specify execution on the trailing edge of the timeout.
		 * @returns {Function} Returns the new throttled function.
		 * @example
		 *
		 * // avoid excessively updating the position while scrolling
		 * var throttled = _.throttle(updatePosition, 100);
		 * jQuery(window).on('scroll', throttled);
		 *
		 * // execute `renewToken` when the click event is fired, but not more than once every 5 minutes
		 * jQuery('.interactive').on('click', _.throttle(renewToken, 300000, {
		 *   'trailing': false
		 * }));
		 */
		function throttle(func, wait, options) {
			var leading = true,
					trailing = true;

			if (!isFunction(func)) {
				throw new TypeError;
			}
			if (options === false) {
				leading = false;
			} else if (isObject(options)) {
				leading = 'leading' in options ? options.leading : leading;
				trailing = 'trailing' in options ? options.trailing : trailing;
			}
			debounceOptions.leading = leading;
			debounceOptions.maxWait = wait;
			debounceOptions.trailing = trailing;

			return debounce(func, wait, debounceOptions);
		}

		/**
		 * Creates a function that provides `value` to the wrapper function as its
		 * first argument. Additional arguments provided to the function are appended
		 * to those provided to the wrapper function. The wrapper is executed with
		 * the `this` binding of the created function.
		 *
		 * @static
		 * @memberOf _
		 * @category Functions
		 * @param {*} value The value to wrap.
		 * @param {Function} wrapper The wrapper function.
		 * @returns {Function} Returns the new function.
		 * @example
		 *
		 * var p = _.wrap(_.escape, function(func, text) {
		 *   return '<p>' + func(text) + '</p>';
		 * });
		 *
		 * p('Fred, Wilma, & Pebbles');
		 * // => '<p>Fred, Wilma, &amp; Pebbles</p>'
		 */
		function wrap(value, wrapper) {
			return createWrapper(wrapper, 16, [value]);
		}

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates a function that returns `value`.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {*} value The value to return from the new function.
		 * @returns {Function} Returns the new function.
		 * @example
		 *
		 * var object = { 'name': 'fred' };
		 * var getter = _.constant(object);
		 * getter() === object;
		 * // => true
		 */
		function constant(value) {
			return function() {
				return value;
			};
		}

		/**
		 * Produces a callback bound to an optional `thisArg`. If `func` is a property
		 * name the created callback will return the property value for a given element.
		 * If `func` is an object the created callback will return `true` for elements
		 * that contain the equivalent object properties, otherwise it will return `false`.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {*} [func=identity] The value to convert to a callback.
		 * @param {*} [thisArg] The `this` binding of the created callback.
		 * @param {number} [argCount] The number of arguments the callback accepts.
		 * @returns {Function} Returns a callback function.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * // wrap to create custom callback shorthands
		 * _.createCallback = _.wrap(_.createCallback, function(func, callback, thisArg) {
		 *   var match = /^(.+?)__([gl]t)(.+)$/.exec(callback);
		 *   return !match ? func(callback, thisArg) : function(object) {
		 *     return match[2] == 'gt' ? object[match[1]] > match[3] : object[match[1]] < match[3];
		 *   };
		 * });
		 *
		 * _.filter(characters, 'age__gt38');
		 * // => [{ 'name': 'fred', 'age': 40 }]
		 */
		function createCallback(func, thisArg, argCount) {
			var type = typeof func;
			if (func == null || type == 'function') {
				return baseCreateCallback(func, thisArg, argCount);
			}
			// handle "_.pluck" style callback shorthands
			if (type != 'object') {
				return property(func);
			}
			var props = keys(func),
					key = props[0],
					a = func[key];

			// handle "_.where" style callback shorthands
			if (props.length == 1 && a === a && !isObject(a)) {
				// fast path the common case of providing an object with a single
				// property containing a primitive value
				return function(object) {
					var b = object[key];
					return a === b && (a !== 0 || (1 / a == 1 / b));
				};
			}
			return function(object) {
				var length = props.length,
						result = false;

				while (length--) {
					if (!(result = baseIsEqual(object[props[length]], func[props[length]], null, true))) {
						break;
					}
				}
				return result;
			};
		}

		/**
		 * Converts the characters `&`, `<`, `>`, `"`, and `'` in `string` to their
		 * corresponding HTML entities.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} string The string to escape.
		 * @returns {string} Returns the escaped string.
		 * @example
		 *
		 * _.escape('Fred, Wilma, & Pebbles');
		 * // => 'Fred, Wilma, &amp; Pebbles'
		 */
		function escape(string) {
			return string == null ? '' : String(string).replace(reUnescapedHtml, escapeHtmlChar);
		}

		/**
		 * This method returns the first argument provided to it.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {*} value Any value.
		 * @returns {*} Returns `value`.
		 * @example
		 *
		 * var object = { 'name': 'fred' };
		 * _.identity(object) === object;
		 * // => true
		 */
		function identity(value) {
			return value;
		}

		/**
		 * Adds function properties of a source object to the destination object.
		 * If `object` is a function methods will be added to its prototype as well.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {Function|Object} [object=lodash] object The destination object.
		 * @param {Object} source The object of functions to add.
		 * @param {Object} [options] The options object.
		 * @param {boolean} [options.chain=true] Specify whether the functions added are chainable.
		 * @example
		 *
		 * function capitalize(string) {
		 *   return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
		 * }
		 *
		 * _.mixin({ 'capitalize': capitalize });
		 * _.capitalize('fred');
		 * // => 'Fred'
		 *
		 * _('fred').capitalize().value();
		 * // => 'Fred'
		 *
		 * _.mixin({ 'capitalize': capitalize }, { 'chain': false });
		 * _('fred').capitalize();
		 * // => 'Fred'
		 */
		function mixin(object, source, options) {
			var chain = true,
					methodNames = source && functions(source);

			if (!source || (!options && !methodNames.length)) {
				if (options == null) {
					options = source;
				}
				ctor = lodashWrapper;
				source = object;
				object = lodash;
				methodNames = functions(source);
			}
			if (options === false) {
				chain = false;
			} else if (isObject(options) && 'chain' in options) {
				chain = options.chain;
			}
			var ctor = object,
					isFunc = isFunction(ctor);

			forEach(methodNames, function(methodName) {
				var func = object[methodName] = source[methodName];
				if (isFunc) {
					ctor.prototype[methodName] = function() {
						var chainAll = this.__chain__,
								value = this.__wrapped__,
								args = [value];

						push.apply(args, arguments);
						var result = func.apply(object, args);
						if (chain || chainAll) {
							if (value === result && isObject(result)) {
								return this;
							}
							result = new ctor(result);
							result.__chain__ = chainAll;
						}
						return result;
					};
				}
			});
		}

		/**
		 * Reverts the '_' variable to its previous value and returns a reference to
		 * the `lodash` function.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @returns {Function} Returns the `lodash` function.
		 * @example
		 *
		 * var lodash = _.noConflict();
		 */
		function noConflict() {
			context._ = oldDash;
			return this;
		}

		/**
		 * A no-operation function.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @example
		 *
		 * var object = { 'name': 'fred' };
		 * _.noop(object) === undefined;
		 * // => true
		 */
		function noop() {
			// no operation performed
		}

		/**
		 * Gets the number of milliseconds that have elapsed since the Unix epoch
		 * (1 January 1970 00:00:00 UTC).
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @example
		 *
		 * var stamp = _.now();
		 * _.defer(function() { console.log(_.now() - stamp); });
		 * // => logs the number of milliseconds it took for the deferred function to be called
		 */
		var now = isNative(now = Date.now) && now || function() {
			return new Date().getTime();
		};

		/**
		 * Converts the given value into an integer of the specified radix.
		 * If `radix` is `undefined` or `0` a `radix` of `10` is used unless the
		 * `value` is a hexadecimal, in which case a `radix` of `16` is used.
		 *
		 * Note: This method avoids differences in native ES3 and ES5 `parseInt`
		 * implementations. See http://es5.github.io/#E.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} value The value to parse.
		 * @param {number} [radix] The radix used to interpret the value to parse.
		 * @returns {number} Returns the new integer value.
		 * @example
		 *
		 * _.parseInt('08');
		 * // => 8
		 */
		var parseInt = nativeParseInt(whitespace + '08') == 8 ? nativeParseInt : function(value, radix) {
			// Firefox < 21 and Opera < 15 follow the ES3 specified implementation of `parseInt`
			return nativeParseInt(isString(value) ? value.replace(reLeadingSpacesAndZeros, '') : value, radix || 0);
		};

		/**
		 * Creates a "_.pluck" style function, which returns the `key` value of a
		 * given object.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} key The name of the property to retrieve.
		 * @returns {Function} Returns the new function.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'fred',   'age': 40 },
		 *   { 'name': 'barney', 'age': 36 }
		 * ];
		 *
		 * var getName = _.property('name');
		 *
		 * _.map(characters, getName);
		 * // => ['barney', 'fred']
		 *
		 * _.sortBy(characters, getName);
		 * // => [{ 'name': 'barney', 'age': 36 }, { 'name': 'fred',   'age': 40 }]
		 */
		function property(key) {
			return function(object) {
				return object[key];
			};
		}

		/**
		 * Produces a random number between `min` and `max` (inclusive). If only one
		 * argument is provided a number between `0` and the given number will be
		 * returned. If `floating` is truey or either `min` or `max` are floats a
		 * floating-point number will be returned instead of an integer.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {number} [min=0] The minimum possible value.
		 * @param {number} [max=1] The maximum possible value.
		 * @param {boolean} [floating=false] Specify returning a floating-point number.
		 * @returns {number} Returns a random number.
		 * @example
		 *
		 * _.random(0, 5);
		 * // => an integer between 0 and 5
		 *
		 * _.random(5);
		 * // => also an integer between 0 and 5
		 *
		 * _.random(5, true);
		 * // => a floating-point number between 0 and 5
		 *
		 * _.random(1.2, 5.2);
		 * // => a floating-point number between 1.2 and 5.2
		 */
		function random(min, max, floating) {
			var noMin = min == null,
					noMax = max == null;

			if (floating == null) {
				if (typeof min == 'boolean' && noMax) {
					floating = min;
					min = 1;
				}
				else if (!noMax && typeof max == 'boolean') {
					floating = max;
					noMax = true;
				}
			}
			if (noMin && noMax) {
				max = 1;
			}
			min = +min || 0;
			if (noMax) {
				max = min;
				min = 0;
			} else {
				max = +max || 0;
			}
			if (floating || min % 1 || max % 1) {
				var rand = nativeRandom();
				return nativeMin(min + (rand * (max - min + parseFloat('1e-' + ((rand +'').length - 1)))), max);
			}
			return baseRandom(min, max);
		}

		/**
		 * Resolves the value of property `key` on `object`. If `key` is a function
		 * it will be invoked with the `this` binding of `object` and its result returned,
		 * else the property value is returned. If `object` is falsey then `undefined`
		 * is returned.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {Object} object The object to inspect.
		 * @param {string} key The name of the property to resolve.
		 * @returns {*} Returns the resolved value.
		 * @example
		 *
		 * var object = {
		 *   'cheese': 'crumpets',
		 *   'stuff': function() {
		 *     return 'nonsense';
		 *   }
		 * };
		 *
		 * _.result(object, 'cheese');
		 * // => 'crumpets'
		 *
		 * _.result(object, 'stuff');
		 * // => 'nonsense'
		 */
		function result(object, key) {
			if (object) {
				var value = object[key];
				return isFunction(value) ? object[key]() : value;
			}
		}

		/**
		 * A micro-templating method that handles arbitrary delimiters, preserves
		 * whitespace, and correctly escapes quotes within interpolated code.
		 *
		 * Note: In the development build, `_.template` utilizes sourceURLs for easier
		 * debugging. See http://www.html5rocks.com/en/tutorials/developertools/sourcemaps/#toc-sourceurl
		 *
		 * For more information on precompiling templates see:
		 * http://lodash.com/custom-builds
		 *
		 * For more information on Chrome extension sandboxes see:
		 * http://developer.chrome.com/stable/extensions/sandboxingEval.html
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} text The template text.
		 * @param {Object} data The data object used to populate the text.
		 * @param {Object} [options] The options object.
		 * @param {RegExp} [options.escape] The "escape" delimiter.
		 * @param {RegExp} [options.evaluate] The "evaluate" delimiter.
		 * @param {Object} [options.imports] An object to import into the template as local variables.
		 * @param {RegExp} [options.interpolate] The "interpolate" delimiter.
		 * @param {string} [sourceURL] The sourceURL of the template's compiled source.
		 * @param {string} [variable] The data object variable name.
		 * @returns {Function|string} Returns a compiled function when no `data` object
		 *  is given, else it returns the interpolated text.
		 * @example
		 *
		 * // using the "interpolate" delimiter to create a compiled template
		 * var compiled = _.template('hello <%= name %>');
		 * compiled({ 'name': 'fred' });
		 * // => 'hello fred'
		 *
		 * // using the "escape" delimiter to escape HTML in data property values
		 * _.template('<b><%- value %></b>', { 'value': '<script>' });
		 * // => '<b>&lt;script&gt;</b>'
		 *
		 * // using the "evaluate" delimiter to generate HTML
		 * var list = '<% _.forEach(people, function(name) { %><li><%- name %></li><% }); %>';
		 * _.template(list, { 'people': ['fred', 'barney'] });
		 * // => '<li>fred</li><li>barney</li>'
		 *
		 * // using the ES6 delimiter as an alternative to the default "interpolate" delimiter
		 * _.template('hello ${ name }', { 'name': 'pebbles' });
		 * // => 'hello pebbles'
		 *
		 * // using the internal `print` function in "evaluate" delimiters
		 * _.template('<% print("hello " + name); %>!', { 'name': 'barney' });
		 * // => 'hello barney!'
		 *
		 * // using a custom template delimiters
		 * _.templateSettings = {
		 *   'interpolate': /{{([\s\S]+?)}}/g
		 * };
		 *
		 * _.template('hello {{ name }}!', { 'name': 'mustache' });
		 * // => 'hello mustache!'
		 *
		 * // using the `imports` option to import jQuery
		 * var list = '<% jq.each(people, function(name) { %><li><%- name %></li><% }); %>';
		 * _.template(list, { 'people': ['fred', 'barney'] }, { 'imports': { 'jq': jQuery } });
		 * // => '<li>fred</li><li>barney</li>'
		 *
		 * // using the `sourceURL` option to specify a custom sourceURL for the template
		 * var compiled = _.template('hello <%= name %>', null, { 'sourceURL': '/basic/greeting.jst' });
		 * compiled(data);
		 * // => find the source of "greeting.jst" under the Sources tab or Resources panel of the web inspector
		 *
		 * // using the `variable` option to ensure a with-statement isn't used in the compiled template
		 * var compiled = _.template('hi <%= data.name %>!', null, { 'variable': 'data' });
		 * compiled.source;
		 * // => function(data) {
		 *   var __t, __p = '', __e = _.escape;
		 *   __p += 'hi ' + ((__t = ( data.name )) == null ? '' : __t) + '!';
		 *   return __p;
		 * }
		 *
		 * // using the `source` property to inline compiled templates for meaningful
		 * // line numbers in error messages and a stack trace
		 * fs.writeFileSync(path.join(cwd, 'jst.js'), '\
		 *   var JST = {\
		 *     "main": ' + _.template(mainText).source + '\
		 *   };\
		 * ');
		 */
		function template(text, data, options) {
			// based on John Resig's `tmpl` implementation
			// http://ejohn.org/blog/javascript-micro-templating/
			// and Laura Doktorova's doT.js
			// https://github.com/olado/doT
			var settings = lodash.templateSettings;
			text = String(text || '');

			// avoid missing dependencies when `iteratorTemplate` is not defined
			options = defaults({}, options, settings);

			var imports = defaults({}, options.imports, settings.imports),
					importsKeys = keys(imports),
					importsValues = values(imports);

			var isEvaluating,
					index = 0,
					interpolate = options.interpolate || reNoMatch,
					source = "__p += '";

			// compile the regexp to match each delimiter
			var reDelimiters = RegExp(
				(options.escape || reNoMatch).source + '|' +
				interpolate.source + '|' +
				(interpolate === reInterpolate ? reEsTemplate : reNoMatch).source + '|' +
				(options.evaluate || reNoMatch).source + '|$'
			, 'g');

			text.replace(reDelimiters, function(match, escapeValue, interpolateValue, esTemplateValue, evaluateValue, offset) {
				interpolateValue || (interpolateValue = esTemplateValue);

				// escape characters that cannot be included in string literals
				source += text.slice(index, offset).replace(reUnescapedString, escapeStringChar);

				// replace delimiters with snippets
				if (escapeValue) {
					source += "' +\n__e(" + escapeValue + ") +\n'";
				}
				if (evaluateValue) {
					isEvaluating = true;
					source += "';\n" + evaluateValue + ";\n__p += '";
				}
				if (interpolateValue) {
					source += "' +\n((__t = (" + interpolateValue + ")) == null ? '' : __t) +\n'";
				}
				index = offset + match.length;

				// the JS engine embedded in Adobe products requires returning the `match`
				// string in order to produce the correct `offset` value
				return match;
			});

			source += "';\n";

			// if `variable` is not specified, wrap a with-statement around the generated
			// code to add the data object to the top of the scope chain
			var variable = options.variable,
					hasVariable = variable;

			if (!hasVariable) {
				variable = 'obj';
				source = 'with (' + variable + ') {\n' + source + '\n}\n';
			}
			// cleanup code by stripping empty strings
			source = (isEvaluating ? source.replace(reEmptyStringLeading, '') : source)
				.replace(reEmptyStringMiddle, '$1')
				.replace(reEmptyStringTrailing, '$1;');

			// frame code as the function body
			source = 'function(' + variable + ') {\n' +
				(hasVariable ? '' : variable + ' || (' + variable + ' = {});\n') +
				"var __t, __p = '', __e = _.escape" +
				(isEvaluating
					? ', __j = Array.prototype.join;\n' +
						"function print() { __p += __j.call(arguments, '') }\n"
					: ';\n'
				) +
				source +
				'return __p\n}';

			// Use a sourceURL for easier debugging.
			// http://www.html5rocks.com/en/tutorials/developertools/sourcemaps/#toc-sourceurl
			var sourceURL = '\n/*\n//# sourceURL=' + (options.sourceURL || '/lodash/template/source[' + (templateCounter++) + ']') + '\n*/';

			try {
				var result = Function(importsKeys, 'return ' + source + sourceURL).apply(undefined, importsValues);
			} catch(e) {
				e.source = source;
				throw e;
			}
			if (data) {
				return result(data);
			}
			// provide the compiled function's source by its `toString` method, in
			// supported environments, or the `source` property as a convenience for
			// inlining compiled templates during the build process
			result.source = source;
			return result;
		}

		/**
		 * Executes the callback `n` times, returning an array of the results
		 * of each callback execution. The callback is bound to `thisArg` and invoked
		 * with one argument; (index).
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {number} n The number of times to execute the callback.
		 * @param {Function} callback The function called per iteration.
		 * @param {*} [thisArg] The `this` binding of `callback`.
		 * @returns {Array} Returns an array of the results of each `callback` execution.
		 * @example
		 *
		 * var diceRolls = _.times(3, _.partial(_.random, 1, 6));
		 * // => [3, 6, 4]
		 *
		 * _.times(3, function(n) { mage.castSpell(n); });
		 * // => calls `mage.castSpell(n)` three times, passing `n` of `0`, `1`, and `2` respectively
		 *
		 * _.times(3, function(n) { this.cast(n); }, mage);
		 * // => also calls `mage.castSpell(n)` three times
		 */
		function times(n, callback, thisArg) {
			n = (n = +n) > -1 ? n : 0;
			var index = -1,
					result = Array(n);

			callback = baseCreateCallback(callback, thisArg, 1);
			while (++index < n) {
				result[index] = callback(index);
			}
			return result;
		}

		/**
		 * The inverse of `_.escape` this method converts the HTML entities
		 * `&amp;`, `&lt;`, `&gt;`, `&quot;`, and `&#39;` in `string` to their
		 * corresponding characters.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} string The string to unescape.
		 * @returns {string} Returns the unescaped string.
		 * @example
		 *
		 * _.unescape('Fred, Barney &amp; Pebbles');
		 * // => 'Fred, Barney & Pebbles'
		 */
		function unescape(string) {
			return string == null ? '' : String(string).replace(reEscapedHtml, unescapeHtmlChar);
		}

		/**
		 * Generates a unique ID. If `prefix` is provided the ID will be appended to it.
		 *
		 * @static
		 * @memberOf _
		 * @category Utilities
		 * @param {string} [prefix] The value to prefix the ID with.
		 * @returns {string} Returns the unique ID.
		 * @example
		 *
		 * _.uniqueId('contact_');
		 * // => 'contact_104'
		 *
		 * _.uniqueId();
		 * // => '105'
		 */
		function uniqueId(prefix) {
			var id = ++idCounter;
			return String(prefix == null ? '' : prefix) + id;
		}

		/*--------------------------------------------------------------------------*/

		/**
		 * Creates a `lodash` object that wraps the given value with explicit
		 * method chaining enabled.
		 *
		 * @static
		 * @memberOf _
		 * @category Chaining
		 * @param {*} value The value to wrap.
		 * @returns {Object} Returns the wrapper object.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney',  'age': 36 },
		 *   { 'name': 'fred',    'age': 40 },
		 *   { 'name': 'pebbles', 'age': 1 }
		 * ];
		 *
		 * var youngest = _.chain(characters)
		 *     .sortBy('age')
		 *     .map(function(chr) { return chr.name + ' is ' + chr.age; })
		 *     .first()
		 *     .value();
		 * // => 'pebbles is 1'
		 */
		function chain(value) {
			value = new lodashWrapper(value);
			value.__chain__ = true;
			return value;
		}

		/**
		 * Invokes `interceptor` with the `value` as the first argument and then
		 * returns `value`. The purpose of this method is to "tap into" a method
		 * chain in order to perform operations on intermediate results within
		 * the chain.
		 *
		 * @static
		 * @memberOf _
		 * @category Chaining
		 * @param {*} value The value to provide to `interceptor`.
		 * @param {Function} interceptor The function to invoke.
		 * @returns {*} Returns `value`.
		 * @example
		 *
		 * _([1, 2, 3, 4])
		 *  .tap(function(array) { array.pop(); })
		 *  .reverse()
		 *  .value();
		 * // => [3, 2, 1]
		 */
		function tap(value, interceptor) {
			interceptor(value);
			return value;
		}

		/**
		 * Enables explicit method chaining on the wrapper object.
		 *
		 * @name chain
		 * @memberOf _
		 * @category Chaining
		 * @returns {*} Returns the wrapper object.
		 * @example
		 *
		 * var characters = [
		 *   { 'name': 'barney', 'age': 36 },
		 *   { 'name': 'fred',   'age': 40 }
		 * ];
		 *
		 * // without explicit chaining
		 * _(characters).first();
		 * // => { 'name': 'barney', 'age': 36 }
		 *
		 * // with explicit chaining
		 * _(characters).chain()
		 *   .first()
		 *   .pick('age')
		 *   .value();
		 * // => { 'age': 36 }
		 */
		function wrapperChain() {
			this.__chain__ = true;
			return this;
		}

		/**
		 * Produces the `toString` result of the wrapped value.
		 *
		 * @name toString
		 * @memberOf _
		 * @category Chaining
		 * @returns {string} Returns the string result.
		 * @example
		 *
		 * _([1, 2, 3]).toString();
		 * // => '1,2,3'
		 */
		function wrapperToString() {
			return String(this.__wrapped__);
		}

		/**
		 * Extracts the wrapped value.
		 *
		 * @name valueOf
		 * @memberOf _
		 * @alias value
		 * @category Chaining
		 * @returns {*} Returns the wrapped value.
		 * @example
		 *
		 * _([1, 2, 3]).valueOf();
		 * // => [1, 2, 3]
		 */
		function wrapperValueOf() {
			return this.__wrapped__;
		}

		/*--------------------------------------------------------------------------*/

		// add functions that return wrapped values when chaining
		lodash.after = after;
		lodash.assign = assign;
		lodash.at = at;
		lodash.bind = bind;
		lodash.bindAll = bindAll;
		lodash.bindKey = bindKey;
		lodash.chain = chain;
		lodash.compact = compact;
		lodash.compose = compose;
		lodash.constant = constant;
		lodash.countBy = countBy;
		lodash.create = create;
		lodash.createCallback = createCallback;
		lodash.curry = curry;
		lodash.debounce = debounce;
		lodash.defaults = defaults;
		lodash.defer = defer;
		lodash.delay = delay;
		lodash.difference = difference;
		lodash.filter = filter;
		lodash.flatten = flatten;
		lodash.forEach = forEach;
		lodash.forEachRight = forEachRight;
		lodash.forIn = forIn;
		lodash.forInRight = forInRight;
		lodash.forOwn = forOwn;
		lodash.forOwnRight = forOwnRight;
		lodash.functions = functions;
		lodash.groupBy = groupBy;
		lodash.indexBy = indexBy;
		lodash.initial = initial;
		lodash.intersection = intersection;
		lodash.invert = invert;
		lodash.invoke = invoke;
		lodash.keys = keys;
		lodash.map = map;
		lodash.mapValues = mapValues;
		lodash.max = max;
		lodash.memoize = memoize;
		lodash.merge = merge;
		lodash.min = min;
		lodash.omit = omit;
		lodash.once = once;
		lodash.pairs = pairs;
		lodash.partial = partial;
		lodash.partialRight = partialRight;
		lodash.pick = pick;
		lodash.pluck = pluck;
		lodash.property = property;
		lodash.pull = pull;
		lodash.range = range;
		lodash.reject = reject;
		lodash.remove = remove;
		lodash.rest = rest;
		lodash.shuffle = shuffle;
		lodash.sortBy = sortBy;
		lodash.tap = tap;
		lodash.throttle = throttle;
		lodash.times = times;
		lodash.toArray = toArray;
		lodash.transform = transform;
		lodash.union = union;
		lodash.uniq = uniq;
		lodash.values = values;
		lodash.where = where;
		lodash.without = without;
		lodash.wrap = wrap;
		lodash.xor = xor;
		lodash.zip = zip;
		lodash.zipObject = zipObject;

		// add aliases
		lodash.collect = map;
		lodash.drop = rest;
		lodash.each = forEach;
		lodash.eachRight = forEachRight;
		lodash.extend = assign;
		lodash.methods = functions;
		lodash.object = zipObject;
		lodash.select = filter;
		lodash.tail = rest;
		lodash.unique = uniq;
		lodash.unzip = zip;

		// add functions to `lodash.prototype`
		mixin(lodash);

		/*--------------------------------------------------------------------------*/

		// add functions that return unwrapped values when chaining
		lodash.clone = clone;
		lodash.cloneDeep = cloneDeep;
		lodash.contains = contains;
		lodash.escape = escape;
		lodash.every = every;
		lodash.find = find;
		lodash.findIndex = findIndex;
		lodash.findKey = findKey;
		lodash.findLast = findLast;
		lodash.findLastIndex = findLastIndex;
		lodash.findLastKey = findLastKey;
		lodash.has = has;
		lodash.identity = identity;
		lodash.indexOf = indexOf;
		lodash.isArguments = isArguments;
		lodash.isArray = isArray;
		lodash.isBoolean = isBoolean;
		lodash.isDate = isDate;
		lodash.isElement = isElement;
		lodash.isEmpty = isEmpty;
		lodash.isEqual = isEqual;
		lodash.isFinite = isFinite;
		lodash.isFunction = isFunction;
		lodash.isNaN = isNaN;
		lodash.isNull = isNull;
		lodash.isNumber = isNumber;
		lodash.isObject = isObject;
		lodash.isPlainObject = isPlainObject;
		lodash.isRegExp = isRegExp;
		lodash.isString = isString;
		lodash.isUndefined = isUndefined;
		lodash.lastIndexOf = lastIndexOf;
		lodash.mixin = mixin;
		lodash.noConflict = noConflict;
		lodash.noop = noop;
		lodash.now = now;
		lodash.parseInt = parseInt;
		lodash.random = random;
		lodash.reduce = reduce;
		lodash.reduceRight = reduceRight;
		lodash.result = result;
		lodash.runInContext = runInContext;
		lodash.size = size;
		lodash.some = some;
		lodash.sortedIndex = sortedIndex;
		lodash.template = template;
		lodash.unescape = unescape;
		lodash.uniqueId = uniqueId;

		// add aliases
		lodash.all = every;
		lodash.any = some;
		lodash.detect = find;
		lodash.findWhere = find;
		lodash.foldl = reduce;
		lodash.foldr = reduceRight;
		lodash.include = contains;
		lodash.inject = reduce;

		mixin(function() {
			var source = {}
			forOwn(lodash, function(func, methodName) {
				if (!lodash.prototype[methodName]) {
					source[methodName] = func;
				}
			});
			return source;
		}(), false);

		/*--------------------------------------------------------------------------*/

		// add functions capable of returning wrapped and unwrapped values when chaining
		lodash.first = first;
		lodash.last = last;
		lodash.sample = sample;

		// add aliases
		lodash.take = first;
		lodash.head = first;

		forOwn(lodash, function(func, methodName) {
			var callbackable = methodName !== 'sample';
			if (!lodash.prototype[methodName]) {
				lodash.prototype[methodName]= function(n, guard) {
					var chainAll = this.__chain__,
							result = func(this.__wrapped__, n, guard);

					return !chainAll && (n == null || (guard && !(callbackable && typeof n == 'function')))
						? result
						: new lodashWrapper(result, chainAll);
				};
			}
		});

		/*--------------------------------------------------------------------------*/

		/**
		 * The semantic version number.
		 *
		 * @static
		 * @memberOf _
		 * @type string
		 */
		lodash.VERSION = '2.4.1';

		// add "Chaining" functions to the wrapper
		lodash.prototype.chain = wrapperChain;
		lodash.prototype.toString = wrapperToString;
		lodash.prototype.value = wrapperValueOf;
		lodash.prototype.valueOf = wrapperValueOf;

		// add `Array` functions that return unwrapped values
		forEach(['join', 'pop', 'shift'], function(methodName) {
			var func = arrayRef[methodName];
			lodash.prototype[methodName] = function() {
				var chainAll = this.__chain__,
						result = func.apply(this.__wrapped__, arguments);

				return chainAll
					? new lodashWrapper(result, chainAll)
					: result;
			};
		});

		// add `Array` functions that return the existing wrapped value
		forEach(['push', 'reverse', 'sort', 'unshift'], function(methodName) {
			var func = arrayRef[methodName];
			lodash.prototype[methodName] = function() {
				func.apply(this.__wrapped__, arguments);
				return this;
			};
		});

		// add `Array` functions that return new wrapped values
		forEach(['concat', 'slice', 'splice'], function(methodName) {
			var func = arrayRef[methodName];
			lodash.prototype[methodName] = function() {
				return new lodashWrapper(func.apply(this.__wrapped__, arguments), this.__chain__);
			};
		});

		return lodash;
	}

	/*--------------------------------------------------------------------------*/

	// expose Lo-Dash
	var _ = $._ = runInContext();

	// expose Lo-Dash methods to jQuery
	$.each(_, function(prop, func){
		if (!Object.hasOwnProperty.call(_, prop) || /VERSION/.test(name)) return;
		if (!$[prop]) $[prop] = func;
	});

});KTVendors.plugin("bootstrap3", function($) {

var jQuery = $;
/*!
 * Bootstrap v3.0.3 (http://getbootstrap.com)
 * Copyright 2015 Twitter, Inc.
 * Licensed under http://www.apache.org/licenses/LICENSE-2.0
 */

if (window["Komento/Bootstrap"]) { throw new Error("An instance of Bootstrap has been initialized before this.") } else { window["Komento/Bootstrap"] = { version: "3.0.3", foundry: jQuery } }

/* ========================================================================
 * Bootstrap: transition.js v3.0.3
 * http://getbootstrap.com/javascript/#transitions
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
	// ============================================================

	function transitionEnd() {
		var el = document.createElement('bootstrap')

		var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd'
		, 'MozTransition'    : 'transitionend'
		, 'OTransition'      : 'oTransitionEnd otransitionend'
		, 'transition'       : 'transitionend'
		}

		for (var name in transEndEventNames) {
			if (el.style[name] !== undefined) {
				return { end: transEndEventNames[name] }
			}
		}
	}

	// http://blog.alexmaccaw.com/css-transitions
	$.fn.emulateTransitionEnd = function (duration) {
		var called = false, $el = this
		$(this).one($.support.transition.end, function () { called = true })
		var callback = function () { if (!called) $($el).trigger($.support.transition.end) }
		setTimeout(callback, duration)
		return this
	}

	$(function () {
		$.support.transition = transitionEnd()
	})

}(jQuery);

/* ========================================================================
 * Bootstrap: alert.js v3.0.3
 * http://getbootstrap.com/javascript/#alerts
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// ALERT CLASS DEFINITION
	// ======================

	var dismiss = '[data-bs-dismiss="alert"]'
	var Alert   = function (el) {
		$(el).on('click', dismiss, this.close)
	}

	Alert.prototype.close = function (e) {
		var $this    = $(this)
		var selector = $this.attr('data-target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
		}

		var $parent = $(selector)

		if (e) e.preventDefault()

		if (!$parent.length) {
			$parent = $this.hasClass('alert') ? $this : $this.parent()
		}

		$parent.trigger(e = $.Event('close.bs.alert'))

		if (e.isDefaultPrevented()) return

		$parent.removeClass('in')

		function removeElement() {
			$parent.trigger('closed.bs.alert').remove()
		}

		$.support.transition && $parent.hasClass('fade') ?
			$parent
				.one($.support.transition.end, removeElement)
				.emulateTransitionEnd(150) :
			removeElement()
	}


	// ALERT PLUGIN DEFINITION
	// =======================

	var old = $.fn.alert

	$.fn.alert = function (option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.alert')

			if (!data) $this.data('bs.alert', (data = new Alert(this)))
			if (typeof option == 'string') data[option].call($this)
		})
	}

	$.fn.alert.Constructor = Alert


	// ALERT NO CONFLICT
	// =================

	$.fn.alert.noConflict = function () {
		$.fn.alert = old
		return this
	}


	// ALERT DATA-API
	// ==============

	$(document).on('click.bs.alert.data-api', dismiss, Alert.prototype.close)

}(jQuery);


/* ========================================================================
 * Bootstrap: dropdown.js v3.0.3
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// DROPDOWN CLASS DEFINITION
	// =========================

	var backdrop = '.dropdown-backdrop'
	var toggle   = '[data-kt-toggle=dropdown]'
	var Dropdown = function (element) {
		$(element).on('click.bs.dropdown', this.toggle)
	}

	Dropdown.prototype.toggle = function (e) {
		var $this = $(this)

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		clearMenus()

		if (!isActive) {
			if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
				// if mobile we use a backdrop because click events don't delegate
				$('<div class="dropdown-backdrop"/>').insertAfter($(this)).on('click', clearMenus)
			}

			$parent.triggerHandler(e = $.Event('show.bs.dropdown'))

			if (e.isDefaultPrevented()) return

			$parent
				.toggleClass('open')
				.trigger('shown.bs.dropdown')

			$this.focus()
		}

		return false
	}

	Dropdown.prototype.keydown = function (e) {
		if (!/(38|40|27)/.test(e.keyCode)) return

		var $this = $(this)

		e.preventDefault()
		e.stopPropagation()

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		if (!isActive || (isActive && e.keyCode == 27)) {
			if (e.which == 27) $parent.find(toggle).focus()
			return $this.click()
		}

		var $items = $('[role=menu] li:not(.divider):visible a', $parent)

		if (!$items.length) return

		var index = $items.index($items.filter(':focus'))

		if (e.keyCode == 38 && index > 0)                 index--                        // up
		if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
		if (!~index)                                      index=0

		$items.eq(index).focus()
	}

	function clearMenus() {
		$(backdrop).remove()
		$(toggle).each(function (e) {
			var $parent = getParent($(this))
			if (!$parent.hasClass('open')) return
			$parent.triggerHandler(e = $.Event('hide.bs.dropdown'))
			if (e.isDefaultPrevented()) return
			$parent.removeClass('open').trigger('hidden.bs.dropdown')
		})
	}

	function getParent($this) {
		var selector = $this.attr('data-target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
		}

		var $parent = selector && $(selector)

		return $parent && $parent.length ? $parent : $this.parent()
	}


	// DROPDOWN PLUGIN DEFINITION
	// ==========================

	var old = $.fn.dropdown

	$.fn.dropdown = function (option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.dropdown')

			if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
			if (typeof option == 'string') data[option].call($this)
		})
	}

	$.fn.dropdown.Constructor = Dropdown


	// DROPDOWN NO CONFLICT
	// ====================

	$.fn.dropdown.noConflict = function () {
		$.fn.dropdown = old
		return this
	}


	// APPLY TO STANDARD DROPDOWN ELEMENTS
	// ===================================

	$(document)
		.on('click.bs.dropdown.data-api', clearMenus)
		.on('click.bs.dropdown.data-api', '.dropdown_ form, .dropdown-static', function (e) { e.stopPropagation() })
		.on('click.bs.dropdown.data-api'  , toggle, Dropdown.prototype.toggle)
		.on('keydown.bs.dropdown.data-api', toggle + ', [role=menu]' , Dropdown.prototype.keydown)

}(jQuery);

/* ========================================================================
 * Bootstrap: tooltip.js v3.0.3
 * http://getbootstrap.com/javascript/#tooltip
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// TOOLTIP PUBLIC CLASS DEFINITION
	// ===============================

	var Tooltip = function (element, options) {
		this.type       =
		this.options    =
		this.enabled    =
		this.timeout    =
		this.hoverState =
		this.$element   = null

		this.init('tooltip', element, options)
	}

	Tooltip.DEFAULTS = {
		animation: true
	, placement: 'top'
	, selector: false
	, template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
	, trigger: 'hover focus'
	, title: ''
	, delay: 0
	, html: false
	, container: false
	}

	Tooltip.prototype.init = function (type, element, options) {
		this.enabled  = true
		this.type     = type
		this.$element = $(element)
		this.options  = this.getOptions(options)

		var triggers = this.options.trigger.split(' ')

		for (var i = triggers.length; i--;) {
			var trigger = triggers[i]

			if (trigger == 'click') {
				this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
			} else if (trigger != 'manual') {
				var eventIn  = trigger == 'hover' ? 'mouseenter' : 'focus'
				var eventOut = trigger == 'hover' ? 'mouseleave' : 'blur'

				this.$element.on(eventIn  + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
				this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
			}
		}

		this.options.selector ?
			(this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
			this.fixTitle()
	}

	Tooltip.prototype.getDefaults = function () {
		return Tooltip.DEFAULTS
	}

	Tooltip.prototype.getOptions = function (options) {
		options = $.extend({}, this.getDefaults(), this.$element.data(), options)

		if (options.delay && typeof options.delay == 'number') {
			options.delay = {
				show: options.delay
			, hide: options.delay
			}
		}

		return options
	}

	Tooltip.prototype.getDelegateOptions = function () {
		var options  = {}
		var defaults = this.getDefaults()

		this._options && $.each(this._options, function (key, value) {
			if (defaults[key] != value) options[key] = value
		})

		return options
	}

	Tooltip.prototype.enter = function (obj) {
		var self = obj instanceof this.constructor ?
			obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type)

		clearTimeout(self.timeout)

		self.hoverState = 'in'

		if (!self.options.delay || !self.options.delay.show) return self.show()

		self.timeout = setTimeout(function () {
			if (self.hoverState == 'in') self.show()
		}, self.options.delay.show)
	}

	Tooltip.prototype.leave = function (obj) {
		var self = obj instanceof this.constructor ?
			obj : $(obj.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type)

		clearTimeout(self.timeout)

		self.hoverState = 'out'

		if (!self.options.delay || !self.options.delay.hide) return self.hide()

		self.timeout = setTimeout(function () {
			if (self.hoverState == 'out') self.hide()
		}, self.options.delay.hide)
	}

	Tooltip.prototype.show = function () {
		var e = $.Event('show.bs.'+ this.type)

		if (this.hasContent() && this.enabled) {
			this.$element.triggerHandler(e)

			if (e.isDefaultPrevented()) return

			var $tip = this.tip()

			this.setContent()

			if (this.options.animation) $tip.addClass('fade')

			var placement = typeof this.options.placement == 'function' ?
				this.options.placement.call(this, $tip[0], this.$element[0]) :
				this.options.placement

			var autoToken = /\s?auto?\s?/i
			var autoPlace = autoToken.test(placement)
			if (autoPlace) placement = placement.replace(autoToken, '') || 'top'

			$tip
				.detach()
				.css({ top: 0, left: 0, display: 'block' })
				.addClass(placement.split('-')[0]);

			this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

			var pos          = this.getPosition()
			var actualWidth  = $tip[0].offsetWidth
			var actualHeight = $tip[0].offsetHeight

			if (autoPlace) {
				var $parent = this.$element.parent()

				var orgPlacement = placement
				var docScroll    = document.documentElement.scrollTop || document.body.scrollTop
				var parentWidth  = this.options.container == 'body' ? window.innerWidth  : $parent.outerWidth()
				var parentHeight = this.options.container == 'body' ? window.innerHeight : $parent.outerHeight()
				var parentLeft   = this.options.container == 'body' ? 0 : $parent.offset().left

				placement = placement == 'bottom' && pos.top   + pos.height  + actualHeight - docScroll > parentHeight  ? 'top'    :
										placement == 'top'    && pos.top   - docScroll   - actualHeight < 0                         ? 'bottom' :
										placement == 'right'  && pos.right + actualWidth > parentWidth                              ? 'left'   :
										placement == 'left'   && pos.left  - actualWidth < parentLeft                               ? 'right'  :
										placement

				$tip
					.removeClass(orgPlacement)
					.addClass(placement)
			}

			var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

			this.applyPlacement(calculatedOffset, placement)
			this.$element.trigger('shown.bs.' + this.type)
		}
	}

	Tooltip.prototype.applyPlacement = function(offset, placement) {
		var replace
		var $tip   = this.tip()
		var width  = $tip[0].offsetWidth
		var height = $tip[0].offsetHeight

		// manually read margins because getBoundingClientRect includes difference
		var marginTop = parseInt($tip.css('margin-top'), 10)
		var marginLeft = parseInt($tip.css('margin-left'), 10)

		// we must check for NaN for ie 8/9
		if (isNaN(marginTop))  marginTop  = 0
		if (isNaN(marginLeft)) marginLeft = 0

		offset.top  = offset.top  + marginTop
		offset.left = offset.left + marginLeft

		$tip
			.offset(offset)
			.addClass('in')

		// check to see if placing tip in new offset caused the tip to resize itself
		var actualWidth  = $tip[0].offsetWidth
		var actualHeight = $tip[0].offsetHeight

		if (placement == 'top' && actualHeight != height) {
			replace = true
			offset.top = offset.top + height - actualHeight
		}

		if (['top', 'bottom'].indexOf(placement.split('-')[0]) === 0) {
			var delta = 0

			if (offset.left < 0) {
				delta       = offset.left * -2
				offset.left = 0

				$tip.offset(offset)

				actualWidth  = $tip[0].offsetWidth
				actualHeight = $tip[0].offsetHeight
			}

			this.replaceArrow(delta - width + actualWidth, actualWidth, 'left')
		}

		if (['left', 'right'].indexOf(placement.split('-')[0]) === 0) {
			this.replaceArrow(actualHeight - height, actualHeight, 'top')
		}

		if (replace) $tip.offset(offset)
	}

	Tooltip.prototype.replaceArrow = function(delta, dimension, position) {
		this.arrow().css(position, delta ? (50 * (1 - delta / dimension) + "%") : '')
	}

	Tooltip.prototype.setContent = function () {
		var $tip  = this.tip()
		var title = this.getTitle()

		$tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
		$tip.removeClass('fade in top bottom left right')
	}

	Tooltip.prototype.hide = function () {
		var that = this
		var $tip = this.tip()
		var e    = $.Event('hide.bs.' + this.type)

		function complete() {
			if (that.hoverState != 'in') $tip.detach()
		}

		this.$element.triggerHandler(e)

		if (e.isDefaultPrevented()) return

		$tip.removeClass('in')

		$.support.transition && this.$tip.hasClass('fade') ?
			$tip
				.one($.support.transition.end, complete)
				.emulateTransitionEnd(150) :
			complete()

		this.$element.trigger('hidden.bs.' + this.type)

		return this
	}

	Tooltip.prototype.fixTitle = function () {
		var $e = this.$element
		if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
			$e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
		}
	}

	Tooltip.prototype.hasContent = function () {
		return this.getTitle()
	}

	Tooltip.prototype.getPosition = function () {
		var el = this.$element[0]
		return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : {
			width: el.offsetWidth
		, height: el.offsetHeight
		}, this.$element.offset())
	}

	Tooltip.prototype.getCalculatedOffset = function (placement, pos, actualWidth, actualHeight) {

		return placement == 'left-top'     ? { top: pos.top, left: pos.left - actualWidth } :
					 placement == 'left-bottom'  ? { top: pos.top + pos.height - actualHeight, left: pos.left - actualWidth } :
					 placement == 'right-top'    ? { top: pos.top, left: pos.left + pos.width } :
					 placement == 'right-bottom' ? { top: pos.top + pos.height - actualHeight, left: pos.left + pos.width } :
					 placement == 'top-left'     ? { top: pos.top - actualHeight, left: pos.left } :
					 placement == 'top-right'    ? { top: pos.top - actualHeight, left: pos.left + pos.width - actualWidth } :
					 placement == 'bottom-left'  ? { top: pos.top + pos.height, left: pos.left } :
					 placement == 'bottom-right' ? { top: pos.top + pos.height, left: pos.left + pos.width - actualWidth } :
					 placement == 'bottom'       ? { top: pos.top + pos.height,   left: pos.left + pos.width / 2 - actualWidth / 2  } :
					 placement == 'top'    ? { top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2  } :
					 placement == 'left'   ? { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth } :
				/* placement == 'right' */ { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width   }
	}

	Tooltip.prototype.getTitle = function () {
		var title
		var $e = this.$element
		var o  = this.options

		title = $e.attr('data-original-title')
			|| (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)

		return title
	}

	Tooltip.prototype.tip = function () {
		return this.$tip = this.$tip || $(this.options.template)
	}

	Tooltip.prototype.arrow = function () {
		return this.$arrow = this.$arrow || this.tip().find('.tooltip-arrow')
	}

	Tooltip.prototype.validate = function () {
		if (!this.$element[0].parentNode) {
			this.hide()
			this.$element = null
			this.options  = null
		}
	}

	Tooltip.prototype.enable = function () {
		this.enabled = true
	}

	Tooltip.prototype.disable = function () {
		this.enabled = false
	}

	Tooltip.prototype.toggleEnabled = function () {
		this.enabled = !this.enabled
	}

	Tooltip.prototype.toggle = function (e) {
		var self = e ? $(e.currentTarget)[this.type](this.getDelegateOptions()).data('bs.' + this.type) : this
		self.tip().hasClass('in') ? self.leave(self) : self.enter(self)
	}

	Tooltip.prototype.destroy = function () {
		this.hide().$element.off('.' + this.type).removeData('bs.' + this.type)
	}


	// TOOLTIP PLUGIN DEFINITION
	// =========================

	var old = $.fn.tooltip

	$.fn.tooltip = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.tooltip')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.tooltip', (data = new Tooltip(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.tooltip.Constructor = Tooltip


	// TOOLTIP NO CONFLICT
	// ===================

	$.fn.tooltip.noConflict = function () {
		$.fn.tooltip = old
		return this
	}

}(jQuery);

/* ========================================================================
 * Bootstrap: popover.js v3.0.3
 * http://getbootstrap.com/javascript/#popovers
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// POPOVER PUBLIC CLASS DEFINITION
	// ===============================

	var Popover = function (element, options) {
		this.init('popover', element, options)
	}

	if (!$.fn.tooltip) throw new Error('Popover requires tooltip.js')

	Popover.DEFAULTS = $.extend({} , $.fn.tooltip.Constructor.DEFAULTS, {
		placement: 'right'
	, trigger: 'click'
	, content: ''
	, template: '<div id="kt" class="o-popover--kt"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
	})


	// NOTE: POPOVER EXTENDS tooltip.js
	// ================================

	Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype)

	Popover.prototype.constructor = Popover

	Popover.prototype.getDefaults = function () {
		return Popover.DEFAULTS
	}

	Popover.prototype.setContent = function () {
		var $tip    = this.tip()
		var title   = this.getTitle()
		var content = this.getContent()

		$tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
		$tip.find('.popover-content')[this.options.html ? 'html' : 'text'](content)

		$tip.removeClass('fade top bottom left right in')

		// IE8 doesn't accept hiding via the `:empty` pseudo selector, we have to do
		// this manually by checking the contents.
		if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
	}

	Popover.prototype.hasContent = function () {
		return this.getTitle() || this.getContent()
	}

	Popover.prototype.getContent = function () {
		var $e = this.$element
		var o  = this.options

		return $e.attr('data-content')
			|| (typeof o.content == 'function' ?
						o.content.call($e[0]) :
						o.content)
	}

	Popover.prototype.arrow = function () {
		return this.$arrow = this.$arrow || this.tip().find('.arrow')
	}

	Popover.prototype.tip = function () {
		if (!this.$tip) this.$tip = $(this.options.template)
		return this.$tip
	}


	// POPOVER PLUGIN DEFINITION
	// =========================

	var old = $.fn.popover

	$.fn.popover = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.popover')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.popover', (data = new Popover(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.popover.Constructor = Popover


	// POPOVER NO CONFLICT
	// ===================

	$.fn.popover.noConflict = function () {
		$.fn.popover = old
		return this
	}

}(jQuery);

/* ========================================================================
 * Bootstrap: collapse.js v3.0.3
 * http://getbootstrap.com/javascript/#collapse
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// COLLAPSE PUBLIC CLASS DEFINITION
	// ================================

	var Collapse = function (element, options) {
		this.$element      = $(element)
		this.options       = $.extend({}, Collapse.DEFAULTS, options)
		this.transitioning = null

		if (this.options.parent) this.$parent = $(this.options.parent)
		if (this.options.toggle) this.toggle()
	}

	Collapse.DEFAULTS = {
		toggle: true
	}

	Collapse.prototype.dimension = function () {
		var hasWidth = this.$element.hasClass('width')
		return hasWidth ? 'width' : 'height'
	}

	Collapse.prototype.show = function () {
		if (this.transitioning || this.$element.hasClass('in')) return

		var startEvent = $.Event('show.bs.collapse')
		this.$element.triggerHandler(startEvent)
		if (startEvent.isDefaultPrevented()) return

		var actives = this.$parent && this.$parent.find('> .panel > .in')

		if (actives && actives.length) {
			var hasData = actives.data('bs.collapse')
			if (hasData && hasData.transitioning) return
			actives.collapse('hide')
			hasData || actives.data('bs.collapse', null)
		}

		var dimension = this.dimension()

		this.$element
			.removeClass('collapse')
			.addClass('collapsing')
			[dimension](0)

		this.transitioning = 1

		var complete = function () {
			this.$element
				.removeClass('collapsing')
				.addClass('in')
				[dimension]('auto')
			this.transitioning = 0
			this.$element.trigger('shown.bs.collapse')
		}

		if (!$.support.transition) return complete.call(this)

		var scrollSize = $.camelCase(['scroll', dimension].join('-'))

		this.$element
			.one($.support.transition.end, $.proxy(complete, this))
			.emulateTransitionEnd(350)
			[dimension](this.$element[0][scrollSize])
	}

	Collapse.prototype.hide = function () {
		if (this.transitioning || !this.$element.hasClass('in')) return

		var startEvent = $.Event('hide.bs.collapse')
		this.$element.triggerHandler(startEvent)
		if (startEvent.isDefaultPrevented()) return

		var dimension = this.dimension()

		this.$element
			[dimension](this.$element[dimension]())
			[0].offsetHeight

		this.$element
			.addClass('collapsing')
			.removeClass('collapse')
			.removeClass('in')

		this.transitioning = 1

		var complete = function () {
			this.transitioning = 0
			this.$element
				.trigger('hidden.bs.collapse')
				.removeClass('collapsing')
				.addClass('collapse')
		}

		if (!$.support.transition) return complete.call(this)

		this.$element
			[dimension](0)
			.one($.support.transition.end, $.proxy(complete, this))
			.emulateTransitionEnd(350)
	}

	Collapse.prototype.toggle = function () {
		this[this.$element.hasClass('in') ? 'hide' : 'show']()
	}


	// COLLAPSE PLUGIN DEFINITION
	// ==========================

	var old = $.fn.collapse

	$.fn.collapse = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.collapse')
			var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

			if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.collapse.Constructor = Collapse


	// COLLAPSE NO CONFLICT
	// ====================

	$.fn.collapse.noConflict = function () {
		$.fn.collapse = old
		return this
	}


	// COLLAPSE DATA-API
	// =================

	$(document).on('click.bs.collapse.data-api', '[data-bp-toggle=collapse]', function (e) {
		var $this   = $(this), href
		var target  = $this.attr('data-target')
				|| e.preventDefault()
				|| (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
		var $target = $(target)
		var data    = $target.data('bs.collapse')
		var option  = data ? 'toggle' : $this.data()
		var parent  = $this.attr('data-parent')
		var $parent = parent && $(parent)

		if (!data || !data.transitioning) {
			if ($parent) $parent.find('[data-bp-toggle=collapse][data-parent="' + parent + '"]').not($this).addClass('collapsed')
			$this[$target.hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
		}

		$target.collapse(option)
	})

}(jQuery);


/* ========================================================================
 * Bootstrap: tab.js v3.0.3
 * http://getbootstrap.com/javascript/#tabs
 * ========================================================================
 * Copyright 2013 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================================== */


+function ($) { "use strict";

	// TAB CLASS DEFINITION
	// ====================

	var Tab = function (element) {
		this.element = $(element)
	}

	Tab.prototype.show = function () {
		var $this    = this.element
		var $ul      = $this.closest('ul:not(.dropdown-menu)')
		var selector = $this.data('target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
		}

		if ($this.parent('li').hasClass('active')) return

		var previous = $ul.find('.active:last a')[0]
		var e        = $.Event('show.bs.tab', {
			relatedTarget: previous
		})

		$this.triggerHandler(e)

		if (e.isDefaultPrevented()) return

		var $target = $(selector)

		this.activate($this.parent('li'), $ul)
		this.activate($target, $target.parent(), function () {
			$this.trigger({
				type: 'shown.bs.tab'
			, relatedTarget: previous
			})
		})
	}

	Tab.prototype.activate = function (element, container, callback) {
		var $active    = container.find('> .active')
		var transition = callback
			&& $.support.transition
			&& $active.hasClass('fade')

		function next() {
			$active
				.removeClass('active')
				.find('> .dropdown-menu > .active')
				.removeClass('active')

			element.addClass('active')

			if (transition) {
				element[0].offsetWidth // reflow for transition
				element.addClass('in')
			} else {
				element.removeClass('fade')
			}

			if (element.parent('.dropdown-menu')) {
				element.closest('li.dropdown').addClass('active')
			}

			callback && callback()
		}

		transition ?
			$active
				.one($.support.transition.end, next)
				.emulateTransitionEnd(150) :
			next()

		$active.removeClass('in')
	}


	// TAB PLUGIN DEFINITION
	// =====================

	var old = $.fn.tab

	$.fn.tab = function ( option ) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.tab')

			if (!data) $this.data('bs.tab', (data = new Tab(this)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.tab.Constructor = Tab


	// TAB NO CONFLICT
	// ===============

	$.fn.tab.noConflict = function () {
		$.fn.tab = old
		return this
	}


	// TAB DATA-API
	// ============

	$(document).on('click.bs.tab.data-api', '[data-bs-toggle="tab"], [data-bs-toggle="pill"]', function (e) {
		e.preventDefault()
		$(this).tab('show')
	})

}(jQuery);


});
KTVendors.plugin("utils", function($) {

/**
 * jquery.Bloop
 * Binary loop helper.
 * https://github.com/jstonne/jquery.Bloop
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne & Jason Rey
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(){

	var Bloop = function(items) {

		this.items = items;
		this.start = 0;
		this.end = items.length - 1;
		this.node = null;
		this.stopped = false;
	};

	$.extend(Bloop.prototype, {

		isLooping: function() {

			if (this.stopped) return false;

			if (Math.abs(this.start - this.end) > 1) {
				this.node = Math.floor((this.start + this.end) / 2);
				return true;
			}

			return false;
		},

		flip: function(flip) {

			if (flip) {
				this.end = this.node - 1;
			} else {
				this.start = this.node + 1;
			}
		},

		stop: function() {
			this.stop = true;
		}
	});


	$.Bloop = function(items){

		return new Bloop(items);
	}

})();
;/*!
 * jquery.Chunk
 * Utility to handle large arrays by processing
 * them in smaller manageable chunks.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.Chunk = function(array, options) {

	if ($.isArray(array)) {
		array = [];
	}

	var options = $.extend({},
		{
			size: 256,
			every: 1000
		},
		options
	);

	var self = $.extend($.Deferred(), {

		size: options.size,

		every: options.every,

		from: 0,

		to: array.length,

		process: function(callback) {

			self.process.fn = callback;

			return self;
		},

		chunkStart: function(callback) {

			self.chunkStart.fn = callback;

			return self;
		},

		chunkEnd: function(callback) {

			self.chunkEnd.fn = callback;

			return self;
		},

		start: function() {

			self.stopped = false;

			self.iterate();

			return self;
		},

		iterate: function() {

			if (self.stopped) return;

			var iterator = self.process.fn;

			if (!iterator) return;

			self.to = from.size + self.size;

			var max = array.length;

			if (self.to > max) {

				self.to = max;
			}

			var range = {from: self.from, to: self.to};

			// Trigger chunkStart event
			self.chunkStart.fn && self.chunkStart.fn.call(self, range.from, range.to);

			while (self.from < self.to) {

				if (self.stopped) break;

				iterator.call(self, array[self.from]);

				self.from++;
			}

			// Trigger chunkEnd event
			self.chunkEnd.fn && self.chunkEnd.fn.call(self, range.from, range.to);

			// Always get the latest array length because
			// it may change through iteration
			self.completed = (self.from >= array.length - 1);

			if (self.completed) {

				self.resolveWith(self);

			} else {

				self.nextIteration = setTimeout(self.iterate, self.every);
			}

			return self;
		},

		pause: function() {

			self.stopped = true;

			clearTimeout(self.nextIteration);

			return self;
		},

		restart: function() {

			if (self.state()==="rejected") return self;

			self.from = 0;

			self.start();

			return self;
		},

		stop: function() {

			self.pause();

			self.rejectWith(self, [self.from]);

			return self;
		}
	});

	return self;
};
;/**
 * jquery.Enqueue
 * Execute only the last added callback.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne & Jason Rey
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(isFunction) {

	var Enqueue = function() {
		this.lastId = 0;
	};

	Enqueue.prototype.queue = function(filter) {

		var self = this,
			id = $.uid();
			self.lastId = id;

		return function() {

			if (self.lastId===id) {

				var args = arguments,
					args = (isFunction(filter)) ? filter.apply(this, args) : args;

				return (isFunction(self.fn)) ? self.fn.apply(this, args) : args;
			}
		}
	};

	$.Enqueue = function(fn) {

		var self = new Enqueue();

		if (isFunction(fn)) self.fn = fn;

		var func = $.proxy(self.queue, self);

		func.reset = function() {
			self.lastId = 0;
		};

		return func;
	};
})($.isFunction);
;/**
 * jquery.Exception
 * Standardized exception object.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(){
	var consoleMethod = {
		error: "error",
		warning: "warn",
		success: "log",
		info: "info"
	};

	// $.Exception("message");
	// $.Exception("success", "message");
	// $.Exception("error", "message", data);
	// $.Exception({type: "info", message: "message", foo: "bar", key: "val"});
	$.Exception = function(exception) {

		// Normalize arguments
		var args = arguments,
			simple = args.length==1,
			hasData = args.length==3;

		exception = $.isPlainObject(exception) ?
			exception :
			{
				type   : simple ? "error" : args[0],
				message: simple ? args[0] : args[1]
			}

		hasData && $.extend(exception, args[2]);

		if ($.environment=="development") {
			console[consoleMethod[exception.type]](exception.message, exception);
		}

		return exception;
	}
})();;/**
 * jquery.IE
 * Returns the current IE version.
 *
 * Based on Padolsey's IE detection script.
 * https://gist.github.com/padolsey/527683
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.IE = (function(){

	// It seems Padolsey's IE detection script
	// doesn't work on IE10 and 11.
	var ua = navigator.userAgent;
	if (ua.match(/MSIE 9/)) return 9;
	if (ua.match(/MSIE 10/)) return 10;
	if (ua.match(/rv:11/i)) return 11;

	var undef,
		v = 3,
		div = document.createElement('div'),
		all = div.getElementsByTagName('i');

	while (
		v++,
		div.innerHTML = '<!--[if gt IE ' + v + ']><i></i><![endif]-->',
		all[0]
	);

	return v > 4 ? v : undef;

}());;/**
 * jquery.Task
 * Task runner utility.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.Task = function(props) {

	var task = $.extend(
		$.Deferred(),
		{
			data: {},
			list: [],
			add: function(name) {

				var item = $.extend(
					$.Deferred(),
					{
						name: name,
						item: item
					}
				);

				task.list.push(item);

				return item;
			},
			process: function() {

				if (!task._promise) {

					task._promise =
						$.when.apply($, task.list)
							.then(
								task.resolve,
								task.reject,
								task.progress
							);
				}

				return task;
			}
		},
		props
	);

	return task;
};;/**
 * jquery.Threads
 * A manager that controls threads a.k.a. execution of function simultaneously.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne & Jason Rey
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function() {

	var Threads = function(options) {
		this.threads = [];
		this.threadCount = 0;
		this.threadLimit = options.threadLimit || 1;
		this.threadDelay = options.threadDelay || 0;
	}

	$.extend(Threads.prototype, {

		add: function(thread, type) {

			if (!$.isFunction(thread)) return;

			thread.type = type || "normal";

			if (type=="deferred") {
				thread.deferred = $.Deferred().always($.proxy(this.next, this));
			}

			this.threads.push(thread);

			this.run();
		},

		addDeferred: function(thread) {

			return this.add(thread, "deferred");
		},

		next: function() {

			// Reduce thread count
			this.threadCount--;

			// And see if there's anymore task to run
			this.run();
		},

		run: function() {

			var self = this;

			setTimeout(function(){

				if (self.threads.length < 1) return;

				if (self.threadCount < self.threadLimit) {

					self.threadCount++;

					var thread = self.threads.shift();

					// Wrap in a try catch in case if the thread
					// throws an error it doesn't break our chain.
					try { thread.call(thread, thread.deferred); }
					catch(e) { console.error(e); }

					!thread.deferred && self.next();
				}

			}, self.threadDelay);
		}
	});

	$.Threads = function(options) {

		return new Threads(options);
	};

})();
;/**
 * jquery.callback
 * Creates a global callback function that gets
 * removed from the window object after it has executed.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.callback = function(func, persist){

	// Create callback
	if ($.isFunction(func)) {

		var funcName = $.uid("cb");

		window[funcName] = function(){

			// Destroy itself after callback has been called
			if (!persist) {
				delete window[funcName];
			}

			return func.apply(null, arguments);
		}

		return funcName;
	}

	// Callback method
	if ($.isString(func)) {
		switch (func) {
			case "destroy":
				var funcName = persist;
				delete window[funcName];
				break;
		}
	}
};/**
 * jquery.fn.checkList.
 * Multiple checkbox handler.
 *
 * $(e).checkList({
 *    check  : function(){},   // callback when an input is checked
 *    uncheck: function(){},   // callback when an input is unchecked
 *
 *    // returns checked elements & unchecked elements in separate arguments
 *    change : function(checked, unchecked){}
 * })
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.checkList = function(options) {

	var defaultOptions = {
		checkbox: ".checkbox",
		masterCheckbox: ".master-checkbox",
		check: function() {},
		uncheck: function() {},
		change: function() {}
	}

	var options = $.extend({}, defaultOptions, options),
		checkList       = this,
		checkboxes      = checkList.find(options.checkbox),
		masterCheckbox  = checkList.find(options.masterCheckbox),
		disableChangeEvent = false;

	var change = function() {

		if (!disableChangeEvent) {

			var checked = checkboxes.filter(':checked'),
				unchecked = checkboxes.not(':checked');

			if (checked.length < 1) {
				masterCheckbox.removeAttr("checked");
			}

			if (checked.length == checkboxes.length) {
				masterCheckbox.prop("checked", true);
			}

			options.change.call(checkList, checked, unchecked);
		}
	}

	checkboxes.checked(

		// checked
		function() {
			options.check.apply(checkList);
			change();
		},

		// unchecked
		function() {
			options.uncheck.apply(checkList);
			change();
		}
	);

	masterCheckbox.checked(

		// checked
		function() {
			disableChangeEvent = true;
			checkboxes.checked(true);
			disableChangeEvent = false;
			change();
		},

		// unchecked
		function() {
			disableChangeEvent = true;
			checkboxes.checked(false);
			disableChangeEvent = false;
			change();
		}
	);

	change();

	return this;
};;/**
 * jquery.classManip
 * Utilities to manipulate classnames.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * $.fn.switchClass
 * Swaps a classname for another classname that bears identical prefix.
 *
 * $("div").switchClass("state-busy")l;
 *
 * Before:
 * <div class="state-idle"></div>
 *
 * After:
 * <div class="state-busy"></div>
 */
$.fn.switchClass = function(classname, delimiter){

	var delimiter = delimiter || "-",
		prefix = classname.split(delimiter)[0] + delimiter,
		length = prefix.length;

	return this.each(function(){

		var $el = $(this),
			classnames =
				$.map(($el.attr("class") || "").split(" "), function(classname){
					return (classname.slice(0, length)==prefix || classname=="") ? null : classname;
				});
			classnames.push(classname);

		$el.attr("class", classnames.join(" "));
	});
};

/**
 * $.fn.activateClass
 * Add classname on current set of elements and
 * remove classname on previous set of elements.
 *
 * $(".item").find("[data-id=64]").activateClass("active");
 *
 * Before:
 * <div class="item active" data-id="62"></div>
 * <div class="item" data-id="63"></div>
 * <div class="item" data-id="64"></div>
 *
 * After:
 * <div class="item" data-id="62"></div>
 * <div class="item" data-id="63"></div>
 * <div class="item active" data-id="64"></div>
 */
$.fn.activateClass = function(className) {
	this.prevObject.removeClass(className);
	return $(this).addClass(className);
};;/**
 * jquery.color
 * Color helpers.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
(function(){

var hexToRgb = function(hex) {
	var hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
	return {r: hex >> 16, g: (hex & 0x00FF00) >> 8, b: (hex & 0x0000FF)};
};

var hexToHsb = function(hex) {
	return rgbToHsb(hexToRgb(hex));
};

var rgbToHsb = function(rgb) {
	var hsb = {h: 0, s: 0, b: 0};
	var min = Math.min(rgb.r, rgb.g, rgb.b);
	var max = Math.max(rgb.r, rgb.g, rgb.b);
	var delta = max - min;
	hsb.b = max;
	hsb.s = max != 0 ? 255 * delta / max : 0;
	if (hsb.s != 0) {
		if (rgb.r == max) hsb.h = (rgb.g - rgb.b) / delta;
		else if (rgb.g == max) hsb.h = 2 + (rgb.b - rgb.r) / delta;
		else hsb.h = 4 + (rgb.r - rgb.g) / delta;
		hsb.h *= 60;
	} else hsb.h = 360;
	if (hsb.h < 0) hsb.h += 360;
	hsb.s *= 100/255;
	hsb.b *= 100/255;
	return hsb;
};

var hsbToRgb = function(hsb) {
	var rgb = {};
	var h = hsb.h;
	var s = hsb.s*255/100;
	var v = hsb.b*255/100;
	if(s == 0) {
		rgb.r = rgb.g = rgb.b = v;
	} else {
		var t1 = v;
		var t2 = (255-s)*v/255;
		var t3 = (t1-t2)*(h%60)/60;
		if(h==360) h = 0;
		if(h<60) {rgb.r=t1; rgb.b=t2; rgb.g=t2+t3}
		else if(h<120) {rgb.g=t1; rgb.b=t2; rgb.r=t1-t3}
		else if(h<180) {rgb.g=t1; rgb.r=t2; rgb.b=t2+t3}
		else if(h<240) {rgb.b=t1; rgb.r=t2; rgb.g=t1-t3}
		else if(h<300) {rgb.b=t1; rgb.g=t2; rgb.r=t2+t3}
		else if(h<360) {rgb.r=t1; rgb.g=t2; rgb.b=t1-t3}
		else {rgb.r=0; rgb.g=0; rgb.b=0}
	}
	return {r:Math.round(rgb.r), g:Math.round(rgb.g), b:Math.round(rgb.b)};
};

var rgbToHex = function(rgb) {
	var hex = [
		rgb.r.toString(16),
		rgb.g.toString(16),
		rgb.b.toString(16)
	];
	$.each(hex, function (nr, val) {
		if (val.length == 1) {
			hex[nr] = '0' + val;
		}
	});
	return hex.join('');
};

var hsbToHex = function (hsb) {
	return rgbToHex(hsbToRgb(hsb));
};

var fixHsb = function (hsb) {
	return {
		h: Math.min(360, Math.max(0, hsb.h)),
		s: Math.min(100, Math.max(0, hsb.s)),
		b: Math.min(100, Math.max(0, hsb.b))
	};
};

var fixRgb = function (rgb) {
	return {
		r: Math.min(255, Math.max(0, rgb.r)),
		g: Math.min(255, Math.max(0, rgb.g)),
		b: Math.min(255, Math.max(0, rgb.b))
	};
};

var fixHex = function (hex) {
	var len = 6 - hex.length;

	if (len == 3) {
		var chars = hex.split(""), chr, hex = "";
		while (chr = chars.shift()) hex += chr + chr;
	} else {
		while (len--) hex = "0" + hex;
	}

	hex.replace(/[^A-Fa-f0-9]/g, "0");

	return hex;
};

$.extend($, {
	hexToRgb: hexToRgb,
	hexToHsb: hexToHsb,
	rgbToHsb: rgbToHsb,
	hsbToRgb: hsbToRgb,
	rgbToHex: rgbToHex,
	hsbToHex: hsbToHex,
	fixHsb: fixHsb,
	fixRgb: fixRgb,
	fixHex: fixHex
});

})();;;/**
 * jquery.fn.htmlData
 * Utilities to handle data within jQuery elements.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * jquery.fn.htmlData
 * Converts inline data attributes into objects.
 */
$.fn.htmlData = function(prefix, nested) {

	var nested = nested===undefined ? true : nested,
		re = new RegExp("^" + "data-" + (prefix ? prefix + "-" : "") + "(.*)", "i"),
		parts,
		data = {};

	if (this.length <= 0) {
		return {};
	}

	// Extract options from data attributes
	$.each(this[0].attributes, function(i, attr){

		if (attr.specified && (parts = attr.name.match(re)) && parts[1]) {
			if (nested) {
				var props = parts[1].split("-"),
					i, prop, obj = data; max = props.length - 1;

				for (i=0; i<=max; i++) {
					prop = props[i];
					if (i==max) {
						obj[prop] = attr.value;
					} else {
						!obj[prop] && (obj[prop] = {});
						obj = obj[prop];
					}
				}
			} else {
				data[parts[1]] = attr.value;
			}
		}
	});

	return data;
};

/**
 * jquery.fn.defineData
 * Creates persistent data that cannot be changed.
 */
$.fn.defineData = function(name, value) {

	if (this.data(name)===undefined) {
		this.data(name, value);
	}

	return this;
};/**
 * jquery.deletes
 * Remove properties from objects.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.deletes = function(obj, props) {
	$.each(props, function(i, prop){
		delete obj[prop];
	});
};
;/**
 * jquery.fn.disabled
 * jquery.fn.enabled
 *
 * Determine if an element is disabled.
 * Also lets you disable or enable an element.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */


$.fn.disabled = function(state) {
	return (state===undefined) ?
				(this.is(":disabled") || this.hasClass('disabled')) :
				this.prop('disabled', !!state).toggleClass("disabled", !!state);
};

$.fn.enabled = function(state) {
	return (state===undefined) ? !this.disabled() : this.disabled(!state);
};
;/**
 * jquery.distinct
 * Enhanced version of jQuery.unique that also removes
 * removes object/string/integer duplicates within an array.
 * https://github.com/jstonne/jquery.distinct
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.distinct = function(items) {

	var uniqueElements = $.unique;

	if (items.length < 1) {
		return;
	};

	// If item is an array of DOM elements
	if (items[0].nodeType) {

		return uniqueElements.apply(this, arguments);
	};

	// If item is an array of objects
	if (typeof items[0]=='object') {

		var unique = Math.random(),
			uniqueObjects = [];

		$.each(items, function(i) {

			if (!items[i][unique]) {

				uniqueObjects.push(items[i]);

				items[i][unique] = true;
			}
		});

		$.each(uniqueObjects, function(i) {

			delete uniqueObjects[i][unique];
		});

		return uniqueObjects;
	};

	// Anything else (can be combination of string, integers and boolean)
	return $.grep(items, function(item, i) {

		return $.inArray(item, items) === i;
	});

};
;/**
 * jquery.fn.domManip
 * Shorthands for common DOM operations.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.tagName = function(){
	return (this[0] || {}).tagName;
};

$.create = function(tagName) {
	return $(document.createElement(tagName));
};

$.fn.editable = function(editable) {
	if ($.isUndefined(editable)) return this.prop("contenteditable")==="true";
	this.prop("contenteditable", editable);
	editable===false && this.removeAttr("contenteditable");
	return this;
};/**
 * jquery.download
 * Simulate a download programatically.
 *
 * The download url should return the correct
 * Content-Type in the response headers to work.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.download = function(src) {
	return $("<iframe>").hide().appendTo("body").bind("load", function(){$(this).remove()}).attr("src", src);
};;/**
 * jquery.eventManip
 * Utilities to handle events in jQuery.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * jquery.ns
 * Adds namespace to events.
 * $(el).on($.ns("mousedown keyup keydown", ".foobar"), function(){});
 */
$.ns = function(event, ns) {
	return event.split(" ").join(ns + " ") + ns;
};


/**
 * jquery.getPointerPosition
 * Get pointer position whether it came from mouse or touch events.
 */
$.getPointerPosition = function(event) {

	return event.type.match("touch") ?
		{
			x: event.originalEvent.changedTouches[0].pageX,
			y: event.originalEvent.changedTouches[0].pageY
		} :
		{
			x: event.pageX,
			y: event.pageY
		};
};;/**
 * jquery.eventable
 * Extend objects with simple event system.
 *
 * Requires jquery.deletes.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function() {

	var instance = "___eventable",
		publicMethods = ["on", "off", "fire"],
		getEventName = function(name){
			return name.split(".")[0];
		};

	var Eventable = function(mode) {
		this.fnList = {};
		this.events = {};
		this.mode = mode;
	}

	$.extend(
		Eventable.prototype,
		{
			createEvent: function(name) {

				return this.events[name] = $.Callbacks(this.mode);
			},

			on: function(name, fn) {

				if (!name || !$.isFunction(fn)) return this;

				var fnList = this.fnList;

				(fnList[name] || (fnList[name] = [])).push(fn);

				// Translate into base event name
				var basename = getEventName(name);

				// Add the event
				(this.events[basename] || this.createEvent(basename)).add(fn);

				return this;
			},

			off: function(name) {

				if (!name) return this;

				var basename = getEventName(name),
					event = this.events[basename];

				if (!event) return this;

				var removeCallbacks = function(fnList) {

					$.each(fnList, function(i, fn) {
						event.remove(fn);
					});
				}

				if (basename!==name) {

					$.each(this.fnList, function(name, fnList) {

						if (name.indexOf(basename) > -1) {

							removeCallbacks(fnList);
						}
					});

				} else {

					removeCallbacks(this.fnList[name]);
				}

				return this;
			},

			fire: function(name) {

				var event = this.events[name];

				if (!event) return;

				event.fire.apply(event, $.makeArray(arguments).slice(1));

				return this;
			},

			destroy: function() {
				for (name in this.events) {
					this.events[name].disable();
				}
			}
		}
	);

	$.eventable = function(obj, mode) {

		var eventable = obj[instance];

		if (eventable && mode==="destroy") {
			eventable.destroy();
			$.deletes(obj, publicMethods);
			return delete obj[instance];
		}

		eventable = obj[instance] = new Eventable(mode);

		obj.on = $.proxy(eventable.on, eventable);
		obj.off = $.proxy(eventable.off, eventable);
		obj.fire = $.proxy(eventable.fire, eventable);

		return obj;
	}

})();
;/**
 * jquery.fn.checkList.
 * Multiple checkbox handler.
 *
 * $(e).checkList({
 *    check  : function(){},   // callback when an input is checked
 *    uncheck: function(){},   // callback when an input is unchecked
 *
 *    // returns checked elements & unchecked elements in separate arguments
 *    change : function(checked, unchecked){}
 * })
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.checkList = function(options) {

	var defaultOptions = {
		checkbox: ".checkbox",
		masterCheckbox: ".master-checkbox",
		check: function() {},
		uncheck: function() {},
		change: function() {}
	}

	var options = $.extend({}, defaultOptions, options),
		checkList       = this,
		checkboxes      = checkList.find(options.checkbox),
		masterCheckbox  = checkList.find(options.masterCheckbox),
		disableChangeEvent = false;

	var change = function() {

		if (!disableChangeEvent) {

			var checked = checkboxes.filter(':checked'),
				unchecked = checkboxes.not(':checked');

			if (checked.length < 1) {
				masterCheckbox.removeAttr("checked");
			}

			if (checked.length == checkboxes.length) {
				masterCheckbox.prop("checked", true);
			}

			options.change.call(checkList, checked, unchecked);
		}
	}

	checkboxes.checked(

		// checked
		function() {
			options.check.apply(checkList);
			change();
		},

		// unchecked
		function() {
			options.uncheck.apply(checkList);
			change();
		}
	);

	masterCheckbox.checked(

		// checked
		function() {
			disableChangeEvent = true;
			checkboxes.checked(true);
			disableChangeEvent = false;
			change();
		},

		// unchecked
		function() {
			disableChangeEvent = true;
			checkboxes.checked(false);
			disableChangeEvent = false;
			change();
		}
	);

	change();

	return this;
};/**
 * jquery.fn.checked
 * Checked/unchecked event handler for checkbox & radio button.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.checked = function(checked, unchecked) {

	// Return checked value if no arguments are given;
	if (arguments.length < 1)
		return this.is(':checked');

	this.each(function(i) {

		var input = $(this);

		if (typeof checked == "boolean") {
			input.attr('checked', checked).trigger('change');
			return;
		}

		if (input.is('input[type=checkbox]') || input.is('input[type=radio]')) {
			input
				.off('change.checked')
				.on('change.checked', function() {
					try {
						return (input.is(':checked')) ? checked.apply(input) : unchecked.apply(input);
					} catch(e) {};
				});
		}
	});

	return this;
};
;/**
 * jquery.fn.locate
 * Locate a related child element based on data attribute.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.locate = function(key) {

	var prefix = "data";

	$.each(this[0].attributes, function(i, attr){
		if (attr.specified && attr.value==="$") {
			prefix = attr.name;
			return false;
		}
	});

	return this.find("[" + prefix + "-" + key.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase() + "]");
};
;/**
 * jquery.fn.noscroll
 * Disable scrollbar on elements
 * with the ability to restore it.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(){

	var props = ["overflow", "overflow-x", "overflow-y"];

	$.fn.noscroll = function(lock) {

		if (lock===undefined) lock = true;

		return this.each(function(){

			var el = $(this),
				overflow = el.data("noscroll");

			// No original overflow values was stored before
			if (!overflow && lock) {

				// Get the original overflow values
				overflow = {};
				$.each(props, function(i, prop){
					overflow[prop] = el.css(prop);
				});

				// Store original values
				el.data("noscroll", overflow);
			}

			if (lock) {
				$.each(props, function(i, prop){
					el.css(prop, "hidden");
				});
			} else {
				overflow && el.css(overflow);
			}
		});
	};

})();
;/**
* Copyright 2012, Digital Fusion
* Licensed under the MIT license.
* http://teamdf.com/jquery-plugins/license/
*
* @author Sam Sehnert
* @desc A small plugin that checks whether elements are within
* the user visible viewport of a web browser.
* only accounts for vertical position, not horizontal.
*/

$.fn.visible = function(partial) {

	var $t = $(this),
		$w = $(window);

	if ($t.length < 1) return;

	var viewTop      = $w.scrollTop(),
		viewBottom   = viewTop + $w.height(),
		_top         = $t.offset().top,
		_bottom      = _top + $t.height(),
		compareTop    = partial === true ? _bottom : _top,
		compareBottom = partial === true ? _top : _bottom;

	return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
};;/**
 * jquery.fn.where
 * Filter jQuery elements by data attributes.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.where = $.fn.filterBy = function(key, val, operator) {

	var operator = operator || "=",
		selector = "[data-" + key.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase() + operator + val + "]";

	return this.filter(selector);
};
;/**
 * jquery.formManip
 * Utilities to manipulate form elements.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

// For checkboxes and radio buttons
$.fn.checked = function(checked, unchecked) {

	// Return checked value if no arguments are given;
	if (arguments.length < 1)
		return this.is(':checked');

	this.each(function(i) {

		var input = $(this);

		if (typeof checked == "boolean") {
			input.attr('checked', checked).trigger('change');
			return;
		}

		if (input.is('input[type=checkbox]') || input.is('input[type=radio]')) {
			input
				.off('change.checked')
				.on('change.checked', function() {
					try {
						return (input.is(':checked')) ? checked.apply(input) : unchecked.apply(input);
					} catch(e) {};
				});
		}
	});

	return this;
};

// For select boxes
$.fn.selectAll = function() {
	return this.each(function(){this.select()});
};

$.fn.unselect = function() {
	return this.each(function(){
		var input = this,
			value = input.value;
			input.value += " ";
			input.value = value;
	});
};;/**
 * jquery.formSerializers
 * Serializes form values to Object or JSON.
 * Utilities to manipulate html content.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.fn.toObject = $.fn.serializeObject = function() {

	var obj = {};

	$.each($(this).serializeArray(), function(i, prop) {
		if (obj.hasOwnProperty(prop.name)) {
			// Convert it into an array
			if (!$.isArray(obj[prop.name])) {
				obj[prop.name] = [obj[prop.name]];
			}
			obj[prop.name].push(prop.value);
		} else {
			obj[prop.name] = prop.value;
		}
	});

	return obj;
};

$.fn.toJSON = $.fn.serializeJSON = function() {

	return JSON.stringify($(this).serializeObject());
};
;/**
 * jquery.htmlManip
 * Utilities to manipulate html content.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.sanitizeHTML = function(html) {
	return $($.parseHTML(html, document, true)).toHTML();
};

// This also encodes html entities.
$.toHTML = function(str) {
	return $("<div>").html(str).html();
};

$.fn.toHTML = function() {
	return $.toHTML(this.clone());
};

// Based on http://stackoverflow.com/questions/1231770/innerhtml-removes-attribute-quotes-in-internet-explorer
$.toXHTML = function(obj, maintainUppercaseTag) {

	var zz = obj.innerHTML ? String(obj.innerHTML) : obj,
		z  = zz.match(/(<.+[^>])/g);

	if (z) {
		for (var i=0; i<z.length; (i=i+1)) {

			var y,
				zSaved = z[i],
				attrRE = /\=[a-zA-Z\.\:\[\]_\(\)\&\$\%#\@\!0-9\/]+[?\s+|?>]/g;

			z[i] =
				z[i].replace(/([<|<\/].+?\w+).+[^>]/, function(a){
					return a;
				});

			y = z[i].match(attrRE);

			if (y) {
				var j = 0,
					len = y.length;

				while (j < len) {

					var replaceRE = /(\=)([a-zA-Z\.\:\[\]_\(\)\&\$\%#\@\!0-9\/]+)?([\s+|?>])/g,
						replacer = function() {
							var args = Array.prototype.slice.call(arguments);
							return '="' + (maintainUppercaseTag ? args[2] : args[2].toLowerCase()) + '"' + args[3];
						};

					z[i] = z[i].replace(y[j], y[j].replace(replaceRE,replacer));
					j += 1;
				}
			}

			zz = zz.replace(zSaved,z[i]);
		}
	}

	return zz;
};

$.fn.xhtml = function() {
	return $.IE ? $.toXHTML(this[0]) : this.html();
};

/**
 * jquery.buildHTML
 * Converts html string into jQuery element where
 * script tags within it gets removed after it is
 * inserted into the DOM.
 *
 * Using $.buildHTML(html) over $(html) also circumvents
 * CloudFlare from modifying the execution behaviour of
 * script elements.
 */

$.buildHTML = function(html, keepScripts) {

	// If a jquery element was passed in, return as it is.
	if (html instanceof $) return html;

	var doc = document;

	// If CloudFlare exists, use document from iframe
	// because CloudFlare Rocketscript overrides native methods.
	if (window["CloudFlare"]) {

		var iframe = $.buildHTML.iframe;

		// If iframe wasn't created, or iframe was removed or detached,
		// create the iframe element again;
		if (!iframe || !iframe.contentDocument) {

			// Create iframe
			var iframe =
				$.buildHTML.iframe =
				document.createElement("iframe");

			// Hide iframe
			iframe.style.display = "none";

			// Append iframe to body
			document.body.appendChild(iframe);
		}

		doc = iframe.contentDocument;
	}

	// Trim out any whitespace so no unusable text nodes are introduced.
	var html = $.trim(html),

		// Build html fragment while keeping a separate reference to the script
		scripts = [],
		fragment = $.buildFragment([html], doc, scripts),

		// Convert childNodes into a proper array
		nodes = $.merge([], fragment.childNodes);

	// If we want to remove the script after
	// it is appended to the DOM & executed
	if (!keepScripts && scripts.length > 0) {

		// Create script remover
		var script = doc.createElement("script");
			// This is wrapped in try..catch because Cloudflare's
			// proxy node executes this twice for some reason.
			// The second time this executes, the callback has been removed,
			// so let it fail silently.
			script.text = "try{" + $.callback(function(){$(scripts).remove();}) + "();}catch(e){}";

		// Go through nodes in reverse
		var i = nodes.length-1, node, inserted;

		while (node = nodes[i--]) {

			// If a script node is found first, we'll just append
			// script remover next to it to ensure this last script
			// executes before any script removal happens.
			if (node.nodeName==="SCRIPT") {
				inserted = nodes.push(script);
			} else if (node.nodeType===1) {
				inserted = node.appendChild(script);
			}

			if (inserted) break;
		}

		// If script remover was not inserted,
		// then just add it to the array of nodes
		if (!inserted) nodes.push(script);

		// Add script remover itself to the
		// array of scripts to be removed.
		scripts.push(script);
	}

	// Convert nodes into jquery instance and return
	return $(nodes);
};;/**
 * jquery.intersects
 * jquery.fn.intersectsWith
 *
 * Determines if an area intersects with another area.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.intersects = function(a, b) {

	if ($.isArray(b)) {
	   b = {top: b.y, left: b.x, bottom: b.y, right: b.x}
	}

	return (
	   b.left <= a.right  &&
	   a.left <= b.right  &&
	   b.top  <= a.bottom &&
	   a.top  <= b.bottom
	);
};

$.fn.intersectsWith = function(top, left, width, height) {

	// TODO: intersectsWith(element)

	var offset = this.offset(),

	   reference = {
			top   : offset.top,
			left  : offset.left,
			bottom: offset.top  + (sourceHeight = this.height()),
			right : offset.left + (sourceWidth  = this.width()),
			width : sourceWidth,
			height: sourceHeight
	   },

	   subject = {
			top   : top,
			left  : left,
			bottom: top  + (height || (height = 0)),
			right : left + (width  || (width  = 0)),
			width : width,
			height: height
	   };

	return ($.intersects(reference, subject)) ? {reference: reference, subject: subject} : false;
};;/**
 * jquery.isDeferred
 * Test if an object is a jQuery Deferred object.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.isDeferred = function(obj) {
	return obj && $.isFunction(obj.always);
};
;/**
 * jquery.number
 * Utilities to deal with numbers.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.isNumeric = function(n) {
	// http://stackoverflow.com/questions/18082/validate-numbers-in-javascript-isnumeric
	return !isNaN(parseFloat(n)) && isFinite(n);
};

$.rotateNumber = function(n, min, max, offset) {

	if (offset===undefined) {
		offset = 0;
	}

	n += offset;

	if (n < min) {
		n += max + 1;
	} else if (n > max) {
		n -= max + 1;
	}

	return n;
};;/**
 * jquery.regExpEscape
 * Makes string regex safe.
 * http://stackoverflow.com/questions/2593637/how-to-escape-regular-expression-in-javascript
 */

$.regExpEscape = function(str) {
	return str.replace(/([-()\[\]{}+?*.$\^|,:#<!\\])/g, '\\$1').replace(/\x08/g, '\\x08');
}
;/**
 * jquery.remap
 * Utility for remapping properties of an object selectively from another object.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.remap = function(to, from, props) {
	$.each(props, function(i, prop){
		to[prop] = from[prop];
	});
	return obj;
};
;/**
 * jquery.throttledAjax
 * jQuery AJAX with throttling.
 *
 * Requires jquery.Threads.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(){

var self = $.Ajax = function(options) {

	// Start ajax manually
	options.autostart = false;

	var ajax = $.ajax(options);

	if ('function' == typeof ajax.send) {
		self.queue
			.addDeferred(function(queue){

				// Start ajax now
				ajax.send();

				// Mark this queue as resolved
				setTimeout(queue.resolve, self.interval);
			});
	}

	return ajax;
}

self.queue    = $.Threads({threadLimit: 1});
self.interval = 1200;

// // Do not throttle ajax calls on Joomla 3.2 and above.
// var version = $.joomla.version.split("."),
//     majorVersion = version[0],
//     minorVersion = version[1];

// if (majorVersion >= 3 && minorVersion >= 2) {
	self.interval = 0;
// }

})();;/*!
 * jquery.transitionClass.
 * jQuery functions to invoke classnames that has CSS3 transitions.
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

(function(){

// addTransitoryClass
$.fn.addTransitoryClass = function(classname, duration) {
	var elem = this.addClass(classname);
	setTimeout(function(){elem.removeClass(classname)}, duration || 1);
	return this;
};

// addClassAfter
// removeClassAfter
var classAfter = function(operation, classname, timer) {
	var elem = this;
	setTimeout(function(){elem[operation+"Class"](classname)}, timer || 50);
	return this;
};

$.fn.addClassAfter = function(classname, timer) {
	return classAfter.call(this, "add", classname, timer);
};

$.fn.removeClassAfter = function(classname, timer) {
	return classAfter.call(this, "remove", classname, timer);
};

// addTransitionClass
// removeTransitionClass
var transitionClass = function(toggle, classname, duration, callback) {
	var suffix = toggle ? "-in" : "-out";
	this.addTransitoryClass(classname.replace(/ /g, suffix + " ") + suffix, duration || 1000)
		[(toggle ? "add" : "remove") + "ClassAfter"](classname);
	callback && setTimeout(callback, duration);
	return this;
};

$.fn.addTransitionClass = function(classname, duration, callback) {
	return transitionClass.call(this, true, classname, duration, callback);
};

$.fn.removeTransitionClass = function(classname, duration, callback) {
	return transitionClass.call(this, false, classname, duration, callback);
};

})();;/**
 * jquery.trimSeparators
 * Trims whitespace and separators.
 *
 * Turns this: ",df        ,,,  ,,,abc, sdasd sdfsdf    ,   asdsad, ,, , "
 * into this : "df,abc,sdasd sdfsdf,asdsad"
 *
 * Requires jquery.distinct
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.trimSeparators = function(keyword, separator, removeDuplicates) {

	var s = separator;

	keyword = keyword
		.replace(new RegExp('^['+s+'\\s]+|['+s+',\\s]+$','g'), '') // /^[,\s]+|[,\s]+$/g
		.replace(new RegExp(s+'['+s+'\\s]*'+s,'g'), s)             // /,[,\s]*,/g
		.replace(new RegExp('[\\s]+'+s,'g'), s)                    // /[\s]+,/g
		.replace(new RegExp(s+'[\\s]+','g'), s);                   // /,[\s]+/g

	if (removeDuplicates) {
		keyword = $.distinct(keyword.split(s)).join(s);
	}

	return keyword;
};
;/**
 * jquery.uid
 * Generates a unique id with optional prefix/suffix.
 *
 * Part of the jQuery Utils family:
 * https://github.com/jstonne/jquery.utils
 *
 * Copyright (c) 2014 Jensen Tonne
 * http://jstonne.me
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.uid = function(p,s) {
	return ((p) ? p : '') + Math.random().toString().replace('.','') + ((s) ? s : '');
};

});KTVendors.plugin("uri", function($) {

$.isUrl = function(s)
{
	var regexp = /^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	return regexp.test(s);
};


var Query = function (queryString) {

	// query string parsing, parameter manipulation and stringification

	'use strict';

	var // parseQuery(q) parses the uri query string and returns a multi-dimensional array of the components
		parseQuery = function (q) {
			var arr = [], i, ps, p, kvp, k, v;

			if (typeof (q) === 'undefined' || q === null || q === '') {
				return arr;
			}

			if (q.indexOf('?') === 0) {
				q = q.substring(1);
			}

			ps = q.toString().split(/[&;]/);

			for (i = 0; i < ps.length; i++) {
				p = ps[i];
				kvp = p.split('=');
				k = kvp[0];
				v = p.indexOf('=') === -1 ? null : (kvp[1] === null ? '' : kvp[1]);
				arr.push([k, v]);
			}

			return arr;
		},

		params = parseQuery(queryString),

		// toString() returns a string representation of the internal state of the object
		toString = function () {
			var s = '', i, param;
			for (i = 0; i < params.length; i++) {
				param = params[i];
				if (s.length > 0) {
					s += '&';
				}
				if (param[1] === null) {
				  s += param[0];
				}
				else {
				  s += param.join('=');
				}
			}
			return s.length > 0 ? '?' + s : s;
		},

		decode = function (s) {
			s = decodeURIComponent(s);
			s = s.replace('+', ' ');
			return s;
		},

		// getParamValues(key) returns the first query param value found for the key 'key'
		getParamValue = function (key) {
			var param, i;
			for (i = 0; i < params.length; i++) {
				param = params[i];
				if (decode(key) === decode(param[0])) {
					return param[1];
				}
			}
		},

		// getParamValues(key) returns an array of query param values for the key 'key'
		getParamValues = function (key) {
			var arr = [], i, param;
			for (i = 0; i < params.length; i++) {
				param = params[i];
				if (decode(key) === decode(param[0])) {
					arr.push(param[1]);
				}
			}
			return arr;
		},

		// deleteParam(key) removes all instances of parameters named (key)
		// deleteParam(key, val) removes all instances where the value matches (val)
		deleteParam = function (key, val) {

			var arr = [], i, param, keyMatchesFilter, valMatchesFilter;

			for (i = 0; i < params.length; i++) {

				param = params[i];
				keyMatchesFilter = decode(param[0]) === decode(key);
				valMatchesFilter = decode(param[1]) === decode(val);

				if ((arguments.length === 1 && !keyMatchesFilter) || (arguments.length === 2 && !keyMatchesFilter && !valMatchesFilter)) {
					arr.push(param);
				}
			}

			params = arr;

			return this;
		},

		// addParam(key, val) Adds an element to the end of the list of query parameters
		// addParam(key, val, index) adds the param at the specified position (index)
		addParam = function (key, val, index) {

			if (arguments.length === 3 && index !== -1) {
				index = Math.min(index, params.length);
				params.splice(index, 0, [key, val]);
			} else if (arguments.length > 0) {
				params.push([key, val]);
			}
			return this;
		},

		// replaceParam(key, newVal) deletes all instances of params named (key) and replaces them with the new single value
		// replaceParam(key, newVal, oldVal) deletes only instances of params named (key) with the value (val) and replaces them with the new single value
		// this function attempts to preserve query param ordering
		replaceParam = function (key, newVal, oldVal) {

			var index = -1, i, param;

			if (arguments.length === 3) {
				for (i = 0; i < params.length; i++) {
					param = params[i];
					if (decode(param[0]) === decode(key) && decodeURIComponent(param[1]) === decode(oldVal)) {
						index = i;
						break;
					}
				}
				deleteParam(key, oldVal).addParam(key, newVal, index);
			} else {
				for (i = 0; i < params.length; i++) {
					param = params[i];
					if (decode(param[0]) === decode(key)) {
						index = i;
						break;
					}
				}
				deleteParam(key);
				addParam(key, newVal, index);
			}
			return this;
		};

	// public api
	return {
		getParamValue: getParamValue,
		getParamValues: getParamValues,
		deleteParam: deleteParam,
		addParam: addParam,
		replaceParam: replaceParam,

		toString: toString
	};
};
var Uri = function (uriString) {

	// uri string parsing, attribute manipulation and stringification

	'use strict';

	/*global Query: true */
	/*jslint regexp: false, plusplus: false */

	var strictMode = false,

		urlExtractor = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)?/gi,

		// parseUri(str) parses the supplied uri and returns an object containing its components
		parseUri = function (str) {

			/*jslint unparam: true */
			var parsers = {
					strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
					loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
				},
				keys = ["source", "protocol", "authority", "userInfo", "user", "password", "host", "port", "relative", "path", "directory", "file", "query", "anchor"],
				q = {
					name: "queryKey",
					parser: /(?:^|&)([^&=]*)=?([^&]*)/g
				},
				m = parsers[strictMode ? "strict" : "loose"].exec(str),
				uri = {},
				i = 14;

			while (i--) {
				uri[keys[i]] = m[i] || "";
			}

			uri[q.name] = {};
			uri[keys[12]].replace(q.parser, function ($0, $1, $2) {
				if ($1) {
					uri[q.name][$1] = $2;
				}
			});

			return uri;
		},

		uriParts = parseUri(uriString || ''),

		queryObj = new Query(uriParts.query),


		/*
			Basic get/set functions for all properties
		*/

		protocol = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.protocol = val;
			}
			return uriParts.protocol;
		},

		hasAuthorityPrefixUserPref = null,

		// hasAuthorityPrefix: if there is no protocol, the leading // can be enabled or disabled
		hasAuthorityPrefix = function (val) {

			if (typeof val !== 'undefined') {
				hasAuthorityPrefixUserPref = val;
			}

			if (hasAuthorityPrefixUserPref === null) {
				return (uriParts.source.indexOf('//') !== -1);
			} else {
				return hasAuthorityPrefixUserPref;
			}
		},

		userInfo = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.userInfo = val;
			}
			return uriParts.userInfo;
		},

		host = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.host = val;
			}
			return uriParts.host;
		},

		port = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.port = val;
			}
			return uriParts.port;
		},

		path = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.path = val;
			}
			return uriParts.path;
		},

		query = function (val) {
			if (typeof val !== 'undefined') {
				queryObj = new Query(val);
			}
			return queryObj;
		},

		anchor = function (val) {
			if (typeof val !== 'undefined') {
				uriParts.anchor = val;
			}
			return uriParts.anchor;
		},


		/*
			Fluent setters for Uri uri properties
		*/

		setProtocol = function (val) {
			protocol(val);
			return this;
		},

		setHasAuthorityPrefix = function (val) {
			hasAuthorityPrefix(val);
			return this;
		},

		setUserInfo = function (val) {
			userInfo(val);
			return this;
		},

		setHost = function (val) {
			host(val);
			return this;
		},

		setPort = function (val) {
			port(val);
			return this;
		},

		setPath = function (val) {
			path(val);
			return this;
		},

		setQuery = function (val) {
			query(val);
			return this;
		},

		setAnchor = function (val) {
			anchor(val);
			return this;
		},

		/*
			Query method wrappers
		*/
		getQueryParamValue = function (key) {
			return query().getParamValue(key);
		},

		getQueryParamValues = function (key) {
			return query().getParamValues(key);
		},

		deleteQueryParam = function (key, val) {
			if (arguments.length === 2) {
				query().deleteParam(key, val);
			} else {
				query().deleteParam(key);
			}

			return this;
		},

		addQueryParam = function (key, val, index) {
			if (arguments.length === 3) {
				query().addParam(key, val, index);
			} else {
				query().addParam(key, val);
			}
			return this;
		},

		replaceQueryParam = function (key, newVal, oldVal) {
			if (arguments.length === 3) {
				query().replaceParam(key, newVal, oldVal);
			} else {
				query().replaceParam(key, newVal);
			}

			return this;
		},

		/*
			Converters
		*/

		// toPath() converts a relative path into its absolute path, e.g.
		//
		// Current path:  /foo/bar/today
		// Relative path: ../tomorrow
		// Result:        /foo/bar/tomorrow

		toPath = function (val) {
			if (val===undefined) {
				return uriParts.path;
			}

			// If relative path starts with '/'
			if (val.substring(0,1)=='/') {
				return uriParts.path = val;
			}

			var base_path = uriParts.path.split('/'),
				rel_path = val.split('/');

			if (base_path.slice(-1)[0]==='') {
				base_path.pop();
			}

			var part;
			while (part = rel_path.shift()) {
				switch (part) {
					case '..':
						if (base_path.length > 1) {
							base_path.pop();
						}
						break;

					case '.':
						// skip
						break;

					default:
						base_path.push(part);
				}
			}

			uriParts.path = base_path.join('/');

			return this;
		},

		/*
			Serialization
		*/

		// toString() stringifies the current state of the uri
		toString = function () {

			var s = '',
				is = function (s) {
					return (s !== null && s !== '');
				};

			if (is(protocol())) {
				s += protocol();
				if (protocol().indexOf(':') !== protocol().length - 1) {
					s += ':';
				}
				s += '//';
			} else {
				if (hasAuthorityPrefix() && is(host())) {
					s += '//';
				}
			}

			if (is(userInfo()) && is(host())) {
				s += userInfo();
				if (userInfo().indexOf('@') !== userInfo().length - 1) {
					s += '@';
				}
			}

			if (is(host())) {
				s += host();
				if (is(port())) {
					s += ':' + port();
				}
			}

			if (is(path())) {
				s += path();
			} else {
				if (is(host()) && (is(query().toString()) || is(anchor()))) {
					s += '/';
				}
			}
			if (is(query().toString())) {
				if (query().toString().indexOf('?') !== 0) {
					s += '?';
				}
				s += query().toString();
			}

			if (is(anchor())) {
				if (anchor().indexOf('#') !== 0) {
					s += '#';
				}
				s += anchor();
			}

			return s;
		},

		extract = function(i) {
			var urls = uriString.match(urlExtractor) || [];
			return (i===undefined) ? urls : (urls[i] || "");
		},

		/*
			Cloning
		*/

		// clone() returns a new, identical Uri instance
		clone = function () {
			return new Uri(toString());
		};

	// public api
	return {

		protocol: protocol,
		hasAuthorityPrefix: hasAuthorityPrefix,
		userInfo: userInfo,
		host: host,
		port: port,
		path: path,
		query: query,
		anchor: anchor,

		setProtocol: setProtocol,
		setHasAuthorityPrefix: setHasAuthorityPrefix,
		setUserInfo: setUserInfo,
		setHost: setHost,
		setPort: setPort,
		setPath: setPath,
		setQuery: setQuery,
		setAnchor: setAnchor,

		getQueryParamValue: getQueryParamValue,
		getQueryParamValues: getQueryParamValues,
		deleteQueryParam: deleteQueryParam,
		addQueryParam: addQueryParam,
		replaceQueryParam: replaceQueryParam,
		extract: extract,

		toPath: toPath,

		toString: toString,
		clone: clone
	};
};
$.uri = function (s) {
	return new Uri(s);
}

});KTVendors.plugin("mvc", function($) {

(function(){
	// Several of the methods in this plugin use code adapated from Prototype
	//  Prototype JavaScript framework, version 1.6.0.1
	//  (c) 2005-2007 Sam Stephenson
	var regs = {
		undHash: /_|-/,
		colons: /::/,
		words: /([A-Z]+)([A-Z][a-z])/g,
		lowUp: /([a-z\d])([A-Z])/g,
		dash: /([a-z\d])([A-Z])/g,
		replacer: /\{([^\}]+)\}/g,
		dot: /\./
	},
		// gets the nextPart property from current
		// add - if true and nextPart doesnt exist, create it as an empty object
		getNext = function(current, nextPart, add){
			return current[nextPart] !== undefined ? current[nextPart] : ( add && (current[nextPart] = {}) );
		},
		// returns true if the object can have properties (no nulls)
		isContainer = function(current){
			var type = typeof current;
			return current && ( type == 'function' || type == 'object' );
		},
		// a reference
		getObject,
		/**
		 * @class jQuery.String
		 * @parent jquerymx.lang
		 *
		 * A collection of useful string helpers. Available helpers are:
		 * <ul>
		 *   <li>[jQuery.String.capitalize|capitalize]: Capitalizes a string (some_string &raquo; Some_string)</li>
		 *   <li>[jQuery.String.camelize|camelize]: Capitalizes a string from something undercored
		 *       (some_string &raquo; someString, some-string &raquo; someString)</li>
		 *   <li>[jQuery.String.classize|classize]: Like [jQuery.String.camelize|camelize],
		 *       but the first part is also capitalized (some_string &raquo; SomeString)</li>
		 *   <li>[jQuery.String.niceName|niceName]: Like [jQuery.String.classize|classize], but a space separates each 'word' (some_string &raquo; Some String)</li>
		 *   <li>[jQuery.String.underscore|underscore]: Underscores a string (SomeString &raquo; some_string)</li>
		 *   <li>[jQuery.String.sub|sub]: Returns a string with {param} replaced values from data.
		 *       <code><pre>
		 *       $.String.sub("foo {bar}",{bar: "far"})
		 *       //-> "foo far"</pre></code>
		 *   </li>
		 * </ul>
		 *
		 */

		str = $.String = $.extend($.String || {} , {


			/**
			 * @function getObject
			 * Gets an object from a string.  It can also modify objects on the
			 * 'object path' by removing or adding properties.
			 *
			 *     Foo = {Bar: {Zar: {"Ted"}}}
			 *     $.String.getObject("Foo.Bar.Zar") //-> "Ted"
			 *
			 * @param {String} name the name of the object to look for
			 * @param {Array} [roots] an array of root objects to look for the
			 *   name.  If roots is not provided, the window is used.
			 * @param {Boolean} [add] true to add missing objects to
			 *  the path. false to remove found properties. undefined to
			 *  not modify the root object
			 * @return {Object} The object.
			 */
			getObject : getObject = function( name, roots, add ) {

				// the parts of the name we are looking up
				// ['App','Models','Recipe']
				var parts = name ? name.split(regs.dot) : [],
					length =  parts.length,
					current,
					ret,
					i,
					r = 0,
					type;

				// make sure roots is an array
				roots = $.isArray(roots) ? roots : [roots || window];

				if(length == 0){
					return roots[0];
				}
				// for each root, mark it as current
				while( current = roots[r++] ) {
					// walk current to the 2nd to last object
					// or until there is not a container
					for (i =0; i < length - 1 && isContainer(current); i++ ) {
						current = getNext(current, parts[i], add);
					}
					// if we can get a property from the 2nd to last object
					if( isContainer(current) ) {

						// get (and possibly set) the property
						ret = getNext(current, parts[i], add);

						// if there is a value, we exit
						if( ret !== undefined ) {
							// if add is false, delete the property
							if ( add === false ) {
								delete current[parts[i]];
							}
							return ret;

						}
					}
				}
			},
			/**
			 * Capitalizes a string
			 * @param {String} s the string.
			 * @return {String} a string with the first character capitalized.
			 */
			capitalize: function( s, cache ) {
				return s.charAt(0).toUpperCase() + s.substr(1);
			},
			/**
			 * Capitalizes a string from something undercored. Examples:
			 * @codestart
			 * jQuery.String.camelize("one_two") //-> "oneTwo"
			 * "three-four".camelize() //-> threeFour
			 * @codeend
			 * @param {String} s
			 * @return {String} a the camelized string
			 */
			camelize: function( s ) {
				s = str.classize(s);
				return s.charAt(0).toLowerCase() + s.substr(1);
			},
			/**
			 * Like [jQuery.String.camelize|camelize], but the first part is also capitalized
			 * @param {String} s
			 * @return {String} the classized string
			 */
			classize: function( s , join) {
				var parts = s.split(regs.undHash),
					i = 0;
				for (; i < parts.length; i++ ) {
					parts[i] = str.capitalize(parts[i]);
				}

				return parts.join(join || '');
			},
			/**
			 * Like [jQuery.String.classize|classize], but a space separates each 'word'
			 * @codestart
			 * jQuery.String.niceName("one_two") //-> "One Two"
			 * @codeend
			 * @param {String} s
			 * @return {String} the niceName
			 */
			niceName: function( s ) {
				return str.classize(s,' ');
			},

			/**
			 * Underscores a string.
			 * @codestart
			 * jQuery.String.underscore("OneTwo") //-> "one_two"
			 * @codeend
			 * @param {String} s
			 * @return {String} the underscored string
			 */
			underscore: function( s ) {
				return s.replace(regs.colons, '/').replace(regs.words, '$1_$2').replace(regs.lowUp, '$1_$2').replace(regs.dash, '_').toLowerCase();
			},
			/**
			 * Returns a string with {param} replaced values from data.
			 *
			 *     $.String.sub("foo {bar}",{bar: "far"})
			 *     //-> "foo far"
			 *
			 * @param {String} s The string to replace
			 * @param {Object} data The data to be used to look for properties.  If it's an array, multiple
			 * objects can be used.
			 * @param {Boolean} [remove] if a match is found, remove the property from the object
			 */
			sub: function( s, data, remove ) {
				var obs = [];
				obs.push(s.replace(regs.replacer, function( whole, inside ) {

					// !-- FOUNDRY HACK --! //
					// Prefer {foobar} over foobar

					//convert inside to type
					var ob = getObject(whole, data, typeof remove == 'boolean' ? !remove : remove) ||
							 getObject(inside, data, typeof remove == 'boolean' ? !remove : remove),
						type = typeof ob;

					if ((type === 'object' || type === 'function') && type !== null) {
						obs.push(ob);
						return "";
					} else {
						return ""+ob;
					}
				}));
				return obs.length <= 1 ? obs[0] : obs;
			},
			_regs : regs
		});

	// !-- FOUNDRY HACK --! //
	// Expose string methods to $.
	$.extend($, str);
})();(function(){
	/**
	 * @add jQuery.String
	 */
	$.String.
	/**
	 * Splits a string with a regex correctly cross browser
	 * 
	 *     $.String.rsplit("a.b.c.d", /\./) //-> ['a','b','c','d']
	 * 
	 * @param {String} string The string to split
	 * @param {RegExp} regex A regular expression
	 * @return {Array} An array of strings
	 */
	rsplit = function( string, regex ) {
		var result = regex.exec(string),
			retArr = [],
			first_idx, last_idx;
		while ( result !== null ) {
			first_idx = result.index;
			last_idx = regex.lastIndex;
			if ( first_idx !== 0 ) {
				retArr.push(string.substring(0, first_idx));
				string = string.slice(first_idx);
			}
			retArr.push(result[0]);
			string = string.slice(result[0].length);
			result = regex.exec(string);
		}
		if ( string !== '' ) {
			retArr.push(string);
		}
		return retArr;
	};
})();(function(){
	
	var digitTest = /^\d+$/,
		keyBreaker = /([^\[\]]+)|(\[\])/g,
		plus = /\+/g,
		paramTest = /([^?#]*)(#.*)?$/;
	
	/**
	 * @add jQuery.String
	 */
	$.String = $.extend($.String || {}, { 
		
		/**
		 * @function deparam
		 * 
		 * Takes a string of name value pairs and returns a Object literal that represents those params.
		 * 
		 * @param {String} params a string like <code>"foo=bar&person[age]=3"</code>
		 * @return {Object} A JavaScript Object that represents the params:
		 * 
		 *     {
		 *       foo: "bar",
		 *       person: {
		 *         age: "3"
		 *       }
		 *     }
		 */
		deparam: function(params){
		
			if(! params || ! paramTest.test(params) ) {
				return {};
			} 
		   
		
			var data = {},
				pairs = params.split('&'),
				current;
				
			for(var i=0; i < pairs.length; i++){
				current = data;
				var pair = pairs[i].split('=');
				
				// if we find foo=1+1=2
				if(pair.length != 2) { 
					pair = [pair[0], pair.slice(1).join("=")]
				}
				  
		var key = decodeURIComponent(pair[0].replace(plus, " ")), 
		  value = decodeURIComponent(pair[1].replace(plus, " ")),
					parts = key.match(keyBreaker);
		
				for ( var j = 0; j < parts.length - 1; j++ ) {
					var part = parts[j];
					if (!current[part] ) {
						// if what we are pointing to looks like an array
						current[part] = digitTest.test(parts[j+1]) || parts[j+1] == "[]" ? [] : {}
					}
					current = current[part];
				}
				lastPart = parts[parts.length - 1];
				if(lastPart == "[]"){
					current.push(value)
				}else{
					current[lastPart] = value;
				}
			}
			return data;
		}
	});
	
})();(function(){
	/**
	 * @attribute destroyed
	 * @parent specialevents
	 * @download  http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/dom/destroyed/destroyed.js
	 * @test jquery/event/destroyed/qunit.html
	 * Provides a destroyed event on an element.
	 * <p>
	 * The destroyed event is called when the element
	 * is removed as a result of jQuery DOM manipulators like remove, html,
	 * replaceWith, etc. Destroyed events do not bubble, so make sure you don't use live or delegate with destroyed
	 * events.
	 * </p>
	 * <h2>Quick Example</h2>
	 * @codestart
	 * $(".foo").bind("destroyed", function(){
	 *    //clean up code
	 * })
	 * @codeend
	 * <h2>Quick Demo</h2>
	 * @demo jquery/event/destroyed/destroyed.html
	 * <h2>More Involved Demo</h2>
	 * @demo jquery/event/destroyed/destroyed_menu.html
	 */

	var oldClean = $.cleanData;

	$.cleanData = function( elems ) {
		for ( var i = 0, elem;
		(elem = elems[i]) !== undefined; i++ ) {
			$(elem).triggerHandler("destroyed");
			//$.event.remove( elem, 'destroyed' );
		}
		oldClean(elems);
	};

})();(function(){
	/**
	 * @function closest
	 * @parent dom
	 * @plugin jquery/dom/closest
	 * Overwrites closest to allow open > selectors.  This allows controller
	 * actions such as:
	 *
	 *     ">li click" : function( el, ev ) { ... }
	 */
	var oldClosest = $.fn._closest = $.fn.closest;
	$.fn.closest = function(selectors, context){

		// FOUNDRY_HACK
		// If a jQuery or node element was passed in, use original closest method.
		if (selectors instanceof $ || $.isElement(selectors)) {
			return oldClosest.call(this, arguments);
		}

		var rooted = {}, res, result, thing, i, j, selector, rootedIsEmpty = true, selector, selectorsArr = selectors;
		if(typeof selectors == "string") selectorsArr = [selectors];

		$.each(selectorsArr, function(i, selector){
			if(selector.indexOf(">") == 0 ){
				if(selector.indexOf(" ") != -1){
					throw " closest does not work with > followed by spaces!"
				}
				rooted[( selectorsArr[i] = selector.substr(1)  )] = selector;
				if(typeof selectors == "string") selectors = selector.substr(1);
				rootedIsEmpty = false;
			}
		})

		res = oldClosest.call(this, selectors, context);

		if(rootedIsEmpty) return res;
		i =0;
		while(i < res.length){
			result = res[i], selector = result.selector;
			if (rooted[selector] !== undefined) {
				result.selector = rooted[selector];
				rooted[selector] = false;
				if(typeof result.selector !== "string"  || result.elem.parentNode !== context ){
					res.splice(i,1);
						continue;
				}
			}
			i++;
		}
		return res;
	}
})();(function(){
	// break
	/**
	 * @function jQuery.cookie
	 * @parent dom
	 * @plugin jquery/dom/cookie
	 * @author Klaus Hartl/klaus.hartl@stilbuero.de
	 *
	 *  JavaScriptMVC's packaged cookie plugin is written by
	 *  Klaus Hartl (stilbuero.de)<br />
	 *  Dual licensed under the MIT and GPL licenses:<br />
	 *  http://www.opensource.org/licenses/mit-license.php<br />
	 *  http://www.gnu.org/licenses/gpl.html
	 *  </p>
	 *  <p>
	 *  Create a cookie with the given name and value and other optional parameters.
	 *  / Get the value of a cookie with the given name.
	 *  </p>
	 *  <h3>Quick Examples</h3>
	 *
	 *  Set the value of a cookie.
	 *
	 *     $.cookie('the_cookie', 'the_value');
	 *
	 *  Create a cookie with all available options.
	 *  @codestart
	 *  $.cookie('the_cookie', 'the_value',
	 *  { expires: 7, path: '/', domain: 'jquery.com', secure: true });
	 *  @codeend
	 *
	 *  Create a session cookie.
	 *  @codestart
	 *  $.cookie('the_cookie', 'the_value');
	 *  @codeend
	 *
	 *  Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
	 *  used when the cookie was set.
	 *  @codestart
	 *  $.cookie('the_cookie', null);
	 *  @codeend
	 *
	 *  Get the value of a cookie.
	 *  @codestart
	 *  $.cookie('the_cookie');
	 *  @codeend
	 *
	 *
	 * @param {String} [name] The name of the cookie.
	 * @param {String} [value] The value of the cookie.
	 * @param {Object} [options] An object literal containing key/value pairs to provide optional cookie attributes.<br />
	 * @param {Number|Date} [expires] Either an integer specifying the expiration date from now on in days or a Date object.
	 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
	 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
	 *                             when the the browser exits.<br />
	 * @param {String} [path] The value of the path atribute of the cookie (default: path of page that created the cookie).<br />
	 * @param {String} [domain] The value of the domain attribute of the cookie (default: domain of page that created the cookie).<br />
	 * @param {Boolean} secure If true, the secure attribute of the cookie will be set and the cookie transmission will
	 *                        require a secure protocol (like HTTPS).<br />
	 * @return {String} the value of the cookie or {undefined} when setting the cookie.
	 */
	$.cookie = function(name, value, options) {
		if (typeof value != 'undefined') { // name and value given, set cookie
			options = options ||
			{};
			if (value === null) {
				value = '';
				options.expires = -1;
			}
			if (typeof value == 'object' && jQuery.toJSON) {
				value = jQuery.toJSON(value);
			}
			var expires = '';
			if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
				var date;
				if (typeof options.expires == 'number') {
					date = new Date();
					date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
				}
				else {
					date = options.expires;
				}
				expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
			}
			// CAUTION: Needed to parenthesize options.path and options.domain
			// in the following expressions, otherwise they evaluate to undefined
			// in the packed version for some reason...
			var path = options.path ? '; path=' + (options.path) : '';
			var domain = options.domain ? '; domain=' + (options.domain) : '';
			var secure = options.secure ? '; secure' : '';
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
		}
		else { // only name given, get cookie
			var cookieValue = null;
			if (document.cookie && document.cookie != '') {
				var cookies = document.cookie.split(';');
				for (var i = 0; i < cookies.length; i++) {
					var cookie = jQuery.trim(cookies[i]);
					// Does this cookie string begin with the name we want?
					if (cookie.substring(0, name.length + 1) == (name + '=')) {
						cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
						break;
					}
				}
			}
			if (jQuery.evalJSON && cookieValue && cookieValue.match(/^\s*\{/)) {
				try {
					cookieValue = jQuery.evalJSON(cookieValue);
				}
				catch (e) {
				}
			}
			return cookieValue;
		}
	};

})();(function(){

	// =============== HELPERS =================

		// if we are initializing a new class
	var initializing = false,
		makeArray = $.makeArray,
		isFunction = $.isFunction,
		isArray = $.isArray,
		extend = $.extend,
		getObject = $.String.getObject,
		concatArgs = function(arr, args){
			return arr.concat(makeArray(args));
		},

		// tests if we can get super in .toString()
		fnTest = /xyz/.test(function() {
			xyz;
		}) ? /\b_super\b/ : /.*/,

		// overwrites an object with methods, sets up _super
		//   newProps - new properties
		//   oldProps - where the old properties might be
		//   addTo - what we are adding to
		inheritProps = function( newProps, oldProps, addTo ) {
			addTo = addTo || newProps
			for ( var name in newProps ) {
				// Check if we're overwriting an existing function
				addTo[name] = isFunction(newProps[name]) &&
							  isFunction(oldProps[name]) &&
							  fnTest.test(newProps[name]) ? (function( name, fn ) {
					return function() {
						var tmp = this._super,
							ret;

						// Add a new ._super() method that is the same method
						// but on the super-class
						this._super = oldProps[name];

						// The method only need to be bound temporarily, so we
						// remove it when we're done executing
						ret = fn.apply(this, arguments);
						this._super = tmp;
						return ret;
					};
				})(name, newProps[name]) : newProps[name];
			}
		},
		STR_PROTOTYPE = 'prototype'

	/**
	 * @class jQuery.Class
	 * @plugin jquery/class
	 * @parent jquerymx
	 * @download dist/jquery/jquery.class.js
	 * @test jquery/class/qunit.html
	 * @description Easy inheritance in JavaScript.
	 *
	 * Class provides simulated inheritance in JavaScript. Use clss to bridge the gap between
	 * jQuery's functional programming style and Object Oriented Programming. It
	 * is based off John Resig's [http://ejohn.org/blog/simple-javascript-inheritance/|Simple Class]
	 * Inheritance library.  Besides prototypal inheritance, it includes a few important features:
	 *
	 *   - Static inheritance
	 *   - Introspection
	 *   - Namespaces
	 *   - Setup and initialization methods
	 *   - Easy callback function creation
	 *
	 *
	 * The [mvc.class Get Started with jQueryMX] has a good walkthrough of $.Class.
	 *
	 * ## Static v. Prototype
	 *
	 * Before learning about Class, it's important to
	 * understand the difference between
	 * a class's __static__ and __prototype__ properties.
	 *
	 *     //STATIC
	 *     MyClass.staticProperty  //shared property
	 *
	 *     //PROTOTYPE
	 *     myclass = new MyClass()
	 *     myclass.prototypeMethod() //instance method
	 *
	 * A static (or class) property is on the Class constructor
	 * function itself
	 * and can be thought of being shared by all instances of the
	 * Class. Prototype propertes are available only on instances of the Class.
	 *
	 * ## A Basic Class
	 *
	 * The following creates a Monster class with a
	 * name (for introspection), static, and prototype members.
	 * Every time a monster instance is created, the static
	 * count is incremented.
	 *
	 * @codestart
	 * $.Class('Monster',
	 * /* @static *|
	 * {
	 *   count: 0
	 * },
	 * /* @prototype *|
	 * {
	 *   init: function( name ) {
	 *
	 *     // saves name on the monster instance
	 *     this.name = name;
	 *
	 *     // sets the health
	 *     this.health = 10;
	 *
	 *     // increments count
	 *     this.constructor.count++;
	 *   },
	 *   eat: function( smallChildren ){
	 *     this.health += smallChildren;
	 *   },
	 *   fight: function() {
	 *     this.health -= 2;
	 *   }
	 * });
	 *
	 * hydra = new Monster('hydra');
	 *
	 * dragon = new Monster('dragon');
	 *
	 * hydra.name        // -> hydra
	 * Monster.count     // -> 2
	 * Monster.shortName // -> 'Monster'
	 *
	 * hydra.eat(2);     // health = 12
	 *
	 * dragon.fight();   // health = 8
	 *
	 * @codeend
	 *
	 *
	 * Notice that the prototype <b>init</b> function is called when a new instance of Monster is created.
	 *
	 *
	 * ## Inheritance
	 *
	 * When a class is extended, all static and prototype properties are available on the new class.
	 * If you overwrite a function, you can call the base class's function by calling
	 * <code>this._super</code>.  Lets create a SeaMonster class.  SeaMonsters are less
	 * efficient at eating small children, but more powerful fighters.
	 *
	 *
	 *     Monster("SeaMonster",{
	 *       eat: function( smallChildren ) {
	 *         this._super(smallChildren / 2);
	 *       },
	 *       fight: function() {
	 *         this.health -= 1;
	 *       }
	 *     });
	 *
	 *     lockNess = new SeaMonster('Lock Ness');
	 *     lockNess.eat(4);   //health = 12
	 *     lockNess.fight();  //health = 11
	 *
	 * ### Static property inheritance
	 *
	 * You can also inherit static properties in the same way:
	 *
	 *     $.Class("First",
	 *     {
	 *         staticMethod: function() { return 1;}
	 *     },{})
	 *
	 *     First("Second",{
	 *         staticMethod: function() { return this._super()+1;}
	 *     },{})
	 *
	 *     Second.staticMethod() // -> 2
	 *
	 * ## Namespaces
	 *
	 * Namespaces are a good idea! We encourage you to namespace all of your code.
	 * It makes it possible to drop your code into another app without problems.
	 * Making a namespaced class is easy:
	 *
	 *
	 *     $.Class("MyNamespace.MyClass",{},{});
	 *
	 *     new MyNamespace.MyClass()
	 *
	 *
	 * <h2 id='introspection'>Introspection</h2>
	 *
	 * Often, it's nice to create classes whose name helps determine functionality.  Ruby on
	 * Rails's [http://api.rubyonrails.org/classes/ActiveRecord/Base.html|ActiveRecord] ORM class
	 * is a great example of this.  Unfortunately, JavaScript doesn't have a way of determining
	 * an object's name, so the developer must provide a name.  Class fixes this by taking a String name for the class.
	 *
	 *     $.Class("MyOrg.MyClass",{},{})
	 *     MyOrg.MyClass.shortName //-> 'MyClass'
	 *     MyOrg.MyClass.fullName //->  'MyOrg.MyClass'
	 *
	 * The fullName (with namespaces) and the shortName (without namespaces) are added to the Class's
	 * static properties.
	 *
	 *
	 * ## Setup and initialization methods
	 *
	 * <p>
	 * Class provides static and prototype initialization functions.
	 * These come in two flavors - setup and init.
	 * Setup is called before init and
	 * can be used to 'normalize' init's arguments.
	 * </p>
	 * <div class='whisper'>PRO TIP: Typically, you don't need setup methods in your classes. Use Init instead.
	 * Reserve setup methods for when you need to do complex pre-processing of your class before init is called.
	 *
	 * </div>
	 * @codestart
	 * $.Class("MyClass",
	 * {
	 *   setup: function() {} //static setup
	 *   init: function() {} //static constructor
	 * },
	 * {
	 *   setup: function() {} //prototype setup
	 *   init: function() {} //prototype constructor
	 * })
	 * @codeend
	 *
	 * ### Setup
	 *
	 * Setup functions are called before init functions.  Static setup functions are passed
	 * the base class followed by arguments passed to the extend function.
	 * Prototype static functions are passed the Class constructor
	 * function arguments.
	 *
	 * If a setup function returns an array, that array will be used as the arguments
	 * for the following init method.  This provides setup functions the ability to normalize
	 * arguments passed to the init constructors.  They are also excellent places
	 * to put setup code you want to almost always run.
	 *
	 *
	 * The following is similar to how [jQuery.Controller.prototype.setup]
	 * makes sure init is always called with a jQuery element and merged options
	 * even if it is passed a raw
	 * HTMLElement and no second parameter.
	 *
	 *     $.Class("jQuery.Controller",{
	 *       ...
	 *     },{
	 *       setup: function( el, options ) {
	 *         ...
	 *         return [$(el),
	 *                 $.extend(true,
	 *                    this.Class.defaults,
	 *                    options || {} ) ]
	 *       }
	 *     })
	 *
	 * Typically, you won't need to make or overwrite setup functions.
	 *
	 * ### Init
	 *
	 * Init functions are called after setup functions.
	 * Typically, they receive the same arguments
	 * as their preceding setup function.  The Foo class's <code>init</code> method
	 * gets called in the following example:
	 *
	 *     $.Class("Foo", {
	 *       init: function( arg1, arg2, arg3 ) {
	 *         this.sum = arg1+arg2+arg3;
	 *       }
	 *     })
	 *     var foo = new Foo(1,2,3);
	 *     foo.sum //-> 6
	 *
	 * ## Proxies
	 *
	 * Similar to jQuery's proxy method, Class provides a
	 * [jQuery.Class.static.proxy proxy]
	 * function that returns a callback to a method that will always
	 * have
	 * <code>this</code> set to the class or instance of the class.
	 *
	 *
	 * The following example uses this.proxy to make sure
	 * <code>this.name</code> is available in <code>show</code>.
	 *
	 *     $.Class("Todo",{
	 *       init: function( name ) {
	 *          this.name = name
	 *       },
	 *       get: function() {
	 *         $.get("/stuff",this.proxy('show'))
	 *       },
	 *       show: function( txt ) {
	 *         alert(this.name+txt)
	 *       }
	 *     })
	 *     new Todo("Trash").get()
	 *
	 * Callback is available as a static and prototype method.
	 *
	 * ##  Demo
	 *
	 * @demo jquery/class/class.html
	 *
	 *
	 * @constructor
	 *
	 * To create a Class call:
	 *
	 *     $.Class( [NAME , STATIC,] PROTOTYPE ) -> Class
	 *
	 * <div class='params'>
	 *   <div class='param'><label>NAME</label><code>{optional:String}</code>
	 *   <p>If provided, this sets the shortName and fullName of the
	 *      class and adds it and any necessary namespaces to the
	 *      window object.</p>
	 *   </div>
	 *   <div class='param'><label>STATIC</label><code>{optional:Object}</code>
	 *   <p>If provided, this creates static properties and methods
	 *   on the class.</p>
	 *   </div>
	 *   <div class='param'><label>PROTOTYPE</label><code>{Object}</code>
	 *   <p>Creates prototype methods on the class.</p>
	 *   </div>
	 * </div>
	 *
	 * When a Class is created, the static [jQuery.Class.static.setup setup]
	 * and [jQuery.Class.static.init init]  methods are called.
	 *
	 * To create an instance of a Class, call:
	 *
	 *     new Class([args ... ]) -> instance
	 *
	 * The created instance will have all the
	 * prototype properties and methods defined by the PROTOTYPE object.
	 *
	 * When an instance is created, the prototype [jQuery.Class.prototype.setup setup]
	 * and [jQuery.Class.prototype.init init]  methods
	 * are called.
	 */

	clss = $.Class = function() {
		if (arguments.length) {
			clss.extend.apply(clss, arguments);
		}
	};

	/* @Static*/
	extend(clss, {
		/**
		 * @function proxy
		 * Returns a callback function for a function on this Class.
		 * Proxy ensures that 'this' is set appropriately.
		 * @codestart
		 * $.Class("MyClass",{
		 *     getData: function() {
		 *         this.showing = null;
		 *         $.get("data.json",this.proxy('gotData'),'json')
		 *     },
		 *     gotData: function( data ) {
		 *         this.showing = data;
		 *     }
		 * },{});
		 * MyClass.showData();
		 * @codeend
		 * <h2>Currying Arguments</h2>
		 * Additional arguments to proxy will fill in arguments on the returning function.
		 * @codestart
		 * $.Class("MyClass",{
		 *    getData: function( <b>callback</b> ) {
		 *      $.get("data.json",this.proxy('process',<b>callback</b>),'json');
		 *    },
		 *    process: function( <b>callback</b>, jsonData ) { //callback is added as first argument
		 *        jsonData.processed = true;
		 *        callback(jsonData);
		 *    }
		 * },{});
		 * MyClass.getData(showDataFunc)
		 * @codeend
		 * <h2>Nesting Functions</h2>
		 * Proxy can take an array of functions to call as
		 * the first argument.  When the returned callback function
		 * is called each function in the array is passed the return value of the prior function.  This is often used
		 * to eliminate currying initial arguments.
		 * @codestart
		 * $.Class("MyClass",{
		 *    getData: function( callback ) {
		 *      //calls process, then callback with value from process
		 *      $.get("data.json",this.proxy(['process2',callback]),'json')
		 *    },
		 *    process2: function( type,jsonData ) {
		 *        jsonData.processed = true;
		 *        return [jsonData];
		 *    }
		 * },{});
		 * MyClass.getData(showDataFunc);
		 * @codeend
		 * @param {String|Array} fname If a string, it represents the function to be called.
		 * If it is an array, it will call each function in order and pass the return value of the prior function to the
		 * next function.
		 * @return {Function} the callback function.
		 */
		proxy: function( funcs ) {

			//args that should be curried
			var args = makeArray(arguments),
				self;

			// get the functions to callback
			funcs = args.shift();

			// if there is only one function, make funcs into an array
			if (!isArray(funcs) ) {
				funcs = [funcs];
			}

			// keep a reference to us in self
			self = this;

			
			return function class_cb() {
				// add the arguments after the curried args
				var cur = concatArgs(args, arguments),
					isString,
					length = funcs.length,
					f = 0,
					func;

				// go through each function to call back
				for (; f < length; f++ ) {
					func = funcs[f];
					if (!func ) {
						continue;
					}

					// set called with the name of the function on self (this is how this.view works)
					isString = typeof func == "string";
					if ( isString && self._set_called ) {
						self.called = func;
					}

					// call the function
					cur = (isString ? self[func] : func).apply(self, cur || []);

					// pass the result to the next function (if there is a next function)
					if ( f < length - 1 ) {
						cur = !isArray(cur) || cur._use_call ? [cur] : cur
					}
				}
				return cur;
			}
		},
		/**
		 * @function newInstance
		 * Creates a new instance of the class.  This method is useful for creating new instances
		 * with arbitrary parameters.
		 * <h3>Example</h3>
		 * @codestart
		 * $.Class("MyClass",{},{})
		 * var mc = MyClass.newInstance.apply(null, new Array(parseInt(Math.random()*10,10))
		 * @codeend
		 * @return {class} instance of the class
		 */
		newInstance: function() {
			// get a raw instance objet (init is not called)
			var inst = this.rawInstance(),
				args;

			// call setup if there is a setup
			if ( inst.setup ) {
				args = inst.setup.apply(inst, arguments);
			}
			// call init if there is an init, if setup returned args, use those as the arguments
			if ( inst.init ) {
				inst.init.apply(inst, isArray(args) ? args : arguments);
			}
			return inst;
		},
		/**
		 * Setup gets called on the inherting class with the base class followed by the
		 * inheriting class's raw properties.
		 *
		 * Setup will deeply extend a static defaults property on the base class with
		 * properties on the base class.  For example:
		 *
		 *     $.Class("MyBase",{
		 *       defaults : {
		 *         foo: 'bar'
		 *       }
		 *     },{})
		 *
		 *     MyBase("Inheriting",{
		 *       defaults : {
		 *         newProp : 'newVal'
		 *       }
		 *     },{}
		 *
		 *     Inheriting.defaults -> {foo: 'bar', 'newProp': 'newVal'}
		 *
		 * @param {Object} baseClass the base class that is being inherited from
		 * @param {String} fullName the name of the new class
		 * @param {Object} staticProps the static properties of the new class
		 * @param {Object} protoProps the prototype properties of the new class
		 */
		setup: function( baseClass, fullName ) {
			// set defaults as the merger of the parent defaults and this object's defaults
			this.defaults = extend(true, {}, baseClass.defaults, this.defaults);
			return arguments;
		},
		rawInstance: function() {
			// prevent running init
			initializing = true;
			var inst = new this();
			initializing = false;
			// allow running init
			return inst;
		},
		/**
		 * Extends a class with new static and prototype functions.  There are a variety of ways
		 * to use extend:
		 *
		 *     // with className, static and prototype functions
		 *     $.Class('Task',{ STATIC },{ PROTOTYPE })
		 *     // with just classname and prototype functions
		 *     $.Class('Task',{ PROTOTYPE })
		 *     // with just a className
		 *     $.Class('Task')
		 *
		 * You no longer have to use <code>.extend</code>.  Instead, you can pass those options directly to
		 * $.Class (and any inheriting classes):
		 *
		 *     // with className, static and prototype functions
		 *     $.Class('Task',{ STATIC },{ PROTOTYPE })
		 *     // with just classname and prototype functions
		 *     $.Class('Task',{ PROTOTYPE })
		 *     // with just a className
		 *     $.Class('Task')
		 *
		 * @param {String} [fullName]  the classes name (used for classes w/ introspection)
		 * @param {Object} [klass]  the new classes static/class functions
		 * @param {Object} [proto]  the new classes prototype functions
		 *
		 * @return {jQuery.Class} returns the new class
		 */
		extend: function( fullName, klass, proto ) {
			// figure out what was passed and normalize it
			if ( typeof fullName != 'string' ) {
				proto = klass;
				klass = fullName;
				fullName = null;
			}
			if (!proto ) {
				proto = klass;
				klass = null;
			}

			proto = proto || {};
			var _super_class = this,
				_super = this[STR_PROTOTYPE],
				name, shortName, namespace, prototype;

			// Instantiate a base class (but only create the instance,
			// don't run the init constructor)
			initializing = true;
			prototype = new this();
			initializing = false;

			// Copy the properties over onto the new prototype
			inheritProps(proto, _super, prototype);

			// The dummy class constructor
			function Class() {
				// All construction is actually done in the init method
				if ( initializing ) return;

				// we are being called w/o new, we are extending
				if ( this.constructor !== Class && arguments.length ) {
					return arguments.callee.extend.apply(arguments.callee, arguments)
				} else { //we are being called w/ new
					return this.Class.newInstance.apply(this.Class, arguments)
				}
			}
			// Copy old stuff onto class
			for ( name in this ) {
				if ( this.hasOwnProperty(name) ) {
					Class[name] = this[name];
				}
			}

			// copy new static props on class
			inheritProps(klass, this, Class);

			// do namespace stuff
			if ( fullName ) {

				var root;
				if (klass && klass.root) {
					root = klass.root;
					if ($.isString(root)) {
						root = getObject(root, window, true);
					}
				}

				var parts = fullName.split(/\./),
					shortName = parts.pop(),
					current = getObject(parts.join('.'), root || window, true),
					namespace = current;

				

				// !-- FOUNDRY HACK --! //
				// Inherit any existing properties from the namespace where Class is being assigned to.
				extend(true, Class, current[shortName]);

				current[shortName] = Class;
			}

			// set things that can't be overwritten
			extend(Class, {
				prototype: prototype,
				/**
				 * @attribute namespace
				 * The namespaces object
				 *
				 *     $.Class("MyOrg.MyClass",{},{})
				 *     MyOrg.MyClass.namespace //-> MyOrg
				 *
				 */
				namespace: namespace,
				/**
				 * @attribute shortName
				 * The name of the class without its namespace, provided for introspection purposes.
				 *
				 *     $.Class("MyOrg.MyClass",{},{})
				 *     MyOrg.MyClass.shortName //-> 'MyClass'
				 *     MyOrg.MyClass.fullName //->  'MyOrg.MyClass'
				 *
				 */
				shortName: shortName,
				constructor: Class,
				/**
				 * @attribute fullName
				 * The full name of the class, including namespace, provided for introspection purposes.
				 *
				 *     $.Class("MyOrg.MyClass",{},{})
				 *     MyOrg.MyClass.shortName //-> 'MyClass'
				 *     MyOrg.MyClass.fullName //->  'MyOrg.MyClass'
				 *
				 */
				fullName: fullName
			});

			//make sure our prototype looks nice
			Class[STR_PROTOTYPE].Class = Class[STR_PROTOTYPE].constructor = Class;



			// call the class setup
			var args = Class.setup.apply(Class, concatArgs([_super_class],arguments));

			// call the class init
			if ( Class.init ) {
				Class.init.apply(Class, args || concatArgs([_super_class],arguments));
			}

			/* @Prototype*/
			return Class;
			/**
			 * @function setup
			 * If a setup method is provided, it is called when a new
			 * instances is created.  It gets passed the same arguments that
			 * were given to the Class constructor function (<code> new Class( arguments ... )</code>).
			 *
			 *     $.Class("MyClass",
			 *     {
			 *        setup: function( val ) {
			 *           this.val = val;
			 *         }
			 *     })
			 *     var mc = new MyClass("Check Check")
			 *     mc.val //-> 'Check Check'
			 *
			 * Setup is called before [jQuery.Class.prototype.init init].  If setup
			 * return an array, those arguments will be used for init.
			 *
			 *     $.Class("jQuery.Controller",{
			 *       setup : function(htmlElement, rawOptions){
			 *         return [$(htmlElement),
			 *                   $.extend({}, this.Class.defaults, rawOptions )]
			 *       }
			 *     })
			 *
			 * <div class='whisper'>PRO TIP:
			 * Setup functions are used to normalize constructor arguments and provide a place for
			 * setup code that extending classes don't have to remember to call _super to
			 * run.
			 * </div>
			 *
			 * Setup is not defined on $.Class itself, so calling super in inherting classes
			 * will break.  Don't do the following:
			 *
			 *     $.Class("Thing",{
			 *       setup : function(){
			 *         this._super(); // breaks!
			 *       }
			 *     })
			 *
			 * @return {Array|undefined} If an array is return, [jQuery.Class.prototype.init] is
			 * called with those arguments; otherwise, the original arguments are used.
			 */
			//break up
			/**
			 * @function init
			 * If an <code>init</code> method is provided, it gets called when a new instance
			 * is created.  Init gets called after [jQuery.Class.prototype.setup setup], typically with the
			 * same arguments passed to the Class
			 * constructor: (<code> new Class( arguments ... )</code>).
			 *
			 *     $.Class("MyClass",
			 *     {
			 *        init: function( val ) {
			 *           this.val = val;
			 *        }
			 *     })
			 *     var mc = new MyClass(1)
			 *     mc.val //-> 1
			 *
			 * [jQuery.Class.prototype.setup Setup] is able to modify the arguments passed to init.  Read
			 * about it there.
			 *
			 */
			//Breaks up code
			/**
			 * @attribute constructor
			 *
			 * A reference to the Class (or constructor function).  This allows you to access
			 * a class's static properties from an instance.
			 *
			 * ### Quick Example
			 *
			 *     // a class with a static property
			 *     $.Class("MyClass", {staticProperty : true}, {});
			 *
			 *     // a new instance of myClass
			 *     var mc1 = new MyClass();
			 *
			 *     // read the static property from the instance:
			 *     mc1.constructor.staticProperty //-> true
			 *
			 * Getting static properties with the constructor property, like
			 * [jQuery.Class.static.fullName fullName], is very common.
			 *
			 */
		}

	})





	clss.callback = clss[STR_PROTOTYPE].callback = clss[STR_PROTOTYPE].
	/**
	 * @function proxy
	 * Returns a method that sets 'this' to the current instance.  This does the same thing as
	 * and is described better in [jQuery.Class.static.proxy].
	 * The only difference is this proxy works
	 * on a instance instead of a class.
	 * @param {String|Array} fname If a string, it represents the function to be called.
	 * If it is an array, it will call each function in order and pass the return value of the prior function to the
	 * next function.
	 * @return {Function} the callback function
	 */
	proxy = clss.proxy;


})();(function(){
	// ------- HELPER FUNCTIONS  ------

	// Binds an element, returns a function that unbinds
	var bind = function( el, ev, callback, eventData ) {
		var wrappedCallback,
			binder = el.bind && el.unbind ? el : $(isFunction(el) ? [el] : el);
		//this is for events like >click.
		if ( ev.indexOf(">") === 0 ) {
			ev = ev.substr(1);
			wrappedCallback = function( event ) {
				if ( event.target === el ) {
					callback.apply(this, arguments);
				}
			};
		}
		// !-- FOUNDRY HACK --! //
		// Support for passing event data
		if (eventData) {
			binder.bind(ev, eventData, wrappedCallback || callback);
		} else {
			binder.bind(ev, wrappedCallback || callback);
		}
		// if ev name has >, change the name and bind
		// in the wrapped callback, check that the element matches the actual element
		return function() {
			binder.unbind(ev, wrappedCallback || callback);
			el = ev = callback = wrappedCallback = null;
		};
	},
		makeArray = $.makeArray,
		isArray = $.isArray,
		isFunction = $.isFunction,
		isString = $.isString,
		extend = $.extend,
		Str = $.String,
		each = $.each,
		getObject = Str.getObject,

		STR_PROTOTYPE = 'prototype',
		STR_CONSTRUCTOR = 'constructor',
		slice = Array[STR_PROTOTYPE].slice,

		// Binds an element, returns a function that unbinds
		delegate = function( el, selector, ev, callback, eventData ) {

			// !-- FOUNDRY HACK --! //
			// Make event delegation work with direct child selector
			if ( selector.indexOf(">") === 0 ) {
				selector = (el.data("directSelector") + " " || "") + selector;
			}

			var binder = el.delegate && el.undelegate ? el : $(isFunction(el) ? [el] : el)

			// !-- FOUNDRY HACK --! //
			// Support for passing event data
			if (eventData) {
				binder.delegate(selector, ev, eventData, callback);
			} else {
				binder.delegate(selector, ev, callback);
			}

			return function() {
				binder.undelegate(selector, ev, callback);
				binder = el = ev = callback = selector = null;
			};
		},

		// calls bind or unbind depending if there is a selector
		binder = function( el, ev, callback, selector, eventData ) {
			// !-- FOUNDRY HACK --! //
			// Support for passing event data
			return selector ? delegate(el, selector, ev, callback, eventData) : bind(el, ev, callback, eventData);
		},

		// moves 'this' to the first argument, wraps it with jQuery if it's an element
		shifter = function shifter(context, name) {
			var method = typeof name == "string" ? context[name] : name;

			// !-- FOUNDRY HACK --! //
			// Support for passing event data
			if (isArray(method) && isFunction(method[1])) {
				method = method[1];
			}

			return function() {
				context.called = name;
				return method.apply(context, [this.nodeName ? $(this) : this].concat( slice.call(arguments, 0) ) );
			};
		},
		// matches dots
		dotsReg = /\./g,
		// matches controller
		controllersReg = /_?controllers?/ig,
		//used to remove the controller from the name
		underscoreAndRemoveController = function( className ) {
			return Str.underscore(className.replace($.globalNamespace + ".", "").replace(dotsReg, '_').replace(controllersReg, ""));
		},
		// checks if it looks like an action
		// actionMatcher = /[^\w]/,

		// !-- FOUNDRY HACK --! //
		// Prevent inclusion of single word property name that starts with a symbol, e.g. $family from MooTools.
		// This is coming from an environment where jQuery and MooTools may coexist.
		actionMatcher = /^\S(.*)\s(.*)/,

		// handles parameterized action names
		parameterReplacer = /\{([^\}]+)\}/g,
		controllerReplacer = /\{([^\.]+[\.][^\.]+)\}/g,
		breaker = /^(?:(.*?)\s)?([\w\.\:>]+)$/,
		basicProcessor,
		data = function(el, data){
			return $.data(el, "controllers", data)
		};
	/**
	 * @class jQuery.Controller
	 * @parent jquerymx
	 * @plugin jquery/controller
	 * @download  http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/controller/controller.js
	 * @test jquery/controller/qunit.html
	 * @inherits jQuery.Class
	 * @description jQuery widget factory.
	 *
	 * jQuery.Controller helps create organized, memory-leak free, rapidly performing
	 * jQuery widgets.  Its extreme flexibility allows it to serve as both
	 * a traditional View and a traditional Controller.
	 *
	 * This means it is used to
	 * create things like tabs, grids, and contextmenus as well as
	 * organizing them into higher-order business rules.
	 *
	 * Controllers make your code deterministic, reusable, organized and can tear themselves
	 * down auto-magically. Read about [http://jupiterjs.com/news/writing-the-perfect-jquery-plugin
	 * the theory behind controller] and
	 * a [http://jupiterjs.com/news/organize-jquery-widgets-with-jquery-controller walkthrough of its features]
	 * on Jupiter's blog. [mvc.controller Get Started with jQueryMX] also has a great walkthrough.
	 *
	 * Controller inherits from [jQuery.Class $.Class] and makes heavy use of
	 * [http://api.jquery.com/delegate/ event delegation]. Make sure
	 * you understand these concepts before using it.
	 *
	 * ## Basic Example
	 *
	 * Instead of
	 *
	 *
	 *     $(function(){
	 *       $('#tabs').click(someCallbackFunction1)
	 *       $('#tabs .tab').click(someCallbackFunction2)
	 *       $('#tabs .delete click').click(someCallbackFunction3)
	 *     });
	 *
	 * do this
	 *
	 *     $.Controller('Tabs',{
	 *       click: function() {...},
	 *       '.tab click' : function() {...},
	 *       '.delete click' : function() {...}
	 *     })
	 *     $('#tabs').tabs();
	 *
	 *
	 * ## Tabs Example
	 *
	 * @demo jquery/controller/controller.html
	 *
	 * ## Using Controller
	 *
	 * Controller helps you build and organize jQuery plugins.  It can be used
	 * to build simple widgets, like a slider, or organize multiple
	 * widgets into something greater.
	 *
	 * To understand how to use Controller, you need to understand
	 * the typical lifecycle of a jQuery widget and how that maps to
	 * controller's functionality:
	 *
	 * ### A controller class is created.
	 *
	 *     $.Controller("MyWidget",
	 *     {
	 *       defaults :  {
	 *         message : "Remove Me"
	 *       }
	 *     },
	 *     {
	 *       init : function(rawEl, rawOptions){
	 *         this.element.append(
	 *            "<div>"+this.options.message+"</div>"
	 *           );
	 *       },
	 *       "div click" : function(div, ev){
	 *         div.remove();
	 *       }
	 *     })
	 *
	 * This creates a <code>$.fn.my_widget</code> jQuery helper function
	 * that can be used to create a new controller instance on an element. Find
	 * more information [jquery.controller.plugin  here] about the plugin gets created
	 * and the rules around its name.
	 *
	 * ### An instance of controller is created on an element
	 *
	 *     $('.thing').my_widget(options) // calls new MyWidget(el, options)
	 *
	 * This calls <code>new MyWidget(el, options)</code> on
	 * each <code>'.thing'</code> element.
	 *
	 * When a new [jQuery.Class Class] instance is created, it calls the class's
	 * prototype setup and init methods. Controller's [jQuery.Controller.prototype.setup setup]
	 * method:
	 *
	 *  - Sets [jQuery.Controller.prototype.element this.element] and adds the controller's name to element's className.
	 *  - Merges passed in options with defaults object and sets it as [jQuery.Controller.prototype.options this.options]
	 *  - Saves a reference to the controller in <code>$.data</code>.
	 *  - [jquery.controller.listening Binds all event handler methods].
	 *
	 *
	 * ### The controller responds to events
	 *
	 * Typically, Controller event handlers are automatically bound.  However, there are
	 * multiple ways to [jquery.controller.listening listen to events] with a controller.
	 *
	 * Once an event does happen, the callback function is always called with 'this'
	 * referencing the controller instance.  This makes it easy to use helper functions and
	 * save state on the controller.
	 *
	 *
	 * ### The widget is destroyed
	 *
	 * If the element is removed from the page, the
	 * controller's [jQuery.Controller.prototype.destroy] method is called.
	 * This is a great place to put any additional teardown functionality.
	 *
	 * You can also teardown a controller programatically like:
	 *
	 *     $('.thing').my_widget('destroy');
	 *
	 * ## Todos Example
	 *
	 * Lets look at a very basic example -
	 * a list of todos and a button you want to click to create a new todo.
	 * Your HTML might look like:
	 *
	 * @codestart html
	 * &lt;div id='todos'>
	 *  &lt;ol>
	 *    &lt;li class="todo">Laundry&lt;/li>
	 *    &lt;li class="todo">Dishes&lt;/li>
	 *    &lt;li class="todo">Walk Dog&lt;/li>
	 *  &lt;/ol>
	 *  &lt;a class="create">Create&lt;/a>
	 * &lt;/div>
	 * @codeend
	 *
	 * To add a mousover effect and create todos, your controller might look like:
	 *
	 *     $.Controller('Todos',{
	 *       ".todo mouseover" : function( el, ev ) {
	 *         el.css("backgroundColor","red")
	 *       },
	 *       ".todo mouseout" : function( el, ev ) {
	 *         el.css("backgroundColor","")
	 *       },
	 *       ".create click" : function() {
	 *         this.find("ol").append("<li class='todo'>New Todo</li>");
	 *       }
	 *     })
	 *
	 * Now that you've created the controller class, you've must attach the event handlers on the '#todos' div by
	 * creating [jQuery.Controller.prototype.setup|a new controller instance].  There are 2 ways of doing this.
	 *
	 * @codestart
	 * //1. Create a new controller directly:
	 * new Todos($('#todos'));
	 * //2. Use jQuery function
	 * $('#todos').todos();
	 * @codeend
	 *
	 * ## Controller Initialization
	 *
	 * It can be extremely useful to add an init method with
	 * setup functionality for your widget.
	 *
	 * In the following example, I create a controller that when created, will put a message as the content of the element:
	 *
	 *     $.Controller("SpecialController",
	 *     {
	 *       init: function( el, message ) {
	 *         this.element.html(message)
	 *       }
	 *     })
	 *     $(".special").special("Hello World")
	 *
	 * ## Removing Controllers
	 *
	 * Controller removal is built into jQuery.  So to remove a controller, you just have to remove its element:
	 *
	 * @codestart
	 * $(".special_controller").remove()
	 * $("#containsControllers").html("")
	 * @codeend
	 *
	 * It's important to note that if you use raw DOM methods (<code>innerHTML, removeChild</code>), the controllers won't be destroyed.
	 *
	 * If you just want to remove controller functionality, call destroy on the controller instance:
	 *
	 * @codestart
	 * $(".special_controller").controller().destroy()
	 * @codeend
	 *
	 * ## Accessing Controllers
	 *
	 * Often you need to get a reference to a controller, there are a few ways of doing that.  For the
	 * following example, we assume there are 2 elements with <code>className="special"</code>.
	 *
	 * @codestart
	 * //creates 2 foo controllers
	 * $(".special").foo()
	 *
	 * //creates 2 bar controllers
	 * $(".special").bar()
	 *
	 * //gets all controllers on all elements:
	 * $(".special").controllers() //-> [foo, bar, foo, bar]
	 *
	 * //gets only foo controllers
	 * $(".special").controllers(FooController) //-> [foo, foo]
	 *
	 * //gets all bar controllers
	 * $(".special").controllers(BarController) //-> [bar, bar]
	 *
	 * //gets first controller
	 * $(".special").controller() //-> foo
	 *
	 * //gets foo controller via data
	 * $(".special").data("controllers")["FooController"] //-> foo
	 * @codeend
	 *
	 * ## Calling methods on Controllers
	 *
	 * Once you have a reference to an element, you can call methods on it.  However, Controller has
	 * a few shortcuts:
	 *
	 * @codestart
	 * //creates foo controller
	 * $(".special").foo({name: "value"})
	 *
	 * //calls FooController.prototype.update
	 * $(".special").foo({name: "value2"})
	 *
	 * //calls FooController.prototype.bar
	 * $(".special").foo("bar","something I want to pass")
	 * @codeend
	 *
	 * These methods let you call one controller from another controller.
	 *
	 */
	var controllerRoot = $.globalNamespace + ".Controller";

	$.Controller = function(name) {

		// !-- FOUNDRY HACK --! //
		// By default, all controllers are created under the
		// $.Controller root namespace.
		var args = makeArray(arguments),
			_static = {
				root: controllerRoot
			},
			_prototype;

		if (args.length > 2) {
			// Namespace can be overriden
			_static = $.extend(_static, args[1]);
			_prototype = args[2];
		} else {
			_prototype = args[1];
		}

		if (_static.namespace) {
			name = _static.namespace + "." + name;
		}

		return $.Controller.Class(name, _static, _prototype);
	}

	var controllerClass = controllerRoot + ".Class";

	$.Class(controllerClass,
	/**
	 * @Static
	 */
	{
		/**
		 * Does 2 things:
		 *
		 *   - Creates a jQuery helper for this controller.</li>
		 *   - Calculates and caches which functions listen for events.</li>
		 *
		 * ### jQuery Helper Naming Examples
		 *
		 *
		 *     "TaskController" -> $().task_controller()
		 *     "Controllers.Task" -> $().controllers_task()
		 *
		 */
		setup: function(baseClass, name) {

			// Allow contollers to inherit "defaults" from superclasses as it done in $.Class
			this._super.apply(this, arguments);

			// if you didn't provide a name, or are controller, don't do anything
			if (!this.shortName || this.fullName == controllerClass) {
				return;
			}

			// !-- FOUNDRY HACK --! //
			// Added support for expandable elements.
			var elements = this.elements || [],
				i = 0,
				defaults = this.defaults;

			while (element = elements[i++]) {

				var start  = element.indexOf("{"),
					end    = element.indexOf("}"),
					length = element.length,
					prefix = element.slice(0, start),
					suffix = element.slice(end + 1),
					names  = element.slice(start + 1, end).split("|"),
					j = 0;

					// "^width [data-eb{label|slider}]" turns into
					// widthLabel  => [data-eb-label]
					// widthSlider => [data-eb-slider]

					// "^width [data-eb".match(/^\^(\S*)\s(.*)/);
					// 0 ==> "^width [data-eb"
					// 1 ==> "width",
					// 2 ==> "[data-eb"
					var parts = prefix.match(/^\^(\S*)\s(.*)/),
						propPrefix = "";

					if (parts) {
						propPrefix = parts[1] + "-";
						prefix = parts[2];
					}

					while (name = names[j++]) {
						var prop = "{" + $.camelize(propPrefix + name) + "}";

						!$.has(defaults, prop) &&
							(defaults[prop] = prefix + name + suffix);
					}
			}

			// cache the underscored names
			this._fullName = underscoreAndRemoveController(this.fullName);
			this._shortName = underscoreAndRemoveController(this.shortName);

			var controller = this,
				/**
				 * @attribute pluginName
				 * Setting the <code>pluginName</code> property allows you
				 * to change the jQuery plugin helper name from its
				 * default value.
				 *
				 *     $.Controller("Mxui.Layout.Fill",{
				 *       pluginName: "fillWith"
				 *     },{});
				 *
				 *     $("#foo").fillWith();
				 */
				funcName, forLint;

			// !-- FOUNDRY HACK --! //
			// Make creation of jQuery plugin by testing the existence of pluginName.
			if (isString(this.pluginName)) {

				// !-- FOUNDRY HACK --! //
				// Add a reference to the fullname
				var _fullName = this._fullName;
				var pluginname = this.pluginName;

				// create jQuery plugin
				if (!$.fn[pluginname] ) {
					$.fn[pluginname] = function( options ) {

						var args = makeArray(arguments);

						// Returning controller instance if it exists
						if ($.isString(options) && options==="controller") {

							var controllers = data(this[0]),
								instance = controllers && controllers[_fullName];

							return instance;
						}

						return this.each(function() {
							//check if created
							var controllers = data(this),
								//plugin is actually the controller instance
								//plugin = controllers && controllers[pluginname];

								// !-- FOUNDRY HACK --! //
								// Check using controller full name
								instance = controllers && controllers[_fullName];

							if (instance) {

								// call a method on the controller with the remaining args
								if ($.isString(options)) {
									var method = instance[options];
									$.isFunction(method) && method.apply(instance, args.slice(1));
									return;
								}

								// call the plugin's update method
								instance.update.apply(instance, args);

							} else {
								//create a new controller instance
								controller.newInstance.apply(controller, [this].concat(args));
							}
						});
					};
				}
			}

			// !-- FOUNDRY HACK --! //
			// If a prototype factory function was given instead of a prototype object,
			// we expect the factory function to return the prototype object upon execution
			// of the factory function. This factory function gets executed during the
			// instantiation of the controller.

			var args         = makeArray(arguments),
				prototype    = this[STR_PROTOTYPE],
				protoFactory = args[(args.length > 3) ? 3 : 2];

			if (isFunction(protoFactory)) {

				// Remap the factory function
				this.protoFactory = protoFactory;

				// Attempt to execute the prototype factory once to get
				// a list of actions that we can cache first.
				prototype = this.protoFactory.call(this, null);
			}

			// calculate and cache actions
			this.actions = {};

			// !-- FOUNDRY HACK --! //
			// Support for handlers that also pass in event data
			for (funcName in prototype) {

				if (funcName=='constructor') continue;

				if (this._isAction(funcName)) {

					var method   = prototype[funcName],
						isMethod = isFunction(method) || (isArray(method) && isFunction(method[1]));

					if (!isMethod) continue;

					this.actions[funcName] = this._action(funcName);
				}
			}

			// !-- FOUNDRY HACK --! //
			// Controller has been created. Resolve module.
			$.module("$:/Controllers/" + this.fullName).resolve(this);
		},

		hookup: function( el ) {
			return new this(el);
		},

		/**
		 * @hide
		 * @param {String} methodName a prototype function
		 * @return {Boolean} truthy if an action or not
		 */
		_isAction: function( methodName ) {
			if ( actionMatcher.test(methodName) ) {
				return true;
			} else {
				return $.inArray(methodName, this.listensTo) > -1 || $.event.special[methodName] || processors[methodName];
			}

		},
		/**
		 * @hide
		 * This takes a method name and the options passed to a controller
		 * and tries to return the data necessary to pass to a processor
		 * (something that binds things).
		 *
		 * For performance reasons, this called twice.  First, it is called when
		 * the Controller class is created.  If the methodName is templated
		 * like : "{window} foo", it returns null.  If it is not templated
		 * it returns event binding data.
		 *
		 * The resulting data is added to this.actions.
		 *
		 * When a controller instance is created, _action is called again, but only
		 * on templated actions.
		 *
		 * @param {Object} methodName the method that will be bound
		 * @param {Object} [options] first param merged with class default options
		 * @return {Object} null or the processor and pre-split parts.
		 * The processor is what does the binding/subscribing.
		 */
		_action: function( methodName, options ) {
			// reset the test index
			parameterReplacer.lastIndex = 0;

			//if we don't have options (a controller instance), we'll run this later
			if (!options && parameterReplacer.test(methodName) ) {
				return null;
			}

			// !-- FOUNDRY HACK --! //
			// Ability to bind custom event to self.
			// "{self} customEvent"
			methodName = methodName.replace("{self} ", "");

			// If we have options, run sub to replace templates "{}" with a value from the options
			// or the window
			var convertedName = methodName;

			if (options) {

				var bindingOtherController = false;

				if (controllerReplacer.test(methodName)) {

					var controller, selector = "";
					convertedName =
						methodName
							.replace(controllerReplacer, function(whole, inside){
								var parts = inside.split(".");
								controller = options["{"+parts[0]+"}"] || {};
								if ($.isControllerInstance(controller)) {
									selector = (controller[parts[1]] || {})["selector"];
								}
								return selector;
							})
							.match(breaker);

					// If there is a selector, this will be true.
					bindingOtherController = !!selector;

					convertedName = [controller.element].concat(convertedName || []);
				}

				if (!bindingOtherController) {

					convertedName = Str.sub(methodName, [options, window]);
				}
			}

			// If a "{}" resolves to an object, convertedName will be an array
			var arr = isArray(convertedName),

				// get the parts of the function = [convertedName, delegatePart, eventPart]
				parts = (arr ? convertedName[1] : convertedName).match(breaker),
				event = parts[2],
				processor = processors[event] || basicProcessor;

			return {
				processor: processor,
				parts: parts,
				delegate : arr ? convertedName[0] : undefined
			};
		},

		/**
		 * @attribute processors
		 * An object of {eventName : function} pairs that Controller uses to hook up events
		 * auto-magically.  A processor function looks like:
		 *
		 *     jQuery.Controller.processors.
		 *       myprocessor = function( el, event, selector, cb, controller ) {
		 *          //el - the controller's element
		 *          //event - the event (myprocessor)
		 *          //selector - the left of the selector
		 *          //cb - the function to call
		 *          //controller - the binding controller
		 *       };
		 *
		 * This would bind anything like: "foo~3242 myprocessor".
		 *
		 * The processor must return a function that when called,
		 * unbinds the event handler.
		 *
		 * Controller already has processors for the following events:
		 *
		 *   - change
		 *   - click
		 *   - contextmenu
		 *   - dblclick
		 *   - focusin
		 *   - focusout
		 *   - keydown
		 *   - keyup
		 *   - keypress
		 *   - mousedown
		 *   - mouseenter
		 *   - mouseleave
		 *   - mousemove
		 *   - mouseout
		 *   - mouseover
		 *   - mouseup
		 *   - reset
		 *   - resize
		 *   - scroll
		 *   - select
		 *   - submit
		 *
		 * Listen to events on the document or window
		 * with templated event handlers:
		 *
		 *
		 *     $.Controller('Sized',{
		 *       "{window} resize" : function(){
		 *         this.element.width(this.element.parent().width() / 2);
		 *       }
		 *     });
		 *
		 *     $('.foo').sized();
		 */
		processors: {},
		/**
		 * @attribute listensTo
		 * An array of special events this controller
		 * listens too.  You only need to add event names that
		 * are whole words (ie have no special characters).
		 *
		 *     $.Controller('TabPanel',{
		 *       listensTo : ['show']
		 *     },{
		 *       'show' : function(){
		 *         this.element.show();
		 *       }
		 *     })
		 *
		 *     $('.foo').tab_panel().trigger("show");
		 *
		 */
		listensTo: [],
		/**
		 * @attribute defaults
		 * A object of name-value pairs that act as default values for a controller's
		 * [jQuery.Controller.prototype.options options].
		 *
		 *     $.Controller("Message",
		 *     {
		 *       defaults : {
		 *         message : "Hello World"
		 *       }
		 *     },{
		 *       init : function(){
		 *         this.element.text(this.options.message);
		 *       }
		 *     })
		 *
		 *     $("#el1").message(); //writes "Hello World"
		 *     $("#el12").message({message: "hi"}); //writes hi
		 *
		 * In [jQuery.Controller.prototype.setup setup] the options passed to the controller
		 * are merged with defaults.  This is not a deep merge.
		 */
		defaults: {},

		hostname: "parent"
	},
	/**
	 * @Prototype
	 */
	{
		/**
		 * Setup is where most of controller's magic happens.  It does the following:
		 *
		 * ### 1. Sets this.element
		 *
		 * The first parameter passed to new Controller(el, options) is expected to be
		 * an element.  This gets converted to a jQuery wrapped element and set as
		 * [jQuery.Controller.prototype.element this.element].
		 *
		 * ### 2. Adds the controller's name to the element's className.
		 *
		 * Controller adds it's plugin name to the element's className for easier
		 * debugging.  For example, if your Controller is named "Foo.Bar", it adds
		 * "foo_bar" to the className.
		 *
		 * ### 3. Saves the controller in $.data
		 *
		 * A reference to the controller instance is saved in $.data.  You can find
		 * instances of "Foo.Bar" like:
		 *
		 *     $("#el").data("controllers")['foo_bar'].
		 *
		 * ### Binds event handlers
		 *
		 * Setup does the event binding described in [jquery.controller.listening Listening To Events].
		 *
		 * @param {HTMLElement} element the element this instance operates on.
		 * @param {Object} [options] option values for the controller.  These get added to
		 * this.options and merged with [jQuery.Controller.static.defaults defaults].
		 * @return {Array} return an array if you wan to change what init is called with. By
		 * default it is called with the element and options passed to the controller.
		 */
		setup: function(elem, options) {

			var instance  = this,
				Class     = instance[STR_CONSTRUCTOR],
				prototype = instance[STR_PROTOTYPE];

			var _fullName = Class._fullName;

			// !-- FOUNDRY HACK --! //
			// Unique id for every controller instance.
			instance.instanceId = $.uid(_fullName + '_');

			// !-- FOUNDRY HACK --! //
			// Added defaultOptions as an alternative to defaults
			var instanceOptions = instance.options
								= extend(true, {}, Class.defaults, Class.defaultOptions, options);

			// Convert HTML element into a jQuery element
			// and store it inside instance.element.
			var element = instance.element
						= $(elem);

			// !-- FOUNDRY HACK --! //
			// Execute factory function if exists, extends the properties
			// of the returned object onto the instance.
			if (Class.protoFactory) {

				// This is where "self" keyword is passed as first argument.
				prototype = Class.protoFactory.apply(Class, [instance, instanceOptions, element]);

				// Extend the properties of the prototype object onto the instance.
				extend(true, instance, prototype);
			}

			// !-- FOUNDRY HACK --! //
			// Use _fullName instead
			// This actually does $(e).data("controllers", _fullName);
			(data(elem) || data(elem, {}))[_fullName] = instance;

			// !-- FOUNDRY HACK --~ //
			// Add a unique direct selector for every controller instance.
			if (!element.data("directSelector")) {
				var selector = $.uid("DS");
				element
					.addClass(selector)
					.data("directSelector", "." + selector);
			}

			// !-- FOUNDRY HACK --! //
			// Augment selector properties into selector functions.
			// The rest are passed in as controller properties.
			instance.selectors = {};

			for (var name in instanceOptions) {

				if (!name.match(/^\{.+\}$/)) continue;

				var key = name.replace(/^\{|\}$/g,''),
					val = instanceOptions[name];

				// Augmented selector function
				if (isString(val)) {

					var selectorFuncExtension = instance[key];

					instance[key] = instance.selectors[key] = (function(instance, selector, funcName) {

						// Selector shorthand for controllers
						selector = /^(\.|\#)$/.test(selector) ? selector + funcName : selector;

						// Create selector function
						var selectorFunc = function(filter) {

							var elements = (selectorFunc.baseElement || instance.element).find(selector);

							if ($.isString(filter)) {
								elements = elements.filter(filter);
							}

							if ($.isPlainObject(filter)) {
								$.each(filter, function(key, val){
									elements = elements.filterBy(key, val);
								});
							}

							return elements;
						};

						// Keep the selector as a property of the function
						selectorFunc.selector = selector;

						selectorFunc.css = function() {

							var cssRule = selectorFunc.cssRule;

							if (!cssRule) {

								var directSelector = element.data("directSelector"),

									ruleSelector = $.map(selector.split(","), function(selector) {
														return directSelector + " " + selector
													});

								cssRule = selectorFunc.cssRule = $.cssRule(ruleSelector);
								cssRule.important = true;
							}

							return (arguments.length) ? cssRule.css.apply(cssRule, arguments) : cssRule;
						};

						selectorFunc.inside = function(el) {
							return $(el).find(selector);
						};

						selectorFunc.of = function(el) {
							return $(el).parents(selector).eq(0);
						};

						selectorFunc.under = function(el) {

							var nodes = [];

							selectorFunc().each(function(){
								if ($(this).parents().filter(el).length) {
									nodes.push(this);
								}
							});

							return $(nodes);
						};
						
						if ($.isPlainObject(selectorFuncExtension)) {
							$.extend(selectorFunc, selectorFuncExtension);
						}

						return selectorFunc;

					})(instance, val, key);

				// Else just reference it, e.g. controller instance
				} else {

					instance[key] = val;
				}
			}

			// !-- FOUNDRY HACK --! //
			// Augment view properties into view functions.
			// self.view.listItem(useHtml, data, callback);
			var views = instanceOptions.view;

			// Prevent augmented functions from being
			// extended onto the prototype view function.
			var __view = instance.view;

			instance.view = function() {
				return __view.apply(this, arguments);
			};

			each(views || {}, function(name, view){

				instance.view[name] = function(useHtml) {

					var args = makeArray(arguments);

					if ($.isBoolean(useHtml)) {
						args = args.slice(1);
					} else {
						useHtml = false;
					}

					return instance.view.apply(instance, [useHtml, name].concat(args));
				}
			});

			// !-- FOUNDRY HACK --! //
			// Instance property override
			$.extend(instance, instanceOptions.controller);

			// !--- FOUNDRY HACK --! //
			instance.pluginInstances = {};

			/**
			 * @attribute called
			 * String name of current function being called on controller instance.  This is
			 * used for picking the right view in render.
			 * @hide
			 */
			instance.called = "init";

			// bind all event handlers
			instance._bind();

			var __init = instance.init || $.noop;

			// !-- FOUNDRY HACK --! //
			// Trigger init event when controller is created.
			instance.init = function(){
				instance.init = __init;
				result = __init.apply(instance, arguments);
				instance.trigger("init." + Class.fullName.toLowerCase(), [instance]);
				return result;
			}

			/**
			 * @attribute element
			 * The controller instance's delegated element. This
			 * is set by [jQuery.Controller.prototype.setup setup]. It
			 * is a jQuery wrapped element.
			 *
			 * For example, if I add MyWidget to a '#myelement' element like:
			 *
			 *     $.Controller("MyWidget",{
			 *       init : function(){
			 *         this.element.css("color","red")
			 *       }
			 *     })
			 *
			 *     $("#myelement").my_widget()
			 *
			 * MyWidget will turn #myelement's font color red.
			 *
			 * ## Using a different element.
			 *
			 * Sometimes, you want a different element to be this.element.  A
			 * very common example is making progressively enhanced form widgets.
			 *
			 * To change this.element, overwrite Controller's setup method like:
			 *
			 *     $.Controller("Combobox",{
			 *       setup : function(el, options){
			 *          this.oldElement = $(el);
			 *          var newEl = $('<div/>');
			 *          this.oldElement.wrap(newEl);
			 *          this._super(newEl, options);
			 *       },
			 *       init : function(){
			 *          this.element //-> the div
			 *       },
			 *       ".option click" : function(){
			 *         // event handler bound on the div
			 *       },
			 *       destroy : function(){
			 *          var div = this.element; //save reference
			 *          this._super();
			 *          div.replaceWith(this.oldElement);
			 *       }
			 *     }
			 */
			return [element, instanceOptions].concat(makeArray(arguments).slice(2));
			/**
			 * @function init
			 *
			 * Implement this.
			 */
		},
		/**
		 * Bind attaches event handlers that will be
		 * removed when the controller is removed.
		 *
		 * This used to be a good way to listen to events outside the controller's
		 * [jQuery.Controller.prototype.element element].  However,
		 * using templated event listeners is now the prefered way of doing this.
		 *
		 * ### Example:
		 *
		 *     init: function() {
		 *        // calls somethingClicked(el,ev)
		 *        this.bind('click','somethingClicked')
		 *
		 *        // calls function when the window is clicked
		 *        this.bind(window, 'click', function(ev){
		 *          //do something
		 *        })
		 *     },
		 *     somethingClicked: function( el, ev ) {
		 *
		 *     }
		 *
		 * @param {HTMLElement|jQuery.fn|Object} [el=this.element]
		 * The element to be bound.  If an eventName is provided,
		 * the controller's element is used instead.
		 *
		 * @param {String} eventName The event to listen for.
		 * @param {Function|String} func A callback function or the String name of a controller function.  If a controller
		 * function name is given, the controller function is called back with the bound element and event as the first
		 * and second parameter.  Otherwise the function is called back like a normal bind.
		 * @return {Integer} The id of the binding in this._bindings
		 */

		on: function(eventName) {

			var args = makeArray(arguments),
				element = this.element,
				length = args.length;

			// Listen to the controller's element
			// on(eventName, eventHandler);
			if (length==2) {
				return this._binder(element, eventName, args[1]);
			}

			// Listen to controller's child elements matching the selector
			// on(eventName, selector, eventHandler);
			// args[1] == selector, jquery collection or dom node.
			// args[2] == eventHandler.
			if (length==3 && isString(args[1])) {
				return this._binder(element, eventName, args[2], args[1]);
			} else {
				return this._binder(args[1], eventName, args[2]);
			}

			// Listen to an element from another element
			// on(eventName, element, selector, eventHandler);
			if (length==4) {
				return this._binder($(args[1]), eventName, args[3], args[2]);
			}
		},

		// !-- FOUNDRY HACK --! //
		// Rename this.bind from this_bind. Conflict with mootools.
		// _bind: function( el, eventName, func ) {
		_bind: function() {

			var instance = this,
				Class    = instance[STR_CONSTRUCTOR],
				actions  = Class.actions,
				bindings = instance._bindings = [],
				element  = instance.element;

			each(actions || {}, function(name, action){

				if (!actions.hasOwnProperty(name)) return;

				var ready = Class.actions[name] || Class._action(name, instance.options);

				// Translate to the controller element first
				if ($.isControllerInstance(ready.delegate)) {
					ready.delegate = ready.delegate.element;
				}

				bindings.push(
					ready.processor(
						ready.delegate || element,
						ready.parts[2],
						ready.parts[1],
						name,
						instance
					)
				);
			});

			//setup to be destroyed ... don't bind b/c we don't want to remove it
			var destroyCB = shifter(this,"destroy");
			element.bind("destroyed", destroyCB);
			bindings.push(function( el ) {
				$(el).unbind("destroyed", destroyCB);
			});
			return bindings.length;
		},
		_binder: function( el, eventName, func, selector ) {
			if ( typeof func == 'string' ) {
				func = shifter(this,func);
			}
			this._bindings.push(binder(el, eventName, func, selector));
			return this._bindings.length;
		},
		_unbind : function(){
			var el = this.element[0];
			each(this._bindings, function( key, value ) {
				value(el);
			});
			//adds bindings
			this._bindings = [];
		},
		// !-- FOUNDRY HACK --! //
		// Element event triggering
		trigger: function(name) {

			var el = this.element;
			if (!el) return;

			var event = $.Event(name);
				el.trigger.apply(el, [event].concat($.makeArray(arguments).slice(1)));

			return event;
		},
		/**
		 * Delegate will delegate on an elememt and will be undelegated when the controller is removed.
		 * This is a good way to delegate on elements not in a controller's element.<br/>
		 * <h3>Example:</h3>
		 * @codestart
		 * // calls function when the any 'a.foo' is clicked.
		 * this.delegate(document.documentElement,'a.foo', 'click', function(ev){
		 *   //do something
		 * })
		 * @codeend
		 * @param {HTMLElement|jQuery.fn} [element=this.element] the element to delegate from
		 * @param {String} selector the css selector
		 * @param {String} eventName the event to bind to
		 * @param {Function|String} func A callback function or the String name of a controller function.  If a controller
		 * function name is given, the controller function is called back with the bound element and event as the first
		 * and second parameter.  Otherwise the function is called back like a normal bind.
		 * @return {Integer} The id of the binding in this._bindings
		 */
		delegate: function( element, selector, eventName, func ) {
			if ( typeof element == 'string' ) {
				func = eventName;
				eventName = selector;
				selector = element;
				element = this.element;
			}
			return this._binder(element, eventName, func, selector);
		},
		/**
		 * Update extends [jQuery.Controller.prototype.options this.options]
		 * with the `options` argument and rebinds all events.  It basically
		 * re-configures the controller.
		 *
		 * For example, the following controller wraps a recipe form. When the form
		 * is submitted, it creates the recipe on the server.  When the recipe
		 * is `created`, it resets the form with a new instance.
		 *
		 *     $.Controller('Creator',{
		 *       "{recipe} created" : function(){
		 *         this.update({recipe : new Recipe()});
		 *         this.element[0].reset();
		 *         this.find("[type=submit]").val("Create Recipe")
		 *       },
		 *       "submit" : function(el, ev){
		 *         ev.preventDefault();
		 *         var recipe = this.options.recipe;
		 *         recipe.attrs( this.element.formParams() );
		 *         this.find("[type=submit]").val("Saving...")
		 *         recipe.save();
		 *       }
		 *     });
		 *     $('#createRecipes').creator({recipe : new Recipe()})
		 *
		 *
		 * @demo jquery/controller/demo-update.html
		 *
		 * Update is called if a controller's [jquery.controller.plugin jQuery helper] is
		 * called on an element that already has a controller instance
		 * of the same type.
		 *
		 * For example, a widget that listens for model updates
		 * and updates it's html would look like.
		 *
		 *     $.Controller('Updater',{
		 *       // when the controller is created, update the html
		 *       init : function(){
		 *         this.updateView();
		 *       },
		 *
		 *       // update the html with a template
		 *       updateView : function(){
		 *         this.element.html( "content.ejs",
		 *                            this.options.model );
		 *       },
		 *
		 *       // if the model is updated
		 *       "{model} updated" : function(){
		 *         this.updateView();
		 *       },
		 *       update : function(options){
		 *         // make sure you call super
		 *         this._super(options);
		 *
		 *         this.updateView();
		 *       }
		 *     })
		 *
		 *     // create the controller
		 *     // this calls init
		 *     $('#item').updater({model: recipe1});
		 *
		 *     // later, update that model
		 *     // this calls "{model} updated"
		 *     recipe1.update({name: "something new"});
		 *
		 *     // later, update the controller with a new recipe
		 *     // this calls update
		 *     $('#item').updater({model: recipe2});
		 *
		 *     // later, update the new model
		 *     // this calls "{model} updated"
		 *     recipe2.update({name: "something newer"});
		 *
		 * _NOTE:_ If you overwrite `update`, you probably need to call
		 * this._super.
		 *
		 * ### Example
		 *
		 *     $.Controller("Thing",{
		 *       init: function( el, options ) {
		 *         alert( 'init:'+this.options.prop )
		 *       },
		 *       update: function( options ) {
		 *         this._super(options);
		 *         alert('update:'+this.options.prop)
		 *       }
		 *     });
		 *     $('#myel').thing({prop : 'val1'}); // alerts init:val1
		 *     $('#myel').thing({prop : 'val2'}); // alerts update:val2
		 *
		 * @param {Object} options A list of options to merge with
		 * [jQuery.Controller.prototype.options this.options].  Often, this method
		 * is called by the [jquery.controller.plugin jQuery helper function].
		 */
		update: function( options ) {
			extend(this.options, options);
			this._unbind();
			this._bind();
		},
		/**
		 * Destroy unbinds and undelegates all event handlers on this controller,
		 * and prevents memory leaks.  This is called automatically
		 * if the element is removed.  You can overwrite it to add your own
		 * teardown functionality:
		 *
		 *     $.Controller("ChangeText",{
		 *       init : function(){
		 *         this.oldText = this.element.text();
		 *         this.element.text("Changed!!!")
		 *       },
		 *       destroy : function(){
		 *         this.element.text(this.oldText);
		 *         this._super(); //Always call this!
		 *     })
		 *
		 * Make sure you always call <code>_super</code> when overwriting
		 * controller's destroy event.  The base destroy functionality unbinds
		 * all event handlers the controller has created.
		 *
		 * You could call destroy manually on an element with ChangeText
		 * added like:
		 *
		 *     $("#changed").change_text("destroy");
		 *
		 */
		destroy: function() {

			if ( this._destroyed ) {
				return;
			}
			var fname = this[STR_CONSTRUCTOR]._fullName,
				controllers;

			// remove all plugins
			for (pname in this.pluginInstances) {
				this.removePlugin(pname);
			}

			// mark as destroyed
			this._destroyed = true;

			// remove the className
			this.element.removeClass(fname);

			// unbind bindings
			this._unbind();
			// clean up
			delete this._actions;

			delete this.element.data("controllers")[fname];

			$(this).triggerHandler("destroyed"); //in case we want to know if the controller is removed

			// !-- FOUNDRY HACK --! //
			// Reassign this.element to an empty jQuery element instead.
			this.element = $();
		},
		/**
		 * Queries from the controller's element.
		 * @codestart
		 * ".destroy_all click" : function() {
		 *    this.find(".todos").remove();
		 * }
		 * @codeend
		 * @param {String} selector selection string
		 * @return {jQuery.fn} returns the matched elements
		 */
		find: function( selector ) {
			return this.element.find(selector);
		},

		// !-- FOUNDRY HACK --! //
		// Quick acccess to views.
		view: function() {

			var args = makeArray(arguments),
				name,
				options = args,
				useHtml = false,
				context = this[STR_CONSTRUCTOR].component || $,
				html = "",
				view = this.options.view || {};

			if (typeof args[0] == "boolean") {
				useHtml = args[0];
				options = args.slice(1);
			}

			name = options[0] = view[options[0]];

			// If view is not assigned, return empty string.
			if (name==undefined) {
				return (useHtml) ? "" : $("");
			}

			html = context.View.apply(context, options);

			return (useHtml) ? html : $($.parseHTML($.trim(html)));
		},

		getPlugin: function(name) {

			return this.pluginInstances[name];
		},

		addSubscriber: function(instance) {

			var instances = ($.isArray(instance)) ? instance : [instance || {}];

			// Prep options
			var host = this,
				hostname = this.Class.hostname,
				options = {};
				options["{" + hostname + "}"] = host;

			$.map(instances, function(instance, i){

				// If this is not a controller instance.
				if (!$.isControllerInstance(instance)) return false;

				// If instance is already a subscriber,skip.
				if (instance.options[hostname]===this) return instance;

				// Also map itself as a method name
				instance[hostname] = host;

				// Attach publisher to subscriber
				return instance.update(options);
			});

			return instances;
		},

		// addPlugin(name, object, [options]);
		// The object should consist of a method called destroy();

		// addPlugin(name, function, [options]);
		// The function should return an object with a method called destroy();

		addPlugin: function(name, plugin, options) {

			if (!name) return;

			// This means we are working with plugin shorthand
			if ((!plugin && !options) || $.isPlainObject(plugin)) {
				options = plugin;
				plugin = [this.Class.root, this.Class.fullName, $.String.capitalize(name)].join(".");
			}

			// If plugin is a string, get the controller from it.
			if ($.isString(plugin)) {
				plugin = $.getController(plugin);
			}

			var isPluginInstance = $.isControllerInstance(plugin);

			// Controller class are also functions,
			// so this simple test is good enough.
			if (!isFunction(plugin) && !isPluginInstance) return;

			// Normalize plugin options
			var pluginOptions =
				this.Class.pluginExtendsInstance ?
					this.options[name] :
					(this.options.plugin || {})[name];

			options = $.extend(true, {element: this.element}, options, pluginOptions);

			// Determine plugin type
			var type =
				((isPluginInstance) ? "instance" :
				(($.isController(plugin)) ? "controller" : "function"));

			// Trigger addPlugin event so controller can decorate the options
			this.trigger("addPlugin", [name, plugin, options, type]);

			var hostname = this.Class.hostname;

			// Subcontrollers should have a way to listen back to host controller
			options["{" + hostname + "}"] = this;

			var pluginInstance;

			switch(type) {

				// Plugin instance
				case "instance":

					pluginInstance = plugin;

					// Update child plugin with custom plugin options from host
					plugin.update(options);

					plugin[hostname] = this;
					break;

				// Plugin controller
				case "controller":
					pluginInstance = options.element.addController(plugin, options);
					break;

				// Plugin function
				case "function":
					pluginInstance = plugin(this, options);
					break;
			}

			// If pluginInstance could not be created, stop.
			if (!pluginInstance) return;

			// Register plugin
			this.pluginInstances[name] = pluginInstance;

			// Also extend instance with a property point to the plugin
			if (this.Class.pluginExtendsInstance) {
				this[name] = pluginInstance;
			}

			// Host controller should also have a way to listen back to the child controller
			if (type!=="function") {

				var hostOptions = {};
				hostOptions["{" + name + "}"] = pluginInstance;

				this.update(hostOptions);
			}

			// Trigger registerPlugin
			this.trigger("registerPlugin", [name, pluginInstance, options, type]);

			return pluginInstance;
		},

		removePlugin: function(name) {

			var plugin = this.getPlugin(name);

			if (!plugin) return;

			// Trigger removePlugin
			this.trigger("removePlugin", [name, plugin]);

			delete this.pluginInstances[name];

			return $.isFunction(plugin.destroy) ? plugin.destroy() : null;
		},

		invokePlugin: function(name, method, args) {

			var plugin = this.getPlugin(name);

			// If plugin not exist, stop.
			if (!plugin) return;

			// If plugin method not exist, stop.
			if (!$.isFunction(plugin[method])) return;

			// Let any third party modify the arguments if required
			this.trigger("invokePlugin", [name, plugin, args]);

			return plugin[method].apply(this, args);
		},

		getMessageGroup: function() {

			// Find parent element
			var messageGroup = ($.isFunction(this.messageGroup)) ? this.messageGroup() : this.element.find("[data-message-group]");

			if (messageGroup.length < 1) {
				messageGroup = $("<div data-message-group></div>").prependTo(this.element);
			}

			return messageGroup;
		},

		setMessage: function(message, type) {

			// Normalize arguments
			var defaultOptions = {
					type   : "warning", // type: info, error, success
					message: "",
					parent : this.getMessageGroup(),
					element: $('<div class="o-alert o-alert--dismissible"><button type="button" class="o-alert__close" data-bs-dismiss="alert">×</button></div>')
				},
				userOptions = {},
				isDeferred = $.isDeferred(message);

			// Normalize user options
			if ($.isPlainObject(message) && !isDeferred) {
				userOptions = message;
			} else {
				userOptions = {
					message: message,
					type   : type || "warning"
				}
			}

			var options = $.extend({}, defaultOptions, userOptions),
				element = options.element;

			if ($.isDeferred(message)) {

				var myself = arguments.callee,
					context = this;

				message.done(function(message, type) {
					options.message = message;
					options.type = type || "warning";
					myself.call(context, options);
					element.show();
				});

			} else {

				element
					.addClass("o-alert--" + options.type)
					.append(options.message);

				if ($('html').has(element).length < 1) {
					element.appendTo(options.parent);
				}
			}

			return element;
		},

		clearMessage: function() {

			this.getMessageGroup().empty();
		},

		//tells callback to set called on this.  I hate this.
		_set_called: true
	});

	var processors = $.Controller.Class.processors,

	//------------- PROCESSSORS -----------------------------
	//processors do the binding.  They return a function that
	//unbinds when called.
	//the basic processor that binds events
	basicProcessor = function( el, event, selector, methodName, controller ) {

		// !-- FOUNDRY HACK --! //
		// Support for passing event data

		var method = controller[methodName],
			eventData;

		if (isArray(method) && isFunction(method[1])) {
			eventData = method[0];
		}

		return binder(el, event, shifter(controller, methodName), selector, eventData);
	};


	//set common events to be processed as a basicProcessor
	each("change click contextmenu dblclick keydown keyup keypress mousedown mousemove mouseout mouseover mouseup reset resize scroll select submit focusin focusout mouseenter mouseleave".split(" "), function( i, v ) {
		processors[v] = basicProcessor;
	});
	/**
	 *  @add jQuery.fn
	 */

	//used to determine if a controller instance is one of controllers
	//controllers can be strings or classes

	var normalizeController = function(controller) {
		return controller.replace("$.Controller", controllerRoot);
	}

	var getController = function(controller) {
		if (isString(controller)) {
			controller = normalizeController(controller);
			controller = getObject(controller) || getObject(controllerRoot + "." + controller);
		};
		if (isController(controller)) {
			return controller;
		};
	}

	var isController = function(controller) {
		return isFunction(controller) && controller.hasOwnProperty("_fullName");
	}

	var flattenControllers = function(controllers) {
		return $.map(controllers, function(controller){
			return (isArray(controller)) ? flattenControllers(controller) : getController(controller);
		});
	};

	$.getController = getController;

	$.isController = function(controller) {
		return !!getController(controller);
	}

	$.isControllerInstance = function(instance) {
		return instance && instance[STR_CONSTRUCTOR] && isController(instance[STR_CONSTRUCTOR]);
	}

	$.isControllerOf = function(instance, controllers) {

		if (!controllers) return false;

		if (!isArray(controllers)) {
			controllers = [controllers];
		}

		for (var i=0; i<controllers.length; i++) {
			var controller = getController(controllers[i]);
			if (instance instanceof controller) return true;
		}

		return false;
	};

	$.fn.extend({
		/**
		 * @function controllers
		 * Gets all controllers in the jQuery element.
		 * @return {Array} an array of controller instances.
		 */
		controllers: function() {

			var candidates = flattenControllers(makeArray(arguments)),
				instances = [];

			this.each(function() {

				var controllers = $.data(this, "controllers");

				each(controllers || {}, function(_fullName, instance){

					if (!controllers.hasOwnProperty(_fullName)) return;

					if (!candidates.length || $.isControllerOf(instance, candidates)) {
						instances.push(instance);
					}
				});
			});

			return instances;
		},

		/**
		 * @function controller
		 * Gets a controller in the jQuery element.  With no arguments, returns the first one found.
		 * @param {Object} controller (optional) if exists, the first controller instance with this class type will be returned.
		 * @return {jQuery.Controller} the first controller.
		 */
		controller: function(controller, options) {

			// Getter
			if (options===undefined) {
				return this.controllers(controller)[0];
			}

			// Setter
			this.addController.apply(this, arguments);
			return this;
		},

		hasController: function(controller) {

			var _fullName =
				(getController(controller) || {})._fullName ||
				(isString(controller) ? underscoreAndRemoveController(normalizeController(controller)) : "");

			return (!_fullName) ? false : (($(this).data("controllers") || {}).hasOwnProperty(_fullName));
		},

		addController: function(controller, options, callback) {

			var Controller = getController(controller);

			if (!Controller) return;

			var instances = [];

			this.each(function(){

				// Do not add controller on script node or non-element nodes.
				if (this.nodeType!==1 || this.nodeName=="SCRIPT") return;

				// Just return existing instance
				var existingInstance = $(this).controller(controller);
				if (existingInstance) {
					instances.push(existingInstance);
					return;
				}

				// Or create a new instance
				var instance = new Controller(this, options);
				isFunction(callback) && callback.apply(instance, [$(this), instance]);
				instances.push(instance);
			});

			return (instances.length > 1) ? instances : instances[0];
		},

		removeController: function(controller) {
			this.each(function(){
				var instances = $(this).controllers(controller);
				while (instances.length) {
					instances.shift().destroy();
				}
			});
			return this;
		},

		addControllerWhenAvailable: function(controller) {

			var elements = this,
				args = arguments,
				task = $.Deferred();

			if ($.isController(controller)) {
				controller = controller.fullName;
			}

			if (!isString(controller)) {
				return task.reject();
			}

			$.module("$:/Controllers/" + controller)
				.pipe(
					function(){
						var instance = elements.addController.apply(elements, args);
						task.resolveWith(instance, [elements, instance]);
					},
					task.reject,
					task.fail
				);

			return task;
		},

		// @deprecated 2.2
		implement: function() {
			this.addController.apply(this, arguments);
			return this;
		}

	});

	// !-- FOUNDRY HACK --! //
	// Add support for augmented selector function on jQuery's DOM traversal/filtering methods.
	(function(){
	var fns = ["is", "find"],
		_fns = {},
		fn;

	while (fn = fns.shift()) {
		_fns[fn] = $.fn[fn];
		$.fn[fn] = (function(fn) {
			return function(obj) {
				return _fns[fn].apply(this, (obj || {}).hasOwnProperty("of") ? [obj.selector] : arguments);
			}
		})(fn);
	}
	})();

})();(function(){

	// a path like string into something that's ok for an element ID
	var toId = function( src ) {
		return src.replace(/^\/\//, "").replace(/[\/\.]/g, "_");
	},
		makeArray = $.makeArray,
		// used for hookup ids
		id = 1;
	// this might be useful for testing if html
	// htmlTest = /^[\s\n\r\xA0]*<(.|[\r\n])*>[\s\n\r\xA0]*$/
	/**
	 * @class jQuery.View
	 * @parent jquerymx
	 * @plugin jquery/view
	 * @test jquery/view/qunit.html
	 * @download dist/jquery.view.js
	 *
	 * @description A JavaScript template framework.
	 *
	 * View provides a uniform interface for using templates with
	 * jQuery. When template engines [jQuery.View.register register]
	 * themselves, you are able to:
	 *
	 *  - Use views with jQuery extensions [jQuery.fn.after after], [jQuery.fn.append append],
	 *   [jQuery.fn.before before], [jQuery.fn.html html], [jQuery.fn.prepend prepend],
	 *   [jQuery.fn.replaceWith replaceWith], [jQuery.fn.text text].
	 *  - Template loading from html elements and external files.
	 *  - Synchronous and asynchronous template loading.
	 *  - [view.deferreds Deferred Rendering].
	 *  - Template caching.
	 *  - Bundling of processed templates in production builds.
	 *  - Hookup jquery plugins directly in the template.
	 *
	 * The [mvc.view Get Started with jQueryMX] has a good walkthrough of $.View.
	 *
	 * ## Use
	 *
	 *
	 * When using views, you're almost always wanting to insert the results
	 * of a rendered template into the page. jQuery.View overwrites the
	 * jQuery modifiers so using a view is as easy as:
	 *
	 *     $("#foo").html('mytemplate.ejs',{message: 'hello world'})
	 *
	 * This code:
	 *
	 *  - Loads the template a 'mytemplate.ejs'. It might look like:
	 *    <pre><code>&lt;h2>&lt;%= message %>&lt;/h2></pre></code>
	 *
	 *  - Renders it with {message: 'hello world'}, resulting in:
	 *    <pre><code>&lt;div id='foo'>"&lt;h2>hello world&lt;/h2>&lt;/div></pre></code>
	 *
	 *  - Inserts the result into the foo element. Foo might look like:
	 *    <pre><code>&lt;div id='foo'>&lt;h2>hello world&lt;/h2>&lt;/div></pre></code>
	 *
	 * ## jQuery Modifiers
	 *
	 * You can use a template with the following jQuery modifiers:
	 *
	 * <table>
	 * <tr><td>[jQuery.fn.after after]</td><td> <code>$('#bar').after('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.append append] </td><td>  <code>$('#bar').append('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.before before] </td><td> <code>$('#bar').before('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.html html] </td><td> <code>$('#bar').html('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.prepend prepend] </td><td> <code>$('#bar').prepend('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.replaceWith replaceWith] </td><td> <code>$('#bar').replaceWith('temp.jaml',{});</code></td></tr>
	 * <tr><td>[jQuery.fn.text text] </td><td> <code>$('#bar').text('temp.jaml',{});</code></td></tr>
	 * </table>
	 *
	 * You always have to pass a string and an object (or function) for the jQuery modifier
	 * to user a template.
	 *
	 * ## Template Locations
	 *
	 * View can load from script tags or from files.
	 *
	 * ## From Script Tags
	 *
	 * To load from a script tag, create a script tag with your template and an id like:
	 *
	 * <pre><code>&lt;script type='text/ejs' id='recipes'>
	 * &lt;% for(var i=0; i &lt; recipes.length; i++){ %>
	 *   &lt;li>&lt;%=recipes[i].name %>&lt;/li>
	 * &lt;%} %>
	 * &lt;/script></code></pre>
	 *
	 * Render with this template like:
	 *
	 * @codestart
	 * $("#foo").html('recipes',recipeData)
	 * @codeend
	 *
	 * Notice we passed the id of the element we want to render.
	 *
	 * ## From File
	 *
	 * You can pass the path of a template file location like:
	 *
	 *     $("#foo").html('templates/recipes.ejs',recipeData)
	 *
	 * However, you typically want to make the template work from whatever page they
	 * are called from.  To do this, use // to look up templates from JMVC root:
	 *
	 *     $("#foo").html('//app/views/recipes.ejs',recipeData)
	 *
	 * Finally, the [jQuery.Controller.prototype.view controller/view] plugin can make looking
	 * up a thread (and adding helpers) even easier:
	 *
	 *     $("#foo").html( this.view('recipes', recipeData) )
	 *
	 * ## Packaging Templates
	 *
	 * If you're making heavy use of templates, you want to organize
	 * them in files so they can be reused between pages and applications.
	 *
	 * But, this organization would come at a high price
	 * if the browser has to
	 * retrieve each template individually. The additional
	 * HTTP requests would slow down your app.
	 *
	 * Fortunately, [steal.static.views steal.views] can build templates
	 * into your production files. You just have to point to the view file like:
	 *
	 *     steal.views('path/to/the/view.ejs');
	 *
	 * ## Asynchronous
	 *
	 * By default, retrieving requests is done synchronously. This is
	 * fine because StealJS packages view templates with your JS download.
	 *
	 * However, some people might not be using StealJS or want to delay loading
	 * templates until necessary. If you have the need, you can
	 * provide a callback paramter like:
	 *
	 *     $("#foo").html('recipes',recipeData, function(result){
	 *       this.fadeIn()
	 *     });
	 *
	 * The callback function will be called with the result of the
	 * rendered template and 'this' will be set to the original jQuery object.
	 *
	 * ## Deferreds (3.0.6)
	 *
	 * If you pass deferreds to $.View or any of the jQuery
	 * modifiers, the view will wait until all deferreds resolve before
	 * rendering the view.  This makes it a one-liner to make a request and
	 * use the result to render a template.
	 *
	 * The following makes a request for todos in parallel with the
	 * todos.ejs template.  Once todos and template have been loaded, it with
	 * render the view with the todos.
	 *
	 *     $('#todos').html("todos.ejs",Todo.findAll());
	 *
	 * ## Just Render Templates
	 *
	 * Sometimes, you just want to get the result of a rendered
	 * template without inserting it, you can do this with $.View:
	 *
	 *     var out = $.View('path/to/template.jaml',{});
	 *
	 * ## Preloading Templates
	 *
	 * You can preload templates asynchronously like:
	 *
	 *     $.get('path/to/template.jaml',{},function(){},'view');
	 *
	 * ## Supported Template Engines
	 *
	 * JavaScriptMVC comes with the following template languages:
	 *
	 *   - EmbeddedJS
	 *     <pre><code>&lt;h2>&lt;%= message %>&lt;/h2></code></pre>
	 *
	 *   - JAML
	 *     <pre><code>h2(data.message);</code></pre>
	 *
	 *   - Micro
	 *     <pre><code>&lt;h2>{%= message %}&lt;/h2></code></pre>
	 *
	 *   - jQuery.Tmpl
	 *     <pre><code>&lt;h2>${message}&lt;/h2></code></pre>

	 *
	 * The popular <a href='http://awardwinningfjords.com/2010/08/09/mustache-for-javascriptmvc-3.html'>Mustache</a>
	 * template engine is supported in a 2nd party plugin.
	 *
	 * ## Using other Template Engines
	 *
	 * It's easy to integrate your favorite template into $.View and Steal.  Read
	 * how in [jQuery.View.register].
	 *
	 * @constructor
	 *
	 * Looks up a template, processes it, caches it, then renders the template
	 * with data and optional helpers.
	 *
	 * With [stealjs StealJS], views are typically bundled in the production build.
	 * This makes it ok to use views synchronously like:
	 *
	 * @codestart
	 * $.View("//myplugin/views/init.ejs",{message: "Hello World"})
	 * @codeend
	 *
	 * If you aren't using StealJS, it's best to use views asynchronously like:
	 *
	 * @codestart
	 * $.View("//myplugin/views/init.ejs",
	 *        {message: "Hello World"}, function(result){
	 *   // do something with result
	 * })
	 * @codeend
	 *
	 * @param {String} view The url or id of an element to use as the template's source.
	 * @param {Object} data The data to be passed to the view.
	 * @param {Object} [helpers] Optional helper functions the view might use. Not all
	 * templates support helpers.
	 * @param {Object} [callback] Optional callback function.  If present, the template is
	 * retrieved asynchronously.  This is a good idea if you aren't compressing the templates
	 * into your view.
	 * @return {String} The rendered result of the view or if deferreds
	 * are passed, a deferred that will resolve to
	 * the rendered result of the view.
	 */
	var $view = $.View = function( view, data, helpers, callback ) {
		// if helpers is a function, it is actually a callback
		if ( typeof helpers === 'function' ) {
			callback = helpers;
			helpers = undefined;
		}

		// see if we got passed any deferreds
		var deferreds = getDeferreds(data);


		if ( deferreds.length ) { // does data contain any deferreds?
			// the deferred that resolves into the rendered content ...
			var deferred = $.Deferred();

			// add the view request to the list of deferreds
			deferreds.push(get(view, true))

			// wait for the view and all deferreds to finish
			$.when.apply($, deferreds).then(function( resolved ) {
				// get all the resolved deferreds
				var objs = makeArray(arguments),
					// renderer is last [0] is the data
					renderer = objs.pop()[0],
					// the result of the template rendering with data
					result;

				// make data look like the resolved deferreds
				if ( isDeferred(data) ) {
					data = usefulPart(resolved);
				}
				else {
					// go through each prop in data again,
					// replace the defferreds with what they resolved to
					for ( var prop in data ) {
						if ( isDeferred(data[prop]) ) {
							data[prop] = usefulPart(objs.shift());
						}
					}
				}
				// get the rendered result
				result = renderer(data, helpers);

				//resolve with the rendered view
				deferred.resolve(result);
				// if there's a callback, call it back with the result
				callback && callback(result);
			});
			// return the deferred ....
			return deferred.promise();
		}
		else {
			// no deferreds, render this bad boy
			var response,
				// if there's a callback function
				async = typeof callback === "function",
				// get the 'view' type
				deferred = get(view, async);

			// if we are async,
			if ( async ) {
				// return the deferred
				response = deferred;
				// and callback callback with the rendered result
				deferred.done(function( renderer ) {
					callback(renderer(data, helpers))
				})
			} else {
				// otherwise, the deferred is complete, so
				// set response to the result of the rendering
				deferred.done(function( renderer ) {
					response = renderer(data, helpers);
				});
			}

			return response;
		}
	},
		// makes sure there's a template, if not, has steal provide a warning
		checkText = function( text, url ) {
			if (!text.match(/[^\s]/) ) {
				
				throw "$.View ERROR: There is no template or an empty template at " + url;
			}
		},
		// returns a 'view' renderer deferred
		// url - the url to the view template
		// async - if the ajax request should be synchronous
		get = function( url, async ) {
			return $.ajax({
				url: url,
				dataType: "view",
				async: async
			});
		},
		// returns true if something looks like a deferred
		isDeferred = function( obj ) {
			return obj && $.isFunction(obj.always) // check if obj is a $.Deferred
		},
		// gets an array of deferreds from an object
		// this only goes one level deep
		getDeferreds = function( data ) {
			var deferreds = [];

			// pull out deferreds
			if ( isDeferred(data) ) {
				return [data]
			} else {
				for ( var prop in data ) {
					if ( isDeferred(data[prop]) ) {
						deferreds.push(data[prop]);
					}
				}
			}
			return deferreds;
		},
		// gets the useful part of deferred
		// this is for Models and $.ajax that resolve to array (with success and such)
		// returns the useful, content part
		usefulPart = function( resolved ) {
			return $.isArray(resolved) && resolved.length === 3 && resolved[1] === 'success' ? resolved[0] : resolved
		};



	// you can request a view renderer (a function you pass data to and get html)
	// Creates a 'view' transport.  These resolve to a 'view' renderer
	// a 'view' renderer takes data and returns a string result.
	// For example:
	//
	//  $.ajax({dataType : 'view', src: 'foo.ejs'}).then(function(renderer){
	//     renderer({message: 'hello world'})
	//  })
	$.ajaxTransport("view", function( options, orig ) {
		// the url (or possibly id) of the view content
		var url = orig.url,
			// check if a suffix exists (ex: "foo.ejs")
			suffix = url.match(/\.[\w\d]+$/),
			type,
			// if we are reading a script element for the content of the template
			// el will be set to that script element
			el,
			// a unique identifier for the view (used for caching)
			// this is typically derived from the element id or
			// the url for the template
			id,
			// the AJAX request used to retrieve the template content
			jqXHR,

			// used to generate the response
			response = function( text ) {
				// get the renderer function
				var func = type.renderer(id, text);
				// cache if if we are caching
				if ( $view.cache ) {
					$view.cached[id] = func;
				}
				// return the objects for the response's dataTypes
				// (in this case view)
				return {
					view: func
				};
			};

		// if we have an inline template, derive the suffix from the 'text/???' part
		// this only supports '<script></script>' tags
		if ( el = document.getElementById(url) ) {
			suffix = "."+el.type.match(/\/(x\-)?(.+)/)[2];
		}

		// if there is no suffix, add one
		if (!suffix ) {
			suffix = $view.ext;
			url = url + $view.ext;
		}

		// convert to a unique and valid id
		id = toId(url);

		// if a absolute path, use steal to get it
		// you should only be using // if you are using steal
		if ( url.match(/^\/\//) ) {
			var sub = url.substr(2);
			url = typeof steal === "undefined" ?
				url = "/" + sub :
				steal.root.mapJoin(sub) +'';
		}

		//set the template engine type
		type = $view.types[suffix];

		// !-- FOUNDRY HACK --! //
		// Retrieve templates stored within $.template
		var template = $.template()[orig.url];

		// return the ajax transport contract: http://api.jquery.com/extending-ajax/
		return {
			send: function( headers, callback ) {

				// !-- FOUNDRY HACK --! //
				// Retrieve templates stored within $.template
				if ( template ) {

					type = $view.types["." + template.type];

					return callback(200, "success", response(template.content));

				// if it is cached,
				} else if ( $view.cached[id] ) {

					// return the catched renderer
					return callback(200, "success", {
						view: $view.cached[id]
					});

				// otherwise if we are getting this from a script elment
				} else if ( el ) {
					// resolve immediately with the element's innerHTML
					callback(200, "success", response(el.innerHTML));
				} else {
					// make an ajax request for text
					jqXHR = $.ajax({
						async: orig.async,
						url: url,
						dataType: "text",
						error: function() {
							checkText("", url);
							callback(404);
						},
						success: function( text ) {
							// make sure we got some text back
							checkText(text, url);
							// cache and send back text
							callback(200, "success", response(text))
						}
					});
				}
			},
			abort: function() {
				jqXHR && jqXHR.abort();
			}
		}
	})
	$.extend($view, {
		/**
		 * @attribute hookups
		 * @hide
		 * A list of pending 'hookups'
		 */
		hookups: {},
		/**
		 * @function hookup
		 * Registers a hookup function that can be called back after the html is
		 * put on the page.  Typically this is handled by the template engine.  Currently
		 * only EJS supports this functionality.
		 *
		 *     var id = $.View.hookup(function(el){
		 *            //do something with el
		 *         }),
		 *         html = "<div data-view-id='"+id+"'>"
		 *     $('.foo').html(html);
		 *
		 *
		 * @param {Function} cb a callback function to be called with the element
		 * @param {Number} the hookup number
		 */
		hookup: function( cb ) {
			var myid = ++id;
			$view.hookups[myid] = cb;
			return myid;
		},
		/**
		 * @attribute cached
		 * @hide
		 * Cached are put in this object
		 */
		cached: {},
		/**
		 * @attribute cache
		 * Should the views be cached or reloaded from the server. Defaults to true.
		 */
		cache: true,
		/**
		 * @function register
		 * Registers a template engine to be used with
		 * view helpers and compression.
		 *
		 * ## Example
		 *
		 * @codestart
		 * $.View.register({
		 *  suffix : "tmpl",
		 *  plugin : "jquery/view/tmpl",
		 *  renderer: function( id, text ) {
		 *      return function(data){
		 *          return jQuery.render( text, data );
		 *      }
		 *  },
		 *  script: function( id, text ) {
		 *      var tmpl = $.tmpl(text).toString();
		 *      return "function(data){return ("+
		 *          tmpl+
		 *          ").call(jQuery, jQuery, data); }";
		 *  }
		 * })
		 * @codeend
		 * Here's what each property does:
		 *
		 *    * plugin - the location of the plugin
		 *    * suffix - files that use this suffix will be processed by this template engine
		 *    * renderer - returns a function that will render the template provided by text
		 *    * script - returns a string form of the processed template function.
		 *
		 * @param {Object} info a object of method and properties
		 *
		 * that enable template integration:
		 * <ul>
		 *   <li>plugin - the location of the plugin.  EX: 'jquery/view/ejs'</li>
		 *   <li>suffix - the view extension.  EX: 'ejs'</li>
		 *   <li>script(id, src) - a function that returns a string that when evaluated returns a function that can be
		 *    used as the render (i.e. have func.call(data, data, helpers) called on it).</li>
		 *   <li>renderer(id, text) - a function that takes the id of the template and the text of the template and
		 *    returns a render function.</li>
		 * </ul>
		 */
		register: function( info ) {
			this.types["." + info.suffix] = info;

			if ( window.steal ) {
				steal.type(info.suffix + " view js", function( options, success, error ) {
					var type = $view.types["." + options.type],
						id = toId(options.rootSrc+'');

					options.text = type.script(id, options.text)
					success();
				})
			}
		},
		types: {},
		/**
		 * @attribute ext
		 * The default suffix to use if none is provided in the view's url.
		 * This is set to .ejs by default.
		 */
		ext: ".ejs",
		/**
		 * Returns the text that
		 * @hide
		 * @param {Object} type
		 * @param {Object} id
		 * @param {Object} src
		 */
		registerScript: function( type, id, src ) {
			return "$.View.preload('" + id + "'," + $view.types["." + type].script(id, src) + ");";
		},
		/**
		 * @hide
		 * Called by a production script to pre-load a renderer function
		 * into the view cache.
		 * @param {String} id
		 * @param {Function} renderer
		 */
		preload: function( id, renderer ) {
			$view.cached[id] = function( data, helpers ) {
				return renderer.call(data, data, helpers);
			};
		}

	});
	if ( window.steal ) {
		steal.type("view js", function( options, success, error ) {
			var type = $view.types["." + options.type],
				id = toId(options.rootSrc+'');

			options.text = "steal('" + (type.plugin || "jquery/view/" + options.type) + "').then(function($){" + "$.View.preload('" + id + "'," + options.text + ");\n})";
			success();
		})
	}

	//---- ADD jQUERY HELPERS -----
	//converts jquery functions to use views
	var convert, modify, isTemplate, isHTML, isDOM, getCallback, hookupView, funcs,
		// text and val cannot produce an element, so don't run hookups on them
		noHookup = {'val':true,'text':true};

	convert = function( func_name ) {
		// save the old jQuery helper
		var old = $.fn[func_name];

		// replace it wiht our new helper
		$.fn[func_name] = function() {

			var args = makeArray(arguments),
				callbackNum,
				callback,
				self = this,
				result;

			// if the first arg is a deferred
			// wait until it finishes, and call
			// modify with the result
			if ( isDeferred(args[0]) ) {
				args[0].done(function( res ) {
					modify.call(self, [res], old);
				})
				return this;
			}
			//check if a template
			else if ( isTemplate(args) ) {

				// if we should operate async
				if ((callbackNum = getCallback(args))) {
					callback = args[callbackNum];
					args[callbackNum] = function( result ) {
						modify.call(self, [result], old);
						callback.call(self, result);
					};
					$view.apply($view, args);
					return this;
				}
				// call view with args (there might be deferreds)
				result = $view.apply($view, args);

				// if we got a string back
				if (!isDeferred(result) ) {
					// we are going to call the old method with that string
					args = [result];
				} else {
					// if there is a deferred, wait until it is done before calling modify
					result.done(function( res ) {
						modify.call(self, [res], old);
					})
					return this;
				}
			}
			return noHookup[func_name] ? old.apply(this,args) :
				modify.call(this, args, old);
		};
	};

	// modifies the content of the element
	// but also will run any hookup
	modify = function( args, old ) {
		var res, stub, hooks;

		//check if there are new hookups
		for ( var hasHookups in $view.hookups ) {
			break;
		}

		//if there are hookups, get jQuery object
		if ( hasHookups && args[0] && isHTML(args[0]) ) {
			hooks = $view.hookups;
			$view.hookups = {};
			args[0] = $(args[0]);
		}
		res = old.apply(this, args);

		//now hookup the hookups
		if ( hooks
		/* && args.length*/
		) {
			hookupView(args[0], hooks);
		}
		return res;
	};

	// returns true or false if the args indicate a template is being used
	// $('#foo').html('/path/to/template.ejs',{data})
	// in general, we want to make sure the first arg is a string
	// and the second arg is data
	isTemplate = function( args ) {
		// save the second arg type
		var secArgType = typeof args[1];

		// the first arg is a string
		return typeof args[0] == "string" &&
				// the second arg is an object or function
			   (secArgType == 'object' || secArgType == 'function') &&
			   // but it is not a dom element
			   !isDOM(args[1]);
	};
	// returns true if the arg is a jQuery object or HTMLElement
	isDOM = function(arg){
		return arg.nodeType || arg.jquery
	};
	// returns whether the argument is some sort of HTML data
	isHTML = function( arg ) {
		if ( isDOM(arg) ) {
			// if jQuery object or DOM node we're good
			return true;
		} else if ( typeof arg === "string" ) {
			// if string, do a quick sanity check that we're HTML
			arg = $.trim(arg);
			return arg.substr(0, 1) === "<" && arg.substr(arg.length - 1, 1) === ">" && arg.length >= 3;
		} else {
			// don't know what you are
			return false;
		}
	};

	//returns the callback arg number if there is one (for async view use)
	getCallback = function( args ) {
		return typeof args[3] === 'function' ? 3 : typeof args[2] === 'function' && 2;
	};

	hookupView = function( els, hooks ) {
		//remove all hookups
		var hookupEls, len, i = 0,
			id, func;
		els = els.filter(function() {
			return this.nodeType != 3; //filter out text nodes
		})
		hookupEls = els.add("[data-view-id]", els);
		len = hookupEls.length;
		for (; i < len; i++ ) {
			if ( hookupEls[i].getAttribute && (id = hookupEls[i].getAttribute('data-view-id')) && (func = hooks[id]) ) {
				func(hookupEls[i], id);
				delete hooks[id];
				hookupEls[i].removeAttribute('data-view-id');
			}
		}
		//copy remaining hooks back
		$.extend($view.hookups, hooks);
	};

	/**
	 *  @add jQuery.fn
	 *  @parent jQuery.View
	 *  Called on a jQuery collection that was rendered with $.View with pending hookups.  $.View can render a
	 *  template with hookups, but not actually perform the hookup, because it returns a string without actual DOM
	 *  elements to hook up to.  So hookup performs the hookup and clears the pending hookups, preventing errors in
	 *  future templates.
	 *
	 * @codestart
	 * $($.View('//views/recipes.ejs',recipeData)).hookup()
	 * @codeend
	 */
	$.fn.hookup = function() {
		var hooks = $view.hookups;
		$view.hookups = {};
		hookupView(this, hooks);
		return this;
	};

	/**
	 *  @add jQuery.fn
	 */
	$.each([
	/**
	 *  @function prepend
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/prepend/ jQuery().prepend()]
	 *  to render [jQuery.View] templates inserted at the beginning of each element in the set of matched elements.
	 *
	 *      $('#test').prepend('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"prepend",
	/**
	 *  @function append
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/append/ jQuery().append()]
	 *  to render [jQuery.View] templates inserted at the end of each element in the set of matched elements.
	 *
	 *      $('#test').append('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"append",
	/**
	 *  @function after
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/after/ jQuery().after()]
	 *  to render [jQuery.View] templates inserted after each element in the set of matched elements.
	 *
	 *      $('#test').after('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"after",
	/**
	 *  @function before
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/before/ jQuery().before()]
	 *  to render [jQuery.View] templates inserted before each element in the set of matched elements.
	 *
	 *      $('#test').before('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"before",
	/**
	 *  @function text
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/text/ jQuery().text()]
	 *  to render [jQuery.View] templates as the content of each matched element.
	 *  Unlike [jQuery.fn.html] jQuery.fn.text also works with XML, escaping the provided
	 *  string as necessary.
	 *
	 *      $('#test').text('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"text",
	/**
	 *  @function html
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/html/ jQuery().html()]
	 *  to render [jQuery.View] templates as the content of each matched element.
	 *
	 *      $('#test').html('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"html",
	/**
	 *  @function replaceWith
	 *  @parent jQuery.View
	 *
	 *  Extending the original [http://api.jquery.com/replaceWith/ jQuery().replaceWith()]
	 *  to render [jQuery.View] templates replacing each element in the set of matched elements.
	 *
	 *      $('#test').replaceWith('path/to/template.ejs', { name : 'javascriptmvc' });
	 *
	 *  @param {String|Object|Function} content A template filename or the id of a view script tag
	 *  or a DOM element, array of elements, HTML string, or jQuery object.
	 *  @param {Object} [data] The data to render the view with.
	 *  If rendering a view template this parameter always has to be present
	 *  (use the empty object initializer {} for no data).
	 */
	"replaceWith", "val"],function(i, func){
		convert(func);
	});

	//go through helper funcs and convert


})();(function(){

	// HELPER METHODS ==============
	var myEval = function( script ) {
		eval(script);
	},
		// removes the last character from a string
		// this is no longer needed
		// chop = function( string ) {
		//  return string.substr(0, string.length - 1);
		//},
		rSplit = $.String.rsplit,
		extend = $.extend,
		isArray = $.isArray,
		// regular expressions for caching
		returnReg = /\r\n/g,
		retReg = /\r/g,
		newReg = /\n/g,
		nReg = /\n/,
		slashReg = /\\/g,
		quoteReg = /"/g,
		singleQuoteReg = /'/g,
		tabReg = /\t/g,
		leftBracket = /\{/g,
		rightBracket = /\}/g,
		quickFunc = /\s*\(([\$\w]+)\)\s*->([^\n]*)/,
		// escapes characters starting with \
		clean = function( content ) {
			return content.replace(slashReg, '\\\\').replace(newReg, '\\n').replace(quoteReg, '\\"').replace(tabReg, '\\t');
		},
		// escapes html
		// - from prototype  http://www.prototypejs.org/
		escapeHTML = function( content ) {
			return content.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(quoteReg, '&#34;').replace(singleQuoteReg, "&#39;");
		},
		$View = $.View,
		bracketNum = function(content){
			var lefts = content.match(leftBracket),
				rights = content.match(rightBracket);

			return (lefts ? lefts.length : 0) -
				   (rights ? rights.length : 0);
		},
		/**
		 * @class jQuery.EJS
		 *
		 * @plugin jquery/view/ejs
		 * @parent jQuery.View
		 * @download  http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/view/ejs/ejs.js
		 * @test jquery/view/ejs/qunit.html
		 *
		 *
		 * Ejs provides <a href="http://www.ruby-doc.org/stdlib/libdoc/erb/rdoc/">ERB</a>
		 * style client side templates.  Use them with controllers to easily build html and inject
		 * it into the DOM.
		 *
		 * ###  Example
		 *
		 * The following generates a list of tasks:
		 *
		 * @codestart html
		 * &lt;ul>
		 * &lt;% for(var i = 0; i < tasks.length; i++){ %>
		 *     &lt;li class="task &lt;%= tasks[i].identity %>">&lt;%= tasks[i].name %>&lt;/li>
		 * &lt;% } %>
		 * &lt;/ul>
		 * @codeend
		 *
		 * For the following examples, we assume this view is in <i>'views\tasks\list.ejs'</i>.
		 *
		 *
		 * ## Use
		 *
		 * ### Loading and Rendering EJS:
		 *
		 * You should use EJS through the helper functions [jQuery.View] provides such as:
		 *
		 *   - [jQuery.fn.after after]
		 *   - [jQuery.fn.append append]
		 *   - [jQuery.fn.before before]
		 *   - [jQuery.fn.html html],
		 *   - [jQuery.fn.prepend prepend],
		 *   - [jQuery.fn.replaceWith replaceWith], and
		 *   - [jQuery.fn.text text].
		 *
		 * or [jQuery.Controller.prototype.view].
		 *
		 * ### Syntax
		 *
		 * EJS uses 5 types of tags:
		 *
		 *   - <code>&lt;% CODE %&gt;</code> - Runs JS Code.
		 *     For example:
		 *
		 *         <% alert('hello world') %>
		 *
		 *   - <code>&lt;%= CODE %&gt;</code> - Runs JS Code and writes the _escaped_ result into the result of the template.
		 *     For example:
		 *
		 *         <h1><%= 'hello world' %></h1>
		 *
		 *   - <code>&lt;%== CODE %&gt;</code> - Runs JS Code and writes the _unescaped_ result into the result of the template.
		 *     For example:
		 *
		 *         <h1><%== '<span>hello world</span>' %></h1>
		 *
		 *   - <code>&lt;%%= CODE %&gt;</code> - Writes <%= CODE %> to the result of the template.  This is very useful for generators.
		 *
		 *         <%%= 'hello world' %>
		 *
		 *   - <code>&lt;%# CODE %&gt;</code> - Used for comments.  This does nothing.
		 *
		 *         <%# 'hello world' %>
		 *
		 * ## Hooking up controllers
		 *
		 * After drawing some html, you often want to add other widgets and plugins inside that html.
		 * View makes this easy.  You just have to return the Contoller class you want to be hooked up.
		 *
		 * @codestart
		 * &lt;ul &lt;%= Mxui.Tabs%>>...&lt;ul>
		 * @codeend
		 *
		 * You can even hook up multiple controllers:
		 *
		 * @codestart
		 * &lt;ul &lt;%= [Mxui.Tabs, Mxui.Filler]%>>...&lt;ul>
		 * @codeend
		 *
		 * To hook up a controller with options or any other jQuery plugin use the
		 * [jQuery.EJS.Helpers.prototype.plugin | plugin view helper]:
		 *
		 * @codestart
		 * &lt;ul &lt;%= plugin('mxui_tabs', { option: 'value' }) %>>...&lt;ul>
		 * @codeend
		 *
		 * Don't add a semicolon when using view helpers.
		 *
		 *
		 * <h2>View Helpers</h2>
		 * View Helpers return html code.  View by default only comes with
		 * [jQuery.EJS.Helpers.prototype.view view] and [jQuery.EJS.Helpers.prototype.text text].
		 * You can include more with the view/helpers plugin.  But, you can easily make your own!
		 * Learn how in the [jQuery.EJS.Helpers Helpers] page.
		 *
		 * @constructor Creates a new view
		 * @param {Object} options A hash with the following options
		 * <table class="options">
		 *     <tbody><tr><th>Option</th><th>Default</th><th>Description</th></tr>
		 *     <tr>
		 *      <td>text</td>
		 *      <td>&nbsp;</td>
		 *      <td>uses the provided text as the template. Example:<br/><code>new View({text: '&lt;%=user%>'})</code>
		 *      </td>
		 *     </tr>
		 *     <tr>
		 *      <td>type</td>
		 *      <td>'<'</td>
		 *      <td>type of magic tags.  Options are '&lt;' or '['
		 *      </td>
		 *     </tr>
		 *     <tr>
		 *      <td>name</td>
		 *      <td>the element ID or url </td>
		 *      <td>an optional name that is used for caching.
		 *      </td>
		 *     </tr>
		 *    </tbody></table>
		 */
		EJS = function( options ) {
			// If called without new, return a function that
			// renders with data and helpers like
			// EJS({text: '<%= message %>'})({message: 'foo'});
			// this is useful for steal's build system
			if ( this.constructor != EJS ) {
				var ejs = new EJS(options);
				return function( data, helpers ) {
					return ejs.render(data, helpers);
				};
			}
			// if we get a function directly, it probably is coming from
			// a steal-packaged view
			if ( typeof options == "function" ) {
				this.template = {
					fn: options
				};
				return;
			}
			//set options on self
			extend(this, EJS.options, options);
			this.template = compile(this.text, this.type, this.name);
		};
	// add EJS to jQuery if it exists
	$ && ($.EJS = EJS);
	/**
	 * @Prototype
	 */
	EJS.prototype.
	/**
	 * Renders an object with view helpers attached to the view.
	 *
	 *     new EJS({text: "<%= message %>"}).render({
	 *       message: "foo"
	 *     },{helper: function(){ ... }})
	 *
	 * @param {Object} object data to be rendered
	 * @param {Object} [extraHelpers] an object with view helpers
	 * @return {String} returns the result of the string
	 */
	render = function( object, extraHelpers ) {
		object = object || {};
		this._extra_helpers = extraHelpers;
		var v = new EJS.Helpers(object, extraHelpers || {});
		return this.template.fn.call(object, object, v);
	};
	/**
	 * @Static
	 */

	extend(EJS, {
		/**
		 * Used to convert what's in &lt;%= %> magic tags to a string
		 * to be inserted in the rendered output.
		 *
		 * Typically, it's a string, and the string is just inserted.  However,
		 * if it's a function or an object with a hookup method, it can potentially be
		 * be ran on the element after it's inserted into the page.
		 *
		 * This is a very nice way of adding functionality through the view.
		 * Usually this is done with [jQuery.EJS.Helpers.prototype.plugin]
		 * but the following fades in the div element after it has been inserted:
		 *
		 * @codestart
		 * &lt;%= function(el){$(el).fadeIn()} %>
		 * @codeend
		 *
		 * @param {String|Object|Function} input the value in between the
		 * write magic tags: &lt;%= %>
		 * @return {String} returns the content to be added to the rendered
		 * output.  The content is different depending on the type:
		 *
		 *   * string - the original string
		 *   * null or undefined - the empty string ""
		 *   * an object with a hookup method - the attribute "data-view-id='XX'", where XX is a hookup number for jQuery.View
		 *   * a function - the attribute "data-view-id='XX'", where XX is a hookup number for jQuery.View
		 *   * an array - the attribute "data-view-id='XX'", where XX is a hookup number for jQuery.View
		 */
		text: function( input ) {
			// if it's a string, return
			if ( typeof input == 'string' ) {
				return input;
			}
			// if has no value
			if ( input === null || input === undefined ) {
				return '';
			}

			// if it's an object, and it has a hookup method
			var hook = (input.hookup &&
			// make a function call the hookup method

			function( el, id ) {
				input.hookup.call(input, el, id);
			}) ||
			// or if it's a function, just use the input
			(typeof input == 'function' && input) ||
			// of it its an array, make a function that calls hookup or the function
			// on each item in the array
			(isArray(input) &&
			function( el, id ) {
				for ( var i = 0; i < input.length; i++ ) {
					input[i].hookup ? input[i].hookup(el, id) : input[i](el, id);
				}
			});
			// finally, if there is a funciton to hookup on some dom
			// pass it to hookup to get the data-view-id back
			if ( hook ) {
				return "data-view-id='" + $View.hookup(hook) + "'";
			}
			// finally, if all else false, toString it
			return input.toString ? input.toString() : "";
		},
		/**
		 * Escapes the text provided as html if it's a string.
		 * Otherwise, the value is passed to EJS.text(text).
		 *
		 * @param {String|Object|Array|Function} text to escape.  Otherwise,
		 * the result of [jQuery.EJS.text] is returned.
		 * @return {String} the escaped text or likely a $.View data-view-id attribute.
		 */
		clean: function( text ) {
			//return sanatized text
			if ( typeof text == 'string' ) {
				return escapeHTML(text);
			} else if ( typeof text == 'number' ) {
				return text;
			} else {
				return EJS.text(text);
			}
		},
		/**
		 * @attribute options
		 * Sets default options for all views.
		 *
		 *     $.EJS.options.type = '['
		 *
		 * Only one option is currently supported: type.
		 *
		 * Type is the left hand magic tag.
		 */
		options: {
			type: '[',
			ext: '.ejs'
		}
	});
	// ========= SCANNING CODE =========
	// Given a scanner, and source content, calls block  with each token
	// scanner - an object of magicTagName : values
	// source - the source you want to scan
	// block - function(token, scanner), called with each token
	var scan = function( scanner, source, block ) {
		// split on /\n/ to have new lines on their own line.
		var source_split = rSplit(source, nReg),
			i = 0;
		for (; i < source_split.length; i++ ) {
			scanline(scanner, source_split[i], block);
		}

	},
		scanline = function( scanner, line, block ) {
			scanner.lines++;
			var line_split = rSplit(line, scanner.splitter),
				token;
			for ( var i = 0; i < line_split.length; i++ ) {
				token = line_split[i];
				if ( token !== null ) {
					block(token, scanner);
				}
			}
		},
		// creates a 'scanner' object.  This creates
		// values for the left and right magic tags
		// it's splitter property is a regexp that splits content
		// by all tags
		makeScanner = function( left, right ) {
			var scanner = {};
			extend(scanner, {
				left: left + '%',
				right: '%' + right,
				dLeft: left + '%%',
				dRight: '%%' + right,
				eeLeft : left + '%==',
				eLeft: left + '%=',
				cmnt: left + '%#',
				cleanLeft: left+"%~",
				scan: scan,
				lines: 0
			});
			scanner.splitter = new RegExp("(" + [scanner.dLeft, scanner.dRight, scanner.eeLeft, scanner.eLeft, scanner.cmnt, scanner.left, scanner.right + '\n', scanner.right, '\n'].join(")|(").
			replace(/\[/g, "\\[").replace(/\]/g, "\\]") + ")");
			return scanner;
		},


		// compiles a template where
		// source - template text
		// left - the left magic tag
		// name - the name of the template (for debugging)
		// returns an object like: {out : "", fn : function(){ ... }} where
		//   out -  the converted JS source of the view
		//   fn - a function made from the JS source
		compile = function( source, left, name ) {
			// make everything only use \n
			source = source.replace(returnReg, "\n").replace(retReg, "\n");
			// if no left is given, assume <
			left = left || '[';

			// put and insert cmds are used for adding content to the template
			// currently they are identical, I am not sure why
			var put_cmd = "___v1ew.push(",
				insert_cmd = put_cmd,
				// the text that starts the view code (or block function)
				startTxt = 'var ___v1ew = [];',
				// the text that ends the view code (or block function)
				finishTxt = "return ___v1ew.join('')",
				// initialize a buffer
				buff = new EJS.Buffer([startTxt], []),
				// content is used as the current 'processing' string
				// this is the content between magic tags
				content = '',
				// adds something to be inserted into the view template
				// this comes out looking like __v1ew.push("CONENT")
				put = function( content ) {
					buff.push(put_cmd, '"', clean(content), '");');
				},
				// the starting magic tag
				startTag = null,
				// cleans the running content
				empty = function() {
					content = ''
				},
				// what comes after clean or text
				doubleParen = "));",
				// a stack used to keep track of how we should end a bracket }
				// once we have a <%= %> with a leftBracket
				// we store how the file should end here (either '))' or ';' )
				endStack =[];

			// start going token to token
			scan(makeScanner(left, left === '[' ? ']' : '>'), source || "", function( token, scanner ) {
				// if we don't have a start pair
				var bn;
				if ( startTag === null ) {
					switch ( token ) {
					case '\n':
						content = content + "\n";
						put(content);
						buff.cr();
						empty();
						break;
						// set start tag, add previous content (if there is some)
						// clean content
					case scanner.left:
					case scanner.eLeft:
					case scanner.eeLeft:
					case scanner.cmnt:
						// a new line, just add whatever content w/i a clean
						// reset everything
						startTag = token;
						if ( content.length > 0 ) {
							put(content);
						}
						empty();
						break;

					case scanner.dLeft:
						// replace <%% with <%
						content += scanner.left;
						break;
					default:
						content += token;
						break;
					}
				}
				else {
					//we have a start tag
					switch ( token ) {
					case scanner.right:
						// %>
						switch ( startTag ) {
						case scanner.left:
							// <%

							// get the number of { minus }
							bn = bracketNum(content);
							// how are we ending this statement
							var last =
								// if the stack has value and we are ending a block
								endStack.length && bn == -1 ?
								// use the last item in the block stack
								endStack.pop() :
								// or use the default ending
								";";

							// if we are ending a returning block
							// add the finish text which returns the result of the
							// block
							if(last === doubleParen) {
								buff.push(finishTxt)
							}
							// add the remaining content
							buff.push(content, last);

							// if we have a block, start counting
							if(bn === 1 ){
								endStack.push(";")
							}
							break;
						case scanner.eLeft:
							// <%= clean content
							bn = bracketNum(content);
							if( bn ) {
								endStack.push(doubleParen)
							}
							if(quickFunc.test(content)){
								var parts = content.match(quickFunc)
								content = "function(__){var "+parts[1]+"=$(__);"+parts[2]+"}"
							}
							buff.push(insert_cmd, $.globalNamespace + ".EJS.clean(", content,bn ? startTxt : doubleParen);
							break;
						case scanner.eeLeft:
							// <%== content

							// get the number of { minus }
							bn = bracketNum(content);
							// if we have more {, it means there is a block
							if( bn ){
								// when we return to the same # of { vs } end wiht a doubleParen
								endStack.push(doubleParen)
							}

							buff.push(insert_cmd, $.globalNamespace + ".EJS.text(", content,
								// if we have a block
								bn ?
								// start w/ startTxt "var _v1ew = [])"
								startTxt :
								// if not, add doubleParent to close push and text
								doubleParen
								);
							break;
						}
						startTag = null;
						empty();
						break;
					case scanner.dRight:
						content += scanner.right;
						break;
					default:
						content += token;
						break;
					}
				}
			})
			if ( content.length > 0 ) {
				// Should be content.dump in Ruby
				buff.push(put_cmd, '"', clean(content) + '");');
			}
			var template = buff.close(),
				out = {
					out: 'try { with(_VIEW) { with (_CONTEXT) {' + template + " "+finishTxt+"}}}catch(e){e.lineNumber=null;throw e;}"
				};
			//use eval instead of creating a function, b/c it is easier to debug
			// myEval.call(out, 'this.fn = (function(_CONTEXT,_VIEW){' + out.out + '});\r\n//@ sourceURL=' + name + ".js");

			// !-- FOUNDRY HACK --! //
			// Removed //@ sourceURL as it will break with conditional compilation turned on in IE.
			myEval.call(out, 'this.fn = (function(_CONTEXT,_VIEW){ var $ = ' + $.globalNamespace + ';' + out.out + '});');

			return out;
		};


	// A Buffer used to add content to.
	// This is useful for performance and simplifying the
	// code above.
	// We also can use this so we know line numbers when there
	// is an error.
	// pre_cmd - code that sets up the buffer
	// post - code that finalizes the buffer
	EJS.Buffer = function( pre_cmd, post ) {
		// the current line we are on
		this.line = [];
		// the combined content added to this buffer
		this.script = [];
		// content at the end of the buffer
		this.post = post;
		// add the pre commands to the first line
		this.push.apply(this, pre_cmd);
	};
	EJS.Buffer.prototype = {
		// add content to this line
		// need to maintain your own semi-colons (for performance)
		push: function() {
			this.line.push.apply(this.line, arguments);
		},
		// starts a new line
		cr: function() {
			this.script.push(this.line.join(''), "\n");
			this.line = [];
		},
		//returns the script too
		close: function() {
			// if we have ending line content, add it to the script
			if ( this.line.length > 0 ) {
				this.script.push(this.line.join(''));
				this.line = [];
			}
			// if we have ending content, add it
			this.post.length && this.push.apply(this, this.post);
			// always end in a ;
			this.script.push(";");
			return this.script.join("");
		}

	};

	/**
	 * @class jQuery.EJS.Helpers
	 * @parent jQuery.EJS
	 * By adding functions to jQuery.EJS.Helpers.prototype, those functions will be available in the
	 * views.
	 *
	 * The following helper converts a given string to upper case:
	 *
	 *  $.EJS.Helpers.prototype.toUpper = function(params)
	 *  {
	 *      return params.toUpperCase();
	 *  }
	 *
	 * Use it like this in any EJS template:
	 *
	 *  <%= toUpper('javascriptmvc') %>
	 *
	 * To access the current DOM element return a function that takes the element as a parameter:
	 *
	 *  $.EJS.Helpers.prototype.upperHtml = function(params)
	 *  {
	 *      return function(el) {
	 *          $(el).html(params.toUpperCase());
	 *      }
	 *  }
	 *
	 * In your EJS view you can then call the helper on an element tag:
	 *
	 *  <div <%= upperHtml('javascriptmvc') %>></div>
	 *
	 *
	 * @constructor Creates a view helper.  This function
	 * is called internally.  You should never call it.
	 * @param {Object} data The data passed to the
	 * view.  Helpers have access to it through this._data
	 */
	EJS.Helpers = function( data, extras ) {
		this._data = data;
		this._extras = extras;
		extend(this, extras);
	};
	/**
	 * @prototype
	 */
	EJS.Helpers.prototype = {
		/**
		 * Hooks up a jQuery plugin on.
		 * @param {String} name the plugin name
		 */
		plugin: function( name ) {
			var args = $.makeArray(arguments),
				widget = args.shift();
			return function( el ) {
				var jq = $(el);
				jq[widget].apply(jq, args);
			};
		},
		/**
		 * Renders a partial view.  This is deprecated in favor of <code>$.View()</code>.
		 */
		view: function( url, data, helpers ) {
			helpers = helpers || this._extras;
			data = data || this._data;
			return $View(url, data, helpers); //new EJS(options).render(data, helpers);
		}
	};

	// options for steal's build
	$View.register({
		suffix: "ejs",
		//returns a function that renders the view
		script: function( id, src ) {
			return $.globalNamespace + ".EJS(function(_CONTEXT,_VIEW) { " + new EJS({
				text: src,
				name: id
			}).template.out + " })";
		},
		renderer: function( id, text ) {
			return EJS({
				text: text,
				name: id
			});
		}
	});
})();(function(){

	// Alias helpful methods from jQuery
	var isArray = $.isArray,
		isObject = function( obj ) {
			return typeof obj === 'object' && obj !== null && obj;
		},
		makeArray = $.makeArray,
		each = $.each,
		// listens to changes on val and 'bubbles' the event up
		// - val the object to listen to changes on
		// - prop the property name val is at on
		// - parent the parent object of prop
		hookup = function( val, prop, parent ) {
			// if it's an array make a list, otherwise a val
			if (val instanceof $.Observe){
				// we have an observe already
				// make sure it is not listening to this already
				unhookup([val], parent._namespace)
			} else if ( isArray(val) ) {
				val = new $.Observe.List(val)
			} else {
				val = new $.Observe(val)
			}
			// attr (like target, how you (delegate) to get to the target)
			// currentAttr (how to get to you)
			// delegateAttr (hot to get to the delegated Attr)

			//
			//
			//listen to all changes and trigger upwards
			val.bind("change" + parent._namespace, function( ev, attr ) {
				// trigger the type on this ...
				var args = $.makeArray(arguments),
					ev = args.shift();
				if(prop === "*"){
					args[0] = parent.indexOf(val)+"." + args[0]
				} else {
					args[0] = prop +  "." + args[0]
				}
				// change the attr
				//ev.origTarget = ev.origTarget || ev.target;
				// the target should still be the original object ...
				$.event.trigger(ev, args, parent)
			});

			return val;
		},
		unhookup = function(items, namespace){
			var item;
			for(var i =0; i < items.length; i++){
				item = items[i]
				if(  item && item.unbind ){
					item.unbind("change" + namespace)
				}
			}
		},
		// an id to track events for a given observe
		id = 0,
		collecting = null,
		// call to start collecting events (Observe sends all events at once)
		collect = function() {
			if (!collecting ) {
				collecting = [];
				return true;
			}
		},
		// creates an event on item, but will not send immediately
		// if collecting events
		// - item - the item the event should happen on
		// - event - the event name ("change")
		// - args - an array of arguments
		trigger = function( item, event, args ) {
			// send no events if initalizing
			if (item._init) {
				return;
			}
			if (!collecting ) {
				return $.event.trigger(event, args, item, true)
			} else {
				collecting.push({
					t: item,
					ev: event,
					args: args
				})
			}
		},
		// which batch of events this is for, might not want to send multiple
		// messages on the same batch.  This is mostly for
		// event delegation
		batchNum = 0,
		// sends all pending events
		sendCollection = function() {
			var len = collecting.length,
				items = collecting.slice(0),
				cur;
			collecting = null;
			batchNum ++;
			for ( var i = 0; i < len; i++ ) {
				cur = items[i];
				// batchNum
				$.event.trigger({
					type: cur.ev,
					batchNum : batchNum
				}, cur.args, cur.t)
			}

		},
		// a helper used to serialize an Observe or Observe.List where:
		// observe - the observable
		// how - to serialize with 'attrs' or 'serialize'
		// where - to put properties, in a {} or [].
		serialize = function( observe, how, where ) {
			// go through each property
			observe.each(function( name, val ) {
				// if the value is an object, and has a attrs or serialize function
				where[name] = isObject(val) && typeof val[how] == 'function' ?
				// call attrs or serialize to get the original data back
				val[how]() :
				// otherwise return the value
				val
			})
			return where;
		};

	/**
	 * @class jQuery.Observe
	 * @parent jquerymx.lang
	 * @test jquery/lang/observe/qunit.html
	 *
	 * Observe provides the awesome observable pattern for
	 * JavaScript Objects and Arrays. It lets you
	 *
	 *   - Set and remove property or property values on objects and arrays
	 *   - Listen for changes in objects and arrays
	 *   - Work with nested properties
	 *
	 * ## Creating an $.Observe
	 *
	 * To create an $.Observe, or $.Observe.List, you can simply use
	 * the `$.O(data)` shortcut like:
	 *
	 *     var person = $.O({name: 'justin', age: 29}),
	 *         hobbies = $.O(['programming', 'basketball', 'nose picking'])
	 *
	 * Depending on the type of data passed to $.O, it will create an instance of either:
	 *
	 *   - $.Observe, which is used for objects like: `{foo: 'bar'}`, and
	 *   - [jQuery.Observe.List $.Observe.List], which is used for arrays like `['foo','bar']`
	 *
	 * $.Observe.List and $.Observe are very similar. In fact,
	 * $.Observe.List inherits $.Observe and only adds a few extra methods for
	 * manipulating arrays like [jQuery.Observe.List.prototype.push push].  Go to
	 * [jQuery.Observe.List $.Observe.List] for more information about $.Observe.List.
	 *
	 * You can also create a `new $.Observe` simply by pass it the data you want to observe:
	 *
	 *     var data = {
	 *       addresses : [
	 *         {
	 *           city: 'Chicago',
	 *           state: 'IL'
	 *         },
	 *         {
	 *           city: 'Boston',
	 *           state : 'MA'
	 *         }
	 *         ],
	 *       name : "Justin Meyer"
	 *     },
	 *     o = new $.Observe(data);
	 *
	 * _o_ now represents an observable copy of _data_.
	 *
	 * ## Getting and Setting Properties
	 *
	 * Use [jQuery.Observe.prototype.attr attr] and [jQuery.Observe.prototype.attr attrs]
	 * to get and set properties.
	 *
	 * For example, you can read the property values of _o_ with
	 * `observe.attr( name )` like:
	 *
	 *     // read name
	 *     o.attr('name') //-> Justin Meyer
	 *
	 * And set property names of _o_ with
	 * `observe.attr( name, value )` like:
	 *
	 *     // update name
	 *     o.attr('name', "Brian Moschel") //-> o
	 *
	 * Observe handles nested data.  Nested Objects and
	 * Arrays are converted to $.Observe and
	 * $.Observe.Lists.  This lets you read nested properties
	 * and use $.Observe methods on them.  The following
	 * updates the second address (Boston) to 'New York':
	 *
	 *     o.attr('addresses.1').attrs({
	 *       city: 'New York',
	 *       state: 'NY'
	 *     })
	 *
	 * `attrs()` can be used to get all properties back from the observe:
	 *
	 *     o.attrs() // ->
	 *     {
	 *       addresses : [
	 *         {
	 *           city: 'Chicago',
	 *           state: 'IL'
	 *         },
	 *         {
	 *           city: 'New York',
	 *           state : 'MA'
	 *         }
	 *       ],
	 *       name : "Brian Moschel"
	 *     }
	 *
	 * ## Listening to property changes
	 *
	 * When a property value is changed, it creates events
	 * that you can listen to.  There are two ways to listen
	 * for events:
	 *
	 *   - [jQuery.Observe.prototype.bind bind] - listen for any type of change
	 *   - [jQuery.Observe.prototype.delegate delegate] - listen to a specific type of change
	 *
	 * With `bind( "change" , handler( ev, attr, how, newVal, oldVal ) )`, you can listen
	 * to any change that happens within the
	 * observe. The handler gets called with the property name that was
	 * changed, how it was changed ['add','remove','set'], the new value
	 * and the old value.
	 *
	 *     o.bind('change', function( ev, attr, how, nevVal, oldVal ) {
	 *
	 *     })
	 *
	 * `delegate( attr, event, handler(ev, newVal, oldVal ) )` lets you listen
	 * to a specific event on a specific attribute.
	 *
	 *     // listen for name changes
	 *     o.delegate("name","set", function(){
	 *
	 *     })
	 *
	 * Delegate lets you specify multiple attributes and values to match
	 * for the callback. For example,
	 *
	 *     r = $.O({type: "video", id : 5})
	 *     r.delegate("type=images id","set", function(){})
	 *
	 * This is used heavily by [jQuery.route $.route].
	 *
	 * @constructor
	 *
	 * @param {Object} obj a JavaScript Object that will be
	 * converted to an observable
	 */
	$.Class($.globalNamespace + '.Observe',
	/**
	 * @prototype
	 */
	{
		init: function( obj ) {
			// _data is where we keep the properties
			this._data = {};
			// the namespace this object uses to listen to events
			this._namespace = ".observe" + (++id);
			// sets all attrs
			this._init = true;
			this.attrs(obj);
			delete this._init;
		},
		/**
		 * Get or set an attribute on the observe.
		 *
		 *     o = new $.Observe({});
		 *
		 *     // sets a user property
		 *     o.attr('user',{name: 'hank'});
		 *
		 *     // read the user's name
		 *     o.attr('user.name') //-> 'hank'
		 *
		 * If a value is set for the first time, it will trigger
		 * an `'add'` and `'set'` change event.  Once
		 * the value has been added.  Any future value changes will
		 * trigger only `'set'` events.
		 *
		 *
		 * @param {String} attr the attribute to read or write.
		 *
		 *     o.attr('name') //-> reads the name
		 *     o.attr('name', 'Justin') //-> writes the name
		 *
		 * You can read or write deep property names.  For example:
		 *
		 *     o.attr('person', {name: 'Justin'})
		 *     o.attr('person.name') //-> 'Justin'
		 *
		 * @param {Object} [val] if provided, sets the value.
		 * @return {Object} the observable or the attribute property.
		 *
		 * If you are reading, the property value is returned:
		 *
		 *     o.attr('name') //-> Justin
		 *
		 * If you are writing, the observe is returned for chaining:
		 *
		 *     o.attr('name',"Brian").attr('name') //-> Justin
		 */
		attr: function( attr, val ) {

			if ( val === undefined ) {
				// if we are getting a value
				return this._get(attr)
			} else {
				// otherwise we are setting
				this._set(attr, val);
				return this;
			}
		},
		/**
		 * Iterates through each attribute, calling handler
		 * with each attribute name and value.
		 *
		 *     new Observe({foo: 'bar'})
		 *       .each(function(name, value){
		 *         equals(name, 'foo')
		 *         equals(value,'bar')
		 *       })
		 *
		 * @param {function} handler(attrName,value) A function that will get
		 * called back with the name and value of each attribute on the observe.
		 *
		 * Returning `false` breaks the looping.  The following will never
		 * log 3:
		 *
		 *     new Observe({a : 1, b : 2, c: 3})
		 *       .each(function(name, value){
		 *         console.log(value)
		 *         if(name == 2){
		 *           return false;
		 *         }
		 *       })
		 *
		 * @return {jQuery.Observe} the original observable.
		 */
		each: function() {
			return each.apply(null, [this.__get()].concat(makeArray(arguments)))
		},
		/**
		 * Removes a property
		 *
		 *     o =  new $.Observe({foo: 'bar'});
		 *     o.removeAttr('foo'); //-> 'bar'
		 *
		 * This creates a `'remove'` change event. Learn more about events
		 * in [jQuery.Observe.prototype.bind bind] and [jQuery.Observe.prototype.delegate delegate].
		 *
		 * @param {String} attr the attribute name to remove.
		 * @return {Object} the value that was removed.
		 */
		removeAttr: function( attr ) {
			// convert the attr into parts (if nested)
			var parts = isArray(attr) ? attr : attr.split("."),
				// the actual property to remove
				prop = parts.shift(),
				// the current value
				current = this._data[prop];

			// if we have more parts, call removeAttr on that part
			if ( parts.length ) {
				return current.removeAttr(parts)
			} else {
				// otherwise, delete
				delete this._data[prop];
				// create the event
				trigger(this, "change", [prop, "remove", undefined, current]);
				return current;
			}
		},
		// reads a property from the object
		_get: function( attr ) {
			var parts = isArray(attr) ? attr : (""+attr).split("."),
				current = this.__get(parts.shift());
			if ( parts.length ) {
				return current ? current._get(parts) : undefined
			} else {
				return current;
			}
		},
		// reads a property directly if an attr is provided, otherwise
		// returns the 'real' data object itself
		__get: function( attr ) {
			return attr ? this._data[attr] : this._data;
		},
		// sets attr prop as value on this object where
		// attr - is a string of properties or an array  of property values
		// value - the raw value to set
		// description - an object with converters / serializers / defaults / getterSetters?
		_set: function( attr, value ) {
			// convert attr to attr parts (if it isn't already)
			var parts = isArray(attr) ? attr : ("" + attr).split("."),
				// the immediate prop we are setting
				prop = parts.shift(),
				// its current value
				current = this.__get(prop);

			// if we have an object and remaining parts
			if ( isObject(current) && parts.length ) {
				// that object should set it (this might need to call attr)
				current._set(parts, value)
			} else if (!parts.length ) {
				// otherwise, we are setting it on this object
				// todo: check if value is object and transform
				// are we changing the value
				if ( value !== current ) {

					// check if we are adding this for the first time
					// if we are, we need to create an 'add' event
					var changeType = this.__get().hasOwnProperty(prop) ? "set" : "add";

					// set the value on data
					this.__set(prop,
					// if we are getting an object
					isObject(value) ?
					// hook it up to send event to us
					hookup(value, prop, this) :
					// value is normal
					value);



					// trigger the change event
					trigger(this, "change", [prop, changeType, value, current]);

					// if we can stop listening to our old value, do it
					current && unhookup([current], this._namespace);
				}

			} else {
				throw "jQuery.Observe: set a property on an object that does not exist"
			}
		},
		// directly sets a property on this object
		__set: function( prop, val ) {
			this._data[prop] = val;
			// add property directly for easy writing
			// check if its on the prototype so we don't overwrite methods like attrs
			if (!(prop in this.constructor.prototype)) {
				this[prop] = val
			}
		},
		/**
		 * Listens to changes on a jQuery.Observe.
		 *
		 * When attributes of an observe change, including attributes on nested objects,
		 * a `'change'` event is triggered on the observe.  These events come
		 * in three flavors:
		 *
		 *   - `add` - a attribute is added
		 *   - `set` - an existing attribute's value is changed
		 *   - `remove` - an attribute is removed
		 *
		 * The change event is fired with:
		 *
		 *  - the attribute changed
		 *  - how it was changed
		 *  - the newValue of the attribute
		 *  - the oldValue of the attribute
		 *
		 * Example:
		 *
		 *     o = new $.Observe({name : "Payal"});
		 *     o.bind('change', function(ev, attr, how, newVal, oldVal){
		 *       // ev    -> {type: 'change'}
		 *       // attr  -> "name"
		 *       // how   -> "add"
		 *       // newVal-> "Justin"
		 *       // oldVal-> undefined
		 *     })
		 *
		 *     o.attr('name', 'Justin')
		 *
		 * Listening to `change` is only useful for when you want to
		 * know every change on an Observe.  For most applications,
		 * [jQuery.Observe.prototype.delegate delegate] is
		 * much more useful as it lets you listen to specific attribute
		 * changes and sepecific types of changes.
		 *
		 *
		 * @param {String} eventType the event name.  Currently,
		 * only 'change' events are supported. For more fine
		 * grained control, use [jQuery.Observe.prototype.delegate].
		 *
		 * @param {Function} handler(event, attr, how, newVal, oldVal) A
		 * callback function where
		 *
		 *   - event - the event
		 *   - attr - the name of the attribute changed
		 *   - how - how the attribute was changed (add, set, remove)
		 *   - newVal - the new value of the attribute
		 *   - oldVal - the old value of the attribute
		 *
		 * @return {$.Observe} the observe for chaining.
		 */
		bind: function( eventType, handler ) {
			$.fn.bind.apply($([this]), arguments);
			return this;
		},
		/**
		 * Unbinds a listener.  This uses [http://api.jquery.com/unbind/ jQuery.unbind]
		 * and works very similar.  This means you can
		 * use namespaces or unbind all event handlers for a given event:
		 *
		 *     // unbind a specific event handler
		 *     o.unbind('change', handler)
		 *
		 *     // unbind all change event handlers bound with the
		 *     // foo namespace
		 *     o.unbind('change.foo')
		 *
		 *     // unbind all change event handlers
		 *     o.unbind('change')
		 *
		 * @param {String} eventType - the type of event with
		 * any optional namespaces.  Currently, only `change` events
		 * are supported with bind.
		 *
		 * @param {Function} [handler] - The original handler function passed
		 * to [jQuery.Observe.prototype.bind bind].
		 *
		 * @return {jQuery.Observe} the original observe for chaining.
		 */
		unbind: function( eventType, handler ) {
			$.fn.unbind.apply($([this]), arguments);
			return this;
		},
		/**
		 * Get the serialized Object form of the observe.  Serialized
		 * data is typically used to send back to a server.
		 *
		 *     o.serialize() //-> { name: 'Justin' }
		 *
		 * Serialize currently returns the same data
		 * as [jQuery.Observe.prototype.attrs].  However, in future
		 * versions, serialize will be able to return serialized
		 * data similar to [jQuery.Model].  The following will work:
		 *
		 *     new Observe({time: new Date()})
		 *       .serialize() //-> { time: 1319666613663 }
		 *
		 * @return {Object} a JavaScript Object that can be
		 * serialized with `JSON.stringify` or other methods.
		 *
		 */
		serialize: function() {
			return serialize(this, 'serialize', {});
		},
		/**
		 * Set multiple properties on the observable
		 * @param {Object} props
		 * @param {Boolean} remove true if you should remove properties that are not in props
		 */
		attrs: function( props, remove ) {
			if ( props === undefined ) {
				return serialize(this, 'attrs', {})
			}

			props = $.extend(true, {}, props);
			var prop, collectingStarted = collect();

			for ( prop in this._data ) {
				var curVal = this._data[prop],
					newVal = props[prop];

				// if we are merging ...
				if ( newVal === undefined ) {
					remove && this.removeAttr(prop);
					continue;
				}
				if ( isObject(curVal) && isObject(newVal) ) {
					curVal.attrs(newVal, remove)
				} else if ( curVal != newVal ) {
					this._set(prop, newVal)
				} else {

				}
				delete props[prop];
			}
			// add remaining props
			for ( var prop in props ) {
				newVal = props[prop];
				this._set(prop, newVal)
			}
			if ( collectingStarted ) {
				sendCollection();
			}
		}
	});
	// Helpers for list
	/**
	 * @class jQuery.Observe.List
	 * @inherits jQuery.Observe
	 * @parent jQuery.Observe
	 *
	 * An observable list.  You can listen to when items are push, popped,
	 * spliced, shifted, and unshifted on this array.
	 *
	 *
	 */
	var list = $.Observe($.globalNamespace + '.Observe.List',
	/**
	 * @prototype
	 */
	{
		init: function( instances, options ) {
			this.length = 0;
			this._namespace = ".list" + (++id);
			this._init = true;
			this.bind('change',this.proxy('_changes'));
			this.push.apply(this, makeArray(instances || []));
			$.extend(this, options);
			if(this.comparator){
				this.sort()
			}
			delete this._init;
		},
		_changes : function(ev, attr, how, newVal, oldVal){
			// detects an add, sorts it, re-adds?
			//console.log("")



			// if we are sorting, and an attribute inside us changed
			if(this.comparator && /^\d+./.test(attr) ) {

				// get the index
				var index = +(/^\d+/.exec(attr)[0]),
					// and item
					item = this[index],
					// and the new item
					newIndex = this.sortedIndex(item);

				if(newIndex !== index){
					// move ...
					[].splice.call(this, index, 1);
					[].splice.call(this, newIndex, 0, item);

					trigger(this, "move", [item, newIndex, index]);
					ev.stopImmediatePropagation();
					trigger(this,"change", [
						attr.replace(/^\d+/,newIndex),
						how,
						newVal,
						oldVal
					]);
					return;
				}
			}


			// if we add items, we need to handle
			// sorting and such

			// trigger direct add and remove events ...
			if(attr.indexOf('.') === -1){

				if( how === 'add' ) {
					trigger(this, how, [newVal,+attr]);
				} else if( how === 'remove' ) {
					trigger(this, how, [oldVal, +attr])
				}

			}
			// issue add, remove, and move events ...
		},
		sortedIndex : function(item){
			var itemCompare = item.attr(this.comparator),
				equaled = 0,
				i;
			for(var i =0; i < this.length; i++){
				if(item === this[i]){
					equaled = -1;
					continue;
				}
				if(itemCompare <= this[i].attr(this.comparator) ) {
					return i+equaled;
				}
			}
			return i+equaled;
		},
		__get : function(attr){
			return attr ? this[attr] : this;
		},
		__set : function(attr, val){
			this[attr] = val;
		},
		/**
		 * Returns the serialized form of this list.
		 */
		serialize: function() {
			return serialize(this, 'serialize', []);
		},
		/**
		 * Iterates through each item of the list, calling handler
		 * with each index and value.
		 *
		 *     new Observe.List(['a'])
		 *       .each(function(index, value){
		 *         equals(index, 1)
		 *         equals(value,'a')
		 *       })
		 *
		 * @param {function} handler(index,value) A function that will get
		 * called back with the index and value of each item on the list.
		 *
		 * Returning `false` breaks the looping.  The following will never
		 * log 'c':
		 *
		 *     new Observe(['a','b','c'])
		 *       .each(function(index, value){
		 *         console.log(value)
		 *         if(index == 1){
		 *           return false;
		 *         }
		 *       })
		 *
		 * @return {jQuery.Observe.List} the original observable.
		 */
		// placeholder for each
		/**
		 * Remove items or add items from a specific point in the list.
		 *
		 * ### Example
		 *
		 * The following creates a list of numbers and replaces 2 and 3 with
		 * "a", and "b".
		 *
		 *     var l = new $.Observe.List([0,1,2,3]);
		 *
		 *     l.bind('change', function( ev, attr, how, newVals, oldVals, where ) { ... })
		 *
		 *     l.splice(1,2, "a", "b"); // results in [0,"a","b",3]
		 *
		 * This creates 2 change events.  The first event is the removal of
		 * numbers one and two where it's callback values will be:
		 *
		 *   - attr - "1" - indicates where the remove event took place
		 *   - how - "remove"
		 *   - newVals - undefined
		 *   - oldVals - [1,2] -the array of removed values
		 *   - where - 1 - the location of where these items where removed
		 *
		 * The second change event is the addition of the "a", and "b" values where
		 * the callback values will be:
		 *
		 *   - attr - "1" - indicates where the add event took place
		 *   - how - "added"
		 *   - newVals - ["a","b"]
		 *   - oldVals - [1, 2] - the array of removed values
		 *   - where - 1 - the location of where these items where added
		 *
		 * @param {Number} index where to start removing or adding items
		 * @param {Object} count the number of items to remove
		 * @param {Object} [added] an object to add to
		 */
		splice: function( index, count ) {
			var args = makeArray(arguments),
				i;

			for ( i = 2; i < args.length; i++ ) {
				var val = args[i];
				if ( isObject(val) ) {
					args[i] = hookup(val, "*", this)
				}
			}
			if ( count === undefined ) {
				count = args[1] = this.length - index;
			}
			var removed = [].splice.apply(this, args);
			if ( count > 0 ) {
				trigger(this, "change", [""+index, "remove", undefined, removed]);
				unhookup(removed, this._namespace);
			}
			if ( args.length > 2 ) {
				trigger(this, "change", [""+index, "add", args.slice(2), removed]);
			}
			return removed;
		},
		/**
		 * Updates an array with a new array.  It is able to handle
		 * removes in the middle of the array.
		 *
		 * @param {Array} props
		 * @param {Boolean} remove
		 */
		attrs: function( props, remove ) {
			if ( props === undefined ) {
				return serialize(this, 'attrs', []);
			}

			// copy
			props = props.slice(0);

			var len = Math.min(props.length, this.length),
				collectingStarted = collect();
			for ( var prop = 0; prop < len; prop++ ) {
				var curVal = this[prop],
					newVal = props[prop];

				if ( isObject(curVal) && isObject(newVal) ) {
					curVal.attrs(newVal, remove)
				} else if ( curVal != newVal ) {
					this._set(prop, newVal)
				} else {

				}
			}
			if ( props.length > this.length ) {
				// add in the remaining props
				this.push(props.slice(this.length))
			} else if ( props.length < this.length && remove ) {
				this.splice(props.length)
			}
			//remove those props didn't get too
			if ( collectingStarted ) {
				sendCollection()
			}
		},
		sort: function(method, silent){
			var comparator = this.comparator,
				args = comparator ? [function(a, b){
					a = a[comparator]
					b = b[comparator]
					return a === b ? 0 : (a < b ? -1 : 1);
				}] : [],
				res = [].sort.apply(this, args);

			!silent && trigger(this, "reset");

		}
	}),


		// create push, pop, shift, and unshift
		// converts to an array of arguments
		getArgs = function( args ) {
			if ( args[0] && ($.isArray(args[0])) ) {
				return args[0]
			}
			else {
				return makeArray(args)
			}
		};
	// describes the method and where items should be added
	each({
		/**
		 * @function push
		 * Add items to the end of the list.
		 *
		 *     var l = new $.Observe.List([]);
		 *
		 *     l.bind('change', function(
		 *         ev,        // the change event
		 *         attr,      // the attr that was changed, for multiple items, "*" is used
		 *         how,       // "add"
		 *         newVals,   // an array of new values pushed
		 *         oldVals,   // undefined
		 *         where      // the location where these items where added
		 *         ) {
		 *
		 *     })
		 *
		 *     l.push('0','1','2');
		 *
		 * @return {Number} the number of items in the array
		 */
		push: "length",
		/**
		 * @function unshift
		 * Add items to the start of the list.  This is very similar to
		 * [jQuery.Observe.prototype.push].
		 */
		unshift: 0
	},
	// adds a method where
	// - name - method name
	// - where - where items in the array should be added


	function( name, where ) {
		list.prototype[name] = function() {
			// get the items being added
			var args = getArgs(arguments),
				// where we are going to add items
				len = where ? this.length : 0;

			// go through and convert anything to an observe that needs to be converted
			for ( var i = 0; i < args.length; i++ ) {
				var val = args[i];
				if ( isObject(val) ) {
					args[i] = hookup(val, "*", this)
				}
			}

			// if we have a sort item, add that
			if( args.length == 1 && this.comparator ) {
				// add each item ...
				// we could make this check if we are already adding in order
				// but that would be confusing ...
				var index = this.sortedIndex(args[0]);
				this.splice(index, 0, args[0]);
				return this.length;
			}

			// call the original method
			var res = [][name].apply(this, args)

			// cause the change where the args are:
			// len - where the additions happened
			// add - items added
			// args - the items added
			// undefined - the old value
			if ( this.comparator  && args.length > 1) {
				this.sort(null, true);
				trigger(this,"reset", [args])
			} else {
				trigger(this, "change", [""+len, "add", args, undefined])
			}


			return res;
		}
	});

	each({
		/**
		 * @function pop
		 *
		 * Removes an item from the end of the list.
		 *
		 *     var l = new $.Observe.List([0,1,2]);
		 *
		 *     l.bind('change', function(
		 *         ev,        // the change event
		 *         attr,      // the attr that was changed, for multiple items, "*" is used
		 *         how,       // "remove"
		 *         newVals,   // undefined
		 *         oldVals,   // 2
		 *         where      // the location where these items where added
		 *         ) {
		 *
		 *     })
		 *
		 *     l.pop();
		 *
		 * @return {Object} the element at the end of the list
		 */
		pop: "length",
		/**
		 * @function shift
		 * Removes an item from the start of the list.  This is very similar to
		 * [jQuery.Observe.prototype.pop].
		 *
		 * @return {Object} the element at the start of the list
		 */
		shift: 0
	},
	// creates a 'remove' type method


	function( name, where ) {
		list.prototype[name] = function() {

			var args = getArgs(arguments),
				len = where && this.length ? this.length - 1 : 0;


			var res = [][name].apply(this, args)

			// create a change where the args are
			// "*" - change on potentially multiple properties
			// "remove" - items removed
			// undefined - the new values (there are none)
			// res - the old, removed values (should these be unbound)
			// len - where these items were removed
			trigger(this, "change", [""+len, "remove", undefined, [res]])

			if ( res && res.unbind ) {
				res.unbind("change" + this._namespace)
			}
			return res;
		}
	});

	list.prototype.
	/**
	 * @function indexOf
	 * Returns the position of the item in the array.  Returns -1 if the
	 * item is not in the array.
	 * @param {Object} item
	 * @return {Number}
	 */
	indexOf = [].indexOf || function(item){
		return $.inArray(item, this)
	}

	/**
	 * @class $.O
	 */
	$.O = function(data, options){
		if(isArray(data) || data instanceof $.Observe.List){
			return new $.Observe.List(data, options)
		} else {
			return new $.Observe(data, options)
		}
	}
})();

});
KTVendors.plugin("joomla", function($) {

/**
 * joomla
 * Abstraction layer for Joomla client-side API.
 * https://github.com/foundry-modules/joomla
 *
 * Copyright (c) 2012 Jason Ramos
 * www.stackideas.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

var parser = {
	squeezebox: function() {
		return window.parent.SqueezeBox;
	}
};

var self = $.Joomla = function(method, args) {

	// Overriding function
	if ($.isFunction(args)) {

		var fn = args;

		if (self.isJoomla15) {
			window[method] = fn;
		} else {
			window.Joomla[method] = fn;
		};

		return;
	}

	// Calling function
	var method = parser[method] || ((self.isJoomla15) ? window[method] : window.Joomla[method]);

	if ($.isFunction(method)) {
		return method.apply(window, args);
	}
};

});KTVendors.plugin("module", function($) {

/**
 * jquery.module.
 * An AMD manager built on top of $.Deferred() backbone.
 * An alternative take on RequireJS's define().
 *
 * Part of the jquery.require family.
 * https://github.com/jstonne/jquery.require
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

var Module = function(name) {

	var module = this,
		ready = $.Callbacks("once memory");

	$.extend(this, $.Deferred(), {

		// Name of the module
		name: name,

		// Module status
		// pending, ready, executing, resolved, rejected
		status: "pending",

		// When a module factory is received,
		// this event is fired.
		ready: function(fn) {
			if (fn===true) ready.fire.call(module, $);
			if ($.isFunction(fn)) ready.add(fn);
		}
	});

	// Listen to the events of the module
	// and update the module status as necessary.
	module.then(
		function() {
			module.exports = this;
			module.status  = "resolved";
		},
		function() {
			module.status  = "rejected";
		}
	);

	// Keep a copy of the original done method.
	// This is so that we can track when this done
	// method is being called for the first time,
	// and perform the necessary actions below.
	var done = module.done;

	module.done = function() {

		// Flag this module as required
		// This indicates that we should
		// execute the module factory.
		module.required = true;

		// Execute the module factory
		// if this module has received it
		// and it hasn't been executed yet.
		var factory = module.factory;
		if (factory && module.status==="ready") {
			factory.call(module, $);
		}

		// Replace this first-time done method
		// with the original done method.
		module.done = done;

		// Execute the original done method.
		return module.done.apply(this, arguments);
	}
}

$.module = (function() {

	var self = function(name, factory) {

		var module;

		if (typeof name === "string") {

			module = self.get(name);

			/** Facade #1. Get module.
			 *
			 *  $.module('foobar'); // returns module
             *
		     */
			if (factory === undefined) {
				return module;
			}

			/** Facade #2. Factory assignment.
             *
			 *  $.module('foobar', function() {
			 *
			 *      // This is required in every module factory.
			 *      // Resolve module, return exports.
			 *
			 *      this.resolveWith(exports, [args]);
			 *
		     *  });
             *
		     */

			if ($.isFunction(factory)) {

				// If module is resolved, don't let new factory overwrite it.
				if (module.status=="resolved") return module;

				module.factory = factory;

				module.status = "ready";

				// Indicates that the module factory
				// for this module has been received.
				module.ready("true");

				// If the module is required,
				// execute the module factory.
				if (module.required) {

					module.status = "executing";

					// Execute factory
					factory.call(module, $);
				}

				return module;
			}
		}

		/** Facade #3. Multiple factory assignments / Predefine modules.
		 *	This is used by Foundry compiler when combining multiple script files into one.
         *
		 *  $.module([
	     *
	     *      // Module task object
	     *      {
	     *			name: "module.name"
	     *			factory: function(){}
	     *      }
	     *
	     *      // Module which is loading
	     *      // but factory assignment kicks in later
	     *      "module.name"
		 *	]);
		 *
		 */

		// Predefine modules
		if ($.isArray(name)) {

			var tasks = $.map(name, function(task) {

				var module = self.get($.isString(task) ? task : task.name);

				if (!module) return;

				// If module is pending, set it to ready.
				// This trick require calls into thinking that
				// the script file of this module has been loaded,
				// so it won't go and load the script file again.
				if (module.status === "pending") {
					module.status = "ready";
				}

				if ($.isPlainObject(task)) return task;
			});

			// Run through the list of tasks and assign its factory to the module.
			$.each(tasks, function(i, task) {

				// Assign factory to module
				self(task.name, task.factory);
			});
		}
	}

	// $.module static methods
	$.extend(self, {

		registry: {},

		get: function(name) {
			if (!name) return;

			if ($.isModule(name)) {
				name = name.replace("module://", "");
			}

			return self.registry[name] || self.create(name);
		},

		create: function(name) {
			return self.registry[name] = new Module(name);
		},

		remove: function(name) {
			delete self.registry[name];
		}
	});

	return self;

})();

$.isModule = function(module) {

	if ($.isString(module)) {
		return !!module.match("module://");
	}

	return module && module instanceof Module;
}

});KTVendors.plugin("script", function($) {

/**
 * jquery.script
 * Script injection utility built on top $.Deferred() backbone.
 * https://github.com/jstonne/jquery.script
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.Script = function(fn) {

    var script = document.createElement("script");
    script.text = $.isString(fn) ? fn : $.callback(fn) + "();";

    return $(script);
};

$.script = (function(){

    var self = function(options) {

        if (options===undefined) {
            return;
        }

        if (typeof options==="string") {
            options = {
                url: options
            }
        }

        var script = new Script(options);

        return script;
    };

    var Script = function(options) {

        var script = $.extend(this, options);

        script.manager = $.Deferred();

        $.extend(script, script.manager.promise());

        script.load();
    };

    var head = document.getElementsByTagName("head")[0];
    var baseElement = document.getElementsByTagName("base")[0];

    $.extend(Script.prototype, {

        timeout: 7000,
        retry: 3,
        retryCount: 1,
        type: "text/javascript",
        async: false,
        charset: "UTF-8",
        verbose: false,
        head: head,

        insert: function() {

            var node = this.node;

            this.head.appendChild(node);
        },

        remove: function() {

            var node = this.node;

            // This prevents IE7-8 locking up.
            setTimeout(function(){

                // Handle memory leak in IE
                node.onload = node.onerror = node.onreadystatechange = null;

                try {
                    head.removeChild(node);
                } catch(e) {};

            }, 1000);
        },

        load: function() {

            var script = this,
                node;

            script.endTime = undefined;

            script.startTime = new Date();

            script.node = node = document.createElement('script');

            script.insert();

            // Create a reference to these proxied functions,
            // so that we can detach them from event listeners.
            script._ready = $.proxy(script.ready, script);
            script._error = $.proxy(script.error, script);

            // On IE9, addEventListener() does not necessary fire the onload event after
            // the script is loaded, attachEvent() method behaves correctly.
            if (node.attachEvent && !$.browser.opera) {
                node.attachEvent("onreadystatechange", script._ready);
                node.attachEvent("onerror"           , script._error); // IE9 only.
            } else {
                node.addEventListener("load"         , script._ready, false);
                node.addEventListener("error"        , script._error, false);
            }

            $(node).attr({
                type    : script.type,
                async   : script.async,
                charset : script.charset,
                src     : script.url
            });

            script.monitor();
        },

        monitor: function() {

            var script = this;

            if (script.retryCount > script.retry) {

                script._error();

                return;
            }

            setTimeout(function() {

                if (script.state()!=="resolved") {

                    if (script.verbose) {
                        console.warn('$.script: Load timeout. [Retry: ' + script.retryCount + ']', script);
                    }

                    script.remove();

                    script.retryCount++;

                    script.load();
                }

            }, script.timeout * script.retryCount);

        },

        ready: function(event) {

            var script = this,
                node = script.node;

            // if (script.verbose) {
            //     console.info('$.script: Loaded' + (($.browser.msie) ? ' ' + script.url.replace($.scriptPath, '') + ' ': ''), script);
            // }

            if (event.type==="load" || /loaded|complete/.test(node.readyState)) {

                script.complete.call(script, event);

                script.manager.resolve(script);
            }
        },

        error: function(event) {

            var script = this;

            if (script.verbose) {
                console.error('$.script: Unable to load ', script);
            }

            script.complete.call(script, event);

            script.remove();

            script.manager.reject(script);
        },

        complete: function(event) {

            var script = this,
                node = script.node;

            script.endTime = new Date();

            if (node.detachEvent && !$.browser.opera) {
                node.detachEvent("onreadystatechange", script._ready);
                node.detachEvent("onerror"           , script._error);
            } else {
                node.removeEventListener("load"      , script._ready, false);
                node.removeEventListener("error"     , script._error, false);
            }
        }

    });

    return self;

})();

});KTVendors.plugin("template", function($) {

/**
 * jquery.template
 * Template repository
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.template = (function() {

	var defaultTemplate = {
		content: "",
		type: "ejs"
	};

	var self = function(name, content) {

		if (name===undefined) {
			return self.templates;
		}

		var template;

		if (typeof content == "string") {

			if ($.isPlainObject(content)) {

				template = content;

			} else {

				template = $.extend({}, defaultTemplate, {name: name, content: content});
			}

			self.templates[name] = template;

			return template;

		} else {

			template = self.templates[name] || {};

			return template.content || "";
		}
	};

	$.extend(self, {

		templates: {},

		remove: function(name) {

			delete self.templates[name];
		}
	});

	return self;

 })();

});KTVendors.plugin("require", function($) {

/**
 * jquery.require.
 * A dependency loader built on top of $.Deferred() backbone.
 * An alternative take on RequireJS.
 * https://github.com/jstonne/jquery.require
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.require = (function() {

	// internal function
	var getFolderPath = function(path) {
		return $.uri(path).setAnchor('').setQuery('').toPath('../').toString();
	};

	var self = function(options) {

		var batch = new Batch(options);

		self.batches[batch.id] = batch;

		return batch;
	};

	// Require methods & properties

	$.extend(self, {

		defaultOptions: {

			// Path selection order:
			path: (function() {
				var path = 
					$.path ||

					// By "require_path" attribute
					$('[require-path]').attr('require-path') ||

					// By last script tag's "src" attribute
					getFolderPath($('script:last').attr('src')) ||

					// By window location
					getFolderPath(window.location.href);

				if (/^(\/|\.)/.test(path)) {
					path = $.uri(window.location.href).toPath(path).toString();
				}

				return path;
			})(),

			timeout: 10000,

			retry: 3,

			verbose: ($.environment=="development")
		},

		setup: function(options) {

			$.extend(self.defaultOptions, options);
		},

		batches: {},

		status: function(filter) {

			$.each(self.batches, function(i, batch){

				var count = {pending: 0, resolved: 0, rejected: 0, ready: 0, total: 0},
					messages = [];

				// Calculate statistics
				$.each(batch.tasks, function(i, task){

					state = (task.module && task.module.status=="ready") ? "ready" : task.state();
					count[state]++;
					count.total++;

					messages.push({
						state: state,
						content: '[' + state + '] ' + task.name 
					});
				});

				var batchName = batch.id + ": " + batch.state() + " [" + count.resolved + "/" + count.total + "]";

				if (filter && count[filter] < 1) return;

				if ($.IE) {

					console.log("$.require.batches[\"" + batch.id + "\"]");
					$.each(messages, function(i, message){
						console.log(message.content);
					});
					console.log("");

				} else {

					// Create log group
					console.groupCollapsed(batchName);

					// Generate list
					console.log("$.require.batches[\"" + batch.id + "\"]", batch);

					$.each(messages, function(i, message){

						var state   = message.state,
							content = message.content;

						if (!filter || state==filter) {
							switch (state) {
								case 'pending' : console.warn(content);  break;
								case 'rejected': console.error(content); break;
								default        : console.info(content);  break;
							}
						}
					});

					console.groupEnd(batchName);
				}
			});

			return "$.require.status(pending|resolved|rejected|ready);";
		},

		loaders: {},

		addLoader: function(name, factory) {

			// Static call, e.g.
			// $.require.script.setup({});
			self[name] = factory;

			// Create proxy functions to require loaders,
			// assigning current batch to factory's "this".
			Batch.prototype[name] = function() {

				var batch = this;

				// Reset auto-finalize timer
				batch.autoFinalize();

				// this == batch
				factory.apply(batch, arguments);

				// Ensure require calls are chainable
				return batch;
			};

			self.loaders[name] = self[name] = factory;
		},

		removeLoader: function(name) {
			delete Batch.prototype[name];
			delete self[name];
		}

	});

	// This serves as batch id counter, it increments
	// whenever a new batch instance is created.
	var id = 0;

	// Batch class.
	// When calling $.require(), it is actually
	// returning an new instance of this class.
	var Batch = function(options) {

		var required = $.Callbacks("once memory"),
		    isRequired = false;

		// We are extending the batch instance
		// with the following properties.
		var batch = $.extend(this, {

			// Unique ID for this batch.
			id: ++id,

			// This array keeps a list of tasks to load.
			tasks: [],

			// Stores options like load path, timeout and retry count. 
			options: $.extend({}, self.defaultOptions, options),

			// Require chain automatically finalizes itself after
			// 300ms if no promise methods were called in the require chain.
			// Set false to disable.
			autoFinalizeDuration: 300,

			// When batch is finalized, further loader calls will be ignored.
			finalized: false,

			// Determine if the contents of the loaded task is required.
			required: function(fn) {
				if (fn===true) isRequired=true && required.fire();
				if ($.isFunction(fn)) required.add(fn);
				return isRequired;
			}
		});

		return batch;
	}

	$.extend(Batch.prototype, {

		addTask: function(task) {

			var batch = this;

			// Don't add invalid tasks.
			// Tasks should be a deferred object.
			if (!$.isDeferred(task)) return;

			// Don't accept anymore tasks if this batch is finalized.
			// Batch is finalized upon calling any of the promises, e.g.
			// done, fail, progress, always, then, pipe
			if (batch.finalized) return;

			// Add this task to the batch's task list
			batch.tasks.push(task);

			// Decorate task with a reference to the current batch
			task.batch = batch;
		},

		autoFinalize: function() {

			var batch = this,
				duration = batch.autoFinalizeDuration;

			// If autoFinalize is disabled, stop.
			if (duration===false) return;

			// Clear previous timer
			clearTimeout(batch.autoFinalizeTimer);

			// Start a new timer
			batch.autoFinalizeTimer = 
				setTimeout(function(){
					batch.finalize();
				}, duration);
		},

		finalize: function() {

			var batch = this;

			// If this batch has been finalized, stop.
			if (batch.finalized) return;

			// Finalize all tasks so no further
			// tasks can be added to this batch.
			batch.finalized = true;

			// Create batch manager which is a
			// master deferred object for all tasks.
			var manager = batch.manager = $.when.apply(null, batch.tasks);

			// Now that tasks are finalized, we can override
			// this batch's pseudo-promise methods with actual
			// promise methods from batch manager.
			var promise  = manager.promise(),
				progress = $.Callbacks();

			$.extend(batch, promise, {

				// Progress & notify method behaves differently.
				// We want progress callback to continue executing
				// even after after manager has been resolved or rejected.
				progress: progress.add,
				notify  : progress.fire,

				// Done method also behaves differently.
				// It will trigger an event notifying all tasks that
				// there is a demand for the content of the task.
				// This is currently used to lazy execute module factories
				// to ensure they don't execute until they are asked for.
				done: function(){

					// Trigger required event
					batch.required(true);

					// After done has been called once, it will be
					// replaced with the actual done method from the
					// master deferred object.
					batch.done = promise.done;

					// And the actual done method gets executed.
					return batch.done.apply(batch, arguments);
				}
			});

			// Flag to indicate whether to make
			// generate debug messages.
			var verbose = batch.options.verbose;

			manager
				.progress(function(state, task){
					if (verbose && state=="rejected") {
						console.warn('Require: Task ' + task.name + ' failed to load.', task);
					}
				})
				.fail(function(){
					if (verbose) {
						console.warn('Require: Batch ' + batch.id + ' failed.', batch);
					}
				});

			// We wrap this in a setTimeout to let existing require chain
			// to continue execute. This ensures that progress call in that
			// require chain receives the activities of each task below.
			setTimeout(function(){

				// Always notify whenever there is an activity on every task.
				$.each(batch.tasks, function(i, task){
					task.then(
						function(){ batch.notify("resolved", task) },
						function(){ batch.notify("rejected", task) },
						function(){ batch.notify("progress", task) }
					);
				});
			}, 1);
		},

		expand: function(args, opts) {

			var args = $.makeArray(args),
				options = opts || {},
				names = [];

	        if ($.isPlainObject(args[0])) {
	            options = $.extend(args[0], opts);
	            names = args.slice(1);
	        } else {
	            names = args;
	        }

	        return {
	        	options: options,
	        	names: names
	        }
		}
	});

	// Masquerade newly created batch instances as a pseudo-promise object
	// until one of those promise's method is called. This is to ensure that
	// no callbacks are fired too early until all require tasks are finalized.
	$.each(["done","fail","progress","always","then"], function(i, method) {

		Batch.prototype[method] = function() {

			var batch = this;

			// Finalize batch
			batch.finalize();

			// Execute method that was originally called
			return batch[method].apply(batch, arguments);
		}
	});

	return self;

})();
/**
 * jquery.require.script
 * Script loader plugin for $.require.
 *
 * Part of jquery.require family.
 * https://github.com/jstonne/jquery.require
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.require.addLoader('script', (function() {

	// IE & Opera thinks punycoded urls are cross-domain requests,
	// and rejects the ajax request because they think they don't have
	// the necesary transport to facilitate such requests.

	var ajaxHost = $.uri($.indexUrl).host(),
		documentHost = $.uri(document.location.href).host();

	if (ajaxHost!==documentHost && ajaxHost.match("xn--")) {
		$.support.cors = true;
	}

	var canAsync = document.createElement("script").async === true || "MozAppearance" in document.documentElement.style || window.opera;

	var self = function() {

		var batch = this,
			args = $.makeArray(arguments),
			options,
			names;

		// Expand arguments into its actual definition
		if ($.isPlainObject(args[0])) {
			options = args[0];
			names = args.slice(1);
		} else {
			names = args;
		}

		options = $.extend(
			{},
			self.defaultOptions,
			batch.options,
			options,
			{batch: batch}
		);

		// Create tasks and add it to the batch.
		var taskBefore;

		$.each(names, function(i, name) {

			var task = new self.task(name, options, taskBefore);

			batch.addTask(task);

			// Serial script loading
			if (options.serial && taskBefore!==undefined) {

				// Only start current task when the
				// task before is resolved/rejected.
				taskBefore.always(task.start);

			} else {

				task.start();
			}

			taskBefore = task;

		});

	};

	$.extend(self, {

		defaultOptions: {
			// Overrides require path.
			path: '',

			extension: (($.mode=='compressed') ? 'min.js' : 'js'),

			// Serial script loading. Default: Parallel script loading.
			serial: false,

			// Asynchronous script execution. Default: Synchronous script execution.
			async: false,

			// Use XHR to load script. Default: Script injection.
			xhr: false
		},

		setup: function() {

			$.extend(self.defaultOptions, options);
		},

		scripts: {},

		task: function(name, options, taskBefore) {

			var task = $.extend(this, $.Deferred());

			task.name = name;

			task.options = options;

			task.taskBefore = taskBefore;

			// Module assignment or module url override
			if ($.isArray(name)) {

				task.name = name[0] + "@" + name[1];

				task.moduleName = name[0];

				var overrideModuleUrl = name[2];

				// Module assignment
				if (!overrideModuleUrl) {

					// Set module flag
					task.defineModule = true;

					// Raise a warning if the module already exist
					if ($.module.registry[task.moduleName]) {
						console.warn("$.require.script: " + task.moduleName + ' exists! Using existing module instead.');
					}

					// Use XHR for module assignments
					task.options.xhr = true;
				}

				// Assign path to be resolved
				name = name[1];

				task.module = $.module(task.moduleName);
			}

			// Resolve name to paths

			// Absolute path
			if ($.isUrl(name)) {

				task.url = name;

			// Relative path
			} else if (/^(\/|\.)/.test(name)) {

				task.url = $.uri(task.options.path)
							.toPath(name)
							.toString();

			// Module path
			} else {

				task.url = $.uri(task.options.path)
							.toPath('./' + name + '.' + task.options.extension)
							.toString();

				task.module = $.module(name);
			}
		}

	});

	$.extend(self.task.prototype, {

		start: function() {

			var task = this,
				module = task.module;

			// If module has already been loaded,
			// we can skip the whole script loading process.
			if (module && module.status!=="pending") {
				task.waitForModule();
				return;
			}

			// Else load the script that has this module.
			task.load();
		},

		waitForModule: function() {

			var task = this,
				module = task.module;

			// Listen to the events in the module
			// without causing the module factory to execute.
			module.then(
				task.resolve,
				task.reject,
				task.notify
			);

			// When there is demand for this module,
			// we will call the module's done method.
			task.batch.required(function(){

				// This will execute the module factory
				// in case it wasn't executed before.
				module.done(task.resolve);
			});
		},

		load: function() {

			var task = this,
				taskBefore = task.taskBefore,
				options = {};

			// Use previously created script instance if exists,
			// else create a new one.
			task.script = self.scripts[task.url] || (function() {

				var script = (task.options.xhr) ?

					// Load script via ajax.
					$.ajax({

						url: task.url,

						dataType: "text"

					}) :

					// Load script using script injection.
					$.script({

						url: task.url,

						type: "text/javascript",

						async: task.options.async,

						timeout: task.batch.options.timeout,

						retry: task.batch.options.retry,

						verbose: task.batch.options.verbose

					});

				return self.scripts[task.url] = script;

			})();

			// At this point, script may be loaded, BUT may yet
			// to be executed under the following conditions:
			// - Module loaded via script injection/xhr.
			// - Script loaded via via xhr.
			task.script
				.done(function(data) {

					var resolveTask = function() {

						// If task loads a module, resolve/reject task only when
						// the module is resolved/rejected as the module itself
						// may perform additional require tasks.
						if (task.module) {

							task.waitForModule();

						} else {

							task.resolve();
						}
					};

					if (task.options.xhr) {

						if (task.defineModule) {

							// Create our own module factory
							task.module = $.module(task.moduleName, function() {

								var module = this;

								$.globalEval(data);

								module.resolveWith(data);
							});
						};

						// For XHR, if scripts needs to be executed synchronously
						// a.k.a. ordered script execution, then only eval it when
						// the task before it is resolved.
						if (!task.options.async || taskBefore) {

							taskBefore.done(function() {

								$.globalEval(data);

								resolveTask();

							});

							return;
						}

					};

					resolveTask();

				})
				.fail(function() {

					task.reject();
				});
		}
	});

	return self;

})()
);

/**
 * jquery.require.library
 * Foundry script loader.
 *
 * Copyright (c) 2011 Jason Ramos
 * www.stackideas.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.require.addLoader('library', function() {

	var batch = this,
		args = $.makeArray(arguments),
		options = {},
		names;

	// Expand arguments into its actual definition
	if ($.isPlainObject(args[0])) {
		options = args[0];
		names = args.slice(1);
	} else {
		names = args;
	}

	$.extend(options, {
		path: $.scriptPath
	});

	return batch.script.apply(batch, [options].concat(names));

});
/**
 * jquery.require.stylesheet
 * Stylesheet loader plugin for $.require.
 *
 * Part of jquery.require family.
 * https://github.com/jstonne/jquery.require
 *
 * Copyright (c) 2012 Jensen Tonne
 * www.jstonne.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

$.require.addLoader('image', (function() {

	var self = function() {

		var batch = this,
			args = $.makeArray(arguments),
			options,
			names;

		// Expand arguments into its actual definition
		if ($.isPlainObject(args[0])) {
			options = args[0];
			names = args.slice(1);
		} else {
			names = args;
		}

		options = $.extend(
			{},
			self.defaultOptions,
			batch.options,
			options,
			{batch: batch}
		);

		$.each(names, function(i, name) {

			var task = new self.task(name, options),
				existingTask = self.images[task.url];

			task = existingTask || task;

			batch.addTask(task);

			if (!existingTask) {
				self.images[task.url] = task;
				task.start();
			}
		});
	};

	$.extend(self, {

		defaultOptions: {
			// Overrides require path.
			path: ''
		},

		setup: function() {

			$.extend(self.defaultOptions, options);
		},

		images: {},

		task: function(name, options) {

			var task = $.extend(this, $.Deferred());

			task.name = name;

			task.options = options;

			// Absolute path
			if ($.isUrl(name)) {

				task.url = name;

			// Relative path
			} else if (/^(\/|\.)/.test(name)) {

				task.url = $.uri(task.options.path)
							.toPath(name)
							.toString();

			// Module path
			} else {

				task.url = $.uri(task.options.path)
							.toPath('./' + name)
							.toString();
			}

			// Remap task.url to task.options.url
			task.options.url = task.url;
		}

	});

	$.extend(self.task.prototype, {

		start: function() {

			var task = this;

			task.image = $(new Image())
							.load(function(){
								task.resolve();
							})
							.error(function(){
								task.reject();
							})
							.attr("src", task.options.url);
		}

	});

	return self;

})()
);

});KTVendors.plugin("server", function($) {

/*!
 * jquery.server.
 * Extension of jquery.ajax with ability to parse server commands.
 *
 * Copyright (c) 2011 Jason Ramos
 * www.stackideas.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
var self = $.server = function(options) {

	var request = $.Deferred(),

		ajaxOptions = $.extend(true, {}, self.defaultOptions, options, {success: function(){}}),

		xhr = request.xhr =
			$.Ajax(ajaxOptions)
				.done(function(commands){

					if (typeof commands==="string") {
						try {
							commands = $.parseJSON(commands);
						} catch(e) {
							request.rejectWith(request, ["Unable to parse Ajax commands.", "error"])
						}
					}

					if (!$.isArray(commands)) {

						request.rejectWith(request, ["Invalid ajax commands.", "error"]);

					} else {

						var parse = function(command){
							var type = command.type,
								parser = self.parsers[type] || options[type];

							if ($.isFunction(parser)) {
								return parser.apply(request, command.data);
							}
						}

						// Execute all the notifications first
						var commands = $.map(commands, function(command) {
							if (command.type=="notify") {
								parse(command);
							} else {
								return command;
							}
						})

						$.each(commands, function(i, command) {
							parse(command);
						});
					}

					// If server did not resolve this request
					if (request.state()==="pending") {

						// We'll resolve it ourselves
						request.resolveWith(request);
					}
				})
				.fail(function(jqXHR, status, statusText){

					request.rejectWith(request, [statusText, status]);
				});

		// Add abort method
		request.abort = xhr.abort;

	return request;
};

self.defaultOptions = {
	type: 'POST',
	data: {
		tmpl: 'component',
		format: 'ajax',
		no_html: 1
	},
	cache: false,
	contentType: 'application/x-www-form-urlencoded',
	dataType: 'json'
};

self.parsers = {

	script: function() {

		var data = $.makeArray(arguments);

		// For hardcoded javascript codes
		if (typeof data[0] == 'string') {
			try { eval(data[0]) } catch(err) {};
			return;
		}

		/**
		* Execute each method and assign returned object back to the chain.
		*
		* Foundry().attr('checked', true);
		* 	is equivalent to:
		* window['Foundry']('.element')[attr]('checked', true);
		*/
		var chain = window, chainBroken = false;

		$.each(data, function(i, chainer)
		{
			if (chainer.property==="Foundry") {
				chainer.property = $.globalNamespace;
			}

			if (chainer.method==="Foundry") {
				chainer.method = $.globalNamespace;
			}

			try {
				switch(chainer.type)
				{
					case 'get':
						chain = chain[chainer.property];
						break;

					case 'set':
						chain[chainer.property] = chainer.value;
						chainBroken=true;
						break;

					case 'call':
						chain = chain[chainer.method].apply(chain, chainer.args);
						break;
				}
			} catch(err) {
				chainBroken = true;
			}
		})
	},

	resolve: function() {

		this.resolveWith(this, arguments);
	},

	reject: function() {

		this.rejectWith(this, arguments);
	},

	notify: function() {

		this.notifyWith(this, arguments);
	},

	redirect: function(url) {

		window.location = url;
	}
};
});KTVendors.plugin("component", function($) {

/**
 * jquery.component.
 * Boilerplate for client-side MVC application.
 *
 * Copyright (c) 2011 Jason Ramos
 * www.stackideas.com
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

var Component = $.Component = function(name, options) {

	if (arguments.length < 1) {
		return Component.registry;
	}

	if (arguments.length < 2) {
		return Component.registry[name];
	}

	return Component.register(name, options);
}

Component.registry = {};

Component.proxy = function(component, property, value) {

	// If it's a method
	if ($.isFunction(value)) {

		// Change the "this" context to the component itself
		component[property] = $.proxy(value, component);

	} else {

		component[property] = value;
	}
}

Component.register = function(name, options) {

	// If an abstract component was passed in
	var abstractComponent;

	// Normalize arguments
	if ($.isFunction(name)) {
		abstractComponent = name;
		name = abstractComponent.className;
		options = abstractComponent.options;
	}

	var self =

		// Put it in component registry
		Component.registry[name] =

		// Set it to the global namespace
		window[name] =

		// When called as a function, it will return the correct jQuery object.
		function(command) {

			return ($.isFunction(command)) ? command($) : component;
		};

	// Extend component with properties in component prototype
	$.each(Component.prototype, function(property, value) {

		Component.proxy(self, property, value);
	});


	self.$ = $;
	self.options = options;
	self.className = name;
	self.identifier = 'komento';
	self.componentName = "com_komento";
	self.prefix = self.identifier + "/";
	self.version = options.version;
	self.safeVersion = self.version.replace(/\./g,"");
	self.environment = options.environment  || $.environment;
	self.mode = options.mode || $.mode;
	self.debug = (self.environment==='development');
	self.console = Component.console(self);
	self.language = options.language || $.locale.lang || "en";
	self.baseUrl = options.baseUrl || $.indexUrl + "?option=" + self.componentName;
	self.ajaxUrl = options.ajaxUrl || $.basePath + "/?option=" + self.componentName;
	self.scriptPath = options.scriptPath || $.rootPath + "/media/" + self.componentName + "/scripts/";

	// Legacy and needs to be removed
	self.stylePath = options.stylePath    || $.rootPath + "/media/" + self.componentName + "/styles/";
	self.templatePath = options.templatePath || options.scriptPath;
	self.languagePath = options.languagePath || self.ajaxUrl + '&tmpl=component&no_html=1&controller=lang&task=getLanguage';
	self.viewPath = options.viewPath     || self.ajaxUrl + '&tmpl=component&no_html=1&controller=themes&task=getAjaxTemplate';
	self.optimizeResources = true;
	self.resourcePath = options.resourcePath || self.ajaxUrl + '&tmpl=component&no_html=1&controller=foundry&task=getResource';
	self.resourceInterval = 1200; // Joomla session timestamp is per second, we add another 200ms just to be safe.
	
	self.scriptVersioning  = options.scriptVersioning || false;
	self.tasks = [];

	// Register component to bootleader
	KTVendors.component(name, self);

	// If there's no abstract componet prior to this, we're done!
	if (!abstractComponent) {
		return;
	}

	// If we're on development mode
	if (self.debug) {

		// Execute queue in abstract component straightaway
		abstractComponent.queue.execute();

	// If we're on static or optimized mode
	} else {

		// Get component installers from bootloader and install them
		var installer, installers = KTVendors.installer(name);
		while(installer = installers.shift()) {
			self.install.apply(self, installer);
		}

		// Wait until definitions, scripts & resources are installed
		$.when(
			self.install("definitions"),
			self.install("scripts"),
			self.install("resources")
		).done(function(){

			// Then only execute queue in abstract component.
			abstractComponent.queue.execute();
		});
	}
}

Component.extend = function(property, value) {

	// For later components
	Component.prototype[property] = value;

	// For existing components
	$.each(Component.registry, function(name, component) {
		Component.proxy(component, property, value);
	});
}

$.template("component/console",'<div id="[%== component.identifier %]-console" class="foundry-console" style="display: none; z-index: 999999;"><div class="console-header"><div class="console-title">[%= component.className %] [%= component.version %]</div><div class="console-remove-button">x</div></div><div class="console-log-item-group"></div><style type="text/css">.foundry-console{position:fixed;width:50%;height:50%;bottom:0;left:0;background:white;box-shadow: 0 0 5px 0;margin-left: 25px;}.console-log-item-group{width: 100%;height: 100%;overflow-y:scroll;}.console-header{position: absolute;background:red;color:white;font-weight:bold;top:-24px;left: 0;line-height:24px;width:100%}.console-remove-button{text-align:center;cursor: pointer;display:block;width: 24px;float:right}.console-remove-button:hover{color: yellow}.console-title{padding: 0 5px;float:left}.console-log-item{padding: 5px}.console-log-item + .console-log-item{border-top: 1px solid #ccc}</style></div>');

Component.console = function(component) {

	return (function(self){

		var instance = function(method) {

				if (arguments.length < 1) {
					return instance.toggle();
				}

				return instance[method] && instance[method].apply(instance, arguments);
			},

			element;

			instance.selector = "#" + self.identifier + "-console";

			instance.init = function() {

				element = $(instance.selector);

				if (element.length < 1) {
					element = $($.View("component/console", {component: self})).appendTo("body");

					element.find(".console-remove-button").click(function(){
						element.hide();
					});
				}

				instance.element = element;

				return arguments.callee;
			};

			instance.methods = {

				log: function(message, type, code) {

					type = type || "info";

					var itemGroup = element.find(".console-log-item-group"),
						item =
							$(document.createElement("div"))
								.addClass("console-log-item type-" + type)
								.attr("data-code", code)
								.html(message);

					itemGroup.append(item);
					itemGroup[0].scrollTop = itemGroup[0].scrollHeight;

					// Automatically show window on each log
					if (self.debug) { element.show(); }
				},

				toggle: function() {
					element.toggle();
				},

				reset: function() {
					element.find(".console-log-item-group").empty();
				}
			};

		$.each(instance.methods, function(method, fn) {
			instance[method] = function() {
				instance.init(); // Always call init in case of destruction of element
				return fn.apply(instance, arguments);
			}
		});

		return instance;

	})(component);
}

var doc = $(document),
	proto = Component.prototype;

$.extend(proto, {

	run: function(command) {

		return ($.isFunction(command)) ? command($) : this;
	},

	ready: (function(){

		// Replace itself once document is ready
		doc.ready(function(){
			proto.ready = proto.run;
		});

		return function(callback) {

			if (!$.isFunction(callback)) return;

			// When document is ready
			doc.ready(function() {
				callback($);
			});
		}
	})(),

	install: function(name, factory) {

		var self = this,
			task = self.tasks[name] || (self.tasks[name] = $.Deferred());

		// Getter
		if (!factory) return task;

		// Setter
		var install = function(){
			factory($, self);
			return task.resolve();
		}

		// If this is installer contains component definitions,
		// install straightaway.
		if (name=="definitions") return install();

		// Else for component definitiosn to install first,
		// then only install this installer.
		$.when(self.install("definitions")).done(install);
	},

	token: function() {

		var self = this;

		if (self.token.value) {
			return self.token.value;
		}

		return self.token.value = window.kt.token;
	},

	template: function(name) {

		var self = this;

		// Get all component templates
		if (name==undefined) {

			return $.grep($.template(), function(template) {

				return template.indexOf(self.prefix)==0;
			});
		}

		// Prepend component prefix
		arguments[0] = self.prefix + name;

		// Getter or setter
		return $.template.apply(null, arguments);
	},

	// Component require extends $.require with the following additional methods:
	// - resource()
	// - view()
	// - language()
	//
	// It also changes the behaviour of existing methods to load in component-specific behaviour.
	require: function(options) {

		var self = this,

			options = options || {},

			require = $.require(options),

			_require = {};

			// Keep a copy of the original method so the duck punchers below can use it.
			$.each(["library", "script", "template", "done"], function(i, method){
				_require[method] = require[method];
			});

		// Resource call should NOT be called directly.
		// .resource({type: "view", name: "photo.item", loader: deferredObject})
		require.resource = function(resource) {

			// If this is not a valid resource object, skip.
			if (!$.isPlainObject(resource)) return;
			if (!resource.type || !resource.name || !$.isDeferred(resource.loader)) return;

			var batch = this;

			// Get resource collector
			var resourceCollector = self.resourceCollector;

			// If we haven't started collecting resources
			if (!resourceCollector) {

				// Then start collecting resources
				resourceCollector = self.resourceCollector = $.Deferred();

				$.extend(resourceCollector, {

					name: $.uid("ResourceCollector"),

					manifest: [],

					loaderList: [],

					loaders: [],

					load: function() {

						// End this batch of resource collecting
						delete self.resourceCollector;

						// If there are not resources to pull,
						// just resolve resource collector.
						if (resourceCollector.manifest.length < 0) {
							resourceCollector.resolve();
							return;
						}

						var retry = 0;

						var loadResources = function(){

							retry++;

							$.Ajax(
								{
									type: 'POST',
									url: self.resourcePath,
									dataType: "json",
									data: {
										resource: resourceCollector.manifest
									}
								})
								.done(function(manifest) {

									if (!$.isArray(manifest)) {
										resourceCollector.reject("Server did not return a valid resource manifest.");
										return;
									}

									$.each(manifest, function(i, resource) {

										var content = resource.content;

										resourceCollector.loaders[resource.id]
											[content!==undefined ? "resolve" : "reject"]
											(content);
									});

									if (retry > 1 && self.debug) {
										console.info("Attempt to try and get resources again was successful!");
									}
								})
								.fail(function(){
									if (retry > 2) {
										if (self.debug) { console.error("Unable to get resource again. Giving up!"); };
										return;
									}
									if (self.debug) {
										console.warn("Unable to get resource. Trying again...");
									}
									loadResources();
								});
						}

						loadResources();

						// Resolve resource collector when all is done
						$.when.apply(null, resourceCollector.loaderList)
							.done(resourceCollector.resolve)
							.fail(resourceCollector.reject);
					}
				});

				setTimeout(resourceCollector.load, self.resourceCollectionInterval);
			}

			// Create a resource id
			var id = resource.id = $.uid("Resource");

			// Add to the loader map
			// - to be used to resolve the loader with the returned content
			resourceCollector.loaders[id] = resource.loader;

			// Add to the loader list
			// - to be used with $.when()
			resourceCollector.loaderList.push(resource.loader);

			// Remove the reference to the loader
			// - so the loader doesn't get included in the manifest that gets sent to the server
			delete resource.loader;

			// Then add it to our list of resource manifest
			resourceCollector.manifest.push(resource);

			// Note: Only resource loaders are batch tasks, not resource collectors.
			// var task = resourceCollector;
			// batch.addTask(task);
			return require;
		};

		require.view = function() {

			var batch   = this,

				request = batch.expand(arguments, {path: self.viewPath}),

				loaders = {},

				options = request.options,

				names   = $.map(request.names, function(name) {

					// Get template loader
					var absoluteName = self.prefix + name,
						loader = $.require.template.loaders[absoluteName];

					// If this is being loaded, skip.
					if (loader) return;

					loader = $.require.template.loader(absoluteName);

					loader.name = absoluteName;

					// Add template loader as a task of this batch
					batch.addTask(loader);

					// Load as part of a coalesced ajax call if enabled
					if (self.optimizeResources) {

						require.resource({
							type: "view",
							name: name,
							loader: loader
						});

						return;

					} else {

						loaders[name] = loader;
						return name;
					}
				});

			// Load using regular ajax call
			// This will always be zero when optimizeResources is enabled.
			if (names.length > 0) {

				$.Ajax(
					{
						url: options.path,
						dataType: "json",
						data: { names: names }
					})
					.done(function(templates) {

						if (!$.isArray(templates)) return;

						$.each(templates, function(i, template) {

							var content = template.content;

							loaders[template.name]
								[content!==undefined ? "resolve" : "reject"]
								(content);
						});
					});
			}

			return require;
		};

		require.library = function() {

			_require.script.apply(this, arguments);

			return require;
		};

		require.script = function() {

			var batch = this,

				request = batch.expand(arguments, {path: self.scriptPath}),

				names = $.map(request.names, function(name) {

					// Ignore module definitions
					if ($.isArray(name) ||

						// and urls
						$.isUrl(name) ||

						// and relative paths.
						/^(\/|\.)/.test(name)) return name;

					var moduleName = self.prefix + name,

						moduleUrl =

							$.uri(request.options.path)
								.toPath(
									'./' + name + '.' + (request.options.extension || 'js') +
									((self.scriptVersioning) ? "?" + "version=" + self.safeVersion : "")
								)
								.toString();

					return [[moduleName, moduleUrl, true]];
				});

			_require.script.apply(require, [request.options].concat(names));

			return require;
		};

		// Override path
		require.template = function() {

			var batch   = this,

				request = batch.expand(arguments, {path: self.templatePath});

			_require.template.apply(require, [request.options].concat(

				$.map(request.names, function(name) {

					return [[self.prefix + name, name]];
				})
			));

			return require;
		};

		require.app = function() {

			var batch = this,

				request = batch.expand(arguments, {path: self.scriptPath})

				names = $.map(request.names, function(name) {

					// Ignore module definitions
					if ($.isArray(name) ||

						// and urls
						$.isUrl(name) ||

						// and relative paths.
						/^(\/|\.)/.test(name)) return name;

					var parts = name.split('/'),
						path = $.rootPath + '/media/' + self.componentName + '/apps';

					// Currently used by fields
					if (parts.length===4) {
						path += '/' + parts.shift();
					}

					// Build path
					path += '/' + parts[0] + '/' + parts[1] + '/scripts/' + parts[2];

					var moduleName = self.prefix + name,

						moduleUrl = path + '.' +
							(request.options.extension || 'js') +
							((self.scriptVersioning) ? "?" + "version=" + self.safeVersion : "");

					return [[moduleName, moduleUrl, true]];
				});

			_require.script.apply(require, [request.options].concat(names));

			return require;
		};

		// Only execute require done callback when component is ready
		require.done = function(callback) {

			return _require.done.call(require, function(){

				self.ready(callback);
			});
		};

		return require;
	},

	module: function(name, factory) {

		var self = this;

		// TODO: Support for multiple module factory assignment
		if ($.isArray(name)) {
			return;
		}

		var fullname = self.prefix + name;

		return (factory) ?

			// Set module
			$.module.apply(null, [fullname, function(){

				var module = this;

				factory.call(module, $);
			}])

			:

			// Get module
			$.module(fullname);
	}
});
$.Component.extend("ajax", function(namespace, params, callback) {

	var self = this;
	var date = new Date();

	var options = {
			url: self.ajaxUrl + "&_ts=" + date.getTime(),
			data: $.extend(
				params, {
					option: self.componentName,
					namespace: namespace
				}
			)
		};

	options = $.extend(true, options, self.options.ajax);
	options.data[self.token()] = 1;

	// This is for server-side function arguments
	if (options.data.hasOwnProperty('args')) {
		options.data.args = $.toJSON(options.data.args);
	}

	if ($.isPlainObject(callback)) {

		if (callback.type) {

			switch (callback.type) {

				case 'jsonp':

					callback.dataType = 'jsonp';

					// This ensure jQuery doesn't use XHR should it detect the ajax url is a local domain.
					callback.crossDomain = true;

					options.data.transport = 'jsonp';
					break;

				case 'iframe':

					// For use with iframe-transport
					callback.iframe = true;

					callback.processData = false;

					callback.files = options.data.files;

					delete options.data.files;

					options.data.transport = 'iframe';
					break;
			}

			delete callback.type;
		}

		$.extend(options, callback);
	}

	if ($.isFunction(callback)) {
		options.success = callback;
	}

	var ajax = $.server(options);

	ajax.progress(function(message, type, code) {
		if (self.debug && type=="debug") {
			self.console.log(message, type, code);
		}
	});

	return ajax;
});

$.Component.extend("Controller", function() {

	var self = this,
		args = $.makeArray(arguments),
		name = args[0],
		staticProps,
		protoFactory;

	// Getter
	if (args.length==1) {
		return $.String.getObject(name);
	};

	// Setter
	if (args.length > 2) {
		staticProps = args[1],
		protoFactory = args[2]
	} else {
		staticProps = {},
		protoFactory = args[1]
	}

	// Map component as a static property
	// of the controller class
	$.extend(staticProps, {
		root: self.className + '.Controller',
		component: self
	});

	return $.Controller.apply(this, [name, staticProps, protoFactory]);
});


$.Component.extend("View", function(name) {

	var self = this;

	// Gett all component views
	if (arguments.length < 1) {
		return self.template();
	}

	// Prepend component prefix
	arguments[0] = self.prefix + arguments[0];

	// Getter or setter
	return $.View.apply(this, arguments);
});
// Component should always be the last core plugin to load.

// Execute all pending foundry modules
KTVendors.module.execute();

// Get all abstract components
$.each(KTVendors.component(), function(i, abstractComponent){

	// If this component is registered, stop.
	if (abstractComponent.registered) return;

	// Create an instance of the component
	$.Component.register(abstractComponent);
});

});KTVendors.plugin("static", function($) {
	$.module(["datetimepicker","dialog","expanding","markitup","moment","plupload","raty","scrollTo","ui\/autocomplete","ui\/core","ui\/menu","ui\/position","ui\/widget"]);

	// Now we need to retrieve the contents of each files
			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
$.require() 
 .script("moment") 
 .done(function() { 
var exports = function() { 

/*
Version 3.0.0
=========================================================
bootstrap-datetimepicker.js
https://github.com/Eonasdan/bootstrap-datetimepicker
=========================================================
The MIT License (MIT)

Copyright (c) 2014 Jonathan Peterson

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

	var dpgId = 0,

	pMoment = $.moment,

// ReSharper disable once InconsistentNaming
	DateTimePicker = function (element, options) {
		var defaults = {
			pickDate: true,
			pickTime: true,
			useMinutes: true,
			useSeconds: false,
			useCurrent: true,
			minuteStepping: 1,
			minDate: new pMoment({ y: 1900 }),
			maxDate: new pMoment().add(100, "y"),
			showToday: true,
			collapse: true,
			language: "en",
			defaultDate: "",
			disabledDates: false,
			enabledDates: false,
			icons: {},
			useStrict: false,
			direction: "auto",
			sideBySide: false,
			daysOfWeekDisabled: false,
			component: "",
			dow: 0
		},

		icons = {
			time  : 'fa fa-clock-o',
			date  : 'fa fa-calendar',
			up    : 'fa fa-chevron-up',
			down  : 'fa fa-chevron-down'
		},

		picker = this,

		init = function () {

			var icon = false, i, dDate, longDateFormat;
			picker.options = $.extend({}, defaults, options);
			picker.options.icons = $.extend({}, icons, picker.options.icons);

			picker.element = $(element);

			dataToOptions();

			if (!(picker.options.pickTime || picker.options.pickDate))
				throw new Error('Must choose at least one picker');

			picker.id = dpgId++;
			pMoment.lang(picker.options.language);
			picker.date = pMoment();
			picker.unset = false;
			picker.isInput = picker.element.is('input');
			picker.component = false;

			// jasonrey@stackideas.com
			// Set the start of week
			pMoment()._lang._week.dow = picker.options.dow;

			if (picker.element.hasClass('input-group')) {
				if (picker.element.find('.datepickerbutton').size() == 0) {//in case there is more then one 'input-group-addon' Issue #48
					picker.component = picker.element.find("[class^='input-group-']");
				}
				else {
					picker.component = picker.element.find('.datepickerbutton');
				}
			}
			picker.format = picker.options.format;

			longDateFormat = pMoment()._lang._longDateFormat;

			if (!picker.format) {
				picker.format = (picker.options.pickDate ? longDateFormat.L : '');
				if (picker.options.pickDate && picker.options.pickTime) picker.format += ' ';
				picker.format += (picker.options.pickTime ? longDateFormat.LT : '');
				if (picker.options.useSeconds) {
					if (~longDateFormat.LT.indexOf(' A')) {
						picker.format = picker.format.split(" A")[0] + ":ss A";
					}
					else {
						picker.format += ':ss';
					}
				}
			}
			picker.use24hours = picker.format.toLowerCase().indexOf("a") < 1;

			if (picker.component) icon = picker.component.find('span');

			if (picker.options.pickTime) {
				if (icon) icon.addClass(picker.options.icons.time);
			}
			if (picker.options.pickDate) {
				if (icon) {
					icon.removeClass(picker.options.icons.time);
					icon.addClass(picker.options.icons.date);
				}
			}

			picker.widget = $(getTemplate()).appendTo('body');

			if (picker.options.useSeconds && !picker.use24hours) {
				picker.widget.width(300);
			}

			picker.minViewMode = picker.options.minViewMode || 0;
			if (typeof picker.minViewMode === 'string') {
				switch (picker.minViewMode) {
					case 'months':
						picker.minViewMode = 1;
						break;
					case 'years':
						picker.minViewMode = 2;
						break;
					default:
						picker.minViewMode = 0;
						break;
				}
			}
			picker.viewMode = picker.options.viewMode || 0;
			if (typeof picker.viewMode === 'string') {
				switch (picker.viewMode) {
					case 'months':
						picker.viewMode = 1;
						break;
					case 'years':
						picker.viewMode = 2;
						break;
					default:
						picker.viewMode = 0;
						break;
				}
			}

			picker.options.disabledDates = indexGivenDates(picker.options.disabledDates);
			picker.options.enabledDates = indexGivenDates(picker.options.enabledDates);

			picker.startViewMode = picker.viewMode;
			picker.setMinDate(picker.options.minDate);
			picker.setMaxDate(picker.options.maxDate);
			fillDow();
			fillMonths();
			fillHours();
			fillMinutes();
			fillSeconds();
			update();
			showMode();
			attachDatePickerEvents();
			if (picker.options.defaultDate !== "" && getPickerInput().val() == "") picker.setValue(picker.options.defaultDate);
			if (picker.options.minuteStepping !== 1) {
				var rInterval = picker.options.minuteStepping;
				picker.date.minutes((Math.round(picker.date.minutes() / rInterval) * rInterval) % 60).seconds(0);
			}
		},

		getPickerInput = function () {
			if (picker.isInput) {
				return picker.element;
			} else {
				return dateStr = picker.element.find('input');
			}
		},

		dataToOptions = function () {
			var eData
			if (picker.element.is('input')) {
				eData = picker.element.data();
			}
			else {
				eData = picker.element.data();
			}
			if (eData.dateFormat !== undefined) picker.options.format = eData.dateFormat;
			if (eData.datePickdate !== undefined) picker.options.pickDate = eData.datePickdate;
			if (eData.datePicktime !== undefined) picker.options.pickTime = eData.datePicktime;
			if (eData.dateUseminutes !== undefined) picker.options.useMinutes = eData.dateUseminutes;
			if (eData.dateUseseconds !== undefined) picker.options.useSeconds = eData.dateUseseconds;
			if (eData.dateUsecurrent !== undefined) picker.options.useCurrent = eData.dateUsecurrent;
			if (eData.dateMinutestepping !== undefined) picker.options.minuteStepping = eData.dateMinutestepping;
			if (eData.dateMindate !== undefined) picker.options.minDate = eData.dateMindate;
			if (eData.dateMaxdate !== undefined) picker.options.maxDate = eData.dateMaxdate;
			if (eData.dateShowtoday !== undefined) picker.options.showToday = eData.dateShowtoday;
			if (eData.dateCollapse !== undefined) picker.options.collapse = eData.dateCollapse;
			if (eData.dateLanguage !== undefined) picker.options.language = eData.dateLanguage;
			if (eData.dateDefaultdate !== undefined) picker.options.defaultDate = eData.dateDefaultdate;
			if (eData.dateDisableddates !== undefined) picker.options.disabledDates = eData.dateDisableddates;
			if (eData.dateEnableddates !== undefined) picker.options.enabledDates = eData.dateEnableddates;
			if (eData.dateIcons !== undefined) picker.options.icons = eData.dateIcons;
			if (eData.dateUsestrict !== undefined) picker.options.useStrict = eData.dateUsestrict;
			if (eData.dateDirection !== undefined) picker.options.direction = eData.dateDirection;
			if (eData.dateSidebyside !== undefined) picker.options.sideBySide = eData.dateSidebyside;
		},

		place = function () {
			var position = 'absolute',
			offset = picker.component ? picker.component.offset() : picker.element.offset(), $window = $(window);
			picker.width = picker.component ? picker.component.outerWidth() : picker.element.outerWidth();
			offset.top = offset.top + picker.element.outerHeight();

			var placePosition;
			if (picker.options.direction === 'up') {
				placePosition = 'top'
			} else if (picker.options.direction === 'bottom') {
				placePosition = 'bottom'
			} else if (picker.options.direction === 'auto') {
				if (offset.top + picker.widget.height() > $window.height() + $window.scrollTop() && picker.widget.height() + picker.element.outerHeight() < offset.top) {
					placePosition = 'top';
				} else {
					placePosition = 'bottom';
				}
			};
			if (placePosition === 'top') {
				offset.top -= picker.widget.height() + picker.element.outerHeight() + 15;
				picker.widget.addClass('top').removeClass('bottom');
			} else {
				offset.top += 1;
				picker.widget.addClass('bottom').removeClass('top');
			}

			if (picker.options.width !== undefined) {
				picker.widget.width(picker.options.width);
			}

			if (picker.options.orientation === 'left') {
				picker.widget.addClass('left-oriented');
				offset.left = offset.left - picker.widget.width() + 20;
			}

			if (isInFixed()) {
				position = 'fixed';
				offset.top -= $window.scrollTop();
				offset.left -= $window.scrollLeft();
			}

			if ($window.width() < offset.left + picker.widget.outerWidth()) {
				offset.right = $window.width() - offset.left - picker.width;
				offset.left = 'auto';
				picker.widget.addClass('pull-right');
			} else {
				offset.right = 'auto';
				picker.widget.removeClass('pull-right');
			}

			picker.widget.css({
				position: position,
				top: offset.top,
				left: offset.left,
				right: offset.right
			});
		},

		notifyChange = function (oldDate, eventType) {
			if (pMoment(picker.date).isSame(pMoment(oldDate))) return;
			picker.element.trigger({
				type: 'dp.change',
				date: pMoment(picker.date),
				oldDate: pMoment(oldDate)
			});

			if (eventType !== 'change')
				picker.element.change();
		},

		notifyError = function (date) {
			picker.element.trigger({
				type: 'dp.error',
				date: pMoment(date)
			});
		},

		update = function (newDate) {
			pMoment.lang(picker.options.language);
			var dateStr = newDate;
			if (!dateStr) {
				dateStr = getPickerInput().val()
				if (dateStr) picker.date = pMoment(dateStr, picker.format, picker.options.useStrict);
				if (!picker.date) picker.date = pMoment();
			}
			picker.viewDate = pMoment(picker.date).startOf("month");
			fillDate();
			fillTime();
		},

		fillDow = function () {
			pMoment.lang(picker.options.language);
			var html = $('<tr>'), weekdaysMin = pMoment.weekdaysMin(), i;

			// jasonrey@stackideas.com
			// Reconstruct weekdays structure by start day of the week
			var spliced = weekdaysMin.splice(pMoment()._lang._week.dow);
			weekdaysMin = spliced.concat(weekdaysMin);

			$.each(weekdaysMin, function(i, w) {
				html.append('<th class="dow">' + w + '</th>');
			});

			// if (pMoment()._lang._week.dow == 0) { // starts on Sunday
			//     for (i = 0; i < 7; i++) {
			//         html.append('<th class="dow">' + weekdaysMin[i] + '</th>');
			//     }
			// } else {
			//     for (i = 1; i < 8; i++) {
			//         if (i == 7) {
			//             html.append('<th class="dow">' + weekdaysMin[0] + '</th>');
			//         } else {
			//             html.append('<th class="dow">' + weekdaysMin[i] + '</th>');
			//         }
			//     }
			// }
			picker.widget.find('.datepicker-days thead').append(html);
		},

		fillMonths = function () {
			pMoment.lang(picker.options.language);
			var html = '', i = 0, monthsShort = pMoment.monthsShort();
			while (i < 12) {
				html += '<span class="month">' + monthsShort[i++] + '</span>';
			}
			picker.widget.find('.datepicker-months td').append(html);
		},

		fillDate = function () {
			pMoment.lang(picker.options.language);
			var year = picker.viewDate.year(),
				month = picker.viewDate.month(),
				startYear = picker.options.minDate.year(),
				startMonth = picker.options.minDate.month(),
				endYear = picker.options.maxDate.year(),
				endMonth = picker.options.maxDate.month(),
				currentDate,
				prevMonth, nextMonth, html = [], row, clsName, i, days, yearCont, currentYear, months = pMoment.months();

			picker.widget.find('.datepicker-days').find('.disabled').removeClass('disabled');
			picker.widget.find('.datepicker-months').find('.disabled').removeClass('disabled');
			picker.widget.find('.datepicker-years').find('.disabled').removeClass('disabled');

			picker.widget.find('.datepicker-days th:eq(1)').text(
				months[month] + ' ' + year);

			prevMonth = pMoment(picker.viewDate).subtract("months", 1);
			days = prevMonth.daysInMonth();
			prevMonth.date(days).startOf('week');
			if ((year == startYear && month <= startMonth) || year < startYear) {
				picker.widget.find('.datepicker-days th:eq(0)').addClass('disabled');
			}
			if ((year == endYear && month >= endMonth) || year > endYear) {
				picker.widget.find('.datepicker-days th:eq(2)').addClass('disabled');
			}

			nextMonth = pMoment(prevMonth).add(42, "d");
			while (prevMonth.isBefore(nextMonth)) {
				if (prevMonth.weekday() === pMoment().startOf('week').weekday()) {
					row = $('<tr>');
					html.push(row);
				}
				clsName = '';
				if (prevMonth.year() < year || (prevMonth.year() == year && prevMonth.month() < month)) {
					clsName += ' old';
				} else if (prevMonth.year() > year || (prevMonth.year() == year && prevMonth.month() > month)) {
					clsName += ' new';
				}
				if (prevMonth.isSame(pMoment({ y: picker.date.year(), M: picker.date.month(), d: picker.date.date() }))) {
					clsName += ' active';
				}
				if (isInDisableDates(prevMonth) || !isInEnableDates(prevMonth)) {
					clsName += ' disabled';
				}
				if (picker.options.showToday === true) {
					if (prevMonth.isSame(pMoment(), 'day')) {
						clsName += ' today';
					}
				}
				if (picker.options.daysOfWeekDisabled) {
					for (i in picker.options.daysOfWeekDisabled) {
						if (prevMonth.day() == picker.options.daysOfWeekDisabled[i]) {
							clsName += ' disabled';
							break;
						}
					}
				}
				row.append('<td class="day' + clsName + '">' + prevMonth.date() + '</td>');

				currentDate = prevMonth.date();
				prevMonth.add(1, "d");

				if (currentDate == prevMonth.date()) {
				  prevMonth.add(1, "d");
				}
			}
			picker.widget.find('.datepicker-days tbody').empty().append(html);
			currentYear = picker.date.year(), months = picker.widget.find('.datepicker-months')
				.find('th:eq(1)').text(year).end().find('span').removeClass('active');
			if (currentYear === year) {
				months.eq(picker.date.month()).addClass('active');
			}
			if (currentYear - 1 < startYear) {
				picker.widget.find('.datepicker-months th:eq(0)').addClass('disabled');
			}
			if (currentYear + 1 > endYear) {
				picker.widget.find('.datepicker-months th:eq(2)').addClass('disabled');
			}
			for (i = 0; i < 12; i++) {
				if ((year == startYear && startMonth > i) || (year < startYear)) {
					$(months[i]).addClass('disabled');
				} else if ((year == endYear && endMonth < i) || (year > endYear)) {
					$(months[i]).addClass('disabled');
				}
			}

			html = '';
			year = parseInt(year / 10, 10) * 10;
			yearCont = picker.widget.find('.datepicker-years').find(
				'th:eq(1)').text(year + '-' + (year + 9)).end().find('td');
			picker.widget.find('.datepicker-years').find('th').removeClass('disabled');
			if (startYear > year) {
				picker.widget.find('.datepicker-years').find('th:eq(0)').addClass('disabled');
			}
			if (endYear < year + 9) {
				picker.widget.find('.datepicker-years').find('th:eq(2)').addClass('disabled');
			}
			year -= 1;
			for (i = -1; i < 11; i++) {
				html += '<span class="year' + (i === -1 || i === 10 ? ' old' : '') + (currentYear === year ? ' active' : '') + ((year < startYear || year > endYear) ? ' disabled' : '') + '">' + year + '</span>';
				year += 1;
			}
			yearCont.html(html);
		},

		fillHours = function () {
			pMoment.lang(picker.options.language);
			var table = picker.widget.find('.timepicker .timepicker-hours table'), html = '', current, i, j;
			table.parent().hide();
			if (picker.use24hours) {
				current = 0;
				for (i = 0; i < 6; i += 1) {
					html += '<tr>';
					for (j = 0; j < 4; j += 1) {
						html += '<td class="hour">' + padLeft(current.toString()) + '</td>';
						current++;
					}
					html += '</tr>';
				}
			}
			else {
				current = 1;
				for (i = 0; i < 3; i += 1) {
					html += '<tr>';
					for (j = 0; j < 4; j += 1) {
						html += '<td class="hour">' + padLeft(current.toString()) + '</td>';
						current++;
					}
					html += '</tr>';
				}
			}
			table.html(html);
		},

		fillMinutes = function () {
			var table = picker.widget.find('.timepicker .timepicker-minutes table'), html = '', current = 0, i, j, step = picker.options.minuteStepping;
			table.parent().hide();
			if (step == 1) step = 5;
			for (i = 0; i < Math.ceil(60 / step / 4) ; i++) {
				html += '<tr>';
				for (j = 0; j < 4; j += 1) {
					if (current < 60) {
						html += '<td class="minute">' + padLeft(current.toString()) + '</td>';
						current += step;
					} else {
						html += '<td></td>';
					}
				}
				html += '</tr>';
			}
			table.html(html);
		},

		fillSeconds = function () {
			var table = picker.widget.find('.timepicker .timepicker-seconds table'), html = '', current = 0, i, j;
			table.parent().hide();
			for (i = 0; i < 3; i++) {
				html += '<tr>';
				for (j = 0; j < 4; j += 1) {
					html += '<td class="second">' + padLeft(current.toString()) + '</td>';
					current += 5;
				}
				html += '</tr>';
			}
			table.html(html);
		},

		fillTime = function () {
			if (!picker.date) return;
			var timeComponents = picker.widget.find('.timepicker span[data-time-component]'),
			hour = picker.date.hours(),
			period = 'AM';
			if (!picker.use24hours) {
				if (hour >= 12) period = 'PM';
				if (hour === 0) hour = 12;
				else if (hour != 12) hour = hour % 12;
				picker.widget.find('.timepicker [data-action=togglePeriod]').text(period);
			}
			timeComponents.filter('[data-time-component=hours]').text(padLeft(hour));
			timeComponents.filter('[data-time-component=minutes]').text(padLeft(picker.date.minutes()));
			timeComponents.filter('[data-time-component=seconds]').text(padLeft(picker.date.second()));
		},

		click = function (e) {
			e.stopPropagation();
			e.preventDefault();
			picker.unset = false;
			var target = $(e.target).closest('span, td, th'), month, year, step, day, oldDate = pMoment(picker.date);
			if (target.length === 1) {
				if (!target.is('.disabled')) {
					switch (target[0].nodeName.toLowerCase()) {
						case 'th':
							switch (target[0].className) {
								case 'switch':
									showMode(1);
									break;
								case 'prev':
								case 'next':
									step = dpGlobal.modes[picker.viewMode].navStep;
									if (target[0].className === 'prev') step = step * -1;
									picker.viewDate.add(step, dpGlobal.modes[picker.viewMode].navFnc);
									fillDate();
									break;
							}
							break;
						case 'span':
							if (target.is('.month')) {
								month = target.parent().find('span').index(target);
								picker.viewDate.month(month);
							} else {
								year = parseInt(target.text(), 10) || 0;
								picker.viewDate.year(year);
							}
							if (picker.viewMode === picker.minViewMode) {
								picker.date = pMoment({
									y: picker.viewDate.year(),
									M: picker.viewDate.month(),
									d: picker.viewDate.date(),
									h: picker.date.hours(),
									m: picker.date.minutes(),
									s: picker.date.seconds()
								});
								notifyChange(oldDate, e.type);
								set();
							}
							showMode(-1);
							fillDate();
							break;
						case 'td':
							if (target.is('.day')) {
								day = parseInt(target.text(), 10) || 1;
								month = picker.viewDate.month();
								year = picker.viewDate.year();
								if (target.is('.old')) {
									if (month === 0) {
										month = 11;
										year -= 1;
									} else {
										month -= 1;
									}
								} else if (target.is('.new')) {
									if (month == 11) {
										month = 0;
										year += 1;
									} else {
										month += 1;
									}
								}
								picker.date = pMoment({
									y: year,
									M: month,
									d: day,
									h: picker.date.hours(),
									m: picker.date.minutes(),
									s: picker.date.seconds()
								}
								);
								picker.viewDate = pMoment({
									y: year, M: month, d: Math.min(28, day)
								});
								fillDate();
								set();
								notifyChange(oldDate, e.type);
							}
							break;
					}
				}
			}
		},

		actions = {
			incrementHours: function () {
				checkDate("add", "hours", 1);
			},

			incrementMinutes: function () {
				checkDate("add", "minutes", picker.options.minuteStepping);
			},

			incrementSeconds: function () {
				checkDate("add", "seconds", 1);
			},

			decrementHours: function () {
				checkDate("subtract", "hours", 1);
			},

			decrementMinutes: function () {
				checkDate("subtract", "minutes", picker.options.minuteStepping);
			},

			decrementSeconds: function () {
				checkDate("subtract", "seconds", 1);
			},

			togglePeriod: function () {
				var hour = picker.date.hours();
				if (hour >= 12) hour -= 12;
				else hour += 12;
				picker.date.hours(hour);
			},

			showPicker: function () {
				picker.widget.find('.timepicker > div:not(.timepicker-picker)').hide();
				picker.widget.find('.timepicker .timepicker-picker').show();
			},

			showHours: function () {
				picker.widget.find('.timepicker .timepicker-picker').hide();
				picker.widget.find('.timepicker .timepicker-hours').show();
			},

			showMinutes: function () {
				picker.widget.find('.timepicker .timepicker-picker').hide();
				picker.widget.find('.timepicker .timepicker-minutes').show();
			},

			showSeconds: function () {
				picker.widget.find('.timepicker .timepicker-picker').hide();
				picker.widget.find('.timepicker .timepicker-seconds').show();
			},

			selectHour: function (e) {
				var period = picker.widget.find('.timepicker [data-action=togglePeriod]').text(), hour = parseInt($(e.target).text(), 10);
				if (period == "PM") hour += 12
				picker.date.hours(hour);
				actions.showPicker.call(picker);
			},

			selectMinute: function (e) {
				picker.date.minutes(parseInt($(e.target).text(), 10));
				actions.showPicker.call(picker);
			},

			selectSecond: function (e) {
				picker.date.seconds(parseInt($(e.target).text(), 10));
				actions.showPicker.call(picker);
			}
		},

		doAction = function (e) {
			var oldDate = pMoment(picker.date), action = $(e.currentTarget).data('action'), rv = actions[action].apply(picker, arguments);
			stopEvent(e);
			if (!picker.date) picker.date = pMoment({ y: 1970 });
			set();
			fillTime();
			notifyChange(oldDate, e.type);
			return rv;
		},

		stopEvent = function (e) {
			e.stopPropagation();
			e.preventDefault();
		},

		change = function (e) {
			pMoment.lang(picker.options.language);
			var input = $(e.target), oldDate = pMoment(picker.date), newDate = pMoment(input.val(), picker.format, picker.options.useStrict);
			if (newDate.isValid() && !isInDisableDates(newDate) && isInEnableDates(newDate)) {
				update();
				picker.setValue(newDate);
				notifyChange(oldDate, e.type);
				set();
			}
			else {
				picker.viewDate = oldDate;
				notifyChange(oldDate, e.type);
				notifyError(newDate);
				picker.unset = true;
			}
		},

		showMode = function (dir) {
			if (dir) {
				picker.viewMode = Math.max(picker.minViewMode, Math.min(2, picker.viewMode + dir));
			}
			var f = dpGlobal.modes[picker.viewMode].clsName;
			picker.widget.find('.datepicker > div').hide().filter('.datepicker-' + dpGlobal.modes[picker.viewMode].clsName).show();
		},

		attachDatePickerEvents = function () {
			var $this, $parent, expanded, closed, collapseData;
			picker.widget.on('click', '.datepicker *', $.proxy(click, this)); // this handles date picker clicks
			picker.widget.on('click', '[data-action]', $.proxy(doAction, this)); // this handles time picker clicks
			picker.widget.on('mousedown', $.proxy(stopEvent, this));
			if (picker.options.pickDate && picker.options.pickTime) {
				picker.widget.on('click.togglePicker', '.accordion-toggle', function (e) {
					e.stopPropagation();
					$this = $(this);
					$parent = $this.closest('ul');
					expanded = $parent.find('.in');
					closed = $parent.find('.collapse:not(.in)');

					if (expanded && expanded.length) {
						collapseData = expanded.data('collapse');
						if (collapseData && collapseData.date - transitioning) return;
						expanded.collapse('hide');
						closed.collapse('show');
						$this.find('span').toggleClass(picker.options.icons.time + ' ' + picker.options.icons.date);
						picker.element.find('.input-group-addon span').toggleClass(picker.options.icons.time + ' ' + picker.options.icons.date);
					}
				});
			}
			if (picker.isInput) {
				picker.element.on({
					'focus': $.proxy(picker.show, this),
					'change': $.proxy(change, this),
					'blur': $.proxy(picker.hide, this)
				});
			} else {
				picker.element.on({
					'change': $.proxy(change, this)
				}, 'input');
				if (picker.component) {
					picker.component.on('click', $.proxy(picker.show, this));
				} else {
					picker.element.on('click', $.proxy(picker.show, this));
				}
			}
		},

		attachDatePickerGlobalEvents = function () {
			$(window).on(
				'resize.datetimepicker' + picker.id, $.proxy(place, this));
			if (!picker.isInput) {
				$(document).on(
					'mousedown.datetimepicker' + picker.id, $.proxy(picker.hide, this));
			}
		},

		detachDatePickerEvents = function () {
			picker.widget.off('click', '.datepicker *', picker.click);
			picker.widget.off('click', '[data-action]');
			picker.widget.off('mousedown', picker.stopEvent);
			if (picker.options.pickDate && picker.options.pickTime) {
				picker.widget.off('click.togglePicker');
			}
			if (picker.isInput) {
				picker.element.off({
					'focus': picker.show,
					'change': picker.change
				});
			} else {
				picker.element.off({
					'change': picker.change
				}, 'input');
				if (picker.component) {
					picker.component.off('click', picker.show);
				} else {
					picker.element.off('click', picker.show);
				}
			}
		},

		detachDatePickerGlobalEvents = function () {
			$(window).off('resize.datetimepicker' + picker.id);
			if (!picker.isInput) {
				$(document).off('mousedown.datetimepicker' + picker.id);
			}
		},

		isInFixed = function () {
			if (picker.element) {
				var parents = picker.element.parents(), inFixed = false, i;
				for (i = 0; i < parents.length; i++) {
					if ($(parents[i]).css('position') == 'fixed') {
						inFixed = true;
						break;
					}
				}
				;
				return inFixed;
			} else {
				return false;
			}
		},

		set = function () {
			pMoment.lang(picker.options.language);
			var formatted = '', input;
			if (!picker.unset) formatted = pMoment(picker.date).format(picker.format);
			getPickerInput().val(formatted);
			picker.element.data('date', formatted);
			if (!picker.options.pickTime) picker.hide();
		},

		checkDate = function (direction, unit, amount) {
			pMoment.lang(picker.options.language);
			var newDate;
			if (direction == "add") {
				newDate = pMoment(picker.date);
				if (newDate.hours() == 23) newDate.add(amount, unit);
				newDate.add(amount, unit);
			}
			else {
				newDate = pMoment(picker.date).subtract(amount, unit);
			}
			if (isInDisableDates(pMoment(newDate.subtract(amount, unit))) || isInDisableDates(newDate)) {
				notifyError(newDate.format(picker.format));
				return;
			}

			if (direction == "add") {
				picker.date.add(amount, unit);
			}
			else {
				picker.date.subtract(amount, unit);
			}
			picker.unset = false;
		},

		isInDisableDates = function (date) {
			pMoment.lang(picker.options.language);
			if (date.isAfter(picker.options.maxDate) || date.isBefore(picker.options.minDate)) return true;
			if (picker.options.disabledDates === false) {
				return false;
			}
			return picker.options.disabledDates[pMoment(date).format("YYYY-MM-DD")] === true;
		},
		isInEnableDates = function (date) {
			pMoment.lang(picker.options.language);
			if (picker.options.enabledDates === false) {
				return true;
			}
			return picker.options.enabledDates[pMoment(date).format("YYYY-MM-DD")] === true;
		},

		indexGivenDates = function (givenDatesArray) {
			// Store given enabledDates and disabledDates as keys.
			// This way we can check their existence in O(1) time instead of looping through whole array.
			// (for example: picker.options.enabledDates['2014-02-27'] === true)
			var givenDatesIndexed = {};
			var givenDatesCount = 0;
			for (i = 0; i < givenDatesArray.length; i++) {
				dDate = pMoment(givenDatesArray[i]);
				if (dDate.isValid()) {
					givenDatesIndexed[dDate.format("YYYY-MM-DD")] = true;
					givenDatesCount++;
				}
			}
			if (givenDatesCount > 0) {
				return givenDatesIndexed;
			}
			return false;
		},

		padLeft = function (string) {
			string = string.toString();
			if (string.length >= 2) return string;
			else return '0' + string;
		},

		getTemplate = function () {

			if (picker.options.pickDate && picker.options.pickTime) {
				var ret = '';
				ret = '<div id="kt" class="bootstrap-datetimepicker-widget' + (picker.options.sideBySide ? ' timepicker-sbs' : '') + ' ' + (picker.options.component) + '">';
				if (picker.options.sideBySide) {
					ret += '<div class="row">' +
					   '<div class="col-sm-6 datepicker">' + dpGlobal.template + '</div>' +
					   '<div class="col-sm-6 timepicker">' + tpGlobal.getTemplate() + '</div>' +
					 '</div>';
				} else {
					ret += '<ul class="list-unstyled">' +
						'<li' + (picker.options.collapse ? ' class="collapse in"' : '') + '>' +
							'<div class="datepicker">' + dpGlobal.template + '</div>' +
						'</li>' +
						'<li class="picker-switch accordion-toggle"><a class="btn btn-primary" style="width:100%"><span class="' + picker.options.icons.time + '"></span></a></li>' +
						'<li' + (picker.options.collapse ? ' class="collapse"' : '') + '>' +
							'<div class="timepicker">' + tpGlobal.getTemplate() + '</div>' +
						'</li>' +
				   '</ul>';

				   // Reserved in case if topdown layout is needed
				   //  ret += '<ul class="list-unstyled">' +
				   //      '<li>' +
				   //          '<div class="datepicker">' + dpGlobal.template + '</div>' +
				   //      '</li>' +
				   //      '<li>' +
				   //          '<div class="timepicker">' + tpGlobal.getTemplate() + '</div>' +
				   //      '</li>' +
				   // '</ul>';
				}
				ret += '</div>';
				return ret;
			} else if (picker.options.pickTime) {
				return (
					'<div id="kt" class="bootstrap-datetimepicker-widget ' + (picker.options.component) + '">' +
						'<div class="timepicker">' + tpGlobal.getTemplate() + '</div>' +
					'</div>'
				);
			} else {
				return (
					'<div id="kt" class="bootstrap-datetimepicker-widget ' + (picker.options.component) + '">' +
						'<ul class="list-unstyled">' +
							'<li' + (picker.options.collapse ? ' class="collapse in"' : '') + '>' +
								'<div class="datepicker">' + dpGlobal.template + '</div>' +
							'</li>' +
						'</ul>' +
					'</div>'
				);
			}
		},

		dpGlobal = {
			modes: [
				{
					clsName: 'days',
					navFnc: 'month',
					navStep: 1
				},
				{
					clsName: 'months',
					navFnc: 'year',
					navStep: 1
				},
				{
					clsName: 'years',
					navFnc: 'year',
					navStep: 10
				}],
			headTemplate:
					'<thead>' +
						'<tr>' +
							'<th class="prev">&lsaquo;</th><th colspan="5" class="switch"></th><th class="next">&rsaquo;</th>' +
						'</tr>' +
					'</thead>',
			contTemplate:
		'<tbody><tr><td colspan="7"></td></tr></tbody>'
		},

		tpGlobal = {
			hourTemplate: '<span data-action="showHours"   data-time-component="hours"   class="timepicker-hour"></span>',
			minuteTemplate: '<span data-action="showMinutes" data-time-component="minutes" class="timepicker-minute"></span>',
			secondTemplate: '<span data-action="showSeconds"  data-time-component="seconds" class="timepicker-second"></span>'
		};

		dpGlobal.template =
			'<div class="datepicker-days">' +
				'<table class="table-condensed">' + dpGlobal.headTemplate + '<tbody></tbody></table>' +
			'</div>' +
			'<div class="datepicker-months">' +
				'<table class="table-condensed">' + dpGlobal.headTemplate + dpGlobal.contTemplate + '</table>' +
			'</div>' +
			'<div class="datepicker-years">' +
				'<table class="table-condensed">' + dpGlobal.headTemplate + dpGlobal.contTemplate + '</table>' +
			'</div>';

		tpGlobal.getTemplate = function () {
			return (
				'<div class="timepicker-picker">' +
					'<table class="table-condensed">' +
						'<tr>' +
							'<td><a href="#" class="btn" data-action="incrementHours"><i class="' + picker.options.icons.up + '"></i></a></td>' +
							'<td class="separator"></td>' +
							'<td>' + (picker.options.useMinutes ? '<a href="#" class="btn" data-action="incrementMinutes"><i class="' + picker.options.icons.up + '"></i></a>' : '') + '</td>' +
							(picker.options.useSeconds ?
								'<td class="separator"></td><td><a href="#" class="btn" data-action="incrementSeconds"><i class="' + picker.options.icons.up + '"></i></a></td>' : '') +
							(picker.use24hours ? '' : '<td class="separator"></td>') +
						'</tr>' +
						'<tr>' +
							'<td>' + tpGlobal.hourTemplate + '</td> ' +
							'<td class="separator">:</td>' +
							'<td>' + (picker.options.useMinutes ? tpGlobal.minuteTemplate : '<span class="timepicker-minute">00</span>') + '</td> ' +
							(picker.options.useSeconds ?
								'<td class="separator">:</td><td>' + tpGlobal.secondTemplate + '</td>' : '') +
							(picker.use24hours ? '' : '<td class="separator"></td>' +
							'<td><button type="button" class="btn btn-primary" data-action="togglePeriod"></button></td>') +
						'</tr>' +
						'<tr>' +
							'<td><a href="#" class="btn" data-action="decrementHours"><i class="' + picker.options.icons.down + '"></i></a></td>' +
							'<td class="separator"></td>' +
							'<td>' + (picker.options.useMinutes ? '<a href="#" class="btn" data-action="decrementMinutes"><i class="' + picker.options.icons.down + '"></i></a>' : '') + '</td>' +
							(picker.options.useSeconds ?
								'<td class="separator"></td><td><a href="#" class="btn" data-action="decrementSeconds"><i class="' + picker.options.icons.down + '"></i></a></td>' : '') +
							(picker.use24hours ? '' : '<td class="separator"></td>') +
						'</tr>' +
					'</table>' +
				'</div>' +
				'<div class="timepicker-hours" data-action="selectHour">' +
					'<table class="table-condensed"></table>' +
				'</div>' +
				'<div class="timepicker-minutes" data-action="selectMinute">' +
					'<table class="table-condensed"></table>' +
				'</div>' +
				(picker.options.useSeconds ?
					'<div class="timepicker-seconds" data-action="selectSecond"><table class="table-condensed"></table></div>' : '')
			);
		};

		picker.destroy = function () {
			detachDatePickerEvents();
			detachDatePickerGlobalEvents();
			picker.widget.remove();
			picker.element.removeData('DateTimePicker');
			if (picker.component)
				picker.component.removeData('DateTimePicker');
		};

		picker.show = function (e) {
			if (picker.options.useCurrent) {
				if (getPickerInput().val() == '') {
					if (picker.options.minuteStepping !== 1) {
						var mDate = pMoment(),
						rInterval = picker.options.minuteStepping;
						mDate.minutes((Math.round(mDate.minutes() / rInterval) * rInterval) % 60)
							.seconds(0);
						picker.setValue(mDate.format(picker.format))
					} else {
						picker.setValue(pMoment().format(picker.format))
					}
				};
			}
			if (picker.widget.hasClass("picker-open")) {
				picker.widget.hide();
				picker.widget.removeClass("picker-open");
			}
			else {
				picker.widget.show();
				picker.widget.addClass("picker-open");
			}
			picker.height = picker.component ? picker.component.outerHeight() : picker.element.outerHeight();
			place();
			picker.element.trigger({
				type: 'dp.show',
				date: pMoment(picker.date)
			});
			attachDatePickerGlobalEvents();
			if (e) {
				stopEvent(e);
			}
		},

		picker.disable = function () {
			var input = picker.element.find('input');
			if (input.prop('disabled')) return;

			input.prop('disabled', true);
			detachDatePickerEvents();
		},

		picker.enable = function () {
			var input = picker.element.find('input');
			if (!input.prop('disabled')) return;

			input.prop('disabled', false);
			attachDatePickerEvents();
		},

		picker.hide = function (event) {
			if (event && $(event.target).is(picker.element.attr("id")))
				return;
			// Ignore event if in the middle of a picker transition
			var collapse = picker.widget.find('.collapse'), i, collapseData;
			for (i = 0; i < collapse.length; i++) {
				collapseData = collapse.eq(i).data('collapse');
				if (collapseData && collapseData.date - transitioning)
					return;
			}
			picker.widget.hide();
			picker.widget.removeClass("picker-open");
			picker.viewMode = picker.startViewMode;
			showMode();
			picker.element.trigger({
				type: 'dp.hide',
				date: pMoment(picker.date)
			});
			detachDatePickerGlobalEvents();
		},

		picker.setValue = function (newDate) {
			pMoment.lang(picker.options.language);
			if (!newDate) {
				picker.unset = true;
				set();
			} else {
				picker.unset = false;
			}
			if (!pMoment.isMoment(newDate)) newDate = pMoment(newDate, picker.format);
			if (newDate.isValid()) {
				picker.date = newDate;
				set();
				picker.viewDate = pMoment({ y: picker.date.year(), M: picker.date.month() });
				fillDate();
				fillTime();
			}
			else {
				notifyError(newDate);
			}
		},

		picker.getDate = function () {
			if (picker.unset) return null;
			return picker.date;
		},

		picker.setDate = function (date) {
			var oldDate = pMoment(picker.date);
			if (!date) {
				picker.setValue(null);
			} else {
				picker.setValue(date);
			}
			notifyChange(oldDate, "function");
		},

		picker.setDisabledDates = function (dates) {
			picker.options.disabledDates = indexGivenDates(dates);
			if (picker.viewDate) update();
		},
		picker.setEnabledDates = function (dates) {
			picker.options.enabledDates = indexGivenDates(dates);
			if (picker.viewDate) update();
		},

		picker.setMaxDate = function (date) {
			if (date == undefined) return;
			picker.options.maxDate = pMoment(date);
			if (picker.viewDate) update();
		},

		picker.setMinDate = function (date) {
			if (date == undefined) return;
			picker.options.minDate = pMoment(date);
			if (picker.viewDate) update();
		};

		init();
	};

	$.fn._datetimepicker = function (options) {
		return this.each(function () {
			var $this = $(this), data = $this.data('DateTimePicker');
			if (!data) $this.data('DateTimePicker', new DateTimePicker(this, options));
		});
	};

}; 

exports(); 
module.resolveWith(exports); 

}); 
// module body: end

}; 
// module factory: end

KTVendors.module("datetimepicker", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() {

var dialogHtml = '<div id="kt" class="kt-dialog"> <div class="kt-dialog-modal"> <div class="kt-dialog-header"> <div class="kt-dialog-header__grid"> <div class="kt-dialog-header__cell"><span class="kt-dialog-title"></span></div> <div class="kt-dialog-close-button"><i class="fa fa-close"></i></div> </div> </div> <div class="kt-dialog-body"> <div class="kt-dialog-container"> <div class="kt-dialog-content"></div> <div class="o-loader"></div> <div class="o-empty"> <div class="o-empty__content"><i class="o-empty__icon fa fa-exclamation-triangle"></i> <div class="o-empty__text"><span class="kt-dialog-error-message"></span></div> </div> </div> </div> </div> <div class="kt-dialog-footer"> <div class=""> <div class="kt-dialog-footer-content"></div> </div> </div> </div></div>';
var dialog_ = ".kt-dialog";
var dialogModal_ = ".kt-dialog-modal";
var dialogContent_ = ".kt-dialog-content";
var dialogHeader_ = ".kt-dialog-header";
var dialogFooter_ = ".kt-dialog-footer";
var dialogFooterContent_ = ".kt-dialog-footer-content";
var dialogCloseButton_ = ".kt-dialog-close-button";
var dialogTitle_ = ".kt-dialog-title";
var dialogErrorMessage_ = ".kt-dialog-error-message";

var isFailed = "is-failed";
var isLoading = "is-loading";
var rxBraces = /\{|\}/gi;

var self = Komento.dialog = function(options) {

	// For places calling Komento.dialog().close();
	if (options === undefined) {
		return self;
	}

	// Normalize options
	if ($.isString(options)) {
		options = {content: options};
	}

	var method = self.open;

	method.apply(self, [options]);

	return self;
}

$.extend(self, {

	defaultOptions: {
		title: "",
		content: "",
		buttons: "",
		classname: "",
		width: "auto",
		height: "auto",
		escapeKey: true
	},

	open: function(options) {

		// Get dialog
		var dialog = $(dialog_);
		if (dialog.length < 1) {
			dialog = $(dialogHtml).appendTo("body");
		}

		// Normalize options
		var options = $.extend({}, self.defaultOptions, options);

		// Set title
		var dialogTitle = $(dialogTitle_);
		dialogTitle.text(options.title);

		// Set buttons
		var dialogFooterContent = $(dialogFooterContent_);
		dialogFooterContent.html(options.buttons);
		dialog.toggleClass("has-footer", !!options.buttons)

		// Set bindings
		self.setBindings(options);

		// Set content
		var dialogContent = $(dialogContent_).empty();
		var content = options.content;
		var contentType = self.getContentType(content);
		dialog.switchClass("type-" + contentType)

		if (window.kt.mobile) {
			dialog.addClass('is-mobile');
		}
		
		// Set width & height
		var dialogModal = $(dialogModal_);
		var dialogWidth = options.width;
		var dialogHeight = options.height;

		if ($.isNumeric(dialogHeight)) {
			var dialogHeader = $(dialogHeader_);
			var dialogFooter = $(dialogFooter_);
			dialogHeight += dialogHeader.height() + dialogFooter.height();
		}

		dialogModal.css({
			width: dialogWidth,
			height: dialogHeight
		});

		dialog.addClassAfter("active");

		// HTML
		switch (contentType) {

			case "html":
				dialogContent.html(content);
				dialog.trigger('init');
				break;

			case "iframe":
				var iframe = $("<iframe>");
				var iframeUrl = content;
				iframe
					.appendTo(dialogContent)
					.one("load", function(){

					})
					.attr("src", iframeUrl);
				break;

			case "deferred":
				dialog.switchClass(isLoading);
				content
					.done(function(content) {
						// Options
						if ($.isPlainObject(content)) {
							self.reopen($.extend(true, options, content));
						// Content
						} else if ($.isString(content)) {
							options.content = content;
							self.reopen(options);
						// Unknown
						} else {
							dialog.switchClass(isFailed);
						}
					})
					.fail(function(exception){
						dialog.switchClass(isFailed);

						var dialogErrorMessage = $(dialogErrorMessage_);

						// Error message
						if ($.isString(exception)) {
							dialogErrorMessage.html(exception);
						}

						// Exception object
						if ($.isPlainObject(exception) && exception.message) {
							dialogErrorMessage.html(exception.message);
						}
					});
				return;
				break;

			case "dialog":
				var xmlOptions = self.parseXMLOptions(content);
				self.open($.extend(true, options, xmlOptions));
				return;
				break;
		}
	},

	reopen: function(options) {
		self.close();
		self.open(options);
	},

	close: function() {

		// Unset bindings
		self.unsetBindings();

		// Remove dialog
		var dialog = $(dialog_);
		dialog.remove();
	},

	getContentType: function(content) {

		if (/<dialog>(.*?)/.test(content)) {
			return "dialog";
		}

		if ($.isUrl(content)) {
			return "iframe";
		}

		if ($.isDeferred(content)) {
			return "deferred";
		}

		return "html";
	},

	parseXMLOptions: function(xml) {

		var xmlOptions = $.buildHTML(xml);
		var newOptions = {};

		$.each(xmlOptions.children(), function(i, node){

			var node = $(node);
			var key  = $.String.camelize(this.nodeName.toLowerCase());
			var val  = node.html();
			var type = node.attr("type");

			switch (type) {
				case "json":
					try {
						val = $.parseJSON(val);
					} catch(e) {};
					break;

				case "javascript":
					try {
						val = eval('(function($){ return ' + $.trim(val) + ' })(' + $.globalNamespace + ')');
					} catch(e) {};
					break;

				case "text":
					val = node.text();
					break;
			}

			// Automatically convert numerical values
			if ($.isNumeric(val)) {
				val = parseFloat(val);
			}

			newOptions[key] = val;
		});

		return newOptions;
	},

	bindings: {},

	setBindings: function(options) {

		// Remove previous bindings
		self.unsetBindings();

		// Create new bindings
		var selectors = options.selectors;
		var bindings  = options.bindings;
		var dialog = $(dialog_);

		if (selectors && bindings) {

			// Simulate a controller instance
			var controller = {parent: self};
			
			$.each(selectors, function(element, selector){

				var element = element.replace(rxBraces, "");

				// Create selector fn
				var selectorFn = controller[element] = function() {
					return dialog.find(selector);
				};
				selectorFn.selector = selector;
			});

			// Simulate mvc here
			controller["parent"] = self;
			controller["element"] = dialog;
			controller["self"] = function() {
									return dialog;
								};

			// Make the caller available to the dialog if a caller is provided
			if (options.caller) {
				controller["caller"] = options.caller;
			}
			// controller["self"].selector = dialog.selector;

			$.each(bindings, function(binder, eventHandler){

				// Get element and event name
				var parts = binder.split(" ");
				var element = parts[0].replace(rxBraces, "");
				var eventName = parts[1] + ".es.dialog";

				// Get selector fn
				var selectorFn = controller[element];

				// Custom way of simulating a controller's init method
				if (element == 'init') {
					dialog.on(element, function() {

						var args = [this].concat(arguments);

						eventHandler.apply(controller, args);
					});
				}

				// No binding if selector fn is not found
				if (!selectorFn) {

					// These items could be 
					controller[element] = eventHandler;

					return;
				}

				// Bind event handler
				var selector = selectorFn.selector;

				dialog.on(eventName, selector, function(){
					// Convert the argument object into an array first.
					var args = [].slice.call(arguments);
					
					eventHandler.apply(controller, [$(this)].concat(args));
				});

				// Add to bindings
				self.bindings[eventName] = eventHandler;
			});
		}

		if (options.escapeKey) {
			$(document).on("keydown.kt.dialog", function(event){
				if (event.keyCode==27) {
					self.close();
				}
			});
		}
	},

	setMessage: function(response) {
		var element = $('<div class="o-alert o-alert--' + response.type + '"><button type="button" class="close" data-bs-dismiss="alert">×</button></div>');
		var content = $(dialogContent_);

		element.append(response.message);
		element.prependTo(content);
	},

	unsetBindings: function() {

		// Get dialog
		var dialog = $(dialog_);

		// Unbind bindings
		$.each(self.bindings, function(eventName, eventHandler){
			dialog.off(eventName);
		});

		// Unbind escape
		$(document).off("keydown.kt.dialog");
	}
});

$(document)
	.on("click", dialogCloseButton_, function(){
		self.close();
	})
	.on("click", dialog_, function(event){
		var dialog = $(dialog_);
		if (event.target==dialog[0]) {
			self.close();
		}
	});


};

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("dialog", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var exports = function() { 

// Expanding Textareas
// https://github.com/bgrins/ExpandingTextareas

	$.expandingTextarea = $.extend({
		autoInitialize: true,
		initialSelector: "textarea.expanding",
		opts: {
			resize: function() { }
		}
	}, $.expandingTextarea || {});
	
	var cloneCSSProperties = [
		'lineHeight', 'textDecoration', 'letterSpacing',
		'fontSize', 'fontFamily', 'fontStyle', 
		'fontWeight', 'textTransform', 'textAlign', 
		'direction', 'wordSpacing', 'fontSizeAdjust', 
		'wordWrap', 'word-break',
		'borderLeftWidth', 'borderRightWidth',
		'borderTopWidth','borderBottomWidth',
		'paddingLeft', 'paddingRight',
		'paddingTop','paddingBottom',
		'marginLeft', 'marginRight',
		'marginTop','marginBottom',
		'boxSizing', 'webkitBoxSizing', 'mozBoxSizing', 'msBoxSizing'
	];
	
	var textareaCSS = {
		position: "absolute",
		height: "100%",
		resize: "none"
	};
	
	var preCSS = {
		visibility: "hidden",
		border: "0 solid",
		whiteSpace: "pre-wrap" 
	};
	
	var containerCSS = {
		position: "relative"
	};
	
	function resize() {

		var clone = $(this).data("textareaClone");
		clone.find("div").text(this.value.replace(/\r\n/g, "\n") + ' ');
		$(this).trigger("resize.expanding");
	}
	
	$.fn.expandingTextarea = function(o) {
		
		var opts = $.extend({ }, $.expandingTextarea.opts, o);
		
		if (o === "resize") {
			return this.trigger("input.expanding");
		}
		
		if (o === "destroy") {
			this.filter(".expanding-init").each(function() {
				// TODO: Restore container position value
				var textarea = $(this).removeClass('expanding-init');
				textarea
					.attr('style', textarea.data('expanding-styles') || '')
					.removeData('expanding-styles');
			});
			
			return this;
		}
		
		this.filter("textarea").not(".expanding-init").addClass("expanding-init").each(function() {

			var textarea  = $(this),
				container = textarea.parent(),
				clone     = $($.parseHTML("<pre class='textareaClone'><div></div></pre>"));

			textarea
				.after(clone)
				.data("textareaClone", clone);

			// Container
			container.css(containerCSS);
			
			// Store the original styles in case of destroying.
			textarea.data('expanding-styles', textarea.attr('style'));
			textarea.css(textareaCSS);

			// Clone
			clone.css(preCSS);
			
			$.each(cloneCSSProperties, function(i, p) {
				var val = textarea.css(p);
				
				// Only set if different to prevent overriding percentage css values.
				if (clone.css(p) !== val) {
					clone.css(p, val);
				}
			});
			
			textarea.bind("input.expanding propertychange.expanding keyup.expanding", resize);
			resize.apply(this);
			
			if (opts.resize) {
				textarea.bind("resize.expanding", opts.resize);
			}
		});
		
		return this;
	};
	
	$(function () {
		if ($.expandingTextarea.autoInitialize) {
			$($.expandingTextarea.initialSelector).expandingTextarea();
		}
	});


}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("expanding", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 

// ----------------------------------------------------------------------------
// markItUp! Universal MarkUp Engine, JQuery plugin
// v 1.1.x
// Dual licensed under the MIT and GPL licenses.
// ----------------------------------------------------------------------------
// Copyright (C) 2007-2012 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ----------------------------------------------------------------------------

(function($) {
	$.fn.markItUp = function(settings, extraSettings) {
		var method, params, options, ctrlKey, shiftKey, altKey; ctrlKey = shiftKey = altKey = false;

		if (typeof settings == 'string') {
			method = settings;
			params = extraSettings;
		}

		options = {	id:						'',
					nameSpace:				'',
					root:					'',
					previewHandler:			false,
					previewInWindow:		'', // 'width=800, height=600, resizable=yes, scrollbars=yes'
					previewInElement:		'',
					previewAutoRefresh:		true,
					previewPosition:		'after',
					previewTemplatePath:	'~/templates/preview.html',
					previewParser:			false,
					previewParserPath:		'',
					previewParserVar:		'data',
					resizeHandle:			true,
					beforeInsert:			'',
					afterInsert:			'',
					onEnter:				{},
					onShiftEnter:			{},
					onCtrlEnter:			{},
					onTab:					{},
					markupSet:			[	{ /* set */ } ]
				};

		$.extend(options, settings, $.markItUp.sets[(extraSettings || settings || {}).set], extraSettings);

		// compute markItUp! path
		if (!options.root) {
			$('script').each(function(a, tag) {
				miuScript = $(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
				if (miuScript !== null) {
					options.root = miuScript[1];
				}
			});
		}

		return this.each(function() {
			var $$, textarea, levels, scrollPosition, caretPosition, caretOffset,
				clicked, hash, header, footer, previewWindow, template, iFrame, abort;
			$$ = $(this);
			textarea = this;
			levels = [];
			abort = false;
			scrollPosition = caretPosition = 0;
			caretOffset = -1;

			options.previewParserPath = localize(options.previewParserPath);
			options.previewTemplatePath = localize(options.previewTemplatePath);

			if (method) {
				switch(method) {
					case 'remove':
						remove();
					break;
					case 'insert':
						markup(params);
					break;
					default:
						$.error('Method ' +  method + ' does not exist on jQuery.markItUp');
				}
				return;
			}

			// apply the computed path to ~/
			function localize(data, inText) {
				if (inText) {
					return 	data.replace(/("|')~\//g, "$1"+options.root);
				}
				return 	data.replace(/^~\//, options.root);
			}

			// init and build editor
			function init() {
				id = ''; nameSpace = '';
				if (options.id) {
					id = 'id="'+options.id+'"';
				} else if ($$.attr("id")) {
					id = 'id="markItUp'+($$.attr("id").substr(0, 1).toUpperCase())+($$.attr("id").substr(1))+'"';

				}
				if (options.nameSpace) {
					nameSpace = 'class="'+options.nameSpace+'"';
				}
				$$.wrap('<div '+nameSpace+'></div>');
				$$.wrap('<div '+id+' class="markItUp"></div>');
				$$.wrap('<div class="markItUpContainer"></div>');
				$$.addClass("markItUpEditor");

				// add the header before the textarea
				header = $('<div class="markItUpHeader"></div>').insertBefore($$);
				$(dropMenus(options.markupSet)).appendTo(header);

				// add the footer after the textarea
				footer = $('<div class="markItUpFooter"></div>').insertAfter($$);

				// Wrapper for expandingTextarea
				$$.wrap('<div class="markItUpExpanding"></div>');

				// add the resize handle after textarea
				if (options.resizeHandle === true && $.browser.safari !== true) {
					resizeHandle = $('<div class="markItUpResizeHandle"></div>')
						.insertAfter($$)
						.bind("mousedown.markItUp", function(e) {
							var h = $$.height(), y = e.clientY, mouseMove, mouseUp;
							mouseMove = function(e) {
								$$.css("height", Math.max(20, e.clientY+h-y)+"px");
								return false;
							};
							mouseUp = function(e) {
								$("html").unbind("mousemove.markItUp", mouseMove).unbind("mouseup.markItUp", mouseUp);
								return false;
							};
							$("html").bind("mousemove.markItUp", mouseMove).bind("mouseup.markItUp", mouseUp);
					});
					footer.append(resizeHandle);
				}

				// listen key events
				$$.bind('keydown.markItUp', keyPressed).bind('keyup', keyPressed);

				// bind an event to catch external calls
				$$.bind("insertion.markItUp", function(e, settings) {
					if (settings.target !== false) {
						get();
					}
					if (textarea === $.markItUp.focused) {
						markup(settings);
					}
				});

				// remember the last focus
				$$.bind('focus.markItUp', function() {
					$.markItUp.focused = this;
				});

				if (options.previewInElement) {
					refreshPreview();
				}
			}

			// recursively build header with dropMenus from markupset
			function dropMenus(markupSet) {
				var ul = $('<ul></ul>'), i = 0;
				$('li:hover > ul', ul).css('display', 'block');
				$.each(markupSet, function() {
					var button = this, t = '', title, li, j;
					title = (button.key) ? (button.name||'')+' [Ctrl+'+button.key+']' : (button.name||'');
					key   = (button.key) ? 'accesskey="'+button.key+'"' : '';
					if (button.separator) {
						li = $('<li class="markItUpSeparator">'+(button.separator||'')+'</li>').appendTo(ul);
					} else {
						i++;
						for (j = levels.length -1; j >= 0; j--) {
							t += levels[j]+"-";
						}
						li = $('<li class="markItUpButton markItUpButton'+t+(i)+' '+(button.className||'')+'"><a href="" '+key+' title="'+title+'">'+(button.name||'')+'</a></li>')
						.bind("contextmenu.markItUp", function() { // prevent contextmenu on mac and allow ctrl+click
							return false;
						}).bind('click.markItUp', function() {
							return false;
						}).bind("focusin.markItUp", function(){
                            $$.focus();
						}).bind('mouseup', function() {
							if (button.call) {
								eval(button.call)();
							}
							setTimeout(function() { markup(button) },1);
							return false;
						}).bind('mouseenter.markItUp', function() {
								$('> ul', this).show();
								$(document).one('click', function() { // close dropmenu if click outside
										$('ul ul', header).hide();
									}
								);
						}).bind('mouseleave.markItUp', function() {
								$('> ul', this).hide();
						}).appendTo(ul);
						if (button.dropMenu) {
							levels.push(i);
							$(li).addClass('markItUpDropMenu').append(dropMenus(button.dropMenu));
						}
					}
				});
				levels.pop();
				return ul;
			}

			// markItUp! markups
			function magicMarkups(string) {
				if (string) {
					string = string.toString();
					string = string.replace(/\(\!\(([\s\S]*?)\)\!\)/g,
						function(x, a) {
							var b = a.split('|!|');
							if (altKey === true) {
								return (b[1] !== undefined) ? b[1] : b[0];
							} else {
								return (b[1] === undefined) ? "" : b[0];
							}
						}
					);
					// [![prompt]!], [![prompt:!:value]!]
					string = string.replace(/\[\!\[([\s\S]*?)\]\!\]/g,
						function(x, a) {
							var b = a.split(':!:');
							if (abort === true) {
								return false;
							}
							value = prompt(b[0], (b[1]) ? b[1] : '');
							if (value === null) {
								abort = true;
							}
							return value;
						}
					);
					return string;
				}
				return "";
			}

			// prepare action
			function prepare(action) {
				if ($.isFunction(action)) {
					action = action(hash);
				}
				return magicMarkups(action);
			}

			// build block to insert
			function build(string) {
				var openWith 			= prepare(clicked.openWith);
				var placeHolder 		= prepare(clicked.placeHolder);
				var replaceWith 		= prepare(clicked.replaceWith);
				var closeWith 			= prepare(clicked.closeWith);
				var openBlockWith 		= prepare(clicked.openBlockWith);
				var closeBlockWith 		= prepare(clicked.closeBlockWith);
				var multiline 			= clicked.multiline;

				if (replaceWith !== "") {
					block = openWith + replaceWith + closeWith;
				} else if (selection === '' && placeHolder !== '') {
					block = openWith + placeHolder + closeWith;
				} else {
					string = string || selection;

					var lines = [string], blocks = [];

					if (multiline === true) {
						lines = string.split(/\r?\n/);
					}

					for (var l = 0; l < lines.length; l++) {
						line = lines[l];
						var trailingSpaces;
						if (trailingSpaces = line.match(/ *$/)) {
							blocks.push(openWith + line.replace(/ *$/g, '') + closeWith + trailingSpaces);
						} else {
							blocks.push(openWith + line + closeWith);
						}
					}

					block = blocks.join("\n");
				}

				block = openBlockWith + block + closeBlockWith;

				return {	block:block,
							openWith:openWith,
							replaceWith:replaceWith,
							placeHolder:placeHolder,
							closeWith:closeWith
					};
			}

			// define markup to insert
			function markup(button) {
				var len, j, n, i;
				hash = clicked = button;
				get();
				$.extend(hash, {	line:"",
						 			root:options.root,
									textarea:textarea,
									selection:(selection||''),
									caretPosition:caretPosition,
									ctrlKey:ctrlKey,
									shiftKey:shiftKey,
									altKey:altKey
								}
							);
				// callbacks before insertion
				prepare(options.beforeInsert);
				prepare(clicked.beforeInsert);
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.beforeMultiInsert);
				}
				$.extend(hash, { line:1 });

				if ((ctrlKey === true && shiftKey === true)) {
					lines = selection.split(/\r?\n/);
					for (j = 0, n = lines.length, i = 0; i < n; i++) {
						if ($.trim(lines[i]) !== '') {
							$.extend(hash, { line:++j, selection:lines[i] } );
							lines[i] = build(lines[i]).block;
						} else {
							lines[i] = "";
						}
					}

					string = { block:lines.join('\n')};
					start = caretPosition;
					len = string.block.length + (($.browser.opera) ? n-1 : 0);
				} else if (ctrlKey === true) {
					string = build(selection);
					start = caretPosition + string.openWith.length;
					len = string.block.length - string.openWith.length - string.closeWith.length;
					len = len - (string.block.match(/ $/) ? 1 : 0);
					len -= fixIeBug(string.block);
				} else if (shiftKey === true) {
					string = build(selection);
					start = caretPosition;
					len = string.block.length;
					len -= fixIeBug(string.block);
				} else {
					string = build(selection);
					start = caretPosition + string.block.length ;
					len = 0;
					start -= fixIeBug(string.block);
				}
				if ((selection === '' && string.replaceWith === '')) {
					caretOffset += fixOperaBug(string.block);

					start = caretPosition + string.openWith.length;
					len = string.block.length - string.openWith.length - string.closeWith.length;

					caretOffset = $$.val().substring(caretPosition,  $$.val().length).length;
					caretOffset -= fixOperaBug($$.val().substring(0, caretPosition));
				}
				$.extend(hash, { caretPosition:caretPosition, scrollPosition:scrollPosition } );

				if (string.block !== selection && abort === false) {
					insert(string.block);
					set(start, len);
				} else {
					caretOffset = -1;
				}
				get();

				$.extend(hash, { line:'', selection:selection });

				// callbacks after insertion
				if ((ctrlKey === true && shiftKey === true) || button.multiline === true) {
					prepare(clicked.afterMultiInsert);
				}
				prepare(clicked.afterInsert);
				prepare(options.afterInsert);

				// refresh preview if opened
				if (previewWindow && options.previewAutoRefresh) {
					refreshPreview();
				}

				// reinit keyevent
				shiftKey = altKey = ctrlKey = abort = false;
			}

			// Substract linefeed in Opera
			function fixOperaBug(string) {
				if ($.browser.opera) {
					return string.length - string.replace(/\n*/g, '').length;
				}
				return 0;
			}
			// Substract linefeed in IE
			function fixIeBug(string) {
				if ($.browser.msie) {
					return string.length - string.replace(/\r*/g, '').length;
				}
				return 0;
			}

			// add markup
			function insert(block) {
				if (document.selection) {
					var newSelection = document.selection.createRange();
					newSelection.text = block;
				} else {
					textarea.value =  textarea.value.substring(0, caretPosition)  + block + textarea.value.substring(caretPosition + selection.length, textarea.value.length);
				}
			}

			// set a selection
			function set(start, len) {
				if (textarea.createTextRange){
					// quick fix to make it work on Opera 9.5
					if ($.browser.opera && $.browser.version >= 9.5 && len == 0) {
						return false;
					}
					range = textarea.createTextRange();
					range.collapse(true);
					range.moveStart('character', start);
					range.moveEnd('character', len);
					range.select();
				} else if (textarea.setSelectionRange ){
					textarea.setSelectionRange(start, start + len);
				}
				textarea.scrollTop = scrollPosition;
				textarea.focus();
			}

			// get the selection
			function get() {
				textarea.focus();

				scrollPosition = textarea.scrollTop;
				if (document.selection) {
					selection = document.selection.createRange().text;
					if ($.browser.msie) { // ie
						var range = document.selection.createRange(), rangeCopy = range.duplicate();
						rangeCopy.moveToElementText(textarea);
						caretPosition = -1;
						while(rangeCopy.inRange(range)) {
							rangeCopy.moveStart('character');
							caretPosition ++;
						}
					} else { // opera
						caretPosition = textarea.selectionStart;
					}
				} else { // gecko & webkit
					caretPosition = textarea.selectionStart;

					selection = textarea.value.substring(caretPosition, textarea.selectionEnd);
				}
				return selection;
			}

			// open preview window
			function preview() {
				if (typeof options.previewHandler === 'function') {
					previewWindow = true;
				} else if (options.previewInElement) {
					previewWindow = $(options.previewInElement);
				} else if (!previewWindow || previewWindow.closed) {
					if (options.previewInWindow) {
						previewWindow = window.open('', 'preview', options.previewInWindow);
						$(window).unload(function() {
							previewWindow.close();
						});
					} else {
						iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
						if (options.previewPosition == 'after') {
							iFrame.insertAfter(footer);
						} else {
							iFrame.insertBefore(header);
						}
						previewWindow = iFrame[iFrame.length - 1].contentWindow || frame[iFrame.length - 1];
					}
				} else if (altKey === true) {
					if (iFrame) {
						iFrame.remove();
					} else {
						previewWindow.close();
					}
					previewWindow = iFrame = false;
				}
				if (!options.previewAutoRefresh) {
					refreshPreview();
				}
				if (options.previewInWindow) {
					previewWindow.focus();
				}
			}

			// refresh Preview window
			function refreshPreview() {
 				renderPreview();
			}

			function renderPreview() {
				var phtml;
				if (options.previewHandler && typeof options.previewHandler === 'function') {
					options.previewHandler( $$.val() );
				} else if (options.previewParser && typeof options.previewParser === 'function') {
					var data = options.previewParser( $$.val() );
					writeInPreview(localize(data, 1) );
				} else if (options.previewParserPath !== '') {
					$.ajax({
						type: 'POST',
						dataType: 'text',
						global: false,
						url: options.previewParserPath,
						data: options.previewParserVar+'='+encodeURIComponent($$.val()),
						success: function(data) {
							writeInPreview( localize(data, 1) );
						}
					});
				} else {
					if (!template) {
						$.ajax({
							url: options.previewTemplatePath,
							dataType: 'text',
							global: false,
							success: function(data) {
								writeInPreview( localize(data, 1).replace(/<!-- content -->/g, $$.val()) );
							}
						});
					}
				}
				return false;
			}

			function writeInPreview(data) {
				if (options.previewInElement) {
					$(options.previewInElement).html(data);
				} else if (previewWindow && previewWindow.document) {
					try {
						sp = previewWindow.document.documentElement.scrollTop
					} catch(e) {
						sp = 0;
					}
					previewWindow.document.open();
					previewWindow.document.write(data);
					previewWindow.document.close();
					previewWindow.document.documentElement.scrollTop = sp;
				}
			}

			// set keys pressed
			function keyPressed(e) {
				shiftKey = e.shiftKey;
				altKey = e.altKey;
				ctrlKey = (!(e.altKey && e.ctrlKey)) ? (e.ctrlKey || e.metaKey) : false;

				if (e.type === 'keydown') {
					if (ctrlKey === true) {
						li = $('a[accesskey="'+((e.keyCode == 13) ? '\\n' : String.fromCharCode(e.keyCode))+'"]', header).parent('li');
						if (li.length !== 0) {
							ctrlKey = false;
							setTimeout(function() {
								li.triggerHandler('mouseup');
							},1);
							return false;
						}
					}
					if (e.keyCode === 13 || e.keyCode === 10) { // Enter key
						if (ctrlKey === true) {  // Enter + Ctrl
							ctrlKey = false;
							markup(options.onCtrlEnter);
							return options.onCtrlEnter.keepDefault;
						} else if (shiftKey === true) { // Enter + Shift
							shiftKey = false;
							markup(options.onShiftEnter);
							return options.onShiftEnter.keepDefault;
						} else { // only Enter
							markup(options.onEnter);
							return options.onEnter.keepDefault;
						}
					}
					if (e.keyCode === 9) { // Tab key
						if (shiftKey == true || ctrlKey == true || altKey == true) {
							return false;
						}
						if (caretOffset !== -1) {
							get();
							caretOffset = $$.val().length - caretOffset;
							set(caretOffset, 0);
							caretOffset = -1;
							return false;
						} else {
							markup(options.onTab);
							return options.onTab.keepDefault;
						}
					}
				}
			}

			function remove() {
				$$.unbind(".markItUp").removeClass('markItUpEditor');
				$$.parents('div.markItUp').parent('div').replaceWith($$);
				$$.data('markItUp', null);
			}

			init();
		});
	};

	$.fn.markItUpRemove = function() {
		return this.each(function() {
				$(this).markItUp('remove');
			}
		);
	};

	var sets;

	if (typeof $.markItUp === "object") {
		sets = $.markItUp.sets;
	}

	$.markItUp = function(settings) {
		var options = { target:false };
		$.extend(options, settings);
		if (options.target) {
			return $(options.target).each(function() {
				$(this).focus();
				$(this).trigger('insertion', [options]);
			});
		} else {
			$('textarea').trigger('insertion', [options]);
		}
	};

	$.markItUp.sets = {};

	if (sets) {
		$.extend($.markItUp.sets, sets);
	}

})(jQuery);

}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("markitup", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var exports = function() { 

//! moment.js
//! version : 2.6.0
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

(function (undefined) {

	/************************************
		Constants
	************************************/

	var moment,
		VERSION = "2.6.0",
		// the global-scope this is NOT the global object in Node.js
		globalScope = typeof global !== 'undefined' ? global : this,
		oldGlobalMoment,
		round = Math.round,
		i,

		YEAR = 0,
		MONTH = 1,
		DATE = 2,
		HOUR = 3,
		MINUTE = 4,
		SECOND = 5,
		MILLISECOND = 6,

		// internal storage for language config files
		languages = {},

		// moment internal properties
		momentProperties = {
			_isAMomentObject: null,
			_i : null,
			_f : null,
			_l : null,
			_strict : null,
			_isUTC : null,
			_offset : null,  // optional. Combine with _isUTC
			_pf : null,
			_lang : null  // optional
		},

		// check for nodeJS
		hasModule = (typeof module !== 'undefined' && module.exports),

		// ASP.NET json date format regex
		aspNetJsonRegex = /^\/?Date\((\-?\d+)/i,
		aspNetTimeSpanJsonRegex = /(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/,

		// from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
		// somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
		isoDurationRegex = /^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/,

		// format tokens
		formattingTokens = /(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Q|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|S{1,4}|X|zz?|ZZ?|.)/g,
		localFormattingTokens = /(\[[^\[]*\])|(\\)?(LT|LL?L?L?|l{1,4})/g,

		// parsing token regexes
		parseTokenOneOrTwoDigits = /\d\d?/, // 0 - 99
		parseTokenOneToThreeDigits = /\d{1,3}/, // 0 - 999
		parseTokenOneToFourDigits = /\d{1,4}/, // 0 - 9999
		parseTokenOneToSixDigits = /[+\-]?\d{1,6}/, // -999,999 - 999,999
		parseTokenDigits = /\d+/, // nonzero number of digits
		parseTokenWord = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i, // any word (or two) characters or numbers including two/three word month in arabic.
		parseTokenTimezone = /Z|[\+\-]\d\d:?\d\d/gi, // +00:00 -00:00 +0000 -0000 or Z
		parseTokenT = /T/i, // T (ISO separator)
		parseTokenTimestampMs = /[\+\-]?\d+(\.\d{1,3})?/, // 123456789 123456789.123
		parseTokenOrdinal = /\d{1,2}/,

		//strict parsing regexes
		parseTokenOneDigit = /\d/, // 0 - 9
		parseTokenTwoDigits = /\d\d/, // 00 - 99
		parseTokenThreeDigits = /\d{3}/, // 000 - 999
		parseTokenFourDigits = /\d{4}/, // 0000 - 9999
		parseTokenSixDigits = /[+-]?\d{6}/, // -999,999 - 999,999
		parseTokenSignedNumber = /[+-]?\d+/, // -inf - inf

		// iso 8601 regex
		// 0000-00-00 0000-W00 or 0000-W00-0 + T + 00 or 00:00 or 00:00:00 or 00:00:00.000 + +00:00 or +0000 or +00)
		isoRegex = /^\s*(?:[+-]\d{6}|\d{4})-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d+)?)?)?)?([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/,

		isoFormat = 'YYYY-MM-DDTHH:mm:ssZ',

		isoDates = [
			['YYYYYY-MM-DD', /[+-]\d{6}-\d{2}-\d{2}/],
			['YYYY-MM-DD', /\d{4}-\d{2}-\d{2}/],
			['GGGG-[W]WW-E', /\d{4}-W\d{2}-\d/],
			['GGGG-[W]WW', /\d{4}-W\d{2}/],
			['YYYY-DDD', /\d{4}-\d{3}/]
		],

		// iso time formats and regexes
		isoTimes = [
			['HH:mm:ss.SSSS', /(T| )\d\d:\d\d:\d\d\.\d+/],
			['HH:mm:ss', /(T| )\d\d:\d\d:\d\d/],
			['HH:mm', /(T| )\d\d:\d\d/],
			['HH', /(T| )\d\d/]
		],

		// timezone chunker "+10:00" > ["10", "00"] or "-1530" > ["-15", "30"]
		parseTimezoneChunker = /([\+\-]|\d\d)/gi,

		// getter and setter names
		proxyGettersAndSetters = 'Date|Hours|Minutes|Seconds|Milliseconds'.split('|'),
		unitMillisecondFactors = {
			'Milliseconds' : 1,
			'Seconds' : 1e3,
			'Minutes' : 6e4,
			'Hours' : 36e5,
			'Days' : 864e5,
			'Months' : 2592e6,
			'Years' : 31536e6
		},

		unitAliases = {
			ms : 'millisecond',
			s : 'second',
			m : 'minute',
			h : 'hour',
			d : 'day',
			D : 'date',
			w : 'week',
			W : 'isoWeek',
			M : 'month',
			Q : 'quarter',
			y : 'year',
			DDD : 'dayOfYear',
			e : 'weekday',
			E : 'isoWeekday',
			gg: 'weekYear',
			GG: 'isoWeekYear'
		},

		camelFunctions = {
			dayofyear : 'dayOfYear',
			isoweekday : 'isoWeekday',
			isoweek : 'isoWeek',
			weekyear : 'weekYear',
			isoweekyear : 'isoWeekYear'
		},

		// format function strings
		formatFunctions = {},

		// tokens to ordinalize and pad
		ordinalizeTokens = 'DDD w W M D d'.split(' '),
		paddedTokens = 'M D H h m s w W'.split(' '),

		formatTokenFunctions = {
			M    : function () {
				return this.month() + 1;
			},
			MMM  : function (format) {
				return this.lang().monthsShort(this, format);
			},
			MMMM : function (format) {
				return this.lang().months(this, format);
			},
			D    : function () {
				return this.date();
			},
			DDD  : function () {
				return this.dayOfYear();
			},
			d    : function () {
				return this.day();
			},
			dd   : function (format) {
				return this.lang().weekdaysMin(this, format);
			},
			ddd  : function (format) {
				return this.lang().weekdaysShort(this, format);
			},
			dddd : function (format) {
				return this.lang().weekdays(this, format);
			},
			w    : function () {
				return this.week();
			},
			W    : function () {
				return this.isoWeek();
			},
			YY   : function () {
				return leftZeroFill(this.year() % 100, 2);
			},
			YYYY : function () {
				return leftZeroFill(this.year(), 4);
			},
			YYYYY : function () {
				return leftZeroFill(this.year(), 5);
			},
			YYYYYY : function () {
				var y = this.year(), sign = y >= 0 ? '+' : '-';
				return sign + leftZeroFill(Math.abs(y), 6);
			},
			gg   : function () {
				return leftZeroFill(this.weekYear() % 100, 2);
			},
			gggg : function () {
				return leftZeroFill(this.weekYear(), 4);
			},
			ggggg : function () {
				return leftZeroFill(this.weekYear(), 5);
			},
			GG   : function () {
				return leftZeroFill(this.isoWeekYear() % 100, 2);
			},
			GGGG : function () {
				return leftZeroFill(this.isoWeekYear(), 4);
			},
			GGGGG : function () {
				return leftZeroFill(this.isoWeekYear(), 5);
			},
			e : function () {
				return this.weekday();
			},
			E : function () {
				return this.isoWeekday();
			},
			a    : function () {
				return this.lang().meridiem(this.hours(), this.minutes(), true);
			},
			A    : function () {
				return this.lang().meridiem(this.hours(), this.minutes(), false);
			},
			H    : function () {
				return this.hours();
			},
			h    : function () {
				return this.hours() % 12 || 12;
			},
			m    : function () {
				return this.minutes();
			},
			s    : function () {
				return this.seconds();
			},
			S    : function () {
				return toInt(this.milliseconds() / 100);
			},
			SS   : function () {
				return leftZeroFill(toInt(this.milliseconds() / 10), 2);
			},
			SSS  : function () {
				return leftZeroFill(this.milliseconds(), 3);
			},
			SSSS : function () {
				return leftZeroFill(this.milliseconds(), 3);
			},
			Z    : function () {
				var a = -this.zone(),
					b = "+";
				if (a < 0) {
					a = -a;
					b = "-";
				}
				return b + leftZeroFill(toInt(a / 60), 2) + ":" + leftZeroFill(toInt(a) % 60, 2);
			},
			ZZ   : function () {
				var a = -this.zone(),
					b = "+";
				if (a < 0) {
					a = -a;
					b = "-";
				}
				return b + leftZeroFill(toInt(a / 60), 2) + leftZeroFill(toInt(a) % 60, 2);
			},
			z : function () {
				return this.zoneAbbr();
			},
			zz : function () {
				return this.zoneName();
			},
			X    : function () {
				return this.unix();
			},
			Q : function () {
				return this.quarter();
			}
		},

		lists = ['months', 'monthsShort', 'weekdays', 'weekdaysShort', 'weekdaysMin'];

	function defaultParsingFlags() {
		// We need to deep clone this object, and es5 standard is not very
		// helpful.
		return {
			empty : false,
			unusedTokens : [],
			unusedInput : [],
			overflow : -2,
			charsLeftOver : 0,
			nullInput : false,
			invalidMonth : null,
			invalidFormat : false,
			userInvalidated : false,
			iso: false
		};
	}

	function deprecate(msg, fn) {
		var firstTime = true;
		function printMsg() {
			if (moment.suppressDeprecationWarnings === false &&
					typeof console !== 'undefined' && console.warn) {
				console.warn("Deprecation warning: " + msg);
			}
		}
		return extend(function () {
			if (firstTime) {
				printMsg();
				firstTime = false;
			}
			return fn.apply(this, arguments);
		}, fn);
	}

	function padToken(func, count) {
		return function (a) {
			return leftZeroFill(func.call(this, a), count);
		};
	}
	function ordinalizeToken(func, period) {
		return function (a) {
			return this.lang().ordinal(func.call(this, a), period);
		};
	}

	while (ordinalizeTokens.length) {
		i = ordinalizeTokens.pop();
		formatTokenFunctions[i + 'o'] = ordinalizeToken(formatTokenFunctions[i], i);
	}
	while (paddedTokens.length) {
		i = paddedTokens.pop();
		formatTokenFunctions[i + i] = padToken(formatTokenFunctions[i], 2);
	}
	formatTokenFunctions.DDDD = padToken(formatTokenFunctions.DDD, 3);


	/************************************
		Constructors
	************************************/

	function Language() {

	}

	// Moment prototype object
	function Moment(config) {
		checkOverflow(config);
		extend(this, config);
	}

	// Duration Constructor
	function Duration(duration) {
		var normalizedInput = normalizeObjectUnits(duration),
			years = normalizedInput.year || 0,
			quarters = normalizedInput.quarter || 0,
			months = normalizedInput.month || 0,
			weeks = normalizedInput.week || 0,
			days = normalizedInput.day || 0,
			hours = normalizedInput.hour || 0,
			minutes = normalizedInput.minute || 0,
			seconds = normalizedInput.second || 0,
			milliseconds = normalizedInput.millisecond || 0;

		// representation for dateAddRemove
		this._milliseconds = +milliseconds +
			seconds * 1e3 + // 1000
			minutes * 6e4 + // 1000 * 60
			hours * 36e5; // 1000 * 60 * 60
		// Because of dateAddRemove treats 24 hours as different from a
		// day when working around DST, we need to store them separately
		this._days = +days +
			weeks * 7;
		// It is impossible translate months into days without knowing
		// which months you are are talking about, so we have to store
		// it separately.
		this._months = +months +
			quarters * 3 +
			years * 12;

		this._data = {};

		this._bubble();
	}

	/************************************
		Helpers
	************************************/


	function extend(a, b) {
		for (var i in b) {
			if (b.hasOwnProperty(i)) {
				a[i] = b[i];
			}
		}

		if (b.hasOwnProperty("toString")) {
			a.toString = b.toString;
		}

		if (b.hasOwnProperty("valueOf")) {
			a.valueOf = b.valueOf;
		}

		return a;
	}

	function cloneMoment(m) {
		var result = {}, i;
		for (i in m) {
			if (m.hasOwnProperty(i) && momentProperties.hasOwnProperty(i)) {
				result[i] = m[i];
			}
		}

		return result;
	}

	function absRound(number) {
		if (number < 0) {
			return Math.ceil(number);
		} else {
			return Math.floor(number);
		}
	}

	// left zero fill a number
	// see http://jsperf.com/left-zero-filling for performance comparison
	function leftZeroFill(number, targetLength, forceSign) {
		var output = '' + Math.abs(number),
			sign = number >= 0;

		while (output.length < targetLength) {
			output = '0' + output;
		}
		return (sign ? (forceSign ? '+' : '') : '-') + output;
	}

	// helper function for _.addTime and _.subtractTime
	function addOrSubtractDurationFromMoment(mom, duration, isAdding, updateOffset) {
		var milliseconds = duration._milliseconds,
			days = duration._days,
			months = duration._months;
		updateOffset = updateOffset == null ? true : updateOffset;

		if (milliseconds) {
			mom._d.setTime(+mom._d + milliseconds * isAdding);
		}
		if (days) {
			rawSetter(mom, 'Date', rawGetter(mom, 'Date') + days * isAdding);
		}
		if (months) {
			rawMonthSetter(mom, rawGetter(mom, 'Month') + months * isAdding);
		}
		if (updateOffset) {
			moment.updateOffset(mom, days || months);
		}
	}

	// check if is an array
	function isArray(input) {
		return Object.prototype.toString.call(input) === '[object Array]';
	}

	function isDate(input) {
		return  Object.prototype.toString.call(input) === '[object Date]' ||
				input instanceof Date;
	}

	// compare two arrays, return the number of differences
	function compareArrays(array1, array2, dontConvert) {
		var len = Math.min(array1.length, array2.length),
			lengthDiff = Math.abs(array1.length - array2.length),
			diffs = 0,
			i;
		for (i = 0; i < len; i++) {
			if ((dontConvert && array1[i] !== array2[i]) ||
				(!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
				diffs++;
			}
		}
		return diffs + lengthDiff;
	}

	function normalizeUnits(units) {
		if (units) {
			var lowered = units.toLowerCase().replace(/(.)s$/, '$1');
			units = unitAliases[units] || camelFunctions[lowered] || lowered;
		}
		return units;
	}

	function normalizeObjectUnits(inputObject) {
		var normalizedInput = {},
			normalizedProp,
			prop;

		for (prop in inputObject) {
			if (inputObject.hasOwnProperty(prop)) {
				normalizedProp = normalizeUnits(prop);
				if (normalizedProp) {
					normalizedInput[normalizedProp] = inputObject[prop];
				}
			}
		}

		return normalizedInput;
	}

	function makeList(field) {
		var count, setter;

		if (field.indexOf('week') === 0) {
			count = 7;
			setter = 'day';
		}
		else if (field.indexOf('month') === 0) {
			count = 12;
			setter = 'month';
		}
		else {
			return;
		}

		moment[field] = function (format, index) {
			var i, getter,
				method = moment.fn._lang[field],
				results = [];

			if (typeof format === 'number') {
				index = format;
				format = undefined;
			}

			getter = function (i) {
				var m = moment().utc().set(setter, i);
				return method.call(moment.fn._lang, m, format || '');
			};

			if (index != null) {
				return getter(index);
			}
			else {
				for (i = 0; i < count; i++) {
					results.push(getter(i));
				}
				return results;
			}
		};
	}

	function toInt(argumentForCoercion) {
		var coercedNumber = +argumentForCoercion,
			value = 0;

		if (coercedNumber !== 0 && isFinite(coercedNumber)) {
			if (coercedNumber >= 0) {
				value = Math.floor(coercedNumber);
			} else {
				value = Math.ceil(coercedNumber);
			}
		}

		return value;
	}

	function daysInMonth(year, month) {
		return new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
	}

	function weeksInYear(year, dow, doy) {
		return weekOfYear(moment([year, 11, 31 + dow - doy]), dow, doy).week;
	}

	function daysInYear(year) {
		return isLeapYear(year) ? 366 : 365;
	}

	function isLeapYear(year) {
		return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
	}

	function checkOverflow(m) {
		var overflow;
		if (m._a && m._pf.overflow === -2) {
			overflow =
				m._a[MONTH] < 0 || m._a[MONTH] > 11 ? MONTH :
				m._a[DATE] < 1 || m._a[DATE] > daysInMonth(m._a[YEAR], m._a[MONTH]) ? DATE :
				m._a[HOUR] < 0 || m._a[HOUR] > 23 ? HOUR :
				m._a[MINUTE] < 0 || m._a[MINUTE] > 59 ? MINUTE :
				m._a[SECOND] < 0 || m._a[SECOND] > 59 ? SECOND :
				m._a[MILLISECOND] < 0 || m._a[MILLISECOND] > 999 ? MILLISECOND :
				-1;

			if (m._pf._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
				overflow = DATE;
			}

			m._pf.overflow = overflow;
		}
	}

	function isValid(m) {
		if (m._isValid == null) {
			m._isValid = !isNaN(m._d.getTime()) &&
				m._pf.overflow < 0 &&
				!m._pf.empty &&
				!m._pf.invalidMonth &&
				!m._pf.nullInput &&
				!m._pf.invalidFormat &&
				!m._pf.userInvalidated;

			if (m._strict) {
				m._isValid = m._isValid &&
					m._pf.charsLeftOver === 0 &&
					m._pf.unusedTokens.length === 0;
			}
		}
		return m._isValid;
	}

	function normalizeLanguage(key) {
		return key ? key.toLowerCase().replace('_', '-') : key;
	}

	// Return a moment from input, that is local/utc/zone equivalent to model.
	function makeAs(input, model) {
		return model._isUTC ? moment(input).zone(model._offset || 0) :
			moment(input).local();
	}

	/************************************
		Languages
	************************************/


	extend(Language.prototype, {

		set : function (config) {
			var prop, i;
			for (i in config) {
				prop = config[i];
				if (typeof prop === 'function') {
					this[i] = prop;
				} else {
					this['_' + i] = prop;
				}
			}
		},

		_months : "January_February_March_April_May_June_July_August_September_October_November_December".split("_"),
		months : function (m) {
			return this._months[m.month()];
		},

		_monthsShort : "Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),
		monthsShort : function (m) {
			return this._monthsShort[m.month()];
		},

		monthsParse : function (monthName) {
			var i, mom, regex;

			if (!this._monthsParse) {
				this._monthsParse = [];
			}

			for (i = 0; i < 12; i++) {
				// make the regex if we don't have it already
				if (!this._monthsParse[i]) {
					mom = moment.utc([2000, i]);
					regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
					this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
				}
				// test the regex
				if (this._monthsParse[i].test(monthName)) {
					return i;
				}
			}
		},

		_weekdays : "Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),
		weekdays : function (m) {
			return this._weekdays[m.day()];
		},

		_weekdaysShort : "Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),
		weekdaysShort : function (m) {
			return this._weekdaysShort[m.day()];
		},

		_weekdaysMin : "Su_Mo_Tu_We_Th_Fr_Sa".split("_"),
		weekdaysMin : function (m) {
			return this._weekdaysMin[m.day()];
		},

		weekdaysParse : function (weekdayName) {
			var i, mom, regex;

			if (!this._weekdaysParse) {
				this._weekdaysParse = [];
			}

			for (i = 0; i < 7; i++) {
				// make the regex if we don't have it already
				if (!this._weekdaysParse[i]) {
					mom = moment([2000, 1]).day(i);
					regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
					this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
				}
				// test the regex
				if (this._weekdaysParse[i].test(weekdayName)) {
					return i;
				}
			}
		},

		_longDateFormat : {
			LT : "h:mm A",
			L : "MM/DD/YYYY",
			LL : "MMMM D YYYY",
			LLL : "MMMM D YYYY LT",
			LLLL : "dddd, MMMM D YYYY LT"
		},
		longDateFormat : function (key) {
			var output = this._longDateFormat[key];
			if (!output && this._longDateFormat[key.toUpperCase()]) {
				output = this._longDateFormat[key.toUpperCase()].replace(/MMMM|MM|DD|dddd/g, function (val) {
					return val.slice(1);
				});
				this._longDateFormat[key] = output;
			}
			return output;
		},

		isPM : function (input) {
			// IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
			// Using charAt should be more compatible.
			return ((input + '').toLowerCase().charAt(0) === 'p');
		},

		_meridiemParse : /[ap]\.?m?\.?/i,
		meridiem : function (hours, minutes, isLower) {
			if (hours > 11) {
				return isLower ? 'pm' : 'PM';
			} else {
				return isLower ? 'am' : 'AM';
			}
		},

		_calendar : {
			sameDay : '[Today at] LT',
			nextDay : '[Tomorrow at] LT',
			nextWeek : 'dddd [at] LT',
			lastDay : '[Yesterday at] LT',
			lastWeek : '[Last] dddd [at] LT',
			sameElse : 'L'
		},
		calendar : function (key, mom) {
			var output = this._calendar[key];
			return typeof output === 'function' ? output.apply(mom) : output;
		},

		_relativeTime : {
			future : "in %s",
			past : "%s ago",
			s : "a few seconds",
			m : "a minute",
			mm : "%d minutes",
			h : "an hour",
			hh : "%d hours",
			d : "a day",
			dd : "%d days",
			M : "a month",
			MM : "%d months",
			y : "a year",
			yy : "%d years"
		},
		relativeTime : function (number, withoutSuffix, string, isFuture) {
			var output = this._relativeTime[string];
			return (typeof output === 'function') ?
				output(number, withoutSuffix, string, isFuture) :
				output.replace(/%d/i, number);
		},
		pastFuture : function (diff, output) {
			var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
			return typeof format === 'function' ? format(output) : format.replace(/%s/i, output);
		},

		ordinal : function (number) {
			return this._ordinal.replace("%d", number);
		},
		_ordinal : "%d",

		preparse : function (string) {
			return string;
		},

		postformat : function (string) {
			return string;
		},

		week : function (mom) {
			return weekOfYear(mom, this._week.dow, this._week.doy).week;
		},

		_week : {
			dow : 0, // Sunday is the first day of the week.
			doy : 6  // The week that contains Jan 1st is the first week of the year.
		},

		_invalidDate: 'Invalid date',
		invalidDate: function () {
			return this._invalidDate;
		}
	});

	// Loads a language definition into the `languages` cache.  The function
	// takes a key and optionally values.  If not in the browser and no values
	// are provided, it will load the language file module.  As a convenience,
	// this function also returns the language values.
	function loadLang(key, values) {
		values.abbr = key;
		if (!languages[key]) {
			languages[key] = new Language();
		}
		languages[key].set(values);
		return languages[key];
	}

	// Remove a language from the `languages` cache. Mostly useful in tests.
	function unloadLang(key) {
		delete languages[key];
	}

	// Determines which language definition to use and returns it.
	//
	// With no parameters, it will return the global language.  If you
	// pass in a language key, such as 'en', it will return the
	// definition for 'en', so long as 'en' has already been loaded using
	// moment.lang.
	function getLangDefinition(key) {
		var i = 0, j, lang, next, split,
			get = function (k) {
				if (!languages[k] && hasModule) {
					try {
						require('./lang/' + k);
					} catch (e) { }
				}
				return languages[k];
			};

		if (!key) {
			return moment.fn._lang;
		}

		if (!isArray(key)) {
			//short-circuit everything else
			lang = get(key);
			if (lang) {
				return lang;
			}
			key = [key];
		}

		//pick the language from the array
		//try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
		//substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
		while (i < key.length) {
			split = normalizeLanguage(key[i]).split('-');
			j = split.length;
			next = normalizeLanguage(key[i + 1]);
			next = next ? next.split('-') : null;
			while (j > 0) {
				lang = get(split.slice(0, j).join('-'));
				if (lang) {
					return lang;
				}
				if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
					//the next array item is better than a shallower substring of this one
					break;
				}
				j--;
			}
			i++;
		}
		return moment.fn._lang;
	}

	/************************************
		Formatting
	************************************/


	function removeFormattingTokens(input) {
		if (input.match(/\[[\s\S]/)) {
			return input.replace(/^\[|\]$/g, "");
		}
		return input.replace(/\\/g, "");
	}

	function makeFormatFunction(format) {
		var array = format.match(formattingTokens), i, length;

		for (i = 0, length = array.length; i < length; i++) {
			if (formatTokenFunctions[array[i]]) {
				array[i] = formatTokenFunctions[array[i]];
			} else {
				array[i] = removeFormattingTokens(array[i]);
			}
		}

		return function (mom) {
			var output = "";
			for (i = 0; i < length; i++) {
				output += array[i] instanceof Function ? array[i].call(mom, format) : array[i];
			}
			return output;
		};
	}

	// format date using native date object
	function formatMoment(m, format) {

		if (!m.isValid()) {
			return m.lang().invalidDate();
		}

		format = expandFormat(format, m.lang());

		if (!formatFunctions[format]) {
			formatFunctions[format] = makeFormatFunction(format);
		}

		return formatFunctions[format](m);
	}

	function expandFormat(format, lang) {
		var i = 5;

		function replaceLongDateFormatTokens(input) {
			return lang.longDateFormat(input) || input;
		}

		localFormattingTokens.lastIndex = 0;
		while (i >= 0 && localFormattingTokens.test(format)) {
			format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
			localFormattingTokens.lastIndex = 0;
			i -= 1;
		}

		return format;
	}


	/************************************
		Parsing
	************************************/


	// get the regex to find the next token
	function getParseRegexForToken(token, config) {
		var a, strict = config._strict;
		switch (token) {
		case 'Q':
			return parseTokenOneDigit;
		case 'DDDD':
			return parseTokenThreeDigits;
		case 'YYYY':
		case 'GGGG':
		case 'gggg':
			return strict ? parseTokenFourDigits : parseTokenOneToFourDigits;
		case 'Y':
		case 'G':
		case 'g':
			return parseTokenSignedNumber;
		case 'YYYYYY':
		case 'YYYYY':
		case 'GGGGG':
		case 'ggggg':
			return strict ? parseTokenSixDigits : parseTokenOneToSixDigits;
		case 'S':
			if (strict) { return parseTokenOneDigit; }
			/* falls through */
		case 'SS':
			if (strict) { return parseTokenTwoDigits; }
			/* falls through */
		case 'SSS':
			if (strict) { return parseTokenThreeDigits; }
			/* falls through */
		case 'DDD':
			return parseTokenOneToThreeDigits;
		case 'MMM':
		case 'MMMM':
		case 'dd':
		case 'ddd':
		case 'dddd':
			return parseTokenWord;
		case 'a':
		case 'A':
			return getLangDefinition(config._l)._meridiemParse;
		case 'X':
			return parseTokenTimestampMs;
		case 'Z':
		case 'ZZ':
			return parseTokenTimezone;
		case 'T':
			return parseTokenT;
		case 'SSSS':
			return parseTokenDigits;
		case 'MM':
		case 'DD':
		case 'YY':
		case 'GG':
		case 'gg':
		case 'HH':
		case 'hh':
		case 'mm':
		case 'ss':
		case 'ww':
		case 'WW':
			return strict ? parseTokenTwoDigits : parseTokenOneOrTwoDigits;
		case 'M':
		case 'D':
		case 'd':
		case 'H':
		case 'h':
		case 'm':
		case 's':
		case 'w':
		case 'W':
		case 'e':
		case 'E':
			return parseTokenOneOrTwoDigits;
		case 'Do':
			return parseTokenOrdinal;
		default :
			a = new RegExp(regexpEscape(unescapeFormat(token.replace('\\', '')), "i"));
			return a;
		}
	}

	function timezoneMinutesFromString(string) {
		string = string || "";
		var possibleTzMatches = (string.match(parseTokenTimezone) || []),
			tzChunk = possibleTzMatches[possibleTzMatches.length - 1] || [],
			parts = (tzChunk + '').match(parseTimezoneChunker) || ['-', 0, 0],
			minutes = +(parts[1] * 60) + toInt(parts[2]);

		return parts[0] === '+' ? -minutes : minutes;
	}

	// function to convert string input to date
	function addTimeToArrayFromToken(token, input, config) {
		var a, datePartArray = config._a;

		switch (token) {
		// QUARTER
		case 'Q':
			if (input != null) {
				datePartArray[MONTH] = (toInt(input) - 1) * 3;
			}
			break;
		// MONTH
		case 'M' : // fall through to MM
		case 'MM' :
			if (input != null) {
				datePartArray[MONTH] = toInt(input) - 1;
			}
			break;
		case 'MMM' : // fall through to MMMM
		case 'MMMM' :
			a = getLangDefinition(config._l).monthsParse(input);
			// if we didn't find a month name, mark the date as invalid.
			if (a != null) {
				datePartArray[MONTH] = a;
			} else {
				config._pf.invalidMonth = input;
			}
			break;
		// DAY OF MONTH
		case 'D' : // fall through to DD
		case 'DD' :
			if (input != null) {
				datePartArray[DATE] = toInt(input);
			}
			break;
		case 'Do' :
			if (input != null) {
				datePartArray[DATE] = toInt(parseInt(input, 10));
			}
			break;
		// DAY OF YEAR
		case 'DDD' : // fall through to DDDD
		case 'DDDD' :
			if (input != null) {
				config._dayOfYear = toInt(input);
			}

			break;
		// YEAR
		case 'YY' :
			datePartArray[YEAR] = moment.parseTwoDigitYear(input);
			break;
		case 'YYYY' :
		case 'YYYYY' :
		case 'YYYYYY' :
			datePartArray[YEAR] = toInt(input);
			break;
		// AM / PM
		case 'a' : // fall through to A
		case 'A' :
			config._isPm = getLangDefinition(config._l).isPM(input);
			break;
		// 24 HOUR
		case 'H' : // fall through to hh
		case 'HH' : // fall through to hh
		case 'h' : // fall through to hh
		case 'hh' :
			datePartArray[HOUR] = toInt(input);
			break;
		// MINUTE
		case 'm' : // fall through to mm
		case 'mm' :
			datePartArray[MINUTE] = toInt(input);
			break;
		// SECOND
		case 's' : // fall through to ss
		case 'ss' :
			datePartArray[SECOND] = toInt(input);
			break;
		// MILLISECOND
		case 'S' :
		case 'SS' :
		case 'SSS' :
		case 'SSSS' :
			datePartArray[MILLISECOND] = toInt(('0.' + input) * 1000);
			break;
		// UNIX TIMESTAMP WITH MS
		case 'X':
			config._d = new Date(parseFloat(input) * 1000);
			break;
		// TIMEZONE
		case 'Z' : // fall through to ZZ
		case 'ZZ' :
			config._useUTC = true;
			config._tzm = timezoneMinutesFromString(input);
			break;
		case 'w':
		case 'ww':
		case 'W':
		case 'WW':
		case 'd':
		case 'dd':
		case 'ddd':
		case 'dddd':
		case 'e':
		case 'E':
			token = token.substr(0, 1);
			/* falls through */
		case 'gg':
		case 'gggg':
		case 'GG':
		case 'GGGG':
		case 'GGGGG':
			token = token.substr(0, 2);
			if (input) {
				config._w = config._w || {};
				config._w[token] = input;
			}
			break;
		}
	}

	// convert an array to a date.
	// the array should mirror the parameters below
	// note: all values past the year are optional and will default to the lowest possible value.
	// [year, month, day , hour, minute, second, millisecond]
	function dateFromConfig(config) {
		var i, date, input = [], currentDate,
			yearToUse, fixYear, w, temp, lang, weekday, week;

		if (config._d) {
			return;
		}

		currentDate = currentDateArray(config);

		//compute day of the year from weeks and weekdays
		if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
			fixYear = function (val) {
				var intVal = parseInt(val, 10);
				return val ?
				  (val.length < 3 ? (intVal > 68 ? 1900 + intVal : 2000 + intVal) : intVal) :
				  (config._a[YEAR] == null ? moment().weekYear() : config._a[YEAR]);
			};

			w = config._w;
			if (w.GG != null || w.W != null || w.E != null) {
				temp = dayOfYearFromWeeks(fixYear(w.GG), w.W || 1, w.E, 4, 1);
			}
			else {
				lang = getLangDefinition(config._l);
				weekday = w.d != null ?  parseWeekday(w.d, lang) :
				  (w.e != null ?  parseInt(w.e, 10) + lang._week.dow : 0);

				week = parseInt(w.w, 10) || 1;

				//if we're parsing 'd', then the low day numbers may be next week
				if (w.d != null && weekday < lang._week.dow) {
					week++;
				}

				temp = dayOfYearFromWeeks(fixYear(w.gg), week, weekday, lang._week.doy, lang._week.dow);
			}

			config._a[YEAR] = temp.year;
			config._dayOfYear = temp.dayOfYear;
		}

		//if the day of the year is set, figure out what it is
		if (config._dayOfYear) {
			yearToUse = config._a[YEAR] == null ? currentDate[YEAR] : config._a[YEAR];

			if (config._dayOfYear > daysInYear(yearToUse)) {
				config._pf._overflowDayOfYear = true;
			}

			date = makeUTCDate(yearToUse, 0, config._dayOfYear);
			config._a[MONTH] = date.getUTCMonth();
			config._a[DATE] = date.getUTCDate();
		}

		// Default to current date.
		// * if no year, month, day of month are given, default to today
		// * if day of month is given, default month and year
		// * if month is given, default only year
		// * if year is given, don't default anything
		for (i = 0; i < 3 && config._a[i] == null; ++i) {
			config._a[i] = input[i] = currentDate[i];
		}

		// Zero out whatever was not defaulted, including time
		for (; i < 7; i++) {
			config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
		}

		// add the offsets to the time to be parsed so that we can have a clean array for checking isValid
		input[HOUR] += toInt((config._tzm || 0) / 60);
		input[MINUTE] += toInt((config._tzm || 0) % 60);

		config._d = (config._useUTC ? makeUTCDate : makeDate).apply(null, input);
	}

	function dateFromObject(config) {
		var normalizedInput;

		if (config._d) {
			return;
		}

		normalizedInput = normalizeObjectUnits(config._i);
		config._a = [
			normalizedInput.year,
			normalizedInput.month,
			normalizedInput.day,
			normalizedInput.hour,
			normalizedInput.minute,
			normalizedInput.second,
			normalizedInput.millisecond
		];

		dateFromConfig(config);
	}

	function currentDateArray(config) {
		var now = new Date();
		if (config._useUTC) {
			return [
				now.getUTCFullYear(),
				now.getUTCMonth(),
				now.getUTCDate()
			];
		} else {
			return [now.getFullYear(), now.getMonth(), now.getDate()];
		}
	}

	// date from string and format string
	function makeDateFromStringAndFormat(config) {

		config._a = [];
		config._pf.empty = true;

		// This array is used to make a Date, either with `new Date` or `Date.UTC`
		var lang = getLangDefinition(config._l),
			string = '' + config._i,
			i, parsedInput, tokens, token, skipped,
			stringLength = string.length,
			totalParsedInputLength = 0;

		tokens = expandFormat(config._f, lang).match(formattingTokens) || [];

		for (i = 0; i < tokens.length; i++) {
			token = tokens[i];
			parsedInput = (string.match(getParseRegexForToken(token, config)) || [])[0];
			if (parsedInput) {
				skipped = string.substr(0, string.indexOf(parsedInput));
				if (skipped.length > 0) {
					config._pf.unusedInput.push(skipped);
				}
				string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
				totalParsedInputLength += parsedInput.length;
			}
			// don't parse if it's not a known token
			if (formatTokenFunctions[token]) {
				if (parsedInput) {
					config._pf.empty = false;
				}
				else {
					config._pf.unusedTokens.push(token);
				}
				addTimeToArrayFromToken(token, parsedInput, config);
			}
			else if (config._strict && !parsedInput) {
				config._pf.unusedTokens.push(token);
			}
		}

		// add remaining unparsed input length to the string
		config._pf.charsLeftOver = stringLength - totalParsedInputLength;
		if (string.length > 0) {
			config._pf.unusedInput.push(string);
		}

		// handle am pm
		if (config._isPm && config._a[HOUR] < 12) {
			config._a[HOUR] += 12;
		}
		// if is 12 am, change hours to 0
		if (config._isPm === false && config._a[HOUR] === 12) {
			config._a[HOUR] = 0;
		}

		dateFromConfig(config);
		checkOverflow(config);
	}

	function unescapeFormat(s) {
		return s.replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
			return p1 || p2 || p3 || p4;
		});
	}

	// Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
	function regexpEscape(s) {
		return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
	}

	// date from string and array of format strings
	function makeDateFromStringAndArray(config) {
		var tempConfig,
			bestMoment,

			scoreToBeat,
			i,
			currentScore;

		if (config._f.length === 0) {
			config._pf.invalidFormat = true;
			config._d = new Date(NaN);
			return;
		}

		for (i = 0; i < config._f.length; i++) {
			currentScore = 0;
			tempConfig = extend({}, config);
			tempConfig._pf = defaultParsingFlags();
			tempConfig._f = config._f[i];
			makeDateFromStringAndFormat(tempConfig);

			if (!isValid(tempConfig)) {
				continue;
			}

			// if there is any input that was not parsed add a penalty for that format
			currentScore += tempConfig._pf.charsLeftOver;

			//or tokens
			currentScore += tempConfig._pf.unusedTokens.length * 10;

			tempConfig._pf.score = currentScore;

			if (scoreToBeat == null || currentScore < scoreToBeat) {
				scoreToBeat = currentScore;
				bestMoment = tempConfig;
			}
		}

		extend(config, bestMoment || tempConfig);
	}

	// date from iso format
	function makeDateFromString(config) {
		var i, l,
			string = config._i,
			match = isoRegex.exec(string);

		if (match) {
			config._pf.iso = true;
			for (i = 0, l = isoDates.length; i < l; i++) {
				if (isoDates[i][1].exec(string)) {
					// match[5] should be "T" or undefined
					config._f = isoDates[i][0] + (match[6] || " ");
					break;
				}
			}
			for (i = 0, l = isoTimes.length; i < l; i++) {
				if (isoTimes[i][1].exec(string)) {
					config._f += isoTimes[i][0];
					break;
				}
			}
			if (string.match(parseTokenTimezone)) {
				config._f += "Z";
			}
			makeDateFromStringAndFormat(config);
		}
		else {
			moment.createFromInputFallback(config);
		}
	}

	function makeDateFromInput(config) {
		var input = config._i,
			matched = aspNetJsonRegex.exec(input);

		if (input === undefined) {
			config._d = new Date();
		} else if (matched) {
			config._d = new Date(+matched[1]);
		} else if (typeof input === 'string') {
			makeDateFromString(config);
		} else if (isArray(input)) {
			config._a = input.slice(0);
			dateFromConfig(config);
		} else if (isDate(input)) {
			config._d = new Date(+input);
		} else if (typeof(input) === 'object') {
			dateFromObject(config);
		} else if (typeof(input) === 'number') {
			// from milliseconds
			config._d = new Date(input);
		} else {
			moment.createFromInputFallback(config);
		}
	}

	function makeDate(y, m, d, h, M, s, ms) {
		//can't just apply() to create a date:
		//http://stackoverflow.com/questions/181348/instantiating-a-javascript-object-by-calling-prototype-constructor-apply
		var date = new Date(y, m, d, h, M, s, ms);

		//the date constructor doesn't accept years < 1970
		if (y < 1970) {
			date.setFullYear(y);
		}
		return date;
	}

	function makeUTCDate(y) {
		var date = new Date(Date.UTC.apply(null, arguments));
		if (y < 1970) {
			date.setUTCFullYear(y);
		}
		return date;
	}

	function parseWeekday(input, language) {
		if (typeof input === 'string') {
			if (!isNaN(input)) {
				input = parseInt(input, 10);
			}
			else {
				input = language.weekdaysParse(input);
				if (typeof input !== 'number') {
					return null;
				}
			}
		}
		return input;
	}

	/************************************
		Relative Time
	************************************/


	// helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
	function substituteTimeAgo(string, number, withoutSuffix, isFuture, lang) {
		return lang.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
	}

	function relativeTime(milliseconds, withoutSuffix, lang) {
		var seconds = round(Math.abs(milliseconds) / 1000),
			minutes = round(seconds / 60),
			hours = round(minutes / 60),
			days = round(hours / 24),
			years = round(days / 365),
			args = seconds < 45 && ['s', seconds] ||
				minutes === 1 && ['m'] ||
				minutes < 45 && ['mm', minutes] ||
				hours === 1 && ['h'] ||
				hours < 22 && ['hh', hours] ||
				days === 1 && ['d'] ||
				days <= 25 && ['dd', days] ||
				days <= 45 && ['M'] ||
				days < 345 && ['MM', round(days / 30)] ||
				years === 1 && ['y'] || ['yy', years];
		args[2] = withoutSuffix;
		args[3] = milliseconds > 0;
		args[4] = lang;
		return substituteTimeAgo.apply({}, args);
	}


	/************************************
		Week of Year
	************************************/


	// firstDayOfWeek       0 = sun, 6 = sat
	//                      the day of the week that starts the week
	//                      (usually sunday or monday)
	// firstDayOfWeekOfYear 0 = sun, 6 = sat
	//                      the first week is the week that contains the first
	//                      of this day of the week
	//                      (eg. ISO weeks use thursday (4))
	function weekOfYear(mom, firstDayOfWeek, firstDayOfWeekOfYear) {
		var end = firstDayOfWeekOfYear - firstDayOfWeek,
			daysToDayOfWeek = firstDayOfWeekOfYear - mom.day(),
			adjustedMoment;


		if (daysToDayOfWeek > end) {
			daysToDayOfWeek -= 7;
		}

		if (daysToDayOfWeek < end - 7) {
			daysToDayOfWeek += 7;
		}

		adjustedMoment = moment(mom).add('d', daysToDayOfWeek);
		return {
			week: Math.ceil(adjustedMoment.dayOfYear() / 7),
			year: adjustedMoment.year()
		};
	}

	//http://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
	function dayOfYearFromWeeks(year, week, weekday, firstDayOfWeekOfYear, firstDayOfWeek) {
		var d = makeUTCDate(year, 0, 1).getUTCDay(), daysToAdd, dayOfYear;

		weekday = weekday != null ? weekday : firstDayOfWeek;
		daysToAdd = firstDayOfWeek - d + (d > firstDayOfWeekOfYear ? 7 : 0) - (d < firstDayOfWeek ? 7 : 0);
		dayOfYear = 7 * (week - 1) + (weekday - firstDayOfWeek) + daysToAdd + 1;

		return {
			year: dayOfYear > 0 ? year : year - 1,
			dayOfYear: dayOfYear > 0 ?  dayOfYear : daysInYear(year - 1) + dayOfYear
		};
	}

	/************************************
		Top Level Functions
	************************************/

	function makeMoment(config) {
		var input = config._i,
			format = config._f;

		if (input === null || (format === undefined && input === '')) {
			return moment.invalid({nullInput: true});
		}

		if (typeof input === 'string') {
			config._i = input = getLangDefinition().preparse(input);
		}

		if (moment.isMoment(input)) {
			config = cloneMoment(input);

			config._d = new Date(+input._d);
		} else if (format) {
			if (isArray(format)) {
				makeDateFromStringAndArray(config);
			} else {
				makeDateFromStringAndFormat(config);
			}
		} else {
			makeDateFromInput(config);
		}

		return new Moment(config);
	}

	moment = function (input, format, lang, strict) {
		var c;

		if (typeof(lang) === "boolean") {
			strict = lang;
			lang = undefined;
		}
		// object construction must be done this way.
		// https://github.com/moment/moment/issues/1423
		c = {};
		c._isAMomentObject = true;
		c._i = input;
		c._f = format;
		c._l = lang;
		c._strict = strict;
		c._isUTC = false;
		c._pf = defaultParsingFlags();

		return makeMoment(c);
	};

	moment.suppressDeprecationWarnings = false;

	moment.createFromInputFallback = deprecate(
			"moment construction falls back to js Date. This is " +
			"discouraged and will be removed in upcoming major " +
			"release. Please refer to " +
			"https://github.com/moment/moment/issues/1407 for more info.",
			function (config) {
		config._d = new Date(config._i);
	});

	// creating with utc
	moment.utc = function (input, format, lang, strict) {
		var c;

		if (typeof(lang) === "boolean") {
			strict = lang;
			lang = undefined;
		}
		// object construction must be done this way.
		// https://github.com/moment/moment/issues/1423
		c = {};
		c._isAMomentObject = true;
		c._useUTC = true;
		c._isUTC = true;
		c._l = lang;
		c._i = input;
		c._f = format;
		c._strict = strict;
		c._pf = defaultParsingFlags();

		return makeMoment(c).utc();
	};

	// creating with unix timestamp (in seconds)
	moment.unix = function (input) {
		return moment(input * 1000);
	};

	// duration
	moment.duration = function (input, key) {
		var duration = input,
			// matching against regexp is expensive, do it on demand
			match = null,
			sign,
			ret,
			parseIso;

		if (moment.isDuration(input)) {
			duration = {
				ms: input._milliseconds,
				d: input._days,
				M: input._months
			};
		} else if (typeof input === 'number') {
			duration = {};
			if (key) {
				duration[key] = input;
			} else {
				duration.milliseconds = input;
			}
		} else if (!!(match = aspNetTimeSpanJsonRegex.exec(input))) {
			sign = (match[1] === "-") ? -1 : 1;
			duration = {
				y: 0,
				d: toInt(match[DATE]) * sign,
				h: toInt(match[HOUR]) * sign,
				m: toInt(match[MINUTE]) * sign,
				s: toInt(match[SECOND]) * sign,
				ms: toInt(match[MILLISECOND]) * sign
			};
		} else if (!!(match = isoDurationRegex.exec(input))) {
			sign = (match[1] === "-") ? -1 : 1;
			parseIso = function (inp) {
				// We'd normally use ~~inp for this, but unfortunately it also
				// converts floats to ints.
				// inp may be undefined, so careful calling replace on it.
				var res = inp && parseFloat(inp.replace(',', '.'));
				// apply sign while we're at it
				return (isNaN(res) ? 0 : res) * sign;
			};
			duration = {
				y: parseIso(match[2]),
				M: parseIso(match[3]),
				d: parseIso(match[4]),
				h: parseIso(match[5]),
				m: parseIso(match[6]),
				s: parseIso(match[7]),
				w: parseIso(match[8])
			};
		}

		ret = new Duration(duration);

		if (moment.isDuration(input) && input.hasOwnProperty('_lang')) {
			ret._lang = input._lang;
		}

		return ret;
	};

	// version number
	moment.version = VERSION;

	// default format
	moment.defaultFormat = isoFormat;

	// Plugins that add properties should also add the key here (null value),
	// so we can properly clone ourselves.
	moment.momentProperties = momentProperties;

	// This function will be called whenever a moment is mutated.
	// It is intended to keep the offset in sync with the timezone.
	moment.updateOffset = function () {};

	// This function will load languages and then set the global language.  If
	// no arguments are passed in, it will simply return the current global
	// language key.
	moment.lang = function (key, values) {
		var r;
		if (!key) {
			return moment.fn._lang._abbr;
		}
		if (values) {
			loadLang(normalizeLanguage(key), values);
		} else if (values === null) {
			unloadLang(key);
			key = 'en';
		} else if (!languages[key]) {
			getLangDefinition(key);
		}
		r = moment.duration.fn._lang = moment.fn._lang = getLangDefinition(key);
		return r._abbr;
	};

	// returns language data
	moment.langData = function (key) {
		if (key && key._lang && key._lang._abbr) {
			key = key._lang._abbr;
		}
		return getLangDefinition(key);
	};

	// compare moment object
	moment.isMoment = function (obj) {
		return obj instanceof Moment ||
			(obj != null &&  obj.hasOwnProperty('_isAMomentObject'));
	};

	// for typechecking Duration objects
	moment.isDuration = function (obj) {
		return obj instanceof Duration;
	};

	for (i = lists.length - 1; i >= 0; --i) {
		makeList(lists[i]);
	}

	moment.normalizeUnits = function (units) {
		return normalizeUnits(units);
	};

	moment.invalid = function (flags) {
		var m = moment.utc(NaN);
		if (flags != null) {
			extend(m._pf, flags);
		}
		else {
			m._pf.userInvalidated = true;
		}

		return m;
	};

	moment.parseZone = function () {
		return moment.apply(null, arguments).parseZone();
	};

	moment.parseTwoDigitYear = function (input) {
		return toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
	};

	/************************************
		Moment Prototype
	************************************/


	extend(moment.fn = Moment.prototype, {

		clone : function () {
			return moment(this);
		},

		valueOf : function () {
			return +this._d + ((this._offset || 0) * 60000);
		},

		unix : function () {
			return Math.floor(+this / 1000);
		},

		toString : function () {
			return this.clone().lang('en').format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ");
		},

		toDate : function () {
			return this._offset ? new Date(+this) : this._d;
		},

		toISOString : function () {
			var m = moment(this).utc();
			if (0 < m.year() && m.year() <= 9999) {
				return formatMoment(m, 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
			} else {
				return formatMoment(m, 'YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
			}
		},

		toArray : function () {
			var m = this;
			return [
				m.year(),
				m.month(),
				m.date(),
				m.hours(),
				m.minutes(),
				m.seconds(),
				m.milliseconds()
			];
		},

		isValid : function () {
			return isValid(this);
		},

		isDSTShifted : function () {

			if (this._a) {
				return this.isValid() && compareArrays(this._a, (this._isUTC ? moment.utc(this._a) : moment(this._a)).toArray()) > 0;
			}

			return false;
		},

		parsingFlags : function () {
			return extend({}, this._pf);
		},

		invalidAt: function () {
			return this._pf.overflow;
		},

		utc : function () {
			return this.zone(0);
		},

		local : function () {
			this.zone(0);
			this._isUTC = false;
			return this;
		},

		format : function (inputString) {
			var output = formatMoment(this, inputString || moment.defaultFormat);
			return this.lang().postformat(output);
		},

		add : function (input, val) {
			var dur;
			// switch args to support add('s', 1) and add(1, 's')
			if (typeof input === 'string') {
				dur = moment.duration(+val, input);
			} else {
				dur = moment.duration(input, val);
			}
			addOrSubtractDurationFromMoment(this, dur, 1);
			return this;
		},

		subtract : function (input, val) {
			var dur;
			// switch args to support subtract('s', 1) and subtract(1, 's')
			if (typeof input === 'string') {
				dur = moment.duration(+val, input);
			} else {
				dur = moment.duration(input, val);
			}
			addOrSubtractDurationFromMoment(this, dur, -1);
			return this;
		},

		diff : function (input, units, asFloat) {
			var that = makeAs(input, this),
				zoneDiff = (this.zone() - that.zone()) * 6e4,
				diff, output;

			units = normalizeUnits(units);

			if (units === 'year' || units === 'month') {
				// average number of days in the months in the given dates
				diff = (this.daysInMonth() + that.daysInMonth()) * 432e5; // 24 * 60 * 60 * 1000 / 2
				// difference in months
				output = ((this.year() - that.year()) * 12) + (this.month() - that.month());
				// adjust by taking difference in days, average number of days
				// and dst in the given months.
				output += ((this - moment(this).startOf('month')) -
						(that - moment(that).startOf('month'))) / diff;
				// same as above but with zones, to negate all dst
				output -= ((this.zone() - moment(this).startOf('month').zone()) -
						(that.zone() - moment(that).startOf('month').zone())) * 6e4 / diff;
				if (units === 'year') {
					output = output / 12;
				}
			} else {
				diff = (this - that);
				output = units === 'second' ? diff / 1e3 : // 1000
					units === 'minute' ? diff / 6e4 : // 1000 * 60
					units === 'hour' ? diff / 36e5 : // 1000 * 60 * 60
					units === 'day' ? (diff - zoneDiff) / 864e5 : // 1000 * 60 * 60 * 24, negate dst
					units === 'week' ? (diff - zoneDiff) / 6048e5 : // 1000 * 60 * 60 * 24 * 7, negate dst
					diff;
			}
			return asFloat ? output : absRound(output);
		},

		from : function (time, withoutSuffix) {
			return moment.duration(this.diff(time)).lang(this.lang()._abbr).humanize(!withoutSuffix);
		},

		fromNow : function (withoutSuffix) {
			return this.from(moment(), withoutSuffix);
		},

		calendar : function () {
			// We want to compare the start of today, vs this.
			// Getting start-of-today depends on whether we're zone'd or not.
			var sod = makeAs(moment(), this).startOf('day'),
				diff = this.diff(sod, 'days', true),
				format = diff < -6 ? 'sameElse' :
					diff < -1 ? 'lastWeek' :
					diff < 0 ? 'lastDay' :
					diff < 1 ? 'sameDay' :
					diff < 2 ? 'nextDay' :
					diff < 7 ? 'nextWeek' : 'sameElse';
			return this.format(this.lang().calendar(format, this));
		},

		isLeapYear : function () {
			return isLeapYear(this.year());
		},

		isDST : function () {
			return (this.zone() < this.clone().month(0).zone() ||
				this.zone() < this.clone().month(5).zone());
		},

		day : function (input) {
			var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
			if (input != null) {
				input = parseWeekday(input, this.lang());
				return this.add({ d : input - day });
			} else {
				return day;
			}
		},

		month : makeAccessor('Month', true),

		startOf: function (units) {
			units = normalizeUnits(units);
			// the following switch intentionally omits break keywords
			// to utilize falling through the cases.
			switch (units) {
			case 'year':
				this.month(0);
				/* falls through */
			case 'quarter':
			case 'month':
				this.date(1);
				/* falls through */
			case 'week':
			case 'isoWeek':
			case 'day':
				this.hours(0);
				/* falls through */
			case 'hour':
				this.minutes(0);
				/* falls through */
			case 'minute':
				this.seconds(0);
				/* falls through */
			case 'second':
				this.milliseconds(0);
				/* falls through */
			}

			// weeks are a special case
			if (units === 'week') {
				this.weekday(0);
			} else if (units === 'isoWeek') {
				this.isoWeekday(1);
			}

			// quarters are also special
			if (units === 'quarter') {
				this.month(Math.floor(this.month() / 3) * 3);
			}

			return this;
		},

		endOf: function (units) {
			units = normalizeUnits(units);
			return this.startOf(units).add((units === 'isoWeek' ? 'week' : units), 1).subtract('ms', 1);
		},

		isAfter: function (input, units) {
			units = typeof units !== 'undefined' ? units : 'millisecond';
			return +this.clone().startOf(units) > +moment(input).startOf(units);
		},

		isBefore: function (input, units) {
			units = typeof units !== 'undefined' ? units : 'millisecond';
			return +this.clone().startOf(units) < +moment(input).startOf(units);
		},

		isSame: function (input, units) {
			units = units || 'ms';
			return +this.clone().startOf(units) === +makeAs(input, this).startOf(units);
		},

		min: function (other) {
			other = moment.apply(null, arguments);
			return other < this ? this : other;
		},

		max: function (other) {
			other = moment.apply(null, arguments);
			return other > this ? this : other;
		},

		// keepTime = true means only change the timezone, without affecting
		// the local hour. So 5:31:26 +0300 --[zone(2, true)]--> 5:31:26 +0200
		// It is possible that 5:31:26 doesn't exist int zone +0200, so we
		// adjust the time as needed, to be valid.
		//
		// Keeping the time actually adds/subtracts (one hour)
		// from the actual represented time. That is why we call updateOffset
		// a second time. In case it wants us to change the offset again
		// _changeInProgress == true case, then we have to adjust, because
		// there is no such time in the given timezone.
		zone : function (input, keepTime) {
			var offset = this._offset || 0;
			if (input != null) {
				if (typeof input === "string") {
					input = timezoneMinutesFromString(input);
				}
				if (Math.abs(input) < 16) {
					input = input * 60;
				}
				this._offset = input;
				this._isUTC = true;
				if (offset !== input) {
					if (!keepTime || this._changeInProgress) {
						addOrSubtractDurationFromMoment(this,
								moment.duration(offset - input, 'm'), 1, false);
					} else if (!this._changeInProgress) {
						this._changeInProgress = true;
						moment.updateOffset(this, true);
						this._changeInProgress = null;
					}
				}
			} else {
				return this._isUTC ? offset : this._d.getTimezoneOffset();
			}
			return this;
		},

		zoneAbbr : function () {
			return this._isUTC ? "UTC" : "";
		},

		zoneName : function () {
			return this._isUTC ? "Coordinated Universal Time" : "";
		},

		parseZone : function () {
			if (this._tzm) {
				this.zone(this._tzm);
			} else if (typeof this._i === 'string') {
				this.zone(this._i);
			}
			return this;
		},

		hasAlignedHourOffset : function (input) {
			if (!input) {
				input = 0;
			}
			else {
				input = moment(input).zone();
			}

			return (this.zone() - input) % 60 === 0;
		},

		daysInMonth : function () {
			return daysInMonth(this.year(), this.month());
		},

		dayOfYear : function (input) {
			var dayOfYear = round((moment(this).startOf('day') - moment(this).startOf('year')) / 864e5) + 1;
			return input == null ? dayOfYear : this.add("d", (input - dayOfYear));
		},

		quarter : function (input) {
			return input == null ? Math.ceil((this.month() + 1) / 3) : this.month((input - 1) * 3 + this.month() % 3);
		},

		weekYear : function (input) {
			var year = weekOfYear(this, this.lang()._week.dow, this.lang()._week.doy).year;
			return input == null ? year : this.add("y", (input - year));
		},

		isoWeekYear : function (input) {
			var year = weekOfYear(this, 1, 4).year;
			return input == null ? year : this.add("y", (input - year));
		},

		week : function (input) {
			var week = this.lang().week(this);
			return input == null ? week : this.add("d", (input - week) * 7);
		},

		isoWeek : function (input) {
			var week = weekOfYear(this, 1, 4).week;
			return input == null ? week : this.add("d", (input - week) * 7);
		},

		weekday : function (input) {
			var weekday = (this.day() + 7 - this.lang()._week.dow) % 7;
			return input == null ? weekday : this.add("d", input - weekday);
		},

		isoWeekday : function (input) {
			// behaves the same as moment#day except
			// as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
			// as a setter, sunday should belong to the previous week.
			return input == null ? this.day() || 7 : this.day(this.day() % 7 ? input : input - 7);
		},

		isoWeeksInYear : function () {
			return weeksInYear(this.year(), 1, 4);
		},

		weeksInYear : function () {
			var weekInfo = this._lang._week;
			return weeksInYear(this.year(), weekInfo.dow, weekInfo.doy);
		},

		get : function (units) {
			units = normalizeUnits(units);
			return this[units]();
		},

		set : function (units, value) {
			units = normalizeUnits(units);
			if (typeof this[units] === 'function') {
				this[units](value);
			}
			return this;
		},

		// If passed a language key, it will set the language for this
		// instance.  Otherwise, it will return the language configuration
		// variables for this instance.
		lang : function (key) {
			if (key === undefined) {
				return this._lang;
			} else {
				this._lang = getLangDefinition(key);
				return this;
			}
		}
	});

	function rawMonthSetter(mom, value) {
		var dayOfMonth;

		// TODO: Move this out of here!
		if (typeof value === 'string') {
			value = mom.lang().monthsParse(value);
			// TODO: Another silent failure?
			if (typeof value !== 'number') {
				return mom;
			}
		}

		dayOfMonth = Math.min(mom.date(),
				daysInMonth(mom.year(), value));
		mom._d['set' + (mom._isUTC ? 'UTC' : '') + 'Month'](value, dayOfMonth);
		return mom;
	}

	function rawGetter(mom, unit) {
		return mom._d['get' + (mom._isUTC ? 'UTC' : '') + unit]();
	}

	function rawSetter(mom, unit, value) {
		if (unit === 'Month') {
			return rawMonthSetter(mom, value);
		} else {
			return mom._d['set' + (mom._isUTC ? 'UTC' : '') + unit](value);
		}
	}

	function makeAccessor(unit, keepTime) {
		return function (value) {
			if (value != null) {
				rawSetter(this, unit, value);
				moment.updateOffset(this, keepTime);
				return this;
			} else {
				return rawGetter(this, unit);
			}
		};
	}

	moment.fn.millisecond = moment.fn.milliseconds = makeAccessor('Milliseconds', false);
	moment.fn.second = moment.fn.seconds = makeAccessor('Seconds', false);
	moment.fn.minute = moment.fn.minutes = makeAccessor('Minutes', false);
	// Setting the hour should keep the time, because the user explicitly
	// specified which hour he wants. So trying to maintain the same hour (in
	// a new timezone) makes sense. Adding/subtracting hours does not follow
	// this rule.
	moment.fn.hour = moment.fn.hours = makeAccessor('Hours', true);
	// moment.fn.month is defined separately
	moment.fn.date = makeAccessor('Date', true);
	moment.fn.dates = deprecate("dates accessor is deprecated. Use date instead.", makeAccessor('Date', true));
	moment.fn.year = makeAccessor('FullYear', true);
	moment.fn.years = deprecate("years accessor is deprecated. Use year instead.", makeAccessor('FullYear', true));

	// add plural methods
	moment.fn.days = moment.fn.day;
	moment.fn.months = moment.fn.month;
	moment.fn.weeks = moment.fn.week;
	moment.fn.isoWeeks = moment.fn.isoWeek;
	moment.fn.quarters = moment.fn.quarter;

	// add aliased format methods
	moment.fn.toJSON = moment.fn.toISOString;

	/************************************
		Duration Prototype
	************************************/


	extend(moment.duration.fn = Duration.prototype, {

		_bubble : function () {
			var milliseconds = this._milliseconds,
				days = this._days,
				months = this._months,
				data = this._data,
				seconds, minutes, hours, years;

			// The following code bubbles up values, see the tests for
			// examples of what that means.
			data.milliseconds = milliseconds % 1000;

			seconds = absRound(milliseconds / 1000);
			data.seconds = seconds % 60;

			minutes = absRound(seconds / 60);
			data.minutes = minutes % 60;

			hours = absRound(minutes / 60);
			data.hours = hours % 24;

			days += absRound(hours / 24);
			data.days = days % 30;

			months += absRound(days / 30);
			data.months = months % 12;

			years = absRound(months / 12);
			data.years = years;
		},

		weeks : function () {
			return absRound(this.days() / 7);
		},

		valueOf : function () {
			return this._milliseconds +
			  this._days * 864e5 +
			  (this._months % 12) * 2592e6 +
			  toInt(this._months / 12) * 31536e6;
		},

		humanize : function (withSuffix) {
			var difference = +this,
				output = relativeTime(difference, !withSuffix, this.lang());

			if (withSuffix) {
				output = this.lang().pastFuture(difference, output);
			}

			return this.lang().postformat(output);
		},

		add : function (input, val) {
			// supports only 2.0-style add(1, 's') or add(moment)
			var dur = moment.duration(input, val);

			this._milliseconds += dur._milliseconds;
			this._days += dur._days;
			this._months += dur._months;

			this._bubble();

			return this;
		},

		subtract : function (input, val) {
			var dur = moment.duration(input, val);

			this._milliseconds -= dur._milliseconds;
			this._days -= dur._days;
			this._months -= dur._months;

			this._bubble();

			return this;
		},

		get : function (units) {
			units = normalizeUnits(units);
			return this[units.toLowerCase() + 's']();
		},

		as : function (units) {
			units = normalizeUnits(units);
			return this['as' + units.charAt(0).toUpperCase() + units.slice(1) + 's']();
		},

		lang : moment.fn.lang,

		toIsoString : function () {
			// inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
			var years = Math.abs(this.years()),
				months = Math.abs(this.months()),
				days = Math.abs(this.days()),
				hours = Math.abs(this.hours()),
				minutes = Math.abs(this.minutes()),
				seconds = Math.abs(this.seconds() + this.milliseconds() / 1000);

			if (!this.asSeconds()) {
				// this is the same as C#'s (Noda) and python (isodate)...
				// but not other JS (goog.date)
				return 'P0D';
			}

			return (this.asSeconds() < 0 ? '-' : '') +
				'P' +
				(years ? years + 'Y' : '') +
				(months ? months + 'M' : '') +
				(days ? days + 'D' : '') +
				((hours || minutes || seconds) ? 'T' : '') +
				(hours ? hours + 'H' : '') +
				(minutes ? minutes + 'M' : '') +
				(seconds ? seconds + 'S' : '');
		}
	});

	function makeDurationGetter(name) {
		moment.duration.fn[name] = function () {
			return this._data[name];
		};
	}

	function makeDurationAsGetter(name, factor) {
		moment.duration.fn['as' + name] = function () {
			return +this / factor;
		};
	}

	for (i in unitMillisecondFactors) {
		if (unitMillisecondFactors.hasOwnProperty(i)) {
			makeDurationAsGetter(i, unitMillisecondFactors[i]);
			makeDurationGetter(i.toLowerCase());
		}
	}

	makeDurationAsGetter('Weeks', 6048e5);
	moment.duration.fn.asMonths = function () {
		return (+this - this.years() * 31536e6) / 2592e6 + this.years() * 12;
	};


	/************************************
		Default Lang
	************************************/


	// Set default language, other languages will inherit from English.
	moment.lang('en', {
		ordinal : function (number) {
			var b = number % 10,
				output = (toInt(number % 100 / 10) === 1) ? 'th' :
				(b === 1) ? 'st' :
				(b === 2) ? 'nd' :
				(b === 3) ? 'rd' : 'th';
			return number + output;
		}
	});

	$.moment = moment;

}).call(this);

}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("moment", moduleFactory);
}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 
/**
 * plupload.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

// JSLint defined globals
/*global window:false, escape:false */

/*!@@version@@*/

(function() {
	var count = 0, runtimes = [], i18n = {}, mimes = {},
		xmlEncodeChars = {'<' : 'lt', '>' : 'gt', '&' : 'amp', '"' : 'quot', '\'' : '#39'},
		xmlEncodeRegExp = /[<>&\"\']/g, undef, delay = window.setTimeout,
		// A place to store references to event handlers
		eventhash = {},
		uid;

	// IE W3C like event funcs
	function preventDefault() {
		this.returnValue = false;
	}

	function stopPropagation() {
		this.cancelBubble = true;
	}

	// Parses the default mime types string into a mimes lookup map
	(function(mime_data) {
		var items = mime_data.split(/,/), i, y, ext;

		for (i = 0; i < items.length; i += 2) {
			ext = items[i + 1].split(/ /);

			for (y = 0; y < ext.length; y++) {
				mimes[ext[y]] = items[i];
			}
		}
	})(
		"application/msword,doc dot," +
		"application/pdf,pdf," +
		"application/pgp-signature,pgp," +
		"application/postscript,ps ai eps," +
		"application/rtf,rtf," +
		"application/vnd.ms-excel,xls xlb," +
		"application/vnd.ms-powerpoint,ppt pps pot," +
		"application/zip,zip," +
		"application/x-shockwave-flash,swf swfl," +
		"application/vnd.openxmlformats-officedocument.wordprocessingml.document,docx," +
		"application/vnd.openxmlformats-officedocument.wordprocessingml.template,dotx," +
		"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,xlsx," +
		"application/vnd.openxmlformats-officedocument.presentationml.presentation,pptx," + 
		"application/vnd.openxmlformats-officedocument.presentationml.template,potx," +
		"application/vnd.openxmlformats-officedocument.presentationml.slideshow,ppsx," +
		"application/x-javascript,js," +
		"application/json,json," +
		"audio/mpeg,mpga mpega mp2 mp3," +
		"audio/x-wav,wav," +
		"audio/mp4,m4a," +
		"image/bmp,bmp," +
		"image/gif,gif," +
		"image/jpeg,jpeg jpg jpe," +
		"image/photoshop,psd," +
		"image/png,png," +
		"image/svg+xml,svg svgz," +
		"image/tiff,tiff tif," +
		"text/plain,asc txt text diff log," +
		"text/html,htm html xhtml," +
		"text/css,css," +
		"text/csv,csv," +
		"text/rtf,rtf," +
		"video/mpeg,mpeg mpg mpe m2v," +
		"video/quicktime,qt mov," +
		"video/mp4,mp4," +
		"video/x-m4v,m4v," +
		"video/x-flv,flv," +
		"video/x-ms-wmv,wmv," +
		"video/avi,avi," +
		"video/webm,webm," +
		"video/3gpp,3gp," +
		"video/3gpp2,3g2," +
		"video/vnd.rn-realvideo,rv," +
		"application/vnd.oasis.opendocument.formula-template,otf," +
		"application/octet-stream,exe," +

		// Special cases for .rar file mime type
		".rar,rar"
		// "application/x-rar,rar"
		// "application/x-rar-compressed,rar"
	);

	/**
	 * Plupload class with some global constants and functions.
	 *
	 * @example
	 * // Encode entities
	 * console.log(plupload.xmlEncode("My string &lt;&gt;"));
	 *
	 * // Generate unique id
	 * console.log(plupload.guid());
	 *
	 * @static
	 * @class plupload
	 */
	var plupload = {
		/**
		 * Plupload version will be replaced on build.
		 */
		VERSION : '@@version@@',

		/**
		 * Inital state of the queue and also the state ones it's finished all it's uploads.
		 *
		 * @property STOPPED
		 * @final
		 */
		STOPPED : 1,

		/**
		 * Upload process is running
		 *
		 * @property STARTED
		 * @final
		 */
		STARTED : 2,

		/**
		 * File is queued for upload
		 *
		 * @property QUEUED
		 * @final
		 */
		QUEUED : 1,

		/**
		 * File is being uploaded
		 *
		 * @property UPLOADING
		 * @final
		 */
		UPLOADING : 2,

		/**
		 * File has failed to be uploaded
		 *
		 * @property FAILED
		 * @final
		 */
		FAILED : 4,

		/**
		 * File has been uploaded successfully
		 *
		 * @property DONE
		 * @final
		 */
		DONE : 5,

		// Error constants used by the Error event

		/**
		 * Generic error for example if an exception is thrown inside Silverlight.
		 *
		 * @property GENERIC_ERROR
		 * @final
		 */
		GENERIC_ERROR : -100,

		/**
		 * HTTP transport error. For example if the server produces a HTTP status other than 200.
		 *
		 * @property HTTP_ERROR
		 * @final
		 */
		HTTP_ERROR : -200,

		/**
		 * Generic I/O error. For exampe if it wasn't possible to open the file stream on local machine.
		 *
		 * @property IO_ERROR
		 * @final
		 */
		IO_ERROR : -300,

		/**
		 * Generic I/O error. For exampe if it wasn't possible to open the file stream on local machine.
		 *
		 * @property SECURITY_ERROR
		 * @final
		 */
		SECURITY_ERROR : -400,

		/**
		 * Initialization error. Will be triggered if no runtime was initialized.
		 *
		 * @property INIT_ERROR
		 * @final
		 */
		INIT_ERROR : -500,

		/**
		 * File size error. If the user selects a file that is too large it will be blocked and an error of this type will be triggered.
		 *
		 * @property FILE_SIZE_ERROR
		 * @final
		 */
		FILE_SIZE_ERROR : -600,

		/**
		 * File extension error. If the user selects a file that isn't valid according to the filters setting.
		 *
		 * @property FILE_EXTENSION_ERROR
		 * @final
		 */
		FILE_EXTENSION_ERROR : -601,
		
		/**
		 * Runtime will try to detect if image is proper one. Otherwise will throw this error.
		 *
		 * @property IMAGE_FORMAT_ERROR
		 * @final
		 */
		IMAGE_FORMAT_ERROR : -700,
		
		/**
		 * While working on the image runtime will try to detect if the operation may potentially run out of memeory and will throw this error.
		 *
		 * @property IMAGE_MEMORY_ERROR
		 * @final
		 */
		IMAGE_MEMORY_ERROR : -701,
		
		/**
		 * Each runtime has an upper limit on a dimension of the image it can handle. If bigger, will throw this error.
		 *
		 * @property IMAGE_DIMENSIONS_ERROR
		 * @final
		 */
		IMAGE_DIMENSIONS_ERROR : -702,
		

		/**
		 * Mime type lookup table.
		 *
		 * @property mimeTypes
		 * @type Object
		 * @final
		 */
		mimeTypes : mimes,
		
		/**
		 * In some cases sniffing is the only way around :(
		 */
		ua: (function() {
			var nav = navigator, userAgent = nav.userAgent, vendor = nav.vendor, webkit, opera, safari;
			
			webkit = /WebKit/.test(userAgent);
			safari = webkit && vendor.indexOf('Apple') !== -1;
			opera = window.opera && window.opera.buildNumber;
			
			return {
				windows: navigator.platform.indexOf('Win') !== -1,
				android: /Android/.test(userAgent),
				ie: !webkit && !opera && (/MSIE/gi).test(userAgent) && (/Explorer/gi).test(nav.appName),
				webkit: webkit,
				gecko: !webkit && /Gecko/.test(userAgent),
				safari: safari,
				opera: !!opera
			};
		}()),
		
		/**
		 * Gets the true type of the built-in object (better version of typeof).
		 * @credits Angus Croll (http://javascriptweblog.wordpress.com/)
		 *
		 * @param {Object} o Object to check.
		 * @return {String} Object [[Class]]
		 */
		typeOf: function(o) {
			return ({}).toString.call(o).match(/\s([a-z|A-Z]+)/)[1].toLowerCase();
		},

		/**
		 * Extends the specified object with another object.
		 *
		 * @method extend
		 * @param {Object} target Object to extend.
		 * @param {Object..} obj Multiple objects to extend with.
		 * @return {Object} Same as target, the extended object.
		 */
		extend : function(target) {
			plupload.each(arguments, function(arg, i) {
				if (i > 0) {
					plupload.each(arg, function(value, key) {
						target[key] = value;
					});
				}
			});

			return target;
		},

		/**
		 * Cleans the specified name from national characters (diacritics). The result will be a name with only a-z, 0-9 and _.
		 *
		 * @method cleanName
		 * @param {String} s String to clean up.
		 * @return {String} Cleaned string.
		 */
		cleanName : function(name) {
			var i, lookup;

			// Replace diacritics
			lookup = [
				/[\300-\306]/g, 'A', /[\340-\346]/g, 'a', 
				/\307/g, 'C', /\347/g, 'c',
				/[\310-\313]/g, 'E', /[\350-\353]/g, 'e',
				/[\314-\317]/g, 'I', /[\354-\357]/g, 'i',
				/\321/g, 'N', /\361/g, 'n',
				/[\322-\330]/g, 'O', /[\362-\370]/g, 'o',
				/[\331-\334]/g, 'U', /[\371-\374]/g, 'u'
			];

			for (i = 0; i < lookup.length; i += 2) {
				name = name.replace(lookup[i], lookup[i + 1]);
			}

			// Replace whitespace
			name = name.replace(/\s+/g, '_');

			// Remove anything else
			name = name.replace(/[^a-z0-9_\-\.]+/gi, '');

			return name;
		},

		/**
		 * Adds a specific upload runtime like for example flash or gears.
		 *
		 * @method addRuntime
		 * @param {String} name Runtime name for example flash.
		 * @param {Object} obj Object containing init/destroy method.
		 */
		addRuntime : function(name, runtime) {			
			runtime.name = name;
			runtimes[name] = runtime;
			runtimes.push(runtime);

			return runtime;
		},

		/**
		 * Generates an unique ID. This is 99.99% unique since it takes the current time and 5 random numbers.
		 * The only way a user would be able to get the same ID is if the two persons at the same exact milisecond manages
		 * to get 5 the same random numbers between 0-65535 it also uses a counter so each call will be guaranteed to be page unique.
		 * It's more probable for the earth to be hit with an ansteriod. You can also if you want to be 100% sure set the plupload.guidPrefix property
		 * to an user unique key.
		 *
		 * @method guid
		 * @return {String} Virtually unique id.
		 */
		guid : function() {
			var guid = new Date().getTime().toString(32), i;

			for (i = 0; i < 5; i++) {
				guid += Math.floor(Math.random() * 65535).toString(32);
			}

			return (plupload.guidPrefix || 'p') + guid + (count++).toString(32);
		},

		/**
		 * Builds a full url out of a base URL and an object with items to append as query string items.
		 *
		 * @param {String} url Base URL to append query string items to.
		 * @param {Object} items Name/value object to serialize as a querystring.
		 * @return {String} String with url + serialized query string items.
		 */
		buildUrl : function(url, items) {
			var query = '';

			plupload.each(items, function(value, name) {
				query += (query ? '&' : '') + encodeURIComponent(name) + '=' + encodeURIComponent(value);
			});

			if (query) {
				url += (url.indexOf('?') > 0 ? '&' : '?') + query;
			}

			return url;
		},

		/**
		 * Executes the callback function for each item in array/object. If you return false in the
		 * callback it will break the loop.
		 *
		 * @param {Object} obj Object to iterate.
		 * @param {function} callback Callback function to execute for each item.
		 */
		each : function(obj, callback) {
			var length, key, i;

			if (obj) {
				length = obj.length;

				if (length === undef) {
					// Loop object items
					for (key in obj) {
						if (obj.hasOwnProperty(key)) {
							if (callback(obj[key], key) === false) {
								return;
							}
						}
					}
				} else {
					// Loop array items
					for (i = 0; i < length; i++) {
						if (callback(obj[i], i) === false) {
							return;
						}
					}
				}
			}
		},

		/**
		 * Formats the specified number as a size string for example 1024 becomes 1 KB.
		 *
		 * @method formatSize
		 * @param {Number} size Size to format as string.
		 * @return {String} Formatted size string.
		 */
		formatSize : function(size) {
			if (size === undef || /\D/.test(size)) {
				return plupload.translate('N/A');
			}
			
			// GB
			if (size > 1073741824) {
				return Math.round(size / 1073741824, 1) + " GB";
			}

			// MB
			if (size > 1048576) {
				return Math.round(size / 1048576, 1) + " MB";
			}

			// KB
			if (size > 1024) {
				return Math.round(size / 1024, 1) + " KB";
			}

			return size + " b";
		},

		/**
		 * Returns the absolute x, y position of an Element. The position will be returned in a object with x, y fields.
		 *
		 * @method getPos
		 * @param {Element} node HTML element or element id to get x, y position from.
		 * @param {Element} root Optional root element to stop calculations at.
		 * @return {object} Absolute position of the specified element object with x, y fields.
		 */
		 getPos : function(node, root) {
			var x = 0, y = 0, parent, doc = document, nodeRect, rootRect;

			node = node;
			root = root || doc.body;

			// Returns the x, y cordinate for an element on IE 6 and IE 7
			function getIEPos(node) {
				var bodyElm, rect, x = 0, y = 0;

				if (node) {
					rect = node.getBoundingClientRect();
					bodyElm = doc.compatMode === "CSS1Compat" ? doc.documentElement : doc.body;
					x = rect.left + bodyElm.scrollLeft;
					y = rect.top + bodyElm.scrollTop;
				}

				return {
					x : x,
					y : y
				};
			}

			// Use getBoundingClientRect on IE 6 and IE 7 but not on IE 8 in standards mode
			if (node && node.getBoundingClientRect && plupload.ua.ie && (!doc.documentMode || doc.documentMode < 8)) {
				nodeRect = getIEPos(node);
				rootRect = getIEPos(root);

				return {
					x : nodeRect.x - rootRect.x,
					y : nodeRect.y - rootRect.y
				};
			}

			parent = node;
			while (parent && parent != root && parent.nodeType) {
				x += parent.offsetLeft || 0;
				y += parent.offsetTop || 0;
				parent = parent.offsetParent;
			}

			parent = node.parentNode;
			while (parent && parent != root && parent.nodeType) {
				x -= parent.scrollLeft || 0;
				y -= parent.scrollTop || 0;
				parent = parent.parentNode;
			}

			return {
				x : x,
				y : y
			};
		},

		/**
		 * Returns the size of the specified node in pixels.
		 *
		 * @param {Node} node Node to get the size of.
		 * @return {Object} Object with a w and h property.
		 */
		getSize : function(node) {
			return {
				w : node.offsetWidth || node.clientWidth,
				h : node.offsetHeight || node.clientHeight
			};
		},

		/**
		 * Parses the specified size string into a byte value. For example 10kb becomes 10240.
		 *
		 * @method parseSize
		 * @param {String/Number} size String to parse or number to just pass through.
		 * @return {Number} Size in bytes.
		 */
		parseSize : function(size) {
			var mul;

			if (typeof(size) == 'string') {
				size = /^([0-9]+)([mgk]?)$/.exec(size.toLowerCase().replace(/[^0-9mkg]/g, ''));
				mul = size[2];
				size = +size[1];

				if (mul == 'g') {
					size *= 1073741824;
				}

				if (mul == 'm') {
					size *= 1048576;
				}

				if (mul == 'k') {
					size *= 1024;
				}
			}

			return size;
		},

		/**
		 * Encodes the specified string.
		 *
		 * @method xmlEncode
		 * @param {String} s String to encode.
		 * @return {String} Encoded string.
		 */
		xmlEncode : function(str) {
			return str ? ('' + str).replace(xmlEncodeRegExp, function(chr) {
				return xmlEncodeChars[chr] ? '&' + xmlEncodeChars[chr] + ';' : chr;
			}) : str;
		},

		/**
		 * Forces anything into an array.
		 *
		 * @method toArray
		 * @param {Object} obj Object with length field.
		 * @return {Array} Array object containing all items.
		 */
		toArray : function(obj) {
			var i, arr = [];

			for (i = 0; i < obj.length; i++) {
				arr[i] = obj[i];
			}

			return arr;
		},
		
		/**
		 * Find an element in array and return it's index if present, otherwise return -1.
		 *
		 * @method inArray
		 * @param {mixed} needle Element to find
		 * @param {Array} array
		 * @return {Int} Index of the element, or -1 if not found
		 */
		inArray : function(needle, array) {			
			if (array) {
				if (Array.prototype.indexOf) {
					return Array.prototype.indexOf.call(array, needle);
				}
			
				for (var i = 0, length = array.length; i < length; i++) {
					if (array[i] === needle) {
						return i;
					}
				}
			}
			return -1;
		},

		/**
		 * Extends the language pack object with new items.
		 *
		 * @param {Object} pack Language pack items to add.
		 * @return {Object} Extended language pack object.
		 */
		addI18n : function(pack) {
			return plupload.extend(i18n, pack);
		},

		/**
		 * Translates the specified string by checking for the english string in the language pack lookup.
		 *
		 * @param {String} str String to look for.
		 * @return {String} Translated string or the input string if it wasn't found.
		 */
		translate : function(str) {
			return i18n[str] || str;
		},
		
		/**
		 * Checks if object is empty.
		 *
		 * @param {Object} obj Object to check.
		 * @return {Boolean}
		 */
		isEmptyObj : function(obj) {
			if (obj === undef) return true;
			
			for (var prop in obj) {
				return false;	
			}
			return true;
		},
		
		/**
		 * Checks if specified DOM element has specified class.
		 *
		 * @param {Object} obj DOM element like object to add handler to.
		 * @param {String} name Class name
		 */
		hasClass : function(obj, name) {
			var regExp;
		
			if (obj.className == '') {
				return false;
			}

			regExp = new RegExp("(^|\\s+)"+name+"(\\s+|$)");

			return regExp.test(obj.className);
		},
		
		/**
		 * Adds specified className to specified DOM element.
		 *
		 * @param {Object} obj DOM element like object to add handler to.
		 * @param {String} name Class name
		 */
		addClass : function(obj, name) {
			if (!plupload.hasClass(obj, name)) {
				obj.className = obj.className == '' ? name : obj.className.replace(/\s+$/, '')+' '+name;
			}
		},
		
		/**
		 * Removes specified className from specified DOM element.
		 *
		 * @param {Object} obj DOM element like object to add handler to.
		 * @param {String} name Class name
		 */
		removeClass : function(obj, name) {
			var regExp = new RegExp("(^|\\s+)"+name+"(\\s+|$)");
			
			obj.className = obj.className.replace(regExp, function($0, $1, $2) {
				return $1 === ' ' && $2 === ' ' ? ' ' : '';
			});
		},
	
		/**
		 * Returns a given computed style of a DOM element.
		 *
		 * @param {Object} obj DOM element like object.
		 * @param {String} name Style you want to get from the DOM element
		 */
		getStyle : function(obj, name) {
			if (obj.currentStyle) {
				return obj.currentStyle[name];
			} else if (window.getComputedStyle) {
				return window.getComputedStyle(obj, null)[name];
			}
		},

		/**
		 * Adds an event handler to the specified object and store reference to the handler
		 * in objects internal Plupload registry (@see removeEvent).
		 *
		 * @param {Object} obj DOM element like object to add handler to.
		 * @param {String} name Name to add event listener to.
		 * @param {Function} callback Function to call when event occurs.
		 * @param {String} (optional) key that might be used to add specifity to the event record.
		 */
		addEvent : function(obj, name, callback) {
			var func, events, types, key;
			
			// if passed in, event will be locked with this key - one would need to provide it to removeEvent
			key = arguments[3];
						
			name = name.toLowerCase();
						
			// Initialize unique identifier if needed
			if (uid === undef) {
				uid = 'Plupload_' + plupload.guid();
			}

			// Add event listener
			if (obj.addEventListener) {
				func = callback;
				
				obj.addEventListener(name, func, false);
			} else if (obj.attachEvent) {
				
				func = function() {
					var evt = window.event;

					if (!evt.target) {
						evt.target = evt.srcElement;
					}

					evt.preventDefault = preventDefault;
					evt.stopPropagation = stopPropagation;

					callback(evt);
				};
				obj.attachEvent('on' + name, func);
			} 
			
			// Log event handler to objects internal Plupload registry
			if (obj[uid] === undef) {
				obj[uid] = plupload.guid();
			}
			
			if (!eventhash.hasOwnProperty(obj[uid])) {
				eventhash[obj[uid]] = {};
			}
			
			events = eventhash[obj[uid]];
			
			if (!events.hasOwnProperty(name)) {
				events[name] = [];
			}
					
			events[name].push({
				func: func,
				orig: callback, // store original callback for IE
				key: key
			});
		},
		
		
		/**
		 * Remove event handler from the specified object. If third argument (callback)
		 * is not specified remove all events with the specified name.
		 *
		 * @param {Object} obj DOM element to remove event listener(s) from.
		 * @param {String} name Name of event listener to remove.
		 * @param {Function|String} (optional) might be a callback or unique key to match.
		 */
		removeEvent: function(obj, name) {
			var type, callback, key;
			
			// match the handler either by callback or by key	
			if (typeof(arguments[2]) == "function") {
				callback = arguments[2];
			} else {
				key = arguments[2];
			}
						
			name = name.toLowerCase();
			
			if (obj[uid] && eventhash[obj[uid]] && eventhash[obj[uid]][name]) {
				type = eventhash[obj[uid]][name];
			} else {
				return;
			}
			
				
			for (var i=type.length-1; i>=0; i--) {
				// undefined or not, key should match			
				if (type[i].key === key || type[i].orig === callback) {
										
					if (obj.removeEventListener) {
						obj.removeEventListener(name, type[i].func, false);		
					} else if (obj.detachEvent) {
						obj.detachEvent('on'+name, type[i].func);
					}
					
					type[i].orig = null;
					type[i].func = null;
					
					type.splice(i, 1);
					
					// If callback was passed we are done here, otherwise proceed
					if (callback !== undef) {
						break;
					}
				}			
			}	
			
			// If event array got empty, remove it
			if (!type.length) {
				delete eventhash[obj[uid]][name];
			}
			
			// If Plupload registry has become empty, remove it
			if (plupload.isEmptyObj(eventhash[obj[uid]])) {
				delete eventhash[obj[uid]];
				
				// IE doesn't let you remove DOM object property with - delete
				try {
					delete obj[uid];
				} catch(e) {
					obj[uid] = undef;
				}
			}
		},
		
		
		/**
		 * Remove all kind of events from the specified object
		 *
		 * @param {Object} obj DOM element to remove event listeners from.
		 * @param {String} (optional) unique key to match, when removing events.
		 */
		removeAllEvents: function(obj) {
			var key = arguments[1];
			
			if (obj[uid] === undef || !obj[uid]) {
				return;
			}
			
			plupload.each(eventhash[obj[uid]], function(events, name) {
				plupload.removeEvent(obj, name, key);
			});		
		}
	};
	

	/**
	 * Uploader class, an instance of this class will be created for each upload field.
	 *
	 * @example
	 * var uploader = new plupload.Uploader({
	 *     runtimes : 'gears,html5,flash',
	 *     browse_button : 'button_id'
	 * });
	 *
	 * uploader.bind('Init', function(up) {
	 *     alert('Supports drag/drop: ' + (!!up.features.dragdrop));
	 * });
	 *
	 * uploader.bind('FilesAdded', function(up, files) {
	 *     alert('Selected files: ' + files.length);
	 * });
	 *
	 * uploader.bind('QueueChanged', function(up) {
	 *     alert('Queued files: ' + uploader.files.length);
	 * });
	 *
	 * uploader.init();
	 *
	 * @class plupload.Uploader
	 */

	/**
	 * Constructs a new uploader instance.
	 *
	 * @constructor
	 * @method Uploader
	 * @param {Object} settings Initialization settings, to be used by the uploader instance and runtimes.
	 */
	plupload.Uploader = function(settings) {
		var events = {}, total, files = [], startTime, disabled = false;

		// Inital total state
		total = new plupload.QueueProgress();

		// Default settings
		settings = plupload.extend({
			chunk_size : 0,
			multipart : true,
			multi_selection : true,
			file_data_name : 'file',
			filters : []
		}, settings);

		// Private methods
		function uploadNext() {
			var file, count = 0, i;

			if (this.state == plupload.STARTED) {
				// Find first QUEUED file
				for (i = 0; i < files.length; i++) {
					if (!file && files[i].status == plupload.QUEUED) {
						file = files[i];
						file.status = plupload.UPLOADING;
						if (this.trigger("BeforeUpload", file)) {
							this.trigger("UploadFile", file);
						}
					} else {
						count++;
					}
				}

				// All files are DONE or FAILED
				if (count == files.length) {
					this.stop();
					this.trigger("UploadComplete", files);
				}
			}
		}

		function calc() {
			var i, file;

			// Reset stats
			total.reset();

			// Check status, size, loaded etc on all files
			for (i = 0; i < files.length; i++) {
				file = files[i];

				if (file.size !== undef) {
					total.size += file.size;
					total.loaded += file.loaded;
				} else {
					total.size = undef;
				}

				if (file.status == plupload.DONE) {
					total.uploaded++;
				} else if (file.status == plupload.FAILED) {
					total.failed++;
				} else {
					total.queued++;
				}
			}

			// If we couldn't calculate a total file size then use the number of files to calc percent
			if (total.size === undef) {
				total.percent = files.length > 0 ? Math.ceil(total.uploaded / files.length * 100) : 0;
			} else {
				total.bytesPerSec = Math.ceil(total.loaded / ((+new Date() - startTime || 1) / 1000.0));
				total.percent = total.size > 0 ? Math.ceil(total.loaded / total.size * 100) : 0;
			}
		}

		// Add public methods
		plupload.extend(this, {
			/**
			 * Current state of the total uploading progress. This one can either be plupload.STARTED or plupload.STOPPED.
			 * These states are controlled by the stop/start methods. The default value is STOPPED.
			 *
			 * @property state
			 * @type Number
			 */
			state : plupload.STOPPED,
			
			/**
			 * Current runtime name.
			 *
			 * @property runtime
			 * @type String
			 */
			runtime: '',

			/**
			 * Map of features that are available for the uploader runtime. Features will be filled
			 * before the init event is called, these features can then be used to alter the UI for the end user.
			 * Some of the current features that might be in this map is: dragdrop, chunks, jpgresize, pngresize.
			 *
			 * @property features
			 * @type Object
			 */
			features : {},

			/**
			 * Current upload queue, an array of File instances.
			 *
			 * @property files
			 * @type Array
			 * @see plupload.File
			 */
			files : files,

			/**
			 * Object with name/value settings.
			 *
			 * @property settings
			 * @type Object
			 */
			settings : settings,

			/**
			 * Total progess information. How many files has been uploaded, total percent etc.
			 *
			 * @property total
			 * @type plupload.QueueProgress
			 */
			total : total,

			/**
			 * Unique id for the Uploader instance.
			 *
			 * @property id
			 * @type String
			 */
			id : plupload.guid(),

			/**
			 * Initializes the Uploader instance and adds internal event listeners.
			 *
			 * @method init
			 */
			init : function() {
				var self = this, i, runtimeList, a, runTimeIndex = 0, items;

				if (typeof(settings.preinit) == "function") {
					settings.preinit(self);
				} else {
					plupload.each(settings.preinit, function(func, name) {
						self.bind(name, func);
					});
				}

				settings.page_url = settings.page_url || document.location.pathname.replace(/\/[^\/]+$/g, '/');

				// If url is relative force it absolute to the current page
				if (!/^(\w+:\/\/|\/)/.test(settings.url)) {
					settings.url = settings.page_url + settings.url;
				}

				// Convert settings
				settings.chunk_size = plupload.parseSize(settings.chunk_size);
				settings.max_file_size = plupload.parseSize(settings.max_file_size);

				// Add files to queue
				self.bind('FilesAdded', function(up, selected_files) {
					var i, file, count = 0, extensionsRegExp, filters = settings.filters;

					// Convert extensions to regexp
					if (filters && filters.length) {
						extensionsRegExp = [];
						
						plupload.each(filters, function(filter) {
							plupload.each(filter.extensions.split(/,/), function(ext) {
								if (/^\s*\*\s*$/.test(ext)) {
									extensionsRegExp.push('\\.*');
								} else {
									extensionsRegExp.push('\\.' + ext.replace(new RegExp('[' + ('/^$.*+?|()[]{}\\'.replace(/./g, '\\$&')) + ']', 'g'), '\\$&'));
								}
							});
						});
						
						extensionsRegExp = new RegExp(extensionsRegExp.join('|') + '$', 'i');
					}

					for (i = 0; i < selected_files.length; i++) {
						file = selected_files[i];
						file.loaded = 0;
						file.percent = 0;
						file.status = plupload.QUEUED;

						// Invalid file extension
						if (extensionsRegExp && !extensionsRegExp.test(file.name)) {
							up.trigger('Error', {
								code : plupload.FILE_EXTENSION_ERROR,
								message : plupload.translate('File extension error.'),
								file : file
							});

							continue;
						}

						// Invalid file size
						if (file.size !== undef && file.size > settings.max_file_size) {
							up.trigger('Error', {
								code : plupload.FILE_SIZE_ERROR,
								message : plupload.translate('File size error.'),
								file : file
							});

							continue;
						}

						// Add valid file to list
						files.push(file);
						count++;
					}

					// Only trigger QueueChanged event if any files where added
					if (count) {
						delay(function() {
							self.trigger("QueueChanged");
							self.refresh();
						}, 1);
					} else {
						return false; // Stop the FilesAdded event from immediate propagation
					}
				});

				// Generate unique target filenames
				if (settings.unique_names) {
					self.bind("UploadFile", function(up, file) {
						var matches = file.name.match(/\.([^.]+)$/), ext = "tmp";

						if (matches) {
							ext = matches[1];
						}

						file.target_name = file.id + '.' + ext;
					});
				}

				self.bind('UploadProgress', function(up, file) {
					file.percent = file.size > 0 ? Math.ceil(file.loaded / file.size * 100) : 100;
					calc();
				});

				self.bind('StateChanged', function(up) {
					if (up.state == plupload.STARTED) {
						// Get start time to calculate bps
						startTime = (+new Date());
						
					} else if (up.state == plupload.STOPPED) {						
						// Reset currently uploading files
						for (i = up.files.length - 1; i >= 0; i--) {
							if (up.files[i].status == plupload.UPLOADING) {
								up.files[i].status = plupload.QUEUED;
								calc();
							}
						}
					}
				});

				self.bind('QueueChanged', calc);

				self.bind("Error", function(up, err) {
					// Set failed status if an error occured on a file
					if (err.file) {
						err.file.status = plupload.FAILED;
						calc();

						// Upload next file but detach it from the error event
						// since other custom listeners might want to stop the queue
						if (up.state == plupload.STARTED) {
							delay(function() {
								uploadNext.call(self);
							}, 1);
						}
					}
				});

				self.bind("FileUploaded", function(up, file) {
					file.status = plupload.DONE;
					file.loaded = file.size;
					up.trigger('UploadProgress', file);

					// Upload next file but detach it from the error event
					// since other custom listeners might want to stop the queue
					delay(function() {
						uploadNext.call(self);
					}, 1);
				});

				// Setup runtimeList
				if (settings.runtimes) {
					runtimeList = [];
					items = settings.runtimes.split(/\s?,\s?/);

					for (i = 0; i < items.length; i++) {
						if (runtimes[items[i]]) {
							runtimeList.push(runtimes[items[i]]);
						}
					}
				} else {
					runtimeList = runtimes;
				}

				// Call init on each runtime in sequence
				function callNextInit() {
					var runtime = runtimeList[runTimeIndex++], features, requiredFeatures, i;

					if (runtime) {
						features = runtime.getFeatures();

						// Check if runtime supports required features
						requiredFeatures = self.settings.required_features;
						if (requiredFeatures) {
							requiredFeatures = requiredFeatures.split(',');

							for (i = 0; i < requiredFeatures.length; i++) {
								// Specified feature doesn't exist
								if (!features[requiredFeatures[i]]) {
									callNextInit();
									return;
								}
							}
						}

						// Try initializing the runtime
						runtime.init(self, function(res) {
							if (res && res.success) {
								// Successful initialization
								self.features = features;
								self.runtime = runtime.name;
								self.trigger('Init', {runtime : runtime.name});
								self.trigger('PostInit');
								self.refresh();
							} else {
								callNextInit();
							}
						});
					} else {
						// Trigger an init error if we run out of runtimes
						self.trigger('Error', {
							code : plupload.INIT_ERROR,
							message : plupload.translate('Init error.')
						});
					}
				}

				callNextInit();

				if (typeof(settings.init) == "function") {
					settings.init(self);
				} else {
					plupload.each(settings.init, function(func, name) {
						self.bind(name, func);
					});
				}
			},

			/**
			 * Refreshes the upload instance by dispatching out a refresh event to all runtimes.
			 * This would for example reposition flash/silverlight shims on the page.
			 *
			 * @method refresh
			 */
			refresh : function() {
				this.trigger("Refresh");
			},

			/**
			 * Starts uploading the queued files.
			 *
			 * @method start
			 */
			start : function() {
				if (files.length && this.state != plupload.STARTED) {
					this.state = plupload.STARTED;
					this.trigger("StateChanged");	
					
					uploadNext.call(this);				
				}
			},

			/**
			 * Stops the upload of the queued files.
			 *
			 * @method stop
			 */
			stop : function() {
				if (this.state != plupload.STOPPED) {
					this.state = plupload.STOPPED;	
					this.trigger("CancelUpload");				
					this.trigger("StateChanged");
				}
			},
			
			/** 
			 * Disables/enables browse button on request.
			 *
			 * @method disableBrowse
			 * @param {Boolean} disable Whether to disable or enable (default: true)
			 */
			disableBrowse : function() {
				disabled = arguments[0] !== undef ? arguments[0] : true;
				this.trigger("DisableBrowse", disabled);
			},

			/**
			 * Returns the specified file object by id.
			 *
			 * @method getFile
			 * @param {String} id File id to look for.
			 * @return {plupload.File} File object or undefined if it wasn't found;
			 */
			getFile : function(id) {
				var i;

				for (i = files.length - 1; i >= 0; i--) {
					if (files[i].id === id) {
						return files[i];
					}
				}
			},

			/**
			 * Removes a specific file.
			 *
			 * @method removeFile
			 * @param {plupload.File} file File to remove from queue.
			 */
			removeFile : function(file) {
				var i;

				for (i = files.length - 1; i >= 0; i--) {
					if (files[i].id === file.id) {
						return this.splice(i, 1)[0];
					}
				}
			},

			/**
			 * Removes part of the queue and returns the files removed. This will also trigger the FilesRemoved and QueueChanged events.
			 *
			 * @method splice
			 * @param {Number} start (Optional) Start index to remove from.
			 * @param {Number} length (Optional) Lengh of items to remove.
			 * @return {Array} Array of files that was removed.
			 */
			splice : function(start, length) {
				var removed;

				// Splice and trigger events
				removed = files.splice(start === undef ? 0 : start, length === undef ? files.length : length);

				this.trigger("FilesRemoved", removed);
				this.trigger("QueueChanged");

				return removed;
			},

			/**
			 * Dispatches the specified event name and it's arguments to all listeners.
			 *
			 *
			 * @method trigger
			 * @param {String} name Event name to fire.
			 * @param {Object..} Multiple arguments to pass along to the listener functions.
			 */
			trigger : function(name) {
				var list = events[name.toLowerCase()], i, args;

				// console.log(name, arguments);

				if (list) {
					// Replace name with sender in args
					args = Array.prototype.slice.call(arguments);
					args[0] = this;

					// Dispatch event to all listeners
					for (i = 0; i < list.length; i++) {
						// Fire event, break chain if false is returned
						if (list[i].func.apply(list[i].scope, args) === false) {
							return false;
						}
					}
				}

				return true;
			},
			
			/**
			 * Check whether uploader has any listeners to the specified event.
			 *
			 * @method hasEventListener
			 * @param {String} name Event name to check for.
			 */
			hasEventListener : function(name) {
				return !!events[name.toLowerCase()];
			},

			/**
			 * Adds an event listener by name.
			 *
			 * @method bind
			 * @param {String} name Event name to listen for.
			 * @param {function} func Function to call ones the event gets fired.
			 * @param {Object} scope Optional scope to execute the specified function in.
			 */
			bind : function(name, func, scope) {
				var list;

				name = name.toLowerCase();
				list = events[name] || [];
				list.push({func : func, scope : scope || this});
				events[name] = list;
			},

			/**
			 * Removes the specified event listener.
			 *
			 * @method unbind
			 * @param {String} name Name of event to remove.
			 * @param {function} func Function to remove from listener.
			 */
			unbind : function(name) {
				name = name.toLowerCase();

				var list = events[name], i, func = arguments[1];

				if (list) {
					if (func !== undef) {
						for (i = list.length - 1; i >= 0; i--) {
							if (list[i].func === func) {
								list.splice(i, 1);
									break;
							}
						}
					} else {
						list = [];
					}

					// delete event list if it has become empty
					if (!list.length) {
						delete events[name];
					}
				}
			},

			/**
			 * Removes all event listeners.
			 *
			 * @method unbindAll
			 */
			unbindAll : function() {
				var self = this;
				
				plupload.each(events, function(list, name) {
					self.unbind(name);
				});
			},
			
			/**
			 * Destroys Plupload instance and cleans after itself.
			 *
			 * @method destroy
			 */
			destroy : function() {	
				this.stop();						
				this.trigger('Destroy');
				
				// Clean-up after uploader itself
				this.unbindAll();
			}

			/**
			 * Fires when the current RunTime has been initialized.
			 *
			 * @event Init
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */

			/**
			 * Fires after the init event incase you need to perform actions there.
			 *
			 * @event PostInit
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */

			/**
			 * Fires when the silverlight/flash or other shim needs to move.
			 *
			 * @event Refresh
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */
	
			/**
			 * Fires when the overall state is being changed for the upload queue.
			 *
			 * @event StateChanged
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */

			/**
			 * Fires when a file is to be uploaded by the runtime.
			 *
			 * @event UploadFile
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {plupload.File} file File to be uploaded.
			 */

			/**
			 * Fires when just before a file is uploaded. This event enables you to override settings
			 * on the uploader instance before the file is uploaded.
			 *
			 * @event BeforeUpload
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {plupload.File} file File to be uploaded.
			 */

			/**
			 * Fires when the file queue is changed. In other words when files are added/removed to the files array of the uploader instance.
			 *
			 * @event QueueChanged
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */
	
			/**
			 * Fires while a file is being uploaded. Use this event to update the current file upload progress.
			 *
			 * @event UploadProgress
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {plupload.File} file File that is currently being uploaded.
			 */

			/**
			 * Fires while a file was removed from queue.
			 *
			 * @event FilesRemoved
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {Array} files Array of files that got removed.
			 */

			/**
			 * Fires while when the user selects files to upload.
			 *
			 * @event FilesAdded
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {Array} files Array of file objects that was added to queue/selected by the user.
			 */

			/**
			 * Fires when a file is successfully uploaded.
			 *
			 * @event FileUploaded
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {plupload.File} file File that was uploaded.
			 * @param {Object} response Object with response properties.
			 */

			/**
			 * Fires when file chunk is uploaded.
			 *
			 * @event ChunkUploaded
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {plupload.File} file File that the chunk was uploaded for.
			 * @param {Object} response Object with response properties.
			 */

			/**
			 * Fires when all files in a queue are uploaded.
			 *
			 * @event UploadComplete
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {Array} files Array of file objects that was added to queue/selected by the user.
			 */

			/**
			 * Fires when a error occurs.
			 *
			 * @event Error
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 * @param {Object} error Contains code, message and sometimes file and other details.
			 */
			 
			 /**
			 * Fires when destroy method is called.
			 *
			 * @event Destroy
			 * @param {plupload.Uploader} uploader Uploader instance sending the event.
			 */
		});
	};

	/**
	 * File instance.
	 *
	 * @class plupload.File
	 * @param {String} name Name of the file.
	 * @param {Number} size File size.
	 */

	/**
	 * Constructs a new file instance.
	 *
	 * @constructor
	 * @method File
	 * @param {String} id Unique file id.
	 * @param {String} name File name.
	 * @param {Number} size File size in bytes.
	 */
	plupload.File = function(id, name, size) {
		var self = this; // Setup alias for self to reduce code size when it's compressed

		/**
		 * File id this is a globally unique id for the specific file.
		 *
		 * @property id
		 * @type String
		 */
		self.id = id;

		/**
		 * File name for example "myfile.gif".
		 *
		 * @property name
		 * @type String
		 */
		self.name = name;

		/**
		 * File size in bytes.
		 *
		 * @property size
		 * @type Number
		 */
		self.size = size;

		/**
		 * Number of bytes uploaded of the files total size.
		 *
		 * @property loaded
		 * @type Number
		 */
		self.loaded = 0;

		/**
		 * Number of percentage uploaded of the file.
		 *
		 * @property percent
		 * @type Number
		 */
		self.percent = 0;

		/**
		 * Status constant matching the plupload states QUEUED, UPLOADING, FAILED, DONE.
		 *
		 * @property status
		 * @type Number
		 * @see plupload
		 */
		self.status = 0;
	};

	/**
	 * Runtime class gets implemented by each upload runtime.
	 *
	 * @class plupload.Runtime
	 * @static
	 */
	plupload.Runtime = function() {
		/**
		 * Returns a list of supported features for the runtime.
		 *
		 * @return {Object} Name/value object with supported features.
		 */
		this.getFeatures = function() {
		};

		/**
		 * Initializes the upload runtime. This method should add necessary items to the DOM and register events needed for operation. 
		 *
		 * @method init
		 * @param {plupload.Uploader} uploader Uploader instance that needs to be initialized.
		 * @param {function} callback Callback function to execute when the runtime initializes or fails to initialize. If it succeeds an object with a parameter name success will be set to true.
		 */
		this.init = function(uploader, callback) {
		};
	};

	/**
	 * Runtime class gets implemented by each upload runtime.
	 *
	 * @class plupload.QueueProgress
	 */

	/**
	 * Constructs a queue progress.
	 *
	 * @constructor
	 * @method QueueProgress
	 */
	 plupload.QueueProgress = function() {
		var self = this; // Setup alias for self to reduce code size when it's compressed

		/**
		 * Total queue file size.
		 *
		 * @property size
		 * @type Number
		 */
		self.size = 0;

		/**
		 * Total bytes uploaded.
		 *
		 * @property loaded
		 * @type Number
		 */
		self.loaded = 0;

		/**
		 * Number of files uploaded.
		 *
		 * @property uploaded
		 * @type Number
		 */
		self.uploaded = 0;

		/**
		 * Number of files failed to upload.
		 *
		 * @property failed
		 * @type Number
		 */
		self.failed = 0;

		/**
		 * Number of files yet to be uploaded.
		 *
		 * @property queued
		 * @type Number
		 */
		self.queued = 0;

		/**
		 * Total percent of the uploaded bytes.
		 *
		 * @property percent
		 * @type Number
		 */
		self.percent = 0;

		/**
		 * Bytes uploaded per second.
		 *
		 * @property bytesPerSec
		 * @type Number
		 */
		self.bytesPerSec = 0;

		/**
		 * Resets the progress to it's initial values.
		 *
		 * @method reset
		 */
		self.reset = function() {
			self.size = self.loaded = self.uploaded = self.failed = self.queued = self.percent = self.bytesPerSec = 0;
		};
	};

	// Create runtimes namespace
	plupload.runtimes = {};

	// Expose plupload namespace
	$.plupload = plupload;
})();
/**
 * plupload.html4.js
 *
 * Copyright 2010, Ryan Demmer
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

// JSLint defined globals
/*global plupload:false, window:false */

(function(window, document, plupload, undef) {
	function getById(id) {
		return document.getElementById(id);
	}

	/**
	 * HTML4 implementation. This runtime has no special features it uses an form that posts files into an hidden iframe.
	 *
	 * @static
	 * @class plupload.runtimes.Html4
	 * @extends plupload.Runtime
	 */
	plupload.runtimes.Html4 = plupload.addRuntime("html4", {
		/**
		 * Returns a list of supported features for the runtime.
		 *
		 * @return {Object} Name/value object with supported features.
		 */
		getFeatures : function() {			
			// Only multipart feature
			return {
				multipart: true,
				
				// WebKit and Gecko 2+ can trigger file dialog progrmmatically
				triggerDialog: (plupload.ua.gecko && window.FormData || plupload.ua.webkit) 
			};
		},

		/**
		 * Initializes the upload runtime.
		 *
		 * @method init
		 * @param {plupload.Uploader} uploader Uploader instance that needs to be initialized.
		 * @param {function} callback Callback to execute when the runtime initializes or fails to initialize. If it succeeds an object with a parameter name success will be set to true.
		 */
		init : function(uploader, callback) {
			uploader.bind("Init", function(up) {
				var container = document.body, iframe, url = "javascript", currentFile,
					input, currentFileId, fileIds = [], IE = /MSIE/.test(navigator.userAgent), mimes = [],
					filters = up.settings.filters, i, ext, type, y;

				// Convert extensions to mime types list
				no_type_restriction:
				for (i = 0; i < filters.length; i++) {
					ext = filters[i].extensions.split(/,/);

					for (y = 0; y < ext.length; y++) {
						
						// If there's an asterisk in the list, then accept attribute is not required
						if (ext[y] === '*') {
							mimes = [];
							break no_type_restriction;
						}
						
						type = plupload.mimeTypes[ext[y]];

						if (type && plupload.inArray(type, mimes) === -1) {
							mimes.push(type);
						}
					}
				}
				
				mimes = mimes.join(',');

				function createForm() {
					var form, input, bgcolor, browseButton;

					// Setup unique id for form
					currentFileId = plupload.guid();
					
					// Save id for Destroy handler
					fileIds.push(currentFileId);

					// Create form
					form = document.createElement('form');
					form.setAttribute('id', 'form_' + currentFileId);
					form.setAttribute('method', 'post');
					form.setAttribute('enctype', 'multipart/form-data');
					form.setAttribute('encoding', 'multipart/form-data');
					form.setAttribute("target", up.id + '_iframe');
					form.style.position = 'absolute';

					// Create input and set attributes
					input = document.createElement('input');
					input.setAttribute('id', 'input_' + currentFileId);
					input.setAttribute('type', 'file');
					input.setAttribute('accept', mimes);
					input.setAttribute('size', 1);
					
					browseButton = getById(up.settings.browse_button);
					
					// Route click event to input element programmatically, if possible
					if (up.features.triggerDialog && browseButton) {
						plupload.addEvent(getById(up.settings.browse_button), 'click', function(e) {
							if (!input.disabled) {
								input.click();
							}
							e.preventDefault();
						}, up.id);
					}

					// Set input styles
					plupload.extend(input.style, {
						width : '100%',
						height : '100%',
						opacity : 0,
						fontSize: '99px', // force input element to be bigger then needed to occupy whole space
						cursor: 'pointer'
					});
					
					plupload.extend(form.style, {
						overflow: 'hidden'
					});

					// Show the container if shim_bgcolor is specified
					bgcolor = up.settings.shim_bgcolor;
					if (bgcolor) {
						form.style.background = bgcolor;
					}

					// no opacity in IE
					if (IE) {
						plupload.extend(input.style, {
							filter : "alpha(opacity=0)"
						});
					}

					// add change event
					plupload.addEvent(input, 'change', function(e) {
						var element = e.target, name, files = [], topElement;

						if (element.value) {
							getById('form_' + currentFileId).style.top = -0xFFFFF + "px";

							// Get file name
							name = element.value.replace(/\\/g, '/');
							name = name.substring(name.length, name.lastIndexOf('/') + 1);

							// Push files
							files.push(new plupload.File(currentFileId, name));
							
							// Clean-up events - they won't be needed anymore
							if (!up.features.triggerDialog) {
								plupload.removeAllEvents(form, up.id);								
							} else {
								plupload.removeEvent(browseButton, 'click', up.id);	
							}
							plupload.removeEvent(input, 'change', up.id);

							// Create and position next form
							createForm();

							// Fire FilesAdded event
							if (files.length) {
								uploader.trigger("FilesAdded", files);
							}							
						}
					}, up.id);

					// append to container
					form.appendChild(input);
					container.appendChild(form);

					up.refresh();
				}


				function createIframe() {
					var temp = document.createElement('div');

					// Create iframe using a temp div since IE 6 won't be able to set the name using setAttribute or iframe.name
					temp.innerHTML = '<iframe id="' + up.id + '_iframe" name="' + up.id + '_iframe" src="' + url + ':&quot;&quot;" style="display:none"></iframe>';
					iframe = temp.firstChild;
					container.appendChild(iframe);

					// Add IFrame onload event
					plupload.addEvent(iframe, 'load', function(e) {
						var n = e.target, el, result;

						// Ignore load event if there is no file
						if (!currentFile) {
							return;
						}

						try {
							el = n.contentWindow.document || n.contentDocument || window.frames[n.id].document;
						} catch (ex) {
							// Probably a permission denied error
							up.trigger('Error', {
								code : plupload.SECURITY_ERROR,
								message : plupload.translate('Security error.'),
								file : currentFile
							});

							return;
						}

						// Get result
						result = el.documentElement.innerText || el.documentElement.textContent;
						
						// Assume no error
						if (result) {
							currentFile.status = plupload.DONE;
							currentFile.loaded = 1025;
							currentFile.percent = 100;

							up.trigger('UploadProgress', currentFile);
							up.trigger('FileUploaded', currentFile, {
								response : result
							});
						}
					}, up.id);
				} // end createIframe
				
				if (up.settings.container) {
					container = getById(up.settings.container);
					if (plupload.getStyle(container, 'position') === 'static') {
						container.style.position = 'relative';
					}
				}
				
				// Upload file
				up.bind("UploadFile", function(up, file) {
					var form, input;
					
					// File upload finished
					if (file.status == plupload.DONE || file.status == plupload.FAILED || up.state == plupload.STOPPED) {
						return;
					}

					// Get the form and input elements
					form = getById('form_' + file.id);
					input = getById('input_' + file.id);

					// Set input element name attribute which allows it to be submitted
					input.setAttribute('name', up.settings.file_data_name);

					// Store action
					form.setAttribute("action", up.settings.url);

					// Append multipart parameters
					plupload.each(plupload.extend({name : file.target_name || file.name}, up.settings.multipart_params), function(value, name) {
						var hidden = document.createElement('input');

						plupload.extend(hidden, {
							type : 'hidden',
							name : name,
							value : value
						});

						form.insertBefore(hidden, form.firstChild);
					});

					currentFile = file;

					// Hide the current form
					getById('form_' + currentFileId).style.top = -0xFFFFF + "px";
					
					form.submit();
				});
				
				
				
				up.bind('FileUploaded', function(up) {
					up.refresh(); // just to get the form back on top of browse_button
				});				

				up.bind('StateChanged', function(up) {
					if (up.state == plupload.STARTED) {
						createIframe();
					} else if (up.state == plupload.STOPPED) {
						window.setTimeout(function() {
							plupload.removeEvent(iframe, 'load', up.id);
							if (iframe.parentNode) { // #382
								iframe.parentNode.removeChild(iframe);
							}
						}, 0);
					}
					
					plupload.each(up.files, function(file, i) {
						if (file.status === plupload.DONE || file.status === plupload.FAILED) {
							var form = getById('form_' + file.id);

							if(form){
								form.parentNode.removeChild(form);
							}
						}
					});
				});

				// Refresh button, will reposition the input form
				up.bind("Refresh", function(up) {
					var browseButton, topElement, hoverClass, activeClass, browsePos, browseSize, inputContainer, inputFile, zIndex;

					browseButton = getById(up.settings.browse_button);
					if (browseButton) {
						browsePos = plupload.getPos(browseButton, getById(up.settings.container));
						browseSize = plupload.getSize(browseButton);
						inputContainer = getById('form_' + currentFileId);
						inputFile = getById('input_' + currentFileId);
	
						plupload.extend(inputContainer.style, {
							top : browsePos.y + 'px',
							left : browsePos.x + 'px',
							width : browseSize.w + 'px',
							height : browseSize.h + 'px'
						});
						
						// for IE and WebKit place input element underneath the browse button and route onclick event 
						// TODO: revise when browser support for this feature will change
						if (up.features.triggerDialog) {
							if (plupload.getStyle(browseButton, 'position') === 'static') {
								plupload.extend(browseButton.style, {
									position : 'relative'
								});
							}
							
							zIndex = parseInt(browseButton.style.zIndex, 10);

							if (isNaN(zIndex)) {
								zIndex = 0;
							}

							plupload.extend(browseButton.style, {
								zIndex : zIndex
							});							

							plupload.extend(inputContainer.style, {
								zIndex : zIndex - 1
							});
						}

						/* Since we have to place input[type=file] on top of the browse_button for some browsers (FF, Opera),
						browse_button loses interactivity, here we try to neutralize this issue highlighting browse_button
						with a special class
						TODO: needs to be revised as things will change */
						hoverClass = up.settings.browse_button_hover;
						activeClass = up.settings.browse_button_active;
						topElement = up.features.triggerDialog ? browseButton : inputContainer;
						
						if (hoverClass) {
							plupload.addEvent(topElement, 'mouseover', function() {
								plupload.addClass(browseButton, hoverClass);	
							}, up.id);
							plupload.addEvent(topElement, 'mouseout', function() {
								plupload.removeClass(browseButton, hoverClass);
							}, up.id);
						}
						
						if (activeClass) {
							plupload.addEvent(topElement, 'mousedown', function() {
								plupload.addClass(browseButton, activeClass);	
							}, up.id);
							plupload.addEvent(document.body, 'mouseup', function() {
								plupload.removeClass(browseButton, activeClass);	
							}, up.id);
						}
					}
				});

				// Remove files
				uploader.bind("FilesRemoved", function(up, files) {
					var i, n;

					for (i = 0; i < files.length; i++) {
						n = getById('form_' + files[i].id);
						if (n) {
							n.parentNode.removeChild(n);
						}
					}
				});
				
				uploader.bind("DisableBrowse", function(up, disabled) {
					var input = document.getElementById('input_' + currentFileId);
					if (input) {
						input.disabled = disabled;	
					}
				});
				
				
				// Completely destroy the runtime
				uploader.bind("Destroy", function(up) {
					var name, element, form,
						elements = {
							inputContainer: 'form_' + currentFileId,
							inputFile: 'input_' + currentFileId,	
							browseButton: up.settings.browse_button
						};

					// Unbind event handlers
					for (name in elements) {
						element = getById(elements[name]);
						if (element) {
							plupload.removeAllEvents(element, up.id);
						}
					}
					plupload.removeAllEvents(document.body, up.id);
					
					// Remove mark-up
					plupload.each(fileIds, function(id, i) {
						form = getById('form_' + id);
						if (form) {
							form.parentNode.removeChild(form);
						}
					});
					
				});

				// Create initial form
				createForm();
			});

			callback({success : true});
		}
	});
})(window, document, $.plupload);
/**
 * plupload.html5.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

// JSLint defined globals
/*global plupload:false, File:false, window:false, atob:false, FormData:false, FileReader:false, ArrayBuffer:false, Uint8Array:false, BlobBuilder:false, unescape:false */

(function(window, document, plupload, undef) {
	var html5files = {}, // queue of original File objects
		fakeSafariDragDrop;

	/**
	 * Detect subsampling in loaded image.
	 * In iOS, larger images than 2M pixels may be subsampled in rendering.
	 */
	function detectSubsampling(img) {
		var iw = img.naturalWidth, ih = img.naturalHeight;
		if (iw * ih > 1024 * 1024) { // subsampling may happen over megapixel image
			var canvas = document.createElement('canvas');
			canvas.width = canvas.height = 1;
			var ctx = canvas.getContext('2d');
			ctx.drawImage(img, -iw + 1, 0);
			// subsampled image becomes half smaller in rendering size.
			// check alpha channel value to confirm image is covering edge pixel or not.
			// if alpha value is 0 image is not covering, hence subsampled.
			return ctx.getImageData(0, 0, 1, 1).data[3] === 0;
		} else {
			return false;
		}
	}

	/**
	 * Detecting vertical squash in loaded image.
	 * Fixes a bug which squash image vertically while drawing into canvas for some images.
	 */
	function detectVerticalSquash(img, iw, ih) {
		var canvas = document.createElement('canvas');
		canvas.width = 1;
		canvas.height = ih;
		var ctx = canvas.getContext('2d');
		ctx.drawImage(img, 0, 0);
		var data = ctx.getImageData(0, 0, 1, ih).data;
		// search image edge pixel position in case it is squashed vertically.
		var sy = 0;
		var ey = ih;
		var py = ih;
		while (py > sy) {
			var alpha = data[(py - 1) * 4 + 3];
			if (alpha === 0) {
				ey = py;
			} else {
				sy = py;
			}

			py = (ey + sy) >> 1;
		}

		var ratio = (py / ih);
		return (ratio === 0) ? 1 : ratio;
	}

	/**
	* Rendering image element (with resizing) into the canvas element
	*/
	function renderImageToCanvas(img, canvas, options) {
		var iw = img.naturalWidth, ih = img.naturalHeight;
		var width = options.width, height = options.height;
		var ctx = canvas.getContext('2d');
		ctx.save();
		var subsampled = detectSubsampling(img);
		if (subsampled) {
			iw /= 2;
			ih /= 2;
		}

		var d = 1024; // size of tiling canvas
		var tmpCanvas = document.createElement('canvas');
		tmpCanvas.width = tmpCanvas.height = d;
		var tmpCtx = tmpCanvas.getContext('2d');
		var vertSquashRatio = detectVerticalSquash(img, iw, ih);
		var sy = 0;
		while (sy < ih) {
			var sh = sy + d > ih ? ih - sy : d;
			var sx = 0;
			while (sx < iw) {
				var sw = sx + d > iw ? iw - sx : d;
				tmpCtx.clearRect(0, 0, d, d);
				tmpCtx.drawImage(img, -sx, -sy);
				var dx = (sx * width / iw) << 0;
				var dw = Math.ceil(sw * width / iw);
				var dy = (sy * height / ih / vertSquashRatio) << 0;
				var dh = Math.ceil(sh * height / ih / vertSquashRatio);
				ctx.drawImage(tmpCanvas, 0, 0, sw, sh, dx, dy, dw, dh);
				sx += d;
			}

			sy += d;
		}

		ctx.restore();
		tmpCanvas = tmpCtx = null;
	}

	function readFileAsDataURL(file, callback) {
		var reader;

		// Use FileReader if it's available
		if ("FileReader" in window) {
			reader = new FileReader();
			reader.readAsDataURL(file);
			reader.onload = function() {
				callback(reader.result);
			};
		} else {
			return callback(file.getAsDataURL());
		}
	}

	function readFileAsBinary(file, callback) {
		var reader;

		// Use FileReader if it's available
		if ("FileReader" in window) {
			reader = new FileReader();
			reader.readAsBinaryString(file);
			reader.onload = function() {
				callback(reader.result);
			};
		} else {
			return callback(file.getAsBinary());
		}
	}

	function scaleImage(file, resize, mime, callback) {
		var canvas, context, img, scale,
			up = this;
			
		readFileAsDataURL(html5files[file.id], function(data) {
			// Setup canvas and context
			canvas = document.createElement("canvas");
			canvas.style.display = 'none';
			document.body.appendChild(canvas);

			// Load image
			img = new Image();
			img.onerror = img.onabort = function() {
				// Failed to load, the image may be invalid
				callback({success : false});
			};
			img.onload = function() {
				var width, height, percentage, jpegHeaders, exifParser;
				
				if (!resize['width']) {
					resize['width'] = img.width;
				}
				
				if (!resize['height']) {
					resize['height'] = img.height;	
				}
				
				scale = Math.min(resize.width / img.width, resize.height / img.height);

				if (scale < 1) {
					width = Math.round(img.width * scale);
					height = Math.round(img.height * scale);
				} else if (resize['quality'] && mime === 'image/jpeg') {
					// do not upsize, but drop the quality for jpegs
					width = img.width;
					height = img.height;
				} else {
					// Image does not need to be resized
					callback({success : false});
					return;
				}

				// Scale image and canvas
				canvas.width = width;
				canvas.height = height;
				renderImageToCanvas(img, canvas, { width: width, height: height });
				
				// Preserve JPEG headers
				if (mime === 'image/jpeg') {
					jpegHeaders = new JPEG_Headers(atob(data.substring(data.indexOf('base64,') + 7)));
					if (jpegHeaders['headers'] && jpegHeaders['headers'].length) {
						exifParser = new ExifParser();			
										
						if (exifParser.init(jpegHeaders.get('exif')[0])) {
							// Set new width and height
							exifParser.setExif('PixelXDimension', width);
							exifParser.setExif('PixelYDimension', height);
																						
							// Update EXIF header
							jpegHeaders.set('exif', exifParser.getBinary());
							
							// trigger Exif events only if someone listens to them
							if (up.hasEventListener('ExifData')) {
								up.trigger('ExifData', file, exifParser.EXIF());
							}
							
							if (up.hasEventListener('GpsData')) {
								up.trigger('GpsData', file, exifParser.GPS());
							}
						}
					}					
				} 

				if (resize['quality'] && mime === 'image/jpeg') {							
					// Try quality property first
					try {
						data = canvas.toDataURL(mime, resize['quality'] / 100);	// used to throw an exception in Firefox
					} catch (ex) {
						data = canvas.toDataURL(mime);	
					}
				} else {
					data = canvas.toDataURL(mime);
				}


				// Remove data prefix information and grab the base64 encoded data and decode it
				data = data.substring(data.indexOf('base64,') + 7);
				data = atob(data);

				// Restore JPEG headers if applicable
				if (jpegHeaders && jpegHeaders['headers'] && jpegHeaders['headers'].length) {
					data = jpegHeaders.restore(data);
					jpegHeaders.purge(); // free memory
				}

				// Remove canvas and execute callback with decoded image data
				canvas.parentNode.removeChild(canvas);
				callback({success : true, data : data});
			};

			img.src = data;
		});
	}

	/**
	 * HMTL5 implementation. This runtime supports these features: dragdrop, jpgresize, pngresize.
	 *
	 * @static
	 * @class plupload.runtimes.Html5
	 * @extends plupload.Runtime
	 */
	plupload.runtimes.Html5 = plupload.addRuntime("html5", {
		/**
		 * Returns a list of supported features for the runtime.
		 *
		 * @return {Object} Name/value object with supported features.
		 */
		getFeatures : function() {
			var xhr, hasXhrSupport, hasProgress, canSendBinary, dataAccessSupport, sliceSupport;

			hasXhrSupport = hasProgress = dataAccessSupport = sliceSupport = false;
			
			if (window.XMLHttpRequest) {
				xhr = new XMLHttpRequest();
				hasProgress = !!xhr.upload;
				hasXhrSupport = !!(xhr.sendAsBinary || xhr.upload);
			}

			// Check for support for various features
			if (hasXhrSupport) {
				canSendBinary = !!(xhr.sendAsBinary || (window.Uint8Array && window.ArrayBuffer));
				
				// Set dataAccessSupport only for Gecko since BlobBuilder and XHR doesn't handle binary data correctly				
				dataAccessSupport = !!(File && (File.prototype.getAsDataURL || window.FileReader) && canSendBinary);
				sliceSupport = !!(File && (File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice)); 
			}

			// sniff out Safari for Windows and fake drag/drop
			fakeSafariDragDrop = plupload.ua.safari && plupload.ua.windows;

			return {
				html5: hasXhrSupport, // This is a special one that we check inside the init call
				dragdrop: (function() {
					// this comes directly from Modernizr: http://www.modernizr.com/
					var div = document.createElement('div');
					return ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
				}()),
				jpgresize: dataAccessSupport,
				pngresize: dataAccessSupport,
				multipart: dataAccessSupport || !!window.FileReader || !!window.FormData,
				canSendBinary: canSendBinary,
				// gecko 2/5/6 can't send blob with FormData: https://bugzilla.mozilla.org/show_bug.cgi?id=649150 
				// Android browsers (default one and Dolphin) seem to have the same issue, see: #613
				cantSendBlobInFormData: !!(plupload.ua.gecko && window.FormData && window.FileReader && !FileReader.prototype.readAsArrayBuffer) || plupload.ua.android,
				progress: hasProgress,
				chunks: sliceSupport,
				// Safari on Windows has problems when selecting multiple files
				multi_selection: !(plupload.ua.safari && plupload.ua.windows),
				// WebKit and Gecko 2+ can trigger file dialog progrmmatically
				triggerDialog: (plupload.ua.gecko && window.FormData || plupload.ua.webkit) 
			};
		},

		/**
		 * Initializes the upload runtime.
		 *
		 * @method init
		 * @param {plupload.Uploader} uploader Uploader instance that needs to be initialized.
		 * @param {function} callback Callback to execute when the runtime initializes or fails to initialize. If it succeeds an object with a parameter name success will be set to true.
		 */
		init : function(uploader, callback) {
			var features, xhr;

			function addSelectedFiles(native_files) {
				var file, i, files = [], id, fileNames = {};

				// Add the selected files to the file queue
				for (i = 0; i < native_files.length; i++) {
					file = native_files[i];
										
					// Safari on Windows will add first file from dragged set multiple times
					// @see: https://bugs.webkit.org/show_bug.cgi?id=37957
					if (fileNames[file.name] && plupload.ua.safari && plupload.ua.windows) {
						continue;
					}
					fileNames[file.name] = true;

					// Store away gears blob internally
					id = plupload.guid();
					html5files[id] = file;

					// Expose id, name and size
					files.push(new plupload.File(id, file.fileName || file.name, file.fileSize || file.size)); // fileName / fileSize depricated
				}

				// Trigger FilesAdded event if we added any
				if (files.length) {
					uploader.trigger("FilesAdded", files);
				}
			}

			// No HTML5 upload support
			features = this.getFeatures();
			if (!features.html5) {
				callback({success : false});
				return;
			}

			uploader.bind("Init", function(up) {
				var inputContainer, browseButton, mimes = [], i, y, filters = up.settings.filters, ext, type, container = document.body, inputFile;

				// Create input container and insert it at an absolute position within the browse button
				inputContainer = document.createElement('div');
				inputContainer.id = up.id + '_html5_container';

				plupload.extend(inputContainer.style, {
					position : 'absolute',
					background : uploader.settings.shim_bgcolor || 'transparent',
					width : '100px',
					height : '100px',
					overflow : 'hidden',
					zIndex : 99999,
					opacity : uploader.settings.shim_bgcolor ? '' : 0 // Force transparent if bgcolor is undefined
				});
				inputContainer.className = 'plupload html5';

				if (uploader.settings.container) {
					container = document.getElementById(uploader.settings.container);
					if (plupload.getStyle(container, 'position') === 'static') {
						container.style.position = 'relative';
					}
				}

				container.appendChild(inputContainer);
				
				// Convert extensions to mime types list
				no_type_restriction:
				for (i = 0; i < filters.length; i++) {
					ext = filters[i].extensions.split(/,/);

					for (y = 0; y < ext.length; y++) {
						
						// If there's an asterisk in the list, then accept attribute is not required
						if (ext[y] === '*') {
							mimes = [];
							break no_type_restriction;
						}
						
						type = plupload.mimeTypes[ext[y]];

						if (type && plupload.inArray(type, mimes) === -1) {
							mimes.push(type);
						}
					}
				}


				// Insert the input inside the input container
				inputContainer.innerHTML = '<input id="' + uploader.id + '_html5" ' + ' style="font-size:999px"' +
											' type="file" accept="' + mimes.join(',') + '" ' +
											(uploader.settings.multi_selection && uploader.features.multi_selection ? 'multiple="multiple"' : '') + ' />';

				inputContainer.scrollTop = 100;
				inputFile = document.getElementById(uploader.id + '_html5');
				
				if (up.features.triggerDialog) {
					plupload.extend(inputFile.style, {
						position: 'absolute',
						width: '100%',
						height: '100%'
					});
				} else {
					// shows arrow cursor instead of the text one, bit more logical
					plupload.extend(inputFile.style, {
						cssFloat: 'right', 
						styleFloat: 'right'
					});
				}
				
				inputFile.onchange = function() {
					// Add the selected files from file input
					addSelectedFiles(this.files);
					
					// Clearing the value enables the user to select the same file again if they want to
					this.value = '';
				};
				
				/* Since we have to place input[type=file] on top of the browse_button for some browsers (FF, Opera),
				browse_button loses interactivity, here we try to neutralize this issue highlighting browse_button
				with a special classes
				TODO: needs to be revised as things will change */
				browseButton = document.getElementById(up.settings.browse_button);
				if (browseButton) {				
					var hoverClass = up.settings.browse_button_hover,
						activeClass = up.settings.browse_button_active,
						topElement = up.features.triggerDialog ? browseButton : inputContainer;
					
					if (hoverClass) {
						plupload.addEvent(topElement, 'mouseover', function() {
							plupload.addClass(browseButton, hoverClass);	
						}, up.id);
						plupload.addEvent(topElement, 'mouseout', function() {
							plupload.removeClass(browseButton, hoverClass);	
						}, up.id);
					}
					
					if (activeClass) {
						plupload.addEvent(topElement, 'mousedown', function() {
							plupload.addClass(browseButton, activeClass);	
						}, up.id);
						plupload.addEvent(document.body, 'mouseup', function() {
							plupload.removeClass(browseButton, activeClass);	
						}, up.id);
					}

					// Route click event to the input[type=file] element for supporting browsers
					if (up.features.triggerDialog) {
						plupload.addEvent(browseButton, 'click', function(e) {
							var input = document.getElementById(up.id + '_html5');
							if (input && !input.disabled) { // for some reason FF (up to 8.0.1 so far) lets to click disabled input[type=file]
								input.click();
							}
							e.preventDefault();
						}, up.id); 
					}
				}
			});

			// Add drop handler
			uploader.bind("PostInit", function() {
				var dropElm = document.getElementById(uploader.settings.drop_element);

				if (dropElm) {
					// Lets fake drag/drop on Safari by moving a input type file in front of the mouse pointer when we drag into the drop zone
					// TODO: Remove this logic once Safari has official drag/drop support
					if (fakeSafariDragDrop) {
						plupload.addEvent(dropElm, 'dragenter', function(e) {
							var dropInputElm, dropPos, dropSize;

							// Get or create drop zone
							dropInputElm = document.getElementById(uploader.id + "_drop");
							if (!dropInputElm) {
								dropInputElm = document.createElement("input");
								dropInputElm.setAttribute('type', "file");
								dropInputElm.setAttribute('id', uploader.id + "_drop");
								dropInputElm.setAttribute('multiple', 'multiple');

								plupload.addEvent(dropInputElm, 'change', function() {
									// Add the selected files from file input
									addSelectedFiles(this.files);
																		
									// Remove input element
									plupload.removeEvent(dropInputElm, 'change', uploader.id);
									dropInputElm.parentNode.removeChild(dropInputElm);									
								}, uploader.id);

								// avoid event propagation as Safari cancels the whole capability of dropping files if you are doing a preventDefault of this event on the document body
								plupload.addEvent(dropInputElm, 'dragover', function(e) {
									e.stopPropagation();
								}, uploader.id);
								
								dropElm.appendChild(dropInputElm);
							}

							dropPos = plupload.getPos(dropElm, document.getElementById(uploader.settings.container));
							dropSize = plupload.getSize(dropElm);
							
							if (plupload.getStyle(dropElm, 'position') === 'static') {
								plupload.extend(dropElm.style, {
									position : 'relative'
								});
							}
			  
							plupload.extend(dropInputElm.style, {
								position : 'absolute',
								display : 'block',
								top : 0,
								left : 0,
								width : dropSize.w + 'px',
								height : dropSize.h + 'px',
								opacity : 0
							});							
						}, uploader.id);

						return;
					}

					// Block browser default drag over
					plupload.addEvent(dropElm, 'dragover', function(e) {
						e.preventDefault();
					}, uploader.id);

					// Attach drop handler and grab files
					plupload.addEvent(dropElm, 'drop', function(e) {
						var dataTransfer = e.dataTransfer;

						// Add dropped files
						if (dataTransfer && dataTransfer.files) {
							addSelectedFiles(dataTransfer.files);
						}

						e.preventDefault();
					}, uploader.id);
				}
			});

			uploader.bind("Refresh", function(up) {
				var browseButton, browsePos, browseSize, inputContainer, zIndex;
					
				browseButton = document.getElementById(uploader.settings.browse_button);
				if (browseButton) {
					browsePos = plupload.getPos(browseButton, document.getElementById(up.settings.container));
					browseSize = plupload.getSize(browseButton);
					inputContainer = document.getElementById(uploader.id + '_html5_container');
	
					plupload.extend(inputContainer.style, {
						top : browsePos.y + 'px',
						left : browsePos.x + 'px',
						width : browseSize.w + 'px',
						height : browseSize.h + 'px'
					});
					
					// for WebKit place input element underneath the browse button and route onclick event 
					// TODO: revise when browser support for this feature will change
					if (uploader.features.triggerDialog) {
						if (plupload.getStyle(browseButton, 'position') === 'static') {
							plupload.extend(browseButton.style, {
								position : 'relative'
							});
						}
						
						zIndex = parseInt(plupload.getStyle(browseButton, 'zIndex'), 10);
						if (isNaN(zIndex)) {
							zIndex = 0;
						}						
							
						plupload.extend(browseButton.style, {
							zIndex : zIndex
						});						
											
						plupload.extend(inputContainer.style, {
							zIndex : zIndex - 1
						});
					}				
				}
			});
			
			uploader.bind("DisableBrowse", function(up, disabled) {
				var input = document.getElementById(up.id + '_html5');
				if (input) {
					input.disabled = disabled;	
				}
			});
			
			uploader.bind("CancelUpload", function() {
				if (xhr && xhr.abort) {
					xhr.abort();	
				}
			});

			uploader.bind("UploadFile", function(up, file) {
				var settings = up.settings, nativeFile, resize;
					
				function w3cBlobSlice(blob, start, end) {
					var blobSlice;
					
					if (File.prototype.slice) {
						try {
							blob.slice();	// depricated version will throw WRONG_ARGUMENTS_ERR exception
							return blob.slice(start, end);
						} catch (e) {
							// depricated slice method
							return blob.slice(start, end - start); 
						}
					// slice method got prefixed: https://bugzilla.mozilla.org/show_bug.cgi?id=649672	
					} else if (blobSlice = File.prototype.webkitSlice || File.prototype.mozSlice) {
						return blobSlice.call(blob, start, end);	
					} else {
						return null; // or throw some exception	
					}
				}	

				function sendBinaryBlob(blob) {
					var chunk = 0, loaded = 0;
						

					function uploadNextChunk() {
						var chunkBlob, br, chunks, args, chunkSize, curChunkSize, mimeType, url = up.settings.url;	

						function sendAsBinaryString(bin) {
							if (xhr.sendAsBinary) { // Gecko
								xhr.sendAsBinary(bin);
							} else if (up.features.canSendBinary) { // WebKit with typed arrays support
								var ui8a = new Uint8Array(bin.length);
								for (var i = 0; i < bin.length; i++) {
									ui8a[i] = (bin.charCodeAt(i) & 0xff);
								}
								xhr.send(ui8a.buffer);
							}
						}												
	
						function prepareAndSend(bin) {
							var multipartDeltaSize = 0,
								boundary = '----pluploadboundary' + plupload.guid(), formData, dashdash = '--', crlf = '\r\n', multipartBlob = '';
								
							xhr = new XMLHttpRequest;
															
							// Do we have upload progress support
							if (xhr.upload) {
								xhr.upload.onprogress = function(e) {
									file.loaded = Math.min(file.size, loaded + e.loaded - multipartDeltaSize); // Loaded can be larger than file size due to multipart encoding
									up.trigger('UploadProgress', file);
								};
							}
	
							xhr.onreadystatechange = function() {
								var httpStatus, chunkArgs;
																	
								if (xhr.readyState == 4 && up.state !== plupload.STOPPED) {
									// Getting the HTTP status might fail on some Gecko versions
									try {
										httpStatus = xhr.status;
									} catch (ex) {
										httpStatus = 0;
									}
	
									// Is error status
									if (httpStatus >= 400) {
										up.trigger('Error', {
											code : plupload.HTTP_ERROR,
											message : plupload.translate('HTTP Error.'),
											file : file,
											status : httpStatus
										});
									} else {
										// Handle chunk response
										if (chunks) {
											chunkArgs = {
												chunk : chunk,
												chunks : chunks,
												response : xhr.responseText,
												status : httpStatus
											};
	
											up.trigger('ChunkUploaded', file, chunkArgs);
											loaded += curChunkSize;
	
											// Stop upload
											if (chunkArgs.cancelled) {
												file.status = plupload.FAILED;
												return;
											}
	
											file.loaded = Math.min(file.size, (chunk + 1) * chunkSize);
										} else {
											file.loaded = file.size;
										}
	
										up.trigger('UploadProgress', file);
										
										bin = chunkBlob = formData = multipartBlob = null; // Free memory
										
										// Check if file is uploaded
										if (!chunks || ++chunk >= chunks) {
											file.status = plupload.DONE;
																						
											up.trigger('FileUploaded', file, {
												response : xhr.responseText,
												status : httpStatus
											});										
										} else {										
											// Still chunks left
											uploadNextChunk();
										}
									}																	
								}
							};
							
	
							// Build multipart request
							if (up.settings.multipart && features.multipart) {
								
								args.name = file.target_name || file.name;
								
								xhr.open("post", url, true);
								
								// Set custom headers
								plupload.each(up.settings.headers, function(value, name) {
									xhr.setRequestHeader(name, value);
								});
								
								
								// if has FormData support like Chrome 6+, Safari 5+, Firefox 4, use it
								if (typeof(bin) !== 'string' && !!window.FormData) {
									formData = new FormData();
	
									// Add multipart params
									plupload.each(plupload.extend(args, up.settings.multipart_params), function(value, name) {
										formData.append(name, value);
									});
	
									// Add file and send it
									formData.append(up.settings.file_data_name, bin);								
									xhr.send(formData);
	
									return;
								}  // if no FormData we can still try to send it directly as last resort (see below)
								
								
								if (typeof(bin) === 'string') {
									// Trying to send the whole thing as binary...
		
									// multipart request
									xhr.setRequestHeader('Content-Type', 'multipart/form-data; boundary=' + boundary);
		
									// append multipart parameters
									plupload.each(plupload.extend(args, up.settings.multipart_params), function(value, name) {
										multipartBlob += dashdash + boundary + crlf +
											'Content-Disposition: form-data; name="' + name + '"' + crlf + crlf;
		
										multipartBlob += unescape(encodeURIComponent(value)) + crlf;
									});
		
									mimeType = plupload.mimeTypes[file.name.replace(/^.+\.([^.]+)/, '$1').toLowerCase()] || 'application/octet-stream';
		
									// Build RFC2388 blob
									multipartBlob += dashdash + boundary + crlf +
										'Content-Disposition: form-data; name="' + up.settings.file_data_name + '"; filename="' + unescape(encodeURIComponent(file.name)) + '"' + crlf +
										'Content-Type: ' + mimeType + crlf + crlf +
										bin + crlf +
										dashdash + boundary + dashdash + crlf;
		
									multipartDeltaSize = multipartBlob.length - bin.length;
									bin = multipartBlob;
							
									sendAsBinaryString(bin);
									return; // will return from here only if shouldn't send binary
								} 							
							}
							
							// if no multipart, or last resort, send as binary stream
							url = plupload.buildUrl(up.settings.url, plupload.extend(args, up.settings.multipart_params));
							
							xhr.open("post", url, true);
							
							xhr.setRequestHeader('Content-Type', 'application/octet-stream'); // Binary stream header
								
							// Set custom headers
							plupload.each(up.settings.headers, function(value, name) {
								xhr.setRequestHeader(name, value);
							});
							
							if (typeof(bin) === 'string') {	
								sendAsBinaryString(bin);
							} else {				
								xhr.send(bin); 
							}
						} // prepareAndSend


						// File upload finished
						if (file.status == plupload.DONE || file.status == plupload.FAILED || up.state == plupload.STOPPED) {
							return;
						}

						// Standard arguments
						args = {name : file.target_name || file.name};

						// Only add chunking args if needed
						if (settings.chunk_size && file.size > settings.chunk_size && (features.chunks || typeof(blob) == 'string')) { // blob will be of type string if it was loaded in memory 
							chunkSize = settings.chunk_size;
							chunks = Math.ceil(file.size / chunkSize);
							curChunkSize = Math.min(chunkSize, file.size - (chunk * chunkSize));

							// Blob is string so we need to fake chunking, this is not
							// ideal since the whole file is loaded into memory
							if (typeof(blob) == 'string') {
								chunkBlob = blob.substring(chunk * chunkSize, chunk * chunkSize + curChunkSize);
							} else {
								// Slice the chunk
								chunkBlob = w3cBlobSlice(blob, chunk * chunkSize, chunk * chunkSize + curChunkSize);
							}

							// Setup query string arguments
							args.chunk = chunk;
							args.chunks = chunks;
						} else {
							curChunkSize = file.size;
							chunkBlob = blob;
						}
						
						// workaround for Android and Gecko 2,5,6 FormData+Blob bug: https://bugzilla.mozilla.org/show_bug.cgi?id=649150
						if (up.settings.multipart && features.multipart && typeof(chunkBlob) !== 'string' && window.FileReader && features.cantSendBlobInFormData && features.chunks && up.settings.chunk_size) { // Gecko 2,5,6
							(function() {
								var fr = new FileReader(); // we need to recreate FileReader object in Android, otherwise it hangs
								fr.onload = function() {
									prepareAndSend(fr.result);
									fr = null; // maybe give a hand to GC (Gecko had problems with this)
								}
								fr.readAsBinaryString(chunkBlob);
							}());
						} else {
							prepareAndSend(chunkBlob);
						}	
					}

					// Start uploading chunks
					uploadNextChunk();
				}

				nativeFile = html5files[file.id];
								
				// Resize image if it's a supported format and resize is enabled
				if (features.jpgresize && up.settings.resize && /\.(png|jpg|jpeg)$/i.test(file.name)) {
					scaleImage.call(up, file, up.settings.resize, /\.png$/i.test(file.name) ? 'image/png' : 'image/jpeg', function(res) {
						// If it was scaled send the scaled image if it failed then
						// send the raw image and let the server do the scaling
						if (res.success) {
							file.size = res.data.length;
							sendBinaryBlob(res.data);
						} else if (features.chunks) {
							sendBinaryBlob(nativeFile); 
						} else {
							readFileAsBinary(nativeFile, sendBinaryBlob); // for browsers not supporting File.slice (e.g. FF3.6)
						}
					});
				// if there's no way to slice file without preloading it in memory, preload it
				} else if (!features.chunks && features.jpgresize) { 
					readFileAsBinary(nativeFile, sendBinaryBlob); 
				} else {
					sendBinaryBlob(nativeFile); 
				}
			});
			
			
			uploader.bind('Destroy', function(up) {
				var name, element, container = document.body,
					elements = {
						inputContainer: up.id + '_html5_container',
						inputFile: up.id + '_html5',
						browseButton: up.settings.browse_button,
						dropElm: up.settings.drop_element
					};

				// Unbind event handlers
				for (name in elements) {
					element = document.getElementById(elements[name]);
					if (element) {
						plupload.removeAllEvents(element, up.id);
					}
				}
				plupload.removeAllEvents(document.body, up.id);
				
				if (up.settings.container) {
					container = document.getElementById(up.settings.container);
				}
				
				// Remove mark-up
				container.removeChild(document.getElementById(elements.inputContainer));
			});

			callback({success : true});
		}
	});
	
	function BinaryReader() {
		var II = false, bin;

		// Private functions
		function read(idx, size) {
			var mv = II ? 0 : -8 * (size - 1), sum = 0, i;

			for (i = 0; i < size; i++) {
				sum |= (bin.charCodeAt(idx + i) << Math.abs(mv + i*8));
			}

			return sum;
		}

		function putstr(segment, idx, length) {
			var length = arguments.length === 3 ? length : bin.length - idx - 1;
			
			bin = bin.substr(0, idx) + segment + bin.substr(length + idx);
		}

		function write(idx, num, size) {
			var str = '', mv = II ? 0 : -8 * (size - 1), i;

			for (i = 0; i < size; i++) {
				str += String.fromCharCode((num >> Math.abs(mv + i*8)) & 255);
			}

			putstr(str, idx, size);
		}

		// Public functions
		return {
			II: function(order) {
				if (order === undef) {
					return II;
				} else {
					II = order;
				}
			},

			init: function(binData) {
				II = false;
				bin = binData;
			},

			SEGMENT: function(idx, length, segment) {				
				switch (arguments.length) {
					case 1: 
						return bin.substr(idx, bin.length - idx - 1);
					case 2: 
						return bin.substr(idx, length);
					case 3: 
						putstr(segment, idx, length);
						break;
					default: return bin;	
				}
			},

			BYTE: function(idx) {
				return read(idx, 1);
			},

			SHORT: function(idx) {
				return read(idx, 2);
			},

			LONG: function(idx, num) {
				if (num === undef) {
					return read(idx, 4);
				} else {
					write(idx, num, 4);
				}
			},

			SLONG: function(idx) { // 2's complement notation
				var num = read(idx, 4);

				return (num > 2147483647 ? num - 4294967296 : num);
			},

			STRING: function(idx, size) {
				var str = '';

				for (size += idx; idx < size; idx++) {
					str += String.fromCharCode(read(idx, 1));
				}

				return str;
			}
		};
	}
	
	function JPEG_Headers(data) {
		
		var markers = {
				0xFFE1: {
					app: 'EXIF',
					name: 'APP1',
					signature: "Exif\0" 
				},
				0xFFE2: {
					app: 'ICC',
					name: 'APP2',
					signature: "ICC_PROFILE\0" 
				},
				0xFFED: {
					app: 'IPTC',
					name: 'APP13',
					signature: "Photoshop 3.0\0" 
				}
			},
			headers = [], read, idx, marker = undef, length = 0, limit;
			
		
		read = new BinaryReader();
		read.init(data);
				
		// Check if data is jpeg
		if (read.SHORT(0) !== 0xFFD8) {
			return;
		}
		
		idx = 2;
		limit = Math.min(1048576, data.length);	
			
		while (idx <= limit) {
			marker = read.SHORT(idx);
			
			// omit RST (restart) markers
			if (marker >= 0xFFD0 && marker <= 0xFFD7) {
				idx += 2;
				continue;
			}
			
			// no headers allowed after SOS marker
			if (marker === 0xFFDA || marker === 0xFFD9) {
				break;	
			}	
			
			length = read.SHORT(idx + 2) + 2;	
			
			if (markers[marker] && 
				read.STRING(idx + 4, markers[marker].signature.length) === markers[marker].signature) {
				headers.push({ 
					hex: marker,
					app: markers[marker].app.toUpperCase(),
					name: markers[marker].name.toUpperCase(),
					start: idx,
					length: length,
					segment: read.SEGMENT(idx, length)
				});
			}
			idx += length;			
		}
					
		read.init(null); // free memory
						
		return {
			
			headers: headers,
			
			restore: function(data) {
				read.init(data);
				
				// Check if data is jpeg
				var jpegHeaders = new JPEG_Headers(data);
				
				if (!jpegHeaders['headers']) {
					return false;
				}	
				
				// Delete any existing headers that need to be replaced
				for (var i = jpegHeaders['headers'].length; i > 0; i--) {
					var hdr = jpegHeaders['headers'][i - 1];
					read.SEGMENT(hdr.start, hdr.length, '')
				}
				jpegHeaders.purge();
				
				idx = read.SHORT(2) == 0xFFE0 ? 4 + read.SHORT(4) : 2;
								
				for (var i = 0, max = headers.length; i < max; i++) {
					read.SEGMENT(idx, 0, headers[i].segment);						
					idx += headers[i].length;
				}
				
				return read.SEGMENT();
			},
			
			get: function(app) {
				var array = [];
								
				for (var i = 0, max = headers.length; i < max; i++) {
					if (headers[i].app === app.toUpperCase()) {
						array.push(headers[i].segment);
					}
				}
				return array;
			},
			
			set: function(app, segment) {
				var array = [];
				
				if (typeof(segment) === 'string') {
					array.push(segment);	
				} else {
					array = segment;	
				}
				
				for (var i = ii = 0, max = headers.length; i < max; i++) {
					if (headers[i].app === app.toUpperCase()) {
						headers[i].segment = array[ii];
						headers[i].length = array[ii].length;
						ii++;
					}
					if (ii >= array.length) break;
				}
			},
			
			purge: function() {
				headers = [];
				read.init(null);
			}
		};		
	}
	
	
	function ExifParser() {
		// Private ExifParser fields
		var data, tags, offsets = {}, tagDescs;

		data = new BinaryReader();

		tags = {
			tiff : {
				/*
				The image orientation viewed in terms of rows and columns.
	
				1 - The 0th row is at the visual top of the image, and the 0th column is the visual left-hand side.
				2 - The 0th row is at the visual top of the image, and the 0th column is the visual left-hand side.
				3 - The 0th row is at the visual top of the image, and the 0th column is the visual right-hand side.
				4 - The 0th row is at the visual bottom of the image, and the 0th column is the visual right-hand side.
				5 - The 0th row is at the visual bottom of the image, and the 0th column is the visual left-hand side.
				6 - The 0th row is the visual left-hand side of the image, and the 0th column is the visual top.
				7 - The 0th row is the visual right-hand side of the image, and the 0th column is the visual top.
				8 - The 0th row is the visual right-hand side of the image, and the 0th column is the visual bottom.
				9 - The 0th row is the visual left-hand side of the image, and the 0th column is the visual bottom.
				*/
				0x0112: 'Orientation',
				0x8769: 'ExifIFDPointer',
				0x8825:	'GPSInfoIFDPointer'
			},
			exif : {
				0x9000: 'ExifVersion',
				0xA001: 'ColorSpace',
				0xA002: 'PixelXDimension',
				0xA003: 'PixelYDimension',
				0x9003: 'DateTimeOriginal',
				0x829A: 'ExposureTime',
				0x829D: 'FNumber',
				0x8827: 'ISOSpeedRatings',
				0x9201: 'ShutterSpeedValue',
				0x9202: 'ApertureValue'	,
				0x9207: 'MeteringMode',
				0x9208: 'LightSource',
				0x9209: 'Flash',
				0xA402: 'ExposureMode',
				0xA403: 'WhiteBalance',
				0xA406: 'SceneCaptureType',
				0xA404: 'DigitalZoomRatio',
				0xA408: 'Contrast',
				0xA409: 'Saturation',
				0xA40A: 'Sharpness'
			},
			gps : {
				0x0000: 'GPSVersionID',
				0x0001: 'GPSLatitudeRef',
				0x0002: 'GPSLatitude',
				0x0003: 'GPSLongitudeRef',
				0x0004: 'GPSLongitude'
			}
		};

		tagDescs = {
			'ColorSpace': {
				1: 'sRGB',
				0: 'Uncalibrated'
			},

			'MeteringMode': {
				0: 'Unknown',
				1: 'Average',
				2: 'CenterWeightedAverage',
				3: 'Spot',
				4: 'MultiSpot',
				5: 'Pattern',
				6: 'Partial',
				255: 'Other'
			},

			'LightSource': {
				1: 'Daylight',
				2: 'Fliorescent',
				3: 'Tungsten',
				4: 'Flash',
				9: 'Fine weather',
				10: 'Cloudy weather',
				11: 'Shade',
				12: 'Daylight fluorescent (D 5700 - 7100K)',
				13: 'Day white fluorescent (N 4600 -5400K)',
				14: 'Cool white fluorescent (W 3900 - 4500K)',
				15: 'White fluorescent (WW 3200 - 3700K)',
				17: 'Standard light A',
				18: 'Standard light B',
				19: 'Standard light C',
				20: 'D55',
				21: 'D65',
				22: 'D75',
				23: 'D50',
				24: 'ISO studio tungsten',
				255: 'Other'
			},

			'Flash': {
				0x0000: 'Flash did not fire.',
				0x0001: 'Flash fired.',
				0x0005: 'Strobe return light not detected.',
				0x0007: 'Strobe return light detected.',
				0x0009: 'Flash fired, compulsory flash mode',
				0x000D: 'Flash fired, compulsory flash mode, return light not detected',
				0x000F: 'Flash fired, compulsory flash mode, return light detected',
				0x0010: 'Flash did not fire, compulsory flash mode',
				0x0018: 'Flash did not fire, auto mode',
				0x0019: 'Flash fired, auto mode',
				0x001D: 'Flash fired, auto mode, return light not detected',
				0x001F: 'Flash fired, auto mode, return light detected',
				0x0020: 'No flash function',
				0x0041: 'Flash fired, red-eye reduction mode',
				0x0045: 'Flash fired, red-eye reduction mode, return light not detected',
				0x0047: 'Flash fired, red-eye reduction mode, return light detected',
				0x0049: 'Flash fired, compulsory flash mode, red-eye reduction mode',
				0x004D: 'Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected',
				0x004F: 'Flash fired, compulsory flash mode, red-eye reduction mode, return light detected',
				0x0059: 'Flash fired, auto mode, red-eye reduction mode',
				0x005D: 'Flash fired, auto mode, return light not detected, red-eye reduction mode',
				0x005F: 'Flash fired, auto mode, return light detected, red-eye reduction mode'
			},

			'ExposureMode': {
				0: 'Auto exposure',
				1: 'Manual exposure',
				2: 'Auto bracket'
			},

			'WhiteBalance': {
				0: 'Auto white balance',
				1: 'Manual white balance'
			},

			'SceneCaptureType': {
				0: 'Standard',
				1: 'Landscape',
				2: 'Portrait',
				3: 'Night scene'
			},

			'Contrast': {
				0: 'Normal',
				1: 'Soft',
				2: 'Hard'
			},

			'Saturation': {
				0: 'Normal',
				1: 'Low saturation',
				2: 'High saturation'
			},

			'Sharpness': {
				0: 'Normal',
				1: 'Soft',
				2: 'Hard'
			},

			// GPS related
			'GPSLatitudeRef': {
				N: 'North latitude',
				S: 'South latitude'
			},

			'GPSLongitudeRef': {
				E: 'East longitude',
				W: 'West longitude'
			}
		};

		function extractTags(IFD_offset, tags2extract) {
			var length = data.SHORT(IFD_offset), i, ii,
				tag, type, count, tagOffset, offset, value, values = [], hash = {};

			for (i = 0; i < length; i++) {
				// Set binary reader pointer to beginning of the next tag
				offset = tagOffset = IFD_offset + 12 * i + 2;

				tag = tags2extract[data.SHORT(offset)];

				if (tag === undef) {
					continue; // Not the tag we requested
				}

				type = data.SHORT(offset+=2);
				count = data.LONG(offset+=2);

				offset += 4;
				values = [];

				switch (type) {
					case 1: // BYTE
					case 7: // UNDEFINED
						if (count > 4) {
							offset = data.LONG(offset) + offsets.tiffHeader;
						}

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.BYTE(offset + ii);
						}

						break;

					case 2: // STRING
						if (count > 4) {
							offset = data.LONG(offset) + offsets.tiffHeader;
						}

						hash[tag] = data.STRING(offset, count - 1);

						continue;

					case 3: // SHORT
						if (count > 2) {
							offset = data.LONG(offset) + offsets.tiffHeader;
						}

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.SHORT(offset + ii*2);
						}

						break;

					case 4: // LONG
						if (count > 1) {
							offset = data.LONG(offset) + offsets.tiffHeader;
						}

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.LONG(offset + ii*4);
						}

						break;

					case 5: // RATIONAL
						offset = data.LONG(offset) + offsets.tiffHeader;

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.LONG(offset + ii*4) / data.LONG(offset + ii*4 + 4);
						}

						break;

					case 9: // SLONG
						offset = data.LONG(offset) + offsets.tiffHeader;

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.SLONG(offset + ii*4);
						}

						break;

					case 10: // SRATIONAL
						offset = data.LONG(offset) + offsets.tiffHeader;

						for (ii = 0; ii < count; ii++) {
							values[ii] = data.SLONG(offset + ii*4) / data.SLONG(offset + ii*4 + 4);
						}

						break;

					default:
						continue;
				}

				value = (count == 1 ? values[0] : values);

				if (tagDescs.hasOwnProperty(tag) && typeof value != 'object') {
					hash[tag] = tagDescs[tag][value];
				} else {
					hash[tag] = value;
				}
			}

			return hash;
		}

		function getIFDOffsets() {
			var Tiff = undef, idx = offsets.tiffHeader;

			// Set read order of multi-byte data
			data.II(data.SHORT(idx) == 0x4949);

			// Check if always present bytes are indeed present
			if (data.SHORT(idx+=2) !== 0x002A) {
				return false;
			}
		
			offsets['IFD0'] = offsets.tiffHeader + data.LONG(idx += 2);
			Tiff = extractTags(offsets['IFD0'], tags.tiff);

			offsets['exifIFD'] = ('ExifIFDPointer' in Tiff ? offsets.tiffHeader + Tiff.ExifIFDPointer : undef);
			offsets['gpsIFD'] = ('GPSInfoIFDPointer' in Tiff ? offsets.tiffHeader + Tiff.GPSInfoIFDPointer : undef);

			return true;
		}
		
		// At the moment only setting of simple (LONG) values, that do not require offset recalculation, is supported
		function setTag(ifd, tag, value) {
			var offset, length, tagOffset, valueOffset = 0;
			
			// If tag name passed translate into hex key
			if (typeof(tag) === 'string') {
				var tmpTags = tags[ifd.toLowerCase()];
				for (hex in tmpTags) {
					if (tmpTags[hex] === tag) {
						tag = hex;
						break;	
					}
				}
			}
			offset = offsets[ifd.toLowerCase() + 'IFD'];
			length = data.SHORT(offset);
						
			for (i = 0; i < length; i++) {
				tagOffset = offset + 12 * i + 2;

				if (data.SHORT(tagOffset) == tag) {
					valueOffset = tagOffset + 8;
					break;
				}
			}
			
			if (!valueOffset) return false;

			
			data.LONG(valueOffset, value);
			return true;
		}
		

		// Public functions
		return {
			init: function(segment) {
				// Reset internal data
				offsets = {
					tiffHeader: 10
				};
				
				if (segment === undef || !segment.length) {
					return false;
				}

				data.init(segment);

				// Check if that's APP1 and that it has EXIF
				if (data.SHORT(0) === 0xFFE1 && data.STRING(4, 5).toUpperCase() === "EXIF\0") {
					return getIFDOffsets();
				}
				return false;
			},
			
			EXIF: function() {
				var Exif;
				
				// Populate EXIF hash
				Exif = extractTags(offsets.exifIFD, tags.exif);

				// Fix formatting of some tags
				if (Exif.ExifVersion && plupload.typeOf(Exif.ExifVersion) === 'array') {
					for (var i = 0, exifVersion = ''; i < Exif.ExifVersion.length; i++) {
						exifVersion += String.fromCharCode(Exif.ExifVersion[i]);	
					}
					Exif.ExifVersion = exifVersion;
				}

				return Exif;
			},

			GPS: function() {
				var GPS;
				
				GPS = extractTags(offsets.gpsIFD, tags.gps);
				
				// iOS devices (and probably some others) do not put in GPSVersionID tag (why?..)
				if (GPS.GPSVersionID) { 
					GPS.GPSVersionID = GPS.GPSVersionID.join('.');
				}

				return GPS;
			},
			
			setExif: function(tag, value) {
				// Right now only setting of width/height is possible
				if (tag !== 'PixelXDimension' && tag !== 'PixelYDimension') return false;
				
				return setTag('exif', tag, value);
			},


			getBinary: function() {
				return data.SEGMENT();
			}
		};
	};
})(window, document, $.plupload);
$.Controller("plupload",
{
	pluginName: "plupload",
	hostname: "plupload",

	defaultOptions: {

		"{uploader}" : "[data-plupload-uploader]",
		"{uploadButton}" : "[data-plupload-upload-button]",
		"{uploadDropsite}" : "[data-plupload-dropsite]",

		settings: {
			runtimes: "html5, html4",
			url: $.indexUrl,
			max_file_count: 20,
			unique_names: true
		}
	}
},
function(self, opts, base) { return {

	init: function() {

		var settings = self.options.settings;

		// Create upload container identifier
		var uploadContainerId = $.uid("uploadContainer-");

		self.element
			.attr('id', uploadContainerId);

		settings.container = uploadContainerId;

		// Create upload button identifier
		var uploadButtonId = self.uploadButtonId = $.uid("uploadButton-");

		// Apply the id to the first found upload button
		self.uploadButtonMain =
			self.uploadButton(":first")
				.attr('id', uploadButtonId);

		settings.browse_button = uploadButtonId;

		// Create upload drop site identifier
		var uploadDropsiteId = $.uid("uploadDropsite-");

		if (self.uploadDropsite().length > 0) {

			self.uploadDropsite()
						.attr('id', uploadDropsiteId);

			settings.drop_element = uploadDropsiteId;
		}

		// Decide where the uploader events are binded to
		self.uploader = $(self.uploader()[0] || self.element);

		// Create new plupload instance
		self.plupload = new $.plupload.Uploader(settings);

		// @rule: Init() plupload before you bind except for postInit
		self.plupload.bind('PostInit', function() {
			self.eventHandler("PostInit", $.makeArray(arguments));
		});

		self.plupload.init();

		var events = [
			"BeforeUpload",
			"ChunkUploaded",
			"Destroy",
			"Error",
			"FilesAdded",
			"FilesRemoved",
			"FileUploaded",
			"Init",
			"QueueChanged",
			"Refresh",
			"StateChanged",
			"UploadComplete",
			"UploadFile",
			"UploadProgress"
		];

		$.each(events, function(i, eventName) {

			self.plupload.bind(eventName, function(){
				self.eventHandler(eventName, $.makeArray(arguments));
			});
		});

		// Indicate uploader supports drag & drop
		if (!$.IE && self.plupload.runtime=="html5") {

			base.addClass("can-drop-file");
		}

		// Indicate uploader is ready
		base.addClass("can-upload");
	},

	"{uploadButton} click": function(uploadButton) {
		
		if (uploadButton[0]==self.uploadButtonMain[0]) {
			return;
		}

		if (self.plupload.features.triggerDialog) {
			self.uploadButtonMain.click();
		}
	},

	"{uploadButton} mouseover": function(uploadButton) {

		// If we can trigger browser dialog programatically,
		// don't do anything.
		if (self.plupload.features.triggerDialog) return;

		// Remove id on all upload buttons
		self.uploadButton().removeAttr('id');

		// Add to the current one
		uploadButton.attr('id', self.uploadButtonId);

		// Reposition button
		self.plupload.refresh();
	},

	eventHandler: function(eventName, args) {

		var eventHandler = self["plupload::"+eventName],

			elementEventHandler = (function(){
				var eventHandlers = (self.uploader.data("events") || {})[eventName];
				return (eventHandlers) ? eventHandlers[0].handler : undefined;
			})(),

			elementEventHandlerArgs;

		if ($.isFunction(eventHandler)) {

			elementEventHandlerArgs = eventHandler.apply(self, args);
		}

		if (elementEventHandlerArgs!==false) {

			self.uploader.trigger(eventName, elementEventHandlerArgs || args);
		}
	},

	"plupload::FileUploaded": function(up, file, data, handler) {

		var response;

		try {

			response = eval('('+data.response+')');

		} catch(e) {

			response = {
				type: "error",
				message: "Unable to parse server response.",
				data: data
			};
		}

		// If response type is an error, trigger FileError event
		if (response.type=="error") {

			self.uploader.trigger("FileError", [up, file, response]);

			// This ensure the FileUploaded event
			// doesn't get triggered anymore.
			return false;
		}

		// Trigger FileUploaded event with the following params
		return [up, file, response];
	},

	"plupload::Error": function(up, error) {

		try { console.log('plupload Error: ', up, error); } catch(e) {};
	}

}});

};

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("plupload", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var exports = function() {

var jQuery = $;

/*!
 * jQuery Raty FA - A Star Rating Plugin with Font Awesome
 *
 * Licensed under The MIT License
 *
 * @author  : Jacob Overgaard
 * @doc     : http://jacob87.github.io/raty-fa/
 * @version : 0.1.1
 *
 */

;(function($) {

	var methods = {
		init: function(settings) {
			return this.each(function() {
				methods.destroy.call(this);

				this.opt = $.extend(true, {}, $.fn.raty.defaults, settings);

				var that  = $(this),
						inits = ['number', 'readOnly', 'score', 'scoreName'];

				methods._callback.call(this, inits);

				if (this.opt.precision) {
					methods._adjustPrecision.call(this);
				}

				this.opt.number = methods._between(this.opt.number, 0, this.opt.numberMax);

				this.stars = methods._createStars.call(this);
				this.score = methods._createScore.call(this);

				methods._apply.call(this, this.opt.score);

				if (this.opt.cancel) {
					this.cancel = methods._createCancel.call(this);
				}

				if (this.opt.width) {
					that.css('width', this.opt.width);
				}

				if (this.opt.readOnly) {
					methods._lock.call(this);
				} else {
					that.css('cursor', 'pointer');
					methods._binds.call(this);
				}

				methods._target.call(this, this.opt.score);

				that.data({ 'settings': this.opt, 'raty': true });
			});
		}, _adjustPrecision: function() {
			this.opt.targetType = 'score';
			this.opt.half       = true;
		}, _apply: function(score) {
			if (typeof score !== 'undefined' && score >= 0) {
				score = methods._between(score, 0, this.opt.number);
				this.score.val(score);
			}

			methods._fill.call(this, score);

			if (score) {
				methods._roundStars.call(this, score);
			}
		}, _between: function(value, min, max) {
			return Math.min(Math.max(parseFloat(value), min), max);
		}, _binds: function() {
			if (this.cancel) {
				methods._bindCancel.call(this);
			}

			methods._bindClick.call(this);
			methods._bindOut.call(this);
			methods._bindOver.call(this);
		}, _bindCancel: function() {
			methods._bindClickCancel.call(this);
			methods._bindOutCancel.call(this);
			methods._bindOverCancel.call(this);
		}, _bindClick: function() {
			var self = this,
					that = $(self);

			self.stars.on('click.raty', function(evt) {
				self.score.val((self.opt.half || self.opt.precision) ? that.data('score') : $(this).data('score'));

				if (self.opt.click) {
					self.opt.click.call(self, parseFloat(self.score.val()), evt);
				}
			});
		}, _bindClickCancel: function() {
			var self = this;

			self.cancel.on('click.raty', function(evt) {
				self.score.removeAttr('value');

				if (self.opt.click) {
					self.opt.click.call(self, null, evt);
				}
			});
		}, _bindOut: function() {
			var self = this;

			$(this).on('mouseleave.raty', function(evt) {
				var score = parseFloat(self.score.val()) || undefined;

				methods._apply.call(self, score);
				methods._target.call(self, score, evt);

				if (self.opt.mouseout) {
					self.opt.mouseout.call(self, score, evt);
				}
			});
		}, _bindOutCancel: function() {
			var self = this;

			self.cancel.on('mouseleave.raty', function(evt) {
				$(this).attr('class', self.opt.cancelOff);

				if (self.opt.mouseout) {
					self.opt.mouseout.call(self, self.score.val() || null, evt);
				}
			});
		}, _bindOverCancel: function() {
			var self = this;

			self.cancel.on('mouseover.raty', function(evt) {
				$(this).attr('class', self.opt.cancelOn);

				self.stars.attr('class', self.opt.starOff);

				methods._target.call(self, null, evt);

				if (self.opt.mouseover) {
					self.opt.mouseover.call(self, null);
				}
			});
		}, _bindOver: function() {
			var self   = this,
					that   = $(self),
					action = self.opt.half ? 'mousemove.raty' : 'mouseover.raty';

			self.stars.on(action, function(evt) {
				var score = parseInt($(this).data('score'), 10);

				if (self.opt.half) {
					var position = parseFloat((evt.pageX - $(this).offset().left) / (self.opt.size ? self.opt.size : parseInt(that.css('font-size')))),
							plus     = (position > .5) ? 1 : .5;

					score = score - 1 + plus;

					methods._fill.call(self, score);

					if (self.opt.precision) {
						score = score - plus + position;
					}

					methods._roundStars.call(self, score);

					that.data('score', score);
				} else {
					methods._fill.call(self, score);
				}

				methods._target.call(self, score, evt);

				if (self.opt.mouseover) {
					self.opt.mouseover.call(self, score, evt);
				}
			});
		}, _callback: function(options) {
			for (var i in options) {
				if (typeof this.opt[options[i]] === 'function') {
					this.opt[options[i]] = this.opt[options[i]].call(this);
				}
			}
		}, _createCancel: function() {
			var that   = $(this),
					icon   = this.opt.cancelOff,
					cancel = $('<i />', { 'class': icon, title: this.opt.cancelHint });

			if (this.opt.cancelPlace == 'left') {
				that.prepend('&#160;').prepend(cancel);
			} else {
				that.append('&#160;').append(cancel);
			}

			return cancel;
		}, _createScore: function() {
			return $('<input />', { type: 'hidden', name: this.opt.scoreName }).appendTo(this);
		}, _createStars: function() {
			var that = $(this);

			for (var i = 1; i <= this.opt.number; i++) {
				var title = methods._getHint.call(this, i),
						icon  = (this.opt.score && this.opt.score >= i) ? 'starOn' : 'starOff';

				icon = this.opt[icon];

				$('<i />', { 'class' : icon, title: title, 'data-score': i }).appendTo(this);

				if (this.opt.space) {
					that.append((i < this.opt.number) ? '&#160;' : '');
				}
			}

			return that.children('i');
		}, _error: function(message) {
			$(this).html(message);

			$.error(message);
		}, _fill: function(score) {
			var self  = this,
					hash  = 0;

			for (var i = 1; i <= self.stars.length; i++) {
				var star   = self.stars.eq(i - 1),
						select = self.opt.single ? (i == score) : (i <= score);

				if (self.opt.iconRange && self.opt.iconRange.length > hash) {
					var irange = self.opt.iconRange[hash],
							on     = irange.on  || self.opt.starOn,
							off    = irange.off || self.opt.starOff,
							icon   = select ? on : off;

					if (i <= irange.range) {
						star.attr('class', icon);
					}

					if (i == irange.range) {
						hash++;
					}
				} else {
					var icon = select ? 'starOn' : 'starOff';

					star.attr('class', this.opt[icon]);
				}
			}
		}, _getHint: function(score) {
			var hint = this.opt.hints[score - 1];
			return (hint === '') ? '' : (hint || score);
		}, _lock: function() {
			var score = parseInt(this.score.val(), 10), // TODO: 3.1 >> [['1'], ['2'], ['3', '.1', '.2']]
					hint  = score ? methods._getHint.call(this, score) : this.opt.noRatedMsg;

			$(this).data('readonly', true).css('cursor', '').attr('title', hint);

			this.score.attr('readonly', 'readonly');
			this.stars.attr('title', hint);

			if (this.cancel) {
				this.cancel.hide();
			}
		}, _roundStars: function(score) {
			var rest = (score - Math.floor(score)).toFixed(2);

			if (rest > this.opt.round.down) {
				var icon = 'starOn';                                 // Up:   [x.76 .. x.99]

				if (this.opt.halfShow && rest < this.opt.round.up) { // Half: [x.26 .. x.75]
					icon = 'starHalf';
				} else if (rest < this.opt.round.full) {             // Down: [x.00 .. x.5]
					icon = 'starOff';
				}

				this.stars.eq(Math.ceil(score) - 1).attr('class', this.opt[icon]);
			}                              // Full down: [x.00 .. x.25]
		}, _target: function(score, evt) {
			if (this.opt.target) {
				var target = $(this.opt.target);

				if (target.length === 0) {
					methods._error.call(this, 'Target selector invalid or missing!');
				}

				if (this.opt.targetFormat.indexOf('{score}') < 0) {
					methods._error.call(this, 'Template "{score}" missing!');
				}

				var mouseover = evt && evt.type == 'mouseover';

				if (score === undefined) {
					score = this.opt.targetText;
				} else if (score === null) {
					score = mouseover ? this.opt.cancelHint : this.opt.targetText;
				} else {
					if (this.opt.targetType == 'hint') {
						score = methods._getHint.call(this, Math.ceil(score));
					} else if (this.opt.precision) {
						score = parseFloat(score).toFixed(1);
					}

					if (!mouseover && !this.opt.targetKeep) {
						score = this.opt.targetText;
					}
				}

				if (score) {
					score = this.opt.targetFormat.toString().replace('{score}', score);
				}

				if (target.is(':input')) {
					target.val(score);
				} else {
					target.html(score);
				}
			}
		}, _unlock: function() {
			$(this).data('readonly', false).css('cursor', 'pointer').removeAttr('title');

			this.score.removeAttr('readonly', 'readonly');

			for (var i = 0; i < this.opt.number; i++) {
				this.stars.eq(i).attr('title', methods._getHint.call(this, i + 1));
			}

			if (this.cancel) {
				this.cancel.css('display', '');
			}
		}, cancel: function(click) {
			return this.each(function() {
				if ($(this).data('readonly') !== true) {
					methods[click ? 'click' : 'score'].call(this, null);
					this.score.removeAttr('value');
				}
			});
		}, click: function(score) {
			return $(this).each(function() {
				if ($(this).data('readonly') !== true) {
					methods._apply.call(this, score);

					if (!this.opt.click) {
						methods._error.call(this, 'You must add the "click: function(score, evt) { }" callback.');
					}


					this.opt.click.call(this, score, $.Event('click'));

					methods._target.call(this, score);
				}
			});
		}, destroy: function() {
			return $(this).each(function() {
				var that = $(this),
						raw  = that.data('raw');

				if (raw) {
					that.off('.raty').empty().css({ cursor: raw.style.cursor, width: raw.style.width }).removeData('readonly');
				} else {
					that.data('raw', that.clone()[0]);
				}
			});
		}, getScore: function() {
			var score = [],
					value ;

			$(this).each(function() {
				value = this.score.val();

				score.push(value ? parseFloat(value) : undefined);
			});

			return (score.length > 1) ? score : score[0];
		}, readOnly: function(readonly) {
			return this.each(function() {
				var that = $(this);

				if (that.data('readonly') !== readonly) {
					if (readonly) {
						that.off('.raty').children('i').off('.raty');

						methods._lock.call(this);
					} else {
						methods._binds.call(this);
						methods._unlock.call(this);
					}

					that.data('readonly', readonly);
				}
			});
		}, reload: function() {
			return methods.set.call(this, {});
		}, score: function() {
			return arguments.length ? methods.setScore.apply(this, arguments) : methods.getScore.call(this);
		}, set: function(settings) {
			return this.each(function() {
				var that   = $(this),
						actual = that.data('settings'),
						news   = $.extend({}, actual, settings);

				that.raty(news);
			});
		}, setScore: function(score) {
			return $(this).each(function() {
				if ($(this).data('readonly') !== true) {
					methods._apply.call(this, score);
					methods._target.call(this, score);
				}
			});
		}
	};

	$.fn.raty = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist!');
		}
	};

	$.fn.raty.defaults = {
		cancel        : false,
		cancelHint    : 'Cancel this rating!',
		cancelOff     : 'fa fa-fw fa-minus-square',
		cancelOn      : 'fa fa-fw fa-check-square',
		cancelPlace   : 'left',
		click         : undefined,
		half          : false,
		halfShow      : true,
		hints         : ['bad', 'poor', 'regular', 'good', 'gorgeous'],
		iconRange     : undefined,
		mouseout      : undefined,
		mouseover     : undefined,
		noRatedMsg    : 'Not rated yet!',
		number        : 5,
		numberMax     : 20,
		precision     : false,
		readOnly      : false,
		round         : { down: .25, full: .6, up: .76 },
		score         : undefined,
		scoreName     : 'score',
		single        : false,
		size          : null,
		space         : true,
		starHalf      : 'fa fa-fw fa-star-half-o',
		starOff       : 'fa fa-fw fa-star-o',
		starOn        : 'fa fa-fw fa-star',
		target        : undefined,
		targetFormat  : '{score}',
		targetKeep    : false,
		targetText    : '',
		targetType    : 'hint',
		width         : false
	};

})(jQuery);

};

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module('raty', moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 

/*!
 * jQuery.ScrollTo
 * Copyright (c) 2007-2012 Ariel Flesler - aflesler(at)gmail(dot)com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * Date: 4/09/2012
 *
 * @projectDescription Easy element scrolling using jQuery.
 * http://flesler.blogspot.com/2007/10/jqueryscrollto.html
 * @author Ariel Flesler
 * @version 1.4.3.1
 *
 * @id jQuery.scrollTo
 * @id jQuery.fn.scrollTo
 * @param {String, Number, DOMElement, jQuery, Object} target Where to scroll the matched elements.
 *	  The different options for target are:
 *		- A number position (will be applied to all axes).
 *		- A string position ('44', '100px', '+=90', etc ) will be applied to all axes
 *		- A jQuery/DOM element ( logically, child of the element to scroll )
 *		- A string selector, that will be relative to the element to scroll ( 'li:eq(2)', etc )
 *		- A hash { top:x, left:y }, x and y can be any kind of number/string like above.
 *		- A percentage of the container's dimension/s, for example: 50% to go to the middle.
 *		- The string 'max' for go-to-end. 
 * @param {Number, Function} duration The OVERALL length of the animation, this argument can be the settings object instead.
 * @param {Object,Function} settings Optional set of settings or the onAfter callback.
 *	 @option {String} axis Which axis must be scrolled, use 'x', 'y', 'xy' or 'yx'.
 *	 @option {Number, Function} duration The OVERALL length of the animation.
 *	 @option {String} easing The easing method for the animation.
 *	 @option {Boolean} margin If true, the margin of the target element will be deducted from the final position.
 *	 @option {Object, Number} offset Add/deduct from the end position. One number for both axes or { top:x, left:y }.
 *	 @option {Object, Number} over Add/deduct the height/width multiplied by 'over', can be { top:x, left:y } when using both axes.
 *	 @option {Boolean} queue If true, and both axis are given, the 2nd axis will only be animated after the first one ends.
 *	 @option {Function} onAfter Function to be called after the scrolling ends. 
 *	 @option {Function} onAfterFirst If queuing is activated, this function will be called after the first scrolling ends.
 * @return {jQuery} Returns the same jQuery object, for chaining.
 *
 * @desc Scroll to a fixed position
 * @example $('div').scrollTo( 340 );
 *
 * @desc Scroll relatively to the actual position
 * @example $('div').scrollTo( '+=340px', { axis:'y' } );
 *
 * @desc Scroll using a selector (relative to the scrolled element)
 * @example $('div').scrollTo( 'p.paragraph:eq(2)', 500, { easing:'swing', queue:true, axis:'xy' } );
 *
 * @desc Scroll to a DOM element (same for jQuery object)
 * @example var second_child = document.getElementById('container').firstChild.nextSibling;
 *			$('#container').scrollTo( second_child, { duration:500, axis:'x', onAfter:function(){
 *				alert('scrolled!!');																   
 *			}});
 *
 * @desc Scroll on both axes, to different values
 * @example $('div').scrollTo( { top: 300, left:'+=200' }, { axis:'xy', offset:-20 } );
 */

;(function( $ ){
	
	var $scrollTo = $.scrollTo = function( target, duration, settings ){
		$(window).scrollTo( target, duration, settings );
	};

	$scrollTo.defaults = {
		axis:'xy',
		duration: parseFloat($.fn.jquery) >= 1.3 ? 0 : 1,
		limit:true
	};

	// Returns the element that needs to be animated to scroll the window.
	// Kept for backwards compatibility (specially for localScroll & serialScroll)
	$scrollTo.window = function( scope ){
		return $(window)._scrollable();
	};

	// Hack, hack, hack :)
	// Returns the real elements to scroll (supports window/iframes, documents and regular nodes)
	$.fn._scrollable = function(){
		return this.map(function(){
			var elem = this,
				isWin = !elem.nodeName || $.inArray( elem.nodeName.toLowerCase(), ['iframe','#document','html','body'] ) != -1;

				if( !isWin )
					return elem;

			var doc = (elem.contentWindow || elem).document || elem.ownerDocument || elem;
			
			return /webkit/i.test(navigator.userAgent) || doc.compatMode == 'BackCompat' ?
				doc.body : 
				doc.documentElement;
		});
	};

	$.fn.scrollTo = function( target, duration, settings ){
		if( typeof duration == 'object' ){
			settings = duration;
			duration = 0;
		}
		if( typeof settings == 'function' )
			settings = { onAfter:settings };
			
		if( target == 'max' )
			target = 9e9;
			
		settings = $.extend( {}, $scrollTo.defaults, settings );
		// Speed is still recognized for backwards compatibility
		duration = duration || settings.duration;
		// Make sure the settings are given right
		settings.queue = settings.queue && settings.axis.length > 1;
		
		if( settings.queue )
			// Let's keep the overall duration
			duration /= 2;
		settings.offset = both( settings.offset );
		settings.over = both( settings.over );

		return this._scrollable().each(function(){
			// Null target yields nothing, just like jQuery does
			if (target == null) return;

			var elem = this,
				$elem = $(elem),
				targ = target, toff, attr = {},
				win = $elem.is('html,body');

			switch( typeof targ ){
				// A number will pass the regex
				case 'number':
				case 'string':
					if( /^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(targ) ){
						targ = both( targ );
						// We are done
						break;
					}
					// Relative selector, no break!
					targ = $(targ,this);
					if (!targ.length) return;
				case 'object':
					// DOMElement / jQuery
					if( targ.is || targ.style )
						// Get the real position of the target 
						toff = (targ = $(targ)).offset();
			}
			$.each( settings.axis.split(''), function( i, axis ){
				var Pos	= axis == 'x' ? 'Left' : 'Top',
					pos = Pos.toLowerCase(),
					key = 'scroll' + Pos,
					old = elem[key],
					max = $scrollTo.max(elem, axis);

				if( toff ){// jQuery / DOMElement
					attr[key] = toff[pos] + ( win ? 0 : old - $elem.offset()[pos] );

					// If it's a dom element, reduce the margin
					if( settings.margin ){
						attr[key] -= parseInt(targ.css('margin'+Pos)) || 0;
						attr[key] -= parseInt(targ.css('border'+Pos+'Width')) || 0;
					}
					
					attr[key] += settings.offset[pos] || 0;
					
					if( settings.over[pos] )
						// Scroll to a fraction of its width/height
						attr[key] += targ[axis=='x'?'width':'height']() * settings.over[pos];
				}else{ 
					var val = targ[pos];
					// Handle percentage values
					attr[key] = val.slice && val.slice(-1) == '%' ? 
						parseFloat(val) / 100 * max
						: val;
				}

				// Number or 'number'
				if( settings.limit && /^\d+$/.test(attr[key]) )
					// Check the limits
					attr[key] = attr[key] <= 0 ? 0 : Math.min( attr[key], max );

				// Queueing axes
				if( !i && settings.queue ){
					// Don't waste time animating, if there's no need.
					if( old != attr[key] )
						// Intermediate animation
						animate( settings.onAfterFirst );
					// Don't animate this axis again in the next iteration.
					delete attr[key];
				}
			});

			animate( settings.onAfter );			

			function animate( callback ){
				$elem.animate( attr, duration, settings.easing, callback && function(){
					callback.call(this, target, settings);
				});
			};

		}).end();
	};

	$.fn.scrollIntoView = function(target) {

		// TODO: Add support for X axis.
		// TODO: Add support for passing in custom options.

		var target = $(target);
		if (target.length < 1) return;

		var viewportOffset = this.offset(),
			viewportHeight = this.height(),
			viewportTop    = viewportOffset.top,
			viewportBottom = viewportTop + viewportHeight,

			targetOffset = target.offset(),
			targetHeight = target.height(),
			targetTop    = targetOffset.top,
			targetBottom = targetTop + targetHeight;

		if (targetBottom > viewportBottom) {
			return this.scrollTo(target);
		}

		if (targetTop < viewportTop) {
			return this.scrollTo(target, {offset: (viewportHeight - targetHeight) * -1});
		}
	};	
	
	// Max scrolling position, works on quirks mode
	// It only fails (not too badly) on IE, quirks mode.
	$scrollTo.max = function( elem, axis ){
		var Dim = axis == 'x' ? 'Width' : 'Height',
			scroll = 'scroll'+Dim;
		
		if( !$(elem).is('html,body') )
			return elem[scroll] - $(elem)[Dim.toLowerCase()]();
		
		var size = 'client' + Dim,
			html = elem.ownerDocument.documentElement,
			body = elem.ownerDocument.body;

		return Math.max( html[scroll], body[scroll] ) 
			 - Math.min( html[size]  , body[size]   );
	};

	function both( val ){
		return typeof val == 'object' ? val : { top:val, left:val };
	};

})( jQuery );
}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("scrollTo", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
$.require() 
 .script("ui/core","ui/widget","ui/position","ui/menu") 
 .done(function() { 
var exports = function() { 

/*!
 * jQuery UI Autocomplete 1.10.4pre
 * http://jqueryui.com
 *
 * Copyright 2013 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/autocomplete/
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	jquery.ui.position.js
 *	jquery.ui.menu.js
 */
(function( $, undefined ) {

// used to prevent race conditions with remote data sources
var requestIndex = 0;

$.widget( "ui.autocomplete", {
	version: "1.10.4pre",
	defaultElement: "<input>",
	options: {
		appendTo: "#kt.ui",
		autoFocus: false,
		delay: 300,
		minLength: 1,
		position: {
			my: "left top",
			at: "left bottom",
			collision: "none"
		},
		source: null,

		// callbacks
		change: null,
		close: null,
		focus: null,
		open: null,
		response: null,
		search: null,
		select: null
	},

	pending: 0,

	_create: function() {
		// Some browsers only repeat keydown events, not keypress events,
		// so we use the suppressKeyPress flag to determine if we've already
		// handled the keydown event. #7269
		// Unfortunately the code for & in keypress is the same as the up arrow,
		// so we use the suppressKeyPressRepeat flag to avoid handling keypress
		// events when we know the keydown event was used to modify the
		// search term. #7799
		var suppressKeyPress, suppressKeyPressRepeat, suppressInput,
			nodeName = this.element[0].nodeName.toLowerCase(),
			isTextarea = nodeName === "textarea",
			isInput = nodeName === "input";

		this.isMultiLine =
			// Textareas are always multi-line
			isTextarea ? true :
			// Inputs are always single-line, even if inside a contentEditable element
			// IE also treats inputs as contentEditable
			isInput ? false :
			// All other element types are determined by whether or not they're contentEditable
			this.element.prop( "isContentEditable" );

		this.valueMethod = this.element[ isTextarea || isInput ? "val" : "text" ];
		this.isNewMenu = true;

		this.element
			.addClass( "ui-autocomplete-input" )
			.attr( "autocomplete", "off" );

		this._on( this.element, {
			keydown: function( event ) {
				/*jshint maxcomplexity:15*/
				if ( this.element.prop( "readOnly" ) ) {
					suppressKeyPress = true;
					suppressInput = true;
					suppressKeyPressRepeat = true;
					return;
				}

				suppressKeyPress = false;
				suppressInput = false;
				suppressKeyPressRepeat = false;
				var keyCode = $.ui.keyCode;
				switch( event.keyCode ) {
				case keyCode.PAGE_UP:
					suppressKeyPress = true;
					this._move( "previousPage", event );
					break;
				case keyCode.PAGE_DOWN:
					suppressKeyPress = true;
					this._move( "nextPage", event );
					break;
				case keyCode.UP:
					suppressKeyPress = true;
					this._keyEvent( "previous", event );
					break;
				case keyCode.DOWN:
					suppressKeyPress = true;
					this._keyEvent( "next", event );
					break;
				case keyCode.ENTER:
				case keyCode.NUMPAD_ENTER:
					// when menu is open and has focus
					if ( this.menu.active ) {
						// #6055 - Opera still allows the keypress to occur
						// which causes forms to submit
						suppressKeyPress = true;
						event.preventDefault();
						this.menu.select( event );
					}
					break;
				case keyCode.TAB:
					if ( this.menu.active ) {
						this.menu.select( event );
					}
					break;
				case keyCode.ESCAPE:
					if ( this.menu.element.is( ":visible" ) ) {
						this._value( this.term );
						this.close( event );
						// Different browsers have different default behavior for escape
						// Single press can mean undo or clear
						// Double press in IE means clear the whole form
						event.preventDefault();
					}
					break;
				default:
					suppressKeyPressRepeat = true;
					// search timeout should be triggered before the input value is changed
					this._searchTimeout( event );
					break;
				}
			},
			keypress: function( event ) {
				if ( suppressKeyPress ) {
					suppressKeyPress = false;
					if ( !this.isMultiLine || this.menu.element.is( ":visible" ) ) {
						event.preventDefault();
					}
					return;
				}
				if ( suppressKeyPressRepeat ) {
					return;
				}

				// replicate some key handlers to allow them to repeat in Firefox and Opera
				var keyCode = $.ui.keyCode;
				switch( event.keyCode ) {
				case keyCode.PAGE_UP:
					this._move( "previousPage", event );
					break;
				case keyCode.PAGE_DOWN:
					this._move( "nextPage", event );
					break;
				case keyCode.UP:
					this._keyEvent( "previous", event );
					break;
				case keyCode.DOWN:
					this._keyEvent( "next", event );
					break;
				}
			},
			input: function( event ) {
				if ( suppressInput ) {
					suppressInput = false;
					event.preventDefault();
					return;
				}
				this._searchTimeout( event );
			},
			focus: function() {
				this.selectedItem = null;
				this.previous = this._value();
			},
			blur: function( event ) {
				if ( this.cancelBlur ) {
					delete this.cancelBlur;
					return;
				}

				clearTimeout( this.searching );
				this.close( event );
				this._change( event );
			}
		});

		this._initSource();
		this.menu = $( "<ul>" )
			.addClass( "ui-autocomplete ui-front" )
			.appendTo( this._appendTo() )
			.menu({
				// disable ARIA support, the live region takes care of that
				role: null
			})
			.hide()
			.data( "ui-menu" );

		this._on( this.menu.element, {
			mousedown: function( event ) {
				// prevent moving focus out of the text field
				event.preventDefault();

				// IE doesn't prevent moving focus even with event.preventDefault()
				// so we set a flag to know when we should ignore the blur event
				this.cancelBlur = true;
				this._delay(function() {
					delete this.cancelBlur;
				});

				// clicking on the scrollbar causes focus to shift to the body
				// but we can't detect a mouseup or a click immediately afterward
				// so we have to track the next mousedown and close the menu if
				// the user clicks somewhere outside of the autocomplete
				var menuElement = this.menu.element[ 0 ];
				if ( !$( event.target ).closest( ".ui-menu-item" ).length ) {
					this._delay(function() {
						var that = this;
						this.document.one( "mousedown", function( event ) {
							if ( event.target !== that.element[ 0 ] &&
									event.target !== menuElement &&
									!$.contains( menuElement, event.target ) ) {
								that.close();
							}
						});
					});
				}
			},
			menufocus: function( event, ui ) {
				// support: Firefox
				// Prevent accidental activation of menu items in Firefox (#7024 #9118)
				if ( this.isNewMenu ) {
					this.isNewMenu = false;
					if ( event.originalEvent && /^mouse/.test( event.originalEvent.type ) ) {
						this.menu.blur();

						this.document.one( "mousemove", function() {
							$( event.target ).trigger( event.originalEvent );
						});

						return;
					}
				}

				var item = ui.item.data( "ui-autocomplete-item" );
				if ( false !== this._trigger( "focus", event, { item: item } ) ) {
					// use value to match what will end up in the input, if it was a key event
					if ( event.originalEvent && /^key/.test( event.originalEvent.type ) ) {
						this._value( item.value );
					}
				} else {
					// Normally the input is populated with the item's value as the
					// menu is navigated, causing screen readers to notice a change and
					// announce the item. Since the focus event was canceled, this doesn't
					// happen, so we update the live region so that screen readers can
					// still notice the change and announce it.
					this.liveRegion.text( item.value );
				}
			},
			menuselect: function( event, ui ) {
				var item = ui.item.data( "ui-autocomplete-item" ),
					previous = this.previous;

				// only trigger when focus was lost (click on menu)
				if ( this.element[0] !== this.document[0].activeElement ) {
					this.element.focus();
					this.previous = previous;
					// #6109 - IE triggers two focus events and the second
					// is asynchronous, so we need to reset the previous
					// term synchronously and asynchronously :-(
					this._delay(function() {
						this.previous = previous;
						this.selectedItem = item;
					});
				}

				if ( false !== this._trigger( "select", event, { item: item } ) ) {
					this._value( item.value );
				}
				// reset the term after the select event
				// this allows custom select handling to work properly
				this.term = this._value();

				this.close( event );
				this.selectedItem = item;
			}
		});

		this.liveRegion = $( "<span>", {
				role: "status",
				"aria-live": "polite"
			})
			.addClass( "ui-helper-hidden-accessible" )
			.insertBefore( this.element );

		// turning off autocomplete prevents the browser from remembering the
		// value when navigating through history, so we re-enable autocomplete
		// if the page is unloaded before the widget is destroyed. #7790
		this._on( this.window, {
			beforeunload: function() {
				this.element.removeAttr( "autocomplete" );
			}
		});
	},

	_destroy: function() {
		clearTimeout( this.searching );
		this.element
			.removeClass( "ui-autocomplete-input" )
			.removeAttr( "autocomplete" );
		this.menu.element.remove();
		this.liveRegion.remove();
	},

	_setOption: function( key, value ) {
		this._super( key, value );
		if ( key === "source" ) {
			this._initSource();
		}
		if ( key === "appendTo" ) {
			this.menu.element.appendTo( this._appendTo() );
		}
		if ( key === "disabled" && value && this.xhr ) {
			this.xhr.abort();
		}
	},

	_appendTo: function() {
		var element = this.options.appendTo;

		if ( element ) {
			element = element.jquery || element.nodeType ?
				$( element ) :
				this.document.find( element ).eq( 0 );
		}

		if ( !element ) {
			element = this.element.closest( ".ui-front" );
		}

		if ( !element.length ) {
			element = this.document[0].body;
		}

		return element;
	},

	_initSource: function() {
		var array, url,
			that = this;
		if ( $.isArray(this.options.source) ) {
			array = this.options.source;
			this.source = function( request, response ) {
				response( $.ui.autocomplete.filter( array, request.term ) );
			};
		} else if ( typeof this.options.source === "string" ) {
			url = this.options.source;
			this.source = function( request, response ) {
				if ( that.xhr ) {
					that.xhr.abort();
				}
				that.xhr = $.ajax({
					url: url,
					data: request,
					dataType: "json",
					success: function( data ) {
						response( data );
					},
					error: function() {
						response( [] );
					}
				});
			};
		} else {
			this.source = this.options.source;
		}
	},

	_searchTimeout: function( event ) {
		clearTimeout( this.searching );
		this.searching = this._delay(function() {
			// only search if the value has changed
			if ( this.term !== this._value() ) {
				this.selectedItem = null;
				this.search( null, event );
			}
		}, this.options.delay );
	},

	search: function( value, event ) {
		value = value != null ? value : this._value();

		// always save the actual value, not the one passed as an argument
		this.term = this._value();

		if ( value.length < this.options.minLength ) {
			return this.close( event );
		}

		if ( this._trigger( "search", event ) === false ) {
			return;
		}

		return this._search( value );
	},

	_search: function( value ) {
		this.pending++;
		this.element.addClass( "ui-autocomplete-loading" );
		this.cancelSearch = false;

		this.source( { term: value }, this._response() );
	},

	_response: function() {
		var that = this,
			index = ++requestIndex;

		return function( content ) {
			if ( index === requestIndex ) {
				that.__response( content );
			}

			that.pending--;
			if ( !that.pending ) {
				that.element.removeClass( "ui-autocomplete-loading" );
			}
		};
	},

	__response: function( content ) {
		if ( content ) {
			content = this._normalize( content );
		}
		this._trigger( "response", null, { content: content } );
		if ( !this.options.disabled && content && content.length && !this.cancelSearch ) {
			this._suggest( content );
			this._trigger( "open" );
		} else {
			// use ._close() instead of .close() so we don't cancel future searches
			this._close();
		}
	},

	close: function( event ) {
		this.cancelSearch = true;
		this._close( event );
	},

	_close: function( event ) {
		if ( this.menu.element.is( ":visible" ) ) {
			this.menu.element.hide();
			this.menu.blur();
			this.isNewMenu = true;
			this._trigger( "close", event );
		}
	},

	_change: function( event ) {
		if ( this.previous !== this._value() ) {
			this._trigger( "change", event, { item: this.selectedItem } );
		}
	},

	_normalize: function( items ) {
		// assume all items have the right format when the first item is complete
		if ( items.length && items[0].label && items[0].value ) {
			return items;
		}
		return $.map( items, function( item ) {
			if ( typeof item === "string" ) {
				return {
					label: item,
					value: item
				};
			}
			return $.extend({
				label: item.label || item.value,
				value: item.value || item.label
			}, item );
		});
	},

	_suggest: function( items ) {
		var ul = this.menu.element.empty();
		this._renderMenu( ul, items );
		this.isNewMenu = true;
		this.menu.refresh();

		// size and position menu
		ul.show();
		this._resizeMenu();
		ul.position( $.extend({
			of: this.element
		}, this.options.position ));

		if ( this.options.autoFocus ) {
			this.menu.next();
		}
	},

	_resizeMenu: function() {
		var ul = this.menu.element;
		ul.outerWidth( Math.max(
			// Firefox wraps long text (possibly a rounding bug)
			// so we add 1px to avoid the wrapping (#7513)
			ul.width( "" ).outerWidth() + 1,
			this.element.outerWidth()
		) );
	},

	_renderMenu: function( ul, items ) {
		var that = this;
		$.each( items, function( index, item ) {
			that._renderItemData( ul, item );
		});
	},

	_renderItemData: function( ul, item ) {
		return this._renderItem( ul, item ).data( "ui-autocomplete-item", item );
	},

	_renderItem: function( ul, item ) {
		return $( "<li>" )
			.append( $( "<a>" ).text( item.label ) )
			.appendTo( ul );
	},

	_move: function( direction, event ) {
		if ( !this.menu.element.is( ":visible" ) ) {
			this.search( null, event );
			return;
		}
		if ( this.menu.isFirstItem() && /^previous/.test( direction ) ||
				this.menu.isLastItem() && /^next/.test( direction ) ) {
			this._value( this.term );
			this.menu.blur();
			return;
		}
		this.menu[ direction ]( event );
	},

	widget: function() {
		return this.menu.element;
	},

	_value: function() {
		return this.valueMethod.apply( this.element, arguments );
	},

	_keyEvent: function( keyEvent, event ) {
		if ( !this.isMultiLine || this.menu.element.is( ":visible" ) ) {
			this._move( keyEvent, event );

			// prevents moving cursor to beginning/end of the text field in some browsers
			event.preventDefault();
		}
	}
});

$.extend( $.ui.autocomplete, {
	escapeRegex: function( value ) {
		return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
	},
	filter: function(array, term) {
		var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
		return $.grep( array, function(value) {
			return matcher.test( value.label || value.value || value );
		});
	}
});


// live region extension, adding a `messages` option
// NOTE: This is an experimental API. We are still investigating
// a full solution for string manipulation and internationalization.
$.widget( "ui.autocomplete", $.ui.autocomplete, {
	options: {
		messages: {
			noResults: "No search results.",
			results: function( amount ) {
				return amount + ( amount > 1 ? " results are" : " result is" ) +
					" available, use up and down arrow keys to navigate.";
			}
		}
	},

	__response: function( content ) {
		var message;
		this._superApply( arguments );
		if ( this.options.disabled || this.cancelSearch ) {
			return;
		}
		if ( content && content.length ) {
			message = this.options.messages.results( content.length );
		} else {
			message = this.options.messages.noResults;
		}
		this.liveRegion.text( message );
	}
});

}( jQuery ));


/*
* jQuery UI Autocomplete HTML Extension
*
* Copyright 2010, Scott González (http://scottgonzalez.com)
* Dual licensed under the MIT or GPL Version 2 licenses.
*
* http://github.com/scottgonzalez/jquery-ui-extensions
*/
(function( $ ) {

	var proto = $.ui.autocomplete.prototype,
		initSource = proto._initSource;

	function filter( array, term ) {
		var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
		
		return $.grep( array, function(value) {
			return matcher.test( $( "<div>" ).html( value.label || value.value || value ).text() );
		});
	}

	$.extend( proto, {
		_initSource: function() {
		
			if ( this.options.html && $.isArray(this.options.source) ) {
				this.source = function( request, response ) {
					response( filter( this.options.source, request.term ) );
				};
			} else {
				initSource.call( this );
			}
		},

		_renderItem: function( ul, item) {
			return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( $( "<a></a>" )[ this.options.html ? "html" : "text" ]( item.label ) )
						.appendTo( ul );
		}
	});

})( jQuery );

}; 

exports(); 
module.resolveWith(exports); 

}); 
// module body: end

}; 
// module factory: end

KTVendors.module("ui/autocomplete", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 

/*!
 * jQuery UI Core 1.10.4pre
 * http://jqueryui.com
 *
 * Copyright 2013 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/category/ui-core/
 */
(function( $, undefined ) {

var uuid = 0,
	runiqueId = /^ui-id-\d+$/;

// $.ui might exist from components with no dependencies, e.g., $.ui.position
$.ui = $.ui || {};

$.extend( $.ui, {
	version: "1.10.4pre",

	keyCode: {
		BACKSPACE: 8,
		COMMA: 188,
		DELETE: 46,
		DOWN: 40,
		END: 35,
		ENTER: 13,
		ESCAPE: 27,
		HOME: 36,
		LEFT: 37,
		NUMPAD_ADD: 107,
		NUMPAD_DECIMAL: 110,
		NUMPAD_DIVIDE: 111,
		NUMPAD_ENTER: 108,
		NUMPAD_MULTIPLY: 106,
		NUMPAD_SUBTRACT: 109,
		PAGE_DOWN: 34,
		PAGE_UP: 33,
		PERIOD: 190,
		RIGHT: 39,
		SPACE: 32,
		TAB: 9,
		UP: 38
	}
});

// plugins
$.fn.extend({
	focus: (function( orig ) {
		return function( delay, fn ) {
			return typeof delay === "number" ?
				this.each(function() {
					var elem = this;
					setTimeout(function() {
						$( elem ).focus();
						if ( fn ) {
							fn.call( elem );
						}
					}, delay );
				}) :
				orig.apply( this, arguments );
		};
	})( $.fn.focus ),

	scrollParent: function() {
		var scrollParent;
		if (($.ui.ie && (/(static|relative)/).test(this.css("position"))) || (/absolute/).test(this.css("position"))) {
			scrollParent = this.parents().filter(function() {
				return (/(relative|absolute|fixed)/).test($.css(this,"position")) && (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
			}).eq(0);
		} else {
			scrollParent = this.parents().filter(function() {
				return (/(auto|scroll)/).test($.css(this,"overflow")+$.css(this,"overflow-y")+$.css(this,"overflow-x"));
			}).eq(0);
		}

		return (/fixed/).test(this.css("position")) || !scrollParent.length ? $(document) : scrollParent;
	},

	zIndex: function( zIndex ) {
		if ( zIndex !== undefined ) {
			return this.css( "zIndex", zIndex );
		}

		if ( this.length ) {
			var elem = $( this[ 0 ] ), position, value;
			while ( elem.length && elem[ 0 ] !== document ) {
				// Ignore z-index if position is set to a value where z-index is ignored by the browser
				// This makes behavior of this function consistent across browsers
				// WebKit always returns auto if the element is positioned
				position = elem.css( "position" );
				if ( position === "absolute" || position === "relative" || position === "fixed" ) {
					// IE returns 0 when zIndex is not specified
					// other browsers return a string
					// we ignore the case of nested elements with an explicit value of 0
					// <div style="z-index: -10;"><div style="z-index: 0;"></div></div>
					value = parseInt( elem.css( "zIndex" ), 10 );
					if ( !isNaN( value ) && value !== 0 ) {
						return value;
					}
				}
				elem = elem.parent();
			}
		}

		return 0;
	},

	uniqueId: function() {
		return this.each(function() {
			if ( !this.id ) {
				this.id = "ui-id-" + (++uuid);
			}
		});
	},

	removeUniqueId: function() {
		return this.each(function() {
			if ( runiqueId.test( this.id ) ) {
				$( this ).removeAttr( "id" );
			}
		});
	}
});

// selectors
function focusable( element, isTabIndexNotNaN ) {
	var map, mapName, img,
		nodeName = element.nodeName.toLowerCase();
	if ( "area" === nodeName ) {
		map = element.parentNode;
		mapName = map.name;
		if ( !element.href || !mapName || map.nodeName.toLowerCase() !== "map" ) {
			return false;
		}
		img = $( "img[usemap=#" + mapName + "]" )[0];
		return !!img && visible( img );
	}
	return ( /input|select|textarea|button|object/.test( nodeName ) ?
		!element.disabled :
		"a" === nodeName ?
			element.href || isTabIndexNotNaN :
			isTabIndexNotNaN) &&
		// the element and all of its ancestors must be visible
		visible( element );
}

function visible( element ) {
	return $.expr.filters.visible( element ) &&
		!$( element ).parents().addBack().filter(function() {
			return $.css( this, "visibility" ) === "hidden";
		}).length;
}

$.extend( $.expr[ ":" ], {
	data: $.expr.createPseudo ?
		$.expr.createPseudo(function( dataName ) {
			return function( elem ) {
				return !!$.data( elem, dataName );
			};
		}) :
		// support: jQuery <1.8
		function( elem, i, match ) {
			return !!$.data( elem, match[ 3 ] );
		},

	focusable: function( element ) {
		return focusable( element, !isNaN( $.attr( element, "tabindex" ) ) );
	},

	tabbable: function( element ) {
		var tabIndex = $.attr( element, "tabindex" ),
			isTabIndexNaN = isNaN( tabIndex );
		return ( isTabIndexNaN || tabIndex >= 0 ) && focusable( element, !isTabIndexNaN );
	}
});

// support: jQuery <1.8
if ( !$( "<a>" ).outerWidth( 1 ).jquery ) {
	$.each( [ "Width", "Height" ], function( i, name ) {
		var side = name === "Width" ? [ "Left", "Right" ] : [ "Top", "Bottom" ],
			type = name.toLowerCase(),
			orig = {
				innerWidth: $.fn.innerWidth,
				innerHeight: $.fn.innerHeight,
				outerWidth: $.fn.outerWidth,
				outerHeight: $.fn.outerHeight
			};

		function reduce( elem, size, border, margin ) {
			$.each( side, function() {
				size -= parseFloat( $.css( elem, "padding" + this ) ) || 0;
				if ( border ) {
					size -= parseFloat( $.css( elem, "border" + this + "Width" ) ) || 0;
				}
				if ( margin ) {
					size -= parseFloat( $.css( elem, "margin" + this ) ) || 0;
				}
			});
			return size;
		}

		$.fn[ "inner" + name ] = function( size ) {
			if ( size === undefined ) {
				return orig[ "inner" + name ].call( this );
			}

			return this.each(function() {
				$( this ).css( type, reduce( this, size ) + "px" );
			});
		};

		$.fn[ "outer" + name] = function( size, margin ) {
			if ( typeof size !== "number" ) {
				return orig[ "outer" + name ].call( this, size );
			}

			return this.each(function() {
				$( this).css( type, reduce( this, size, true, margin ) + "px" );
			});
		};
	});
}

// support: jQuery <1.8
if ( !$.fn.addBack ) {
	$.fn.addBack = function( selector ) {
		return this.add( selector == null ?
			this.prevObject : this.prevObject.filter( selector )
		);
	};
}

// support: jQuery 1.6.1, 1.6.2 (http://bugs.jquery.com/ticket/9413)
if ( $( "<a>" ).data( "a-b", "a" ).removeData( "a-b" ).data( "a-b" ) ) {
	$.fn.removeData = (function( removeData ) {
		return function( key ) {
			if ( arguments.length ) {
				return removeData.call( this, $.camelCase( key ) );
			} else {
				return removeData.call( this );
			}
		};
	})( $.fn.removeData );
}





// deprecated
$.ui.ie = !!/msie [\w.]+/.exec( navigator.userAgent.toLowerCase() );

$.support.selectstart = "onselectstart" in document.createElement( "div" );
$.fn.extend({
	disableSelection: function() {
		return this.bind( ( $.support.selectstart ? "selectstart" : "mousedown" ) +
			".ui-disableSelection", function( event ) {
				event.preventDefault();
			});
	},

	enableSelection: function() {
		return this.unbind( ".ui-disableSelection" );
	}
});

$.extend( $.ui, {
	// $.ui.plugin is deprecated. Use $.widget() extensions instead.
	plugin: {
		add: function( module, option, set ) {
			var i,
				proto = $.ui[ module ].prototype;
			for ( i in set ) {
				proto.plugins[ i ] = proto.plugins[ i ] || [];
				proto.plugins[ i ].push( [ option, set[ i ] ] );
			}
		},
		call: function( instance, name, args ) {
			var i,
				set = instance.plugins[ name ];
			if ( !set || !instance.element[ 0 ].parentNode || instance.element[ 0 ].parentNode.nodeType === 11 ) {
				return;
			}

			for ( i = 0; i < set.length; i++ ) {
				if ( instance.options[ set[ i ][ 0 ] ] ) {
					set[ i ][ 1 ].apply( instance.element, args );
				}
			}
		}
	},

	// only used by resizable
	hasScroll: function( el, a ) {

		//If overflow is hidden, the element might have extra content, but the user wants to hide it
		if ( $( el ).css( "overflow" ) === "hidden") {
			return false;
		}

		var scroll = ( a && a === "left" ) ? "scrollLeft" : "scrollTop",
			has = false;

		if ( el[ scroll ] > 0 ) {
			return true;
		}

		// TODO: determine which cases actually cause this to happen
		// if the element doesn't have the scroll set, see if it's possible to
		// set the scroll
		el[ scroll ] = 1;
		has = ( el[ scroll ] > 0 );
		el[ scroll ] = 0;
		return has;
	}
});

$(function(){

	if ($("body > [id=kt].ui").length > 0) return;

	// Create container for #fd-ui
	$(document.createElement("div"))
		.attr("id", "kt")
		.addClass("ui")
		.css({
			position: "absolute",
			top: 0,
			left: 0,
			overflow: "visible",
			width: 0,
			height: 0,
			zIndex: 10002
		})
		.appendTo("body");
});

})( jQuery );

}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("ui/core", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
$.require() 
 .script("ui/widget") 
 .done(function() { 
var exports = function() { 

/*!
 * jQuery UI Menu 1.10.4pre
 * http://jqueryui.com
 *
 * Copyright 2013 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/menu/
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *	jquery.ui.position.js
 */
(function( $, undefined ) {

$.widget( "ui.menu", {
	version: "1.10.4pre",
	defaultElement: "<ul>",
	delay: 300,
	options: {
		icons: {
			submenu: "ui-icon-carat-1-e"
		},
		menus: "ul",
		position: {
			my: "left top",
			at: "right top"
		},
		role: "menu",

		// callbacks
		blur: null,
		focus: null,
		select: null
	},

	_create: function() {
		this.activeMenu = this.element;
		// flag used to prevent firing of the click handler
		// as the event bubbles up through nested menus
		this.mouseHandled = false;
		this.element
			.uniqueId()
			.addClass( "ui-menu ui-widget ui-widget-content ui-corner-all" )
			.toggleClass( "ui-menu-icons", !!this.element.find( ".ui-icon" ).length )
			.attr({
				role: this.options.role,
				tabIndex: 0
			})
			// need to catch all clicks on disabled menu
			// not possible through _on
			.bind( "click" + this.eventNamespace, $.proxy(function( event ) {
				if ( this.options.disabled ) {
					event.preventDefault();
				}
			}, this ));

		if ( this.options.disabled ) {
			this.element
				.addClass( "ui-state-disabled" )
				.attr( "aria-disabled", "true" );
		}

		this._on({
			// Prevent focus from sticking to links inside menu after clicking
			// them (focus should always stay on UL during navigation).
			"mousedown .ui-menu-item > a": function( event ) {
				event.preventDefault();
			},
			"click .ui-state-disabled > a": function( event ) {
				event.preventDefault();
			},
			"click .ui-menu-item:has(a)": function( event ) {
				var target = $( event.target ).closest( ".ui-menu-item" );
				if ( !this.mouseHandled && target.not( ".ui-state-disabled" ).length ) {
					this.mouseHandled = true;

					this.select( event );
					// Open submenu on click
					if ( target.has( ".ui-menu" ).length ) {
						this.expand( event );
					} else if ( !this.element.is( ":focus" ) ) {
						// Redirect focus to the menu
						this.element.trigger( "focus", [ true ] );

						// If the active item is on the top level, let it stay active.
						// Otherwise, blur the active item since it is no longer visible.
						if ( this.active && this.active.parents( ".ui-menu" ).length === 1 ) {
							clearTimeout( this.timer );
						}
					}
				}
			},
			"mouseenter .ui-menu-item": function( event ) {
				var target = $( event.currentTarget );
				// Remove ui-state-active class from siblings of the newly focused menu item
				// to avoid a jump caused by adjacent elements both having a class with a border
				target.siblings().children( ".ui-state-active" ).removeClass( "ui-state-active" );
				this.focus( event, target );
			},
			mouseleave: "collapseAll",
			"mouseleave .ui-menu": "collapseAll",
			focus: function( event, keepActiveItem ) {
				// If there's already an active item, keep it active
				// If not, activate the first item
				var item = this.active || this.element.children( ".ui-menu-item" ).eq( 0 );

				if ( !keepActiveItem ) {
					this.focus( event, item );
				}
			},
			blur: function( event ) {
				this._delay(function() {
					if ( !$.contains( this.element[0], this.document[0].activeElement ) ) {
						this.collapseAll( event );
					}
				});
			},
			keydown: "_keydown"
		});

		this.refresh();

		// Clicks outside of a menu collapse any open menus
		this._on( this.document, {
			click: function( event ) {
				if ( !$( event.target ).closest( ".ui-menu" ).length ) {
					this.collapseAll( event );
				}

				// Reset the mouseHandled flag
				this.mouseHandled = false;
			}
		});
	},

	_destroy: function() {
		// Destroy (sub)menus
		this.element
			.removeAttr( "aria-activedescendant" )
			.find( ".ui-menu" ).addBack()
				.removeClass( "ui-menu ui-widget ui-widget-content ui-corner-all ui-menu-icons" )
				.removeAttr( "role" )
				.removeAttr( "tabIndex" )
				.removeAttr( "aria-labelledby" )
				.removeAttr( "aria-expanded" )
				.removeAttr( "aria-hidden" )
				.removeAttr( "aria-disabled" )
				.removeUniqueId()
				.show();

		// Destroy menu items
		this.element.find( ".ui-menu-item" )
			.removeClass( "ui-menu-item" )
			.removeAttr( "role" )
			.removeAttr( "aria-disabled" )
			.children( "a" )
				.removeUniqueId()
				.removeClass( "ui-corner-all ui-state-hover" )
				.removeAttr( "tabIndex" )
				.removeAttr( "role" )
				.removeAttr( "aria-haspopup" )
				.children().each( function() {
					var elem = $( this );
					if ( elem.data( "ui-menu-submenu-carat" ) ) {
						elem.remove();
					}
				});

		// Destroy menu dividers
		this.element.find( ".ui-menu-divider" ).removeClass( "ui-menu-divider ui-widget-content" );
	},

	_keydown: function( event ) {
		/*jshint maxcomplexity:20*/
		var match, prev, character, skip, regex,
			preventDefault = true;

		function escape( value ) {
			return value.replace( /[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&" );
		}

		switch ( event.keyCode ) {
		case $.ui.keyCode.PAGE_UP:
			this.previousPage( event );
			break;
		case $.ui.keyCode.PAGE_DOWN:
			this.nextPage( event );
			break;
		case $.ui.keyCode.HOME:
			this._move( "first", "first", event );
			break;
		case $.ui.keyCode.END:
			this._move( "last", "last", event );
			break;
		case $.ui.keyCode.UP:
			this.previous( event );
			break;
		case $.ui.keyCode.DOWN:
			this.next( event );
			break;
		case $.ui.keyCode.LEFT:
			this.collapse( event );
			break;
		case $.ui.keyCode.RIGHT:
			if ( this.active && !this.active.is( ".ui-state-disabled" ) ) {
				this.expand( event );
			}
			break;
		case $.ui.keyCode.ENTER:
		case $.ui.keyCode.SPACE:
			this._activate( event );
			break;
		case $.ui.keyCode.ESCAPE:
			this.collapse( event );
			break;
		default:
			preventDefault = false;
			prev = this.previousFilter || "";
			character = String.fromCharCode( event.keyCode );
			skip = false;

			clearTimeout( this.filterTimer );

			if ( character === prev ) {
				skip = true;
			} else {
				character = prev + character;
			}

			regex = new RegExp( "^" + escape( character ), "i" );
			match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
				return regex.test( $( this ).children( "a" ).text() );
			});
			match = skip && match.index( this.active.next() ) !== -1 ?
				this.active.nextAll( ".ui-menu-item" ) :
				match;

			// If no matches on the current filter, reset to the last character pressed
			// to move down the menu to the first item that starts with that character
			if ( !match.length ) {
				character = String.fromCharCode( event.keyCode );
				regex = new RegExp( "^" + escape( character ), "i" );
				match = this.activeMenu.children( ".ui-menu-item" ).filter(function() {
					return regex.test( $( this ).children( "a" ).text() );
				});
			}

			if ( match.length ) {
				this.focus( event, match );
				if ( match.length > 1 ) {
					this.previousFilter = character;
					this.filterTimer = this._delay(function() {
						delete this.previousFilter;
					}, 1000 );
				} else {
					delete this.previousFilter;
				}
			} else {
				delete this.previousFilter;
			}
		}

		if ( preventDefault ) {
			event.preventDefault();
		}
	},

	_activate: function( event ) {
		if ( !this.active.is( ".ui-state-disabled" ) ) {
			if ( this.active.children( "a[aria-haspopup='true']" ).length ) {
				this.expand( event );
			} else {
				this.select( event );
			}
		}
	},

	refresh: function() {
		var menus,
			icon = this.options.icons.submenu,
			submenus = this.element.find( this.options.menus );

		// Initialize nested menus
		submenus.filter( ":not(.ui-menu)" )
			.addClass( "ui-menu ui-widget ui-widget-content ui-corner-all" )
			.hide()
			.attr({
				role: this.options.role,
				"aria-hidden": "true",
				"aria-expanded": "false"
			})
			.each(function() {
				var menu = $( this ),
					item = menu.prev( "a" ),
					submenuCarat = $( "<span>" )
						.addClass( "ui-menu-icon ui-icon " + icon )
						.data( "ui-menu-submenu-carat", true );

				item
					.attr( "aria-haspopup", "true" )
					.prepend( submenuCarat );
				menu.attr( "aria-labelledby", item.attr( "id" ) );
			});

		menus = submenus.add( this.element );

		// Don't refresh list items that are already adapted
		menus.children( ":not(.ui-menu-item):has(a)" )
			.addClass( "ui-menu-item" )
			.attr( "role", "presentation" )
			.children( "a" )
				.uniqueId()
				.addClass( "ui-corner-all" )
				.attr({
					tabIndex: -1,
					role: this._itemRole()
				});

		// Initialize unlinked menu-items containing spaces and/or dashes only as dividers
		menus.children( ":not(.ui-menu-item)" ).each(function() {
			var item = $( this );
			// hyphen, em dash, en dash
			if ( !/[^\-\u2014\u2013\s]/.test( item.text() ) ) {
				item.addClass( "ui-widget-content ui-menu-divider" );
			}
		});

		// Add aria-disabled attribute to any disabled menu item
		menus.children( ".ui-state-disabled" ).attr( "aria-disabled", "true" );

		// If the active item has been removed, blur the menu
		if ( this.active && !$.contains( this.element[ 0 ], this.active[ 0 ] ) ) {
			this.blur();
		}
	},

	_itemRole: function() {
		return {
			menu: "menuitem",
			listbox: "option"
		}[ this.options.role ];
	},

	_setOption: function( key, value ) {
		if ( key === "icons" ) {
			this.element.find( ".ui-menu-icon" )
				.removeClass( this.options.icons.submenu )
				.addClass( value.submenu );
		}
		this._super( key, value );
	},

	focus: function( event, item ) {
		var nested, focused;
		this.blur( event, event && event.type === "focus" );

		this._scrollIntoView( item );

		this.active = item.first();
		focused = this.active.children( "a" ).addClass( "ui-state-focus" );
		// Only update aria-activedescendant if there's a role
		// otherwise we assume focus is managed elsewhere
		if ( this.options.role ) {
			this.element.attr( "aria-activedescendant", focused.attr( "id" ) );
		}

		// Highlight active parent menu item, if any
		this.active
			.parent()
			.closest( ".ui-menu-item" )
			.children( "a:first" )
			.addClass( "ui-state-active" );

		if ( event && event.type === "keydown" ) {
			this._close();
		} else {
			this.timer = this._delay(function() {
				this._close();
			}, this.delay );
		}

		nested = item.children( ".ui-menu" );
		if ( nested.length && ( /^mouse/.test( event.type ) ) ) {
			this._startOpening(nested);
		}
		this.activeMenu = item.parent();

		this._trigger( "focus", event, { item: item } );
	},

	_scrollIntoView: function( item ) {
		var borderTop, paddingTop, offset, scroll, elementHeight, itemHeight;
		if ( this._hasScroll() ) {
			borderTop = parseFloat( $.css( this.activeMenu[0], "borderTopWidth" ) ) || 0;
			paddingTop = parseFloat( $.css( this.activeMenu[0], "paddingTop" ) ) || 0;
			offset = item.offset().top - this.activeMenu.offset().top - borderTop - paddingTop;
			scroll = this.activeMenu.scrollTop();
			elementHeight = this.activeMenu.height();
			itemHeight = item.height();

			if ( offset < 0 ) {
				this.activeMenu.scrollTop( scroll + offset );
			} else if ( offset + itemHeight > elementHeight ) {
				this.activeMenu.scrollTop( scroll + offset - elementHeight + itemHeight );
			}
		}
	},

	blur: function( event, fromFocus ) {
		if ( !fromFocus ) {
			clearTimeout( this.timer );
		}

		if ( !this.active ) {
			return;
		}

		this.active.children( "a" ).removeClass( "ui-state-focus" );
		this.active = null;

		this._trigger( "blur", event, { item: this.active } );
	},

	_startOpening: function( submenu ) {
		clearTimeout( this.timer );

		// Don't open if already open fixes a Firefox bug that caused a .5 pixel
		// shift in the submenu position when mousing over the carat icon
		if ( submenu.attr( "aria-hidden" ) !== "true" ) {
			return;
		}

		this.timer = this._delay(function() {
			this._close();
			this._open( submenu );
		}, this.delay );
	},

	_open: function( submenu ) {
		var position = $.extend({
			of: this.active
		}, this.options.position );

		clearTimeout( this.timer );
		this.element.find( ".ui-menu" ).not( submenu.parents( ".ui-menu" ) )
			.hide()
			.attr( "aria-hidden", "true" );

		submenu
			.show()
			.removeAttr( "aria-hidden" )
			.attr( "aria-expanded", "true" )
			.position( position );
	},

	collapseAll: function( event, all ) {
		clearTimeout( this.timer );
		this.timer = this._delay(function() {
			// If we were passed an event, look for the submenu that contains the event
			var currentMenu = all ? this.element :
				$( event && event.target ).closest( this.element.find( ".ui-menu" ) );

			// If we found no valid submenu ancestor, use the main menu to close all sub menus anyway
			if ( !currentMenu.length ) {
				currentMenu = this.element;
			}

			this._close( currentMenu );

			this.blur( event );
			this.activeMenu = currentMenu;
		}, this.delay );
	},

	// With no arguments, closes the currently active menu - if nothing is active
	// it closes all menus.  If passed an argument, it will search for menus BELOW
	_close: function( startMenu ) {
		if ( !startMenu ) {
			startMenu = this.active ? this.active.parent() : this.element;
		}

		startMenu
			.find( ".ui-menu" )
				.hide()
				.attr( "aria-hidden", "true" )
				.attr( "aria-expanded", "false" )
			.end()
			.find( "a.ui-state-active" )
				.removeClass( "ui-state-active" );
	},

	collapse: function( event ) {
		var newItem = this.active &&
			this.active.parent().closest( ".ui-menu-item", this.element );
		if ( newItem && newItem.length ) {
			this._close();
			this.focus( event, newItem );
		}
	},

	expand: function( event ) {
		var newItem = this.active &&
			this.active
				.children( ".ui-menu " )
				.children( ".ui-menu-item" )
				.first();

		if ( newItem && newItem.length ) {
			this._open( newItem.parent() );

			// Delay so Firefox will not hide activedescendant change in expanding submenu from AT
			this._delay(function() {
				this.focus( event, newItem );
			});
		}
	},

	next: function( event ) {
		this._move( "next", "first", event );
	},

	previous: function( event ) {
		this._move( "prev", "last", event );
	},

	isFirstItem: function() {
		return this.active && !this.active.prevAll( ".ui-menu-item" ).length;
	},

	isLastItem: function() {
		return this.active && !this.active.nextAll( ".ui-menu-item" ).length;
	},

	_move: function( direction, filter, event ) {
		var next;
		if ( this.active ) {
			if ( direction === "first" || direction === "last" ) {
				next = this.active
					[ direction === "first" ? "prevAll" : "nextAll" ]( ".ui-menu-item" )
					.eq( -1 );
			} else {
				next = this.active
					[ direction + "All" ]( ".ui-menu-item" )
					.eq( 0 );
			}
		}
		if ( !next || !next.length || !this.active ) {
			next = this.activeMenu.children( ".ui-menu-item" )[ filter ]();
		}

		this.focus( event, next );
	},

	nextPage: function( event ) {
		var item, base, height;

		if ( !this.active ) {
			this.next( event );
			return;
		}
		if ( this.isLastItem() ) {
			return;
		}
		if ( this._hasScroll() ) {
			base = this.active.offset().top;
			height = this.element.height();
			this.active.nextAll( ".ui-menu-item" ).each(function() {
				item = $( this );
				return item.offset().top - base - height < 0;
			});

			this.focus( event, item );
		} else {
			this.focus( event, this.activeMenu.children( ".ui-menu-item" )
				[ !this.active ? "first" : "last" ]() );
		}
	},

	previousPage: function( event ) {
		var item, base, height;
		if ( !this.active ) {
			this.next( event );
			return;
		}
		if ( this.isFirstItem() ) {
			return;
		}
		if ( this._hasScroll() ) {
			base = this.active.offset().top;
			height = this.element.height();
			this.active.prevAll( ".ui-menu-item" ).each(function() {
				item = $( this );
				return item.offset().top - base + height > 0;
			});

			this.focus( event, item );
		} else {
			this.focus( event, this.activeMenu.children( ".ui-menu-item" ).first() );
		}
	},

	_hasScroll: function() {
		return this.element.outerHeight() < this.element.prop( "scrollHeight" );
	},

	select: function( event ) {
		// TODO: It should never be possible to not have an active item at this
		// point, but the tests don't trigger mouseenter before click.
		this.active = this.active || $( event.target ).closest( ".ui-menu-item" );
		var ui = { item: this.active };
		if ( !this.active.has( ".ui-menu" ).length ) {
			this.collapseAll( event, true );
		}
		this._trigger( "select", event, ui );
	}
});

}( jQuery ));

}; 

exports(); 
module.resolveWith(exports); 

}); 
// module body: end

}; 
// module factory: end

KTVendors.module("ui/menu", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 

/*!
 * jQuery UI Position 1.10.4pre
 * http://jqueryui.com
 *
 * Copyright 2013 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/position/
 */
(function( $, undefined ) {

$.ui = $.ui || {};

var cachedScrollbarWidth,
	max = Math.max,
	abs = Math.abs,
	round = Math.round,
	rhorizontal = /left|center|right/,
	rvertical = /top|center|bottom/,
	roffset = /[\+\-]\d+(\.[\d]+)?%?/,
	rposition = /^\w+/,
	rpercent = /%$/,
	_position = $.fn.position;

function getOffsets( offsets, width, height ) {
	return [
		parseFloat( offsets[ 0 ] ) * ( rpercent.test( offsets[ 0 ] ) ? width / 100 : 1 ),
		parseFloat( offsets[ 1 ] ) * ( rpercent.test( offsets[ 1 ] ) ? height / 100 : 1 )
	];
}

function parseCss( element, property ) {
	return parseInt( $.css( element, property ), 10 ) || 0;
}

function getDimensions( elem ) {
	var raw = elem[0];
	if ( raw.nodeType === 9 ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: 0, left: 0 }
		};
	}
	if ( $.isWindow( raw ) ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: elem.scrollTop(), left: elem.scrollLeft() }
		};
	}
	if ( raw.preventDefault ) {
		return {
			width: 0,
			height: 0,
			offset: { top: raw.pageY, left: raw.pageX }
		};
	}
	return {
		width: elem.outerWidth(),
		height: elem.outerHeight(),
		offset: elem.offset()
	};
}

$.position = {
	scrollbarWidth: function() {
		if ( cachedScrollbarWidth !== undefined ) {
			return cachedScrollbarWidth;
		}
		var w1, w2,
			div = $( "<div style='display:block;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>" ),
			innerDiv = div.children()[0];

		$( "body" ).append( div );
		w1 = innerDiv.offsetWidth;
		div.css( "overflow", "scroll" );

		w2 = innerDiv.offsetWidth;

		if ( w1 === w2 ) {
			w2 = div[0].clientWidth;
		}

		div.remove();

		return (cachedScrollbarWidth = w1 - w2);
	},
	getScrollInfo: function( within ) {
		var overflowX = within.isWindow ? "" : within.element.css( "overflow-x" ),
			overflowY = within.isWindow ? "" : within.element.css( "overflow-y" ),
			hasOverflowX = overflowX === "scroll" ||
				( overflowX === "auto" && within.width < within.element[0].scrollWidth ),
			hasOverflowY = overflowY === "scroll" ||
				( overflowY === "auto" && within.height < within.element[0].scrollHeight );
		return {
			width: hasOverflowY ? $.position.scrollbarWidth() : 0,
			height: hasOverflowX ? $.position.scrollbarWidth() : 0
		};
	},
	getWithinInfo: function( element ) {
		var withinElement = $( element || window ),
			isWindow = $.isWindow( withinElement[0] );
		return {
			element: withinElement,
			isWindow: isWindow,
			offset: withinElement.offset() || { left: 0, top: 0 },
			scrollLeft: withinElement.scrollLeft(),
			scrollTop: withinElement.scrollTop(),
			width: isWindow ? withinElement.width() : withinElement.outerWidth(),
			height: isWindow ? withinElement.height() : withinElement.outerHeight()
		};
	}
};

$.fn.position = function( options ) {
	if ( !options || !options.of ) {
		return _position.apply( this, arguments );
	}

	// make a copy, we don't want to modify arguments
	options = $.extend( {}, options );

	var atOffset, targetWidth, targetHeight, targetOffset, basePosition, dimensions,
		target = $( options.of ),
		within = $.position.getWithinInfo( options.within ),
		scrollInfo = $.position.getScrollInfo( within ),
		collision = ( options.collision || "flip" ).split( " " ),
		offsets = {};

	dimensions = getDimensions( target );
	if ( target[0].preventDefault ) {
		// force left top to allow flipping
		options.at = "left top";
	}
	targetWidth = dimensions.width;
	targetHeight = dimensions.height;
	targetOffset = dimensions.offset;
	// clone to reuse original targetOffset later
	basePosition = $.extend( {}, targetOffset );

	// force my and at to have valid horizontal and vertical positions
	// if a value is missing or invalid, it will be converted to center
	$.each( [ "my", "at" ], function() {
		var pos = ( options[ this ] || "" ).split( " " ),
			horizontalOffset,
			verticalOffset;

		if ( pos.length === 1) {
			pos = rhorizontal.test( pos[ 0 ] ) ?
				pos.concat( [ "center" ] ) :
				rvertical.test( pos[ 0 ] ) ?
					[ "center" ].concat( pos ) :
					[ "center", "center" ];
		}
		pos[ 0 ] = rhorizontal.test( pos[ 0 ] ) ? pos[ 0 ] : "center";
		pos[ 1 ] = rvertical.test( pos[ 1 ] ) ? pos[ 1 ] : "center";

		// calculate offsets
		horizontalOffset = roffset.exec( pos[ 0 ] );
		verticalOffset = roffset.exec( pos[ 1 ] );
		offsets[ this ] = [
			horizontalOffset ? horizontalOffset[ 0 ] : 0,
			verticalOffset ? verticalOffset[ 0 ] : 0
		];

		// reduce to just the positions without the offsets
		options[ this ] = [
			rposition.exec( pos[ 0 ] )[ 0 ],
			rposition.exec( pos[ 1 ] )[ 0 ]
		];
	});

	// normalize collision option
	if ( collision.length === 1 ) {
		collision[ 1 ] = collision[ 0 ];
	}

	if ( options.at[ 0 ] === "right" ) {
		basePosition.left += targetWidth;
	} else if ( options.at[ 0 ] === "center" ) {
		basePosition.left += targetWidth / 2;
	}

	if ( options.at[ 1 ] === "bottom" ) {
		basePosition.top += targetHeight;
	} else if ( options.at[ 1 ] === "center" ) {
		basePosition.top += targetHeight / 2;
	}

	atOffset = getOffsets( offsets.at, targetWidth, targetHeight );
	basePosition.left += atOffset[ 0 ];
	basePosition.top += atOffset[ 1 ];

	return this.each(function() {
		var collisionPosition, using,
			elem = $( this ),
			elemWidth = elem.outerWidth(),
			elemHeight = elem.outerHeight(),
			marginLeft = parseCss( this, "marginLeft" ),
			marginTop = parseCss( this, "marginTop" ),
			collisionWidth = elemWidth + marginLeft + parseCss( this, "marginRight" ) + scrollInfo.width,
			collisionHeight = elemHeight + marginTop + parseCss( this, "marginBottom" ) + scrollInfo.height,
			position = $.extend( {}, basePosition ),
			myOffset = getOffsets( offsets.my, elem.outerWidth(), elem.outerHeight() );

		if ( options.my[ 0 ] === "right" ) {
			position.left -= elemWidth;
		} else if ( options.my[ 0 ] === "center" ) {
			position.left -= elemWidth / 2;
		}

		if ( options.my[ 1 ] === "bottom" ) {
			position.top -= elemHeight;
		} else if ( options.my[ 1 ] === "center" ) {
			position.top -= elemHeight / 2;
		}

		position.left += myOffset[ 0 ];
		position.top += myOffset[ 1 ];

		// if the browser doesn't support fractions, then round for consistent results
		if ( !$.support.offsetFractions ) {
			position.left = round( position.left );
			position.top = round( position.top );
		}

		collisionPosition = {
			marginLeft: marginLeft,
			marginTop: marginTop
		};

		$.each( [ "left", "top" ], function( i, dir ) {
			if ( $.ui.position[ collision[ i ] ] ) {
				$.ui.position[ collision[ i ] ][ dir ]( position, {
					targetWidth: targetWidth,
					targetHeight: targetHeight,
					elemWidth: elemWidth,
					elemHeight: elemHeight,
					collisionPosition: collisionPosition,
					collisionWidth: collisionWidth,
					collisionHeight: collisionHeight,
					offset: [ atOffset[ 0 ] + myOffset[ 0 ], atOffset [ 1 ] + myOffset[ 1 ] ],
					my: options.my,
					at: options.at,
					within: within,
					elem : elem
				});
			}
		});

		if ( options.using ) {
			// adds feedback as second argument to using callback, if present
			using = function( props ) {
				var left = targetOffset.left - position.left,
					right = left + targetWidth - elemWidth,
					top = targetOffset.top - position.top,
					bottom = top + targetHeight - elemHeight,
					feedback = {
						target: {
							element: target,
							left: targetOffset.left,
							top: targetOffset.top,
							width: targetWidth,
							height: targetHeight
						},
						element: {
							element: elem,
							left: position.left,
							top: position.top,
							width: elemWidth,
							height: elemHeight
						},
						horizontal: right < 0 ? "left" : left > 0 ? "right" : "center",
						vertical: bottom < 0 ? "top" : top > 0 ? "bottom" : "middle"
					};
				if ( targetWidth < elemWidth && abs( left + right ) < targetWidth ) {
					feedback.horizontal = "center";
				}
				if ( targetHeight < elemHeight && abs( top + bottom ) < targetHeight ) {
					feedback.vertical = "middle";
				}
				if ( max( abs( left ), abs( right ) ) > max( abs( top ), abs( bottom ) ) ) {
					feedback.important = "horizontal";
				} else {
					feedback.important = "vertical";
				}
				options.using.call( this, props, feedback );
			};
		}

		elem.offset( $.extend( position, { using: using } ) );
	});
};

$.ui.position = {
	fit: {
		left: function( position, data ) {
			var within = data.within,
				withinOffset = within.isWindow ? within.scrollLeft : within.offset.left,
				outerWidth = within.width,
				collisionPosLeft = position.left - data.collisionPosition.marginLeft,
				overLeft = withinOffset - collisionPosLeft,
				overRight = collisionPosLeft + data.collisionWidth - outerWidth - withinOffset,
				newOverRight;

			// element is wider than within
			if ( data.collisionWidth > outerWidth ) {
				// element is initially over the left side of within
				if ( overLeft > 0 && overRight <= 0 ) {
					newOverRight = position.left + overLeft + data.collisionWidth - outerWidth - withinOffset;
					position.left += overLeft - newOverRight;
				// element is initially over right side of within
				} else if ( overRight > 0 && overLeft <= 0 ) {
					position.left = withinOffset;
				// element is initially over both left and right sides of within
				} else {
					if ( overLeft > overRight ) {
						position.left = withinOffset + outerWidth - data.collisionWidth;
					} else {
						position.left = withinOffset;
					}
				}
			// too far left -> align with left edge
			} else if ( overLeft > 0 ) {
				position.left += overLeft;
			// too far right -> align with right edge
			} else if ( overRight > 0 ) {
				position.left -= overRight;
			// adjust based on position and margin
			} else {
				position.left = max( position.left - collisionPosLeft, position.left );
			}
		},
		top: function( position, data ) {
			var within = data.within,
				withinOffset = within.isWindow ? within.scrollTop : within.offset.top,
				outerHeight = data.within.height,
				collisionPosTop = position.top - data.collisionPosition.marginTop,
				overTop = withinOffset - collisionPosTop,
				overBottom = collisionPosTop + data.collisionHeight - outerHeight - withinOffset,
				newOverBottom;

			// element is taller than within
			if ( data.collisionHeight > outerHeight ) {
				// element is initially over the top of within
				if ( overTop > 0 && overBottom <= 0 ) {
					newOverBottom = position.top + overTop + data.collisionHeight - outerHeight - withinOffset;
					position.top += overTop - newOverBottom;
				// element is initially over bottom of within
				} else if ( overBottom > 0 && overTop <= 0 ) {
					position.top = withinOffset;
				// element is initially over both top and bottom of within
				} else {
					if ( overTop > overBottom ) {
						position.top = withinOffset + outerHeight - data.collisionHeight;
					} else {
						position.top = withinOffset;
					}
				}
			// too far up -> align with top
			} else if ( overTop > 0 ) {
				position.top += overTop;
			// too far down -> align with bottom edge
			} else if ( overBottom > 0 ) {
				position.top -= overBottom;
			// adjust based on position and margin
			} else {
				position.top = max( position.top - collisionPosTop, position.top );
			}
		}
	},
	flip: {
		left: function( position, data ) {
			var within = data.within,
				withinOffset = within.offset.left + within.scrollLeft,
				outerWidth = within.width,
				offsetLeft = within.isWindow ? within.scrollLeft : within.offset.left,
				collisionPosLeft = position.left - data.collisionPosition.marginLeft,
				overLeft = collisionPosLeft - offsetLeft,
				overRight = collisionPosLeft + data.collisionWidth - outerWidth - offsetLeft,
				myOffset = data.my[ 0 ] === "left" ?
					-data.elemWidth :
					data.my[ 0 ] === "right" ?
						data.elemWidth :
						0,
				atOffset = data.at[ 0 ] === "left" ?
					data.targetWidth :
					data.at[ 0 ] === "right" ?
						-data.targetWidth :
						0,
				offset = -2 * data.offset[ 0 ],
				newOverRight,
				newOverLeft;

			if ( overLeft < 0 ) {
				newOverRight = position.left + myOffset + atOffset + offset + data.collisionWidth - outerWidth - withinOffset;
				if ( newOverRight < 0 || newOverRight < abs( overLeft ) ) {
					position.left += myOffset + atOffset + offset;
				}
			}
			else if ( overRight > 0 ) {
				newOverLeft = position.left - data.collisionPosition.marginLeft + myOffset + atOffset + offset - offsetLeft;
				if ( newOverLeft > 0 || abs( newOverLeft ) < overRight ) {
					position.left += myOffset + atOffset + offset;
				}
			}
		},
		top: function( position, data ) {
			var within = data.within,
				withinOffset = within.offset.top + within.scrollTop,
				outerHeight = within.height,
				offsetTop = within.isWindow ? within.scrollTop : within.offset.top,
				collisionPosTop = position.top - data.collisionPosition.marginTop,
				overTop = collisionPosTop - offsetTop,
				overBottom = collisionPosTop + data.collisionHeight - outerHeight - offsetTop,
				top = data.my[ 1 ] === "top",
				myOffset = top ?
					-data.elemHeight :
					data.my[ 1 ] === "bottom" ?
						data.elemHeight :
						0,
				atOffset = data.at[ 1 ] === "top" ?
					data.targetHeight :
					data.at[ 1 ] === "bottom" ?
						-data.targetHeight :
						0,
				offset = -2 * data.offset[ 1 ],
				newOverTop,
				newOverBottom;
			if ( overTop < 0 ) {
				newOverBottom = position.top + myOffset + atOffset + offset + data.collisionHeight - outerHeight - withinOffset;
				if ( ( position.top + myOffset + atOffset + offset) > overTop && ( newOverBottom < 0 || newOverBottom < abs( overTop ) ) ) {
					position.top += myOffset + atOffset + offset;
				}
			}
			else if ( overBottom > 0 ) {
				newOverTop = position.top -  data.collisionPosition.marginTop + myOffset + atOffset + offset - offsetTop;
				if ( ( position.top + myOffset + atOffset + offset) > overBottom && ( newOverTop > 0 || abs( newOverTop ) < overBottom ) ) {
					position.top += myOffset + atOffset + offset;
				}
			}
		}
	},
	flipfit: {
		left: function() {
			$.ui.position.flip.left.apply( this, arguments );
			$.ui.position.fit.left.apply( this, arguments );
		},
		top: function() {
			$.ui.position.flip.top.apply( this, arguments );
			$.ui.position.fit.top.apply( this, arguments );
		}
	}
};

// fraction support test
(function () {
	var testElement, testElementParent, testElementStyle, offsetLeft, i,
		body = document.getElementsByTagName( "body" )[ 0 ],
		div = document.createElement( "div" );

	//Create a "fake body" for testing based on method used in jQuery.support
	testElement = document.createElement( body ? "div" : "body" );
	testElementStyle = {
		visibility: "hidden",
		width: 0,
		height: 0,
		border: 0,
		margin: 0,
		background: "none"
	};
	if ( body ) {
		$.extend( testElementStyle, {
			position: "absolute",
			left: "-1000px",
			top: "-1000px"
		});
	}
	for ( i in testElementStyle ) {
		testElement.style[ i ] = testElementStyle[ i ];
	}
	testElement.appendChild( div );
	testElementParent = body || document.documentElement;
	testElementParent.insertBefore( testElement, testElementParent.firstChild );

	div.style.cssText = "position: absolute; left: 10.7432222px;";

	offsetLeft = $( div ).offset().left;
	$.support.offsetFractions = offsetLeft > 10 && offsetLeft < 11;

	testElement.innerHTML = "";
	testElementParent.removeChild( testElement );
})();

}( jQuery ) );

}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("ui/position", moduleFactory);

}());			(function(){

// module factory: start

var moduleFactory = function($) {
// module body: start

var module = this; 
var jQuery = $; 
var exports = function() { 

/*!
 * jQuery UI Widget 1.10.4pre
 * http://jqueryui.com
 *
 * Copyright 2013 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * http://api.jqueryui.com/jQuery.widget/
 */
(function( $, undefined ) {

var uuid = 0,
	slice = Array.prototype.slice,
	_cleanData = $.cleanData;
$.cleanData = function( elems ) {
	for ( var i = 0, elem; (elem = elems[i]) != null; i++ ) {
		try {
			$( elem ).triggerHandler( "remove" );
		// http://bugs.jquery.com/ticket/8235
		} catch( e ) {}
	}
	_cleanData( elems );
};

$.widget = function( name, base, prototype ) {
	var fullName, existingConstructor, constructor, basePrototype,
		// proxiedPrototype allows the provided prototype to remain unmodified
		// so that it can be used as a mixin for multiple widgets (#8876)
		proxiedPrototype = {},
		namespace = name.split( "." )[ 0 ];

	name = name.split( "." )[ 1 ];
	fullName = namespace + "-" + name;

	if ( !prototype ) {
		prototype = base;
		base = $.Widget;
	}

	// create selector for plugin
	$.expr[ ":" ][ fullName.toLowerCase() ] = function( elem ) {
		return !!$.data( elem, fullName );
	};

	$[ namespace ] = $[ namespace ] || {};
	existingConstructor = $[ namespace ][ name ];
	constructor = $[ namespace ][ name ] = function( options, element ) {
		// allow instantiation without "new" keyword
		if ( !this._createWidget ) {
			return new constructor( options, element );
		}

		// allow instantiation without initializing for simple inheritance
		// must use "new" keyword (the code above always passes args)
		if ( arguments.length ) {
			this._createWidget( options, element );
		}
	};
	// extend with the existing constructor to carry over any static properties
	$.extend( constructor, existingConstructor, {
		version: prototype.version,
		// copy the object used to create the prototype in case we need to
		// redefine the widget later
		_proto: $.extend( {}, prototype ),
		// track widgets that inherit from this widget in case this widget is
		// redefined after a widget inherits from it
		_childConstructors: []
	});

	basePrototype = new base();
	// we need to make the options hash a property directly on the new instance
	// otherwise we'll modify the options hash on the prototype that we're
	// inheriting from
	basePrototype.options = $.widget.extend( {}, basePrototype.options );
	$.each( prototype, function( prop, value ) {
		if ( !$.isFunction( value ) ) {
			proxiedPrototype[ prop ] = value;
			return;
		}
		proxiedPrototype[ prop ] = (function() {
			var _super = function() {
					return base.prototype[ prop ].apply( this, arguments );
				},
				_superApply = function( args ) {
					return base.prototype[ prop ].apply( this, args );
				};
			return function() {
				var __super = this._super,
					__superApply = this._superApply,
					returnValue;

				this._super = _super;
				this._superApply = _superApply;

				returnValue = value.apply( this, arguments );

				this._super = __super;
				this._superApply = __superApply;

				return returnValue;
			};
		})();
	});
	constructor.prototype = $.widget.extend( basePrototype, {
		// TODO: remove support for widgetEventPrefix
		// always use the name + a colon as the prefix, e.g., draggable:start
		// don't prefix for widgets that aren't DOM-based
		widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix : name
	}, proxiedPrototype, {
		constructor: constructor,
		namespace: namespace,
		widgetName: name,
		widgetFullName: fullName
	});

	// If this widget is being redefined then we need to find all widgets that
	// are inheriting from it and redefine all of them so that they inherit from
	// the new version of this widget. We're essentially trying to replace one
	// level in the prototype chain.
	if ( existingConstructor ) {
		$.each( existingConstructor._childConstructors, function( i, child ) {
			var childPrototype = child.prototype;

			// redefine the child widget using the same prototype that was
			// originally used, but inherit from the new version of the base
			$.widget( childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto );
		});
		// remove the list of existing child constructors from the old constructor
		// so the old child constructors can be garbage collected
		delete existingConstructor._childConstructors;
	} else {
		base._childConstructors.push( constructor );
	}

	$.widget.bridge( name, constructor );
};

$.widget.extend = function( target ) {
	var input = slice.call( arguments, 1 ),
		inputIndex = 0,
		inputLength = input.length,
		key,
		value;
	for ( ; inputIndex < inputLength; inputIndex++ ) {
		for ( key in input[ inputIndex ] ) {
			value = input[ inputIndex ][ key ];
			if ( input[ inputIndex ].hasOwnProperty( key ) && value !== undefined ) {
				// Clone objects
				if ( $.isPlainObject( value ) ) {
					target[ key ] = $.isPlainObject( target[ key ] ) ?
						$.widget.extend( {}, target[ key ], value ) :
						// Don't extend strings, arrays, etc. with objects
						$.widget.extend( {}, value );
				// Copy everything else by reference
				} else {
					target[ key ] = value;
				}
			}
		}
	}
	return target;
};

$.widget.bridge = function( name, object ) {
	var fullName = object.prototype.widgetFullName || name;
	$.fn[ name ] = function( options ) {
		var isMethodCall = typeof options === "string",
			args = slice.call( arguments, 1 ),
			returnValue = this;

		// allow multiple hashes to be passed on init
		options = !isMethodCall && args.length ?
			$.widget.extend.apply( null, [ options ].concat(args) ) :
			options;

		if ( isMethodCall ) {
			this.each(function() {
				var methodValue,
					instance = $.data( this, fullName );
				if ( !instance ) {
					return $.error( "cannot call methods on " + name + " prior to initialization; " +
						"attempted to call method '" + options + "'" );
				}
				if ( !$.isFunction( instance[options] ) || options.charAt( 0 ) === "_" ) {
					return $.error( "no such method '" + options + "' for " + name + " widget instance" );
				}
				methodValue = instance[ options ].apply( instance, args );
				if ( methodValue !== instance && methodValue !== undefined ) {
					returnValue = methodValue && methodValue.jquery ?
						returnValue.pushStack( methodValue.get() ) :
						methodValue;
					return false;
				}
			});
		} else {
			this.each(function() {
				var instance = $.data( this, fullName );
				if ( instance ) {
					instance.option( options || {} )._init();
				} else {
					$.data( this, fullName, new object( options, this ) );
				}
			});
		}

		return returnValue;
	};
};

$.Widget = function( /* options, element */ ) {};
$.Widget._childConstructors = [];

$.Widget.prototype = {
	widgetName: "widget",
	widgetEventPrefix: "",
	defaultElement: "<div>",
	options: {
		disabled: false,

		// callbacks
		create: null
	},
	_createWidget: function( options, element ) {
		element = $( element || this.defaultElement || this )[ 0 ];
		this.element = $( element );
		this.uuid = uuid++;
		this.eventNamespace = "." + this.widgetName + this.uuid;
		this.options = $.widget.extend( {},
			this.options,
			this._getCreateOptions(),
			options );

		this.bindings = $();
		this.hoverable = $();
		this.focusable = $();

		if ( element !== this ) {
			$.data( element, this.widgetFullName, this );
			this._on( true, this.element, {
				remove: function( event ) {
					if ( event.target === element ) {
						this.destroy();
					}
				}
			});
			this.document = $( element.style ?
				// element within the document
				element.ownerDocument :
				// element is window or document
				element.document || element );
			this.window = $( this.document[0].defaultView || this.document[0].parentWindow );
		}

		this._create();
		this._trigger( "create", null, this._getCreateEventData() );
		this._init();
	},
	_getCreateOptions: $.noop,
	_getCreateEventData: $.noop,
	_create: $.noop,
	_init: $.noop,

	destroy: function() {
		this._destroy();
		// we can probably remove the unbind calls in 2.0
		// all event bindings should go through this._on()
		this.element
			.unbind( this.eventNamespace )
			// 1.9 BC for #7810
			// TODO remove dual storage
			.removeData( this.widgetName )
			.removeData( this.widgetFullName )
			// support: jquery <1.6.3
			// http://bugs.jquery.com/ticket/9413
			.removeData( $.camelCase( this.widgetFullName ) );
		this.widget()
			.unbind( this.eventNamespace )
			.removeAttr( "aria-disabled" )
			.removeClass(
				this.widgetFullName + "-disabled " +
				"ui-state-disabled" );

		// clean up events and states
		this.bindings.unbind( this.eventNamespace );
		this.hoverable.removeClass( "ui-state-hover" );
		this.focusable.removeClass( "ui-state-focus" );
	},
	_destroy: $.noop,

	widget: function() {
		return this.element;
	},

	option: function( key, value ) {
		var options = key,
			parts,
			curOption,
			i;

		if ( arguments.length === 0 ) {
			// don't return a reference to the internal hash
			return $.widget.extend( {}, this.options );
		}

		if ( typeof key === "string" ) {
			// handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
			options = {};
			parts = key.split( "." );
			key = parts.shift();
			if ( parts.length ) {
				curOption = options[ key ] = $.widget.extend( {}, this.options[ key ] );
				for ( i = 0; i < parts.length - 1; i++ ) {
					curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
					curOption = curOption[ parts[ i ] ];
				}
				key = parts.pop();
				if ( value === undefined ) {
					return curOption[ key ] === undefined ? null : curOption[ key ];
				}
				curOption[ key ] = value;
			} else {
				if ( value === undefined ) {
					return this.options[ key ] === undefined ? null : this.options[ key ];
				}
				options[ key ] = value;
			}
		}

		this._setOptions( options );

		return this;
	},
	_setOptions: function( options ) {
		var key;

		for ( key in options ) {
			this._setOption( key, options[ key ] );
		}

		return this;
	},
	_setOption: function( key, value ) {
		this.options[ key ] = value;

		if ( key === "disabled" ) {
			this.widget()
				.toggleClass( this.widgetFullName + "-disabled ui-state-disabled", !!value )
				.attr( "aria-disabled", value );
			this.hoverable.removeClass( "ui-state-hover" );
			this.focusable.removeClass( "ui-state-focus" );
		}

		return this;
	},

	enable: function() {
		return this._setOption( "disabled", false );
	},
	disable: function() {
		return this._setOption( "disabled", true );
	},

	_on: function( suppressDisabledCheck, element, handlers ) {
		var delegateElement,
			instance = this;

		// no suppressDisabledCheck flag, shuffle arguments
		if ( typeof suppressDisabledCheck !== "boolean" ) {
			handlers = element;
			element = suppressDisabledCheck;
			suppressDisabledCheck = false;
		}

		// no element argument, shuffle and use this.element
		if ( !handlers ) {
			handlers = element;
			element = this.element;
			delegateElement = this.widget();
		} else {
			// accept selectors, DOM elements
			element = delegateElement = $( element );
			this.bindings = this.bindings.add( element );
		}

		$.each( handlers, function( event, handler ) {
			function handlerProxy() {
				// allow widgets to customize the disabled handling
				// - disabled as an array instead of boolean
				// - disabled class as method for disabling individual parts
				if ( !suppressDisabledCheck &&
						( instance.options.disabled === true ||
							$( this ).hasClass( "ui-state-disabled" ) ) ) {
					return;
				}
				return ( typeof handler === "string" ? instance[ handler ] : handler )
					.apply( instance, arguments );
			}

			// copy the guid so direct unbinding works
			if ( typeof handler !== "string" ) {
				handlerProxy.guid = handler.guid =
					handler.guid || handlerProxy.guid || $.guid++;
			}

			var match = event.match( /^(\w+)\s*(.*)$/ ),
				eventName = match[1] + instance.eventNamespace,
				selector = match[2];
			if ( selector ) {
				delegateElement.delegate( selector, eventName, handlerProxy );
			} else {
				element.bind( eventName, handlerProxy );
			}
		});
	},

	_off: function( element, eventName ) {
		eventName = (eventName || "").split( " " ).join( this.eventNamespace + " " ) + this.eventNamespace;
		element.unbind( eventName ).undelegate( eventName );
	},

	_delay: function( handler, delay ) {
		function handlerProxy() {
			return ( typeof handler === "string" ? instance[ handler ] : handler )
				.apply( instance, arguments );
		}
		var instance = this;
		return setTimeout( handlerProxy, delay || 0 );
	},

	_hoverable: function( element ) {
		this.hoverable = this.hoverable.add( element );
		this._on( element, {
			mouseenter: function( event ) {
				$( event.currentTarget ).addClass( "ui-state-hover" );
			},
			mouseleave: function( event ) {
				$( event.currentTarget ).removeClass( "ui-state-hover" );
			}
		});
	},

	_focusable: function( element ) {
		this.focusable = this.focusable.add( element );
		this._on( element, {
			focusin: function( event ) {
				$( event.currentTarget ).addClass( "ui-state-focus" );
			},
			focusout: function( event ) {
				$( event.currentTarget ).removeClass( "ui-state-focus" );
			}
		});
	},

	_trigger: function( type, event, data ) {
		var prop, orig,
			callback = this.options[ type ];

		data = data || {};
		event = $.Event( event );
		event.type = ( type === this.widgetEventPrefix ?
			type :
			this.widgetEventPrefix + type ).toLowerCase();
		// the original event may come from any element
		// so we need to reset the target on the new event
		event.target = this.element[ 0 ];

		// copy original event properties over to the new event
		orig = event.originalEvent;
		if ( orig ) {
			for ( prop in orig ) {
				if ( !( prop in event ) ) {
					event[ prop ] = orig[ prop ];
				}
			}
		}

		this.element.trigger( event, data );
		return !( $.isFunction( callback ) &&
			callback.apply( this.element[0], [ event ].concat( data ) ) === false ||
			event.isDefaultPrevented() );
	}
};

$.each( { show: "fadeIn", hide: "fadeOut" }, function( method, defaultEffect ) {
	$.Widget.prototype[ "_" + method ] = function( element, options, callback ) {
		if ( typeof options === "string" ) {
			options = { effect: options };
		}
		var hasOptions,
			effectName = !options ?
				method :
				options === true || typeof options === "number" ?
					defaultEffect :
					options.effect || defaultEffect;
		options = options || {};
		if ( typeof options === "number" ) {
			options = { duration: options };
		}
		hasOptions = !$.isEmptyObject( options );
		options.complete = callback;
		if ( options.delay ) {
			element.delay( options.delay );
		}
		if ( hasOptions && $.effects && $.effects.effect[ effectName ] ) {
			element[ method ]( options );
		} else if ( effectName !== method && element[ effectName ] ) {
			element[ effectName ]( options.duration, options.easing, callback );
		} else {
			element.queue(function( next ) {
				$( this )[ method ]();
				if ( callback ) {
					callback.call( element[ 0 ] );
				}
				next();
			});
		}
	};
});

})( jQuery );

}; 

exports(); 
module.resolveWith(exports); 

// module body: end

}; 
// module factory: end

KTVendors.module("ui/widget", moduleFactory);

}());	});
// Prepare the script definitions
KTVendors.installer('Komento', 'definitions', function($) {
	$.module(["komento\/shared\/elements","komento\/site\/comments\/list","komento\/site\/comments\/wrapper","komento\/site\/common","komento\/site\/dashboard\/default","komento\/site\/dashboard\/flag.item","komento\/site\/dashboard\/item","komento\/site\/form\/attachments","komento\/site\/form\/form","komento\/site\/form\/location","komento\/site\/site","komento\/site\/vendors\/lightbox","komento\/site\/vendors\/prism"]);
});

// Prepare the contents of all the scripts
KTVendors.installer('Komento', 'scripts', function($) {
			Komento.module('shared/elements', function($){

var module = this;
var tooltipLoaded = false;

Komento.isMobile = function() {
	try {
		document.createEvent('TouchEvent');
		return true;
	} catch(e) {
		return false;
	}
}

$(document).on('change.form.toggler', '[data-toggler-checkbox]', function() {
	var checkbox = $(this);
	var checked = checkbox.is(':checked');
	var parent = checkbox.parents('[data-bs-toggler]');

	if (parent.length > 0) {

		var input = parent.find('input[type=hidden]');
		input.val(checked ? 1 : 0).trigger('change');
	}
});


// Initialize yes/no buttons.
$(document).on('click.button.data-kt-api', '[data-kt-toggle-value]', function() {

	var button = $(this);
	var siblings = button.siblings("[data-kt-toggle-value]");
	var parent = button.parents('[data-kt-toggle="radio-buttons"]');

	if (parent.hasClass('disabled')) {
		return;
	}

	// This means that this toggle value belongs to a radio button
	if (parent.length > 0) {

		// Get the current button that's clicked.
		var value = $(this).data('kt-toggle-value');

		button.addClass("active");
		siblings.removeClass("active");

		// Set the value here.
		// Have to manually trigger the change event on the input
		parent.find('input[type=hidden]').val(value).trigger('change');
		return;
	}
});

// String truncater
// Used when there is a read more of a truncated content.
var selector = '[data-kt-truncater] > [data-readmore]';

$(document)
	.on('click.kt.strings.truncater', selector, function() {
		
		var section = $(this).parent();
		var original = section.find('[data-original]');
		var text = section.find('[data-text]');

		// Hide the link
		$(this).addClass('t-hidden');

		// Show the full contents
		text.addClass('t-hidden');
		original.removeClass('t-hidden');
	});

// Tooltips
// detect if mouse is being used or not.
var mouseCount = 0;
window.onmousemove = function() {

	mouseCount++;

	addTooltip();
};

var addTooltip = $.debounce(function(){

    if (!tooltipLoaded && mouseCount > 10) {

		tooltipLoaded = true;
		mouseCount = 0;

		$(document).on('mouseover.tooltip.data-kt-api', '[data-kt-provide=tooltip]', function() {

			$(this)
				.tooltip({
					delay: {
						show: 200,
						hide: 100
					},
					animation: false,
					template: '<div id="kt" class="tooltip tooltip-kt"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
					container: 'body'
				})
				.tooltip("show");
		});
    } else {
    	mouseCount = 0;
    }
}, 500);


if (!Komento.isMobile()) {
	$(document).on('mouseover.tooltip.data-kt-api', '[data-kt-provide=tooltip]', function() {

		$(this)
			.tooltip({
				delay: {
					show: 200,
					hide: 100
				},
				animation: false,
				template: '<div id="kt" class="tooltip tooltip-kt"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
				container: 'body'
			})
			.tooltip("show");
	});
}

// Popovers
$(document).on('mouseover.popover.data-kt-api', '[data-kt-provide=popover]', function() {
	$(this)
		.popover({
			delay: {
				show: 200,
				hide: 100
			},
			animation: false,
			trigger: 'hover',
			container: 'body'
		})
		.popover("show");
});

module.resolve();

});
			Komento.module('site/comments/list', function($) {

var module = this;

Komento.require()
.library('markitup')
.script('site/common', 'site/vendors/lightbox')
.done(function($) {


Komento.Controller('Comments.List', {
	defaults: {

		// Comment
		'{item}': '[data-kt-comment-item]',

		// comments list
		// '{commentList}': '[data-kt-comments]',

		// Attachments
		'{attachmentWrapper}': '[data-kt-attachment-wrapper]',
		'{attachmentItem}': '[data-kt-attachments-item]',
		'{deleteAttachment}': '[data-kt-attachment-item-delete]',

		// Ratings
		'{ratings}': '[data-kt-ratings-item]',

		// Comment management tools
		'{delete}': '[data-kt-manage-delete]',
		'{unpublish}': '[data-kt-manage-unpublish]',
		'{pin}': '[data-kt-manage-pin]',
		'{unpin}': '[data-kt-manage-unpin]',
		'{submitSpam}': '[data-kt-submit-spam]',

		// Replying
		'{reply}': '[data-kt-reply]',

		// Editing
		'{edit}': '[data-kt-manage-edit]',
		'{editCancel}': '[data-kt-edit-cancel]',
		'{editSave}': '[data-kt-edit-save]',
		'{editForm}': '[data-kt-edit-form]',

		// Comment actions
		'{like}': '[data-kt-likes-action]',
		'{likeBrowser}': '[data-kt-likes-browser]',
		'{likeBrowserContents}': '[data-kt-likes-browser-contents]',
		'{likeWrapper}': '[data-kt-likes-wrapper]',
		'{likeCounter}': '[data-kt-likes-counter]',
		'{likeViewAll}': '[data-kt-likes-view-all]',

		// Reporting
		'{report}': '[data-kt-report]',

		// Sharing
		'{sharingWrapper}': '[data-kt-sharing]',
		'{sharing}': '[data-kt-sharing] [data-link]',

		// view all replies
		'{viewreplies}' : '[data-kt-view-reply]'
	}
}, function(self, opts) { return {

	init: function() {

		// Initialize sharing options
		opts.sharing = [];

		// Initialize ratings
		if (opts.showRatings) {
			self.initRatings();
		}

		if (opts.cleanGist) {
			self.cleanGist(self.item());
		}
	},

	initRatings: function() {

		self.ratings().each(function() {
			var item = $(this);

			self.initRating(item);
		});
	},

	initRating: function(element) {

		element.raty({
			starType: 'i',
			half: true,
			readOnly: true,
			score: element.data('score')
		});
	},

	insertRow: function(item, parentId, sorting) {
		var item = $(item);

		// Since a new item is added, we should not have an empty class
		self.element.removeClass('is-empty');

		// We also need to apply the ratings on the comment item
		var hasRatings = item.find('[data-kt-ratings-item]').length > 0;

		if (parentId != undefined && parentId != '' && parentId != '0') {

			var elementToInject = self.fintInsertRowPosition(parentId, sorting);

			console.log(elementToInject);

			if (elementToInject === false) {
				elementToInject = self.element.find("[data-id=" + parentId + "]");
				item.insertAfter(elementToInject);
			} else {

				if (sorting == 'latest') {
					item.insertBefore(elementToInject);
				} else {
					item.insertAfter(elementToInject);
				}
			}

		} else {
			if (sorting == 'latest') {
				// Prepend output to the list
				item.prependTo(self.element);
			} else {
				// Append output to the list
				item.appendTo(self.element);
			}
		}


		if (hasRatings) {
			self.initRating(item.find(self.ratings.selector));
		}

		// Reload syntax highlighter
		if (opts.prism) {
			Prism.highlightAll();
		}

		// Try to find for gist embeds
		this.initGist(item);
	},


	fintInsertRowPosition: function(parentId, sorting) {

		if (self.element.find("[data-parentid=kmt-" + parentId + "]").length > 0) {

			var lastItem = null;

			if (sorting == 'latest') {
				lastItem = self.element.find("[data-parentid=kmt-" + parentId + "]").first();
			} else {
				lastItem = self.element.find("[data-parentid=kmt-" + parentId + "]").last();
			}

			lastItemParentId = $(lastItem).data('id');

			if (self.element.find("[data-parentid=kmt-" + lastItemParentId + "]").length > 0) {
				return self.fintInsertRowPosition(lastItemParentId, sorting);
			} else {
				return lastItem;
			}

		} else {
			// lastItem = self.element.find("[data-id=" + parentId + "]");
			// return lastItem;
			return false;
		}
	},

	cleanGist: function(item) {

		var gists = item.find('script[src^="https://gist.github.com/"]');

		if (!gists.length) {
			return;
		}

		gists.each(function(idx, el) {
			$(el).remove();
		});

	},

	initGist: function(item) {

		var gists = item.find('script[src^="https://gist.github.com/"]');

		if (!gists.length) {
			return;
		}

		gists.each(function(idx, el) {

			var embed = $(el);

			// $.getJSON(embed.attr('src') + 'on?callback=?', function(data) {
			// 	embed.replaceWith($(data.div));

			// 	self.insertStylesheet(data.stylesheet);
			// });

			var link = embed.attr('src') + 'on?callback=?';
			var jqxhr = $.getJSON(link, function() {

			})
			.done(function(data) {
				embed.replaceWith($(data.div));
				self.insertStylesheet(data.stylesheet);
			})
			.fail(function() {
				console.log("error");
			});
		});
	},

	insertStylesheet: function(url) {
		var head = $('head');

		if (head.find('link[rel="stylesheet"][href="'+url+'"]').length < 1) {
			head.append('<link rel="stylesheet" href="'+ url +'" type="text/css" />');

			// console.log('appended ' + url);
		}
	},

	getWrapper: function() {
		var wrapper = $('[data-kt-wrapper]').controller();

		return wrapper;
	},

	getItem: function(element) {
		var item = element.parents(self.item().selector);

		return item;
	},

	'{sharing} click': function(link) {
		var item = self.getItem(link);
		var id = item.data('id');

		if (opts.sharing[id] == undefined) {

			var wrapper = item.find(self.sharingWrapper.selector);

			opts.sharing[id] = {
				title: wrapper.data('title'),
				summary: wrapper.data('summary'),
				permalink: wrapper.data('permalink'),
				width: wrapper.data('width'),
				height: wrapper.data('height')
			};
		}

		var sharing = opts.sharing[id];

		// We need to replace the url with the appropriate attributes
		var url = link.data('link');
		url = url.replace(/SUMMARY/, sharing.summary)
				.replace(/TITLE/, sharing.title)
				.replace(/PERMALINK/, sharing.permalink);

		var width = sharing.width;
		var height = sharing.height;
		var left = (screen.width/2) - (width / 2);
		var top = (screen.height/2) - (height / 2);


		window.open(url , "" , 'scrollbars=no,resizable=no,width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
	},

	'{deleteAttachment} click': function(button, event) {

		var item = self.getItem(button);
		var id = item.data('id');
		var attachmentId = button.data('id');
		var attachmentItem = button.parents(self.attachmentItem().selector);

		Komento.dialog({
			"content": Komento.ajax('site/views/attachments/confirmDelete', {"id" : attachmentId}),
			"bindings": {

				"{submit} click": function() {

					Komento.ajax('site/views/attachments/delete', {
						"id": attachmentId
					}).done(function() {

						// Hide dialog
						Komento.dialog().close();

						// Remove file
						attachmentItem.remove();

						var totalAttachments = item.find(self.attachmentItem.selector).length;

						if (totalAttachments <= 0) {
							self.attachmentWrapper().removeClass('has-attachments');
						}
					});
				}
			}
		});

	},

	'{delete} click': function(button, event) {
		var item = self.getItem(button);
		var wrapper = self.getWrapper();
		var id = item.data('id');

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmDelete', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/delete', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	clearNotifications: function() {
		$('[data-kt-alert]')
		.html('')
		.removeClass('error')
		.addClass('t-hidden');
	},

	'{reply} click': function(button, event) {
		var wrapper = self.getWrapper();
		var form = wrapper.getForm();
		var item = self.getItem(button);

		// Clear any message alert on the komento area
		self.clearNotifications();

		// Request the form controller to move itself to this location
		form.reply(item);
	},

	'{edit} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');

		// State to indicate we are now editing the comment
		item.addClass('is-editing');

		// If there is already a form then we shouldn't need to fire another ajax call
		var form = item.find(self.editForm.selector);
		var content = item.find('[data-kt-comment-content]');

		// If the script executes in here, it means that the edit form is already available
		if (form.length > 0) {
			form.removeClass('t-hidden');
			content.addClass('t-hidden');

			return;
		}

		// Get the raw comment object
		Komento.ajax('site/views/comments/edit', {
			"id": id
		}).done(function(form, data) {

			var form = $(form);
			var content = item.find('[data-kt-comment-content]');

			// Add the form after the content
			content.after(form);
			content.addClass('t-hidden');

			if (Komento.bbcode) {
				form.find('[data-kt-editor]').markItUp(Komento.bbcodeButtons());
			}

			// Bind the cancel button
			form.find(self.editCancel.selector)
				.off('click')
				.on('click', function() {
					form.addClass('t-hidden');
					content.removeClass('t-hidden');
				});

			// Bind the save button
			form.find(self.editSave.selector)
				.off('click')
				.on('click', function() {
					var comment = form.find('[data-kt-editor]').val();

					Komento.ajax('site/views/comments/save', {
						"id": id,
						"comment": comment
					}).done(function(message, contents) {

						// Update the contents
						content.html(contents);

						// Update the edited message
						var edited = item.find('[data-kt-comment-edited]');

						if (edited.length > 0) {
							item.addClass('is-edited');
							edited.html(message);
						}


						// Simulate the cancel click button
						form.find(self.editCancel.selector).click();
					});
				});
		});
	},

	'{viewreplies} click': function(button, event) {

		var parentId = button.data('id');
		var rownumber = button.data('rownumber');

		button.find('a').addClass('is-loading');
		button.find('a > span').addClass('t-hidden');

		self.loadReplies(parentId, rownumber)
		.done(function(html){
			// $(html).insertAfter(self.element.find("[data-id=" + parentId + "]"));
			$(html).insertAfter(button);

			var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

			if (hasRatings) {
				self.initRatings($(html).find(self.ratings.selector));
			}

			// Reload syntax highlighter
			if (opts.prism) {
				Prism.highlightAll();
			}

			// Try to find for gist embeds
			self.initGist($(html));

			// hide the button.
			button.find('a').removeClass('is-loading');
			button.find('a > span').removeClass('t-hidden');
			button.addClass('t-hidden');
		});

	},

	loadReplies: function(parentId, rownumber) {
		// var task = $.Deferred();

		return Komento.ajax('site/views/comments/loadReplies', {
			component: Komento.component,
			cid: Komento.cid,
			sort: Komento.sort,
			parentid: parentId,
			rownumber: rownumber,
			contentLink: Komento.contentLink,
		});
		// .done(function(html) {
		// 	task.resolve(html);
		// })
		// .fail(function() {
		// 	task.reject;
		// });

		// return task;
	},



	'{report} click': function(button, event) {
		var item = self.getItem(button);

		Komento.dialog({
			"content": Komento.ajax('site/views/reports/report', { "id": item.data('id')})
		});
	},

	'{publishButton} click': function(el, event) {
		// propagation hack to solve reply form issues sharing the same function name with famelist.js
		event.stopPropagation();

		if(self.item.childs > 0) {
			self.showPublishDialog(el);
		} else {
			self.publishComment(el);
		}
	},

	'{unpublish} click': function(button, event) {

		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmUnpublish', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/unpublish', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	'{unpin} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.ajax('site/views/comments/unpin', {
			"id": id
		}).done(function() {
			item.removeClass('is-featured');
		});
	},

	'{pin} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.ajax('site/views/comments/pin', {
			"id": id
		}).done(function() {
			item.addClass('is-featured');
		});
	},

	'{submitSpam} click': function(button, event) {
		var item = self.getItem(button);
		var id = item.data('id');
		var wrapper = self.getWrapper();

		Komento.dialog({
			"content": Komento.ajax('site/views/comments/confirmSubmitSpam', {"id": id}),
			"bindings": {
				"{submit} click": function() {
					Komento.ajax('site/views/comments/submitSpam', {
						"id": id
					}).done(function() {

						// Hide the dialog
						Komento.dialog().close();

						// Find all comments that has this item as the parent
						var childs = self.item('[data-parentid=' + id + ']');

						// Get the total items to be removed
						var total = childs.length + 1;

						// Remove the childs
						childs.remove();

						// Remove the item
						item.remove();

						// Deduct the counter
						wrapper.decreaseCounter(total);
					});
				}
			}
		});
	},

	// Renders the dialog to display people who likes the comment
	'{likeBrowser} click': function(counter, event) {
		var item = self.getItem(counter);
		var id = item.data('id');

		// Get total likes
		var wrapper = item.find(self.likeWrapper().selector);
		var counter = item.find(self.likeCounter().selector);
		var total = parseInt(counter.text());

		Komento.ajax('site/views/likes/browse', {
			"id": id,
			"total": total
		}).done(function(contents) {
			item.find(self.likeBrowserContents.selector).html(contents);
		});
	},

	'{like} click': function(button, event) {
		var type = button.data('type');
		var item = self.getItem(button);

		Komento.ajax('site/views/likes/action', {
			"type": type,
			"id": item.data('id')
		}).done(function() {

			var wrapper = item.find(self.likeWrapper().selector);
			var counter = item.find(self.likeCounter().selector);
			var count = parseInt(counter.text());

			// Increment counter
			if (type == 'unlike') {
				wrapper.removeClass('is-liked');
				counter.text(count - 1);
				return;
			}

			// Increment likes counter
			wrapper.addClass('is-liked');
			counter.text(count + 1);
		});
	},

	'{likeViewAll} click': function(button) {
		var id = button.data('id');

		Komento.dialog({
			'content': Komento.ajax('site/views/likes/browseAll', {"id" : id})
		})
	}
}});
module.resolve();

});

});
			Komento.module('site/comments/wrapper', function($) {

var module = this;

Komento.require()
.library('scrollTo')
.script('site/comments/list')
.done(function($) {

	Komento.Controller('Wrapper', {
		defaults: {
			"component": null,
			"cid": null,
			"currentUrl": null,

			// Form
			'{form}': '[data-kt-form]',

			// Comments list
			"{comments}": "[data-kt-comments]",

			// Comments sorting
			"{sorting}": "[data-kt-sorting]",

			// ratings
			'{ratings}': '[data-kt-ratings-item]',

			// // comments loadmore button
			// '{loadmoreButton}': '[data-kt-loadmore]',

			//loadmore
			'{loadMore}': '[data-kt-loadmore]',

			// Counter
			"{counter}": "[data-kt-counter]",

			// Subscriptions
			"{subscribe}": "[data-kt-subscribe]",
			"{unsubscribe}": "[data-kt-unsubscribe]"

		}
	}, function(self, opts) { return {

		init: function() {
			opts.component = self.element.data('component');
			opts.cid = self.element.data('cid');
			opts.currentUrl = self.element.data('url');
			opts.live = {
				"enabled": self.element.data('live') == 1 ? true : false,
				"interval": parseInt(self.element.data('live-interval'))
			};

			if (opts.live.enabled) {
				opts.timer = opts.live.interval * 1000;
				self.monitorNewComments();
			}

			// Initialize the list
			if (opts.initList) {
				var list = $('[data-kt-comments]');

				list.implement(Komento.Controller.Comments.List, {
					showRatings: opts.ratings,
					prism: opts.prism,
					cleanGist: true
				});
			}

			// lets see if we have any fragments to process or not.
			if (window.location.hash) {
				var hash = window.location.hash.substring(1);
				self.processFragments(hash);
			}

			// Implement social sharing
		},

		processFragments: function(hash) {
			var fragments = hash.split('=');

			if (fragments[0] == '!kmt-start') {
				// okay we are doing pagnation load

				var currentLimit = fragments[1];

				self.getComments(currentLimit);

			} else if (fragments[0].indexOf('comment-') >= 0) {
				// okay we are doing comment permalink

				// lets base64 decode
				var data = fragments[0].split('comment-');

				// data = atob(data[1]);

				// below is debug code.
				data = data[1];

				var commentFragments = data.split(',');

				// get comment id
				var commentId = commentFragments[0];

				// get parent id
				var parentId = commentFragments[1];

				// get page start
				var currentLimit = 0;
				if (commentFragments[2] != undefined) {
					currentLimit = commentFragments[2];
				}

				// sorting
				var sorting = '';
				if (commentFragments[3] != undefined) {
					sorting = commentFragments[3];
				}

				self.getComments(currentLimit, commentId, parentId, sorting);
			}

			// nothing to process here.
			return;
		},

		// Checks for new comments
		monitorNewComments: function() {

			setTimeout(function() {
				Komento.ajax('site/views/comments/check', {
					"component": opts.component,
					"cid": opts.cid,
					"lastchecktime": opts.lastchecktime
				}).done(function(hasChanges, totalNew, html, nextchecktime) {

					// update the next cycle datetime
					opts.lastchecktime = nextchecktime;

					if (!hasChanges) {
						return;
					}

					// Remove any previously added notification
					$('[data-kt-notifications]').remove();

					// Append the new notification output
					$('body').append(html);

					// Bind the event on the notification object
					$('body').one('click.notifications', '[data-kt-notifications]', function() {
						var wrapper = $(this);

						wrapper.remove();

						// Update the comments list
						window.location.reload();
					});

					// Bind the close button
					$('body').one('click.notifications.close', '[data-kt-notifications] [data-kt-notifications-close]', function(event) {
						event.stopPropagation();
						event.preventDefault();

						var wrapper = $(this).parents('[data-kt-notifications]');

						// Remove the element
						wrapper.remove();
					});
				})
				.always(function() {
					self.monitorNewComments();
				})

			}, opts.timer);
		},

		getCurrentUrl: function() {
			return opts.currentUrl;
		},

		getTotal: function() {
			var total = parseInt(self.counter().text());

			return total;
		},

		setCounter: function(count) {
			self.counter().text(count);
		},

		increaseCounter: function(count) {

			if (count === undefined) {
				count = 1;
			}

			var total = self.getTotal() + count;
			self.setCounter(total);
		},

		decreaseCounter: function(count) {

			if (count === undefined) {
				count = 1;
			}

			var total = self.getTotal() - count;
			self.setCounter(total);
		},

		getForm: function() {
			return self.form().controller();
		},


		getComments: function(start, commentId, parentId, sort) {

			var overrideSorting = false;

			if (sort == undefined || sort == '') {
				var sort = Komento.sort;
			} else {
				Komento.sort = sort;
				overrideSorting = true;
			}

			var task = $.Deferred();

			if (start > 0 || overrideSorting) {

				// disable the loadmore so that user can no longer click.
				self.loadMore().addClass('disabled');

				Komento.ajax('site/views/comments/loadComments', {
					component: Komento.component,
					cid: Komento.cid,
					endlimit: start,
					sort: sort,
					contentLink: Komento.contentLink,
				})
				.done(function(html, nextstart) {

					// remove the comment items.
					// TODO: we should just load the comment that is not being loaded yet.
					self.comments().find("[data-kt-comment-item]").remove();

					// now insert the comemtns before the 'empty div'.
					if (self.comments().find("[data-kt-comment-item]").length > 0) {
						$(html).insertBefore(self.comments().find("[data-kt-comment-item]").last());
					} else {
						$(html).appendTo(self.comments());
					}


					var listController = self.comments().controller();

					listController.initRatings($(html).find(listController.ratings.selector));

					// Reload syntax highlighter
					if (opts.prism) {
						Prism.highlightAll();
					}

					// Try to find for gist embeds
					listController.initGist(self.comments());

					if(nextstart != '-1') {
						self.loadMore().show();
						self.loadMore().removeClass('disabled');

						// update loadmore bar
						var nextStartCount;
						self.loadMore().attr('href', '#!kmt-start=' + nextstart);
						self.loadMore().data('nextstart', nextstart);

					} else {
						// this could be the last page. hide loadmore.
						self.loadMore().hide();
					}

					if (overrideSorting) {
						self.sorting()
							.removeClass('is-active');

						$('[data-kt-sorting][data-type="' + sort +  '"]').addClass('is-active');
					}


					task.resolve(commentId, parentId);

				})
				.fail(function() {
					task.resolve(commentId, parentId);
				});

			} else {
				task.resolve(commentId, parentId);
			}

			task.done(function(commentId, parentId) {

				if (commentId != undefined && commentId) {

					// lets check if the comment is there or not.
					if ($('#comment-' + commentId).length > 0) {
						// TODO: scroll the page down to the anchor
						$(document).scrollTo('#comment-' + commentId);
					} else {

						if (parentId != undefined && parentId) {
							// okay we need to further retrieve the replies as the reply might not loaded yet.
							var button = $('[data-kt-view-reply][data-id="' + parentId + '"]');

							if (button.length > 0) {

								var parentId = button.data('id');
								var rownumber = button.data('rownumber');

								var listController = self.comments().controller();

								if (listController !== undefined) {

									listController.loadReplies(parentId, rownumber)
									.done(function(html){

										$(html).insertAfter(button);

										var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

										if (hasRatings) {
											listController.initRatings($(html).find(listController.ratings.selector));
										}

										// Reload syntax highlighter
										if (opts.prism) {
											Prism.highlightAll();
										}

										// Try to find for gist embeds
										listController.initGist(self.comments());

										// hide the button.
										button.find('a').removeClass('is-loading');
										button.find('a > span').removeClass('t-hidden');
										button.addClass('t-hidden');

										$(document).scrollTo('#comment-' + commentId);
									});
								}
							}
						}
					}
				}
			});


		},

		'{loadMore} click': function(el, event) {

	        // event.preventDefault();
			self.loadMore()
				.addClass('is-loading')
				.attr('disabled', true);

			var startCount;

			startCount = el.data('nextstart');

			Komento.ajax('site/views/comments/loadmore', {
				component: Komento.component,
				cid: Komento.cid,
				start: startCount,
				sort: Komento.sort,
				contentLink: Komento.contentLink,
			})
			.done(function(html, nextstart) {

				var listController = self.comments().controller();


				$(html).insertAfter(self.comments().find("[data-kt-comment-item]").last());

				var hasRatings = $(html).find('[data-kt-ratings-item]').length > 0;

				if (hasRatings) {
					listController.initRatings($(html).find(self.ratings.selector));
				}

				// Reload syntax highlighter
				if (opts.prism) {
					Prism.highlightAll();
				}

				// Try to find for gist embeds
				listController.initGist(self.comments());

				if (nextstart != '-1') {

					self.loadMore()
						.removeClass('is-loading')
						.removeAttr('disabled');


					var nextStartCount;

					self.loadMore().attr('href', '#!kmt-start=' + nextstart);
					self.loadMore().data('nextstart', nextstart);

				} else {
					self.loadMore().hide();
				}

				// callback && callback();
			})
			.fail(function(limit, limitstart, sort) {

			});

		},


		"{sorting} click": function(link, event) {
			var type = link.data('type');

			self.sorting()
				.removeClass('is-active');

			link.addClass('is-active');

			self.getComments(0, 0, 0, type);
		},

		"{subscribe} click": function(button, event) {
			Komento.dialog({
				"content": Komento.ajax('site/views/subscriptions/subscribe', {"component": opts.component, "cid": opts.cid, "currentUrl": opts.currentUrl}),
				"bindings": {
					"{submit} click": function() {

						// Check if the name is empty
						var name = this.name().val();

						if (name == '') {
							this.name().parents('.o-form-group').addClass('has-error');
							return false;
						}

						// Check if the email is empty
						var email = this.email().val();

						if (email == '') {
							this.email().parents('.o-form-group').addClass('has-error');
							return false;
						}

						this.form().submit();
					}
				}
			});
		},

		"{unsubscribe} click": function(button, event) {
			Komento.dialog({
				"content": Komento.ajax('site/views/subscriptions/confirmUnsubscribe', {"component": opts.component, "cid": opts.cid, "currentUrl": opts.currentUrl}),
				"bindings": {
					"{submit} click": function() {
						this.form().submit();
					}
				}
			});
		}

	}});

	module.resolve();
	});

});
			Komento.module('site/common', function($) {

var module = this;

var originalHide = $.fn.hide;
var originalShow = $.fn.show;

var originalScrollTo = function( element ) {
	$.scrollTo(element, 500);
};

$.fn.hide = function() {
	originalHide.apply(this, arguments);
	this.addClass('hidden');
	return this;
}

$.fn.show = function() {
	originalShow.apply(this, arguments);
	this.removeClass('hidden');
	return this;
}

$.fn.scroll = function() {
	originalScrollTo(this);
};

$.fn.highlight = function() {
	this.effect("highlight", {color: '#FDFFE0'}, 2000);
	return this;
};

$.fn.enable = function() {
	this.removeClass('disabled');
	return this;
};

$.fn.disable = function() {
	this.addClass('disabled');
	return this;
};

$.fn.switchOn = function() {
	this.removeClass('cancel');
	return this;
};

$.fn.switchOff = function() {
	this.addClass('cancel');
	return this;
};

$.fn.checkSwitch = function() {
	if(this.hasClass('cancel')) {
		return false;
	} else {
		return true;
	}
};

$.fn.checkClick = function() {

	if (this.hasClass('disabled')) {
		return false;
	}
	
	this.addClass('disabled');
	return true;
};

$.fn.exists = function() {
	return this.length > 0 ? true : false;
};


module.resolve();

});
			Komento.module('site/dashboard/default', function($) {
	
var module = this;

Komento.Controller('Dashboard', {
	defaults: {
		// Actions bar
		'{checkAll}': '[data-kt-dashbaord-checkall]',
		'{actions}': '[data-kt-dashboard-actions]',

		// Notice
		'{notice}': '[data-kt-dashboard-notice]',

		// Item
		'{item}': '[data-kt-dashboard-item]',
		'{checkbox}': '[data-kt-dashboard-item-checkbox]',
		'{delete}': '[data-kt-dashboard-delete]',
		'{unpublish}': '[data-kt-dashboard-unpublish]',
		'{publish}': '[data-kt-dashboard-publish]',
		'{spam}': '[data-kt-dashboard-spam]',
		'{notspam}': '[data-kt-dashboard-notspam]',
		'{moderate}': '[data-kt-dashboard-moderate]',
		'{clearReports}': '[data-kt-dashboard-reports-clear]'
	}
}, function(self, opts) { return {
	init: function() {
	},
	
	updateActions: function() {
		var checked = self.checkbox().is(':checked');

		self.actions().toggleClass('is-checked', checked);
	},

	getItems: function(value) {
		var items = self.checkbox(':checked');

		if (value === undefined) {
			var selected = items.map(function() {
				return this.value;
			}).get();

			return selected;
		}

		return items;
	},

	setNotice: function(message, type) {

		if (type == undefined) {
			type = 'success';
		}

		self.notice()
			.html(message)
			.removeClass('t-hidden o-alert--success o-alert--danger o-alert--warning o-alert--info')
			.addClass('o-alert--' + type);
	},

	updateItemState: function(checkboxes, state) {
		checkboxes.each(function(i) {
			var item = $(this).parents(self.item.selector);

			item
				.removeClass('is-published is-unpublished')
				.addClass(state);
		});
	},

	'{checkbox} change': function(checkbox, event) {
		var checked = checkbox.is(':checked');
		var parent = checkbox.parents(self.item.selector);

		if (checked) {
			parent.addClass('is-selected');
		} else {
			parent.removeClass('is-selected');
		}

		self.updateActions();
	},

	'{checkAll} change': function(checkbox, event) {
		var checked = checkbox.is(':checked');

		self.checkbox().prop('checked', checked);
		self.checkbox().trigger('change');
	},
	
	'{publish} click': function(button, event) {
		var items = this.getItems();

		// Since this is a non destructive operation, no point asking for cofirmation
		Komento.ajax('site/views/dashboard/publish', {
			"id": items
		}).done(function(message) {
			self.setNotice(message, 'success');

			var checkboxes = self.getItems('object');

			self.updateItemState(checkboxes, 'is-published');
		});
	},

	'{unpublish} click': function(button, event) {
		var items = this.getItems();

		// Since this is a non destructive operation, no point asking for cofirmation
		Komento.ajax('site/views/dashboard/unpublish', {
			"id": items
		}).done(function(message) {
			self.setNotice(message, 'success');

			var checkboxes = self.getItems('object');

			self.updateItemState(checkboxes, 'is-unpublished');
		});
	},

	'{delete} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmDelete', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{spam} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmSpam', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{notspam} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmRemoveSpam', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	},

	'{moderate} click': function(button, event) {
		var items = this.getItems();
		var action = button.data('action');

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmModerate', {"items": items, "return": opts.return, "action": action}),
			bindings: {
			}
		});
	},

	'{clearReports} click': function(button, event) {
		var items = this.getItems();

		if (!items) {
			return;
		}

		Komento.dialog({
			content: Komento.ajax('site/views/dashboard/confirmClearReports', {"items": items, "return": opts.return}),
			bindings: {
			}
		});
	}
}});

module.resolve();

});
			Komento.module('dashboard.flag.item', function($) {
	var module = this;

	Komento.require()
	.library('ui/effect', 'dialog')
	.language(
		'COM_KOMENTO_UNPUBLISHED'
	)
	.view(
		'dialogs/delete.affectchild',
		'dialogs/unpublish.affectchild',
		'comment/edit.form'
	)
	.done(function()
	{
		Komento.Controller(
			'Dashboard.FlagItem',
			{
				defaults: {
					'commentId': 0,
					'permalink': 0,
					'{commentText}': '.kmt-text',
					'{commentInfo}': '.kmt-info',
					'{commentStatus}': '.kmt-status',
					'{noflagButton}': '.kmt-noflag',
					'{spamButton}': '.kmt-spam',
					'{offensiveButton}': '.kmt-offensive',
					'{offtopicButton}': '.kmt-offtopic',
					'{publishButton}': '.kmt-publish',
					'{unpublishButton}': '.kmt-unpublish',
					'{deleteButton}': '.kmt-delete',
					view: {
						editForm: 'comment/edit.form',
						deleteDialog: 'dialogs/delete.affectchild',
						unpublishDialog: 'dialogs/unpublish.affectchild'
					}
				}
			},
			function(self)
			{ return {
				init: function()
				{
				},

				closeDialog: function()
				{
					$('.foundryDialog').controller().close();
				},

				'{noflagButton} click': function()
				{
					self.markComment('0');
				},

				'{spamButton} click': function()
				{
					self.markComment('1');
				},

				'{offensiveButton} click': function()
				{
					self.markComment('2');
				},

				'{offtopicButton} click': function()
				{
					self.markComment('3');
				},

				'{publishButton} click': function()
				{
					self.publishComment();
				},

				'{unpublishButton} click': function()
				{
					self.showUnpublishDialog();
				},

				'{deleteButton} click': function()
				{
					self.showDeleteDialog();
				},

				markComment: function(type)
				{
					var commentId = self.options.commentId;
					var id = commentId.split('-')[1];

					Komento.ajax('site.views.komento.mark',
					{
						id: id,
						type: type
					},
					{
						success: function()
						{
							self.element.hide('fade', function() {
								self.element.remove();
							});
						},

						fail: function()
						{

						}
					});
				},

				showPublishDialog: function()
				{

				},

				publishComment: function()
				{
					var commentId = self.options.commentId;
					var id = commentId.split('-')[1];

					Komento.ajax('site.views.komento.publish',
					{
						id: id,
						affectChild: 0
					},
					{
						success: function()
						{
							self.element.hide('fade', function() {
								self.element.remove();
							});
						},

						fail: function()
						{

						}
					});
				},

				showUnpublishDialog: function()
				{
					$.dialog({
						content: self.view.unpublishDialog(true),
						afterShow: function() {
							$('.foundryDialog').find('.unpublish-affectChild').click(function() {
								self.unpublishComment();
							});
						}
					});
				},

				unpublishComment: function()
				{
					var commentId = self.options.commentId;
					var id = commentId.split('-')[1];

					Komento.ajax('site.views.komento.unpublish',
					{
						id: id
					},
					{
						success: function()
						{
							self.closeDialog();
							self.unpublishChild(self.element.attr('id'));

							self.commentStatus().text($.language('COM_KOMENTO_UNPUBLISHED'));
							self.unpublishButton().parent().hide('drop');
						},

						fail: function()
						{

						}
					});
				},

				unpublishChild: function(id)
				{
					$('tr[parentid="' + id + '"]').each(function() {
						$(this).find('.kmt-unpublish').parent().hide('drop');
						$(this).find('.kmt-status').text($.language('COM_KOMENTO_UNPUBLISHED'));
						self.unpublishChild($(this).attr('id'));
					})
				},

				showDeleteDialog: function()
				{
					$.dialog({
						content: self.view.deleteDialog(true),
						afterShow: function() {
							$('.foundryDialog').find('.delete-affectChild').click(function() {
								self.deleteComment(1);
							});

							$('.foundryDialog').find('.delete-moveChild').click(function() {
								self.deleteComment(0);
							});
						}
					});
				},

				deleteComment: function(affectChild)
				{
					var commentId = self.options.commentId;
					var id = commentId.split('-')[1];

					Komento.ajax('site.views.komento.deletecomment',
					{
						id: id,
						affectChild: affectChild
					},
					{
						success: function()
						{
							self.closeDialog();

							if(affectChild)
							{
								self.deleteChild(self.element.attr('id'));
							}

							self.element.hide('fade', function() {
								self.element.remove();
							});
						},

						fail: function()
						{
						}
					});
				},

				deleteChild: function(id)
				{
					$('tr[parentid="' + id + '"]').each(function() {
						self.deleteChild($(this).attr('id'));
					}).hide('fade', function() {
						$(this).remove();
					});
				}

			} }
		);

		module.resolve();
	});
});
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
			Komento.module('site/form/attachments', function($) {

var module = this;

Komento.require()
.library('plupload')
.done(function($) {

Komento.Controller('Uploader', {
	defaults: {
		uploadUrl: $.indexUrl + '?option=com_komento&controller=file&tmpl=component&task=upload&component=' + Komento.component,
		uploadedId: [],

		'{uploader}': '[data-kt-attachments-form]',
		'{uploadButton}': '[data-kt-attachments-button]',
		'{uploadQueue}': '[data-kt-attachments-queue]',
		'{template}': '[data-kt-attachments-item][data-template]',
		'{item}': '[data-kt-attachments-item]',
		'{counter}': '[data-kt-attachments-counter]',
		'{removeFile}': '[data-kt-attachments-item-remove]'
	}
}, function(self, opts) { return {
	init: function() {

		// Initialize the template
		self.initTemplate();

		// Implement plupload
		self.uploader().implement('plupload', {
			settings: {
				"url": self.options.uploadUrl + '&' + Komento.token() + '=1',
				"max_file_size": opts.upload_max_size,
				"filters": [{
						"title": 'Allowed File Type', 
						"extensions": opts.extensions
				}]
			},
			'{uploader}': self.uploader().selector,
			'{uploadButton}': self.uploadButton().selector
		}, function() {
			self.plupload = this.plupload;
		});
	},

	resetForm: function() {
		opts.uploadedId = [];
	},

	getUploadedIds: function() {
		return opts.uploadedId;
	},

	initTemplate: function() {
		opts.itemTemplate = self.template().clone();

		// Remove the template from the layout
		self.template().remove();
	},

	getItemTemplate: function(file) {
		var item = opts.itemTemplate.clone();

		item.removeAttr('data-template');
		item.attr('id', file.id);
		item.find('[data-size]').html(file.size);
		item.find('[data-title]').html(file.title);
		item.removeClass('t-hidden');

		item.data('file', file);

		return item;
	},

	hasItems: function() {
		return opts.uploadedId.length > 0;
	},

	startUpload: function() {

		if (self.plupload.files.length > 0) {
			self.plupload.start();
			return;
		}

		self.parent.saveComment();
	},

	addFiles: function(files) {
		if (files.length < 1) {
			return;
		}

		// Clear notifications
		self.parent.clearNotifications();

		$.each(files, function(index, item) {

			// If the user tries to upload more than the allowed files, do not add them
			if (self.plupload.files.length > opts.upload_max_files) {
				self.plupload.removeFile(item);
				return true;
			}

			// Check for file status before proceeding
			if (item.status != 1) {
				return true;
			}

			var size = parseInt(item.size / 1024);

			var template = self.getItemTemplate({
				"id": item.id,
				"title": item.name,
				"size": size
			});

			// Append the item to the queue
			self.uploadQueue()
				.removeClass('t-hidden')
				.append(template);
		});
	},

	removeItem: function(item) {

		// Remove the dom
		item.remove();

		// Remove from the plupload queue
		var id = item.attr('id');
		var file = self.plupload.getFile(id);

		self.plupload.removeFile(file);

		// Hide the upload queue when it is empty
		var total = self.item().length;

		if (!total) {
			self.uploadQueue().addClass('t-hidden');
		}
	},

	// When a file is added into the queue
	"{uploader} FilesAdded": function(el, event, uploader, file) {
		self.addFiles(file);
	},

	'{uploader} UploadComplete': function(el, event, uploader, files) {

		self.item().each(function(index, item) {
			var item = $(item);

			self.removeItem(item);
		});

		// Once the upload is completed, we'll need to submit the comment
		self.parent.saveComment();
	},

	'{uploader} FileUploaded': function(el, event, uploader, file, response) {
		
		// Once a file is uploaded, push it into the ids so that other controllers know if there are pending files
		if (response.status == 1) {
			opts.uploadedId.push(response.id);
		}

		if( response.status == 'notallowed' ) {
			self.plupload.stop();
			self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_UPLOAD_NOT_ALLOWED'));
			return;
		}

		if( response.status == 'exceedfilesize' ) {
			self.plupload.stop();
			self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_MAX_FILE_SIZE', Komento.options.config.upload_max_size + 'mb'));
			return;
		}
	},

	'{uploader} QueueChanged': function(el, event, uploader) {
		self.counter().text(uploader.files.length);
	},

	'{uploader} Error': function(el, event, uploader, error) {

		// Clear previous notifications
		self.parent.clearNotifications();
		
		switch (error.code) {
			case -600:
				self.parent.notification(error.message, 'error');

				break;
			case -601:
				// self.kmt.form.errorNotification($.language('COM_KOMENTO_FORM_NOTIFICATION_FILE_EXTENSION', Komento.options.config.upload_allowed_extension));
				break;
		}
	},

	'{removeFile} click': function(button) {
		var item = button.parents(self.item().selector);

		self.removeItem(item);
	}
}});

module.resolve();
});
});
			Komento.module('site/form/form', function($) {

var module = this;

Komento.require()
.library('markitup', 'expanding', 'scrollTo')
.script('site/form/location', 'site/form/attachments')
.done(function($) {

Komento.Controller('Form', {
	defaults: {

		// Inputs
		'{form}': '[data-kt-form-element]',
		'{parentId}': '[data-kt-parent]',
		'{username}': '[data-kt-register-username]',
		'{name}': '[data-kt-name]',
		'{email}': '[data-kt-email]',
		'{website}': '[data-kt-website]',
		'{terms}': '[data-kt-terms]',
		'{ratings}': '[data-kt-ratings-star]',

		// Used in replying to comments
		'{parentId}': '[data-kt-parent]',

		// Editor
		'{editor}': '[data-kt-editor]',

		// Form actions
		'{cancel}': '[data-kt-cancel]',
		'{save}': '[data-kt-submit]',

		// Terms
		'{viewTnc}': '[data-kt-tnc-view]',
		'{tncCheckbox}': '[data-kt-terms]',

		// Location services
		'{location}': '[data-kt-location]',

		// Attachments
		'{attachments}': '[data-kt-attachments]',

		// Alerts
		'{alert}': '[data-kt-alert]',

		// Re-Captcha
		'{recaptchaResponse}': "[data-kt-recaptcha-response]",

		// Standard Builtin Captcha
		'{captchaImage}': '[data-kt-captcha-image]',
		'{captchaResponse}': '[data-kt-captcha-response]',
		'{captchaId}': '[data-kt-captcha-id]',
		'{captchaReload}': '[data-kt-captcha-reload]',

		// Counter
		'{counter}': '[data-kt-text-counter]'
	}
}, function(self, opts) { return {

	init: function() {

		// Initialize the editor
		self.initEditor();

		if (opts.location) {
			self.initLocation();
		}

		if (opts.attachments.enabled) {
			self.initAttachments();
		}

		// Reset the form in case the browser is caching it.
		self.resetForm();
	},

	getLocationController: function() {
		return self.location().controller(Komento.Controller.Location.Form);
	},

	getAttachmentsController: function() {
		var controller = self.attachments().controller(Komento.Controller.Uploader);

		return controller;
	},

	getWrapper: function() {
		var wrapper = self.element.parents('[data-kt-wrapper]');

		return wrapper.controller();
	},

	getCommentsList: function() {
		var wrapper = this.getWrapper();
		var list = wrapper.comments();

		return list;
	},

	initEditor: function() {
		// Initialize bbcode if we need to
		if (opts.bbcode) {
			self.editor().markItUp(opts.markupSet());
		}

		// Implement expanding textarea on the editor
		self.editor().expandingTextarea();
	},

	initAttachments: function() {
		self.attachments().addController(Komento.Controller.Uploader, {
			"upload_max_size": opts.attachments.upload_max_size,
			"upload_max_files": opts.attachments.upload_max_files,
			"extensions": opts.attachments.extensions,
			"{parent}": this
		});
	},

	initLocation: function() {
		self.location().addController(Komento.Controller.Location.Form, {
			"{parent}": this,
			"location_key": opts.location_key
		});
	},

	insertText: function(text, position) {
		var position = position == undefined ? 0 : position;
		var contents = self.editor().val();

		if (position == 0) {
			// Since the position is 0, we can say we are just prepending the text
			contents = text + contents;
		} else {
			contents = contents.substring(0, position) + text + contents.substring(position, contents.length);
		}

		// Focus on the editor
		self.editor().val(contents);
		self.editor().focus();

		// Update the comments length
		self.updateCommentLength();
	},

	// Resets the comment form
	resetForm: function() {

		self.editor().val('');

		var parentId = self.parentId().val();

		// Reset reply
		if (parentId != 0) {
			self.cancel().click();
		}

		// Reset comment length count
		self.counter().text('0');

		// Reset location form
		if (self.location().length > 0) {
			self.getLocationController().removeLocation();
		}

		// Reset attachments
		if (opts.attachments.enabled) {
			self.getAttachmentsController().resetForm();
		}

		// Reset ratings
		if (self.ratings().length > 0) {
			self.ratings().raty('cancel');
		}

		// Reset submit button
		self.save()
			.removeAttr('disabled')
			.removeClass('is-loading');
	},

	updateCommentLength: function() {
		self.counter().text(self.editor().val().length);
	},

	saveComment: function() {

		// Get the form inputs
		var data = self.form().serializeObject();

		// Insert attachment ids
		if (opts.attachments.enabled) {
			data.attachments = self.getAttachmentsController().getUploadedIds();
		}

		// data.parentid =
		data.component = Komento.component;
		data.cid = Komento.cid;
		data.contentLink = Komento.contentLink;
		data.parent_id = self.parentId().val();
		data.tnc = self.tncCheckbox().is(':checked');

		// Recaptcha
		data.recaptchaResponse = self.recaptchaResponse().val();

		Komento
			.ajax('site/views/comments/add', $.extend({}, data))
			.done(function(message, html, state, sorting) {

				if (state == 1) {
					var wrapper = self.getWrapper();
					wrapper.increaseCounter();

					// Increase the count so that the notification doesn't notify
					Komento.loadedCount += 1;
					Komento.totalCount += 1;
				}

				var list = self.getCommentsList();
				var item = $(html);

				// if it is detected as spam, don't append list
				if (state == 3) {
					self.notification(message, 'warning');
				} else {
					list.controller().insertRow(item, data.parent_id, sorting);

					type = (state == 2) ? 'info' : 'success';

					self.notification(message, type);
				}

				self.resetForm();
				self.reloadCaptcha();

				$('[data-kt-comments-container]').removeClass('is-empty');
			})
			.fail(function(message) {
				self.notification(message, 'error');
			})
			.always(function() {
				// Even if it fails, we should restart the submit button
				self.save()
					.removeAttr('disabled')
					.removeClass('is-loading');
			});
	},

	reloadCaptcha: function() {

		if (!opts.showCaptcha) {
			return;
		}

		// Recaptcha
		if (opts.recaptcha) {
            grecaptcha.reset();
			return;
		}

		self.captchaReload().addClass('is-loading');

		// Standard built in captcha
		Komento.ajax('site/views/captcha/reload', {
			"id": self.captchaId().val()
		}).done(function(data) {

			self.captchaReload().removeClass('is-loading');

			self.captchaImage().attr('src', data.image);
			self.captchaId().val(data.id);
			self.captchaResponse().val('');
		});
	},

	// Allows caller to invoke the form to be moved to a specific comment
	reply: function(item) {

		// Reset the form
		self.resetForm();

		var id = item.data('id');
		var depth = parseInt(item.data('depth')) + 1;

		self.parentId().val(id);

		// Move the comment to the item
		self.element
			.addClass('is-replying')
			.appendTo(item)
			.scroll();
	},

	notification: function(message, type) {
		self.alert()
			.removeClass('o-alert--success o-alert--danger o-alert--warning o-alert--info o-alert--error')
			.addClass('o-alert--' + type)
			.html(message)
			.removeClass('t-hidden');
	},

	closeNotification: function() {
		self.alert().addClass('t-hidden');
	},

	clearNotifications: function() {
		self.alert()
			.html('')
			.removeClass('error')
			.addClass('t-hidden');
	},

	// We need to convert this into an ajax call to view terms and conditions
	"{viewTnc} click": function() {
		Komento.dialog({
			content: Komento.ajax('site/views/comments/getTnc')
		});
	},

	"{editor} keydown": function(editor, event) {

		// Bind cmd + enter / ctrl + enter
		if ((event.metaKey || event.ctrlKey) && event.keyCode == 13) {
			self.save().click();

			event.preventDefault();
		}
	},

	"{editor} keyup" :function(editor) {
		self.updateCommentLength();
	},

	// Since there is only 1 form at any given point of time, we will now return the form to it's original state
	'{cancel} click': function(button, event) {

		self.parentid = 0;

		// Ensure parent id is always empty.
		self.parentId().val('');

		self.resetForm();
		self.element.removeClass('is-replying');
		self.element
			.appendTo(self.getWrapper().element);
	},

	"{save} click": function(button) {

		// Add loading indicator
		button
			.attr('disabled', true)
			.addClass('is-loading');

		// Clear all prior notifications
		self.clearNotifications();

		if (opts.attachments.enabled) {
			self.getAttachmentsController().startUpload();

			return;
		}

		self.saveComment();
		return;
	},

	"{captchaReload} click": function() {
		self.reloadCaptcha();
	}
}});

module.resolve();
});
});
			Komento.module('site/form/location', function($) {

var module = this;

Komento.require()
.library("ui/autocomplete")
.done(function($){

Komento.Controller('Location.Form', {
	defaultOptions: {
		language: 'en',
		initialLocation: null,

		"{button}": "[data-kt-location-button]",
		"{form}": '[data-kt-location-form]',
		"{address}": "[data-kt-location-address]",
		"{latitude}": "[data-kt-location-lat]",
		"{longitude}": '[data-kt-location-lng]',
		"{detect}": "[data-kt-location-detect]"
	}
}, function(self, opts) { return {

	init: function() {

		self.resetForm();

		var mapReady = $.uid("ext");

		window[mapReady] = function() {
			$.___GoogleMaps.resolve();
		}

		if (!$.___GoogleMaps) {

			$.___GoogleMaps = $.Deferred();

			if (window.google === undefined || window.google.maps === undefined) {
				Komento.require()
					.script(
						{prefetch: false},
						"https://maps.googleapis.com/maps/api/js?sensor=true&language=" + self.options.language + "&callback=" + mapReady + '&key=' + opts.location_key
					);
			} else {
				$.___GoogleMaps.resolve();
			}
		}

		// Defer instantiation of controller until Google Maps library is loaded.
		$.___GoogleMaps.done(function() {
			self._init();
		});
	},

	resetForm: function() {

		self.locationResolved = false;

		self.latitude().val('');
		self.longitude().val('');
		self.address().val('');
	},

	_init: function() {

		self.geocoder = new google.maps.Geocoder();

		self.hasGeolocation = navigator.geolocation!==undefined;

		if (!self.hasGeolocation) {
			self.detect().remove();
		} else {
			self.detect().show();
		}

		self.address()
			.autocomplete({

				delay: 300,
				minLength: 0,
				source: self.retrieveSuggestions,
				select: function(event, ui) {

					self.address()
						.autocomplete("close");

					self.setLocation(ui.item.location);
				}
			})
			.prop("disabled", false);

		self.address().addClass('location-suggestion');

		var initialLocation = $.trim(self.options.initialLocation);

		if (initialLocation) {

			self.getLocationByAddress(initialLocation, function(location) {
					self.setLocation(location[0]);
			});
		}

		self.busy(false);
	},

	busy: function(isBusy) {
		self.address().toggleClass("is-loading", isBusy);
		self.detect().removeClass('is-loading');
	},

	getUserLocations: function(callback) {
		self.getLocationAutomatically(function(locations) {
				self.userLocations = self.buildDataset(locations);
				callback && callback(locations);
		});
	},

	getLocationByAddress: function(address, callback) {

		self.geocoder.geocode({
			"address": address
		}, callback);
	},

	getLocationByCoords: function(latitude, longitude, callback) {

		self.geocoder.geocode({
			"location": new google.maps.LatLng(latitude, longitude)
		}, callback);
	},

	getLocationAutomatically: function(success, failCallback) {

		if (!navigator.geolocation) {
			return fail("ERRCODE", "Browser does not support geolocation or do not have permission to retrieve location data.")
		}

		navigator.geolocation.getCurrentPosition(function(position) {
			self.getLocationByCoords(position.coords.latitude, position.coords.longitude, success)
		}, failCallback);
	},

	setLocation: function(location) {

		if (!location) {
			return;
		}

		self.locationResolved = true;
		self.lastResolvedLocation = location;

		self.address().val(location.formatted_address);

		self.latitude().val(location.geometry.location.lat());

		self.longitude().val(location.geometry.location.lng());
	},

	removeLocation: function() {
		self.resetForm();
	},

	buildDataset: function(locations) {

		var dataset = $.map(locations, function(location){
			return {
				"label": location.formatted_address,
				"value": location.formatted_address,
				"location": location
			};
		});

		return dataset;
	},

	retrieveSuggestions: function(request, response) {

		self.busy(true);

		var address = request.term,

			respondWith = function(locations) {
				response(locations);
				self.busy(false);
			};

		// User location
		if (address=="") {
			respondWith(self.userLocations || []);
		} else {
			// Keyword search
			self.getLocationByAddress(address, function(locations) {
				respondWith(self.buildDataset(locations));
			});
		}
	},

	suggestUserLocations: function() {

		if (self.hasGeolocation && self.userLocations) {
			self.resetForm();
			
			self.address()
				.autocomplete("search", "");
		}

		self.busy(false);
	},

	"{button} click": function(button, event) {
		button.toggleClass('is-active');
		
		self.form().toggleClass('t-hidden');
	},

	"{address} blur": function() {

		// Give way to autocomplete
		setTimeout(function(){

			var address = $.trim(self.address().val());

			// Location removal
			if (address=="") {
				self.resetForm();
			} else if (self.locationResolved) {

				// Unresolved location, reset to last resolved location
				if (address != self.lastResolvedLocation.formatted_address) {
					self.setLocation(self.lastResolvedLocation);
				}
			} else {
				self.resetForm();
			}

		}, 250);
	},

	"{detect} click": function() {

		self.busy(true);

		self.detect().addClass('is-loading');

		if (self.hasGeolocation && !self.userLocations) {
			self.getUserLocations(self.suggestUserLocations);
		} else {
			self.suggestUserLocations();
		}
	},

	"{address} keypress": function(input) {
		input.keypress(function(event) {
			if (event.which == 13) {
				return false;
			}
		});
	}

}});

module.resolve();

});
});
			Komento
.require()
.script(
	'site/common',
	'shared/elements'
)
.library('dialog').done(function($){
	
});
			var rootDoc = this;

Komento.module('site/vendors/lightbox', function($) {

	var module = this;
	var jQuery = $;

		/*!
		* Lightbox v2.8.1
		* by Lokesh Dhakar
		*
		* More info:
		* http://lokeshdhakar.com/projects/lightbox2/
		*
		* Copyright 2007, 2015 Lokesh Dhakar
		* Released under the MIT license
		* https://github.com/lokesh/lightbox2/blob/master/LICENSE
		*/

		// Uses Node, AMD or browser globals to create a module.
		(function (root, factory) {
			rootDoc.lightbox = factory(jQuery);
				// if (typeof define === 'function' && define.amd) {
				//     // AMD. Register as an anonymous module.
				//     define(['jquery'], factory);
				// } else {
				//     // Browser globals
				//     factory(jQuery);
				// }
			// if (typeof define === 'function' && define.amd) {
			//     // AMD. Register as an anonymous module.
			//     define(['jquery'], factory);
			// } else if (typeof exports === 'object') {
			//     // Node. Does not work with strict CommonJS, but
			//     // only CommonJS-like environments that support module.exports,
			//     // like Node.
			//     module.exports = factory(require('jquery'));
			// } else {
			//     // Browser globals (root is window)
			//     root.lightbox = factory(root.jQuery);
			// }
		}(this, function ($) {

				function Lightbox(options) {
					this.album = [];
					this.currentImageIndex = void 0;
					this.init();

					// options
					this.options = $.extend({}, this.constructor.defaults);
					this.option(options);
				}

				// Descriptions of all options available on the demo site:
				// http://lokeshdhakar.com/projects/lightbox2/index.html#options
				Lightbox.defaults = {
					albumLabel: 'Image %1 of %2',
					alwaysShowNavOnTouchDevices: false,
					fadeDuration: 500,
					fitImagesInViewport: true,
					// maxWidth: 800,
					// maxHeight: 600,
					positionFromTop: 50,
					resizeDuration: 700,
					showImageNumberLabel: true,
					wrapAround: false
				};

				Lightbox.prototype.option = function(options) {
					$.extend(this.options, options);
				};

				Lightbox.prototype.imageCountLabel = function(currentImageNum, totalImages) {
					return this.options.albumLabel.replace(/%1/g, currentImageNum).replace(/%2/g, totalImages);
				};

				Lightbox.prototype.init = function() {
					this.enable();
					this.build();
				};

				// Loop through anchors and areamaps looking for either data-lightbox attributes or rel attributes
				// that contain 'lightbox'. When these are clicked, start lightbox.
				Lightbox.prototype.enable = function() {
					var self = this;
					$('body').on('click', 'a[rel^=lightbox], area[rel^=lightbox], a[data-lightbox], area[data-lightbox]', function(event) {
						self.start($(event.currentTarget));
						return false;
					});
				};

				// Build html for the lightbox and the overlay.
				// Attach event handlers to the new DOM elements. click click click
				Lightbox.prototype.build = function() {
					var self = this;
					$('<div id="kt-lightboxOverlay" class="lightboxOverlay"></div><div id="kt-lightbox" class="lightbox"><div class="lb-outerContainer"><div class="lb-container"><img class="lb-image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" /><div class="lb-nav"><a class="lb-prev" href="" ></a><a class="lb-next" href="" ></a></div><div class="lb-loader"><a class="lb-cancel"></a></div></div></div><div class="lb-dataContainer"><div class="lb-data"><div class="lb-details"><span class="lb-caption"></span><span class="lb-number"></span></div><div class="lb-closeContainer"><a class="lb-close"></a></div></div></div></div>').appendTo($('body'));

					// Cache jQuery objects
					this.$lightbox       = $('#kt-lightbox');
					this.$overlay        = $('#kt-lightboxOverlay');
					this.$outerContainer = this.$lightbox.find('.lb-outerContainer');
					this.$container      = this.$lightbox.find('.lb-container');

					// Store css values for future lookup
					this.containerTopPadding = parseInt(this.$container.css('padding-top'), 10);
					this.containerRightPadding = parseInt(this.$container.css('padding-right'), 10);
					this.containerBottomPadding = parseInt(this.$container.css('padding-bottom'), 10);
					this.containerLeftPadding = parseInt(this.$container.css('padding-left'), 10);

					// Attach event handlers to the newly minted DOM elements
					this.$overlay.hide().on('click', function() {
						self.end();
						return false;
					});

					this.$lightbox.hide().on('click', function(event) {
						if ($(event.target).attr('id') === 'kt-lightbox') {
							self.end();
						}
						return false;
					});

					this.$outerContainer.on('click', function(event) {
						if ($(event.target).attr('id') === 'kt-lightbox') {
							self.end();
						}
						return false;
					});

					this.$lightbox.find('.lb-prev').on('click', function() {
						if (self.currentImageIndex === 0) {
							self.changeImage(self.album.length - 1);
						} else {
							self.changeImage(self.currentImageIndex - 1);
						}
						return false;
					});

					this.$lightbox.find('.lb-next').on('click', function() {
						if (self.currentImageIndex === self.album.length - 1) {
							self.changeImage(0);
						} else {
							self.changeImage(self.currentImageIndex + 1);
						}
						return false;
					});

					this.$lightbox.find('.lb-loader, .lb-close').on('click', function() {
						self.end();
						return false;
					});
				};

				// Show overlay and lightbox. If the image is part of a set, add siblings to album array.
				Lightbox.prototype.start = function($link) {
					var self    = this;
					var $window = $(window);

					$window.on('resize', $.proxy(this.sizeOverlay, this));

					$('select, object, embed').css({
						visibility: 'hidden'
					});

					this.sizeOverlay();

					this.album = [];
					var imageNumber = 0;

					function addToAlbum($link) {
						self.album.push({
							link: $link.attr('href'),
							title: $link.attr('data-title') || $link.attr('title')
						});
					}

					// Support both data-lightbox attribute and rel attribute implementations
					var dataLightboxValue = $link.attr('data-lightbox');
					var $links;

					if (dataLightboxValue) {
						$links = $($link.prop('tagName') + '[data-lightbox="' + dataLightboxValue + '"]');
						for (var i = 0; i < $links.length; i = ++i) {
							addToAlbum($($links[i]));
							if ($links[i] === $link[0]) {
								imageNumber = i;
							}
						}
					} else {
						if ($link.attr('rel') === 'lightbox') {
							// If image is not part of a set
							addToAlbum($link);
						} else {
							// If image is part of a set
							$links = $($link.prop('tagName') + '[rel="' + $link.attr('rel') + '"]');
							for (var j = 0; j < $links.length; j = ++j) {
								addToAlbum($($links[j]));
								if ($links[j] === $link[0]) {
									imageNumber = j;
								}
							}
						}
					}

					// Position Lightbox
					var top  = $window.scrollTop() + this.options.positionFromTop;
					var left = $window.scrollLeft();
					this.$lightbox.css({
						top: top + 'px',
						left: left + 'px'
					}).fadeIn(this.options.fadeDuration);

					this.changeImage(imageNumber);
				};

				// Hide most UI elements in preparation for the animated resizing of the lightbox.
				Lightbox.prototype.changeImage = function(imageNumber) {
					var self = this;

					this.disableKeyboardNav();
					var $image = this.$lightbox.find('.lb-image');

					this.$overlay.fadeIn(this.options.fadeDuration);

					$('.lb-loader').fadeIn('slow');
					this.$lightbox.find('.lb-image, .lb-nav, .lb-prev, .lb-next, .lb-dataContainer, .lb-numbers, .lb-caption').hide();

					this.$outerContainer.addClass('animating');

					// When image to show is preloaded, we send the width and height to sizeContainer()
					var preloader = new Image();
					preloader.onload = function() {
						var $preloader;
						var imageHeight;
						var imageWidth;
						var maxImageHeight;
						var maxImageWidth;
						var windowHeight;
						var windowWidth;

						$image.attr('src', self.album[imageNumber].link);

						$preloader = $(preloader);

						$image.width(preloader.width);
						$image.height(preloader.height);

						if (self.options.fitImagesInViewport) {
							// Fit image inside the viewport.
							// Take into account the border around the image and an additional 10px gutter on each side.

							windowWidth    = $(window).width();
							windowHeight   = $(window).height();
							maxImageWidth  = windowWidth - self.containerLeftPadding - self.containerRightPadding - 20;
							maxImageHeight = windowHeight - self.containerTopPadding - self.containerBottomPadding - 120;

							// Check if image size is larger then maxWidth|maxHeight in settings
							if (self.options.maxWidth && self.options.maxWidth < maxImageWidth) {
								maxImageWidth = self.options.maxWidth;
							}
							if (self.options.maxHeight && self.options.maxHeight < maxImageWidth) {
								maxImageHeight = self.options.maxHeight;
							}

							// Is there a fitting issue?
							if ((preloader.width > maxImageWidth) || (preloader.height > maxImageHeight)) {
								if ((preloader.width / maxImageWidth) > (preloader.height / maxImageHeight)) {
									imageWidth  = maxImageWidth;
									imageHeight = parseInt(preloader.height / (preloader.width / imageWidth), 10);
									$image.width(imageWidth);
									$image.height(imageHeight);
								} else {
									imageHeight = maxImageHeight;
									imageWidth = parseInt(preloader.width / (preloader.height / imageHeight), 10);
									$image.width(imageWidth);
									$image.height(imageHeight);
								}
							}
						}
						self.sizeContainer($image.width(), $image.height());
					};

					preloader.src          = this.album[imageNumber].link;
					this.currentImageIndex = imageNumber;
				};

				// Stretch overlay to fit the viewport
				Lightbox.prototype.sizeOverlay = function() {
					this.$overlay
						.width($(window).width())
						.height($(document).height());
				};

				// Animate the size of the lightbox to fit the image we are showing
				Lightbox.prototype.sizeContainer = function(imageWidth, imageHeight) {
					var self = this;

					var oldWidth  = this.$outerContainer.outerWidth();
					var oldHeight = this.$outerContainer.outerHeight();
					var newWidth  = imageWidth + this.containerLeftPadding + this.containerRightPadding;
					var newHeight = imageHeight + this.containerTopPadding + this.containerBottomPadding;

					function postResize() {
						self.$lightbox.find('.lb-dataContainer').width(newWidth);
						self.$lightbox.find('.lb-prevLink').height(newHeight);
						self.$lightbox.find('.lb-nextLink').height(newHeight);
						self.showImage();
					}

					if (oldWidth !== newWidth || oldHeight !== newHeight) {
						this.$outerContainer.animate({
							width: newWidth,
							height: newHeight
						}, this.options.resizeDuration, 'swing', function() {
							postResize();
						});
					} else {
						postResize();
					}
				};

				// Display the image and its details and begin preload neighboring images.
				Lightbox.prototype.showImage = function() {
					this.$lightbox.find('.lb-loader').stop(true).hide();
					this.$lightbox.find('.lb-image').fadeIn('slow');

					this.updateNav();
					this.updateDetails();
					this.preloadNeighboringImages();
					this.enableKeyboardNav();
				};

				// Display previous and next navigation if appropriate.
				Lightbox.prototype.updateNav = function() {
					// Check to see if the browser supports touch events. If so, we take the conservative approach
					// and assume that mouse hover events are not supported and always show prev/next navigation
					// arrows in image sets.
					var alwaysShowNav = false;
					try {
						document.createEvent('TouchEvent');
						alwaysShowNav = (this.options.alwaysShowNavOnTouchDevices) ? true : false;
					} catch (e) {}

					this.$lightbox.find('.lb-nav').show();

					if (this.album.length > 1) {
						if (this.options.wrapAround) {
							if (alwaysShowNav) {
								this.$lightbox.find('.lb-prev, .lb-next').css('opacity', '1');
							}
							this.$lightbox.find('.lb-prev, .lb-next').show();
						} else {
							if (this.currentImageIndex > 0) {
								this.$lightbox.find('.lb-prev').show();
								if (alwaysShowNav) {
									this.$lightbox.find('.lb-prev').css('opacity', '1');
								}
							}
							if (this.currentImageIndex < this.album.length - 1) {
								this.$lightbox.find('.lb-next').show();
								if (alwaysShowNav) {
									this.$lightbox.find('.lb-next').css('opacity', '1');
								}
							}
						}
					}
				};

				// Display caption, image number, and closing button.
				Lightbox.prototype.updateDetails = function() {
					var self = this;

					// Enable anchor clicks in the injected caption html.
					// Thanks Nate Wright for the fix. @https://github.com/NateWr
					if (typeof this.album[this.currentImageIndex].title !== 'undefined' &&
						this.album[this.currentImageIndex].title !== '') {
						this.$lightbox.find('.lb-caption')
							.html(this.album[this.currentImageIndex].title)
							.fadeIn('fast')
							.find('a').on('click', function(event) {
								if ($(this).attr('target') !== undefined) {
									window.open($(this).attr('href'), $(this).attr('target'));
								} else {
									location.href = $(this).attr('href');
								}
							});
					}

					if (this.album.length > 1 && this.options.showImageNumberLabel) {
						var labelText = this.imageCountLabel(this.currentImageIndex + 1, this.album.length);
						this.$lightbox.find('.lb-number').text(labelText).fadeIn('fast');
					} else {
						this.$lightbox.find('.lb-number').hide();
					}

					this.$outerContainer.removeClass('animating');

					this.$lightbox.find('.lb-dataContainer').fadeIn(this.options.resizeDuration, function() {
						return self.sizeOverlay();
					});
				};

				// Preload previous and next images in set.
				Lightbox.prototype.preloadNeighboringImages = function() {
					if (this.album.length > this.currentImageIndex + 1) {
						var preloadNext = new Image();
						preloadNext.src = this.album[this.currentImageIndex + 1].link;
					}
					if (this.currentImageIndex > 0) {
						var preloadPrev = new Image();
						preloadPrev.src = this.album[this.currentImageIndex - 1].link;
					}
				};

				Lightbox.prototype.enableKeyboardNav = function() {
					$(document).on('keyup.keyboard', $.proxy(this.keyboardAction, this));
				};

				Lightbox.prototype.disableKeyboardNav = function() {
					$(document).off('.keyboard');
				};

				Lightbox.prototype.keyboardAction = function(event) {
					var KEYCODE_ESC        = 27;
					var KEYCODE_LEFTARROW  = 37;
					var KEYCODE_RIGHTARROW = 39;

					var keycode = event.keyCode;
					var key     = String.fromCharCode(keycode).toLowerCase();
					if (keycode === KEYCODE_ESC || key.match(/x|o|c/)) {
						this.end();
					} else if (key === 'p' || keycode === KEYCODE_LEFTARROW) {
						if (this.currentImageIndex !== 0) {
							this.changeImage(this.currentImageIndex - 1);
						} else if (this.options.wrapAround && this.album.length > 1) {
							this.changeImage(this.album.length - 1);
						}
					} else if (key === 'n' || keycode === KEYCODE_RIGHTARROW) {
						if (this.currentImageIndex !== this.album.length - 1) {
							this.changeImage(this.currentImageIndex + 1);
						} else if (this.options.wrapAround && this.album.length > 1) {
							this.changeImage(0);
						}
					}
				};

				// Closing time. :-(
				Lightbox.prototype.end = function() {
					this.disableKeyboardNav();
					$(window).off('resize', this.sizeOverlay);
					this.$lightbox.fadeOut(this.options.fadeDuration);
					this.$overlay.fadeOut(this.options.fadeDuration);
					$('select, object, embed').css({
						visibility: 'visible'
					});
				};

				return new Lightbox();
		}));

		module.resolve();

});
			Komento.module('site/vendors/prism', function($){

var module = this;

/**
 * Prism: Lightweight, robust, elegant syntax highlighting
 * MIT license http://www.opensource.org/licenses/mit-license.php/
 * @author Lea Verou http://lea.verou.me
 */(function(){var e=/\blang(?:uage)?-(?!\*)(\w+)\b/i,t=self.Prism={util:{type:function(e){return Object.prototype.toString.call(e).match(/\[object (\w+)\]/)[1]},clone:function(e){var n=t.util.type(e);switch(n){case"Object":var r={};for(var i in e)e.hasOwnProperty(i)&&(r[i]=t.util.clone(e[i]));return r;case"Array":return e.slice()}return e}},languages:{extend:function(e,n){var r=t.util.clone(t.languages[e]);for(var i in n)r[i]=n[i];return r},insertBefore:function(e,n,r,i){i=i||t.languages;var s=i[e],o={};for(var u in s)if(s.hasOwnProperty(u)){if(u==n)for(var a in r)r.hasOwnProperty(a)&&(o[a]=r[a]);o[u]=s[u]}return i[e]=o},DFS:function(e,n){for(var r in e){n.call(e,r,e[r]);t.util.type(e)==="Object"&&t.languages.DFS(e[r],n)}}},highlightAll:function(e,n){var r=document.querySelectorAll('code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code');for(var i=0,s;s=r[i++];)t.highlightElement(s,e===!0,n)},highlightElement:function(r,i,s){var o,u,a=r;while(a&&!e.test(a.className))a=a.parentNode;if(a){o=(a.className.match(e)||[,""])[1];u=t.languages[o]}if(!u)return;r.className=r.className.replace(e,"").replace(/\s+/g," ")+" language-"+o;a=r.parentNode;/pre/i.test(a.nodeName)&&(a.className=a.className.replace(e,"").replace(/\s+/g," ")+" language-"+o);var f=r.textContent;if(!f)return;f=f.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/\u00a0/g," ");var l={element:r,language:o,grammar:u,code:f};t.hooks.run("before-highlight",l);if(i&&self.Worker){var c=new Worker(t.filename);c.onmessage=function(e){l.highlightedCode=n.stringify(JSON.parse(e.data),o);t.hooks.run("before-insert",l);l.element.innerHTML=l.highlightedCode;s&&s.call(l.element);t.hooks.run("after-highlight",l)};c.postMessage(JSON.stringify({language:l.language,code:l.code}))}else{l.highlightedCode=t.highlight(l.code,l.grammar,l.language);t.hooks.run("before-insert",l);l.element.innerHTML=l.highlightedCode;s&&s.call(r);t.hooks.run("after-highlight",l)}},highlight:function(e,r,i){return n.stringify(t.tokenize(e,r),i)},tokenize:function(e,n,r){var i=t.Token,s=[e],o=n.rest;if(o){for(var u in o)n[u]=o[u];delete n.rest}e:for(var u in n){if(!n.hasOwnProperty(u)||!n[u])continue;var a=n[u],f=a.inside,l=!!a.lookbehind,c=0;a=a.pattern||a;for(var h=0;h<s.length;h++){var p=s[h];if(s.length>e.length)break e;if(p instanceof i)continue;a.lastIndex=0;var d=a.exec(p);if(d){l&&(c=d[1].length);var v=d.index-1+c,d=d[0].slice(c),m=d.length,g=v+m,y=p.slice(0,v+1),b=p.slice(g+1),w=[h,1];y&&w.push(y);var E=new i(u,f?t.tokenize(d,f):d);w.push(E);b&&w.push(b);Array.prototype.splice.apply(s,w)}}}return s},hooks:{all:{},add:function(e,n){var r=t.hooks.all;r[e]=r[e]||[];r[e].push(n)},run:function(e,n){var r=t.hooks.all[e];if(!r||!r.length)return;for(var i=0,s;s=r[i++];)s(n)}}},n=t.Token=function(e,t){this.type=e;this.content=t};n.stringify=function(e,r,i){if(typeof e=="string")return e;if(Object.prototype.toString.call(e)=="[object Array]")return e.map(function(t){return n.stringify(t,r,e)}).join("");var s={type:e.type,content:n.stringify(e.content,r,i),tag:"span",classes:["token",e.type],attributes:{},language:r,parent:i};s.type=="comment"&&(s.attributes.spellcheck="true");t.hooks.run("wrap",s);var o="";for(var u in s.attributes)o+=u+'="'+(s.attributes[u]||"")+'"';return"<"+s.tag+' class="'+s.classes.join(" ")+'" '+o+">"+s.content+"</"+s.tag+">"};if(!self.document){self.addEventListener("message",function(e){var n=JSON.parse(e.data),r=n.language,i=n.code;self.postMessage(JSON.stringify(t.tokenize(i,t.languages[r])));self.close()},!1);return}var r=document.getElementsByTagName("script");r=r[r.length-1];if(r){t.filename=r.src;document.addEventListener&&!r.hasAttribute("data-manual")&&document.addEventListener("DOMContentLoaded",t.highlightAll)}})();;
Prism.languages.markup={comment:/&lt;!--[\w\W]*?-->/g,prolog:/&lt;\?.+?\?>/,doctype:/&lt;!DOCTYPE.+?>/,cdata:/&lt;!\[CDATA\[[\w\W]*?]]>/i,tag:{pattern:/&lt;\/?[\w:-]+\s*(?:\s+[\w:-]+(?:=(?:("|')(\\?[\w\W])*?\1|\w+))?\s*)*\/?>/gi,inside:{tag:{pattern:/^&lt;\/?[\w:-]+/i,inside:{punctuation:/^&lt;\/?/,namespace:/^[\w-]+?:/}},"attr-value":{pattern:/=(?:('|")[\w\W]*?(\1)|[^\s>]+)/gi,inside:{punctuation:/=|>|"/g}},punctuation:/\/?>/g,"attr-name":{pattern:/[\w:-]+/g,inside:{namespace:/^[\w-]+?:/}}}},entity:/&amp;#?[\da-z]{1,8};/gi};Prism.hooks.add("wrap",function(e){e.type==="entity"&&(e.attributes.title=e.content.replace(/&amp;/,"&"))});;
Prism.languages.css={comment:/\/\*[\w\W]*?\*\//g,atrule:{pattern:/@[\w-]+?.*?(;|(?=\s*{))/gi,inside:{punctuation:/[;:]/g}},url:/url\((["']?).*?\1\)/gi,selector:/[^\{\}\s][^\{\};]*(?=\s*\{)/g,property:/(\b|\B)[\w-]+(?=\s*:)/ig,string:/("|')(\\?.)*?\1/g,important:/\B!important\b/gi,ignore:/&(lt|gt|amp);/gi,punctuation:/[\{\};:]/g};Prism.languages.markup&&Prism.languages.insertBefore("markup","tag",{style:{pattern:/(&lt;|<)style[\w\W]*?(>|&gt;)[\w\W]*?(&lt;|<)\/style(>|&gt;)/ig,inside:{tag:{pattern:/(&lt;|<)style[\w\W]*?(>|&gt;)|(&lt;|<)\/style(>|&gt;)/ig,inside:Prism.languages.markup.tag.inside},rest:Prism.languages.css}}});;
Prism.languages.css.selector={pattern:/[^\{\}\s][^\{\}]*(?=\s*\{)/g,inside:{"pseudo-element":/:(?:after|before|first-letter|first-line|selection)|::[-\w]+/g,"pseudo-class":/:[-\w]+(?:\(.*\))?/g,"class":/\.[-:\.\w]+/g,id:/#[-:\.\w]+/g}};Prism.languages.insertBefore("css","ignore",{hexcode:/#[\da-f]{3,6}/gi,entity:/\\[\da-f]{1,8}/gi,number:/[\d%\.]+/g,"function":/(attr|calc|cross-fade|cycle|element|hsla?|image|lang|linear-gradient|matrix3d|matrix|perspective|radial-gradient|repeating-linear-gradient|repeating-radial-gradient|rgba?|rotatex|rotatey|rotatez|rotate3d|rotate|scalex|scaley|scalez|scale3d|scale|skewx|skewy|skew|steps|translatex|translatey|translatez|translate3d|translate|url|var)/ig});;
Prism.languages.clike={comment:{pattern:/(^|[^\\])(\/\*[\w\W]*?\*\/|(^|[^:])\/\/.*?(\r?\n|$))/g,lookbehind:!0},string:/("|')(\\?.)*?\1/g,"class-name":{pattern:/((?:(?:class|interface|extends|implements|trait|instanceof|new)\s+)|(?:catch\s+\())[a-z0-9_\.\\]+/ig,lookbehind:!0,inside:{punctuation:/(\.|\\)/}},keyword:/\b(if|else|while|do|for|return|in|instanceof|function|new|try|throw|catch|finally|null|break|continue)\b/g,"boolean":/\b(true|false)\b/g,"function":{pattern:/[a-z0-9_]+\(/ig,inside:{punctuation:/\(/}}, number:/\b-?(0x[\dA-Fa-f]+|\d*\.?\d+([Ee]-?\d+)?)\b/g,operator:/[-+]{1,2}|!|&lt;=?|>=?|={1,3}|(&amp;){1,2}|\|?\||\?|\*|\/|\~|\^|\%/g,ignore:/&(lt|gt|amp);/gi,punctuation:/[{}[\];(),.:]/g};
;
Prism.languages.javascript=Prism.languages.extend("clike",{keyword:/\b(var|let|if|else|while|do|for|return|in|instanceof|function|new|with|typeof|try|throw|catch|finally|null|break|continue)\b/g,number:/\b-?(0x[\dA-Fa-f]+|\d*\.?\d+([Ee]-?\d+)?|NaN|-?Infinity)\b/g});Prism.languages.insertBefore("javascript","keyword",{regex:{pattern:/(^|[^/])\/(?!\/)(\[.+?]|\\.|[^/\r\n])+\/[gim]{0,3}(?=\s*($|[\r\n,.;})]))/g,lookbehind:!0}});Prism.languages.markup&&Prism.languages.insertBefore("markup","tag",{script:{pattern:/(&lt;|<)script[\w\W]*?(>|&gt;)[\w\W]*?(&lt;|<)\/script(>|&gt;)/ig,inside:{tag:{pattern:/(&lt;|<)script[\w\W]*?(>|&gt;)|(&lt;|<)\/script(>|&gt;)/ig,inside:Prism.languages.markup.tag.inside},rest:Prism.languages.javascript}}});
;
Prism.languages.php=Prism.languages.extend("clike",{keyword:/\b(and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|extends|private|protected|parent|static|throw|null|echo|print|trait|namespace|use|final|yield|goto|instanceof|finally|try|catch)\b/ig, constant:/\b[A-Z0-9_]{2,}\b/g});Prism.languages.insertBefore("php","keyword",{delimiter:/(\?>|&lt;\?php|&lt;\?)/ig,variable:/(\$\w+)\b/ig,"package":{pattern:/(\\|namespace\s+|use\s+)[\w\\]+/g,lookbehind:!0,inside:{punctuation:/\\/}}});Prism.languages.insertBefore("php","operator",{property:{pattern:/(->)[\w]+/g,lookbehind:!0}}); Prism.languages.markup&&(Prism.hooks.add("before-highlight",function(a){"php"===a.language&&(a.tokenStack=[],a.code=a.code.replace(/(?:&lt;\?php|&lt;\?|<\?php|<\?)[\w\W]*?(?:\?&gt;|\?>)/ig,function(b){a.tokenStack.push(b);return"{{{PHP"+a.tokenStack.length+"}}}"}))}),Prism.hooks.add("after-highlight",function(a){if("php"===a.language){for(var b=0,c;c=a.tokenStack[b];b++)a.highlightedCode=a.highlightedCode.replace("{{{PHP"+(b+1)+"}}}",Prism.highlight(c,a.grammar,"php"));a.element.innerHTML=a.highlightedCode}}), Prism.hooks.add("wrap",function(a){"php"===a.language&&"markup"===a.type&&(a.content=a.content.replace(/(\{\{\{PHP[0-9]+\}\}\})/g,'<span class="token php">$1</span>'))}),Prism.languages.insertBefore("php","comment",{markup:{pattern:/(&lt;|<)[^?]\/?(.*?)(>|&gt;)/g,inside:Prism.languages.markup},php:/\{\{\{PHP[0-9]+\}\}\}/g}));;
Prism.languages.insertBefore("php","variable",{"this":/\$this/g,global:/\$_?(GLOBALS|SERVER|GET|POST|FILES|REQUEST|SESSION|ENV|COOKIE|HTTP_RAW_POST_DATA|argc|argv|php_errormsg|http_response_header)/g,scope:{pattern:/\b[\w\\]+::/g,inside:{keyword:/(static|self|parent)/,punctuation:/(::|\\)/}}});;
Prism.languages.coffeescript=Prism.languages.extend("javascript",{"block-comment":/([#]{3}\s*\r?\n(.*\s*\r*\n*)\s*?\r?\n[#]{3})/g,comment:/(\s|^)([#]{1}[^#^\r^\n]{2,}?(\r?\n|$))/g,keyword:/\b(this|window|delete|class|extends|namespace|extend|ar|let|if|else|while|do|for|each|of|return|in|instanceof|new|with|typeof|try|catch|finally|null|undefined|break|continue)\b/g});Prism.languages.insertBefore("coffeescript","keyword",{"function":{pattern:/[a-z|A-z]+\s*[:|=]\s*(\([.|a-z\s|,|:|{|}|\"|\'|=]*\))?\s*-&gt;/gi,inside:{"function-name":/[_?a-z-|A-Z-]+(\s*[:|=])| @[_?$?a-z-|A-Z-]+(\s*)| /g,operator:/[-+]{1,2}|!|=?&lt;|=?&gt;|={1,2}|(&amp;){1,2}|\|?\||\?|\*|\//g}},"attr-name":/[_?a-z-|A-Z-]+(\s*:)| @[_?$?a-z-|A-Z-]+(\s*)| /g});;
Prism.languages.scss=Prism.languages.extend("css",{comment:{pattern:/(^|[^\\])(\/\*[\w\W]*?\*\/|\/\/.*?(\r?\n|$))/g,lookbehind:!0},atrule:/@[\w-]+(?=\s+(\(|\{|;))/gi,url:/([-a-z]+-)*url(?=\()/gi,selector:/([^@;\{\}\(\)]?([^@;\{\}\(\)]|&amp;|\#\{\$[-_\w]+\})+)(?=\s*\{(\}|\s|[^\}]+(:|\{)[^\}]+))/gm});Prism.languages.insertBefore("scss","atrule",{keyword:/@(if|else if|else|for|each|while|import|extend|debug|warn|mixin|include|function|return)|(?=@for\s+\$[-_\w]+\s)+from/i});Prism.languages.insertBefore("scss","property",{variable:/((\$[-_\w]+)|(#\{\$[-_\w]+\}))/i});Prism.languages.insertBefore("scss","ignore",{placeholder:/%[-_\w]+/i,statement:/\B!(default|optional)\b/gi,"boolean":/\b(true|false)\b/g,"null":/\b(null)\b/g,operator:/\s+([-+]{1,2}|={1,2}|!=|\|?\||\?|\*|\/|\%)\s+/g});
;
Prism.languages.bash=Prism.languages.extend("clike",{comment:{pattern:/(^|[^"{\\])(#.*?(\r?\n|$))/g,lookbehind:!0},string:{pattern:/("|')(\\?[\s\S])*?\1/g,inside:{property:/\$([a-zA-Z0-9_#\?\-\*!@]+|\{[^\}]+\})/g}},keyword:/\b(if|then|else|elif|fi|for|break|continue|while|in|case|function|select|do|done|until|echo|exit|return|set|declare)\b/g});Prism.languages.insertBefore("bash","keyword",{property:/\$([a-zA-Z0-9_#\?\-\*!@]+|\{[^}]+\})/g});Prism.languages.insertBefore("bash","comment",{important:/(^#!\s*\/bin\/bash)|(^#!\s*\/bin\/sh)/g});
;
Prism.languages.c=Prism.languages.extend("clike",{keyword:/\b(asm|typeof|inline|auto|break|case|char|const|continue|default|do|double|else|enum|extern|float|for|goto|if|int|long|register|return|short|signed|sizeof|static|struct|switch|typedef|union|unsigned|void|volatile|while)\b/g,operator:/[-+]{1,2}|!=?|&lt;{1,2}=?|&gt;{1,2}=?|\-&gt;|={1,2}|\^|~|%|(&amp;){1,2}|\|?\||\?|\*|\//g});Prism.languages.insertBefore("c","keyword",{property:/#\s*[a-zA-Z]+/g});
;
Prism.languages.cpp=Prism.languages.extend("c",{keyword:/\b(alignas|alignof|asm|auto|bool|break|case|catch|char|char16_t|char32_t|class|compl|const|constexpr|const_cast|continue|decltype|default|delete|delete\[\]|do|double|dynamic_cast|else|enum|explicit|export|extern|float|for|friend|goto|if|inline|int|long|mutable|namespace|new|new\[\]|noexcept|nullptr|operator|private|protected|public|register|reinterpret_cast|return|short|signed|sizeof|static|static_assert|static_cast|struct|switch|template|this|thread_local|throw|try|typedef|typeid|typename|union|unsigned|using|virtual|void|volatile|wchar_t|while)\b/g,
operator:/[-+]{1,2}|!=?|&lt;{1,2}=?|&gt;{1,2}=?|\-&gt;|:{1,2}|={1,2}|\^|~|%|(&amp;){1,2}|\|?\||\?|\*|\/|\b(and|and_eq|bitand|bitor|not|not_eq|or|or_eq|xor|xor_eq)\b/g});
;
Prism.languages.sql={comment:{pattern:/(^|[^\\])(\/\*[\w\W]*?\*\/|((--)|(\/\/)).*?(\r?\n|$))/g,lookbehind:!0},string: /("|')(\\?.)*?\1/g,keyword:/\b(ACTION|ADD|AFTER|ALGORITHM|ALTER|ANALYZE|APPLY|AS|AS|ASC|AUTHORIZATION|BACKUP|BDB|BEGIN|BERKELEYDB|BIGINT|BINARY|BIT|BLOB|BOOL|BOOLEAN|BREAK|BROWSE|BTREE|BULK|BY|CALL|CASCADE|CASCADED|CASE|CHAIN|CHAR VARYING|CHARACTER VARYING|CHECK|CHECKPOINT|CLOSE|CLUSTERED|COALESCE|COLUMN|COLUMNS|COMMENT|COMMIT|COMMITTED|COMPUTE|CONNECT|CONSISTENT|CONSTRAINT|CONTAINS|CONTAINSTABLE|CONTINUE|CONVERT|CREATE|CROSS|CURRENT|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|DATA|DATABASE|DATABASES|DATETIME|DBCC|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT|DEFINER|DELAYED|DELETE|DENY|DESC|DESCRIBE|DETERMINISTIC|DISABLE|DISCARD|DISK|DISTINCT|DISTINCTROW|DISTRIBUTED|DO|DOUBLE|DOUBLE PRECISION|DROP|DUMMY|DUMP|DUMPFILE|DUPLICATE KEY|ELSE|ENABLE|ENCLOSED BY|END|ENGINE|ENUM|ERRLVL|ERRORS|ESCAPE|ESCAPED BY|EXCEPT|EXEC|EXECUTE|EXIT|EXPLAIN|EXTENDED|FETCH|FIELDS|FILE|FILLFACTOR|FIRST|FIXED|FLOAT|FOLLOWING|FOR|FOR EACH ROW|FORCE|FOREIGN|FREETEXT|FREETEXTTABLE|FROM|FULL|FUNCTION|GEOMETRY|GEOMETRYCOLLECTION|GLOBAL|GOTO|GRANT|GROUP|HANDLER|HASH|HAVING|HOLDLOCK|IDENTITY|IDENTITY_INSERT|IDENTITYCOL|IF|IGNORE|IMPORT|INDEX|INFILE|INNER|INNODB|INOUT|INSERT|INT|INTEGER|INTERSECT|INTO|INVOKER|ISOLATION LEVEL|JOIN|KEY|KEYS|KILL|LANGUAGE SQL|LAST|LEFT|LIMIT|LINENO|LINES|LINESTRING|LOAD|LOCAL|LOCK|LONGBLOB|LONGTEXT|MATCH|MATCHED|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MERGE|MIDDLEINT|MODIFIES SQL DATA|MODIFY|MULTILINESTRING|MULTIPOINT|MULTIPOLYGON|NATIONAL|NATIONAL CHAR VARYING|NATIONAL CHARACTER|NATIONAL CHARACTER VARYING|NATIONAL VARCHAR|NATURAL|NCHAR|NCHAR VARCHAR|NEXT|NO|NO SQL|NOCHECK|NOCYCLE|NONCLUSTERED|NULLIF|NUMERIC|OF|OFF|OFFSETS|ON|OPEN|OPENDATASOURCE|OPENQUERY|OPENROWSET|OPTIMIZE|OPTION|OPTIONALLY|ORDER|OUT|OUTER|OUTFILE|OVER|PARTIAL|PARTITION|PERCENT|PIVOT|PLAN|POINT|POLYGON|PRECEDING|PRECISION|PREV|PRIMARY|PRINT|PRIVILEGES|PROC|PROCEDURE|PUBLIC|PURGE|QUICK|RAISERROR|READ|READS SQL DATA|READTEXT|REAL|RECONFIGURE|REFERENCES|RELEASE|RENAME|REPEATABLE|REPLICATION|REQUIRE|RESTORE|RESTRICT|RETURN|RETURNS|REVOKE|RIGHT|ROLLBACK|ROUTINE|ROWCOUNT|ROWGUIDCOL|ROWS?|RTREE|RULE|SAVE|SAVEPOINT|SCHEMA|SELECT|SERIAL|SERIALIZABLE|SESSION|SESSION_USER|SET|SETUSER|SHARE MODE|SHOW|SHUTDOWN|SIMPLE|SMALLINT|SNAPSHOT|SOME|SONAME|START|STARTING BY|STATISTICS|STATUS|STRIPED|SYSTEM_USER|TABLE|TABLES|TABLESPACE|TEMPORARY|TEMPTABLE|TERMINATED BY|TEXT|TEXTSIZE|THEN|TIMESTAMP|TINYBLOB|TINYINT|TINYTEXT|TO|TOP|TRAN|TRANSACTION|TRANSACTIONS|TRIGGER|TRUNCATE|TSEQUAL|TYPE|TYPES|UNBOUNDED|UNCOMMITTED|UNDEFINED|UNION|UNPIVOT|UPDATE|UPDATETEXT|USAGE|USE|USER|USING|VALUE|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|VIEW|WAITFOR|WARNINGS|WHEN|WHERE|WHILE|WITH|WITH ROLLUP|WITHIN|WORK|WRITE|WRITETEXT)\b/gi,boolean:/\b(TRUE|FALSE|NULL)\b/gi,number:/\b-?(0x)?\d*\.?[\da-f]+\b/g,operator:/\b(ALL|AND|ANY|BETWEEN|EXISTS|IN|LIKE|NOT|OR|IS|UNIQUE|CHARACTER SET|COLLATE|DIV|OFFSET|REGEXP|RLIKE|SOUNDS LIKE|XOR)\b|[-+]{1}|!|=?&lt;|=?&gt;|={1}|(&amp;){1,2}|\|?\||\?|\*|\//gi,ignore:/&(lt|gt|amp);/gi,punctuation:/[;[\]()`,.]/g};;
Prism.languages.http={"request-line":{pattern:/^(POST|GET|PUT|DELETE|OPTIONS|PATCH|TRACE|CONNECT)\b\shttps?:\/\/\S+\sHTTP\/[0-9.]+/g,inside:{property:/^\b(POST|GET|PUT|DELETE|OPTIONS|PATCH|TRACE|CONNECT)\b/g,"attr-name":/:\w+/g}},"response-status":{pattern:/^HTTP\/1.[01] [0-9]+.*/g,inside:{property:/[0-9]+[A-Z\s-]+$/g}},keyword:/^[\w-]+:(?=.+)/gm};var httpLanguages={"application/json":Prism.languages.javascript,"application/xml":Prism.languages.markup,"text/xml":Prism.languages.markup,"text/html":Prism.languages.markup};for(var contentType in httpLanguages){if(httpLanguages[contentType]){var options={};options[contentType]={pattern:new RegExp("(content-type:\\s*"+contentType+"[\\w\\W]*?)\\n\\n[\\w\\W]*","gi"),lookbehind:true,inside:{rest:httpLanguages[contentType]}};Prism.languages.insertBefore("http","keyword",options)}}
;
Prism.languages.ruby=Prism.languages.extend("clike",{comment:/#[^\r\n]*(\r?\n|$)/g,keyword:/\b(alias|and|BEGIN|begin|break|case|class|def|define_method|defined|do|each|else|elsif|END|end|ensure|false|for|if|in|module|new|next|nil|not|or|raise|redo|require|rescue|retry|return|self|super|then|throw|true|undef|unless|until|when|while|yield)\b/g,builtin:/\b(Array|Bignum|Binding|Class|Continuation|Dir|Exception|FalseClass|File|Stat|File|Fixnum|Fload|Hash|Integer|IO|MatchData|Method|Module|NilClass|Numeric|Object|Proc|Range|Regexp|String|Struct|TMS|Symbol|ThreadGroup|Thread|Time|TrueClass)\b/,constant:/\b[A-Z][a-zA-Z_0-9]*[?!]?\b/g});Prism.languages.insertBefore("ruby","keyword",{regex:{pattern:/(^|[^/])\/(?!\/)(\[.+?]|\\.|[^/\r\n])+\/[gim]{0,3}(?=\s*($|[\r\n,.;})]))/g,lookbehind:true},variable:/[@$&]+\b[a-zA-Z_][a-zA-Z_0-9]*[?!]?\b/g,symbol:/:\b[a-zA-Z_][a-zA-Z_0-9]*[?!]?\b/g})
;
Prism.languages.csharp=Prism.languages.extend("clike",{keyword:/\b(abstract|as|base|bool|break|byte|case|catch|char|checked|class|const|continue|decimal|default|delegate|do|double|else|enum|event|explicit|extern|false|finally|fixed|float|for|foreach|goto|if|implicit|in|int|interface|internal|is|lock|long|namespace|new|null|object|operator|out|override|params|private|protected|public|readonly|ref|return|sbyte|sealed|short|sizeof|stackalloc|static|string|struct|switch|this|throw|true|try|typeof|uint|ulong|unchecked|unsafe|ushort|using|virtual|void|volatile|while|add|alias|ascending|async|await|descending|dynamic|from|get|global|group|into|join|let|orderby|partial|remove|select|set|value|var|where|yield)\b/g,string:/@?("|')(\\?.)*?\1/g,preprocessor:/^\s*#.*/gm,number:/\b-?(0x)?\d*\.?\d+\b/g});
	
	Prism.highlightAll();
	
	module.resolve();
});	});
