<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Pricematch extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_pricematch';
    $this->_blockGroup = 'pricematch';
    $this->_headerText = Mage::helper('pricematch')->__('Price Match Requests');
    $this->_addButtonLabel = Mage::helper('pricematch')->__('Add New Request');
    parent::__construct();
    $this->_removeButton('add');
  }
}