<?php
/**
 * Sweetapple_Geotargeting_Model_Validator_Testip
 *
 * This module was developed by Sweet-Apple.  If you require any
 * support or have any questions please contact us at info@sweet-apple.co.uk.
 *
 * @category   Sweetapple
 * @package    Sweetapple_Geotargeting
 * @author     Clive Sweeting, Sweet-Apple <info@sweet-apple.co.uk>
 * @copyright  Copyright (c) 2013 Sweet-Apple (http://www.sweet-apple.co.uk)
 * @license    OSL v3.0
*/

class Sweetapple_Geotargeting_Model_Validator_Testip extends Mage_Core_Model_Config_Data {


    /**
     * Before saving, check a valid IP adddress has been entered...
     * @return Mage_Core_Model_Abstract
     */
    public function save()
        {
            $newIP = $this->getValue(); //get the value from our config
            $validateIp = new Zend_Validate_Ip();
            if( (strlen($newIP) == 0 )  || $validateIp->isValid( $newIP) ){
                return parent::save();  //call original save method to save value
            }else{
                Mage::getSingleton('adminhtml/session')->addError('Please enter a valid IP address for the Test IP Address field.');
            }
        }


    /**
     * After Test IP Address is changed in System->Config clear the value stored in the session
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterSave()
    {

        Mage::getSingleton('core/session')->setData('sweetapple_geoip_country');
    }

}