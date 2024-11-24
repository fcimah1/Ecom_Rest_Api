<?php

namespace App\Http\Controllers;

use AramexService;
use Illuminate\Http\Request;

class AramexController extends Controller
{
    protected $aramex;
    public function __construct()
    {
        $this->aramex = new AramexService;
    }
    /**
    *
    *  @param array of pickup parameters
    *  @return object described in https://
    */
    public static function createPickup($param = [])
    {        
        $soapClient = AramexHelper::getSoapClient(AramexHelper::SHIPPING);


        // Preparation for initializing pickup request (Extract the data). 
        $pickupAddress = AramexHelper::extractPickupAddressContact($param);     // unchangeable
        $pickupDetails = AramexHelper::extractPickupDetails($param);            // changeable

        // initialize pickup request.
        $this->aramex->initializePickup($pickupDetails , $pickupAddress);

        // call the SoapClient API.
        $call = $soapClient->CreatePickup($this->aramex->getParam());
        
        $ret = new \stdClass;
        // check the response.
        if ($call->HasErrors){

            // prepare return object with errors described in call response.
            $ret->error = 1;
            // No one knows what is the structure of the response 
            if (is_array($call->Notifications)){
                $ret->errors = $call->Notifications['Notification'];
            }
            else {
                $ret->errors = $call->Notifications->Notification;
            }
        }
        else {
            
            // extract helpful data from call response.
            $pickupGUID = $call->ProcessedPickup->GUID;
            $pickupId = $call->ProcessedPickup->ID;
            //Extra Stuffs TODO.


            // Prepare return object.
            $ret->error = 0;
            $ret->pickupGUID = $pickupGUID;
            $ret->pickupID   = $pickupId;
            
        }
        // return the prepared object.
        return $ret;    
    }

    public static function cancelPickup($pickupGuid , $commnet)
    {
        // Define an instance from the core class.
        
        // Import SoapCLient object from Aramex's endpoint. 
        $soapClient = AramexHelper::getSoapClient(AramexHelper::SHIPPING);


        $this->aramex->initializePickupCancelation($pickupGuid , $commnet);

        $call = $soapClient->CancelPickup($this->aramex->getParam());
        
        $ret = new \stdClass;

        if ($call->HasErrors){
            $ret->error = 1;
            $ret->errors = $call->Notifications['Notification'];
        }
        else {
            $ret = $call;
        }
        return $ret;
    }


    /**
    *
    * @param array of shipment parameters 
    * @return object described in https://
    **/
    public static function createShipment($param =[])
    {
        // Define an instance from the core class.
        // Import SoapCLient object from Aramex's endpoint. 

        $soapClient = AramexHelper::getSoapClient(AramexHelper::SHIPPING);

        $shipperAddress = AramexHelper::extractShipperAddressContact($param);
        $consigneeAddress = AramexHelper::extractConsigneeAddressContact($param);

        $shipmentDetails = AramexHelper::extractShipmentDetails($param);

        $this->aramex->initializeShipment($shipperAddress, $consigneeAddress, $shipmentDetails);

        $call =  $soapClient->CreateShipments($this->aramex->getParam());
       
        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            if (isset($call->Notifications->Notification))
            {
                $ret->errors = [$call->Notifications->Notification];
            }

            if (is_object($call->Shipments->ProcessedShipment->Notifications->Notification))
            {
                $ret->errors = [ $call->Shipments->ProcessedShipment->Notifications->Notification ];
            }
            else {
                $ret->errors = $call->Shipments->ProcessedShipment->Notifications->Notification;
            }
        }
        else{
            $ret = $call;
        }

        return $ret;
    }



    public static function calculateRate($origin , $destination , $shipmentDetails , $currency)
    {


        $soapClient = AramexHelper::getSoapClient(AramexHelper::RATE);


        $destinationAddress = AramexHelper::extractAddress($destination);

        $originAddress = AramexHelper::extractAddress($origin);

        $details = AramexHelper::extractCalculateRateShipmentDetails($shipmentDetails);

        $this->aramex->initializeCalculateRate($originAddress, $destinationAddress, $details , $currency);

        $call =  $soapClient->calculateRate($this->aramex->getParam());

        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Notifications;
        }
        else{
            $ret = $call;
        }

        return $ret;

    }


    public static function trackShipments($param)
    {
        if (!is_array($param))
        {
            throw new \Exception("trackShipments Parameter Should Be an Array includes Strings", 1);
        }

        foreach ($param as $shipmentId) {
            if (!is_string($shipmentId))
            {
                throw new \Exception("trackShipments Parameter Should Be an Array includes Strings", 1);
            }
        }

        $soapClient = AramexHelper::getSoapClient(AramexHelper::TRACKING);



        $this->aramex->initializeShipmentTracking($param);

        $call = $soapClient->TrackShipments($this->aramex->getParam());
        
        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Notifications;
        }
        else{
            $ret = $call;
        }

        return $ret;
    }


    public static function fetchCountries($code = null)
    {

        $soapClient = AramexHelper::getSoapClient(AramexHelper::LOCATION);
        

        $this->aramex->initializeFetchCountries($code);

        if (isset($code))
            $call = $soapClient->FetchCountry($this->aramex->getParam());
        else 
            $call = $soapClient->FetchCountries($this->aramex->getParam());

        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Notification;                
        }
        else{
            $ret = $call;
        }

        return $ret;
    }

    public static function fetchCities($code, $nameStartWith = null)
    {
        $soapClient = AramexHelper::getSoapClient(AramexHelper::LOCATION);


        $this->aramex->initializeFetchCities($code, $nameStartWith);

        $call = $soapClient->FetchCities($this->aramex->getParam());
        
        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Notifications;
        }
        else{
            $ret = $call;
        }

        return $ret;
    } 

    public static function validateAddress($address)
    {
        $address = AramexHelper::extractAddress($address);


        $soapClient = AramexHelper::getSoapClient(AramexHelper::LOCATION);


        $this->aramex->initializeValidateAddress($address);

        $call = $soapClient->ValidateAddress($this->aramex->getParam());
        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Notifications;
        }
        else{
            $ret = $call;
        }

        return $ret;
    } 
}
