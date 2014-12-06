<?php
/**
 * Created by PhpStorm.
 * User: manuele
 * Date: 05/12/14
 * Time: 20:41
 */

class Webgriffe_Config_Block_Adminhtml_System_Config_Form_OverriddenField
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $value = $element->getEscapedValue();
        return <<<HTML
<span id="$id" style="font-family: Courier New, Courier, monospace"><strong>$value</strong></span>
HTML;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_preventInheritCheckboxShowing($element);
        return parent::render($element);
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     */
    protected function _preventInheritCheckboxShowing(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);
    }


}
