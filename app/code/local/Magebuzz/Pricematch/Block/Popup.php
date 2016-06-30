<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Popup extends Mage_Core_Block_Template
{
  public function isCustomerLoggedIn()
  {
    return Mage::getSingleton('customer/session')->isLoggedIn();
  }

  public function getCurrentCustomer()
  {
    return Mage::getSingleton('customer/session')->getCustomer();
  }

  public function getCurrentProduct()
  {
    $params = $this->getRequest()->getParams();
    $product = Mage::getModel('catalog/product')->load($params['id']);
    return $product;
  }

  public function getPostUrl()
  {
    return $this->getUrl('pricematch/index/post', array());
  }

  public function getCurrentCurrency()
  {
    return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
  }

  public function getFormData()
  {
    return Mage::getSingleton('pricematch/session')->getFormData(TRUE);
  }

  public function getFormHeader()
  {
    return Mage::getStoreConfig('pricematch/appearance/header', Mage::app()->getStore());
  }

  public function getTextBeforeForm()
  {
    return Mage::getStoreConfig('pricematch/appearance/text_before_form', Mage::app()->getStore());
  }

  public function getTextAfterForm()
  {
    return Mage::getStoreConfig('pricematch/appearance/text_after_form', Mage::app()->getStore());
  }

  public function isEnabled($option)
  {
    return Mage::getStoreConfig("pricematch/appearance/enable_$option", Mage::app()->getStore());
  }
}