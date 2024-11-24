<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    public function address($user_id){
        $query = "select addresses.id, addresses.set_default, addresses.address,addresses.phone,addresses.postal_code,cities.name as city,
        countries.name as country,states.name as state, users.name as username from addresses
        inner join countries on countries.id = addresses.country_id
        inner join states on states.id = addresses.state_id
        inner join cities on cities.id = addresses.city_id
        inner join users on users.id = addresses.user_id
        where addresses.user_id = " . $user_id;
        return  DB::select($query);
    }
    public function addAddress(AddressRequest $request)
    {
        try {
            if (Auth::check()) {
                $data = $request->all();
                $data['user_id'] = Auth::id();
                // dd($data);
                $address = Address::create($data);
                if ($address) {
                    return response()->json([
                        'status' => true,
                        'message' => 'address added successfully',
                        'address' => $this->address(Auth::id())
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'failed to add address'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        }catch (\Throwable $th) {
            Log::error("error message: ". $th->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getAddress()
    {
        try {
            if (Auth::user()) {
                $address = $this->address(Auth::id());
                if ($address) {
                    return response()->json([
                        'status' => true,
                        'message' => 'the address is found',
                        'address' => $address
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'the address is not found'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error("error message: ". $th->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function updateAddress($id, AddressRequest $request)
    {
        if (!Auth::user()) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
    
        try {
            $data = $request->all();
            $addressUpdated = Address::where('id', $id)->update($data);
    
            if ($addressUpdated) {
                return response()->json([
                    'status' => true,
                    'message' => 'The address has been updated',
                    'address' => $this->address(Auth::id())
                ], 200);
            }
    
            return response()->json([
                'status' => false,
                'message' => 'The address was not updated'
            ], 500);
            
        } catch (\Throwable $th) {
            Log::error("Error message: " . $th->getMessage() . ", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    // delete address
    public function deleteAddress($id)
    {
        try {
            if (Auth::user()) {
                $address = Address::where('id', $id)->where('set_default', false)->delete();
                if ($address) {
                    return response()->json([
                        'status' => true,
                        'message' => 'the address is deleted',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'the address can not deleted because it is a default address'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error("error message: ". $th->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }       


    // get set default address
    public function getDefaultAddress()
    {
        try {
            if (Auth::check()) {
                $address = Address::where('user_id', Auth::id())->where('set_default', true)->first();
                if ($address) {
                    foreach ($this->address(Auth::id()) as $address) {

                        if ($address->set_default == true) {
                            return $address;
                        }
                    }
                    
                    return response()->json([
                        'status' => true,
                        'message' => 'the address is a default',
                        'address' => $address
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'the address is not a default'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error("error message: ". $th->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //set address as default
    public function setDefaultAddress($id)
    {
        try {
            if (Auth::user()) {
                $address = Address::where('id', $id)->update([
                    'set_default' => 1,
                ]);
                if ($address) {
                    return response()->json([
                        'status' => true,
                        'message' => 'the address is set as default',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'the address is not set as default'
                    ], 500);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
        } catch (\Throwable $th) {
            Log::error("error message: ". $th->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getCountries()
    {
        try {
            $countries = Country::select('id', 'name', 'code')->get();
            if ($countries->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No countries found'
                ], 404);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Countries found',
                    'countries' => $countries
                ], 200);
            }
        }catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStates(Request $request)
    {
        try {
            $id = $request->country_id;
            $states = State::where('country_id', $id)->select('id', 'name')->get();
            if ($states->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No states found'
                ], 404);
            } else {
                return response()->json([
                    'status' => true,
                    'country' => Country::where('id', $id)->first()->name,
                    'message' => 'States found',
                    'states' => $states
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCities(Request $request)
    {
        try {
            $id = $request->state_id;
            $cities = City::where('state_id', $id)->select('id', 'name')->get();
            if ($cities->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No cities found'
                ], 404);
            } else {
                return response()->json([
                    'status' => true,
                    'state' => State::where('id', $id)->first()->name,
                    'message' => 'Cities found',
                    'cities' => $cities
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error("error message: ". $e->getMessage() .", In Class: " . __CLASS__ . ", Function: " . __FUNCTION__);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
