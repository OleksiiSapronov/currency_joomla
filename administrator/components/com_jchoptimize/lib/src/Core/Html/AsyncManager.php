<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Html;

use JchOptimize\Core\FeatureHelpers\DynamicJs;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class AsyncManager
{
    protected string $onUserInteractFunction = '';

    protected string $onDomContentLoadedFunction = '';

    protected string $loadCssOnUIFunction = '';

    protected string $loadScriptOnUIFunction = '';

    protected string $loadReduceDomFunction = '';

    private Registry $params;

    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    public function loadCssAsync($cssUrls): void
    {
        $this->loadOnUIFunction();
        $sNoScriptUrls = \implode("\n", \array_map(function ($url) {
            // language=HTML
            return '<link rel="stylesheet" href="'.$url.'" />';
        }, $cssUrls));
        $aJsonEncodedUrlArray = $this->jsonEncodeUrlArray($cssUrls);
        $this->loadCssOnUIFunction = <<<HTML
<script>
let jch_css_loaded = false;

onUserInteract(function(){ 
\tconst css_urls = {$aJsonEncodedUrlArray};
        
\tif (!jch_css_loaded){
\t    \tcss_urls.forEach(function(url, index){
\t       \t\tlet l = document.createElement('link');
\t\t\tl.rel = 'stylesheet';
\t\t\tl.href = url;
\t\t\tlet h = document.getElementsByTagName('head')[0];
\t\t\th.append(l); 
\t    \t});
\t    
\t\tjch_css_loaded = true;
        }
});
</script>
<noscript>
{$sNoScriptUrls}
</noscript>
HTML;
    }

    public function printHeaderScript(): string
    {
        $this->loadJsDynamic(DynamicJs::$aJsDynamicUrls);
        $this->loadReduceDom();

        return $this->onUserInteractFunction."\n".$this->onDomContentLoadedFunction."\n".$this->loadCssOnUIFunction."\n".$this->loadScriptOnUIFunction."\n".$this->loadReduceDomFunction;
    }

    public function loadJsDynamic($jsUrls): void
    {
        if ($this->params->get('pro_reduce_unused_js_enable', '0') && !empty($jsUrls)) {
            $this->loadOnUIFunction();
            $aJsonEncodedUrlArray = $this->jsonEncodeUrlArray($jsUrls);
            $this->loadScriptOnUIFunction = <<<HTML
<script>
let jch_js_loaded = false;

const jchOptimizeDynamicScriptLoader = {
\tqueue: [], // Scripts queued to be loaded synchronously
\tloadJs: function(js_obj) {
        
\t\tlet scriptNode = document.createElement('script');
       
\t\tif ('noModule' in HTMLScriptElement.prototype && js_obj.nomodule){
\t\t\tthis.next();
            \t\treturn;
\t\t}
\t\t
\t\tif (!'noModule' in HTMLScriptElement.prototype && js_obj.module){
\t\t\tthis.next();
            \t\treturn;
\t\t}
        
        \tif(js_obj.module){
                \tscriptNode.type = 'module';
                \tscriptNode.onload = function(){
                            \tjchOptimizeDynamicScriptLoader.next();
                \t}
        \t}
   
\t\tif (js_obj.nomodule){
\t\t\tscriptNode.setAttribute('nomodule', '');
\t\t}
        
\t\tif(js_obj.url) { 
            \t\tscriptNode.src = js_obj.url;
        \t}
        \t
        \tif(js_obj.content)
                {
                     \tscriptNode.text = js_obj.content;
                }
\t\tdocument.head.appendChild(scriptNode);
\t},
\tadd: function(data) {
\t\t// Load an array of scripts
\t\tthis.queue = data;
\t\tthis.next();
\t},
\tnext: function() {
\t\tif(this.queue.length >= 1) {
\t\t\t// Load the script
\t\t\tthis.loadJs(this.queue.shift());
\t\t}else{
\t\t\treturn false;
\t\t}
\t}
};

onUserInteract( function(){
    
   \tlet js_urls = {$aJsonEncodedUrlArray} 
   \t    \t
   \tif (!jch_js_loaded){
\t\t
   \t    \tjchOptimizeDynamicScriptLoader.add(js_urls);
   \t    \t
   \t    \tjch_js_loaded = true;
   \t    \t
   \t    \t//window.dispatchEvent(new Event('DOMContentLoded'));
   \t    \t//window.dispatchEvent(new Event('load'));
   \t}
});
</script>
HTML;
        }
    }

    public function loadReduceDom(): void
    {
        if ($this->params->get('pro_reduce_dom', '0')) {
            $this->loadOnUIFunction();
            $this->loadReduceDomFunction = <<<HTML
<script>
onUserInteract(function(){
\tconst containers = document.getElementsByClassName('jch-reduced-dom-container');
\t
\tArray.from(containers).forEach(function(container){
       \t\t//First child should be templates with content attribute
\t\tlet template  = container.firstChild; 
\t\t//clone template
\t\tlet clone = template.content.firstElementChild.cloneNode(true);
\t\t//replace container with content
\t\tcontainer.parentNode.replaceChild(clone, container); 
\t})
});

</script>
HTML;
        }
    }

    private function loadOnUIFunction(): void
    {
        $this->onUserInteractFunction = <<<HTML
<script>
function onUserInteract(callback) { 
\twindow.addEventListener('load', function() {
\t        if (window.pageYOffset !== 0){
\t        \tcallback();
\t        }
\t});
\t
     \tconst events = ['keydown', 'keyup', 'keypress', 'input', 'auxclick', 'click', 'dblclick', 
     \t'mousedown', 'mouseup', 'mouseover', 'mousemove', 'mouseout', 'mouseenter', 'mouseleave', 'mousewheel', 'wheel', 'contextmenu',
     \t'pointerover', 'pointerout', 'pointerenter', 'pointerleave', 'pointerdown', 'pointerup', 'pointermove', 'pointercancel', 'gotpointercapture',
     \t'lostpointercapture', 'pointerrawupdate', 'touchstart', 'touchmove', 'touchend', 'touchcancel'];
         
\tdocument.addEventListener('DOMContentLoaded', function() {
    \t\tevents.forEach(function(e){
\t\t\twindow.addEventListener(e, function() {
\t        \t\tcallback();
\t\t\t}, {once: true, passive: true});
    \t\t});
\t});
}
</script>
HTML;
    }

    /**
     * @param mixed $aUrls
     *
     * @return false|string
     */
    private function jsonEncodeUrlArray($aUrls)
    {
        $aHtmlDecodedUrls = \array_map(function ($mUrl) {
            if (\is_array($mUrl)) {
                if (!empty($mUrl['url'])) {
                    $mUrl['url'] = \html_entity_decode($mUrl['url']);
                }

                return $mUrl;
            }

            return \html_entity_decode($mUrl);
        }, $aUrls);

        return \json_encode($aHtmlDecodedUrls);
    }

    private function loadOnDomContentLoadedFunction(): void
    {
        $this->onDomContentLoadedFunction = <<<HTML
<script>
function onDomContentLoaded(callback) {
\tdocument.addEventListener('DOMContentLoaded', function(){
\t\tcallback();
\t})
}
</script>
HTML;
    }
}
