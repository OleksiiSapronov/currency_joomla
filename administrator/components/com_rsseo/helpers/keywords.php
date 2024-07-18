<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class keywordsHelper {
	
	protected $id;
	protected $keyword;
	protected $lastpostion	= 0;
	protected $domains		= array();
	protected $values		= array('position' => 0, 'badge' => '', 'date' => '0000-00-00 00:00:00');
	
	public function __construct($id, $keyword) {
		// Set Keyword ID
		$this->id = $id;
		// Set Keyword
		$this->keyword = $keyword;
		
		// Set domains
		$this->setDomains();
		
		// Set the last known position
		$this->getPosition();
	}
	
	public static function getInstance($id, $keyword) {
		$modelClass = 'keywordsHelper';
		return new $modelClass($id, $keyword);
	}
	
	/*
	 *	Set domains
	 */
	protected function setDomains() {
		$config = rsseoHelper::getConfig();
		
		$mainsite = JURI::root();
		$mainsite_nohw = str_replace(array('http://','https://','www.'), '', $mainsite);
		
		$this->domains[] = $mainsite;
		$this->domains[] = $mainsite_nohw;
		
		if (!empty($config->subdomains)) {
			if ($subdomains = explode("\n", $config->subdomains)) {
				foreach ($subdomains as $subdomain) {
					$this->domains[] = trim($subdomain);
				}
			}
		}
	}
	
	/*
	 *	Get last position
	 */
	protected function getPosition() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->select($db->qn('position'))
			->from($db->qn('#__rsseo_keyword_position'))
			->where($db->qn('idk').' = '.$db->q($this->id))
			->order($db->qn('date').' DESC');
		$db->setQuery($query,0,1);
		$this->lastpostion = (int) $db->loadResult();
	}
	
	/*
	 *	Check the keywords position
	 */
	public function check() {
		// This class is deprecated and it's not used anymore.
		return json_encode($this->values);
		
		// Get configuration
		$config = rsseoHelper::getConfig();
		
		require_once JPATH_ADMINISTRATOR. '/components/com_rsseo/helpers/phpQuery.php';
		
		$keyword = str_replace(' ', '+', $this->keyword);
		$keyword = str_replace('%26', '&',$keyword);
		
		$valid = false;
		$position = 1;
		
		for($limit = 0; $limit < 5; $limit++) {
			$url 		= 'https://www.'.$config->google_domain.'/search?q='.$keyword.'&pws=0&start='.(10*$limit);
			$contents	= rsseoHelper::fopen($url);
			$dom		= phpQuery::newDocument($contents);
			
			foreach ($dom->find('h3[class=r] a') as $a) {
				$href = phpQuery::pq($a)->attr('href');
				foreach ($this->domains as $domain) {
					if(empty($domain)) continue;
					if(strpos($href,$domain) !== false) {
						$valid = true;
						continue;
					}
				}
				
				if ($valid) continue;
				$position++;
			}
			if ($valid) break;
		}
		
		$position = $valid ? $position : 0;
		if ($position > $this->lastpostion) 
			$color = 'important';
		else if ($position < $this->lastpostion) 
			$color = 'success';
		else if ($position == $this->lastpostion)
			$color = '';
		
		$this->insert($position);
		$this->values['position'] = $position;
		$this->values['badge'] = $color;
		
		return json_encode($this->values);
	}
	
	/*
	*	Insert the new keyword position
	*/
	protected function insert($position) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$now	= JFactory::getDate()->toSql();
		
		$query->insert($db->qn('#__rsseo_keyword_position'))
			->set($db->qn('idk').' = '.$this->id)
			->set($db->qn('position').' = '.$db->q($position))
			->set($db->qn('date').' = '.$db->q($now));
		
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()
			->update($db->qn('#__rsseo_keywords'))
			->set($db->qn('lastcheck').' = '.$db->q($now))
			->where($db->qn('id').' = '.$db->q($this->id));
		
		$db->setQuery($query);
		$db->execute();
		
		$this->values['date'] = JHtml::_('date', $now, rsseoHelper::getConfig('global_dateformat'));
	}
}