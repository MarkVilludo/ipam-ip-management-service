# RMS - Backend

This project is an API built using Laravel. Below are instructions to set up the project locally using various development environments.

## Prerequisites

- PHP 8.2
- Composer
- Database (MySQL or MariaDB)
- Docker and Docker Compose

---

## Local Setup

You have multiple options for setting up the project locally. Choose the one that suits your environment best.

### Option 1: Docker (recommended)

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

### Option 2: Laragon

### Option 3: Herd
