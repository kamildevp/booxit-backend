# Booxit Backend

Booxit is a RESTful API built with the Symfony Framework, designed to provide a user-friendly platform for appointment scheduling and management. It supports reservation storage, availability management and automated email notifications.

## 🚀 Features

The API allows users to:

- **Create and manage organizations**
- **Invite and manage members** with role-based access control
- **Create and configure schedules** for organization services
- **Manage services availability**
- **Create and manage reservations** based on schedules and services

## Tech Stack
<div>
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" height="40" alt="php logo" style="margin-right:1rem"  />
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/symfony/symfony-original.svg" height="40" alt="symfony logo" style="margin-right:1rem"  />
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg" height="40" alt="postgresql logo" style="margin-right:1rem"  />
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/rabbitmq/rabbitmq-original.svg" height="40" alt="rabbitmq logo" style="margin-right:1rem"  />
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nginx/nginx-original.svg" height="40" alt="nginx logo" style="margin-right:1rem"  />
    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg" height="40" alt="docker logo" style="margin-right:1rem"  />
</div>

## 🛠 How to Run Locally (Development Mode)

### ✅ Requirements

- [Docker](https://docs.docker.com/engine/install/) installed on your system
- Mailing service provider credentials configured for application access


### 🔧 Setup Instructions

From the project root directory (where the `docker-compose.yml` file is located):

1. Create a `.env.local` file and configure your mail provider using the `MAILER_DSN` environment variable.
2. Run `docker compose up -d`.
3. Application will be available at `http://localhost:8000` (when using default `APP_PORT=8000`).
4. Visit `http://localhost:8000/api/doc` to display  API documentation.

