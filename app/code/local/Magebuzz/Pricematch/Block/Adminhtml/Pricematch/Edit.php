<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Pricematch_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
  public function __construct()
  {
    parent::__construct();

    $this->_objectId = 'id';
    $this->_blockGroup = 'pricematch';
    $this->_controller = 'adminhtml_pricematch';

    $this->_updateButton('save', 'label', Mage::helper('pricematch')->__('Save'));
    $this->_updateButton('delete', 'label', Mage::helper('pricematch')->__('Delete'));

    $this->_addButton('saveandcontinue', array(
      'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
      'onclick' => 'saveAndContinueEdit()',
      'class'   => 'save',
    ), -100);

    $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('pricematch_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'pricematch_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'pricematch_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
  }

  public function getHeaderText()
  {
    if (Mage::registry('pricematch_data') && Mage::registry('pricematch_data')->getId()) {
      return Mage::helper('pricematch')->__("Edit Request #%s", $this->htmlEscape(Mage::registry('pricematch_data')->getId()));
    } else {
      return Mage::helper('pricematch')->__('Add Item');
    }
  }
}