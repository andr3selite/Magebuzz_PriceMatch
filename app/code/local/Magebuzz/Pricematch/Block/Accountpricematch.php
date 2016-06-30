<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Accountpricematch extends Mage_Core_Block_Template{
  public function __construct(){
    parent::__construct();
    $collection = $this->getPricematch();
    $this->setCollection($collection);
  }

  public function getPricematch(){  
    $customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
    $collection = Mage::getModel('pricematch/pricematch')->getCollection();
    $collection->addFieldToFilter('customer_email',array('in'=>array($customerEmail)));
    return $collection;
  }

  public function getProduct($productId){
    $product = Mage::getModel('catalog/product')->load($productId);
    return $product;
  }
  
  public function convertPricematch($competitorPrice){
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
    $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
    $competitorPriceConvert = Mage::helper('directory')->currencyConvert($competitorPrice, $baseCurrencyCode, $currentCurrencyCode);
    return $final = round($competitorPriceConvert, 2);
  }
  
  protected function _prepareLayout(){
    parent::_prepareLayout();
      $pager = $this->getLayout()->createBlock('page/html_pager', 'mypricematch.pager')
            ->setCollection($this->getCollection());
      $this->setChild('pager', $pager);
      return $this;
  }
  
  public function getPagerHtml(){
    return $this->getChildHtml('pager');
  }

  public function getCurrentCurrency(){
    return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
  }
}