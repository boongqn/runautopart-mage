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

class BL_FileAttributes_Model_Attribute_Frontend_File
    extends Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
{
    /**
    * Get file attribute's formatted value from an entity
    * 
    * @param Varien_Object $object Entity owning file value
    * @return string
    */
    public function getValue(Varien_Object $object)
    {
        $data  = '';
        if ($value = parent::getValue($object)) {
            $data = '<a href="'.Mage::helper('fileattributes')->getFileUrl($value).'" target="_blank">'.$value.'</a>';
        }
        return $data;
    }
}