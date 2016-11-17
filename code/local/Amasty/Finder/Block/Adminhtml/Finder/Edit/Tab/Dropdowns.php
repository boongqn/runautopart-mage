<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_Dropdowns extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Finder_Helper_Data */
        $hlp   = Mage::helper('amfinder');
        $model = Mage::registry('amfinder_finder');
        
        $fldNames = $form->addFieldset('dropdowns', array('legend'=> $hlp->__('Names')));
        
        foreach ($model->getDropdowns() as $drop){
            $alias = 'drop_' . $drop->getId(); 
            $fldNames->addField($alias, 'text', array(
                'label'     => $hlp->__('Name #%s', $drop->getPos()+1),
                'name'      => $alias,
                'required'  => true,
            )); 
        }        
        
        //set form values
        $form->setValues($model->getData()); 
        
        return parent::_prepareForm();
    }
}