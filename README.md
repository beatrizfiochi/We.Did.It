# We.Did.It
Projeto final - Software Developer Cesae Digital - Newsletter

# Project Name

We did it, a Web application developed for CESAE Digital, that serves as an internal newsletter, centralizing information about events, news, and testimonials from staff members and trainees.

## Tech Stack

- MySQL
- Laravel
- Inertia.js + React


## Installation

1. Clone the repository
```bash
   git clone ...
```
2. Install backend dependencies
```bash
   composer install
```
3. Install Laravel Breeze (Inertia + React ) ?????????????????????? é preciso???
```bash
   composer require laravel/breeze --dev
   php artisan breeze:install react
```
4. Install frontend dependencies
```bash
   npm install
```
5. Copy `.env.example` to `.env` and configure with database credentials
```bash
   cp .env.example .env
   php artisan key:generate
```
6. Run migrations
```bash
   php artisan migrate
```

## Running the project

- Backend: `php artisan serve`
- Frontend: `npm run dev`

## Team & Workflow

| Member | Responsibility |
|---|---|
| Beatriz Fiocchi (Team Lead) | Backend routes |
| Jéssica Amorim | Frontend layout & visual identity |
| Leida Dupret | Backend config, protected routes, UX logic |
| Luana Santos | Database configuration & migrations |


The project followed a sprint-based workflow, with each member assigned a set of tasks to complete within a one-week sprint. Merging any task into the main branch required opening a pull request, which triggered a peer review process: the rest of the team would examine the changes, either approving the PR or leaving comments to help refine the implementation. This process kept everyone informed of the project's progress across all components, given their interdependent nature.