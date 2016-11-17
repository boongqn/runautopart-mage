<?php
class Openxcell_Brands_Block_Adminhtml_Brands extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_brands';
    $this->_blockGroup = 'brands';
    $this->_headerText = Mage::helper('brands')->__('Brand Manager');
    $this->_addButtonLabel = Mage::helper('brands')->__('Add Brand');
    parent::__construct();
  }
}