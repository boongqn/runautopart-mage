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

class BL_FileAttributes_Block_System_Config_Form_Field_Select_Attribute
    extends BL_FileAttributes_Block_System_Config_Form_Field_Select_Abstract
{
    protected $_attributes;
    
    protected function _getSourceModelName()
    {
        return null;
    }
    
    /**
    * Return all "blfa_file" EAV attributes
    * 
    * @return array
    */
    protected function _getAttributes()
    {
        if (is_null($this->_attributes)) {
            $eavConfig  = Mage::getSingleton('eav/config');
            $attributes = $this->helper('fileattributes')->getFileAttributesCollection();
            
            foreach ($attributes as $attribute) {
                $type  = $eavConfig->getEntityType($attribute->getEntityTypeId());
                $label = $this->__('%s (%s)', $attribute->getFrontendLabel(), $type->getEntityTypeCode());
                $this->_attributes[$attribute->getId()] = $label;
            }
        }
        return $this->_attributes;
    }
    
    public function setInputName($value)
    {
        return $this->setName($value);
    }
    
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getAttributes() as $id => $label) {
                $this->addOption($id, $label);
            }
        }
        return Mage_Core_Block_Html_Select::_toHtml();
    }
}