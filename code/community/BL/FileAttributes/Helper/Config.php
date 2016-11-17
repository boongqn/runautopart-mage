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

class BL_FileAttributes_Helper_Config
    extends Mage_Core_Helper_Abstract
{
    // XML paths of system config values
    const XML_CONFIG_FILE_SAVE_MOMENT          = 'fileattributes/configuration/file_save_moment';
    const XML_CONFIG_EXCEPTIONS_HANDLING_MODE  = 'fileattributes/configuration/exceptions_handling_mode';
    const XML_CONFIG_ATTRIBUTES_CONFIGURATIONS = 'fileattributes/configuration/attributes_configurations';
    const XML_CONFIG_ALLOW_MIME_HEADER_CHECK   = 'fileattributes/configuration/allow_mime_header_check';
    
    protected $_attributesConfigs = array();
    
    /**
    * Return a serialized config value surely unserialized
    * 
    * @param string $key Key of config value to get
    * @return array Unserialized config value
    */
    protected function _getSerializedConfig($key)
    {
        $values = Mage::getStoreConfig($key);
        if (!is_array($values)) {
            /* 
            Unserialize values if needed 
            (should always be the case, as _afterLoad() is not called with getStoreConfig())
            */
            $values = @unserialize($values);
            $values = (is_array($values) ? $values : array());
        }
        return $values;
    }
    
    public function getFileSaveMoment()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_FILE_SAVE_MOMENT);
    }
    
    public function getExceptionsHandlingMode()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_EXCEPTIONS_HANDLING_MODE);
    }
    
    /**
     * Parse file extensions string with various separators
     *
     * @param string $extensions String to parse
     * @return array|null
     */
    protected function _parseExtensionsString($extensions)
    {
        preg_match_all('/[a-z0-9]+/si', strtolower($extensions), $matches);
        if (isset($matches[0]) && is_array($matches[0]) && (count($matches[0]) > 0)) {
            return $matches[0];
        }
        return null;
    }
    
    /**
    * Parse MIME types string (code + label) with given separator
    * 
    * @param string $types String to parse
    * @param string $sep Values separator
    * @return array|null
    */
    protected function _parseMimeTypesString($types, $sep=',')
    {
        // TODO allow to backslash separator ?
        $types  = explode($sep, $types);
        if (($typesNumber = count($types)) > 1) {
            $result = array();
            for ($i=0; $i<$typesNumber-1; $i+=2) {
                $result[$types[$i]] = $types[$i+1];
            }
            return $result;
        }
        return null;
        
    }
    
    /**
    * Return attribute's configuration
    * 
    * @param int $attributeId ID of attribute from which to get config
    * @param bool $defaultNull Will return null if attribute has no config set, else empty config array
    * @return null|array
    */
    public function getAttributeConfiguration($attributeId, $defaultNull=false)
    {
        if (!array_key_exists($attributeId, $this->_attributesConfigs)) {
            $config = null;
            
            foreach ($this->_getSerializedConfig(self::XML_CONFIG_ATTRIBUTES_CONFIGURATIONS) as $attribute) {
                if ($attribute['attribute_id'] == $attributeId) {
                    // Parse serialized configs
                    $config = $attribute;
                    $config['allowed_mime_types']   = $this->_parseMimeTypesString($attribute['allowed_mime_types']);
                    $config['forbidden_mime_types'] = $this->_parseMimeTypesString($attribute['forbidden_mime_types']);
                    $config['allowed_file_extensions']   = $this->_parseExtensionsString($attribute['allowed_file_extensions']);
                    $config['forbidden_file_extensions'] = $this->_parseExtensionsString($attribute['forbidden_file_extensions']);
                    break;
                }
            }
            
            if (is_null($config)) {
                $config = ($defaultNull ? null : array(
                    'attribute_id'     => $attributeId,
                    'display_config'   => false,
                    'file_save_moment' => $this->getFileSaveMoment(),
                    'file_max_size'    => '',
                    'image_only'       => false,
                    'image_min_width'  => '',
                    'image_max_width'  => '',
                    'image_min_height' => '',
                    'image_max_height' => '',
                    'allowed_mime_types'   => null,
                    'forbidden_mime_types' => null,
                    'exceptions_handling_mode'  => $this->getExceptionsHandlingMode(),
                    'allowed_file_extensions'   => null,
                    'forbidden_file_extensions' => null,
                ));
            }
            
            $this->_attributesConfigs[$attributeId] = $config;
        }
        return $this->_attributesConfigs[$attributeId];
    }
    
    public function getAllowMimeHeaderCheck()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_ALLOW_MIME_HEADER_CHECK);
    }
}