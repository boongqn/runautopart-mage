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

class BL_FileAttributes_Block_System_Config_Form_Field_Attributes_Configuration
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /*
    Renderers for specific columns
    */
    protected $_displayConfigSelectRenderer          = null;
    protected $_attributeSelectRenderer              = null;
    protected $_imageOnlySelectRenderer              = null;
    protected $_fileSaveMomentSelectRenderer         = null;
    protected $_exceptionsHandlingModeSelectRenderer = null;
    
    public function __construct()
    {
        parent::__construct();
        // Use our own template, made for configs with lots of columns
        $this->setTemplate('bl/fileattributes/system/config/form/field/array.phtml');
    }
    
    protected function _renderCellTemplate($columnName)
    {
         if (!empty($this->_columns[$columnName])
            && $this->_columns[$columnName]['renderer']) {
            $column = $this->_columns[$columnName];
            $inputName = $this->getElement()->getName() . '[#{_id}]['.$columnName.']';
            
            $html = $column['renderer']->setColumn($column)
                ->setColumnName($columnName)
                ->setInputName($inputName)
                ->toHtml();
            
            return $this->jsQuoteEscape($html);
         }
         return parent::_renderCellTemplate($columnName);
    }
    
    protected function _getDisplayConfigSelectRenderer()
    {
        if (is_null($this->_displayConfigSelectRenderer)) {
            $this->_displayConfigSelectRenderer = $this->getLayout()->createBlock(
                'fileattributes/system_config_form_field_select_boolean', '',
                array('is_render_to_js_template' => true)
            );
            $this->_displayConfigSelectRenderer->setExtraParams('style="width:80px"');
        }
        return $this->_displayConfigSelectRenderer;
    }
    
    protected function _getAttributeSelectRenderer()
    {
        if (is_null($this->_attributeSelectRenderer)) {
            $this->_attributeSelectRenderer = $this->getLayout()->createBlock(
                'fileattributes/system_config_form_field_select_attribute', '',
                array('is_render_to_js_template' => true)
            );
            $this->_attributeSelectRenderer->setExtraParams('style="width:200px"');
        }
        return $this->_attributeSelectRenderer;
    }
    
    protected function _getImageOnlySelectRenderer()
    {
        if (is_null($this->_imageOnlySelectRenderer)) {
            $this->_imageOnlySelectRenderer = $this->getLayout()->createBlock(
                'fileattributes/system_config_form_field_select_boolean', '',
                array('is_render_to_js_template' => true)
            );
            $this->_imageOnlySelectRenderer->setExtraParams('style="width:80px"');
        }
        return $this->_imageOnlySelectRenderer;
    }
    
    protected function _getFileSaveMomentSelectRenderer()
    {
        if (is_null($this->_fileSaveMomentSelectRenderer)) {
            $this->_fileSaveMomentSelectRenderer = $this->getLayout()->createBlock(
                'fileattributes/system_config_form_field_select_file_save_moment', '',
                array('is_render_to_js_template' => true)
            );
            $this->_fileSaveMomentSelectRenderer->setExtraParams('style="width:200px"');
        }
        return $this->_fileSaveMomentSelectRenderer;
    }
    
    protected function _getExceptionsHandlingModeSelectRenderer()
    {
        if (is_null($this->_exceptionsHandlingModeSelectRenderer)) {
            $this->_exceptionsHandlingModeSelectRenderer = $this->getLayout()->createBlock(
                'fileattributes/system_config_form_field_select_exceptions_handling_mode', '',
                array('is_render_to_js_template' => true)
            );
            $this->_exceptionsHandlingModeSelectRenderer->setExtraParams('style="width:100px"');
        }
        return $this->_exceptionsHandlingModeSelectRenderer;
    }
    
    protected function _prepareToRender()
    {
        $this->addColumn('attribute_id', array(
            'label'    => $this->__('Attribute'),
            'renderer' => $this->_getAttributeSelectRenderer(),
        ));
        $this->addColumn('display_config', array(
            'label'    => $this->__('Display Configuration On Fields'),
            'renderer' => $this->_getDisplayConfigSelectRenderer(),
        ));
        $this->addColumn('file_save_moment', array(
            'label'    => $this->__('File Save Moment'),
            'renderer' => $this->_getFileSaveMomentSelectRenderer(),
        ));
        $this->addColumn('exceptions_handling_mode', array(
            'label'    => $this->__('Exceptions Handling Mode'),
            'renderer' => $this->_getExceptionsHandlingModeSelectRenderer(),
        ));
        $this->addColumn('allowed_file_extensions', array(
            'label' => $this->__('Allowed File Extensions'),
            'style' => 'width:180px',
        ));
        $this->addColumn('forbidden_file_extensions', array(
            'label' => $this->__('Forbidden File Extensions'),
            'style' => 'width:180px',
        ));
        $this->addColumn('file_max_size', array(
            'label' => $this->__('File Max Size'),
            'style' => 'width:90px',
        ));
        $this->addColumn('allowed_mime_types', array(
            'label' => $this->__('Allowed MIME Types'),
            'style' => 'width:180px',
        ));
        $this->addColumn('forbidden_mime_types', array(
            'label' => $this->__('Forbidden MIME Types'),
            'style' => 'width:180px',
        ));
        $this->addColumn('image_only', array(
            'label'    => $this->__('Image Only'),
            'renderer' => $this->_getImageOnlySelectRenderer(),
        ));
        $this->addColumn('image_min_width', array(
            'label' => $this->__('Image Min Width'),
            'style' => 'width:60px',
        ));
        $this->addColumn('image_max_width', array(
            'label' => $this->__('Image Max Width'),
            'style' => 'width:60px',
        ));
        $this->addColumn('image_min_height', array(
            'label' => $this->__('Image Min Height'),
            'style' => 'width:60px',
        ));
        $this->addColumn('image_max_height', array(
            'label' => $this->__('Image Max Height'),
            'style' => 'width:60px',
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add Configuration');
    }
    
    /**
    * Prepare default values for given row's select fields
    * 
    * @param Varien_Object $row Row data object
    */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        parent::_prepareArrayRow($row);
        
        $displayConfig = ($row->hasData('display_config') ? (bool)$row->getData('display_config') : false);
        $row->setData(
            'option_extra_attr_'.$this->_getDisplayConfigSelectRenderer()->calcOptionHash($displayConfig ? 1 : 0),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_'.$this->_getAttributeSelectRenderer()->calcOptionHash($row->getData('attribute_id')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_'.$this->_getFileSaveMomentSelectRenderer()->calcOptionHash($row->getData('file_save_moment')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_'.$this->_getExceptionsHandlingModeSelectRenderer()->calcOptionHash($row->getData('exceptions_handling_mode')),
            'selected="selected"'
        );
        $imageOnly = ($row->hasData('image_only') ? (bool)$row->getData('image_only') : false);
        $row->setData(
            'option_extra_attr_'.$this->_getImageOnlySelectRenderer()->calcOptionHash($imageOnly ? 1 : 0),
            'selected="selected"'
        );
    }
    
    /**
    * Prepare default values for default row
    * 
    * @return string Default values JSON
    */
    public function getDefaultArrayRowJson()
    {
        return Zend_Json::encode(array_fill_keys(array(
            'option_extra_attr_'.$this->_getDisplayConfigSelectRenderer()->calcOptionHash(1),
            // Same values as used in category images' backend model
            'option_extra_attr_'.$this->_getFileSaveMomentSelectRenderer()->calcOptionHash(
                BL_FileAttributes_Model_Attribute_Backend_File::FILE_SAVE_MOMENT_AFTER_ENTITY_SAVE
            ),
            'option_extra_attr_'.$this->_getExceptionsHandlingModeSelectRenderer()->calcOptionHash(
                BL_FileAttributes_Model_Attribute_Backend_File::EXCEPTIONS_HANDLING_MODE_LOG
            ),
            'option_extra_attr_'.$this->_getImageOnlySelectRenderer()->calcOptionHash(0),
        ), 'selected="selected"'));
    }
    
    public function getEmptyWidth()
    {
        return 500;
    }
}