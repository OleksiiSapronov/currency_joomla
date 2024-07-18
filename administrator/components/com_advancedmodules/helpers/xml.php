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

defined('_JEXEC') or die;

use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Log\Log as JLog;

JLog::add('ModulesHelperXML is deprecated. Do not use.', JLog::WARNING, 'deprecated');

/**
 * Helper for parse XML module files
 *
 * @deprecated  3.2  Do not use.
 */
class ModulesHelperXML
{
    /**
     * Parse the module XML file
     *
     * @param array &$rows XML rows
     *
     * @return  void
     *
     * @deprecated  3.2  Do not use.
     */
    public function parseXMLModuleFile(&$rows)
    {
        foreach ($rows as $i => $row)
        {
            if ($row->module == '')
            {
                $rows[$i]->name    = 'custom';
                $rows[$i]->module  = 'custom';
                $rows[$i]->descrip = 'Custom created module, using Module Manager New function';

                continue;
            }

            $data = JInstaller::parseXMLInstallFile($row->path . '/' . $row->file);

            if ($data['type'] != 'module')
            {
                continue;
            }

            $rows[$i]->name    = $data['name'];
            $rows[$i]->descrip = $data['description'];
        }
    }
}
