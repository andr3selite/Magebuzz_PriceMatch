<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Renderer_Currentprice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
  public function render(Varien_Object $row)
  {
    $data = $row->getData();
    $product_id = $data['product_id'];
    $_product = Mage::getModel('catalog/product')->load($product_id);
    $_taxHelper = Mage::helper('tax');
    $current_price = $_taxHelper->getPrice($_product, $_product->getPrice());
		return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol().$current_price;
  }
}