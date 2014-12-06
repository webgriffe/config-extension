<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 06/12/14
 * Time: 13:50
 */

class Webgriffe_Config_Model_Config_Data extends Mage_Core_Model_Config_Data
{
    public function getValue()
    {
        return $this->_getValueFromOverriddenConfig();
    }

    /**
     * @return string
     */
    protected function _getValueFromOverriddenConfig()
    {
        $configModel = Mage::getConfig();
        $pathPrefix = Mage::helper('webgriffe_config')->computePathPrefix($this->getWebsite(), $this->getStore());
        return (string)$configModel->getNode($pathPrefix . '/' . $this->getPath());
    }
}
