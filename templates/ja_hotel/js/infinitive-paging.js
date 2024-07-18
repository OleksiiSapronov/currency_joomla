/**
 * ------------------------------------------------------------------------
 * JA Jason template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

 // Pagination using infinitive-scroll. Support 2 mode, manual & auto
 
(function($){
  $(document).ready(function(){
    var $container = $('.ja-masonry-wrap #grid'),
                nextbtn = $('#infinity-next');

    if (!$container.length || !nextbtn.length || !nextbtn.data('mode')) return ;
    
    // hide default joomla pagination
    $('div.pagination').hide();
    // show next button
    if (nextbtn.data('mode') == 'manual') nextbtn.show().removeClass ('hide');
    
    $container.infinitescroll({
          navSelector  : 'ul.pagination',    // selector for the paged navigation
          nextSelector : 'ul.pagination > li:last > a',  // selector for the NEXT link (to page 2)
          itemSelector : '.item',     // selector for all items you'll retrieve
          loading: {
              finished: function(){
                  $('#infscr-loading').remove();
                  nextbtn.removeClass ('loading');
                  var instance = $(this).data('infinitescroll');
                  if (instance && instance.options.state.currPage >= instance.options.maxPage) {
                    nextbtn.removeClass('hide').addClass('disabled').html(nextbtn.data('finishedmsg'));
                    $container.infinitescroll('destroy');
                  }
              },
              finishedMsg: nextbtn.data('finishedmsg'),
              msgText : '',
              speed : 'fast',
              start: function (opts) {
                nextbtn.addClass ('loading');
                var instance = $(this).data('infinitescroll');
                if (!instance) return ;                
                instance.beginAjax(opts);
              }
          },
          state: {
              isDuringAjax: false,
              isInvalidPage: false,
              isDestroyed: false,
              isDone: false, // For when it goes all the way through the archive.
              isPaused: nextbtn.data ('mode') == 'manual' ? true : false,
              currPage: 1
          },
          binder: $container,
          path: function (page) {
            return $('ul.pagination > li > a').eq(page+1).attr('href');
          },
          binder: $(window), // used to cache the selector for the element that will be scrolling
          extraScrollPx: 150,
          dataType: 'html',
          appendCallback: true,
          bufferPx: 350,
          debug : false,
          errorCallback: function () {
              nextbtn.removeClass('hidden').addClass('disabled').html(nextbtn.data('finishedmsg'));
              $container.infinitescroll('destroy');
          },
          prefill: false, // When the document is smaller than the window, load data until the document is larger or links are exhausted
          maxPage: parseInt(nextbtn.data('pages')) // to manually control maximum page (when maxPage is undefined, maximum page limitation is not work)
      },
      // call Isotope as a callback
      function( items ) {
        if ($container.data ('isotope')) {
          $container.isotope( 'appended', $( items ) );
        }

        //update disqus if needed
        if(typeof DISQUSWIDGETS != 'undefined'){
            DISQUSWIDGETS.getCount();
        }
        
        // update layout when image loaded
        $( items ).imagesLoaded (function() {
          $container.isotope();
        });
    });


    var btnEvent = 'ontouchstart' in document.documentElement ? 'touchstart' : 'click';
    if(nextbtn.length){
        nextbtn.on(btnEvent, function(){
            if(!nextbtn.hasClass('finished')){
                $container.infinitescroll('retrieve');
            }
            return false;
        });
    }        
  });

})(jQuery);
