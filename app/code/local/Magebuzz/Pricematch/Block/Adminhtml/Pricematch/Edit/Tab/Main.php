<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Pricematch_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form();
    $form->setHtmlIdPrefix('pricematch_');
    $this->setForm($form);
    $fieldset = $form->addFieldset('pricematch_form', array('legend' => Mage::helper('pricematch')->__('Request Information')));
    $id = $this->getRequest()->getParam('id');
    $pricematch = Mage::getModel('pricematch/pricematch')->load($id);
    $product_id = $pricematch->getData('product_id');
    $_product = Mage::getModel('catalog/product')->load($product_id);
    $baseUrl = Mage::getBaseUrl();
    $product_url = $baseUrl . $_product->getData('url_path');
		$competitor_link = $pricematch->getData('competitor_link');

    $fieldset->addField('product_name', 'link', array(
      'label'              => Mage::helper('pricematch')->__('Product Name'),
      'name'               => 'product_name',
      'href'               => $product_url,
      'after_element_html' => ''
    ));

    $fieldset->addField('date', 'label', array(
      'label'  => Mage::helper('pricematch')->__('Created on'),
      'name'   => 'date',
      'format' => 'dd-MM-yyyy hh:mm:ss',
    ));

    $fieldset->addField('customer_name', 'text', array(
      'label'    => Mage::helper('pricematch')->__('Customer Name'),
      'class'    => 'required-entry',
      'required' => TRUE,
      'name'     => 'customer_name',
    ));

    $fieldset->addField('customer_email', 'text', array(
      'label'    => Mage::helper('pricematch')->__('Customer Email'),
      'class'    => 'required-entry',
      'required' => TRUE,
      'name'     => 'customer_email',
    ));

    $fieldset->addField('customer_phonenumb', 'text', array(
      'label' => Mage::helper('pricematch')->__('Phone Number'),
      'name'  => 'customer_phonenumb',
    ));

    $fieldset->addField('customer_skype', 'text', array(
      'label' => Mage::helper('pricematch')->__('Customer Skype'),
      'name'  => 'customer_skype',
    ));
    $fieldset->addType('extended_label', 'Magebuzz_Pricematch_Block_Extendedlabel');
    $fieldset->addField('current_price', 'extended_label', array(
      'label' => Mage::helper('pricematch')->__('Current Price'),
      'name'  => 'current_price',

    ));

    $fieldset->addField('competitor_price', 'text', array(
      'label'    => Mage::helper('pricematch')->__('Competitor\'s price, ' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getBaseCurrencyCode())->getSymbol()),
      'class'    => 'required-entry',
      'required' => TRUE,
      'name'     => 'competitor_price',

    ));

    $fieldset->addField('competitor_link', 'link', array(
      'label'              => Mage::helper('pricematch')->__('Link'),
      'href'               => $competitor_link,
      'after_element_html' => ''
    ));

    $fieldset->addField('store_name', 'label', array(
      'label' => Mage::helper('pricematch')->__('Store'),
      'name'  => 'store_name',
    ));

    $fieldset->addField('status', 'select', array(
      'label'  => Mage::helper('pricematch')->__('Status'),
      'name'   => 'status',
      'values' => array(
        array(
          'value' => 1,
          'label' => Mage::helper('pricematch')->__('Approved'),
        ),

        array(
          'value' => 2,
          'label' => Mage::helper('pricematch')->__('Rejected'),
        ),

        array(
          'value' => 3,
          'label' => Mage::helper('pricematch')->__('Pending'),
        ),
      ),
    ));

    $fieldset->addField('information', 'textarea', array(
      'label' => Mage::helper('pricematch')->__('Additional Information'),
      'name'  => 'information',
    ));

    if (Mage::getSingleton('adminhtml/session')->getPricematchData()) {
      $form->setValues(Mage::getSingleton('adminhtml/session')->getPricematchData());
      Mage::getSingleton('adminhtml/session')->setPricematchData(null);
    } elseif (Mage::registry('pricematch_data')) {
      $form->setValues(Mage::registry('pricematch_data')->getData());
    }
    return parent::_prepareForm();
  }
}