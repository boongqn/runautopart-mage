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

abstract class BL_FileAttributes_Block_System_Config_Form_Field_Select_Abstract
    extends Mage_Core_Block_Html_Select
{
    abstract protected function _getSourceModelName();
    
    public function setInputName($value)
    {
        return $this->setName($value);
    }
    
    public function _toHtml()
    {
        if (!$this->getOptions() && $this->_getSourceModelName()) {
            $options = Mage::getModel($this->_getSourceModelName())->toOptionArray();
            foreach ($options as $option) {
                $this->addOption($option['value'], $option['label']);
            }
        }
        return parent::_toHtml();
    }
}