# Triangulation App

Interactive Laravel application for calculating possible coordinates of a point
from distances to three known reference points. The frontend renders a Three.js
globe, shows the reference points, draws distance circles, and marks calculated
intersection candidates.

## Stack

- Laravel 11 / PHP 8.3
- Vite
- Tailwind CSS
- Three.js
- MariaDB
- Docker Compose with PHP-FPM and Nginx

## Project Structure

```text
.
├── Docker/                 # PHP-FPM image and Nginx config
├── backend/                # Laravel application
│   ├── app/Http/Controllers/TriangulationController.php
│   ├── resources/js/       # Three.js scene and form modules
│   ├── resources/views/    # Main Blade view
│   └── tests/              # Feature tests
├── docker-compose.yml
└── .env.example            # Docker database variables
```

## Requirements

- Docker and Docker Compose

## Setup With Docker

1. Create environment files:

   ```bash
   cp .env.example .env
   cp backend/.env.example backend/.env
   ```

2. Start containers:

   ```bash
   docker compose up -d --build
   ```

3. Install backend and frontend dependencies:

   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   ```

4. Prepare Laravel:

   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```

5. Start the Vite dev server:

   ```bash
   docker compose exec app npm run dev -- --host 0.0.0.0
   ```

6. Open the application:

   ```text
   http://localhost
   ```

## API

`POST /api/triangulate`

Request body:

```json
{
  "distanceA": 120,
  "distanceB": 230,
  "distanceC": 180,
  "referenceA": { "lat": 50.110889, "lng": 8.682139 },
  "referenceB": { "lat": 39.048111, "lng": -77.472806 },
  "referenceC": { "lat": 45.8491, "lng": -119.714 }
}
```

`referenceA`, `referenceB`, and `referenceC` are optional. If they are omitted,
the backend uses the default reference points configured in
`TriangulationController`.

Successful response:

```json
{
  "success": true,
  "coordinates": [
    { "latitude": 41.123456, "longitude": -72.123456 },
    { "latitude": 40.654321, "longitude": -73.654321 }
  ]
}
```

## Development

Run tests:

```bash
docker compose exec app php artisan test
```

Build frontend assets:

```bash
docker compose exec app npm run build
```

## Notes

- Distance values are expected in kilometers.
- The calculation can return two possible intersection points.
- The root `.env` file is used by Docker Compose. `backend/.env` is used by
  Laravel inside the mounted application directory.
