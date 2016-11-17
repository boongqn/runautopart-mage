<?php

class Openxcell_Brands_Block_Adminhtml_Brands_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('brands_form', array('legend'=>Mage::helper('brands')->__('Brand information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('brands')->__('Title of Brand'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'image', array(
          'label'     => Mage::helper('brands')->__('Brand Logo'),
          'required'  => true,
          'name'      => 'filename',
	  ));
	  
      $fieldset->addField('brand_url', 'text', array(
          'label'     => Mage::helper('brands')->__('Brand URL'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'brand_url',
      ));
      
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('brands')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('brands')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('brands')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBrandsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBrandsData());
          Mage::getSingleton('adminhtml/session')->setBrandsData(null);
      } elseif ( Mage::registry('brands_data') ) {
          $form->setValues(Mage::registry('brands_data')->getData());
      }
      return parent::_prepareForm();
  }
}