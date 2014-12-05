<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 05/12/14
 * Time: 18:20
 */

class Webgriffe_Config_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    protected $_overriddenPaths;

    public function initFields($fieldset, $group, $section, $fieldPrefix = '', $labelPrefix = '')
    {
        $configModel = Mage::getConfig();

        if (!$configModel instanceof Webgriffe_Config_Model_Config) {
            return parent::initFields($fieldset, $group, $section, $fieldPrefix, $labelPrefix);
        }

        if (!$this->_overriddenPaths) {
            $this->_initOverriddenPaths($configModel);
        }
        $pathPrefix = $this->_computePathPrefix($this->getWebsiteCode(), $this->getStoreCode());

        foreach ($group->fields as $elements) {
            foreach ($elements as $element) {
                $path = $this->_computeElementPath($group, $section, $fieldPrefix, $element, $pathPrefix);
                if ($this->_isOverridden($path)) {
                    $element->show_in_default = false;
                    $element->show_in_website = false;
                    $element->show_in_store = false;
                }
            }
        }

        return parent::initFields($fieldset, $group, $section, $fieldPrefix, $labelPrefix);
    }

    /**
     * @param $websiteCode
     * @param $storeCode
     * @return string
     */
    protected function _computePathPrefix($websiteCode, $storeCode)
    {
        if (empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'default';
            return $pathPrefix;
        } elseif (!empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'websites/' . $websiteCode;
            return $pathPrefix;
        } else { // !empty($websiteCode) && !empty($storeCode)
            $pathPrefix = 'stores/' . $storeCode;
            return $pathPrefix;
        }
    }

    /**
     * @param $group
     * @param $section
     * @param $fieldPrefix
     * @param $element
     * @param $pathPrefix
     * @return string
     */
    protected function _computeElementPath($group, $section, $fieldPrefix, $element, $pathPrefix)
    {
        $configPath = (string)$element->config_path;
        $path = $pathPrefix . '/' . $configPath;
        if (empty($configPath)) {
            $path = $pathPrefix . '/' .
                $section->getName() . '/' .
                $group->getName() . '/' .
                $fieldPrefix . $element->getName();
            return $path;
        }
        return $path;
    }

    /**
     * @param $configModel
     */
    protected function _initOverriddenPaths($configModel)
    {
        $overriddenConfig = $configModel->getNode(Webgriffe_Config_Model_Config::CONFIG_OVERRIDE_NODE_NAME);
        $this->_overriddenPaths = array_keys($configModel->flatConfig($overriddenConfig));
    }

    /**
     * @param $path
     * @return bool
     */
    protected function _isOverridden($path)
    {
        return in_array($path, $this->_overriddenPaths);
    }

} 
