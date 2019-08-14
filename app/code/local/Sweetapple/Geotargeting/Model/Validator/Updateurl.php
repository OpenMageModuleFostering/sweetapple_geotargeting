<?php
/**
 * Sweetapple_Geotargeting_Model_Validator_UpdateUrl
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

class Sweetapple_Geotargeting_Model_Validator_UpdateUrl extends Mage_Core_Model_Config_Data {


    /**
     * Before saving, check a valid IP adddress has been entered...
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        $newUrl = $this->getValue(); //get the value from our config

        if(filter_var($newUrl, FILTER_VALIDATE_URL))
        {
            return parent::save();  //call original save method to save value
        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError('Please enter a valid URL for the Database Update URL field.');
        }
    }


    /**
     * After downloadurl is changed in System->Config clear the value stored in the session
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterSave()
    {
        $_ipHelper = Mage::helper('sweetapple_geotargeting/data'); /* @var $_ipHelper Sweetapple_Geotargeting_Helper_Data */
        $_ipHelper->updateDatabase();
    }

}