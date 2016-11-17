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

class ScarletRed_Packingslips_Model_Order_FauxShipment extends Mage_Sales_Model_Order_Shipment
{
   public function setOrder(Mage_Sales_Model_Order $order)
   {
      parent::setOrder($order);
      $this->_items = array();
      $items = $order->getAllItems();
      foreach($items as $item)
      {
         $shipmentItem = Mage::getModel('sales/order_shipment_item');
         $shipmentItem->setShipment($this);
         $shipmentItem->setOrderItem($item);
		 $shipmentItem->setQty($item->getQtyToShip());
         $shipmentItem->setSku($item->getSku());
         if(!$shipmentItem->getName())
             $shipmentItem->setName($item->getName());
         $this->_items[] = $shipmentItem;
      }
   }
}