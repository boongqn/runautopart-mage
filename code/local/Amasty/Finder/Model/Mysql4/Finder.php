<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */  
class Amasty_Finder_Model_Mysql4_Finder extends Mage_Core_Model_Mysql4_Abstract
{
    const MAX_LINE   = 2000;
    const BATCH_SIZE = 1000; 
    
    public function _construct()
    {    
        $this->_init('amfinder/finder', 'finder_id');
    }
    
    public function import($finder)
    {
        $err = array();
        
        $db = $this->_getWriteAdapter();
        
        //get dropdownds iDs as array 
        $dropdowns = array();
        foreach ($finder->getDropdowns() as $d){
            $dropdowns[] = $d->getId();
        }        
        $dropNum   = count($dropdowns);        
        
        if ($finder->getData('import_clear') && $dropNum){
            $ids = join(',', $dropdowns);
            $db->delete($this->getTable('amfinder/value'), "dropdown_id IN ($ids)"); 
        }
        
        if (empty($_FILES['import_file']['name'])){
            return $err; //ok, no data
        }
            
        $fileName = $_FILES['import_file']['tmp_name'];
        
        //for Mac OS
        ini_set('auto_detect_line_endings', 1);
        
        //file can be very big, so we read it by small chunks
        $fp = fopen($fileName, 'r');
        if (!$fp){
            throw new Exception('Can not open file');   
        }
        
        $err = array();
        $currRow = 0;
        
        $line = true;
        while ($line !== false){
            
            // convert file portion to the matrix
            // validate and normalize strings
            
            $names      = array(); // matrix h=BATCH_SIZE, w=dropNum+1;
            $namesIndex = 0;
            while (($line = fgetcsv($fp, self::MAX_LINE, ',', '"')) !== false) {
                $currRow++;
                
                if (count($line) != $dropNum+1){ 
                   $err[] = 'Line #' . $currRow . ' has been skipped: expected number of columns is '.($dropNum+1);
                   continue;
                }
                
                for ($i = 0; $i < $dropNum+1; $i++) {
                    $names[$namesIndex][$i] = trim($line[$i], "\r\n\t' ".'"');
                    if (!$names[$namesIndex][$i]){
                        $err[] = 'Line #' . $currRow . ' contains empty columns. Possible error.'; 
                    }
                }
                $namesIndex++;
                
                // break to write processed data
                if (self::BATCH_SIZE == $namesIndex){
                    break;
                }
            } // end while read 
            
            if (!$namesIndex){
                continue;
            }
            
            // like names, but 
            // a) contains real IDs from db
            // b) has additional first column=0 as artificial parent_id for the frist dropdown
            // c) has no SKU
            // d) initilized by 0 
            $parents = array_fill(0, $namesIndex, array_fill(0, $dropNum, 0));
            
            for ($j=0; $j < $dropNum; ++$j){ // columns
                $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/value') . '` (parent_id, dropdown_id, name) VALUES ';

                $insertedData = array();
                for ($i=0; $i < $namesIndex; ++$i){ //rows
                    $key = $parents[$i][$j] . '-' . $names[$i][$j];
                    
                    if (!isset($insertedData[$key])){
                        $insertedData[$key] = $parents[$i][$j];
                        $sql .= '(' . $parents[$i][$j] . ',' 
                             . $dropdowns[$j] . ',' 
                             . "'" . addslashes($names[$i][$j]) . "'),";
                    }
                    
                }
                
                //insert current column
                $sql = substr($sql, 0, -1);
                
                //Mage::getSingleton('adminhtml/session')->addSuccess($sql);
                $db->raw_query($sql);
            
                // now we need to select just inserted data to get IDs
                // we can create long where statement or select a bit more data that we actually need.
                // we are implementing the second approach
                $affectedParents = array_keys(array_flip($insertedData));
                $key = new Zend_Db_Expr('CONCAT(parent_id, "-", name)');
                $sql = $db->select()
                    ->from($this->getTable('amfinder/value'), array($key, 'value_id'))
                    ->where('parent_id IN(?)', $affectedParents)
                    ->where('dropdown_id = ?', $dropdowns[$j])
                ;
                
                //Mage::getSingleton('adminhtml/session')->addSuccess(htmlspecialchars($sql));
                $map = $db->fetchPairs($sql);
                
                for ($i=0; $i < $namesIndex; ++$i){ //rows
                    $key = $parents[$i][$j] . '-' . $names[$i][$j];
                    if (isset($map[$key])){
                        $parents[$i][$j+1] = $map[$key];
                    }
                    else {
                        $parents[$i][$j+1] = 0;
                        throw new Exception('Invalid data: key `' . $names[$i][$j] . '` is not found. Make sure the file does not contain the same string lowercase/uppercase.');
                    }
               } 
               
            } //end columns
            
            // now insert SKU as we know the last value_id
            $sql = 'INSERT IGNORE INTO `' . $this->getTable('amfinder/map') . '` (value_id, sku) VALUES ';
            $insertedData  = array();
            for ($i=0; $i < $namesIndex; ++$i){ 
                $valueId = $parents[$i][$dropNum];
                $skus = explode(',', $names[$i][$dropNum]);
                foreach($skus as $sku){
                    $key = $valueId . '-' . $sku;
                    if (!isset($insertedData[$key])){
                        $insertedData[$key] = 1;
                        $sql .= '(' . $valueId . ",'" . addslashes($sku) . "'),";
                    }
                }
            }
            $sql = substr($sql, 0, -1);
            
            //Mage::getSingleton('adminhtml/session')->addSuccess($sql);
            $db->raw_query($sql);
                       
        }// end main loop
        
        return $err;         
        
    }
    
    public function updateLinks()
    {
        $db = $this->_getWriteAdapter();
        $t1 = $this->getTable('amfinder/map');
        $t2 = $this->getTable('catalog/product');
        
        $sql = "UPDATE $t1, $t2  SET pid = entity_id WHERE $t1.sku=$t2.sku";
        $db->raw_query($sql);
        
        $sql = $db->select()
            ->from($t1, array('sku'))
            ->where('pid=0')
            ->limit(10);
        return $db->fetchCol($sql);
    }
        
}