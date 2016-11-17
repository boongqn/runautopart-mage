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

class BL_FileAttributes_Block_Adminhtml_Widget_Grid_Column_Filter_Timestamp
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Datetime
{
    public function getValue($index=null)
    {
        if ($index) {
            if ($data = $this->getData('value', 'orig_'.$index)) {
                return $data;
            }
            return null;
        }
        $value = $this->getData('value');
        if (!empty($value['to']) && !$this->getColumn()->getFilterTime()) {
            $datetime = $value['to'];
            $datetime->addSecond(self::END_OF_DAY_IN_SECONDS);
        }
        if (!empty($value['from'])) {
            $datetime = $value['from'];
            $value['from'] = $datetime->getTimestamp();
        }
        if (!empty($value['to'])) {
            $datetime = $value['to'];
            $value['to'] = $datetime->getTimestamp();
        }
        return $value;
    }
}