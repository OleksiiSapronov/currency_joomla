<?php
/**
 * ------------------------------------------------------------------------
 * JA Hotel Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

//Disqus config
$subdomain     		= $this->plgParams->get('provider-disqus-subdomain');
$devmode        	= $this->plgParams->get('provider-disqus-devmode',0);
$identifier        	= 1;//$this->plgParams->get('provider-disqus-identifier',0);
$disqus_language	= $this->plgParams->get('pvovider-disqus-language','en');

$sefUrl = $this->_sefurl;

if ($this->commentContext == 'count'):
?>
	<div class="jacomment-count">
		<i class="fa fa-comment"></i>
		<strong><?php echo JText::_( 'TPL_JACOMMENT_LABEL' ); ?></strong>
    <a class="jacomment-counter"<?php if($identifier){ echo ' data-disqus-identifier="', $this->_postid ,'"'; } ?> href="<?php echo $this->_sefurl; ?>#disqus_thread" onclick="location.href='<?php echo $this->_url; ?>#disqus_thread'; return false;" title=""></a>
  </div>
<?php
else:
	if(!defined("JA_EMBEDED_DISQUS_FORM")):
		define("JA_EMBEDED_DISQUS_FORM", 1);
?>
<div id="disqus_thread"></div>

<?php if($identifier): ?>
<script id="disqus_identifier_js" type="text/javascript">
	disqus_identifier = "<?php echo $this->_postid ?>";
</script>
<?php endif; ?>
<script id="disqus_js" type="text/javascript">
	/**
	 * var disqus_identifier; [Optional but recommended: Define a unique identifier (e.g. post id or slug) for this thread]
	*/
	<?php if($devmode): ?>
	disqus_developer = 1;
	<?php endif; ?>
	disqus_shortname = '<?php echo $subdomain; ?>';
	var disqus_config = function(){
		this.language = '<?php echo trim($disqus_language) ?>';
	};
	(function() {
	var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	dsq.src = '//<?php echo $subdomain; ?>.disqus.com/embed.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
	})();
</script>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=<?php echo $subdomain; ?>">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dq-powered"><?php echo JText::_("BLOG COMMENTS POWERED BY DISQUS"); ?></a>
<?php
	endif;
endif;
?>