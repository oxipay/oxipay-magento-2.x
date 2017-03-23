<?php

/**
 * Hidden config field with no label
 */
namespace Oxipay\OxipayPaymentGateway\Block\System\Config\Form\Field;

class Hidden extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        $html = '<td class="label"></td>';
        $html .= $this->_renderValue($element);

        return $this->_decorateRowHtml($element, $html);
    }
    
    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);
        $html .= '</td>';
        return $html;
    }
}
