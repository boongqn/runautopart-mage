<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Finder_Block_Form extends Mage_Core_Block_Template
{
    protected $_finderModel = null;
    protected $_parentDropdownId = 0;

    /**
     * @return Amasty_Finder_Model_Finder
     */
    public function getFinder()
    {
        if (is_null($this->_finderModel)){
            $this->_finderModel = Mage::getModel('amfinder/finder')
                ->load($this->getId());

        }
        return $this->_finderModel;
    }

    public function getDropdownAttributes($dropdown)
    {
        $html = sprintf('name="finder[%s]" id="finder-%s--%s"',
            $dropdown->getId(), $this->getId(), $dropdown->getId());

        if ($this->_isHidden($dropdown))
            $html .= 'disabled="disabled"';

        return $html;
    }

    public function getDropdownValues($dropdown)
    {
        $values   = array();
        if ($this->_isHidden($dropdown)){
            return $values;
        }

        $parentValueId  = $this->getFinder()->getSavedValue($this->_parentDropdownId);
        $currentValueId = $this->getFinder()->getSavedValue($dropdown->getId());

        $values = $dropdown->getValues($parentValueId, $currentValueId);

        $this->_parentDropdownId = $dropdown->getId();


        return $values;
    }

    public function isButtonsVisible()
    {
        $cnt = count($this->getFinder()->getDropdowns());
        return (($cnt == 1) || $this->getFinder()->getSavedValue('last'));
    }

    public function getAjaxUrl()
    {
        $url = Mage::getUrl('amfinder/index/options');
        return $url;
    }

    public function getBackUrl()
    {
    	$url = Mage::getUrl('amfinder');
    	if (Mage::helper('ambase')->isModuleActive('Amasty_Shopby')){
            $url = Mage::getBaseUrl() . Mage::getStoreConfig('amshopby/seo/key');
    	}
	return $url; 
    	//not category page
    	$category = Mage::registry('current_category');
    	if (!$category){
    		return $url;
    	}

    	//special page with all products
    	$rootId = (int) Mage::app()->getStore()->getRootCategoryId();
    	if ($category->getId() == $rootId){
    		return $url;
    	}
    	
    	if ($category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE){
    	    return $url;
    	}
    	
        $url = Mage::helper('core/url')->getCurrentUrl();

        return $url;
    }

    public function getActionUrl()
    {
        $url = Mage::getUrl('amfinder/index/search');
        return $url;
    }

    protected function _isHidden($dropdown)
    {
        //it's not the first dropdown && value is not selected
        return ($dropdown->getPos() && !$this->getFinder()->getSavedValue($dropdown->getId()));
    }

    protected function _toHtml()
    {
        $tpl = $this->getTemplate();
        if (!$tpl){
            $tpl = $this->getFinder()->getTemplate();
            if (!$tpl) {
                $tpl = 'vertical';
            }
            $this->setTemplate('amfinder/' . $tpl . '.phtml');
        }

        $this->getFinder()->applyFilter();

        if (isset($_GET['debug'])){
            $session = Mage::getSingleton('catalog/session');
            $name    = 'amfinder_' . $this->getId();
            echo print_r($session->getData($name),1);
        }

        $id = $this->getId();
        if (!$id){
            return 'Please specify the Parts Finder ID';
        }

        $finder = $this->getFinder();
        if (!$finder->getId()){
            return 'Please specify an exiting Parts Finder ID';
        }

        return parent::_toHtml();
    }
}