<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 31/10/14
 * Time: 16:57
 */

class Webgriffe_Config_Model_Config extends Mage_Core_Model_Config
{
    const OVERRIDE_FILENAME = 'config-override.xml';

    public function loadDb()
    {
        parent::loadDb();
        $this->_loadOverride();
        return $this;
    }

    protected function _loadOverride()
    {
        $etcDir = $this->getOptions()->getEtcDir();
        $file = $etcDir . DS . self::OVERRIDE_FILENAME;
        $merge = clone $this->_prototype;
        $merge->loadFile($file);

        $websites = array_keys($this->getNode('websites')->asArray());

        $this->_inheritDefaultConfigToWebsites($merge, $websites);
        $this->_inheritWebsitesConfigToStores($websites, $merge);

        $this->extend($merge);
    }

    protected function _flatConfig(Varien_Simplexml_Element $node, array $flat = array(), $flatKey = '')
    {
        if ($node->hasChildren()) {
            foreach ($node->children() as $key => $child) {
                /** @var $child Varien_Simplexml_Element */
                if ($child->hasChildren()) {
                    $flat = $this->_flatConfig($child, $flat, $flatKey . $key . '/');
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
        $flattenDefault = $this->_flatConfig($merge->getNode('default'));
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
                $flattenWebsite = $this->_flatConfig($merge->getNode('websites/' . $websiteCode));
                foreach ($flattenWebsite as $path => $value) {
                    $merge->setNode('stores/' . $store . '/' . $path, $value, false);
                }
            }
        }
    }
} 
