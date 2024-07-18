<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class rsseoControllerCompetitors extends JControllerAdmin
{
	protected $text_prefix = 'COM_RSSEO_COMPETITOR';
	
	/**
	 * Constructor.
	 *
	 * @param	array	$config	An optional associative array of configuration settings.

	 * @return	rsseoControllerCompetitors
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'Competitor', $prefix = 'rsseoModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Method to export competitors
	 *
	 * @return	FILE
	 */
	public function export() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$file	= 'rsseo_'.JFactory::getDate()->format('YmdHis').'.csv';
		$csv	= '';
		$headers= array();
		$config	= rsseoHelper::getConfig();
		
		// Show headers
		$headers[] = 'Competitor URL';
		if ($config->enable_age) $headers[] = 'Domain age';
		if ($config->enable_bingp) $headers[] = 'Bing Pages';
		if ($config->enable_bingb) $headers[] = 'Bing Backlinks';
		if ($config->enable_alexa) $headers[] = 'Alexa Rank';
		if ($config->enable_moz) $headers[] = 'Moz Rank';
		if ($config->enable_moz) $headers[] = 'Moz Page Authority';
		if ($config->enable_moz) $headers[] = 'Moz Domain Authority';
		
		$this->csvEscape($headers);
		$csv .= $this->csvLine($headers);
		
		$query->clear()
			->select('*')
			->from($db->qn('#__rsseo_competitors'))
			->where($db->qn('parent_id').' = 0');
		$db->setQuery($query);
		if ($competitors = $db->loadObjectList()) {
			foreach($competitors as $competitor) {
				$row = array();
				$domain_age = $competitor->age == -1 ? '-' : rsseoHelper::convertage($competitor->age);
				
				$row[] = $competitor->name;
				if ($config->enable_age) $row[] = $domain_age;
				if ($config->enable_bingp) $row[] = $competitor->bingp;
				if ($config->enable_bingb) $row[] = $competitor->bingb;
				if ($config->enable_alexa) $row[] = $competitor->alexa;
				if ($config->enable_moz) $row[] = $competitor->mozpagerank;
				if ($config->enable_moz) $row[] = $competitor->mozpa;
				if ($config->enable_moz) $row[] = $competitor->mozda;
				
				$this->csvEscape($row);
				$csv .= $this->csvLine($row);
			}
		}
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . strlen($csv));
		ob_clean();
		flush();
		echo $csv;
		
		JFactory::getApplication()->close();
	}
	
	/**
	 * Method to refresh a competitor
	 *
	 * @return	JSON
	 */
	public function refresh() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id');
		$config	= rsseoHelper::getConfig();
		
		$query->clear()
			->select($db->qn('name'))
			->from($db->qn('#__rsseo_competitors'))
			->where($db->qn('id').' = '.(int) $id);
		$db->setQuery($query);
		$url = $db->loadResult();
		
		require_once JPATH_ADMINISTRATOR. '/components/com_rsseo/helpers/competitors.php';
		$competitor = competitorsHelper::getInstance($id, $url);
		$values = $competitor->check();
		$default= json_decode($values);
		$values = json_decode($values, true);
		
		foreach($values as $name => $value) {
			if ($name == 'age') {
				if ($value == -1) { 
					$values[$name] = '-';
				} else {
					$values[$name] = rsseoHelper::convertage($value);
				}
			} else {
				if ($value == -1) {
					$values[$name] = '-';
				} else {
					$values[$name] = number_format($value, 0, '', '.');
				}
			}
		}
		
		// Get history
		$query->clear()
			->select('*')
			->from($db->qn('#__rsseo_competitors'))
			->where($db->qn('parent_id').' = '.(int) $id)
			->order($db->qn('date').' DESC');
		$db->setQuery($query,0,2);
		$history = $db->loadObjectList();
		
		if(isset($history[1])) {
			$compare = $history[1]; 
		} else $compare = $history[0];
		
		if (empty($compare)) {
			$compare = $default;
		}
		
		// Bing pages
		if ($config->enable_bingp) {
			if ($compare->bingp < $values['bingp']) 
				$values['bingpbadge'] = 'success';
			else if ($compare->bingp > $values['bingp'])
				$values['bingpbadge'] = 'important';
			else if ($compare->bingp == $values['bingp']) 
				$values['bingpbadge'] = '';
		} else $values['bingpbadge'] = '';
		
		// Bing backlinks
		if ($config->enable_bingb) {
			if ($compare->bingb < $values['bingb']) 
				$values['bingbbadge'] = 'success';
			else if ($compare->bingb > $values['bingb'])
				$values['bingbbadge'] = 'important';
			else if ($compare->bingb == $values['bingb']) 
				$values['bingbbadge'] = '';
		} else $values['bingbbadge'] = '';
			
		// Alexa page rank
		if ($config->enable_alexa) {
			if ($compare->alexa < $values['alexa']) 
				$values['alexabadge'] = 'important';
			else if ($compare->alexa > $values['alexa'])
				$values['alexabadge'] = 'success';
			else if ($compare->alexa == $values['alexa']) 
				$values['alexabadge'] = '';
		} else $values['alexabadge'] = '';
		
		// Moz
		if ($config->enable_moz) {
			if ($compare->mozpagerank < $values['mozpagerank']) 
				$values['mozpagerankbadge'] = 'success';
			else if ($compare->mozpagerank > $values['mozpagerank'])
				$values['mozpagerankbadge'] = 'important';
			else if ($compare->mozpagerank == $values['mozpagerank']) 
				$values['mozpagerankbadge'] = '';
			
			if ($compare->mozda < $values['mozda']) 
				$values['mozdabadge'] = 'success';
			else if ($compare->mozda > $values['mozda'])
				$values['mozdabadge'] = 'important';
			else if ($compare->mozda == $values['mozda']) 
				$values['mozdabadge'] = '';
			
			if ($compare->mozpa < $values['mozpa']) 
				$values['mozpabadge'] = 'success';
			else if ($compare->mozpa > $values['mozpa'])
				$values['mozpabadge'] = 'important';
			else if ($compare->mozpa == $values['mozpa']) 
				$values['mozpabadge'] = '';
		} else {
			$values['mozpagerankbadge'] = '';
			$values['mozpabadge'] = '';
			$values['mozdabadge'] = '';
		}
		
		// Add date refreshed
		$values['date'] = JHtml::_('date', 'now', $config->global_dateformat);
		
		echo json_encode($values);
		JFactory::getApplication()->close();
	}
	
	// Escape CSV values
	protected function csvEscape(&$array) {
		foreach ($array as &$value) {
			$value = str_replace(array('\\r','\\n','\\t','"'), array("\015","\012","\011","\"\""), $value);
		}
	}
	
	// Create a new CSV line
	protected function csvLine($values) {
		foreach ($values as &$value) {
			$value = '"'.$value.'"';
		}
		
		return implode(',',$values)."\n";
	}
}