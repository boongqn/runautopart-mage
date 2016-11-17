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

class BL_FileAttributes_Model_System_Config_Backend_Serialized_Attributes_Configuration
    extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    protected $_sourcesOptions = array();
    
    /**
    * Verify an integer value taken in an array
    * 
    * @param mixed $array Hash of values
    * @param mixed $key Key of value to verify
    * @return null|mixed
    */
    protected function _verifyIntValue(array $array, $key)
    {
        return (isset($array[$key]) && is_numeric($array[$key]) && (($value = intval($array[$key])) > 0) ? $value : null);
    }
    
    /**
    * Verify a value taken in an array, from an options source
    * 
    * @param mixed $array Hash of values
    * @param mixed $key Key of value to verify
    * @param mixed $modelName Source model name
    * @param mixed $internal True if source model is internal to this module
    * @return null|mixed
    */
    protected function _verifySourceValue(array $array, $key, $modelName, $internal=true)
    {
        if ($internal) {
            $modelName = 'fileattributes/system_config_source_'.$modelName;
        }
        if (!isset($this->_sourcesOptions[$modelName])) {
            $options = Mage::getModel($modelName)->toOptionArray();
            $this->_sourcesOptions[$modelName] = array();
            foreach ($options as $option) {
                $this->_sourcesOptions[$modelName][] = $option['value'];
            }
        }
        if (isset($array[$key])
            && in_array($array[$key], $this->_sourcesOptions[$modelName])) {
            return $array[$key];
        } else {
            return null;
        }
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        
        // Remove obsolete configuration (unexisting attributes)
        if (is_array($value = $this->getValue())) {
            $attributes = Mage::helper('fileattributes')->getFileAttributesCollection();
            foreach ($value as $key => $config) {
                if (!$attributes->getItemById($config['attribute_id'])) {
                    unset($value[$key]);
                }
            }
            $this->setValue($value);
        }
    }
    
    protected function _beforeSave()
    {      
        // Clean given value by removing "__empty" and incomplete sub values
        $value      = $this->getValue();
        $attributes = Mage::helper('fileattributes')->getFileAttributesCollection();
        $foundIds   = array();
        
        if (is_array($value)) {
            if (isset($value['__empty'])) {
                unset($value['__empty']);
            }
            foreach ($value as $key => $config) {
                if (!isset($config['attribute_id'])
                    || !$attributes->getItemById($config['attribute_id'])
                    || isset($foundIds[$config['attribute_id']])) {
                    unset($value[$key]);
                } else {
                    $attributeId = $config['attribute_id'];
                    $foundIds[$attributeId] = true;
                    
                    $value[$key] = array(
                        'attribute_id'     => $attributeId,
                        'display_config'   => (isset($config['display_config']) ? (bool)$config['display_config'] : false),
                        'file_save_moment' => $this->_verifySourceValue($config, 'file_save_moment', 'file_save_moment'),
                        'file_max_size'    => $this->_verifyIntValue($config, 'file_max_size'),
                        'image_only'       => (isset($config['image_only']) ? (bool)$config['image_only'] : false),
                        'image_min_width'  => $this->_verifyIntValue($config, 'image_min_width'),
                        'image_max_width'  => $this->_verifyIntValue($config, 'image_max_width'),
                        'image_min_height' => $this->_verifyIntValue($config, 'image_min_height'),
                        'image_max_height' => $this->_verifyIntValue($config, 'image_max_height'),
                        'allowed_mime_types'   => (isset($config['allowed_mime_types']) ? $config['allowed_mime_types'] : ''),
                        'forbidden_mime_types' => (isset($config['forbidden_mime_types']) ? $config['forbidden_mime_types'] : ''),
                        'exceptions_handling_mode'  => $this->_verifySourceValue($config, 'exceptions_handling_mode', 'exceptions_handling_mode'),
                        'allowed_file_extensions'   => (isset($config['allowed_file_extensions']) ? $config['allowed_file_extensions'] : ''),
                        'forbidden_file_extensions' => (isset($config['forbidden_file_extensions']) ? $config['forbidden_file_extensions'] : ''),
                    );
                }
            }
        } else {
            $value = array();
        }
        
        $this->setValue($value);
        parent::_beforeSave();
    }
}