<?php

class Wyomind_Datafeedmanager_Block_Adminhtml_Configurations_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $types = array(1 => 'xml', 2 => 'txt', 3 => 'csv', 4 => 'tsv');
        $ext = $types[$row->getFeed_type()];

       
       
        $date = Mage::getSingleton('core/date')->date($row->getFeedDateformat(), $row->getFeedUpdatedAt());
       
        $fileNameOutput = str_replace('{f}', $row->getFeedName(), $date);
       
        $fileName = preg_replace('/^\//', '', $row->getFeed_path() . $fileNameOutput . "." . $ext);
        $url = $this->htmlEscape(Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);

        if (file_exists(BP . DS . $fileName)) {
            return sprintf('<a href="%1$s?r=' . time() . '" target="_blank">%1$s</a>', $url);
        }
        return $url;
    }

}
