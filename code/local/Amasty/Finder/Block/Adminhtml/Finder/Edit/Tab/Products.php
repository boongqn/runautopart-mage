<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_finder = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('amfinderProducts');
        $this->setUseAjax(true);
    }
    
    public function getFinder()
    {
        if (is_null($this->_finder)){
            $id = $this->getRequest()->getParam('id');
            $this->_finder = Mage::getModel('amfinder/finder')->load($id);
        }
        return $this->_finder;        
    }

    protected function _prepareCollection()
    {
        $products = Mage::getModel('amfinder/value')->getCollection()
            ->joinAllFor($this->getFinder());
        
        $this->setCollection($products);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        foreach($this->getFinder()->getDropdowns() as $d) {
            $i = $d->getPos();
            $this->addColumn("name$i", array(
                'header'    => $d->getName(),
                'index'     => "name$i",
            ));        
        }
        
        $this->addColumn('sku', array(
            'header'    => Mage::helper('amfinder')->__('SKU'),
            'index'     => 'sku',
        ));
        
        $this->addExportType('*/*/productsCsv', Mage::helper('amfinder')->__('CSV'));
                
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return null; 
    }
      
}