<?php

namespace App\Actions\IpAddress;

use App\Models\IpAddress;

class ListIpAddressesAction
{
    public function execute(): array
    {
        $ipAddresses = IpAddress::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'success' => true,
            'data' => $ipAddresses,
            'status' => 200,
        ];
    }
}
