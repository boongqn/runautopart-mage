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

class BL_FileAttributes_Model_Observer
{
    /**
    * Add file type to available EAV attributes types
    * Used in system config's attributes management page
    * 
    * @param Varien_Event_Observer $observer
    */
    public function addFileAttributeType(Varien_Event_Observer $observer)
    {
        if ($response = $observer->getEvent()->getResponse()) {
            if (!is_array($types = $response->getTypes())) {
                $types = array();
            }
            $types[] = array(
                'value' => 'blfa_file',
                'label' => Mage::helper('fileattributes')->__('File'),
                'hide_fields' => array(
                    'is_unique',
                    'frontend_class',
                    'is_configurable',
                    
                    'is_filterable',
                    'is_filterable_in_search',
                    'is_used_for_promo_rules',
                    'position',
                    '_default_value',
                ),
            );
            $response->setTypes($types);
        }
    }
    
    /**
    * Force some values before "blfa_file" EAV attributes save
    * 
    * @param Varien_Event_Observer $observer
    */
    public function onAttributeSaveBefore(Varien_Event_Observer $observer)
    {
        if (($attribute = $observer->getEvent()->getAttribute())
            && ($attribute->getFrontendInput() == 'blfa_file')) {
            $attribute->setBackendModel('fileattributes/attribute_backend_file')
                ->setBackendType('varchar')
                ->setFrontendModel('fileattributes/attribute_frontend_file')
                ->setFrontendInputRenderer('fileattributes/form_element_file')
                ->setIsHtmlAllowedOnFront(1);
        }
    }
}