<?php

namespace App\Actions\IpAddress;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateIpAddressAction
{
    public function execute(array $data, int $userId, string $userEmail, Request $request): array
    {
        $validator = Validator::make($data, [
            'ip_address' => [
                'required',
                'string',
                'max:45',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                        $fail('The ' . $attribute . ' must be a valid IPv4 or IPv6 address.');
                    }
                },
                Rule::unique('ip_addresses', 'ip_address')
            ],
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

        $ipAddress = IpAddress::create([
            'ip_address' => $data['ip_address'],
            'label' => $data['label'],
            'comment' => $data['comment'] ?? null,
            'created_by' => $userId,
        ]);

        // Activity is automatically logged by Spatie ActivityLog via LogsActivity trait

        return [
            'success' => true,
            'message' => 'IP address created successfully',
            'data' => $ipAddress->load('creator'),
            'status' => 201,
        ];
    }
}
