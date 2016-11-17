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

class BL_FileAttributes_Block_Adminhtml_Widget_Grid_Column_Filter_File_Size
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    protected function _parseUnitValue($value)
    {
        return max(0, min(3, intval($value)));
    }
    
    public function getHtml()
    { 
        $helper = Mage::helper('fileattributes');
        $html   = parent::getHtml();
        $html  .= '<div class="range"><div class="range-line"><span class="label">'.$helper->__('In:').'</span> <select name="'.$this->_getHtmlName().'[unit]" id="'.$this->_getHtmlId().'_unit" class="no-changes">';
        $unit  = $this->_parseUnitValue($this->getValue('unit'));
        $html  .= '<option value="0"'.($unit === 0 ? ' selected="selected"' : '').'>'.$helper->__('Bytes').'</option>';
        $html  .= '<option value="1"'.($unit === 1 ? ' selected="selected"' : '').'>'.$helper->__('Kilobytes').'</option>';
        $html  .= '<option value="2"'.($unit === 2 ? ' selected="selected"' : '').'>'.$helper->__('Megabytes').'</option>';
        $html  .= '<option value="3"'.($unit === 3 ? ' selected="selected"' : '').'>'.$helper->__('Gigabytes').'</option>';
        $html  .= '</select></div></div>';
        return $html;
    }
    
    public function getValue($index=null)
    {
        if (is_array($value = parent::getValue($index))) {
            if (isset($value['unit'])) {
                $unit = $this->_parseUnitValue($value['unit']);
            } else {
                $unit = 0;
            }
            if (isset($value['from']) && (strlen($value['from']) > 0)) {
                $value['from'] *= pow(1024, $unit);
            }
            if (isset($value['to']) && (strlen($value['to']) > 0)) {
                $value['to'] *= pow(1024, $unit);
            }
        }
        return $value;
    }
}