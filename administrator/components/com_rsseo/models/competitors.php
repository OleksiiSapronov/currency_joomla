<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class rsseoModelCompetitors extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'name', 'age', 'alexa', 'bingb',
				'bingp', 'date',
				'mozpagerank', 'mozda', 'mozpa'
			);
		}

		parent::__construct($config);
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery() {
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		
		// Select fields
		$query->select('*');
		
		// Select from table
		$query->from($db->qn('#__rsseo_competitors'));
		
		// Get parents only
		$parent = $this->getState('filter.parent');
		$query->where($db->qn('parent_id').' = '.(int) $parent);
		
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->q('%'.$db->escape($search, true).'%');
			$query->where('('.$db->qn('name').' LIKE '.$search.' OR '.$db->qn('tags').' LIKE '.$search.')');
		}
		
		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'id');
		$listDirn = $db->escape($this->getState('list.direction', 'asc'));
		$query->order($db->escape($listOrdering).' '.$listDirn);
		
		return $query;
	}
	
	/**
	 * Method to get the items list.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.6.1
	 */
	public function getItems() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$items	= parent::getItems();
		$config = rsseoHelper::getConfig();
		
		foreach ($items as $i => $item) {
			if (!$this->getState('filter.parent')) {
				// Get history
				$query->clear();
				$query->select('*')
					->from($db->qn('#__rsseo_competitors'))
					->where($db->qn('parent_id').' = '.(int) $item->id)
					->order($db->qn('date').' DESC');
				$db->setQuery($query,0,2);
				$history = $db->loadObjectList();
				
				if(isset($history[1])) {
					$compare = $history[1]; 
				} else $compare = isset($history[0]) ? $history[0] : array();
				
				if (empty($compare)) {
					$compare = $item;
				}
				
				// Bing pages
				if ($config->enable_bingp) {
					if ($compare->bingp < $item->bingp) 
						$items[$i]->bingpbadge = 'success';
					else if ($compare->bingp > $item->bingp)
						$items[$i]->bingpbadge = 'important';
					else if ($compare->bingp == $item->bingp) 
						$items[$i]->bingpbadge = '';
				} else $items[$i]->bingpbadge = '';
				
				// Bing backlinks
				if ($config->enable_bingb) {
					if ($compare->bingb < $item->bingb) 
						$items[$i]->bingbbadge = 'success';
					else if ($compare->bingb > $item->bingb)
						$items[$i]->bingbbadge = 'important';
					else if ($compare->bingb == $item->bingb) 
						$items[$i]->bingbbadge = '';
				} else $items[$i]->bingbbadge = '';
					
				// Alexa page rank
				if ($config->enable_alexa) {
					if ($compare->alexa < $item->alexa) 
						$items[$i]->alexabadge = 'important';
					else if ($compare->alexa > $item->alexa)
						$items[$i]->alexabadge = 'success';
					else if ($compare->alexa == $item->alexa) 
						$items[$i]->alexabadge = '';
				} else $items[$i]->alexabadge = '';
				
				// Moz
				if ($config->enable_moz) {
					if ($compare->mozpagerank < $item->mozpagerank) 
						$items[$i]->mozpagerankbadge = 'success';
					else if ($compare->mozpagerank > $item->mozpagerank)
						$items[$i]->mozpagerankbadge = 'important';
					else if ($compare->mozpagerank == $item->mozpagerank) 
						$items[$i]->mozpagerankbadge = '';
					
					if ($compare->mozda < $item->mozda) 
						$items[$i]->mozdabadge = 'success';
					else if ($compare->mozda > $item->mozda)
						$items[$i]->mozdabadge = 'important';
					else if ($compare->mozda == $item->mozda) 
						$items[$i]->mozdabadge = '';
					
					if ($compare->mozpa < $item->mozpa) 
						$items[$i]->mozpabadge = 'success';
					else if ($compare->mozpa > $item->mozpa)
						$items[$i]->mozpabadge = 'important';
					else if ($compare->mozpa == $item->mozpa) 
						$items[$i]->mozpabadge = '';
				} else {
					$items[$i]->mozpagerankbadge = '';
					$items[$i]->mozdabadge = '';
					$items[$i]->mozpabadge = '';
				}
			
			} else {
				$items[$i]->pagerankbadge = '';
				$items[$i]->bingpbadge = '';
				$items[$i]->bingbbadge = '';
				$items[$i]->alexabadge = '';
				$items[$i]->technoratibadge = '';
				$items[$i]->mozpagerankbadge = '';
				$items[$i]->mozdabadge = '';
				$items[$i]->mozpabadge = '';
			}
			
			// Convert number
			$items[$i]->bingp = $items[$i]->bingp == -1 ? '-' : number_format($items[$i]->bingp, 0, '', '.');
			$items[$i]->bingb = $items[$i]->bingb == -1 ? '-' : number_format($items[$i]->bingb, 0, '', '.');
			$items[$i]->alexa = $items[$i]->alexa == -1 ? '-' : number_format($items[$i]->alexa, 0, '', '.');
			$items[$i]->technorati = $items[$i]->technorati == -1 ? '-' : number_format($items[$i]->technorati, 0, '', '.');
			$items[$i]->mozpagerank = $items[$i]->mozpagerank == -1 ? '-' : number_format($items[$i]->mozpagerank, 0, '', '.');
			$items[$i]->mozda = $items[$i]->mozda == -1 ? '-' : number_format($items[$i]->mozda, 0, '', '.');
			$items[$i]->mozpa = $items[$i]->mozpa == -1 ? '-' : number_format($items[$i]->mozpa, 0, '', '.');
		}
		
		return $items;
	}
	
	/**
	 * Method to get competitor name.
	 *
	 * @return	string	The name of the competitor.
	 */
	public function getCompetitor() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select($db->qn('name'))
			->from($db->qn('#__rsseo_competitors'))
			->where($db->qn('id').' = '.(int) $this->getState('filter.parent'));
		$db->setQuery($query);
		return $db->loadResult();
	}
}