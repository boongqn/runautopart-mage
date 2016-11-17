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

class BL_FileAttributes_Block_Adminhtml_Widget_Grid_Column_Renderer_Timestamp
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected static $_format = null;
    
    protected function _getFormat()
    {
        $format = $this->getColumn()->getFormat();
        
        if (!$format) {
            if (is_null(self::$_format)) {
                try {
                    self::$_format = Mage::app()->getLocale()->getDateTimeFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
                } catch (Exception $e) { }
            }
            $format = self::$_format;
        }
        
        return $format;
    }
    
    public function render(Varien_Object $row)
    {
        if ($data = $this->_getValue($row)) {
            $format = $this->_getFormat();
            try {
                $data = Mage::app()->getLocale()->date($data, Zend_Date::TIMESTAMP)->toString($format);
            } catch (Exception $e) {
                $data = Mage::app()->getLocale()->date($data, Zend_Date::TIMESTAMP)->toString($format);
            }
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}