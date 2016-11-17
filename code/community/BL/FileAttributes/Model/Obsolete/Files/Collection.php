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

class BL_FileAttributes_Model_Obsolete_Files_Collection
    extends Varien_Data_Collection_Filesystem
{
    public function __construct()
    {
        parent::__construct();
        $this->_collectRecursively = false;
        $this->_allowedFilesMask = '/^.+$/i';
        $this->addTargetDir(Mage::getModel('fileattributes/attribute_backend_file')->getTargetDir());
        $this->addExcludeFilter($this->_getUsedFiles());
    }
    
    protected function _generateRow($filename)
    {
        $fileInfos = pathinfo($filename);
        $fileStats = stat($filename);
        return array_merge(parent::_generateRow($filename), array(
            'id'   => Mage::helper('fileattributes')->encodeFilename(basename($filename)),
            'url'  => Mage::helper('fileattributes')->getFileUrl(basename($filename)),
            'size' => $fileStats['size'],
            'extension'  => (isset($fileInfos['extension']) ? $fileInfos['extension'] : ''),
            'updated_at' => $fileStats['mtime'],
        ));
    }
    
    protected function _getUsedFiles()
    {
        $products   = Mage::getModel('catalog/product')->getCollection();
        $attributes = Mage::helper('fileattributes')->getFileAttributesCollection();
        $usedFiles  = array();
        
        foreach ($attributes as $attribute) {
            $products->addAttributeToSelect($attribute->getAttributeCode())
                ->addAttributeToFilter($attribute->getAttributeCode(), array(
                    array('neq' => ''),
                    array('notnull' => 1),
                ));
        }
        
        foreach ($products as $product) {
            foreach ($attributes as $attribute) {
                if ($file = $product->getData($attribute->getAttributeCode())) {
                    $usedFiles[] = $file;
                }
            }
        }
        
        return $usedFiles;
    }
    
    public function addExcludeFilter(array $files)
    {
        // "nin" filter uses "filterCallbackIn" but this method does not exist...
        return $this->addCallbackFilter('basename', $files, 'nin', array($this, 'filterCallbackInArray'), true);
    }
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('basename', 'filename');
    }
    
    public function toOptionHash()
    {
        return $this->_toOptionHash('basename', 'filename');
    }
}