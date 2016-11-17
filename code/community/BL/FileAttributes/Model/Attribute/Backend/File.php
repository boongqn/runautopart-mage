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

class BL_FileAttributes_Model_Attribute_Backend_File
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    const EXCEPTIONS_HANDLING_MODE_IGNORE = 'ignore';
    const EXCEPTIONS_HANDLING_MODE_THROW  = 'throw';
    const EXCEPTIONS_HANDLING_MODE_LOG    = 'log';
    
    const FILE_SAVE_MOMENT_BEFORE_ENTITY_SAVE = 'before';
    const FILE_SAVE_MOMENT_AFTER_ENTITY_SAVE  = 'after';
    
    protected $_pendingSaveObject = null;
    protected $_pendingSaveValue  = null;
    
    /**
    * Delete a previously uploaded file
    * 
    * @param string $fileName Name of the file to delete
    * @return BL_FileAttributes_Model_Attribute_Backend_File
    */
    protected function _deleteFile($fileName)
    {
        $io = new Varien_Io_File();
        $io->rm($this->getFileDir($fileName, false));
        return $this;
    }
    
    /**
    * Delete a previously uploaded file (wrapper for _deleteFile)
    * 
    * @param string $fileName Name of the file to delete
    * @return BL_FileAttributes_Model_Attribute_Backend_File
    */
    public function deleteFile($fileName)
    {
        return $this->_deleteFile($fileName);
    }
    
    /**
     * Main destination directory
     *
     * @param boolean $relative If true, returns relative path to the webroot
     * @return string
     */
    public function getTargetDir($relative=false)
    {
        $fullPath = rtrim(Mage::getBaseDir('media'), DS) . DS . 'PDF';
        return ($relative ? str_replace(Mage::getBaseDir(), '', $fullPath) : $fullPath);
    }
    
    /**
    * Main destination URL
    * 
    * @param boolean $relative If true, returns relative url to the webroot
    * @param boolean $secure If true, returns secure URL
    * @return string
    */
    public function getTargetUrl($relative=false, $secure=null)
    {
        $fullUrl = rtrim(Mage::getBaseUrl('media'), '/') . '/PDF';
        return ($relative ? str_replace(Mage::getBaseUrl(), '', $fullUrl) : $fullUrl);
    }
    
    /**
    * File path
    * 
    * @param string $fileName File name
    * @param bool $relative If true, returns relative path to the webroot
    * @return string
    */
    public function getFileDir($fileName, $relative=false)
    {
        return $this->getTargetDir($relative) . DS . $fileName;
    }
    
    /**
    * File URL
    * 
    * @param string $fileName File name
    * @param boolean $relative If true, returns relative url to the webroot
    * @param boolean $secure If true, returns secure URL
    * @return string
    */
    public function getFileUrl($fileName, $relative=false, $secure=null)
    {
        return $this->getTargetUrl($relative, $secure) . '/' . $fileName;
    }
    
    /**
    * Save value for current attribute on given object
    * 
    * @param Mage_Core_Model_Abstract $object
    * @param mixed $value
    * @return BL_FileAttributes_Model_Attribute_Backend_File
    */
    protected function _saveObjectAttributeValue($object, $value)
    {
        $attribute = $this->getAttribute();
        $object->setData($attribute->getName(), $value);
        $attribute->getEntity()->saveAttribute($object, $attribute->getName());
        return $this;
    }
    
    /**
    * Save attribute file for given object
    * 
    * @param Mage_Core_Model_Abstract $object
    * @return BL_FileAttributes_Model_Attribute_Backend_File
    */
    protected function _saveAttributeFile($object)
    {
        $helper      = Mage::helper('fileattributes');
        $attribute   = $this->getAttribute();
        $value       = $object->getData($attribute->getName());
        $label       = $attribute->getFrontendLabel();
        $maxFileSize = $this->getUploadMaxFilesize();
        /*
        Using enableHeaderCheck() on Zend "mime-type" file validators seems to be not useful,
        as it checks the type retrieved from Zend_File_Transfer,
        and Zend_File_Transfer_Adapter_Abstract::_detectMimeType() seems to not be using a different detection
        than those validators, actually it does just return "application/octet-stream" by default
        */
        //$allowMimeHeaderCheck = Mage::helper('fileattributes/config')->getAllowMimeHeaderCheck();
        
        if (is_array($value) && !empty($value['delete'])) {
            // Just reset value, files deletion is accessible from elsewhere
            if ($object->getId()) {
                $this->_saveObjectAttributeValue($object, '');
            } else {
                $this->_pendingSaveObject = $object;
                $this->_pendingSaveValue  = '';
            }
            return $this;
        }
        
        $upload = new Zend_File_Transfer_Adapter_Http();
        $file   = $attribute->getName();
        
        try {
            $origData = $object->getOrigData();
            $origFile = (isset($origData[$file]) ? $origData[$file] : null);
            $newFile  = (is_array($value) ? $value['value'] : $value);
            
            if (!$upload->isUploaded($file)
                && (!$attribute->getIsRequired() || ($newFile == $origFile))) {
                // No need to go further
                return $this;
            }
            
            $fileInfo = $upload->getFileInfo($file);
            $fileInfo = $fileInfo[$file];
            $fileInfo['title'] = $fileInfo['name'];
        } catch (Exception $e) {
            // Upload error
            if (isset($_SERVER['CONTENT_LENGTH']) && ($_SERVER['CONTENT_LENGTH'] < $maxFileSize)) {
                $size = Mage::helper('fileattributes')->getFileSizeForDisplay($maxFileSize, 2);
                Mage::throwException($helper->__('The file you uploaded for "%s" attribute is larger than the %s allowed by server', $label, $size));
            } else {
                Mage::throwException($helper->__('An error occured during file upload for "%s" attribute', $label));
            }
        }
        
        $config = Mage::helper('fileattributes/config')->getAttributeConfiguration($attribute->getId());
        
        // Validation for MIME types
        if (isset($config['allowed_mime_types'])
            && is_array($validate = $config['allowed_mime_types'])) {
            $upload->addValidator('MimeType', false, array_keys($validate));
            //$upload->getValidator('MimeType')->enableHeaderCheck($allowMimeHeaderCheck);
        } elseif (isset($config['forbidden_mime_types'])
                  && is_array($validate = $config['forbidden_mime_types'])) {
            $upload->addValidator('ExcludeMimeType', false, array_keys($validate));
            //$upload->getValidator('ExcludeMimeType')->enableHeaderCheck($allowMimeHeaderCheck);
        }
        
        // Validation for image-only flag
        if (isset($config['image_only']) && $config['image_only']) {
            $upload->addValidator('IsImage', false);
            //$upload->getValidator('IsImage')->enableHeaderCheck($allowMimeHeaderCheck);
        }
        
        // Validation for image dimensions
        $validate = array();
        if ($config['image_min_width'] > 0) {
            $validate['minwidth'] = $config['image_min_width'];
        }
        if ($config['image_max_width'] > 0) {
            $validate['maxwidth'] = $config['image_max_width'];
        }
        if ($config['image_min_height'] > 0) {
            $validate['minheight'] = $config['image_min_height'];
        }
        if ($config['image_max_height'] > 0) {
            $validate['maxheight'] = $config['image_max_height'];
        }
        if (count($validate) > 0) {
            $upload->addValidator('ImageSize', false, $validate);
        }
        
        // Validation for file extensions
        if (isset($config['allowed_file_extensions'])
            && is_array($validate = $config['allowed_file_extensions'])) {
            $upload->addValidator('Extension', false, $validate);
        } elseif (isset($config['forbidden_file_extensions'])
                  && is_array($validate = $config['forbidden_file_extensions'])) {
            $upload->addValidator('ExcludeExtension', false, $validate);
        }
        
        // Validation for maximum filesize (take the smallest between config and server ones)
        $validate = ($config['file_max_size'] > 0 ? min($config['file_max_size'], $maxFileSize) : $maxFileSize);
        $upload->addValidator('FilesSize', false, array('max' => $validate));
        
        // Let's upload (if possible) !
        if ($upload->isUploaded($file) && $upload->isValid($file)) {
            try {
                $uploader = new Varien_File_Uploader($attribute->getName());
                $uploader->setAllowCreateFolders(true)
                    ->setAllowRenameFiles(true)
                    ->setFilesDispersion(false);
                
                if (!$uploader->save($this->getTargetDir())) {
                    Mage::throwException($helper->__('File "%s" upload failed for "%s" attribute', $fileInfo['name'], $label));
                }
                
                if ($object->getId()) {
                    $this->_saveObjectAttributeValue($object, $uploader->getUploadedFileName());
                } else {
                    $this->_pendingSaveObject = $object;
                    $this->_pendingSaveValue  = $uploader->getUploadedFileName();
                }
            } catch (Exception $e) {
                Mage::throwException($helper->__('An error occured during file "%s" upload for "%s" attribute : "%s"', $fileInfo['name'], $label, $e->getMessage()));
            }
        } elseif (($errors = $upload->getErrors())
                  && ($errors = $this->_parseValidatorErrors($errors, $fileInfo, $label))
                  && (count($errors) > 0)) {
            // Known upload error(s)
            Mage::throwException(implode("<br />", $errors));
        } else {
            // Unknown or not handled upload error
            Mage::throwException($helper->__('You must upload a valid file for "%s" attribute', $label));
        }
    }
    
    /**
    * Handle attribute file action callback
    * 
    * @param callback $callback
    * @param array $params
    * @return BL_FileAttributes_Model_Attribute_Backend_File
    */
    protected function _handleAttributeFileAction($callback, array $params=array())
    {
        try {
            call_user_func_array($callback, $params);
        } catch (Exception $e) {
            $config = Mage::helper('fileattributes/config')
                ->getAttributeConfiguration($this->getAttribute()->getId());
            
            if (isset($config['exceptions_handling_mode'])) {
                if ($config['exceptions_handling_mode'] == self::EXCEPTIONS_HANDLING_MODE_THROW) {
                    Mage::throwException($e->getMessage());
                } elseif ($config['exceptions_handling_mode'] == self::EXCEPTIONS_HANDLING_MODE_LOG) {
                    Mage::logException($e);
                } // Else ignore mode
            } else {
                Mage::logException($e);
            }
        }
    }
    
    /**
     * Before save attribute manipulation
     *
     * @param Mage_Core_Model_Abstract $object
     * @return BL_FileAttributes_Model_Attribute_Backend_File
     */
    public function beforeSave($object)
    {
        $config = Mage::helper('fileattributes/config')
            ->getAttributeConfiguration($this->getAttribute()->getId());
        
        if (isset($config['file_save_moment'])
            && ($config['file_save_moment'] == self::FILE_SAVE_MOMENT_BEFORE_ENTITY_SAVE)) {
            $this->_handleAttributeFileAction(array($this, '_saveAttributeFile'), array($object));
        }
        
        return $this;
    }
    
    /**
     * After save attribute manipulation
     *
     * @param Mage_Core_Model_Abstract $object
     * @return BL_FileAttributes_Model_Attribute_Backend_File
     */
    public function afterSave($object)
    {
        $config = Mage::helper('fileattributes/config')
            ->getAttributeConfiguration($this->getAttribute()->getId());
        
        if (isset($config['file_save_moment'])
            && ($config['file_save_moment'] == self::FILE_SAVE_MOMENT_AFTER_ENTITY_SAVE)) {
            $this->_handleAttributeFileAction(array($this, '_saveAttributeFile'), array($object));
        }
        
        if (!is_null($this->_pendingSaveObject)
            && ($this->_pendingSaveObject == $object)) {
            $this->_pendingSaveObject = null;
            $value = $this->_pendingSaveValue;
            $this->_pendingSaveValue  = null;
            $this->_handleAttributeFileAction(array($this, '_saveObjectAttributeValue'), array($object, $value));
        }
        
        return $this;
    }
    
    /**
     * Parse error messages from upload validator errors
     * 
     * @param array $errors Validation failure message codes
     * @param array $fileInfo File info
     * @return array Error messages
     */
    protected function _parseValidatorErrors($errors, $fileInfo, $label)
    {
        $messages = array();
        $helper   = Mage::helper('fileattributes');
        
        foreach ($errors as $errorCode) {
            if (($errorCode == Zend_Validate_File_Extension::FALSE_EXTENSION)
                || ($errorCode == Zend_Validate_File_ExcludeExtension::FALSE_EXTENSION)) {
                // Invalid file extension
                $messages[] = $helper->__('The file "%s" you uploaded for "%s" attribute has an invalid extension', $fileInfo['name'], $label);
            } elseif (($errorCode == Zend_Validate_File_MimeType::FALSE_TYPE)
                      || ($errorCode == Zend_Validate_File_ExcludeMimeType::FALSE_TYPE)) {
                // Invalid file type
                $messages[] = $helper->__('The file "%s" you uploaded for "%s" attribute is of an invalid type', $fileInfo['name'], $label);
            } elseif ($errorCode == Zend_Validate_File_IsImage::FALSE_TYPE) {
                // Invalid image
                $messages[] = $helper->__('The file "%s" you uploaded for "%s" attribute is not an image', $fileInfo['name'], $label);
            } elseif (($errorCode == Zend_Validate_File_ImageSize::WIDTH_TOO_BIG)
                      || ($errorCode == Zend_Validate_File_ImageSize::HEIGHT_TOO_BIG)) {
                // Too large images dimensions
                $messages[] = $helper->__('The dimensions of the image "%s" you uploaded for "%s" attribute exceed ones allowed by configuration', $fileInfo['name'], $label);
            } elseif (($errorCode == Zend_Validate_File_ImageSize::WIDTH_TOO_SMALL)
                      || ($errorCode == Zend_Validate_File_ImageSize::HEIGHT_TOO_SMALL)) {
                // Too small images dimensions
                $messages[] = $helper->__('The dimensions of the image "%s" you uploaded for "%s" attribute are smaller than ones required by configuration', $fileInfo['name'], $label);
            } elseif (($errorCode == Zend_Validate_File_FilesSize::TOO_BIG)
                      || ($errorCode == Zend_Validate_File_Upload::INI_SIZE)) {
                // Wrong file size
                $messages[] = $helper->__('The file "%s" you uploaded for "%s" attribute has a larger size than the one allowed by server or configuration', $fileInfo['name'], $label);
            } elseif (($errorCode == Zend_Validate_File_MimeType::NOT_DETECTED)
                      || ($errorCode == Zend_Validate_File_ExcludeMimeType::NOT_DETECTED)
                      || ($errorCode == Zend_Validate_File_IsImage::NOT_DETECTED)) {
                // MIME type not detected
                $messages[] = $helper->__('The MIME type of the file "%s" you uploaded for "%s" attribute could not be detected', $fileInfo['name'], $label);
            }// TODO handle other error codes ?
        }
        
        return array_unique($messages);
    }
    
    /**
     * Return max upload filesize in bytes
     *
     * @return int
     */
    public function getUploadMaxFilesize()
    {
        return min($this->_getBytesIniValue('upload_max_filesize'), $this->_getBytesIniValue('post_max_size'));
    }
    
    /**
     * Return php.ini setting value in bytes
     *
     * @param string $iniKey Var name to look for in php.ini
     * @return int Setting value
     */
    protected function _getBytesIniValue($iniKey)
    {
        $bytes = @ini_get($iniKey);
        
        // Kilobytes
        if (stristr($bytes, 'k')) {
            $bytes = intval($bytes) * 1024;
        // Megabytes
        } elseif (stristr($bytes, 'm')) {
            $bytes = intval($bytes) * 1024 * 1024;
        // Gigabytes
        } elseif (stristr($bytes, 'g')) {
            $bytes = intval($bytes) * 1024 * 1024 * 1024;
        }
        
        return (int)$bytes;
    }
    
    static public function getExceptionsHandlingModesAsOptionArray()
    {
        $helper = Mage::helper('fileattributes');
        return array(
            array(
                'value' => self::EXCEPTIONS_HANDLING_MODE_THROW,
                'label' => $helper->__('Throw'),
            ),
            array(
                'value' => self::EXCEPTIONS_HANDLING_MODE_LOG,
                'label' => $helper->__('Log'),
            ),
            array(
                'value' => self::EXCEPTIONS_HANDLING_MODE_IGNORE,
                'label' => $helper->__('Ignore'),
            ),
        );
    }
    
    static public function getFileSaveMomentsAsOptionArray()
    {
        $helper = Mage::helper('fileattributes');
        return array(
            array(
                'value' => self::FILE_SAVE_MOMENT_BEFORE_ENTITY_SAVE,
                'label' => $helper->__('Before Entity Save'),
            ),
            array(
                'value' => self::FILE_SAVE_MOMENT_AFTER_ENTITY_SAVE,
                'label' => $helper->__('After Entity Save'),
            ),
        );
    }
}