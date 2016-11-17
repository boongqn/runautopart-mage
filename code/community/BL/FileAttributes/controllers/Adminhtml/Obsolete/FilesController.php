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

class BL_FileAttributes_Adminhtml_Obsolete_FilesController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction($layoutIds=null)
    {
        $this->loadLayout($layoutIds)
            ->_setActiveMenu('system/fileattributes/obsolete_files')
            ->_title($this->__('File Attributes'))
            ->_title($this->__('Manage Obsolete Files'))
            ->_addBreadcrumb($this->__('File Attributes'), $this->__('File Attributes'))
            ->_addBreadcrumb($this->__('Manage Obsolete Files'), $this->__('Manage Obsolete Files'));
        return $this;
    }
    
    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_initAction()->renderLayout();
    }
    
    public function deleteAction()
    {
        if ($file = $this->getRequest()->getParam('file')) {
            $files = Mage::getModel('fileattributes/obsolete_files_collection');
            if ($file = $files->getItemById($file)) {
                try {
                    Mage::getModel('fileattributes/attribute_backend_file')
                        ->deleteFile($file->getBasename());
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The obsolete file has been successfully deleted.'));
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This obsolete file no longer exists.'));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function massDeleteAction()
    {
        if (!$this->_validateFiles()) {
            return;
        }
        
        try {
            $collection = Mage::getModel('fileattributes/obsolete_files_collection');
            $files   = $this->getRequest()->getParam('files');
            $model   = Mage::getModel('fileattributes/attribute_backend_file');
            $deleted = 0;
            
            foreach ($files as $file) {
                 if ($file = $collection->getItemById($file)) {
                     $model->deleteFile($file->getBasename());
                     $deleted++;
                 }
            }
            
            $this->_getSession()->addSuccess($this->__('Total of %d obsolete file(s) have been deleted.', $deleted));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        
        $this->getResponse()->setRedirect($this->getUrl('*/*/index'));
    }
    
    protected function _validateFiles()
    {
        if (!is_array($this->getRequest()->getParam('files', null))) {
            $this->_getSession()->addError($this->__('Please select obsolete files to update'));
            $this->_redirect('*/*/index', array('_current' => true));
            return false;
        }
        return true;
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/fileattributes/obsolete_files');
    }
}