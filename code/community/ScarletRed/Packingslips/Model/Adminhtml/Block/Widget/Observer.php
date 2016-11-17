<?php
/**
	THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
	INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
	PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
	LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
	TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
	OR OTHER DEALINGS IN THE SOFTWARE.


	* ScarletRed_Packingslips module
	* 
	* @category   	ScarletRed
	* @package		ScarletRed_Packingslips
	* @version		1.0.1
	* @author		Lee Walker
	* @copyright  	ScarletRed Copyright (c) 2014
**/

class ScarletRed_Packingslips_Model_Adminhtml_Block_Widget_Observer
{
	public function addMassAction($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
			&& in_array($block->getRequest()->getControllerName(),array('sales_order','adminhtml_sales_order')))
        {
            $block->addItem('srpdfshipments_order', array(
                'label'=> Mage::helper('scarletred_packingslips')->__('Print Packingslips (pre-shipped)'),
                'url' => $block->getUrl('*/srpdf_order/srpdfshipments'),
            ));
			
			if(Mage::helper('core')->isModuleEnabled('VES_PdfPro')){
				$block->removeItem('srpdfshipments_order');
				$block->addItem('easypdf-print-shipments', array(
						'label'=> 'Easy PDF - '.Mage::helper('pdfpro')->__('Print Packingslips (pre-shipped)'),
						'url'  => Mage::helper('adminhtml')->getUrl('*/srpdf_order/srpdfproshipments'),
				));
			}
			
			if(Mage::helper('core')->isModuleEnabled('EM_DeleteOrder')){
				$block->removeItem('delete_order');
				$block->addItem('delete_order', array(
					'label'=> Mage::helper('sales')->__('Delete order'),
					'url'  => Mage::helper('adminhtml')->getUrl('*/sales_order/deleteorder'),
				));
			}
        }
    }
}