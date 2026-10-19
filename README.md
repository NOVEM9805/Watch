# Timeless Sight

Responsive PHP 8.1+ / MySQL watch store with customer and administrator sections. Cash on delivery, Gmail email OTP at registration and every login, live prefix search, brand filtering, bilingual UI labels, admin-managed products and slideshow.

## Setup

Read `INSTALL_MM.md`. Import `database/schema.sql` once into MySQL. Copy `.env.example` to `.env` and configure it locally. Run `composer install` to enable PHPMailer. The production mail mode is SMTP. Set a private `ADMIN_SETUP_KEY` in `.env` and create the first admin at `auth/admin-setup.php`, then verify the Gmail code. Administrators sign in only at `auth/admin-login.php`; customers use `auth/login.php`. Admins can add more admins at Admin → Administrators; invited admins activate at `auth/admin-activate.php`. The CLI tool remains available for recovery.

## Sample content

Seeded brands: Casio, Citizen, Orient, Seiko; five model names in each. All 20 seeded watches have illustrative MMK prices and sample stock of five each. Customers may save watches in their cart immediately. Checkout is enabled for the populated sample catalog. The admin may pause checkout in Admin → Settings. All prices and stock are examples and must be reviewed before accepting real customers. Descriptions have been written for all 20 seeded models. Existing installations should import `database/upgrade_2026_09_catalog.sql` once to update only untouched placeholder descriptions, zero prices/stock and blank contact settings. The migration preserves custom values. Admin must verify each watch and update price, stock, descriptions, warranty and delivery policy before taking real orders. Watches have no photos until the admin uploads them. The Shop page initially rotates three included sample images. After product photos are uploaded, All brands rotates one watch image per brand and a selected brand rotates only its own watch images. Brands with no photos show a branded placeholder. The Home promotions and sections remain as they were.

## Security and deployment

Use HTTPS, PHP 8.1+, Apache with mod_rewrite, MySQL, PDO MySQL, mbstring, fileinfo, Composer and a writable `uploads/` directory. The app uses prepared PDO statements, escaped output, CSRF checks, rate limiting, expiring one-use email OTP, password hashing and server-side role/order checks. Review SMTP delivery and web server access control before deployment. Never publish `.env`, `storage`, SQL files or tools; Apache `.htaccess` denies them. For Nginx, configure equivalent deny rules. Do not expose the app through PHP's built-in web server as production.

The `APP_URL` path must match the installation directory. If running from `http://localhost/timeless-sight`, keep that URL. For a root-domain deployment set `APP_URL=https://your-domain.example`.
