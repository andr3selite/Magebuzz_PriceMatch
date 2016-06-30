<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Block_Adminhtml_Pricematch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('pricematchGrid');
    $this->setUseAjax(TRUE);
    $this->setDefaultSort('pricematch_id');
    $this->setDefaultDir('DESC');
    $this->setSaveParametersInSession(TRUE);
  }

  protected function _prepareCollection()
  {
    $collection = Mage::getModel('pricematch/pricematch')->getCollection();
    $this->setCollection($collection);
    return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $this->addColumn('pricematch_id', array(
      'header' => Mage::helper('pricematch')->__('ID'),
      'align'  => 'left',
      'width'  => '50px',
      'index'  => 'pricematch_id',
    ));

    $this->addColumn('store_id', array(
      'header' => Mage::helper('pricematch')->__('Store'),
      'align'  => 'left',
      'index'  => 'store_id',
      'type'   => 'store'
    ));
		
    $this->addColumn('product_id', array(
      'header'   => Mage::helper('pricematch')->__('Product Name'),
      'align'    => 'left',
      'index'    => 'product_id',
      'renderer' => 'pricematch/adminhtml_renderer_product'
    ));

    $this->addColumn('customer_name', array(
      'header' => Mage::helper('pricematch')->__('Customer Name'),
      'align'  => 'left',
      'index'  => 'customer_name',
    ));

    $this->addColumn('customer_email', array(
      'header' => Mage::helper('pricematch')->__('Customer Email'),
      'align'  => 'left',
      'index'  => 'customer_email',
    ));

    $this->addColumn('current_price', array(
      'header'   => Mage::helper('pricematch')->__('Current Price'),
      'align'    => 'left',
      'index'    => 'product_id',
      'renderer' => 'pricematch/adminhtml_renderer_currentprice'
    ));

    $this->addColumn('competitor_price', array(
      'header'   => Mage::helper('pricematch')->__('Competitor\'s Price'),
      'align'    => 'left',
      'index'    => 'competitor_price',
      'renderer' => 'pricematch/adminhtml_renderer_competitorprice'
    ));

    $this->addColumn('competitor_link', array(
      'header' => Mage::helper('pricematch')->__('Link'),
      'width'  => '150px',
      'align'  => 'left',
      'index'  => 'competitor_link',
    ));
		
    $this->addColumn('coupon_code', array(
      'header' => Mage::helper('pricematch')->__('Coupon Code'),
      'align'  => 'left',
      'index'  => 'coupon_code',
    ));

    $this->addColumn('date', array(
      'header' => Mage::helper('pricematch')->__('Created On'),
      'align'  => 'left',
      'index'  => 'date',
      'type'   => 'datetime'
    ));

    $this->addColumn('status', array(
      'header'  => Mage::helper('pricematch')->__('Status'),
      'align'   => 'left',
      'width'   => '80px',
      'index'   => 'status',
      'type'    => 'options',
      'options' => array(
        1 => 'Approved',
        2 => 'Rejected',
        3 => 'Pending'
      ),
    ));

    $this->addColumn('action',
      array(
        'header'    => Mage::helper('pricematch')->__('Action'),
        'width'     => '100',
        'type'      => 'action',
        'getter'    => 'getId',
        'actions'   => array(
          array(
            'caption' => Mage::helper('pricematch')->__('Edit'),
            'url'     => array('base' => '*/*/edit'),
            'field'   => 'id'
          )
        ),
        'filter'    => FALSE,
        'sortable'  => FALSE,
        'index'     => 'stores',
        'is_system' => TRUE,
      ));

    //$this->addExportType('*/*/exportCsv', Mage::helper('pricematch')->__('CSV'));
    //$this->addExportType('*/*/exportXml', Mage::helper('pricematch')->__('XML'));

    return parent::_prepareColumns();
  }

  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('pricematch_id');
    $this->getMassactionBlock()->setFormFieldName('pricematch');

    $this->getMassactionBlock()->addItem('delete', array(
      'label'   => Mage::helper('pricematch')->__('Delete'),
      'url'     => $this->getUrl('*/*/massDelete'),
      'confirm' => Mage::helper('pricematch')->__('Are you sure?')
    ));

    $statuses = Mage::getSingleton('pricematch/status')->getOptionArray();

    array_unshift($statuses, array('label' => '', 'value' => ''));
    $this->getMassactionBlock()->addItem('status', array(
      'label'      => Mage::helper('pricematch')->__('Change status'),
      'url'        => $this->getUrl('*/*/massStatus', array('_current' => TRUE)),
      'additional' => array(
        'visibility' => array(
          'name'   => 'status',
          'type'   => 'select',
          'class'  => 'required-entry',
          'label'  => Mage::helper('pricematch')->__('Status'),
          'values' => $statuses
        )
      )
    ));
    return $this;
  }

  public function getRowUrl($row)
  {
    return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

  public function getGridUrl()
  {
    return $this->getUrl('*/*/grid', array('_current' => TRUE));
  }

}