<?php 
/**
 * Experius
 * 
 * @category model
 * @package experius_taxextended
 * @copyright Copyright (c) 2012 Experius Inc. (http://www.experius.nl)
 * 
 */

/**
 * 
 * This model overrides the age_Tax_Model_Calculation::calcTaxAmount() method and changes the default round to false. (opt-in instead of opt-out)
 * @author Moncif
 *
 */
class Experius_Taxextended_Model_Calculation extends Mage_Tax_Model_Calculation{
	
	/**
	 * This method overrides the calcTaxAmount method. It changes the default value of round argument.
	 * @see Mage_Tax_Model_Calculation::calcTaxAmount()
	 * @param $price float
	 * @param $taxRate int
	 * @param $priceIncludeTax boolean default false
	 * @param $round boolean default false
	 */
	public function calcTaxAmount($price, $taxRate, $priceIncludeTax=false, $round=false){
		return parent::calcTaxAmount($price, $taxRate, $priceIncludeTax, $round);
	}
	
}