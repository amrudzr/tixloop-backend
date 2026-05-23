# TixLoop Backend

Laravel backend service untuk platform circular ticketing marketplace TixLoop.

## Stack
- Laravel 13
- MySQL
- Sanctum
- Queue
- SSE/WebSocket (future)

## Features
- Ticket marketplace
- Burn prevention engine
- Escrow simulation
- Waste dashboard
- Anti-scalper pricing

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```