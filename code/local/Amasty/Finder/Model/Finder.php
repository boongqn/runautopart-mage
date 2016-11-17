<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */  
class Amasty_Finder_Model_Finder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amfinder/finder');
    }
    
    public function getDropdowns()
    {
        $collection = Mage::getModel('amfinder/dropdown')->getResourceCollection()
            ->addFieldToFilter('finder_id', $this->getId());
        $collection->getSelect()->order('pos');            
            
        return $collection;     
    }
    
    public function saveFilter($dropdowns)
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();

        if (!$dropdowns)
            return false;
            
        if (!is_array($dropdowns))
            return false;
         
        $safeValues = array();
        $id     = 0;      
        foreach ($this->getDropdowns() as $d){
            $id = $d->getId();
            $safeValues[$id] = isset($dropdowns[$id]) ? $dropdowns[$id] : 0;
        }  
        if ($id)
            $safeValues['last'] = $safeValues[$id]; 
        
        /*Added code on Date 23rd Feb 2013 by Mitali*/
        $session->setData('clicked_for_search',true); 
        /*Code Ends on Date 23rd Feb 2013 by Mitali*/
        $session->setData($name, $safeValues); 
      
        return true; 
    } 
    
    public function resetFilter()
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();

        $session->setData($name, null);
        return true;        
    }       
    
    public function applyFilter()  
    {
    	/*Added code on Date 23rd Feb 2013 by Mitali
    	* it will clear last searched value from session when it goes to other categorylist
    	*/
    	
        $session = Mage::getSingleton('catalog/session');
        $clicked_for_search=$session->getData('clicked_for_search'); 
        $last_cat_id=$session->getData('last_visited_category');
        $cat_collection=Mage::registry('current_category');
        if($cat_collection)
	        $cat_id=$cat_collection->getID();
	else
		$cat_id=null;
        $name    = 'amfinder_' . $this->getId();
        if($last_cat_id && $last_cat_id != $cat_id && !$clicked_for_search){
            $session->setData($name, null);
        }
        $session->setData('last_visited_category',$cat_id);
        
        if($clicked_for_search)
            $session->setData('clicked_for_search',false);

        /*Code Ends on Date 23rd Feb 2013 by Mitali*/
        
        $id = $this->getSavedValue('last');
        if (!$id)
            return false;
            
        $collection = Mage::getSingleton('catalog/layer')->getProductCollection();
	//$collection = Mage::getModel('catalog/product')->getCollection();
        $select = $collection->getSelect();
        
        $alias = 'map_amfinder_' . $this->getId();
        if (false === strpos((string)$select, $alias.'.pid=e.entity_id')){         
            $select
                ->joinInner(array($alias=>$collection->getTable('amfinder/map')), $alias.'.pid=e.entity_id', array())
                ->where($alias.'.value_id = ?', $id);
        }
        
        return true;        
    }
    
    public function getSavedValue($dropdownId)
    {
        $session = Mage::getSingleton('catalog/session');
        $name    = 'amfinder_' . $this->getId();
        
        $values = $session->getData($name);
        if (!is_array($values))
            return 0;
            
        if (empty($values[$dropdownId]))
            return 0;
            
        return $values[$dropdownId];   
    }
    
    public function import()
    {
        return $this->getResource()->import($this);      
    }
    
    public function updateLinks()
    {
        return $this->getResource()->updateLinks();      
    }    
        
}