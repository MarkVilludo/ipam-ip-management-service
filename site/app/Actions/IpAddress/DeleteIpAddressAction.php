<?php

namespace App\Actions\IpAddress;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Http\Request;

class DeleteIpAddressAction
{
    public function execute(int $id, int $userId, string $userEmail, string $userRole, Request $request): array
    {
        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return [
                'success' => false,
                'message' => 'IP address not found',
                'status' => 404,
            ];
        }

        // Only super-admins can delete IP addresses
        if ($userRole !== 'super_admin') {
            return [
                'success' => false,
                'message' => 'You do not have permission to delete IP addresses',
                'status' => 403,
            ];
        }

        // Set the causer for activity log
        $user = User::find($userId);
        activity()->causedBy($user);

        $ipAddress->delete();

        // Activity is automatically logged by Spatie ActivityLog via LogsActivity trait
        return [
            'success' => true,
            'message' => 'IP address deleted successfully',
            'status' => 200,
        ];
    }
}
