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
        $pathPrefix = Mage::helper('webgriffe_config')->computePathPrefix(
            $this->getWebsiteCode(),
            $this->getStoreCode()
        );

        foreach ($group->fields as $elements) {
            foreach ($elements as $element) {
                $path = $this->_computeElementPath($group, $section, $fieldPrefix, $element, $pathPrefix);
                if ($this->_isOverridden($path)) {
                    $this->_adjustOverriddenConfigElement($element, $configModel);
                }
            }
        }

        return parent::initFields($fieldset, $group, $section, $fieldPrefix, $labelPrefix);
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

    protected function _getOverrideFilesList(Webgriffe_Config_Model_Config $configModel)
    {
        return implode(
            '<br/>',
            array_map(
                function($value) {
                    return "<em>app/etc/$value</em>";
                },
                $configModel->getOverrideFilesProcessed()
            )
        );
    }

    /**
     * @param $element
     * @param $configModel
     */
    protected function _adjustOverriddenConfigElement($element, $configModel)
    {
        $element->frontend_model = 'webgriffe_config/adminhtml_system_config_form_overriddenField';
        $element->backend_model = 'webgriffe_config/config_data';
        $element->comment = $this->__(
            'This config setting has been forced to shown value by Webgriffe_Config extension. Check the ' .
            'following files: %s',
            $this->_getOverrideFilesList($configModel)
        );
    }

} 
