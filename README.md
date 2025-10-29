## Booxit Backend
Dockerized back-end application built with Symfony Framework. Application purpose is to provide easy to use appointment planning and management platform, which handles reservation storage, free terms management, email notifications and does not require organization/service provider to be involved in reservation process.
 
It provides users the ability to: 
- create and manage organizations 
- add other users to their organizations
- create and manage schedules
- manage organization members and their privileges in regard to organization and organization schedules
- create reservations based on organization schedules and services

For detailed API endpoints description please refer to  [API documentation](docs/api-endpoints.md#API-Endpoints)


## How to run locally (development mode)

Requirements:
- [Docker](https://docs.docker.com/engine/install/) installed on your system
- mailing service provider configured for application access

While in main folder, where docker-compose.yml file is located
1. Using your mailing provider information, set ```MAILER_DSN=smtp://user:pass@smtp.example.com:port``` in ```.env``` file. 
<br>Example using gmail:
 ```MAILER_DSN=smtp://mailer@gmail.com:example_password@smtp.gmail.com:465```

2. Run `docker compose build --pull --no-cache` to build fresh images.
3. Run `docker compose up` (the logs will be displayed in the current shell).
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334).
5. Run `docker compose down --remove-orphans` to stop the Docker containers.
