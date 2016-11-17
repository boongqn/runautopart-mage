<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Finder_Block_Adminhtml_Finder_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Finder_Helper_Data */
        $hlp   = Mage::helper('amfinder');
        $model = Mage::registry('amfinder_finder');
        
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Title'),
            'name'      => 'name',
            'required'  => true,
        ));         
        
        if (!$model->getId()){
            $fldInfo->addField('cnt', 'text', array(
                'label'     => $hlp->__('Number of Dropdowns'),
                'name'      => 'cnt',
                'required'  => true,
                'class'     => 'validate-greater-than-zero',
            ));
        }

        $fldInfo->addField('template', 'text', array(
            'label'    => $hlp->__('Template'),
            'name'      => 'template',
            'note'      => $hlp->__('E.g. `vertical`, `horizontal`. Leave blank to use a default template'),
        ));                

        //set form values
        $form->setValues($model->getData()); 
        
        return parent::_prepareForm();
    }
}