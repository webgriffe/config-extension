<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 31/10/14
 * Time: 16:57
 */

class Webgriffe_Config_Model_Config extends Mage_Core_Model_Config
{
    const SERVER_VAR_NAME = 'MAGE_ENVIRONMENT';
    const OVERRIDE_FILENAME = 'config-override.xml';
    const ENV_OVERRIDE_PATTERN = 'config-override-%s.xml';
    const BASE_OVERRIDE_FILENAME = 'local-override.xml';
    const BASE_ENV_OVERRIDE_PATTERN = 'local-override-%s.xml';
    const CONFIG_OVERRIDE_NODE_NAME = 'config_override';

    protected $_overrideFilesProcessed = array();

    public function loadBase()
    {
        parent::loadBase();
        $this->_loadOverride(self::BASE_OVERRIDE_FILENAME, false);
        if ($this->_isEnvironmentSet()) {
            $this->_loadOverride($this->_getBaseEnvOverrideFilename(), false);
        }
        return $this;
    }

    /**
     * This override is needed because in the loadModules method Magento prevents local.xml overwriting that is
     * exactly what we have to do. So we need to re-override base configuration after the loadMoudles.
     *
     * @return $this|Mage_Core_Model_Config
     */
    public function loadModules()
    {
        parent::loadModules();
        $this->_loadOverride(self::BASE_OVERRIDE_FILENAME, false);
        if ($this->_isEnvironmentSet()) {
            $this->_loadOverride($this->_getBaseEnvOverrideFilename(), false);
        }
        return $this;
    }


    public function loadDb()
    {
        parent::loadDb();
        $this->_loadOverride(self::OVERRIDE_FILENAME);
        if ($this->_isEnvironmentSet()) {
            $this->_loadOverride($this->_getEnvOverrideFilename());
        }
        return $this;
    }

    /**
     * @param $overrideFilename
     * @param bool $inheritConfig
     */
    protected function _loadOverride($overrideFilename, $inheritConfig = true)
    {
        $etcDir = $this->getOptions()->getEtcDir();
        $file = $etcDir . DS . $overrideFilename;
        $merge = clone $this->_prototype;
        if ($merge->loadFile($file) === false) {
            return;
        }
        $this->_overrideFilesProcessed[] = $overrideFilename;

        if ($inheritConfig) {
            $websites = array_keys($this->getNode('websites')->asArray());
            $this->_inheritDefaultConfigToWebsites($merge, $websites);
            $this->_inheritWebsitesConfigToStores($websites, $merge);
        }

        $this->_copyOverriddenConfigToDedicatedNode($merge);
        $this->extend($merge);
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

    protected function _getBaseEnvOverrideFilename()
    {
        return sprintf(self::BASE_ENV_OVERRIDE_PATTERN, $this->_getCurrentEnvironment());
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
    public function getOverrideFilesProcessed()
    {
        return $this->_overrideFilesProcessed;
    }
}
