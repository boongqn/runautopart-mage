<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Finder_IndexController extends Mage_Core_Controller_Front_Action
{
    // add
    public function searchAction()
    {
        $id     = Mage::app()->getRequest()->getParam('finder_id');
        $finder = Mage::getModel('amfinder/finder')->setId($id);

        $dropdowns = Mage::app()->getRequest()->getParam('finder');
        
        if ($dropdowns){
            $finder->saveFilter($dropdowns);
        }
        Mage::getSingleton('catalog/session')->setSearchedData('searched');
        if ($this->getRequest()->getParam('reset')){
            Mage::getSingleton('catalog/session')->setSearchedData('');
            $finder->resetFilter();
        }

        $backUrl = $this->getRequest()->getParam('back_url');
		

	Mage::getSingleton('core/session')->setSearchCat($dropdowns[1]);
	Mage::getSingleton('core/session')->setSearchCatOptions("'".$dropdowns[1]."','".$dropdowns[2]."','".$dropdowns[3]."'");
		
        $this->getResponse()->setRedirect($backUrl);
    }


    // AJAX action to show next dropdown
    public function optionsAction()
    {
        $parentId   = Mage::app()->getRequest()->getParam('parent_id');
        $dropdownId = Mage::app()->getRequest()->getParam('dropdown_id');
        $options    = array();
        if ($parentId && $dropdownId) {
            $dropdown = Mage::getModel('amfinder/dropdown')->load($dropdownId);
            $options  = $dropdown->getValues($parentId);
        }
        
        if (version_compare(Mage::getVersion(), '1.4.1.0') < 0) {
            $options = Zend_Json::encode($options);  
        }
        else{
            $options = Mage::helper('core')->jsonEncode($options);  
        }

        $this->getResponse()->setBody($options);
    }


    public function indexAction()
    {
        // init category
        $categoryId = (int) Mage::app()->getStore()->getRootCategoryId();
		$search_cat = Mage::getSingleton('core/session')->getSearchCat();

		$resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
     
        $query = 'SELECT name FROM ' . $resource->getTableName('am_finder_value') . ' WHERE value_id = "' . $search_cat . '" LIMIT 1';
		 
        $results = $readConnection->fetchAll($query);
 
        $name = $results[0]['name'];
		
		//$test = Mage::getModel('amfinder/dropdown')->getValues();
				
        $name = trim($name);
		
        switch ($name) {
    		case 'Audi':
        		$categoryId = 3;
        		break;
			case 'BMW':
				$categoryId = 4;
				break;
			case 'Mercedes Benz':
				$categoryId = 5;
				break;
			case 'MINI':
				$categoryId = 4;
				break;	
			case 'Porsche':
				$categoryId = 6;
				break;	
			case 'VW':
				$categoryId = 7;
				break;	
			case 'Volvo':
				$categoryId = 8;
				break;				
				
		}
		 		
	
        if (empty($name)) {
            $this->_forward('noRoute');
            return;
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        Mage::register('current_category', $category);
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
	
	
        // need to prepare layer params
        try {
            Mage::dispatchEvent('catalog_controller_category_init_after',
                array('category' => $category, 'controller_action' => $this));
        }
        catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return;
        }
        // observer can change value
        if (!$category->getId()){
            $this->_forward('noRoute');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

}