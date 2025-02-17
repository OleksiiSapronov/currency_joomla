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

use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\ShowOn as RL_ShowOn;

jimport('joomla.filesystem.file');

$this->config->show_assignto_groupusers = (int) (
    $this->config->show_assignto_usergrouplevels
    || $this->config->show_assignto_users
);

$this->config->show_assignto_easyblog      = (int) ($this->config->show_assignto_easyblog && RL_Extension::isInstalled('easyblog'));
$this->config->show_assignto_flexicontent  = (int) ($this->config->show_assignto_flexicontent && RL_Extension::isInstalled('flexicontent'));
$this->config->show_assignto_form2content  = (int) ($this->config->show_assignto_form2content && RL_Extension::isInstalled('form2content'));
$this->config->show_assignto_k2            = (int) ($this->config->show_assignto_k2 && RL_Extension::isInstalled('k2'));
$this->config->show_assignto_zoo           = (int) ($this->config->show_assignto_zoo && RL_Extension::isInstalled('zoo'));
$this->config->show_assignto_akeebasubs    = (int) ($this->config->show_assignto_akeebasubs && RL_Extension::isInstalled('akeebasubs'));
$this->config->show_assignto_hikashop      = (int) ($this->config->show_assignto_hikashop && RL_Extension::isInstalled('hikashop'));
$this->config->show_assignto_mijoshop      = (int) ($this->config->show_assignto_mijoshop && RL_Extension::isInstalled('mijoshop'));
$this->config->show_assignto_redshop       = (int) ($this->config->show_assignto_redshop && RL_Extension::isInstalled('redshop'));
$this->config->show_assignto_virtuemart    = (int) ($this->config->show_assignto_virtuemart && RL_Extension::isInstalled('virtuemart'));
$this->config->show_assignto_cookieconfirm = (int) ($this->config->show_assignto_cookieconfirm && RL_Extension::isInstalled('cookieconfirm'));

$assignments = [
    'menuitems',
    'homepage',
    'date',
    'languages',
    'content',
    'tags',
    'usergrouplevels',
    'users',
    'components',
    'templates',
    'urls',
    'devices',
    'os',
    'browsers',
    'ips',
    'geo',
    'easyblog',
    'flexicontent',
    'form2content',
    'k2',
    'zoo',
    'akeebasubs',
    'hikashop',
    'mijoshop',
    'redshop',
    'virtuemart',
    'cookieconfirm',
    'php',
];

foreach ($assignments as $i => $ass)
{
    if ($ass != 'menuitems' && ( ! isset($this->config->{'show_assignto_' . $ass}) || ! $this->config->{'show_assignto_' . $ass}))
    {
        unset($assignments[$i]);
    }
}

$html = [];

$html[] = $this->render($this->assignments, 'assignments');

$html[] = $this->render($this->assignments, 'mirror_module');
$html[] = '<div class="clear"></div>';
$html[] = RL_ShowOn::open('mirror_module:0', 'advancedparams');

if (count($assignments) > 1)
{
    $html[] = $this->render($this->assignments, 'match_method');
    $html[] = $this->render($this->assignments, 'show_assignments');
}
else
{
    $html[] = '<input type="hidden" name="show_assignments" value="1">';
}

foreach ($assignments as $ass)
{
    $html[] = $this->render($this->assignments, 'assignto_' . $ass);
}

$html[] = RL_ShowOn::close();

echo implode("\n\n", $html);
