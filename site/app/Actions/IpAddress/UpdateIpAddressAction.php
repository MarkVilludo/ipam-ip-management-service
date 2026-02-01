<?php

namespace App\Actions\IpAddress;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateIpAddressAction
{
    public function execute(int $id, array $data, int $userId, string $userEmail, string $userRole, Request $request): array
    {
        $ipAddress = IpAddress::find($id);

        if (!$ipAddress) {
            return [
                'success' => false,
                'message' => 'IP address not found',
                'status' => 404,
            ];
        }

        // Check permissions: users can only update their own IPs, super-admins can update any
        if ($userRole !== 'super_admin' && $ipAddress->created_by !== $userId) {
            return [
                'success' => false,
                'message' => 'You do not have permission to update this IP address',
                'status' => 403,
            ];
        }

        $validator = Validator::make($data, [
            'label' => 'required|string|max:255',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        // Set the causer for activity log
        $user = User::find($userId);
        activity()->causedBy($user);

        $ipAddress->update([
            'label' => $data['label'],
            'comment' => $data['comment'] ?? null,
        ]);

        // Activity is automatically logged by Spatie ActivityLog via LogsActivity trait

        return [
            'success' => true,
            'message' => 'IP address updated successfully',
            'data' => $ipAddress->load('creator'),
            'status' => 200,
        ];
    }
}
