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

require_once('Mage/Adminhtml/controllers/Sales/OrderController.php');
class ScarletRed_Packingslips_Adminhtml_Srpdf_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Print packingslips for selected orders
     */
    public function srpdfshipmentsAction(){
        $order_ids = $this->getRequest()->getParam('order_ids');
        $pdf = new Zend_Pdf();

        foreach($order_ids as $order_id)
        {
            $order = Mage::getModel('sales/order');
            $order->load($order_id);
            $fauxShipment = Mage::getModel('scarletred_packingslips/order_fauxShipment');
            $fauxShipment->setOrder($order);
            $shipments = array();
            $shipments[] = $fauxShipment;
            $tmp_pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
            $pdf->pages = array_merge($pdf->pages, $tmp_pdf->pages);
        }

        return $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                $pdf->render(), 'application/pdf'
        );
        $this->_redirect('*/*/');
    }
	
	/**
	 * Print Packingslips
	 */
	public function srpdfproshipmentsAction(){
		$orderIds = $this->getRequest()->getParam('order_ids');
        if (!empty($orderIds)) {
			foreach($orderIds as $orderId) {
				$order = Mage::getModel('sales/order');
				$order->load($orderId);
				$fauxShipment = Mage::getModel('scarletred_packingslips/order_fauxShipment');
				$fauxShipment->setOrder($order);
	            $shipments[] = $fauxShipment;
			}
			foreach($shipments as $shipment){
				$shipmentDatas[] = Mage::getModel('pdfpro/order_shipment')->initShipmentData($shipment);
			}
			try{
				$result = Mage::helper('pdfpro')->initPdf($shipmentDatas,'shipment');
				if($result['success']){
					$this->_prepareDownloadResponse(Mage::helper('pdfpro')->getFileName('shipments').'.pdf', $result['content']);
				}else{
					throw new Mage_Core_Exception($result['msg']);
				}
			}catch(Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				if(!empty($orderIds)) $this->_redirect('adminhtml/sales_order/index');
				else $this->_redirect('adminhtml/sales_shipment/index');
			}
		} else {
			$this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
			if(!empty($orderIds)) $this->_redirect('adminhtml/sales_order/index');
			else $this->_redirect('adminhtml/sales_shipment/index');
		}
	}	
}