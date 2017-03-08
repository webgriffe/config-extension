<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 13/12/14
 * Time: 16:03
 */

class Webgriffe_Config_Model_Config_Override
{
    const SERVER_VAR_NAME = 'MAGE_ENVIRONMENT';
    const LOAD_ECOMDEV_PHPUNIT_CONFIG_SERVER_VAR_NAME = 'MAGE_LOAD_ECOMDEV_PHPUNIT_CONFIG';
    const OVERRIDE_FILENAME = 'config-override.xml';
    const ENV_OVERRIDE_PATTERN = 'config-override-%s.xml';
    const CONFIG_OVERRIDE_NODE_NAME = 'config_override';
    const ECOMDEV_PHPUNIT_CONFIG_FILENAME = 'local.xml.phpunit';
    const DIST_EXTENSION = 'dist';

    /**
     * @var Mage_Core_Model_Config_Base
     */
    protected $_prototype;

    protected $_overrideFilesProcessed = array();

    public function __construct($_prototype)
    {
        $this->_prototype = $_prototype;
        $this->_prototype->setXml(new Varien_Simplexml_Element('<config></config>'));
    }

    public function getBaseOverride()
    {
        $merge = clone $this->_prototype;
        $merge->setXml(new Varien_Simplexml_Element('<config></config>'));
        if ($this->_isEcomdevPhpunitConfigLoadEnabled()) {
            $merge->extend($this->_loadOverride(self::ECOMDEV_PHPUNIT_CONFIG_FILENAME, false));
        }
        return $merge;
    }

    public function getOverride()
    {
        $merge = $this->_loadOverride($this->_getDistFilename(self::OVERRIDE_FILENAME));
        $merge->extend($this->_loadOverride(self::OVERRIDE_FILENAME));
        if ($this->_isEnvironmentSet()) {
            $merge->extend($this->_loadOverride($this->_getDistFilename($this->_getEnvOverrideFilename())));
            $merge->extend($this->_loadOverride($this->_getEnvOverrideFilename()));
        }
        return $merge;
    }

    /**
     * @return array
     */
    public function getOverrideFilesProcessed()
    {
        return $this->_overrideFilesProcessed;
    }

    public function flatConfig($node, array $flat = array(), $flatKey = '')
    {
        if ($node instanceof Varien_Simplexml_Element && $node->hasChildren()) {
            foreach($node->children() as $key => $child) {
                /** @var $child Varien_Simplexml_Element */
                if ($child->hasChildren()) {
                    $flat = $this->flatConfig($child, $flat, $flatKey . $key . '/');
                    continue;
                }

                $flat[$flatKey . $key] = (string)$child;
            }
        }

        return $flat;
    }

    /**
     * @param $overrideFilename
     * @param bool $inheritConfig
     * @return \Mage_Core_Model_Config_Base
     */
    protected function _loadOverride($overrideFilename, $inheritConfig = true)
    {
        $etcDir = Mage::getConfig()->getOptions()->getEtcDir();
        $file = $etcDir . DS . $overrideFilename;
        $merge = clone $this->_prototype;
        $merge->setXml(new Varien_Simplexml_Element('<config></config>'));
        if ($merge->loadFile($file) === false) {
            return $merge;
        }
        $this->_overrideFilesProcessed[] = $overrideFilename;

        if ($inheritConfig) {
            $websites = $this->_getWebsites();
            $this->_inheritDefaultConfigToWebsites($merge, $websites);
            $this->_inheritWebsitesConfigToStores($websites, $merge);
        }

        $this->_copyOverriddenConfigToDedicatedNode($merge);
        return $merge;
    }

    /**
     * @param $merge
     * @param $websites
     */
    protected function _inheritDefaultConfigToWebsites($merge, $websites)
    {
        $flattenDefault = $this->flatConfig($merge->getNode('default'));
        foreach ($flattenDefault as $path => $value) {
            foreach ($websites as $website) {
                $merge->setNode('websites/' . $website . '/' . $path, $value, false);
            }
        }
    }

    /**
     * @param $websites
     * @param $merge
     */
    protected function _inheritWebsitesConfigToStores($websites, $merge)
    {
        foreach ($websites as $websiteCode) {
            $website = Mage::getModel('core/website')->load($websiteCode);
            $stores = $website->getStores();
            if ($websiteCode === 'admin') {
                $stores = array(Mage::getModel('core/store')->load('admin'));
            }
            foreach ($stores as $store) {
                $store = $store->getCode();
                $flattenWebsite = $this->flatConfig($merge->getNode('websites/' . $websiteCode));
                foreach ($flattenWebsite as $path => $value) {
                    $merge->setNode('stores/' . $store . '/' . $path, $value, false);
                }
            }
        }
    }

    protected function _getEnvOverrideFilename()
    {
        return sprintf(self::ENV_OVERRIDE_PATTERN, $this->_getCurrentEnvironment());
    }

    /**
     * @throws RuntimeException
     * @return string Returns current environment by reading the environment variable "MAGE_ENVIRONMENT". Default
     * environment is "prod".
     */
    protected function _getCurrentEnvironment()
    {
        if (isset($_SERVER[self::SERVER_VAR_NAME])) {
            return $_SERVER[self::SERVER_VAR_NAME];
        }

        throw new RuntimeException('Current environment has not been set.');
    }

    protected function _isEnvironmentSet()
    {
        return isset($_SERVER[self::SERVER_VAR_NAME]);
    }

    /**
     * @param Mage_Core_Model_Config_Base $merge
     */
    protected function _copyOverriddenConfigToDedicatedNode($merge)
    {
        $node = $merge->getNode();
        if ($node) {
            $flatConfig = $this->flatConfig($node);
            foreach ($flatConfig as $path => $value) {
                $merge->setNode(self::CONFIG_OVERRIDE_NODE_NAME . '/' . $path, $value);
            }
        }
    }

    /**
     * @return array
     */
    protected function _getWebsites()
    {
        $websites = array();
        foreach (Mage::getConfig()->getNode('websites')->children() as $website) {
            /** @var Varien_Simplexml_Element $website */
            $websites[] = $website->getName();
        }
        return $websites;
    }

    /**
     * @return bool
     */
    protected function _isEcomdevPhpunitConfigLoadEnabled()
    {
        return isset($_SERVER[self::LOAD_ECOMDEV_PHPUNIT_CONFIG_SERVER_VAR_NAME]) &&
            (bool)$_SERVER[self::LOAD_ECOMDEV_PHPUNIT_CONFIG_SERVER_VAR_NAME];
    }

    /**
     * @param $filename
     * @return string
     */
    private function _getDistFilename($filename)
    {
        return $filename . '.' . self::DIST_EXTENSION;
    }
} 
