<?php

namespace App\Http\Controllers;

use App\Actions\IpAddress\CreateIpAddressAction;
use App\Actions\IpAddress\UpdateIpAddressAction;
use App\Actions\IpAddress\DeleteIpAddressAction;
use App\Actions\IpAddress\ListIpAddressesAction;
use App\Actions\IpAddress\ShowIpAddressAction;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class IpAddressController extends Controller
{
    public function __construct(
        private CreateIpAddressAction $createIpAddressAction,
        private UpdateIpAddressAction $updateIpAddressAction,
        private DeleteIpAddressAction $deleteIpAddressAction,
        private ListIpAddressesAction $listIpAddressesAction,
        private ShowIpAddressAction $showIpAddressAction,
    ) {}

    /**
     * Get all IP addresses
     */
    public function index()
    {
        $result = $this->listIpAddressesAction->execute();
        return response()->json(
            collect($result)->except('status')->toArray(),
            $result['status']
        );
    }

    /**
     * Get a single IP address
     */
    public function show($id)
    {
        $result = $this->showIpAddressAction->execute($id);
        return response()->json(
            collect($result)->except('status')->toArray(),
            $result['status']
        );
    }

    /**
     * Create a new IP address
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $result = $this->createIpAddressAction->execute(
            $request->all(),
            $user->id,
            $user->email,
            $request
        );
        return response()->json(
            collect($result)->except('status')->toArray(),
            $result['status']
        );
    }

    /**
     * Update an IP address
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        // Get user role from JWT token
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();
        $userRole = $payload['role'] ?? $user->role ?? 'user';

        $result = $this->updateIpAddressAction->execute(
            $id,
            $request->all(),
            $user->id,
            $user->email,
            $userRole,
            $request
        );
        return response()->json(
            collect($result)->except('status')->toArray(),
            $result['status']
        );
    }

    /**
     * Delete an IP address
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        // Get user role from JWT token
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();
        $userRole = $payload['role'] ?? $user->role ?? 'user';

        $result = $this->deleteIpAddressAction->execute(
            $id,
            $user->id,
            $user->email,
            $userRole,
            $request
        );
        return response()->json(
            collect($result)->except('status')->toArray(),
            $result['status']
        );
    }
}
