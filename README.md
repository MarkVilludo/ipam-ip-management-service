# IP Management Service (IPAM)

IP address CRUD and audit-logging microservice for the IPAM system. It stores IP records (IPv4/IPv6, label, comment), enforces role-based rules (e.g. regular users can only edit their own IPs; super-admins can edit/delete any and access the audit dashboard), and maintains a non-deletable audit log of user actions (login/logout, IP create/update/delete) and session/user tracking. It accepts JWTs issued by the auth service and syncs user data from the token.

**Purpose:** Central place for IP address data and audit history; the gateway routes `/api/ip-addresses` and `/api/audit-logs` here so the frontend can list, add, edit, delete IPs and (for super-admins) view the audit dashboard.

## Prerequisites

- PHP 8.2
- Composer
- Database (MySQL or MariaDB)
- Docker and Docker Compose

---

## Local Setup

### Docker (recommended)

1. Ensure Docker is installed on your machine.
2. Clone the repository:
   ```bash
   git clone git@github.com:MarkVilludo/ipam-ip-management-service.git
   cd ipam-ip-management-service
   ```
3. Copy the `docker\local\.env.local` to `.env` and configure it as needed
4. Append on your local hosts:
   ```text
   127.0.0.1 api-ip-management-service.local.com
   ```
5. Create local certificate (mkcert):

   ```text
   cd docker/local/nginx/mkcert

   // Note: Delete existing certificate files before creating new cert
   mkcert api-ip-management-service.local.com
   ```

6. Build and run the Docker containers:
   ```bash
   docker compose -f docker-compose.local.yml up --build -d
   ```
   Access the API at **https://api-ip-management-service.local.com:8444**. The port is required when running multiple containers (auth, IP management, gateway, etc.) so each stack uses different host ports and does not conflict.
7. Generate new app key (if needed):
   ```bash
   docker exec api-ip-management-service php artisan key:generate
   ```
8. Run the migrations and seed the database:
   ```bash
   docker exec api-ip-management-service php artisan migrate
   docker exec api-ip-management-service php artisan db:seed
   ```
9. Use postman collection to test the app.
   - TBA
