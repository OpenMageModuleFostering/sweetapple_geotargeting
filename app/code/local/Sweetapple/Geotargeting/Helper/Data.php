<?php
/**
 * Sweetapple_Geotargeting
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
class Sweetapple_Geotargeting_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_GEOIP_UPDATE_PATH = 'sweetapple_geotargeting/module/download_url';

    const XML_PATH_GEOIP_TEST_IP = 'sweetapple_geotargeting/module/test_ip';

    const GEOIP_DATABASE_PATH = 'GeoIP/GeoIP.dat';


    private $_modulePath = null;

    private $_databasePath = null;


    public function __construct()
    {
        $this->_modulePath = dirname(dirname(__FILE__));
        $this->_databasePath = $this->_modulePath . "/" . self::GEOIP_DATABASE_PATH;
    }


    /**
     * Fetches the country code for the specified IP or tests $_SERVER['REMOTE_ADDR']
     * @param string $ip
     * @return "UNKNOWN"|string
     */
    public function getCountryCodeFromIPAddress( $ip = null )
    {

        // No point in hitting this everytime. Once per visitor should be fine...
        if( $sessionCountryCode = Mage::getSingleton('core/session')->getData('sweetapple_geoip_country') ){
            return $sessionCountryCode;
        }

        $ip = $this->_getIPToTest( $ip );

        // Update the DB if necessary...
        $this->_updateDatabase();

        //Get the country code...
        require_once( $this->_modulePath . "/GeoIP/geoip.class.php" );
        $gi = geoip_open( $this->_databasePath, GEOIP_STANDARD);
        $visitorCountryCode = geoip_country_code_by_addr($gi, $ip);
        geoip_close($gi);

        if( $visitorCountryCode === ""){
            return "UNKNOWN";
        }else{
            //Store in session so we can save a few cycles..
            Mage::getSingleton('core/session')->setData('sweetapple_geoip_country', $visitorCountryCode);

            return $visitorCountryCode;
        }

    }


    /**
     * Returns the IP to test, using first specfic IP, then value from Module settings, finally $_SERVER['REMOTE_ADDR']
     * @param string $ip
     * @return string
     */
    private function _getIPToTest( $ip = null )
    {
        if( $ip === null ){
            $ip = Mage::getStoreConfig( self::XML_PATH_GEOIP_TEST_IP );
        }
        $validateIp = new Zend_Validate_Ip();
        return ( $validateIp->isValid( $ip) ) ? $ip : $_SERVER['REMOTE_ADDR'];
    }


    /**
     * Checks if the cached database needs updating. Updates are released on the first Tuesday of the month...
     * @return bool
     */
    private function _databaseNeedsUpdate()
    {

        if( file_exists( $this->_databasePath ) ){
            $path = $this->_modulePath . "/" . self::GEOIP_DATABASE_PATH;
            //Get creation date of cached database
            $cachedDatabaseUpdateTime = @filemtime( $path );

            //Get the timestamp for the first Wednesday of the current month..
            $currentDateTime  = new DateTime();
            $monthAndYearAsString = $currentDateTime->format( 'F Y');
            $databaseUpdateDay = new DateTime( 'first wednesday of ' . $monthAndYearAsString );
            $databaseUpdateTime = $databaseUpdateDay->getTimestamp();

            //If the cached db is older than the updatetime?
            return (  $databaseUpdateTime > $cachedDatabaseUpdateTime  ) ? true : false ;
        }
        return true;
    }


    /**
     * If the cached database is older than the first Wednesday of the month, fetch from the URL specified in System->Config and decompress
     * @return bool
     */
    private function _updateDatabase()
    {
        if( $this->_databaseNeedsUpdate() ){

            $store = Mage::app()->getStore();
            $updateUrl = $store->getConfig( self::XML_PATH_GEOIP_UPDATE_PATH );

            $client = new Zend_Http_Client($updateUrl);
            $response = $client->request(Zend_Http_Client::GET);
            // getBody() automatically decodes the body! Nice one Zend!
            $gzBody = $response->getBody();
            $body = $this->_gzdecode( $gzBody );

            return ( file_put_contents( $this->_databasePath, $body ) !== false) ? true : false ;
        }

        return true;
    }


    /**
     * Taken from user comments on http://uk1.php.net/gzdecode
     * @param $data
     * @return string
     */
    private function _gzdecode($data){

        if( function_exists( 'gzdecode' )){
            return gzdecode( $data);
        }else{
            $g = tempnam( sys_get_temp_dir() ,'ff');
            @file_put_contents($g,$data);
            ob_start();
            readgzfile($g);
            $d=ob_get_clean();
            return $d;
        }

    }

}