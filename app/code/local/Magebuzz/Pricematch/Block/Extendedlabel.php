<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Extendedlabel extends Varien_Data_Form_Element_Abstract
{
  public function __construct($attributes = array())
  {
    parent::__construct($attributes);
    $this->setType('label');
  }

  /**
   * Retrieve Element HTML
   *
   * @return string
   */
  public function getElementHtml()
  {
    $html = $this->getBold() ? '<strong>' : '';
    $html .= Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol() . $this->getEscapedValue();
    $html .= $this->getBold() ? '</strong>' : '';
    $html .= $this->getAfterElementHtml();
    return $html;
  }
}
