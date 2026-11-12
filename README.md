# Resource Booking System
 
One shared room inventory, sold through several channels (direct and OTA-style API), without double booking.
 
Built with PHP, Laravel and MySQL. Deployed on AWS EC2.
 
**Live demo:** http://3.107.54.143 (AWS free-plan server, plain HTTP, may be offline later)
Viewing is open. Booking, cancelling and stopping sales need the admin login.
 
![Availability grid]("C:\Users\naksh\OneDrive\Pictures\Screenshots\Screenshot 2026-11-12 175105.png")
 
## The problem
 
A hotel sells the same rooms on several booking sites. When one room is left and two requests arrive at the same moment, both can read "1 left", both pass the check, and the room is sold twice.
 
This project keeps one inventory in MySQL. Every booking goes through one service that checks and updates availability inside a locked transaction, so the second request waits, sees the new count, and is rejected.
 
## Features
 
- **Availability grid.** Room types down the side, 14 days across. Shade shows how full a night is. Hatched means sold out, crossed means sales stopped. Click a cell to book.
- **Locked-transaction booking.** Availability is read with `SELECT ... FOR UPDATE`, then updated, then the booking is written, all in one transaction.
- **Channel API.** A separate channel (for example `sim-ota`) books through `POST /api/channel/bookings` with an API key. Sending the same `external_ref` twice returns the original booking and takes no extra rooms.
- **Cancel booking.** Returns the rooms to inventory. Cancelling twice is safe.
- **Stop sales.** Close a room type for a date range, and reopen it. Closed nights cannot be booked, from the page or the API.
- **Rule-based price suggestion.** The suggested rate rises as a night fills and on Friday and Saturday. It is shown in the grid readout and in the booking panel.
- **Stats from plain SQL.** Rooms free tonight, confirmed bookings, 30-day occupancy, bookings by channel, latest bookings.
- **Admin login.** One admin password from `.env`. Visitors can view, only the admin can change data.
- **Booking log.** Every status change is written to `booking_logs`.
## Screenshots
 
| Sales stopped on a room type | Second booking rejected |
|---|---|
| ![Stopped sales]("C:\Users\naksh\OneDrive\Pictures\Screenshots\Screenshot 2026-11-12 175856.png") | ![Sold out error]("C:\Users\naksh\OneDrive\Pictures\Screenshots\Screenshot 2026-11-12 180215.png") |
 
## How double booking is prevented
 
Each room type has one `inventory` row per night with `total` and `booked`. A stay of N nights touches N rows (check-out day is not booked).
 
`BookingService::book()` runs this in one transaction:
 
1. Lock the inventory rows for the stay, in date order:
```sql
   SELECT id, date, total, booked, closed FROM inventory
   WHERE room_type_id = ? AND date >= ? AND date < ?
   ORDER BY date FOR UPDATE;
```
2. If the channel sent an `external_ref` already used, return that booking.
3. Reject if any night is closed or `booked + units > total`.
4. `UPDATE inventory SET booked = booked + ?` for those nights.
5. Insert the booking and a `booking_logs` row.
Rows are always locked in date order, and cancel locks inventory before the booking row, so booking and cancelling cannot deadlock each other. A unique index on `(channel_id, external_ref)` backs up the duplicate check.
 
## Race test
 
`php artisan race:test` starts 10 separate PHP processes. They wait until the same moment, then all try to book the one remaining room.
 
```bash
php artisan race:test unsafe   # same logic without the lock
php artisan race:test safe     # the real BookingService
php artisan race:test safe --workers=5
```
 
Result:
 
| Mode | Workers | Bookings created |
|---|---|---|
| unsafe (no lock) | 10 | 10, overbooked |
| safe (`FOR UPDATE`) | 10 | 1 |
 
`unsafe` adds a short delay between the check and the write to make the gap easy to hit. The `safe` result was also run on the AWS server.
 
![Race test]("C:\Users\naksh\OneDrive\Pictures\Screenshots\Screenshot 2026-11-12 175042.png")
 
## Channel API
 
`POST /api/channel/bookings`
 
Headers: `X-Api-Key: <channel key>`, `Accept: application/json`, `Content-Type: application/json`
 
```json
{
  "external_ref": "OTA-1001",
  "room_type_id": 1,
  "guest_name": "Jane",
  "check_in": "2026-11-20",
  "check_out": "2026-11-22",
  "units": 1
}
```
 
| Status | Meaning |
|---|---|
| 201 | Booking created. `{"id": 3, "duplicate": false}` |
| 200 | Same `external_ref` seen before. Original booking returned, `"duplicate": true` |
| 401 | Missing or wrong API key, or channel inactive |
| 409 | Sold out, or sales stopped, on a requested night |
| 422 | Validation error |
 
The seeded demo channel `sim-ota` uses the key `test-key-123`. This is a demo key for local use only.
 
## Price suggestion
 
```
rate = base_rate x occupancy tier x weekend factor, rounded to the nearest 100
```
 
| Booked share of the night | Factor |
|---|---|
| 90% or more | 1.30 |
| 70% to 89% | 1.15 |
| 40% to 69% | 1.00 |
| under 40% | 0.90 |
 
Friday and Saturday nights get another x1.10. Base rates in the seed data: Single 8,000, Double 12,000, Suite 25,000. It is a suggestion shown to the user. It does not change what is charged. The rule lives in one SQL expression in `app/Services/PricingService.php`.
 
## Tech stack
 
| Layer | Tech |
|---|---|
| Backend | PHP 8.5, Laravel |
| Database | MySQL (MariaDB under XAMPP locally), raw SQL for booking, locking and stats |
| Frontend | Blade, HTML, CSS, jQuery AJAX |
| Auth | Session flag set by a single admin password, middleware, CSRF, throttled login |
| Server | AWS EC2 (Ubuntu), Nginx, PHP-FPM |
| Tools | Git, GitHub, Composer |
 
## Data model
 
| Table | Purpose |
|---|---|
| `room_types` | name, `default_units`, `base_rate` |
| `channels` | name, `api_key`, `is_active` |
| `inventory` | one row per room type per night: `total`, `booked`, `closed`. Unique on `(room_type_id, date)` |
| `bookings` | channel, room type, guest, check-in, check-out, units, status, `external_ref`. Unique on `(channel_id, external_ref)` |
| `booking_logs` | booking id, old status, new status |
 
Laravel's default `users`, `sessions`, `cache` and `jobs` tables are also created by the stock migrations.
 
## Routes
 
| Method | Path | Access |
|---|---|---|
| GET | `/`, `/availability`, `/quote`, `/summary` | public |
| GET / POST | `/login`, `/logout` | public (login throttled to 5 per minute) |
| POST | `/bookings` | admin |
| POST | `/bookings/{id}/cancel` | admin |
| POST | `/stop-sales` | admin |
| POST | `/api/channel/bookings` | API key |
 
## Key files
 
```
app/Services/BookingService.php      booking and cancel transactions
app/Services/PricingService.php      price rule as SQL
app/Http/Controllers/CalendarController.php
app/Http/Controllers/ChannelBookingController.php
app/Http/Middleware/AdminOnly.php
app/Console/Commands/RaceTest.php    concurrency test
database/migrations/                 schema
database/seeders/DatabaseSeeder.php  2 channels, 3 room types, 60 days of inventory
resources/views/app.blade.php        the one-page UI
```
 
## Run it locally
 
Needs PHP 8.2 or newer, Composer and MySQL (XAMPP works).
 
```bash
git clone https://github.com/NAKSHATHRA305/resource-booking-system.git
cd resource-booking-system
composer install
cp .env.example .env
php artisan key:generate
```
 
Create a database called `booking`, then set these in `.env`:
 
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking
DB_USERNAME=root
DB_PASSWORD=
ADMIN_PASSWORD=choose-a-password
```
 
```bash
php artisan migrate --seed
php artisan serve
```
 
Open http://127.0.0.1:8000. Log in at `/login` with the admin password to book, cancel and stop sales.
 
## Deployment (AWS EC2)
 
Ubuntu server, Nginx, PHP-FPM and MySQL on one small instance on the AWS free plan.
 
1. Launch an Ubuntu instance. Open ports 22 and 80 in the security group. Add 2 GB of swap on a 1 GB instance.
2. Install: `nginx mysql-server git unzip composer php-fpm php-cli php-mysql php-mbstring php-xml php-curl php-zip php-bcmath` (the versioned `php8.5-` packages on this Ubuntu).
3. Create a MySQL database and a user that only has access to it.
4. Clone the repo to `/var/www/booking`, run `composer install --no-dev --optimize-autoloader`, copy `.env.example` to `.env`, run `php artisan key:generate`, set the database and `ADMIN_PASSWORD` values, then `php artisan migrate --force --seed`.
5. Give `storage` and `bootstrap/cache` to `www-data`.
6. Nginx site:
```nginx
   server {
       listen 80 default_server;
       server_name _;
       root /var/www/booking/public;
       index index.php;
       location / { try_files $uri $uri/ /index.php?$query_string; }
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.5-fpm.sock;
       }
       location ~ /\.(?!well-known).* { deny all; }
   }
```
 
Update routine on the server:
 
```bash
cd /var/www/booking
sudo chown -R ubuntu:ubuntu storage bootstrap/cache
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan view:clear
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```
 
Secrets (`.env`, database password, admin password) stay on the server and are not in the repository.
 
## Known limitations
 
- Plain HTTP. No domain or certificate yet, so browsers show "Not secure".
- Tested on laptop browsers. I could not open the live link from my phone's network. The layout has a phone breakpoint, checked in Chrome's device mode.
- The page refreshes after its own actions. A booking made through the API shows up after a reload.
- One shared admin password, not per-user accounts.
- The price suggestion is rule-based and informational only.
- No automated test suite yet. The race test is the concurrency check.
## What I would do next
 
HTTPS with a domain, per-user accounts, PHPUnit tests for the booking service, and a simple refresh so API bookings appear without a reload.
 
 
