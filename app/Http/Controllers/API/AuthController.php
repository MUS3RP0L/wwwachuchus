<?php

namespace Muserpol\Http\Controllers\API;

use Muserpol\Models\Affiliate;
use Muserpol\Models\AffiliateDevice;
use Muserpol\Http\Requests\API\AuthForm;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Muserpol\Http\Controllers\Controller;

class AuthController extends Controller
{
    private function getToken($device_id) {
        do{
            $token = Hash::make($device_id);
        } while(AffiliateDevice::where('api_token', $token)->exists());
        return $token;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json([
            'error' => false,
            'message' => 'Usuario actual',
            'data' => [
                'user' => [
                    'id' => $request->affiliate->id,
                    'name' => $request->affiliate->fullName(),
                    'degree' => $request->affiliate->degree->name,
                    'enrolled' => $request->affiliate->device->enrolled
                ],
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AuthForm $request)
    {
        $affiliate = Affiliate::whereIdentityCard($request->identity_card)->first();
        if (!$affiliate->device) {
            $token = $this->getToken($request->device_id);
            $affiliate->device()->create([
                'api_token' => $token,
                'device_id' => $request->device_id,
            ]);
            return response()->json([
                'error' => false,
                'message' => 'Usuario autenticado',
                'data' => [
                    'api_token' => $token,
                    'user' => [
                        'id' => $affiliate->id,
                        'name' => $affiliate->fullName(),
                        'degree' => $affiliate->degree->name,
                        'enrolled' => $affiliate->device->enrolled
                    ],
                ]
            ], 200);
        } else {
            if ($request->device_id == $affiliate->device->device_id && !$affiliate->device->api_token) {
                $token = $this->getToken($request->device_id);
                $affiliate->device()->update([
                    'api_token' => $token,
                ]);
                return response()->json([
                    'error' => false,
                    'message' => 'Usuario autenticado',
                    'data' => [
                        'api_token' => $token,
                        'user' => [
                            'id' => $affiliate->id,
                            'name' => $affiliate->fullName(),
                            'degree' => $affiliate->degree->name,
                            'enrolled' => $affiliate->device->enrolled
                        ],
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Dispositivo inválido',
                    'data' => []
                ], 403);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Muserpol\Models\AffiliateDevice  $affiliateDevice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->affiliate->device()->update(['api_token' => null]);
        return response()->json([
            'error' => false,
            'message' => 'Sesión terminada',
            'data' => []
        ], 200);
    }
}