<?php

namespace App\Actions\IpAddress;

use App\Models\IpAddress;

class ShowIpAddressAction
{
    public function execute(int $id): array
    {
        $ipAddress = IpAddress::with('creator')->find($id);

        if (!$ipAddress) {
            return [
                'success' => false,
                'message' => 'IP address not found',
                'status' => 404,
            ];
        }

        return [
            'success' => true,
            'data' => $ipAddress,
            'status' => 200,
        ];
    }
}
