/**
 * @package         Regular Labs Library
 * @version         23.9.3039
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

"use strict";

if (typeof window.RegularLabsCodeMirror === 'undefined'
    || typeof RegularLabsCodeMirror.version === 'undefined'
    || RegularLabsCodeMirror.version < '23.9.3039') {

    (function($) {
        window.RegularLabsCodeMirror = {
            version: '23.9.3039',

            init: function(id) {
                if ( ! $(`#rl_codemirror_${id} .CodeMirror`).length) {
                    setTimeout(function() {
                        RegularLabsCodeMirror.init(id);
                    }, 100);
                    return;
                }

                RegularLabsCodeMirror.resizeWidth(id);
                cmResize(Joomla.editors.instances[id], {
                    minHeight      : 50,
                    resizableWidth : false,        //Which direction the editor can be resized (default: both width and height).
                    resizableHeight: true,
                    cssClass       : 'cm-resize-handle' //CSS class to use on the *default* resize handle.
                });

                $(window).resize(function() {
                    RegularLabsCodeMirror.resizeWidth(id);
                });
            },

            resizeWidth: function(id) {
                $(`#rl_codemirror_${id} .CodeMirror`).width(100).css('visibility', 'hidden');
                setTimeout(function() {
                    $(`#rl_codemirror_${id} .CodeMirror`).each(function() {
                        var new_width = $(this).parent().width();

                        if (new_width <= 100) {
                            setTimeout(function() {
                                RegularLabsCodeMirror.resizeWidth(id);
                            }, 100);
                            return;
                        }

                        $(this).width(new_width).css('visibility', 'visible');
                    });
                }, 100);
            }
        };
    })(jQuery);

    (function(global, factory) {
        typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
            typeof define === 'function' && define.amd ? define(factory) :
                (global.cmResize = factory());
    }(this, (function() {
        'use strict';

        function dragTracker(options) {

            var ep = Element.prototype;
            if ( ! ep.matches) {
                ep.matches = ep.msMatchesSelector || ep.webkitMatchesSelector;
            }
            if ( ! ep.closest) {
                ep.closest = function(s) {
                    var node = this;
                    do {
                        if (node.matches(s)) {
                            return node;
                        }
                        node = node.tagName === 'svg' ? node.parentNode : node.parentElement;
                    } while (node);

                    return null;
                };
            }

            options            = options || {};
            var container      = options.container || document.documentElement,
                selector       = options.selector,
                callback       = options.callback || console.log,
                callbackStart  = options.callbackDragStart,
                callbackEnd    = options.callbackDragEnd,

                callbackClick  = options.callbackClick,
                propagate      = options.propagateEvents,
                roundCoords    = options.roundCoords !== false,
                dragOutside    = options.dragOutside !== false,

                handleOffset   = options.handleOffset || options.handleOffset !== false;
            var offsetToCenter = null;
            switch (handleOffset) {
                case 'center':
                    offsetToCenter = true;
                    break;
                case 'topleft':
                case 'top-left':
                    offsetToCenter = false;
                    break;
            }

            var dragged     = void 0,
                mouseOffset = void 0,
                dragStart   = void 0;

            function getMousePos(e, elm, offset, stayWithin) {
                var x = e.clientX,
                    y = e.clientY;

                function respectBounds(value, min, max) {
                    return Math.max(min, Math.min(value, max));
                }

                if (elm) {
                    var bounds = elm.getBoundingClientRect();
                    x -= bounds.left;
                    y -= bounds.top;

                    if (offset) {
                        x -= offset[0];
                        y -= offset[1];
                    }
                    if (stayWithin) {
                        x = respectBounds(x, 0, bounds.width);
                        y = respectBounds(y, 0, bounds.height);
                    }

                    if (elm !== container) {
                        var center = offsetToCenter !== null ? offsetToCenter
                            : elm.nodeName === 'circle' || elm.nodeName === 'ellipse';

                        if (center) {
                            x -= bounds.width / 2;
                            y -= bounds.height / 2;
                        }
                    }
                }
                return roundCoords ? [Math.round(x), Math.round(y)] : [x, y];
            }

            function stopEvent(e) {
                e.preventDefault();
                if ( ! propagate) {
                    e.stopPropagation();
                }
            }

            function onDown(e) {
                if (selector) {
                    dragged = selector instanceof Element ? selector.contains(e.target) ? selector : null : e.target.closest(selector);
                } else {
                    dragged = {};
                }

                if (dragged) {
                    stopEvent(e);

                    mouseOffset = selector && handleOffset ? getMousePos(e, dragged) : [0, 0];
                    dragStart   = getMousePos(e, container, mouseOffset);
                    if (roundCoords) {
                        dragStart = dragStart.map(Math.round);
                    }

                    if (callbackStart) {
                        callbackStart(dragged, dragStart);
                    }
                }
            }

            function onMove(e) {
                if ( ! dragged) {
                    return;
                }
                stopEvent(e);

                var pos = getMousePos(e, container, mouseOffset, ! dragOutside);
                callback(dragged, pos, dragStart);
            }

            function onEnd(e) {
                if ( ! dragged) {
                    return;
                }

                if (callbackEnd || callbackClick) {
                    var pos = getMousePos(e, container, mouseOffset, ! dragOutside);

                    if (callbackClick && dragStart[0] === pos[0] && dragStart[1] === pos[1]) {
                        callbackClick(dragged, dragStart);
                    }
                    if (callbackEnd) {
                        callbackEnd(dragged, pos, dragStart);
                    }
                }
                dragged = null;
            }

            container.addEventListener('mousedown', function(e) {
                if (isLeftButton(e)) {
                    onDown(e);
                }
            });
            container.addEventListener('touchstart', function(e) {
                relayTouch(e, onDown);
            });

            window.addEventListener('mousemove', function(e) {
                if ( ! dragged) {
                    return;
                }

                if (isLeftButton(e)) {
                    onMove(e);
                } else {
                    onEnd(e);
                }
            });
            window.addEventListener('touchmove', function(e) {
                relayTouch(e, onMove);
            });

            window.addEventListener('mouseup', function(e) {
                if (dragged && ! isLeftButton(e)) {
                    onEnd(e);
                }
            });

            function onTouchEnd(e) {
                onEnd(tweakTouch(e));
            }

            container.addEventListener('touchend', onTouchEnd);
            container.addEventListener('touchcancel', onTouchEnd);

            function isLeftButton(e) {
                return e.buttons !== undefined ? e.buttons === 1 :
                    e.which === 1;
            }

            function relayTouch(e, handler) {
                if (e.touches.length !== 1) {
                    onEnd(e);
                    return;
                }

                handler(tweakTouch(e));
            }

            function tweakTouch(e) {
                var touch = e.targetTouches[0];
                if ( ! touch) {
                    touch = e.changedTouches[0];
                }

                touch.preventDefault  = e.preventDefault.bind(e);
                touch.stopPropagation = e.stopPropagation.bind(e);
                return touch;
            }
        }

        function cmResize(cm, config) {
            config = config || {};

            var minW    = config.minWidth || 200,
                minH    = config.minHeight || 100,
                resizeW = config.resizableWidth !== false,
                resizeH = config.resizableHeight !== false,
                css     = config.cssClass || 'cm-resize-handle';

            var cmElement = cm.display.wrapper,
                cmHandle  = config.handle || function() {
                    var h       = cmElement.appendChild(document.createElement('div'));
                    h.className = css;
                    return h;
                }();

            var vScroll = cmElement.querySelector('.CodeMirror-vscrollbar'),
                hScroll = cmElement.querySelector('.CodeMirror-hscrollbar');

            function constrainScrollbars() {
                if ( ! config.handle) {
                    vScroll.style.bottom = '18px';
                    hScroll.style.right  = '18px';
                }
            }

            cm.on('update', constrainScrollbars);
            constrainScrollbars();

            var startPos  = void 0,
                startSize = void 0;
            dragTracker({
                container: cmHandle.offsetParent,
                selector : cmHandle,

                callbackDragStart: function callbackDragStart(handle, pos) {
                    startPos  = pos;
                    startSize = [cmElement.clientWidth, cmElement.clientHeight];
                },
                callback         : function callback(handle, pos) {
                    var diffX = pos[0] - startPos[0],
                        diffY = pos[1] - startPos[1],
                        cw    = resizeW ? Math.max(minW, startSize[0] + diffX) : null,
                        ch    = resizeH ? Math.max(minH, startSize[1] + diffY) : null;

                    cm.setSize(cw, ch);
                }
            });

            return cmHandle;
        }

        return cmResize;

    })));
}
