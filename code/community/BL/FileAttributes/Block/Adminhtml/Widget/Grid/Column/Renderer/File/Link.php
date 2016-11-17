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

class BL_FileAttributes_Block_Adminhtml_Widget_Grid_Column_Renderer_File_Link
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if ($value = $this->_getValue($row)) {
            return '<a href="'.$value.'" target="_blank">'.$this->helper('fileattributes')->__('View File').'</a>';
        }
        return parent::render($row);
    }
    
    public function renderExport(Varien_Object $row)
    {
        return parent::render($row);
    }
}