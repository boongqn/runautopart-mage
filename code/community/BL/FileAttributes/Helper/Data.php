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

class BL_FileAttributes_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    public function getFileAttributesCollection()
    {    
        return Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->addFieldToFilter('backend_model', 'fileattributes/attribute_backend_file');
    }
    
    /**
    * Return file's full URL
    * 
    * @param string $file File name
    * @return string
    */
    public function getFileUrl($file)
    {
        return Mage::getModel('fileattributes/attribute_backend_file')->getFileUrl($file);
    }
    
    /**
    * Return an entity's file full URL
    * 
    * @param Varien_Object $entity Entity owning the file
    * @param string $attribute Code of the attribute corresponding to the file
    * @return string
    */
    public function getEntityFileUrl($entity, $attributeCode)
    {
        if ($file = $entity->getDataUsingMethod($attributeCode)) {
            return $this->getFileUrl($file);
        }
        return false;
    }
    
    /**
    * Return a file size which is suitable for display
    * 
    * @param int $size File size in bytes
    * @param bool $roundTo Whether to round size value when it is converted to higher units
    * @return string
    */
    public function getFileSizeForDisplay($size, $roundTo=null)
    {
        $units = array('bytes', 'kb', 'mb', 'gb');
        $unitsNumber = count($units);
        $i = 0;
        while (($i++ < $unitsNumber) && ($size > 1024)) $size /= 1024;
        return $this->__('%s '.$units[$i-1], (!is_null($roundTo) ? round($size, $roundTo) : $size));
    }
    
    public function decodeFileName($file)
    {
        return base64_decode($file);
    }
    
    public function encodeFileName($file)
    {
        return base64_encode($file);
    }
}