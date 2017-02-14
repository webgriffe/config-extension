<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 05/12/14
 * Time: 18:20
 */

class Webgriffe_Config_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    /**
     * @var Webgriffe_Config_Model_Config_Override
     */
    protected $_configOverrideModel;

    protected $_overriddenPaths;

    public function initFields($fieldset, $group, $section, $fieldPrefix = '', $labelPrefix = '')
    {
        $configModel = Mage::getConfig();

        $this->_configOverrideModel = $configModel->getConfigOverrideModel();

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
                    $this->_adjustOverriddenConfigElement($element);
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
     * @param Mage_Core_Model_Config $configModel
     */
    protected function _initOverriddenPaths(Mage_Core_Model_Config $configModel)
    {
        $overriddenConfig = $configModel->getNode(Webgriffe_Config_Model_Config_Override::CONFIG_OVERRIDE_NODE_NAME);
        $this->_overriddenPaths = array_keys($this->_configOverrideModel->flatConfig($overriddenConfig));
    }

    /**
     * @param $path
     * @return bool
     */
    protected function _isOverridden($path)
    {
        return in_array($path, $this->_overriddenPaths);
    }

    protected function _getOverrideFilesList()
    {
        return implode(
            '<br/>',
            array_map(
                function($value) {
                    return "<em>app/etc/$value</em>";
                },
                $this->_configOverrideModel->getOverrideFilesProcessed()
            )
        );
    }

    /**
     * @param $element
     */
    protected function _adjustOverriddenConfigElement($element) {
        $element->frontend_model = 'webgriffe_config/adminhtml_system_config_form_overriddenField';
        $element->backend_model = 'webgriffe_config/config_data';
        $element->comment = $this->__(
            'This config setting has been forced to shown value by Webgriffe_Config extension. Check the ' .
            'following files: %s',
            $this->_getOverrideFilesList()
        );
    }

} 
