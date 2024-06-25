<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;

/**
 * Script file of Video Field plugin
 */
class plgContentInsertimageInstallerScript extends InstallerScript
{
    /**
     * Method to install the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    function install($parent) 
    {

    }

    /**
     * Method to uninstall the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    function uninstall($parent) 
    {

    }

    /**
     * Method to update the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    function update($parent) 
    {

    }

    /**
     * Method to run before an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return void
     */
    function preflight($type, $parent) 
    {

    }

    /**
     * Method to run after an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
        // Get the plugin installation path for the site side
        $path = $parent->getParent()->getPath('extension_site');

        // Define the path to the "language" folder within the plugin's directory
        $this->deleteFolders[] = $path . '/language';

        // Define the path to the "language" folder within the plugin's directory
        $this->deleteFiles[] = $path . '/script.php';

        // Call removeFiles to delete the folders listed in deleteFolders
        $this->removeFiles($parent);
    }
}