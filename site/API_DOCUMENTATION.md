# IP Management Service API Documentation

Base URL: `http://localhost:8000/api` (via Gateway) or `http://localhost:9003/api` (direct)

All endpoints return JSON responses with the following structure:

-   Success: `{ "success": true, "data": {...}, "message": "..." }`
-   Error: `{ "success": false, "errors": {...} }` or `{ "success": false, "message": "..." }`

**Authentication:** Most endpoints require JWT authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_jwt_token}
```

---

## Table of Contents

1. [IP Address Management](#ip-address-management)

    - [List All IP Addresses](#1-list-all-ip-addresses)
    - [Get Single IP Address](#2-get-single-ip-address)
    - [Create IP Address](#3-create-ip-address)
    - [Update IP Address](#4-update-ip-address)
    - [Delete IP Address](#5-delete-ip-address)

2. [Audit Log Management](#audit-log-management)

    - [List Audit Logs](#6-list-audit-logs)
    - [Get Audit Dashboard](#7-get-audit-dashboard)
    - [Get IP Address Logs](#8-get-ip-address-logs)
    - [Get User Logs](#9-get-user-logs)
    - [Get Session Logs](#10-get-session-logs)

3. [Internal Endpoints](#internal-endpoints)
    - [Log Event](#11-log-event)

---

## IP Address Management

### 1. List All IP Addresses

Get a list of all IP addresses. All authenticated users can view all IP addresses.

**Endpoint:** `GET /api/ip-addresses`

**Authentication:** Required (Bearer token)

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**cURL Example:**

```bash
curl -X GET http://localhost:8000/api/ip-addresses \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "ip_address": "192.168.1.1",
            "label": "Main Router",
            "comment": "Primary network gateway",
            "created_by": 1,
            "created_at": "2026-01-24T10:00:00.000000Z",
            "updated_at": "2026-01-24T10:00:00.000000Z",
            "creator": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "role": "user"
            }
        },
        {
            "id": 2,
            "ip_address": "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
            "label": "IPv6 Server",
            "comment": null,
            "created_by": 2,
            "created_at": "2026-01-24T11:00:00.000000Z",
            "updated_at": "2026-01-24T11:00:00.000000Z",
            "creator": {
                "id": 2,
                "name": "Jane Smith",
                "email": "jane@example.com",
                "role": "user"
            }
        }
    ]
}
```

---

### 2. Get Single IP Address

Get details of a specific IP address by ID.

**Endpoint:** `GET /api/ip-addresses/{id}`

**Authentication:** Required (Bearer token)

**URL Parameters:**

-   `id` (integer, required) - The ID of the IP address

**cURL Example:**

```bash
curl -X GET http://localhost:8000/api/ip-addresses/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "ip_address": "192.168.1.1",
        "label": "Main Router",
        "comment": "Primary network gateway",
        "created_by": 1,
        "created_at": "2026-01-24T10:00:00.000000Z",
        "updated_at": "2026-01-24T10:00:00.000000Z",
        "creator": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user"
        }
    }
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "IP address not found"
}
```

---

### 3. Create IP Address

Create a new IP address record. Supports both IPv4 and IPv6 addresses.

**Endpoint:** `POST /api/ip-addresses`

**Authentication:** Required (Bearer token)

**Request Body:**

```json
{
    "ip_address": "192.168.1.100",
    "label": "Web Server",
    "comment": "Production web server"
}
```

**Field Descriptions:**

-   `ip_address` (string, required) - Valid IPv4 or IPv6 address (max 45 characters)
-   `label` (string, required) - Descriptive label for the IP address (max 255 characters)
-   `comment` (string, optional) - Additional notes or comments

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/ip-addresses \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "192.168.1.100",
    "label": "Web Server",
    "comment": "Production web server"
  }'
```

**IPv6 Example:**

```bash
curl -X POST http://localhost:8000/api/ip-addresses \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
    "label": "IPv6 Database Server",
    "comment": "Main database server with IPv6"
  }'
```

**Success Response (201):**

```json
{
    "success": true,
    "message": "IP address created successfully",
    "data": {
        "id": 3,
        "ip_address": "192.168.1.100",
        "label": "Web Server",
        "comment": "Production web server",
        "created_by": 1,
        "created_at": "2026-01-24T12:00:00.000000Z",
        "updated_at": "2026-01-24T12:00:00.000000Z",
        "creator": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user"
        }
    }
}
```

**Error Response (422) - Validation Error:**

```json
{
    "success": false,
    "errors": {
        "ip_address": [
            "The ip_address has already been taken.",
            "The ip_address must be a valid IPv4 or IPv6 address."
        ],
        "label": ["The label field is required."]
    }
}
```

---

### 4. Update IP Address

Update the label and/or comment of an existing IP address.

**Permissions:**

-   Regular users can only update IP addresses they created
-   Super-admins can update any IP address

**Endpoint:** `PUT /api/ip-addresses/{id}`

**Authentication:** Required (Bearer token)

**URL Parameters:**

-   `id` (integer, required) - The ID of the IP address to update

**Request Body:**

```json
{
    "label": "Updated Label",
    "comment": "Updated comment"
}
```

**Field Descriptions:**

-   `label` (string, required) - New label for the IP address (max 255 characters)
-   `comment` (string, optional) - New comment (can be null)

**cURL Example:**

```bash
curl -X PUT http://localhost:8000/api/ip-addresses/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "label": "Updated Router Label",
    "comment": "Updated comment with new information"
  }'
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "IP address updated successfully",
    "data": {
        "id": 1,
        "ip_address": "192.168.1.1",
        "label": "Updated Router Label",
        "comment": "Updated comment with new information",
        "created_by": 1,
        "created_at": "2026-01-24T10:00:00.000000Z",
        "updated_at": "2026-01-24T13:00:00.000000Z",
        "creator": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "user"
        }
    }
}
```

**Error Response (403) - Permission Denied:**

```json
{
    "success": false,
    "message": "You do not have permission to update this IP address"
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "IP address not found"
}
```

---

### 5. Delete IP Address

Delete an IP address. **Only super-admins can delete IP addresses.**

**Endpoint:** `DELETE /api/ip-addresses/{id}`

**Authentication:** Required (Bearer token)

**URL Parameters:**

-   `id` (integer, required) - The ID of the IP address to delete

**cURL Example:**

```bash
curl -X DELETE http://localhost:8000/api/ip-addresses/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "IP address deleted successfully"
}
```

**Error Response (403) - Permission Denied:**

```json
{
    "success": false,
    "message": "You do not have permission to delete IP addresses"
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "IP address not found"
}
```

---

## Audit Log Management

All audit log endpoints are **super-admin only**. Regular users will receive a 403 Forbidden response.

### 6. List Audit Logs

Get a paginated list of audit logs with optional filtering.

**Endpoint:** `GET /api/audit-logs`

**Authentication:** Required (Bearer token - Super Admin only)

**Query Parameters (all optional):**

-   `user_id` (integer) - Filter by user ID
-   `entity_type` (string) - Filter by entity type (e.g., "App\Models\IpAddress")
-   `entity_id` (integer) - Filter by entity ID
-   `action` (string) - Filter by event/action (e.g., "created", "updated", "deleted")
-   `session_id` (string) - Filter by session ID
-   `log_name` (string) - Filter by log name
-   `start_date` (datetime) - Filter logs from this date
-   `end_date` (datetime) - Filter logs until this date
-   `per_page` (integer) - Number of results per page (default: 50)

**cURL Example:**

```bash
curl -X GET "http://localhost:8000/api/audit-logs?per_page=20&entity_type=App\Models\IpAddress" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "log_name": "default",
                "description": "IP address 192.168.1.1 created with label 'Main Router'",
                "subject_type": "App\\Models\\IpAddress",
                "subject_id": 1,
                "event": "created",
                "causer_type": "App\\Models\\User",
                "causer_id": 1,
                "properties": {
                    "attributes": {
                        "ip_address": "192.168.1.1",
                        "label": "Main Router",
                        "comment": null
                    }
                },
                "session_id": "550e8400-e29b-41d4-a716-446655440000",
                "user_email": "john@example.com",
                "ip_address": "127.0.0.1",
                "user_agent": "Mozilla/5.0...",
                "created_at": "2026-01-24T10:00:00.000000Z",
                "updated_at": "2026-01-24T10:00:00.000000Z"
            }
        ],
        "per_page": 20,
        "total": 100
    }
}
```

---

### 7. Get Audit Dashboard

Get statistics and dashboard data for audit logs.

**Endpoint:** `GET /api/audit-logs/dashboard`

**Authentication:** Required (Bearer token - Super Admin only)

**cURL Example:**

```bash
curl -X GET http://localhost:8000/api/audit-logs/dashboard \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": {
        "total_logs": 150,
        "logs_by_event": {
            "created": 50,
            "updated": 70,
            "deleted": 30
        },
        "logs_by_subject_type": {
            "App\\Models\\IpAddress": 120,
            "App\\Models\\User": 30
        },
        "recent_logs": [
            {
                "id": 150,
                "description": "IP address 192.168.1.100 updated",
                "event": "updated",
                "created_at": "2026-01-24T15:00:00.000000Z"
            }
        ],
        "logs_by_user": [
            {
                "causer_id": 1,
                "user_email": "john@example.com",
                "count": 80
            },
            {
                "causer_id": 2,
                "user_email": "jane@example.com",
                "count": 70
            }
        ]
    }
}
```

---

### 8. Get IP Address Logs

Get all audit logs for a specific IP address.

**Endpoint:** `GET /api/audit-logs/ip-address/{ipId}`

**Authentication:** Required (Bearer token - Super Admin only)

**URL Parameters:**

-   `ipId` (integer, required) - The ID of the IP address

**cURL Example:**

```bash
curl -X GET http://localhost:8000/api/audit-logs/ip-address/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "description": "IP address 192.168.1.1 created with label 'Main Router'",
            "event": "created",
            "created_at": "2026-01-24T10:00:00.000000Z",
            "causer": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        },
        {
            "id": 5,
            "description": "IP address 192.168.1.1 updated",
            "event": "updated",
            "created_at": "2026-01-24T13:00:00.000000Z",
            "causer": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        }
    ]
}
```

---

### 9. Get User Logs

Get all audit logs for a specific user.

**Endpoint:** `GET /api/audit-logs/user/{userId}`

**Authentication:** Required (Bearer token - Super Admin only)

**URL Parameters:**

-   `userId` (integer, required) - The ID of the user

**cURL Example:**

```bash
curl -X GET http://localhost:8000/api/audit-logs/user/1 \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "description": "IP address 192.168.1.1 created with label 'Main Router'",
            "event": "created",
            "subject_type": "App\\Models\\IpAddress",
            "subject_id": 1,
            "created_at": "2026-01-24T10:00:00.000000Z"
        }
    ]
}
```

---

### 10. Get Session Logs

Get all audit logs for a specific session.

**Endpoint:** `GET /api/audit-logs/session/{sessionId}`

**Authentication:** Required (Bearer token - Super Admin only)

**URL Parameters:**

-   `sessionId` (string, required) - The session ID (UUID)

**cURL Example:**

```bash
curl -X GET "http://localhost:8000/api/audit-logs/session/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json"
```

**Success Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "description": "IP address 192.168.1.1 created with label 'Main Router'",
            "event": "created",
            "session_id": "550e8400-e29b-41d4-a716-446655440000",
            "created_at": "2026-01-24T10:00:00.000000Z"
        }
    ]
}
```

---

## Internal Endpoints

### 11. Log Event

Internal endpoint for service-to-service communication to log events (e.g., login/logout from auth service).

**Endpoint:** `POST /api/internal/audit-log`

**Authentication:** Not required (internal service-to-service endpoint)

**Request Body:**

```json
{
    "action": "login",
    "user_id": 1,
    "user_email": "john@example.com",
    "description": "User john@example.com logged in",
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "role": "user",
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0..."
}
```

**Field Descriptions:**

-   `action` (string, required) - The action/event name (e.g., "login", "logout")
-   `user_id` (integer, required) - The ID of the user
-   `user_email` (string, required) - The email of the user
-   `description` (string, optional) - Human-readable description
-   `session_id` (string, optional) - Session UUID
-   `name` (string, optional) - User display name (used to sync user in IP service)
-   `role` (string, optional) - User role (used to sync user in IP service)
-   `ip_address` (string, optional) - Client IP address
-   `user_agent` (string, optional) - Client user agent

If the user does not exist in the IP management service, they are created/updated from the request (same as SyncUserMiddleware) so audit logging always succeeds.

**cURL Example:**

```bash
curl -X POST http://localhost:8000/api/internal/audit-log \
  -H "Content-Type: application/json" \
  -d '{
    "action": "login",
    "user_id": 1,
    "user_email": "john@example.com",
    "description": "User john@example.com logged in",
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "role": "user"
  }'
```

**Success Response (200):**

```json
{
    "success": true,
    "message": "Event logged successfully",
    "data": {
        "activity_id": 123,
        "session_id": "550e8400-e29b-41d4-a716-446655440000"
    }
}
```

**Error Response (422):**

```json
{
    "success": false,
    "errors": {
        "user_id": ["The user id field is required."],
        "user_email": ["The user email must be a valid email address."]
    }
}
```

---

## Complete Workflow Example

### Step 1: Get Authentication Token

First, authenticate with the Auth Service to get a JWT token:

```bash
# Register a new user (or login if already registered)
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'

# Response includes token:
# {
#   "success": true,
#   "data": {
#     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
#     ...
#   }
# }
```

### Step 2: Create an IP Address

```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://localhost:8000/api/ip-addresses \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "ip_address": "192.168.1.100",
    "label": "Web Server",
    "comment": "Production server"
  }'
```

### Step 3: List All IP Addresses

```bash
curl -X GET http://localhost:8000/api/ip-addresses \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

### Step 4: Update IP Address

```bash
curl -X PUT http://localhost:8000/api/ip-addresses/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "label": "Updated Label",
    "comment": "Updated comment"
  }'
```

### Step 5: View Audit Logs (Super Admin Only)

```bash
curl -X GET http://localhost:8000/api/audit-logs \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

---

## Error Codes

| Status Code | Description                             |
| ----------- | --------------------------------------- |
| 200         | Success                                 |
| 201         | Created (Resource created successfully) |
| 401         | Unauthorized (Invalid or missing token) |
| 403         | Forbidden (Insufficient permissions)    |
| 404         | Not Found (Resource not found)          |
| 422         | Validation Error (Invalid input data)   |
| 500         | Internal Server Error                   |

---

## Notes

1. **IP Address Validation**: The system accepts both IPv4 (e.g., `192.168.1.1`) and IPv6 (e.g., `2001:0db8:85a3:0000:0000:8a2e:0370:7334`) addresses.

2. **Permissions**:

    - All authenticated users can view all IP addresses
    - Regular users can only update IP addresses they created
    - Only super-admins can delete IP addresses
    - Only super-admins can access audit log endpoints

3. **Audit Logging**: All IP address operations (create, update, delete) are automatically logged with:

    - User information (who performed the action)
    - Timestamp
    - IP address and user agent
    - Session ID
    - Old and new values (for updates)

4. **Token Expiration**: JWT tokens expire after 60 minutes by default. Use the refresh endpoint from the Auth Service to extend the session.

5. **Gateway vs Direct Access**:
    - Via Gateway: `http://localhost:8000/api/*`
    - Direct to IP Management Service: `http://localhost:9003/api/*`
