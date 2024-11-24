<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Octw\Aramex\Aramex;

class AramexTrackController extends Controller
{
    $data = Aramex::createPickup([
        'name' => 'MyName',
        'cell_phone' => '+123123123',
        'phone' => '+123123123',
        'email' => 'myEmail@gmail.com',
        'city' => 'New York',
        'country_code' => 'US',
        'zip_code'=> 10001,
        'line1' => 'The line1 Details',
        'line2' => 'The line2 Details',
        'line3' => 'The line2 Details',
        'pickup_date' => time() + 45000,
        'ready_time' => time()  + 43000,
        'last_pickup_time' => time() +  45000,
        'closing_time' => time()  + 45000,
        'status' => 'Ready', 
        'pickup_location' => 'some location',
        'weight' => 123,
        'volume' => 1
    ]);

    // extracting GUID
   if (!$data->error)
      $guid = $data->pickupGUID;
   else
      $error = $data->error;

   // create shipment
      $callResponse = Aramex::createShipment([
        'shipper' => [
            'name' => 'Steve',
            'email' => 'email@users.companies',
            'phone'      => '+123456789982',
            'cell_phone' => '+321654987789',
            'country_code' => 'US',
            'city' => 'New York',
            'zip_code' => 32160,
            'line1' => 'Line1 Details',
            'line2' => 'Line2 Details',
            'line3' => 'Line3 Details',
        ],
        'consignee' => [
            'name' => 'Steve',
            'email' => 'email@users.companies',
            'phone'      => '+123456789982',
            'cell_phone' => '+321654987789',
            'country_code' => 'US',
            'city' => 'New York',
            'zip_code' => 32160,
            'line1' => 'Line1 Details',
            'line2' => 'Line2 Details',
            'line3' => 'Line3 Details',
        ],
        'shipping_date_time' => time() + 50000,
        'due_date' => time() + 60000,
        'comments' => 'No Comment',
        'pickup_location' => 'at reception',
        // 'pickup_guid' => $guid,
        'weight' => 1,
        'number_of_pieces' => 1,
        'description' => 'Goods Description, like Boxes of flowers',
    ]);
    if (!empty($callResponse->error))
    {
        foreach ($callResponse->errors as $errorObject) {
          handleError($errorObject->Code, $errorObject->Message);
        }
    }
    else {
      // extract your data here, for example
      // $shipmentId = $response->Shipments->ProcessedShipment->ID;
      // $labelUrl = $response->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
    }

}
