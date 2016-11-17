<?php
/**
 * Activo
 *
 * @category    
 * @package     Activo_Adwordsconversion
 * @copyright   Copyright (c) 2012 Activo Extensions. (http://extensions.activo.com)
 * @license     Commercial
 */
?>
<?php
class Activo_Adwordsconversion_Model_System_Config_TotalfieldOptions
{
    public function toOptionArray()
    {
        return array(
            //array('value'=>'', 'label'=>''),
            array('value'=>0, 
                    'label'=>Mage::helper('adwordsconversion')->__('Subtotal')),
            array('value'=>1, 
                    'label'=>Mage::helper('adwordsconversion')->__('Grand Total'))
        );
    }
}
