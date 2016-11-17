<?php

class Zizio_Powershare_Block_Adminhtml_Incentives extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_incentives';
		$this->_blockGroup = 'powershare';

		parent::__construct();

		$this->_helper = Mage::helper('powershare');
		$this->_headerText = $this->_helper->__('Power Share Incentives Management');
		//$this->_addButtonLabel = $this->_helper->__('Add Social Deal');
	}
	
	/**
	 * Prepare html output
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		try
		{
			// get generic html
			$html = parent::_toHtml();

			// add js
			$static_data = array(
				'pub_id'	 => $this->_helper->GetPublisherId(),
				'gmt_offset' => $this->_helper->GetGmtOffset(),
				'ext_type'	 => "powershare",
				'admin_loc'	 => "grid",
				'ext_ver'	 => $this->_helper->GetExtVer("")
			);
			$zizio_js = sprintf("<script type='text/javascript'> var z_statdata = %s; </script>",
							    $this->_helper->json_encode($static_data));
			$zizio_js .= $this->_helper->GetScriptBlock(array(
				array($this->_helper->GetZUtilsScriptUrl(array(), false)),
				array($this->_helper->GetAdminIncentiveGridScriptUrl(array(), false))
			), true);
			
			// Prepare the "Click Here" button and link below the powershare grid and append them to the grid HTML:
			$buttonAttributes = array(
				'label'		=>	$this->_helper->__('Click Here!'),
				'onclick'	=>	'setLocation(\'' . $this->getCreateUrl() .'\')',
			);
			$buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');
			$buttonBlock->addData($buttonAttributes);

			$html .= "<table width='100%' height='100'><tr><td style='text-align:center; vertical-align:middle;'>";
			$html .= $buttonBlock->toHtml();
			$html .= "<br />Create a new Incentive - <a href='".$this->getCreateUrl()."'>click here</a>!</td></tr></table>";
			
			// add additional buttons
			$html .= $this->getAdditionalHtml();
			
			return $zizio_js . $html;
		}
		catch (Exception $ex)
		{
			$this->_helper->LogError($ex);
			return parent::_toHtml();
		}
	}
	
	private function _getAdditionalButtonHtml($location, $label, $description, $new_tab=false)
	{
		if ($new_tab)
			$button = "<a target=\"_blank\" href=\"{$location}\"><button type=\"button\" class=\"scalable\"><span>{$label}</span></button></a>";
		else
			$button = "<button onclick=\"setLocation('{$location}')\" type=\"button\" class=\"scalable\"><span>{$label}</span></button>";
		
		return <<<EOF
<tr>
	<td class="scope-label">
		{$button}
	</td>
	<td class="scope-label">
		{$description}
	</td>
</tr>
EOF;
	}
	
	public function getAdditionalHtml()
	{
		return '
<br/>
<div class="content-header">
    <table cellspacing="0">
        <tr>
            <td><h3>' . $this->_helper->__('Additional Management') . '</h3></td><td class="form-buttons"></td>
         </tr>
    </table>
</div>
<table class="form-list">
	<tr><td class="scope-label">'
	 . '<a href="' . $this->_helper->GetZizioAttachUrl() . '" target="_blank">Attach this store</a> to your Zizio Account and recieve in-depth sale statistics and tools<br/>'
	 . '<a href="' . $this->_helper->GetZizioLoginUrl() . '" target="_blank">View your Zizio Account</a><br/>'
. '</tr></td>
</table>';

	}

	public function getButtonsHtml($area = null)
	{
		$configuration = '<a href="' . $this->getUrl('adminhtml/system_config/edit', array('section' => "powershare")) . '">Power Share Configuration</a>';
		return $this->_helper->GetSupportLinkHtml() . "&nbsp;|&nbsp;" . $configuration . parent::getButtonsHtml($area);
	}
}
