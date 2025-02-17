<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;

$data = $displayData;

// Receive overridable options
$data['options'] = ($data['options'] ?? null) ?: [];

if ($data['view'] instanceof AdvancedModulesViewModules && JFactory::getApplication()->input->get('layout', '', 'cmd') !== 'modal')
{
    RL_Document::styleDeclaration("
        /* Fixed filter field in search bar */
        .js-stools .js-stools-client_id {
            float: left;
            margin-right: 10px;
            min-width: 220px;
        }
        html[dir=rtl] .js-stools .js-stools-client_id {
            float: right;
            margin-left: 10px
            margin-right: 0;
        }
        .js-stools .js-stools-container-bar .js-stools-field-filter .chzn-container {
            padding: 3px 0;
        }
    ");

    // Client selector doesn't have to activate the filter bar.
    unset($data['view']->activeFilters['client_id']);
}

// Display the main joomla layout.
include JPATH_SITE . '/layouts/joomla/searchtools/default.php';
