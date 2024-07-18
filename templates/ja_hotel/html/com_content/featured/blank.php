<?php
/* Blank content */
  defined('_JEXEC') or die;

  JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
  JHtml::addIncludePath(T3_PATH.'/html/com_content');
  JHtml::addIncludePath(dirname(dirname(__FILE__)));
  JHtml::_('behavior.caption');
?>

<?php if ($this->params->get('show_page_heading') != 0) : ?>
<div class="blog-featured">
  <div class="page-header">
  	<h1>
  	<?php echo $this->escape($this->params->get('page_heading')); ?>
  	</h1>
  	<span> <?php echo $this->escape($this->params->get('itemTitleDesc')); ?> </span>
  </div>
</div>
<?php endif; ?>
