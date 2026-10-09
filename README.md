<!-- path: D:\WEB CIM\Website CIM\cim-web\README.md -->

# PT Cahya Intan Medika Website

Company profile website and CMS for PT Cahya Intan Medika.

## Runtime Architecture

The application is a Laravel modular monolith.

Public website:

- Laravel Blade
- Tailwind CSS
- lightweight JavaScript only where required

CMS:

- React
- TypeScript
- Inertia
- Tailwind CSS
- shadcn-style source components

Development runtime:

- Nginx
- PHP-FPM 8.3
- Node.js 24
- MySQL 8.4
- Mailpit

Docker Compose is the local runtime source of truth.

Host PHP, Composer, Node.js, and npm are optional.

## Requirements

Required on the developer workstation:

- Git
- Docker Desktop / Docker Engine
- Docker Compose

You do not need host PHP, Composer, Node.js, MySQL, or Nginx to run the project.

## Fresh Setup

Clone or enter the repository.

Create the local environment:

```powershell
Copy-Item .env.example .env