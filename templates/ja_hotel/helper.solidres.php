<?php
/**
 * Created by PhpStorm.
 * User: TOANDD
 * Date: 21/01/2015
 * Time: 9:19 SA
 */

defined('_JEXEC') or die;

$com_path = JPATH_SITE . '/templates/ja_hotel/';
require_once $com_path . 'router.php';
require_once $com_path . 'helpers/route.php';

JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');

JLoader::register('JAContentTypeModelItems', JPATH_ROOT . '/plugins/system/jacontenttype/models/items.php');

abstract class JATemplateHelperSolidres{
    /**
     * Get a list of Assets from specific Asset's Category.
     * @param JRegistry  &$params  object holding the models parameters
     * return   mixed
     * @since   1.6
     */
    public static  function getListAssets(&$params){
        // Get an instance of the generic articles model
        $assets = new JAContentTypeModelItems(array('ignore_request' => true));

        // Set application parameters in model
        $app       = JFactory::getApplication();
        $appParams = $app->getParams();
        $assets->getState('params', $appParams);

        // Set the filters based on the module params
        $assets->setState('list.start', 0);
        $assets->setState('list.limit', (int) $params->get('count', 0));
        $assets->setState('filter.published', 1);

        // Access filter
        $access     = !JComponentHelper::getParams('com_solidres')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $assets->setState('filter.access', $access);

        $catids = $params->get('catid');

        // Ordering
        $assets->setState('list.ordering', $params->get('article_ordering', 'ordering'));
        $assets->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

        // Find current Asset ID if on an article page
        $option = $app->input->get('option');
        $view   = $app->input->get('view');

        //Display options
        $show_date        = $params->get('show_date', 0);
        $show_date_field  = $params->get('show_date_field', 'created');
        $show_date_format = $params->get('show_date_format', 'Y-m-d H:i:s');
        $show_category    = $params->get('show_intro_category', 0);
        $show_rating      = $params->get('show_rating', 0);
        //$show_author      = $params->get('show_author', 0);
        $show_introtext   = $params->get('show_intro_text', 0);
        $introtext_limit  = $params->get('intro_limit', 100);

        if ($option === 'com_solidres' && $view === 'reservationasset')
        {
            $active_article_id = $app->input->getInt('id');
        }
        else
        {
            $active_article_id = 0;
        }

        foreach($catids as $catid){
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query -> select ('*')
                ->from($db->quoteName('#__sr_reservation_assets'))
                ->where('category_id='.(int)$catid)
                ->order($params->get('article_ordering', 'ordering').' '.$params->get('article_ordering_direction', 'ASC'));
            $db->setQuery($query);
            $items = $db->loadObjectList();
        }

        // prepare data for display
        foreach($items as $item){
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->clear();
            $query ->select($db->quoteName('value'))
                ->from($db->quoteName('#__sr_media','a'))
                ->join('INNER', $db->quoteName('#__sr_media_reservation_assets_xref','b').' ON ('.$db->quoteName('a.id').' = '.$db->quoteName('b.media_id').')')
                ->where('b.reservation_asset_id ='.(int) $item->id);
            $db->setQuery($query);
            $result = $db->loadAssocList();

            $item->images = new stdClass();

            if(count($result)){
                foreach($result[0] as $r){
                    $item->images->image_intro = $item->images->image_fulltext = 'media/com_solidres/assets/images/system/'.$r;
                    $item->images->float_intro = '';
                    $item->images->image_intro_alt = '';
                    $item->images->image_intro_caption = '';
                    $item->images->float_fulltext = '';
                    $item->images->float_fulltext_alt = '';
                    $item->images->float_fulltext_caption = '';
                }

            }
            $item->images = json_encode($item->images, true);

            $item->slug    = $item->id . ':' . $item->alias;
            $item->catslug = $item->category_id ? $item->category_id . ':' . $item->alias : $item->category_id;

            //$item->link = JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.(int) $item->id);

            if ($access || in_array($item->access, $authorised))
            {
                // We know that user has the privilege to view the article
                $item->link = JRoute::_(JATemplateHelperRoute::getReservationAssetRoute($item->id));
            }
            else {
                $app = JFactory::getApplication();
                $menu = $app->getMenu();
                $menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
                if (isset($menuitems[0])) {
                    $Itemid = $menuitems[0]->id;
                } elseif ($app->input->getInt('Itemid') > 0) {
                    // Use Itemid from requesting page only if there is no existing menu
                    $Itemid = $app->input->getInt('Itemid');
                }

                $item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
            }
            // Used for styling the active asset
            $item->active      = $item->id == $active_article_id ? 'active' : '';
            $item->displayDate = '';

            if ($show_date)
            {
                $item->displayDate = JHTML::_('date', $item->$show_date_field, $show_date_format);
            }

            if ($item->category_id)
            {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->clear();
                $query->select($db->quoteName('title'))
                    ->from($db->quoteName('#__categories'))
                    ->where('id = '.(int) $item->category_id);
                $db->setQuery($query);
                $item->category_title = $db->loadResult();
                //$item->displayCategoryLink  = JRoute::_(JATemplateHelperRoute::getCategoryRoute($item->category_id));
                //$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
            }
            else
            {
                $item->displayCategoryTitle = $show_category ? $item->category_title : '';
            }

            $item->displayRating       = $show_rating ? $item->rating : '';
            //$item->displayAuthorName = $show_author ? $item->author : '';

            if ($show_introtext)
            {
                $item->introtext = JHtml::_('content.prepare', $item->description, '', 'mod_jacontenttype.content');
                $item->introtext = self::_cleanIntrotext($item->description);
            }

            $item->displayDescription = $show_introtext ? self::truncate($item->description, $introtext_limit) : '';
            //$item->displayReadmore  = $item->alternative_readmore;

        }

        return $items;

    }

    /**
     * Strips unnecessary tags from the introtext
     *
     * @param   string  $introtext  introtext to sanitize
     *
     * @return mixed|string
     *
     * @since  1.6
     */
    public static function _cleanIntrotext($introtext)
    {
        $introtext = str_replace('<p>', ' ', $introtext);
        $introtext = str_replace('</p>', ' ', $introtext);
        $introtext = strip_tags($introtext, '<a><em><strong>');
        $introtext = trim($introtext);

        return $introtext;
    }

    /**
     * Method to truncate introtext
     *
     * The goal is to get the proper length plain text string with as much of
     * the html intact as possible with all tags properly closed.
     *
     * @param   string   $html       The content of the introtext to be truncated
     * @param   integer  $maxLength  The maximum number of charactes to render
     *
     * @return  string  The truncated string
     *
     * @since   1.6
     */
    public static function truncate($html, $maxLength = 0)
    {
        $baseLength = strlen($html);

        // First get the plain text string. This is the rendered text we want to end up with.
        $ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

        for ($maxLength; $maxLength < $baseLength;)
        {
            // Now get the string if we allow html.
            $htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

            // Now get the plain text from the html string.
            $htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

            // If the new plain text string matches the original plain text string we are done.
            if ($ptString == $htmlStringToPtString)
            {
                return $htmlString;
            }

            // Get the number of html tag characters in the first $maxlength characters
            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);

            // Set new $maxlength that adjusts for the html tags
            $maxLength += $diffLength;

            if ($baseLength <= $maxLength || $diffLength <= 0)
            {
                return $htmlString;
            }
        }

        return $html;
    }

    public static function jaFilter(&$articles, &$params) {
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();

        // Define null and now dates
        $nullDate	= $db->getNullDate();
        $nowDate	= JFactory::getDate()->toSql();

        //Ajax Paging
        if($app->input->get('t3action') == 'module') {
            //load template language
            $lang = JFactory::getLanguage();
            $lang->load('com_content');
            $lang->load('tpl_'.JFactory::getApplication()->getTemplate());
            $limit = (int) $articles->getState('list.limit', 0);
            if($limit) {
                $page = $app->input->getInt('_module_page', 1);
                if(!$page) {
                    $page = 1;
                }
                $start = ($page - 1) * $limit;

                $articles->setState('list.start', $start);
            }
        }

        // Set the filters based on the module params
        $filter_preset = $params->get('filter_preset', '');
        if(!empty($filter_preset)) {
            switch($filter_preset) {
                case 'all_events':
                    //All Events
                    $articles->metaFilter('content_type', 'event');
                    break;
                case 'upcoming_events':
                    //Upcoming Events
                    $articles->metaFilter('content_type', 'event');
                    $articles->metaFilter('start', $nowDate, '>=');
                    $articles->setMetaOrder('start', 'ASC');
                    break;
                case 'current_events':
                    //Current Events
                    $articles->metaFilter('content_type', 'event');
                    $articles->metaFilter('start', $nowDate, '<=');
                    $articles->metaFilter('end', $nowDate, '>=');
                    $articles->setMetaOrder('start', 'ASC');
                    break;
                case 'past_events':
                    //Upcoming Events
                    $articles->metaFilter('content_type', 'event');
                    $articles->metaFilter('end', $nowDate, '<');
                    $articles->setMetaOrder('end', 'DESC');
                    break;
            }
        } else {

            //Filter by Content Type
            $content_type = $params->get('content_type', '');
            if($content_type) {
                $articles->metaFilter('content_type', $content_type);
            }

            //Filter by Topic
            $topic_id = $params->get('topic_id', '');
            if($topic_id) {
                $articles->metaFilter('topic_id', $topic_id);
            }
        }
    }
}