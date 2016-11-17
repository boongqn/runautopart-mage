<?php

class Zizio_Powershare_Block_Adminhtml_Incentives_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'powershare';
		$this->_controller = 'adminhtml_incentives';
		$this->_helper = Mage::helper('powershare');
		
		$this->_updateButton('save', 'label', $this->_helper->__('Save Item'));
		$this->_updateButton('delete', 'label', $this->_helper->__('Delete Item'));
		
		// Save and Continue Edit
		$this->_addButton(
			'saveandcontinue', array(
				'label'	  => Mage::helper('adminhtml')->__('Save And Continue Edit'),
				'onclick' => 'saveAndContinueEdit()',
				'class'	  => 'save'
			),
			-100
		);

		$this->_formScripts[] = "function saveAndContinueEdit() { editForm.submit($('edit_form').action + 'back/edit/'); }";
	}
	
	public function getHeaderText()
	{
		$incentive = Mage::registry('powershare_incentive');
		if($incentive && $incentive->getId())
			return $this->_helper->__("Edit '%s' Incentive", $incentive->getName());
		else
			return $this->_helper->__("Create New Incentive");
	}
	
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        
		$static_data = array(
			'pub_id'	 		  => $this->_helper->GetPublisherId(),
			'gmt_offset' 		  => $this->_helper->GetGmtOffset(),
			'ext_type'	 		  => "powershare",
			'admin_loc'	 		  => "edit",
			'ext_ver'	 		  => $this->_helper->GetExtVer(""),
			'validate_coupon_url' => $this->getUrl("powershare/index/validateCoupon", array('_query' => array(
									     'ajax' => "1",
									     'zizio_coupon' => "__ZIZIO_COUPON__",
									     'zizio_callback' => "__ZIZIO_CALLBACK__",
									     'zizio_end_date' => "__ZIZIO_END_DATE__"
								     ))),
			'coupons_grid_url'    => $this->getUrl("adminhtml/promo_quote/index")
		);
		$zizio_js = sprintf("<script type='text/javascript'> var z_statdata = %s; </script>",
						    $this->_helper->json_encode($static_data));
		$zizio_js .= $this->_helper->GetScriptBlock(array(
			array($this->_helper->GetZUtilsScriptUrl(array(), false)),
			array($this->_helper->GetAdminIncentiveEditScriptUrl(array(), false))
		), true);
		
        return $zizio_js . $html;
    }
}
