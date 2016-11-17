<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   BL
 * @package    BL_FileAttributes
 * @copyright  Copyright (c) 2011 Benoît Leulliette <benoit.leulliette@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class BL_FileAttributes_Block_Adminhtml_Obsolete_Files_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('BLFA_ObsoleteFilesGrid')
            ->setSaveParametersInSession(true)
            ->setUseAjax(false);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('fileattributes/obsolete_files_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $helper = Mage::helper('fileattributes');
        
        $this->addColumn('basename', array(
            'header' => $helper->__('File Name'),
            'index'  => 'basename',
        ));
        
        $this->addColumn('extension', array(
            'header' => $helper->__('Extension'),
            'index'  => 'extension',
        ));
        
        $this->addColumn('size', array(
            'header'   => $helper->__('Size'),
            'index'    => 'size',
            'filter'   => 'fileattributes/adminhtml_widget_grid_column_filter_file_size',
            'renderer' => 'fileattributes/adminhtml_widget_grid_column_renderer_file_size',
        ));
        
        $this->addColumn('updated_at', array(
            'header'      => $helper->__('Updated At'),
            'index'       => 'updated_at',
            'filter_time' => true,
            'filter'      => 'fileattributes/adminhtml_widget_grid_column_filter_timestamp',
            'renderer'    => 'fileattributes/adminhtml_widget_grid_column_renderer_timestamp',
        ));
        
        $this->addColumn('url', array(
            'header'   => $helper->__('File'),
            'index'    => 'url',
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'fileattributes/adminhtml_widget_grid_column_renderer_file_link',
        ));
        
        $this->addColumn('action',
            array(
                'header'  => $this->__('Actions'),
                'width'   => '120px',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Delete'),
                        'confirm' => $this->__('Are you sure?'),
                        'url'     => array(
                            'base' => '*/*/delete',
                        ),
                        'field'   => 'file'
                    )
                ),
                'filter'   => false,
                'sortable' => false,
                'index'    => 'id',
        ));
        
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return false;
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('files');
        
        $this->getMassactionBlock()->addItem('mass_delete', array(
            'label'   => $this->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete', array('_current' => true)),
            'confirm' => $this->__('Are you sure?'),
        ));
        
        return $this;
    }
}