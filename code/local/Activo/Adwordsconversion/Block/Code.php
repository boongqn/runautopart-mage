<?php
/**
 * Activo
 *
 * @category    
 * @package     Activo_Adwordsconversion
 * @copyright   Copyright (c) 2012 Activo Extensions. (http://extensions.activo.com)
 * @license     Commercial
 */
?>
<?php
class Activo_Adwordsconversion_Block_Code extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        if (Mage::getStoreConfig('activo_adwordsconversion/global/enabled')==0)
        {
            return '';
        }
        
        $conversionId = Mage::getStoreConfig('activo_adwordsconversion/global/id');
        $conversionLanguage = Mage::getStoreConfig('activo_adwordsconversion/global/language');
        $conversionLabel = Mage::getStoreConfig('activo_adwordsconversion/global/label');
        $conversionColor = Mage::getStoreConfig('activo_adwordsconversion/global/bgcolor');
        
        $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $value = 0;
        if ($order != null)
        {
            if (Mage::getStoreConfig('activo_adwordsconversion/global/totalfield')==0)
            {
                if ($order->getSubtotal() > 0)
                {
                    $value = number_format($order->getSubtotal(),2,".","");
                }
            }
            else
            {
                if ($order->getGrandTotal() > 0)
                {
                    $value = number_format($order->getGrandTotal(),2,".","");
                }
            }
        }
        
$html = <<<HTMLEND

<!-- [START] Activo Adwords Conversion Tracking Code -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = $conversionId;
var google_conversion_language = "$conversionLanguage";
var google_conversion_format = "1";
var google_conversion_color = "$conversionColor";
var google_conversion_label = "$conversionLabel";
var google_conversion_value = $value;
/* ]]> */
</script>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>

<noscript>
  <img height=1 width=1 border=0
  src="http://www.googleadservices.com/pagead/conversion/$conversionId/?value=$value&label=$conversionLabel&script=0">
</noscript>
  
<!-- [END] Activo Adwords Conversion Tracking Code -->

HTMLEND;
 
        if (Mage::getStoreConfig('activo_adwordsconversion/global/isdebug')==1)
        {
            return ' <pre>'.htmlentities($html).'</pre> '.$html;
        }
        else
        {
            return $html;
        }
    }
}