<?php

/**
 * Renderer for FuturePay banner in System Configuration
 */
class FuturePay_FuturePay_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'futurepay/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();
        if (isset($elementOriginalData['help_link'])) {
            $this->setHelpLink($elementOriginalData['help_link']);
        }
        $js = <<<JS
document.observe("dom:loaded", function() {
    FuturePayAdmin.displaySignupLoginTpl();
});


JS;
        return $this->toHtml() . $this->helper('adminhtml/js')->getScript($js);
    }
}
