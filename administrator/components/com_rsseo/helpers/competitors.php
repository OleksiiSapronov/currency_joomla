<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class competitorsHelper {
	
	protected $id;
	protected $url;
	protected $statistics = false;
	
	protected $values = array(
		'age' => 0,
		'bingp' => 0,
		'bingb' => 0,
		'alexa' => 0,
		'mozpagerank' => 0,
		'mozda' => 0,
		'mozpa' => 0
	);
	
	public function __construct($id, $url, $statistics = false) {
		// Set Competitor ID
		$this->id = $id;
		
		// Set Competitor URL
		$this->url = $url;
		
		// Statistics
		$this->statistics = $statistics;
	}
	
	public static function getInstance($id, $url, $statistics = false) {
		$modelClass = 'competitorsHelper';
		return new $modelClass($id, $url, $statistics);
	}
	
	public function check() {
		// Get configuration
		$config = rsseoHelper::getConfig();
		
		if ($config->enable_age || $this->statistics) {
			$this->domainage();
		}
		
		if ($config->enable_bingp || $this->statistics) {
			$this->bingpages();
		}
		
		if ($config->enable_bingb || $this->statistics) {
			$this->bingbacklinks();
		}
		
		if ($config->enable_alexa || $this->statistics) {
			$this->alexa();
		}
		
		if ($config->enable_moz || $this->statistics) {
			$this->moz();
		}
		
		if ($this->statistics) {
			$this->facebook();
			$this->linkedin();
		}
		
		$this->update();
		
		return $this->statistics ? $this->values : json_encode($this->values);
	}
	
	/**
	 *	Calculate Google pages
	 */
	protected function googlepages() {
		$url		= str_replace(array('http://','https://','www.'),'',$this->url);
		$g_pages	= false;
		$search 	= urlencode('site:'.$url);
		$url		= 'https://www.'.rsseoHelper::getConfig('google_domain').'/search?q='.$search.'&gws_rd=cr';
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern1 = '#<div id=["|\']resultStats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']resultStats["|\']>(.*?)<\/div>#is';
			if (preg_match($pattern1, $response, $match)) {
				if (isset($match[1])) {
					$result  = trim($match[1]);
					$result  = preg_replace('#[^0-9]#', '', $result);
					$g_pages = $result;
				}
			}
			
			if ($g_pages === false) {
				if (preg_match($pattern2, $response, $match)) {
					if (isset($match[1])) {
						$result  = trim($match[1]);
						$result  = preg_replace('#[^0-9]#', '', $result);
						$g_pages = $result;
					}
				}
			}
		}
		
		$this->values['googlep'] = $g_pages === false ? -1 : (int) $g_pages;
	}
	
	/**
	 *	Calculate Google backlinks
	 */
	protected function googlebacklinks() {
		$url	= str_replace(array('http://','https://','www.'), '', $this->url);
		$search = urlencode('"'.$url.'" -site:'.$url);
		$url	= 'https://www.'.rsseoHelper::getConfig('google_domain').'/search?q='.$search.'&as_lq=&num=100&start=0&filter=0&gws_rd=cr';
		$g_back	= false;
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern1 = '#<div id=["|\']resultStats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']resultStats["|\']>(.*?)<\/div>#is';
			if (preg_match($pattern1, $response, $match)) {
				if (isset($match[1])) {
					$result  = trim($match[1]);
					$result  = preg_replace('#[^0-9]#', '', $result);
					$g_back	 = $result;
				}
			}
			
			if ($g_back === false) {
				if (preg_match($pattern2, $response, $match)) {
					if (isset($match[1])) {
						$result  = trim($match[1]);
						$result  = preg_replace('#[^0-9]#', '', $result);
						$g_back	 = $result;
					}
				}
			}
		}
		
		$this->values['googleb'] = $g_back === false ? -1 : (int) $g_back;
	}
	
	/**
	 *	Calculate Google similar pages
	 */
	protected function googleRelated() {
		$url		= str_replace(array('http://','https://','www.'),'',$this->url);
		$r_pages	= false;
		$search 	= urlencode('related:'.$url);
		$url		= 'https://www.'.rsseoHelper::getConfig('google_domain').'/search?q='.$search.'&gws_rd=cr';
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern1 = '#<div id=["|\']resultStats["|\']>(.*?)<nobr>#is';
			$pattern2 = '#<div class="sd" id=["|\']resultStats["|\']>(.*?)<\/div>#is';
			if (preg_match($pattern1, $response, $match)) {
				if (isset($match[1])) {
					$result  = trim($match[1]);
					$result  = preg_replace('#[^0-9]#', '', $result);
					$r_pages = $result;
				}
			}
			
			if ($r_pages === false) {
				if (preg_match($pattern2, $response, $match)) {
					if (isset($match[1])) {
						$result  = trim($match[1]);
						$result  = preg_replace('#[^0-9]#', '', $result);
						$r_pages = $result;
					}
				}
			}
		}
		
		$this->values['googler'] = $r_pages === false ? -1 : (int) $r_pages;
	}
	
	/**
	 *	Calculate Bing pages
	 */
	protected function bingpages() {
		$url = str_replace(array('http://','https://','www.'), '', $this->url);
		$url = 'http://www.bing.com/search?q='.urlencode($url);
		$found = false;
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern1 = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
			$pattern2 = '#<span class="sb_count" id="count">(.*?) of (.*?) results<\/span>#i';
			$pattern3 = '#<span class="sb_count">(.*?)<\/span>#i';
			
			if (preg_match($pattern1, $response, $matches1)) {
				if (!empty($matches1[1])) {
					$number = explode(' ',$matches1[1]);
					
					$this->values['bingp'] = (int) str_replace(array(',','.','&#160;'),'',@$number[0]);
					$found = true;
				}
			}
			
			if (!$found) {
				if (preg_match($pattern2, $response, $matches2)) {
					if (!empty($matches2[2])) {
						$this->values['bingp'] = (int) str_replace(array(',','.','&#160;'), '', $matches2[2]);
						$found = true;
					}
				}
			}
			
			if (!$found) {
				if (preg_match($pattern3, $response, $matches3)) {
					if (!empty($matches3[1])) {
						$number = explode(' ',$matches3[1]);
						
						$this->values['bingp'] = str_replace(array(',','.',' '), '', @$number[0]);
						$found = true;
					}
				}
			}
		}
		
		if (!$found)
			$this->values['bingp'] = -1;
	}
	
	/**
	 *	Calculate Bing backlinks
	 */
	protected function bingbacklinks() {
		$url = str_replace(array('http://','https://','www.'),'',$this->url);
		$url = 'http://www.bing.com/search?filt=all&q='.urlencode('link: '.$url);
		$found = false;
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern1 = '#<span class="sb_count" id="count">(.*?)<\/span>#i';
			$pattern2 = '#<span class="sb_count" id="count">(.*?) of (.*?) results<\/span>#is';
			$pattern3 = '#<span class="sb_count">(.*?)<\/span>#i';
			
			if (preg_match($pattern1, $response, $matches1)) {
				if (!empty($matches1[1])) {
					$number = explode(' ',$matches1[1]);
					$this->values['bingb'] = (int) str_replace(array(',','.','&#160;'), '', @$number[0]);
					$found = true;
				}
			}
			
			if (!$found) {
				if (preg_match($pattern2, $response, $matches2)) {
					if (!empty($matches2[2])) {
						$this->values['bingb'] = (int) str_replace(array(',','.','&#160;'), '', $matches2[2]);
						$found = true;
					}
				}
			}
			
			if (!$found) {
				if (preg_match($pattern3, $response, $matches3)) {
					if (!empty($matches3[1])) {
						$number = explode(' ',$matches3[1]);
						
						$this->values['bingb'] = str_replace(array(',','.',' '), '', @$number[0]);
						$found = true;
					}
				}
			}
		}
		
		if (!$found)
			$this->values['bingb'] = -1;
	}
	
	/**
	 *	Calculate Alexa rank
	 */
	protected function alexa() {
		$url = trim($this->url);
		$url = str_replace(array('http://','https://','www.'), '', $url);
		$url = 'https://www.alexa.com/minisiteinfo/'.urlencode($url);
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			$pattern = '#<div class="data (up|down|steady)">(.*?)<\/div>#is';
			if (preg_match($pattern, $response, $match)) {
				if (isset($match[2])) {
					$rank = trim(strip_tags($match[2]));
					$rank = str_replace(',', '', $rank);
					$this->values['alexa'] = (int) $rank;
				} else {
					$this->values['alexa'] = 0;
				}
			}
		} else $this->values['alexa'] = -1;
	}
	
	/**
	 *	Get Facebook shares & likes
	 */
	protected function facebook() {
		$url = $this->url;
		$url = substr($url,0,4) == 'http' ? $url : 'http://'.$url;
		$fql = sprintf('SELECT share_count, like_count FROM link_stat WHERE url="%s"', $url);
		$url = 'https://api.facebook.com/method/fql.query?query='.rawurlencode($fql).'&format=json';
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			if ($response = json_decode($response)) {
				if (is_object($response)) {
					$this->values['fb_share_count'] = -1;
					$this->values['fb_like_count'] = -1;
				} elseif (is_array($response)) {
					$response = isset($response[0]) ? $response[0] : false;
					if (isset($response->share_count)) {
						$this->values['fb_share_count'] = $response->share_count;
					} else $this->values['fb_share_count'] = -1;
					
					if (isset($response->like_count)) {
						$this->values['fb_like_count'] = $response->like_count;
					} else $this->values['fb_like_count'] = -1;
				} else {
					$this->values['fb_share_count'] = -1;
					$this->values['fb_like_count'] = -1;
				}
			} else {
				$this->values['fb_share_count'] = -1;
				$this->values['fb_like_count'] = -1;
			}
		} else {
			$this->values['fb_share_count'] = -1;
			$this->values['fb_like_count'] = -1;
		}
	}
	
	/**
	 *	Get LinkedIn shares
	 */
	protected function linkedin() {
		$url = $this->url;
		$url = substr($url,0,4) == 'http' ? $url : 'http://'.$url;
		$url = 'http://www.linkedin.com/countserv/count/share?url='.urlencode($url).'&format=json';
		
		$response = rsseoHelper::fopen($url);
		if ($response && $response != 'RSSEOINVALID') {
			if ($response = json_decode($response)) {
				if (isset($response->count)) {
					$this->values['linkedin'] = $response->count;
				} else $this->values['linkedin'] = -1;
			} else $this->values['linkedin'] = -1;
		} else $this->values['linkedin'] = -1;
	}
	
	/**
	 *	Get domain age
	 */
	protected function domainage() {
		require_once JPATH_ADMINISTRATOR.'/components/com_rsseo/helpers/whois.php';
		$whois	= new rsseoWhois($this->url);
		$age	= $whois->age();
		
		$this->values['age'] = $age ? $age : -1;
	}
	
	/**
	 *	Get Moz data
	 */
	protected function moz() {
		$domain		= str_replace(array('http://','https://'), '', $this->url);
		$config		= rsseoHelper::getConfig();
		$mozPA		= -1;
		$mozDA		= -1;
		$mozRank	= -1;
		
		if (!empty($config->moz_access_id) && !empty($config->moz_secret)) {
			$expires	= time() + 300;
			$accessID	= $config->moz_access_id;
			$string		= $accessID."\n".$expires;
			$binary		= hash_hmac('sha1', $string, $config->moz_secret, true);
			$signature	= urlencode(base64_encode($binary));
		} else {
			JFactory::getSession()->set('rsseo.custom.agent', 'RSJoomla');
			if ($contents = rsseoHelper::fopen('https://moz.com/users/level?src=mozbar')) {
				$data		= json_decode($contents);
				$accessID	= isset($data->access_id) ? $data->access_id : null;
				$expires	= isset($data->expires) ? $data->expires : null;
				$signature	= isset($data->signature) ? $data->signature : null;
			}
		}
		
		if (!empty($accessID) && !empty($expires) && !empty($signature)) {
			JFactory::getSession()->set('rsseo.custom.agent', 'RSJoomla');
			$url		= 'http://lsapi.seomoz.com/linkscape/url-metrics/'.urlencode($domain).'?Cols=128849070112&AccessID='.$accessID.'&Expires='.$expires.'&Signature='.$signature;
			$mozData	= rsseoHelper::fopen($url);
			
			if ($mozData) {
				$mozData = json_decode($mozData);
				
				if (!isset($mozData->error_message)) {
					if (isset($mozData->upa))  $mozPA 	 = round($mozData->upa);
					if (isset($mozData->pda))  $mozDA	 = round($mozData->pda);
					if (isset($mozData->umrp)) $mozRank  = round($mozData->umrp);
				}
			}
		}
		
		$this->values['mozda'] = $mozDA;
		$this->values['mozpa'] = $mozPA;
		$this->values['mozpagerank'] = $mozRank;
	}
	
	/**
	 *	Update values
	 */
	protected function update() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$now	= JFactory::getDate()->toSql();
		
		if ($this->statistics) {
			$query->clear()
				->update($db->qn('#__rsseo_statistics'))
				->set($db->qn('date').' = '.$db->q($now));
			
			foreach($this->values as $name => $value) {
				$query->set($db->qn($name).' = '.$db->q($value));
			}
			
			$db->setQuery($query);
			$db->execute();
		} else {
			// Add new record for history
			$query->clear()
				->insert($db->qn('#__rsseo_competitors'))
				->set($db->qn('parent_id').' = '. (int) $this->id)
				->set($db->qn('date').' = '.$db->q($now));
			
			foreach($this->values as $name => $value) {
				$query->set($db->qn($name).' = '.$db->q($value));
			}
			
			$db->setQuery($query);
			$db->execute();
			
			// Update parent
			$query->clear()
				->update($db->qn('#__rsseo_competitors'))
				->set($db->qn('date').' = '.$db->q($now))
				->where($db->qn('id').' = '.(int) $this->id);
			
			foreach($this->values as $name => $value) {
				$query->set($db->qn($name).' = '.$db->q($value));
			}
			
			$db->setQuery($query);
			$db->execute();
		}
	}
}