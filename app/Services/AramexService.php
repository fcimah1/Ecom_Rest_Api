<?php

class AramexService  
{

    public $param;
    public function __construct()
    {
        $this->param = [
            'ClientInfo'            => [
                'AccountCountryCode'    => '',
                'AccountEntity'         => '',
                'AccountNumber'         => '',
                'AccountPin'            => '',
                'UserName'              => '',
                'Password'              => '',
                'Version'               => ''
            ],
            'Transaction'           => [
                'Reference1'            => '', // extra param for response
                'Reference2'            => '', // extra param for response
                'Reference3'            => '', // extra param for response
                'Reference4'            => '', // extra param for response
            ],
            'LabelInfo'     => config('aramex.LabelInfo'),
        ];


        
    }

    public function initializeShipment($shipper, $consignee, $details)
    {
        $this->param['Shipments'] = [
            [
                'Shipper' => [
                    'Reference1' => $details->ShipperReference, // for response              
                    'AccountNumber' => $this->accNum,
                    'Contact' => [
                        'PersonName' => $shipper->PersonName,
                        'CompanyName' => config('aramex.company_name'),
                        'PhoneNumber1' => $shipper->PhoneNumber1,
                        'CellPhone' => $shipper->CellPhone,
                        'EmailAddress' => $shipper->EmailAddress,
                    ],
                    "PartyAddress" => [
                        "Line1" => $shipper->Line1,
                        "Line2" => $shipper->Line2,
                        "Line3" => $shipper->Line3,
                        "City" => $shipper->City,
                        // "StateOrProvinceCode"=> null,
                        "PostCode" => $shipper->ZipCode,
                        "CountryCode" => $shipper->CountryCode,
                        "Longitude" => 0,
                        "Latitude" => 0
                    ],
                ],
                'Consignee' => [
                    'Reference1' => $details->ConsgineeReference, // for response              
                    'AccountNumber' => $this->accNum, //Account Number 
                    'Contact' => [
                        'PersonName' => $consignee->PersonName,//Person Name 
                        'CompanyName' => $consignee->PersonName,
                        'PhoneNumber1' => $consignee->PhoneNumber1, //Phone Number
                        'CellPhone' => $consignee->CellPhone, //Cell Phone 
                        'EmailAddress' => $consignee->EmailAddress, // Email
                    ],
                    'PartyAddress' => [
                        'Line1' => $consignee->Line1,//Line1 
                        'Line2' => $consignee->Line2,//Line2 
                        'Line3' => $consignee->Line3,//Line3
                        'CountryCode' => $consignee->CountryCode, // Country Code
                        'City' => $consignee->City,
                    ],

                ],
                'ShippingDateTime' => $details->ShippingDateTime, // Should be Filled
                "DueDate" => $details->DueDate, // Should be Filled
                "Comments" => $details->Comments, //Should Be Filled
                "PickupLocation" => $details->PickupLocation, // Should be Filled
                "Attachments" => null,
                "ForeignHAWB" => null,
                'Reference1' => $details->Reference1, // for response              
                "TransportType" => 0,
                "PickupGUID" => $details->PickupGUID,
                "Number" => null,
                'Details' => [
                    'ActualWeight' => [
                        'Value' => $details->ActualWeight,
                        'Unit' => 'Kg'
                    ],

                    'ProductGroup' => $details->ProductGroup,
                    'ProductType' => $details->ProductType,
                    'PaymentType' => $details->PaymentType,
                    'PaymentOptions' => $details->PaymentOptions, // it can be filled
                    'NumberOfPieces' => $details->NumberOfPieces,
                    'DescriptionOfGoods' => $details->DescriptionOfGoods,
                    'GoodsOriginCountry' => $details->GoodsOriginCountry,
                    'Services' => $details->Services,
                    'Items' => $details->NumberOfPieces,

                    // Optionals Depending on Payment terms above

                    'CollectAmount' => [
                        'Value' => $details->CollectAmount,
                        'CurrencyCode' => $details->CurrencyCode
                    ],

                    'CashOnDeliveryAmount' => [
                        'Value' => $details->CashOnDeliveryAmount,
                        'CurrencyCode' => 'USD'
                        // 'CurrencyCode'  => $details->CurrencyCode
                    ],

                    'InsuranceAmount' => [
                        'Value' => $details->InsuranceAmount,
                        'CurrencyCode' => $details->CurrencyCode
                    ],

                    'CashAdditionalAmount' => [
                        'Value' => $details->CashAdditionalAmount,
                        'CurrencyCode' => $details->CurrencyCode
                    ],
                    'CashAdditionalAmountDescription' => $details->CashAdditionalAmountDescription,

                    'CustomsValueAmount' => [
                        'Value' => $details->CustomsValueAmount,
                        'CurrencyCode' => $details->CurrencyCode
                    ],

                ]
            ]
        ];

    }

    public function initializePickup($pickupDetails, $pickupAddress){

        $this->param['Pickup'] = [  
            'Reference1'=> $pickupDetails->Reference1,
            'Reference2'=> $pickupDetails->Reference1,
            'PickupLocation' =>$pickupDetails->PickupLocation,
            'Status' => $pickupDetails->Status, 
            'PickupDate' => $pickupDetails->PickupDate,
            'ReadyTime' => $pickupDetails->ReadyTime,
            'LastPickupTime' => $pickupDetails->LastPickupTime, // +26 hours
            'ClosingTime' => $pickupDetails->ClosingTime, //+28 hours
            'PickupContact' => [
                'PersonName'    => $pickupAddress->PersonName, // should be static 'SomeName',
                'CompanyName'   => config('aramex.CompanyName'), // config file
                'PhoneNumber1'  => $pickupAddress->PhoneNumber1, // should be static '0777777',
                'CellPhone'     => $pickupAddress->CellPhone, // should be static '0555555',
                'EmailAddress'  => $pickupAddress->EmailAddress // should be static 'email@somedomain.com'
            ],
            'PickupAddress' => [
                'Line1' => $pickupAddress->Line1, // should be static 'Line1 Address',
                'Line2' => $pickupAddress->Line2, // should be static 'Line2 Address',
                'Line3' => $pickupAddress->Line3, // should be static 'Line3 Address',
                'CountryCode' => $pickupAddress->CountryCode, // should be static 'AE',
                'City' => $pickupAddress->City, // should be static 'Dubai'
                'PostCode' => $pickupAddress->ZipCode
            ],
            'PickupItems' => [
                'PickupItemDetail' => [
                    'ProductGroup' => $pickupDetails->ProductGroup,
                    'Payment' => $pickupDetails->Payment,
                    'ProductType' => $pickupDetails->ProductType,
                    'NumberOfPieces' => '1',
                    'ShipmentWeight' => [
                        'Value' => $pickupDetails->Weight,
                        'Unit' => 'Kg'
                    ],
                    'NumberOfShipments' => 1,
                    'ShipmentVolume'=> [
                        'Value'=> $pickupDetails->Volume,
                        'Unit'=>'Cm3'
                    ]
                ]
            ]
        ];
    }

    public function initializePickupCancelation($guid , $comment)
    {
        $this->param['PickupGUID'] = $guid;
        $this->param['Comments'] = $comment;
    }

    public function initializeCalculateRate($originAddress,$destinationAddress , $shipmentDetails, $currencyCode){

        $this->param['OriginAddress'] = $originAddress;

        $this->param['DestinationAddress'] = $destinationAddress;

        $this->param['ShipmentDetails'] = $shipmentDetails;

        $this->param["PreferredCurrencyCode"] = $currencyCode;
    }


    public function initializeShipmentTracking( $param)
    {
        $this->param['Shipments'] = $param; 
    }

    public function initializeFetchCountries($code = null)
    {
        if (isset($code))
            $this->param['Code'] = $code;
    }

    public function initializeFetchCities($code, $nameStartWith = null)
    {
        $this->param['CountryCode'] = $code;
        $this->param['NameStartsWith'] = $nameStartWith;
    }

    public function initializeValidateAddress($address)
    {
        $this->param['Address'] = $address;
    }

    public function getParam()
    {
        return $this->param;
    }
}