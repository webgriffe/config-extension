<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 05/12/14
 * Time: 18:20
 */

class Webgriffe_Config_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{
    public function initFields($fieldset, $group, $section, $fieldPrefix = '', $labelPrefix = '')
    {
        $config = Mage::getConfig();

        if (!$config instanceof Webgriffe_Config_Model_Config) {
            return parent::initFields(
                $fieldset,
                $group,
                $section,
                $fieldPrefix,
                $labelPrefix
            );
        }

        $overriddenConfig = $config->getNode(Webgriffe_Config_Model_Config::CONFIG_OVERRIDE_NODE_NAME);
        $skipPathArray = array_keys($config->flatConfig($overriddenConfig));
        $websiteCode = $this->getWebsiteCode();
        $storeCode = $this->getStoreCode();
        if (empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'default';
        } elseif (!empty($websiteCode) && empty($storeCode)) {
            $pathPrefix = 'websites/' . $websiteCode;
        } else { // !empty($websiteCode) && !empty($storeCode)
            $pathPrefix = 'stores/' . $storeCode;
        }

        foreach ($group->fields as $elements) {
            foreach ($elements as $element) {
                $path = (string)$element->config_path;
                if (empty($path)) {
                    $path = $pathPrefix . '/' .
                        $section->getName() . '/' .
                        $group->getName() . '/' .
                        $fieldPrefix . $element->getName();
                }
                if (in_array($path, $skipPathArray)) {
                    $element->show_in_default = false;
                    $element->show_in_website = false;
                    $element->show_in_store = false;
                }
            }
        }

        return parent::initFields(
            $fieldset,
            $group,
            $section,
            $fieldPrefix,
            $labelPrefix
        );
    }

} 
