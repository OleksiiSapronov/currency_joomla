<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

// Protect from unauthorized access
use JchOptimize\ContainerFactory;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\GetApplicationTrait;
use JchOptimize\Model\OrderPlugins;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Component\Plugins\Administrator\Model\PluginModel;
use Joomla\Filesystem\File;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class Pkg_JchoptimizeInstallerScript extends InstallerScript
{
    /**
     * The primary field of the paramTable
     *
     * @var string
     */
    protected $primaryKey = 'extension_id';
    /**
     * The minimum PHP version required to install this extension
     *
     * @var   string
     */
    protected $minimumPhp = '7.4.0';

    /**
     * The minimum Joomla! version required to install this extension
     *
     * @var   string
     */
    protected $minimumJoomla = '3.10.0';

    /**
     * The maximum Joomla! version this extension can be installed on
     *
     * @var   string
     */
    protected $allowDowngrades = true;


    /**
     * A list of extensions (modules, plugins) to enable after installation. Each item has four values, in this order:
     * type (plugin, module, ...), name (of the extension), client (0=site, 1=admin), group (for plugins).
     *
     * @var array
     */
    protected $extensionsToEnable = [
        'plg_system_jchoptimize',
        'plg_user_jchoptimizeuserstate',
        'mod_jchmodeswitcher'
    ];

    /**
     * Joomla! pre-flight event. This runs before Joomla! installs or updates the package. This is our last chance to
     * tell Joomla! if it should abort the installation.
     *
     * In here we'll try to install FOF. We have to do that before installing the component since it's using an
     * installation script extending FOF's InstallScript class. We can't use a <file> tag in the manifest to install FOF
     * since the FOF installation is expected to fail if a newer version of FOF is already installed on the site.
     *
     * @param string $type Installation type (install, update, discover_install)
     * @param PackageAdapter $parent Parent object
     *
     * @return  boolean  True to let the installation proceed, false to halt the installation
     */
    public function preflight($type, $parent): bool
    {
        if (!parent::preflight($type, $parent)) {
            return false;
        }

        if ($type === 'uninstall') {
            return true;
        }

        $manifest = $parent->getManifest();
        $newVariant = (string)$manifest->variant;

        $files = [];
        $files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jchoptimize.xml';
        $files[] = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_jch_optimize.xml';

        foreach ($files as $file) {
            if (file_exists($file)) {
                $xml = simplexml_load_file($file);
                $oldVariant = (string)$xml->variant;

                if ($oldVariant == 'PRO' && $newVariant == 'FREE') {
                    $msg = '<p>You are trying to install the FREE version of JCH Optimize, but you currently have the PRO version installed. You must uninstall the PRO version first before you can install the FREE version.</p>';
                    Log::add($msg, Log::WARNING, 'jerror');

                    return false;
                }

                break;
            }
        }

        return true;
    }

    /**
     * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
     * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
     * database updates and similar housekeeping functions.
     *
     * @param string $type install, update or discover_update
     * @param PackageAdapter $parent Parent object
     */
    public function postflight(string $type, PackageAdapter $parent)
    {
        if ($type == 'uninstall') {
            return;
        }

        $this->removeOldJchOptimizePackage();

        $installer = new Installer();

        if (version_compare(JVERSION, '4.0', '<')) {
            //Uninstall console plugin on joomla3
            $ids = $this->getInstances(false, 'plg_console_jchoptimize');
            if (!empty($ids)) {
                $id = $ids[0];
                $installer->uninstall('plugin', $id);
            }
        } else {
            //Uninstall cli scripts on joomla4
            $ids = $this->getInstances(false, 'file_jchoptimize');
            if (!empty($ids)) {
                $id = $ids[0];
                $installer->uninstall('file', $id);
            }

            $this->enableExtension('plg_console_jchoptimize');
        }

        if ($type == 'update') {
            /**
             * Clean up the obsolete package update sites.
             *
             * If you specify a new update site location in the XML manifest Joomla will install it in the #__update_sites
             * table but it will NOT remove the previous update site. This method removes the old update sites which are
             * left behind by Joomla.
             */
            $this->removeObsoleteUpdateSites();

            //Delete static cache files
            try {
                $staticCacheFolder = JPATH_ROOT . '/media/com_jchoptimize/cache';

                if (file_exists($staticCacheFolder)) {
                    Folder::delete($staticCacheFolder);
                }
            } catch (Throwable $e) {
                //Don't cry
            }

            //File changes can occur between versions. Let us dare to reset opcache
            // to avoid issues
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            clearstatcache();
        }

        //Let's try to load the autoloader if not already loaded
        $filePath = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

        if (file_exists($filePath) && is_readable($filePath)) {
            include($filePath);

            //We may have to individually load classes added in this version; If autoloader runs already
            //they wouldn't be included.
            $classMap = [
                GetApplicationTrait::class => JPATH_ADMINISTRATOR . '/components/com_jchoptimize/lib/src/GetApplicationTrait.php',
            ];

            foreach ($classMap as $class => $file) {
                if (!class_exists($class) && file_exists($file)) {
                    include_once($file);
                }
            }

            //leverage browser caching
            Tasks::leverageBrowserCaching();

            if (class_exists(ContainerFactory::class)) {
                //Order plugins
                $container = ContainerFactory::getNewContainerInstance();
                /** @see OrderPlugins::orderPlugins() */
                $container->get(OrderPlugins::class)->orderPlugins();
            }
        }

        $this->fixMetaFileSecurityIssue();
    }


    private function removeOldJchOptimizePackage()
    {
        //Get id of old package
        $packageIds = $this->getInstances(false, 'pkg_jch_optimize');

        if (empty($packageIds)) {
            return;
        }

        $packageId = (int)$packageIds[0];

        //Get id of new component
        $componentIds = $this->getInstances(false, 'com_jchoptimize');

        if (empty($componentIds)) {
            return;
        }

        $componentId = (int)$componentIds[0];

        //Get id of old plugin
        $pluginIds = $this->getInstances(false, 'plg_system_jch_optimize');

        if (empty($pluginIds)) {
            return;
        }

        $pluginId = (int)$pluginIds[0];

        //Get plugin parameters
        $pluginParams = $this->getItemArray('params', '#__extensions', 'extension_id', $pluginId);

        //Transfer settings to new component
        try {
            $this->setParams($pluginParams, 'edit', $componentId);
        } catch (\Exception $e) {
            $msg = "<p>We weren't able to transfer the settings from the plugin to the component. You may have to reconfigure JCH Optimize.</p>";
            Log::add($msg, Log::WARNING, 'jerror');
        }

        //Uninstall old package
        try {
            $installer = new Installer();
            $installer->uninstall('package', $packageId);
        } catch (Exception $e) {
            $msg = "<p>We weren't able to uninstall the previous version of JCH Optimize. You'll need to do that from the Extensions Manager.</p>";
            Log::add($msg, Log::WARNING, 'jerror');
        }
    }

    /**
     * Removes the obsolete update sites for the component, since now we're dealing with a package.
     *
     * Controlled by componentName, packageName and obsoleteUpdateSiteLocations
     *
     * Depends on getExtensionId, getUpdateSitesFor
     *
     * @return  void
     */
    private function removeObsoleteUpdateSites()
    {
        // Get package ID
        $packageIds = $this->getInstances(false);
        if (empty($packageIds)) {
            return;
        }

        $packageID = $packageIds[0];

        // All update sites for the package
        $deleteIDs = $this->getUpdateSitesFor($packageID);

        if (empty($deleteIDs)) {
            $deleteIDs = [];
        }

        if (count($deleteIDs) <= 1) {
            return;
        }

        $deleteIDs = array_unique($deleteIDs);

        // Remove the latest update site, the one we just installed
        array_pop($deleteIDs);

        $db = Factory::getDbo();

        if (empty($deleteIDs) || !count($deleteIDs)) {
            return;
        }

        // Delete the remaining update sites
        $deleteIDs = array_map([$db, 'q'], $deleteIDs);

        $query = $db->getQuery(true)
            ->delete($db->qn('#__update_sites'))
            ->where($db->qn('update_site_id') . ' IN(' . implode(',', $deleteIDs) . ')');

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            // Do nothing.
        }

        $query = $db->getQuery(true)
            ->delete($db->qn('#__update_sites_extensions'))
            ->where($db->qn('update_site_id') . ' IN(' . implode(',', $deleteIDs) . ')');

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            // Do nothing.
        }
    }

    /**
     * Returns the update site IDs for the specified Joomla Extension ID.
     *
     * @param int $eid Extension ID for which to retrieve update sites
     *
     * @return  array  The IDs of the update sites
     */
    private function getUpdateSitesFor($eid = null)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn('s.update_site_id'))
            ->from($db->qn('#__update_sites', 's'))
            ->innerJoin(
                $db->qn('#__update_sites_extensions', 'e') . 'ON(' . $db->qn('e.update_site_id') .
                ' = ' . $db->qn('s.update_site_id') . ')'
            )
            ->where($db->qn('e.extension_id') . ' = ' . $db->q($eid));

        try {
            $ret = $db->setQuery($query)->loadColumn();
        } catch (Exception $e) {
            return [];
        }

        return empty($ret) ? [] : $ret;
    }

    /**
     * Runs on installation (but not on upgrade). This happens in install and discover_install installation routes.
     *
     * @param \JInstallerAdapterPackage $parent Parent object
     *
     * @return  bool
     */
    public function install($parent)
    {
        // Enable the extensions we need to install
        $this->enableExtensions();

        return true;
    }

    /**
     * Enable modules and plugins after installing them
     */
    private function enableExtensions()
    {
        foreach ($this->extensionsToEnable as $ext) {
            $this->enableExtension($ext);
        }
    }

    /**
     * Enable an extension
     *
     * @param null $extension
     */
    private function enableExtension($extension = null)
    {
        $extension = $extension ?? $this->extension;

        $ids = $this->getInstances(false, $extension);

        if (empty($ids)) {
            return;
        }

        $id = (int)$ids[0];

        try {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->update('#__extensions')
                ->set($db->quoteName('enabled') . ' = ' . $db->quote(1))
                ->where($db->quoteName('extension_id') . ' = ' . $db->quote($id));
            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
        }
    }

    /**
     * Gets each instance of a module in the #__modules table or extension in the #__extensions table
     *
     * @param boolean $isModule True if the extension is a module as this can have multiple instances
     * @param string $extension Name of extension to find instance of
     *
     * @return  array  An array of ID's of the extension
     *
     * @since   3.6
     */
    public function getInstances($isModule, $extension = null)
    {
        $extension = $extension ?? $this->extension;

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select the item(s) and retrieve the id
        if ($isModule) {
            $query->select($db->quoteName('id'));
            $query->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = ' . $db->quote($extension));
        } else {
            $query->select($db->quoteName('extension_id'));
            $query->from($db->quoteName('#__extensions'));
            //Special handling for plugins, we extract the element and folder from the extension name
            $parts = explode('_', $extension, 3);

            if (count($parts) == 3 && $parts[0] == 'plg') {
                $extension = $parts[2];
                $folder = $parts[1];

                $query->where($db->quoteName('folder') . ' = ' . $db->quote($folder));
            }

            $query->where($db->quoteName('element') . ' = ' . $db->quote($extension));
        }

        // Set the query and obtain an array of id's
        return $db->setQuery($query)->loadColumn();
    }


    /**
     * Sets parameter values in the extensions row of the extension table. Note that the
     * this must be called separately for deleting and editing. Note if edit is called as a
     * type then if the param doesn't exist it will be created
     *
     * @param array $paramArray The array of parameters to be added/edited/removed
     * @param string $type The type of change to be made to the param (edit/remove)
     * @param integer $id The id of the item in the relevant table
     *
     * @return  boolean  True on success
     *
     * @since   3.6
     */
    public function setParams($paramArray = null, $type = 'edit', $id = 0)
    {
        if (!\is_int($id) || $id == 0) {
            // Return false if there is no valid item given
            return false;
        }

        $params = $this->getItemArray('params', $this->paramTable, $this->primaryKey, $id);

        if ($paramArray) {
            foreach ($paramArray as $name => $value) {
                if ($type === 'edit') {
                    // Add or edit the new variable(s) to the existing params
                    if (\is_array($value)) {
                        // Convert an array into a json encoded string
                        $params[(string)$name] = array_values($value);
                    } else {
                        $params[(string)$name] = (string)$value;
                    }
                } elseif ($type === 'remove') {
                    // Unset the parameter from the array
                    unset($params[(string)$name]);
                }
            }
        }

        // Store the combined new and existing values back as a JSON string
        $paramsString = json_encode($params);

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName($this->paramTable))
            ->set($db->quoteName('params') . ' = ' . $db->quote($paramsString))
            ->where($db->quoteName($this->primaryKey) . ' = ' . $db->quote($id));

        // Update table
        $db->setQuery($query)->execute();

        return true;
    }

    /**
     * The full paths to optimized files were added to the metafile, that was available from the internet. this
     * function corrects that.
     *
     * @return void
     */
    private function fixMetaFileSecurityIssue(): void
    {
        $metaFile = AdminHelper::getMetaFile();
        $metaFileDir = dirname($metaFile);

        if (file_exists($metaFile)
            && (!file_exists($metaFileDir . '/index.html')
                || !file_exists($metaFileDir . '/.htaccess'))
        ) {
            /** @var string[] $optimizedFiles */
            $optimizedFiles = AdminHelper::getOptimizedFiles();
            File::delete($metaFile);

            foreach ($optimizedFiles as $files) {
                AdminHelper::markOptimized($files);
            }
        }
    }
}
