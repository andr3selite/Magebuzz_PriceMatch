<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Pricematch_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('pricematch_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('pricematch')->__('Price Match Request'));
  }

  protected function _beforeToHtml()
  {
    $this->addTab('form_section', array(
      'label'   => Mage::helper('pricematch')->__('Request Information'),
      'title'   => Mage::helper('pricematch')->__('Request Information'),
      'content' => $this->getLayout()->createBlock('pricematch/adminhtml_pricematch_edit_tab_main')->toHtml(),
    ));
    return parent::_beforeToHtml();
  }
}