<?php
/**
* @package RSJoomla! Adapter
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

/**
 * Utility class for FilterBar.
 *
 * @package     RSJoomla!
 */
class RSFilterBar {
	
	// Show search bar
	public $search;
	// show additional items located in the right
	public $rightItems = array();
	// show the ordering select
	public $orderDir = true;
	public $listDirn = '';
	// show the sorting select
	public $sortFields = array();
	public $listOrder = '';
	public $limitBox = '';
	
	public function __construct($options = array()) {
		foreach ($options as $k => $v) {
			$this->{$k} = $v;
		}
	}
	
	protected function escape($string) {
		return htmlentities($string, ENT_COMPAT, 'utf-8');
	}
	
	public function show() {
		if ($this->sortFields || $this->orderDir) {
			JFactory::getDocument()->addScript(JURI::root(true).'/administrator/components/com_rsseo/assets/js/ordertable.js');
		}
		?>
		<div id="filter-bar" class="btn-toolbar">
			<?php if ($this->search) { ?>
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo $this->search['label']; ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo $this->escape($this->search['label']); ?>" value="<?php echo $this->escape($this->search['value']); ?>" title="<?php echo $this->escape($this->search['label']); ?>" />
			</div>
			<div class="btn-group hidden-phone">
				<button class="btn" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn" type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<?php } ?>
			<?php if ($this->limitBox) { ?>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->limitBox; ?>
			</div>
			<?php } ?>
			<?php if ($this->orderDir) { ?>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="filter_order_Dir" id="directionTable" class="input-small" onchange="Joomla.orderTable('<?php echo $this->escape($this->listOrder); ?>')">
					<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '', JText::_('JFIELD_ORDERING_DESC')), JHtml::_('select.option', 'asc', JText::_('JGLOBAL_ORDER_ASCENDING')), JHtml::_('select.option', 'desc', JText::_('JGLOBAL_ORDER_DESCENDING'))), 'value', 'text', $this->listDirn, false); ?>
				</select>
			</div>
			<?php } ?>
			<?php if ($this->sortFields) { ?>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="filter_order" id="sortTable" class="input-medium" onchange="Joomla.orderTable('<?php echo $this->escape($this->listOrder); ?>')">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $this->sortFields, 'value', 'text', $this->listOrder);?>
				</select>
			</div>
			<?php } ?>
			
			<div class="clearfix"> </div>
		</div>
		<?php
	}
}