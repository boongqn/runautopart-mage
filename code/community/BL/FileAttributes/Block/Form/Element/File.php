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

class BL_FileAttributes_Block_Form_Element_File
    extends Varien_Data_Form_Element_Abstract
{
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('file');
        $this->setExtType('file');
    }
    
    public function getElementHtml()
    {
        $html = '';
        
        if ($this->getValue()) {
            $url = Mage::helper('fileattributes')->getFileUrl($this->getValue());
            $html = '<a href="'.$url.'" target="_blank">'.$this->getValue().'</a><br />';
        }
        
        $this->setClass('input-file');
        if ($this->getEntityAttribute()->getIsRequired()) {
            if (!$this->getValue()) {
                // Add required class only if we don't have any current value, because of file inputs
                $this->addClass('required-entry');
            }
        } else {
            // No delete for required attributes, to delete a file it is just needed to change it
            $html .= $this->_getDeleteCheckbox();
        }
        
        $html .= parent::getElementHtml();
        $html .= $this->_getDescriptionHtml();
        
        return $html;
    }
    
    protected function _getDeleteCheckbox()
    {
        if ($this->getValue()) {
            $html = '<span class="delete-file">';
            $html .= '<input type="checkbox" name="'.parent::getName().'[delete]" value="1" class="checkbox" id="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' disabled="disabled"': '').' onchange="(this.checked ? $(\''.$this->getHtmlId().'\').disable() : $(\''.$this->getHtmlId().'\').enable());"/>';
            $html .= '<label for="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' class="disabled"' : '').'> '.Mage::helper('fileattributes')->__('Delete File').'</label>';
            $html .= $this->_getHiddenInput();
            $html .= '</span>';
            return $html;
        }
        return '';
    }
    
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="'.parent::getName().'[value]" value="'.$this->getValue().'" />';
    }
    
    protected function _getDescriptionHtml()
    {
        $attributeModel = Mage::getModel('fileattributes/attribute_backend_file')
            ->setAttribute($this->getEntityAttribute());
        
        $config = Mage::helper('fileattributes/config')
            ->getAttributeConfiguration($this->getEntityAttribute()->getId());
        
        if (!isset($config['display_config']) || !$config['display_config']) {
            return '';
        }
        
        $helper = Mage::helper('fileattributes');
        $html   = array();
        
        // File extensions
        if (isset($config['allowed_file_extensions'])
            && is_array($exts = $config['allowed_file_extensions'])) {
            sort($exts);
            $html[] = array($helper->__('Allowed File Extensions'), implode(', ', $exts));
        } elseif (isset($config['forbidden_file_extensions'])
                  && is_array($exts = $config['forbidden_file_extensions'])) {
            sort($exts);
            $html[] = array($helper->__('Forbidden File Extensions'), implode(', ', $exts));
        }
        
        // File size
        $serverMaxFileSize = $attributeModel->getUploadMaxFileSize();
        if (($config['file_max_size'] > 0)
             && ($config['file_max_size'] < $serverMaxFileSize)) {
            $html[] = array(
                $helper->__('Maximum File Size'),
                $helper->__('%s (configuration)', $helper->getFileSizeForDisplay($config['file_max_size']))
            );
        } else {
            $html[] = array(
                $helper->__('Maximum File Size'),
                $helper->__('%s (server)', $helper->getFileSizeForDisplay($serverMaxFileSize))
            );
        }
        
        // MIME types
        if (isset($config['allowed_mime_types'])
            && is_array($types = $config['allowed_mime_types'])) {
            $types = array_values($types);
            sort($types);
            $html[] = array($helper->__('Allowed File Types'), implode(', ', $types));
        } elseif (isset($config['forbidden_mime_types'])
                  && is_array($types = $config['forbidden_mime_types'])) {
            $types = array_values($types);
            sort($types);
            $html[] = array($helper->__('Forbidden File Types'), implode(', ', $types));
        }
        
        // Image only
        if ($config['image_only']) {
            $html[] = array($helper->__('Image Only'), $helper->__('Yes'));
        }
        
        if ($config['image_min_width'] > 0) {
            $html[] = array($helper->__('Image Minimum Width'), $helper->__('%spx', $config['image_min_width']));
        }
        if ($config['image_max_width'] > 0) {
            $html[] = array($helper->__('Image Maximum Width'), $helper->__('%spx', $config['image_max_width']));
        }
        if ($config['image_min_height'] > 0) {
            $html[] = array($helper->__('Image Minimum Height'), $helper->__('%spx', $config['image_min_height']));
        }
        if ($config['image_max_height'] > 0) {
            $html[] = array($helper->__('Image Maximum Height'), $helper->__('%spx', $config['image_max_height']));
        }
        
        if (!empty($html)) {
            // Wrap all configs in a toggleable table
            $result =  '<br /><a href="#" onclick="$(\''.$this->getHtmlId().'_config_container\').toggle(); return false;">';
            $result .= $helper->__('Show / Hide Configuration File').'</a>';
            $result .= '<div id="'.$this->getHtmlId().'_config_container" style="display: none;">';
            $result .= '<table class="blfa-file-configuration-table">';
            foreach ($html as $part) {
                $result .= '<tr><th>'.$part[0].'</th><td>'.$part[1].'</td></tr>';
            }
            $result .= '</table>';
            return $result;
        } else {
            return '';
        }
    }
    
    public function getName()
    {
        return  $this->getData('name');
    }
}