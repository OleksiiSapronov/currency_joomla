<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ModuleAdapter;
use Joomla\CMS\Installer\InstallerScript;

defined('_JEXEC') or die('Restricted Access');

class Mod_JchmodeswitcherInstallerScript extends InstallerScript
{
    protected $extensionClient = 1;

    protected $allowDowngrades = true;

    public function postflight(string $type, ModuleAdapter $parent)
    {
        if ($type == 'install' || $type == 'update') {
            $ids = $this->getInstances(true);
            if (empty($ids)) {
                return true;
            }

            $db = Factory::getDbo();

            //Get highest order
            $query = $db->getQuery(true);
            $query->select($db->quoteName('ordering'))
                  ->from('#__modules')
                  ->where($db->quoteName('position') . ' = ' . $db->quote('status'))
                  ->where($db->quoteName('client_id') . ' = ' . $db->quote($this->extensionClient))
                  ->order('ordering DESC');
            $db->setQuery($query, 0, 1);
            $highestOrder = $db->loadResult();

            $order = $highestOrder++;

            $options = [
                    'ordering' => (int)$order,
                    'position' => 'status',
                    'access'   => 2
            ];

            //Should be only one instance
            $this->publishModule($ids[0], $options);
        }

        return true;
    }

    private function publishModule($id, $options = [])
    {
        $db = Factory::getDbo();

        //Check if module in modules_menu table
        $query = $db->getQuery(true)
                    ->select($db->quoteName('moduleid'))
                    ->from('#__modules_menu')
                    ->where($db->quoteName('moduleid') . ' = ' . (int)$id);
        $db->setQuery($query, 0, 1);

        if ($db->loadResult()) {
            return;
        }

        //publish module
        $query->clear()
              ->update('#__modules')
              ->set($db->quoteName('published') . ' = 1');

        foreach ($options as $field => $value) {
            $query->set($db->quoteName($field) . ' = ' . $db->quote($value));
        }

        $query->where($db->quoteName('id') . ' = ' . (int)$id);
        $db->setQuery($query);
        $db->execute();

        //add module to the modules_menu table
        $query->clear()
              ->insert('#__modules_menu')
              ->columns([$db->quoteName('moduleid'), $db->quoteName('menuid')])
              ->values((int)$id . ',  0');
        $db->setQuery($query);
        $db->execute();
    }
}
