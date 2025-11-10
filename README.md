# DriveXpert

DriveXpert is a PHP-based vehicle rental platform that delivers a modern customer journey and an operations-ready admin back office. Customers can browse cars, configure rentals, and manage bookings, while administrators supervise fleet availability, approvals, fines, and financial reporting.

## Features

- **Customer Portal**: Browsing, filtering, and booking vehicles with transparent pricing and optional add-ons.
- **Checkout Workflow**: Guided rental flow with validation, booking summaries, and secure authentication.
- **Invoice Generation**: GD-powered renderer in `Admin/generate_invoice_image.php` produces branded A4 invoices ready for download.
- **Admin Dashboard**: Fleet management, rental confirmation/completion, fine handling, and performance reporting tools.
- **Dynamic Modals**: Detailed booking popups enhance management workflows without leaving context.
- **Branding Assets**: Centralized styling, reusable favicon/logo (`Assets/Images/DriveXpert.png`), and modular CSS/JS assets.

## Technology Stack

- PHP 8.x with MySQLi
- MySQL / MariaDB database
- HTML5, CSS3, vanilla JavaScript, and Font Awesome
- GD extension for invoice image generation

## Project Structure

```
DriveXpert/
├─ Admin/                # Back-office pages, helpers, invoice generator
├─ Assets/               # Shared CSS, JS, and image assets
├─ Client/               # Authenticated customer portal pages
├─ *.php                 # Public entry points (marketing, auth, landing pages)
└─ README.md
```

## Getting Started

1. **Prerequisites**

   - PHP 8.1+ with the GD and MySQLi extensions enabled
   - MySQL or MariaDB server
   - Web server (Apache via XAMPP/WAMP, or built-in PHP server)

2. **Configure the Application**

   - Copy the project into your web root (e.g., `htdocs/DriveXpert`).
   - Create a database and import the schema/data dump you maintain for DriveXpert.
   - Update database credentials and paths in `config.php`.

3. **Serve the Site**

   - Start Apache/MySQL (for XAMPP) or your chosen stack.
   - Navigate to `http://localhost/DriveXpert/index.php` for the marketing site.
   - Use `auth.php` to sign in; customer dashboard lives under `Client/Home.php`, admin tools under `Admin/Admin_dashboard.php`.

4. **Assets & Branding**
   - The favicon/logo is stored at `Assets/Images/DriveXpert.png` and is referenced across all pages.
   - Additional uploads (e.g., car images) are saved to `Assets/Images/uploads/`.

## Development Tips

- Keep CSS/JS changes in `Assets/` to maintain consistency across both portals.
- When extending invoice layouts, ensure fonts are available alongside `Admin/Arial.ttf` or adjust font paths accordingly.
- Use prepared statements (pattern shown throughout `Admin/` and `Client/` modules) for any new database interactions.
- Run through both customer and admin journeys after significant changes to confirm booking, invoice, and modal flows remain intact.

## Contributing

1. Fork the repository and create a feature branch.
2. Make your changes with clear, descriptive commits.
3. Test both customer and admin experiences.
4. Submit a pull request detailing your updates.

## License

This project is proprietary to the DriveXpert team. All rights reserved unless a license file is added in the future.

Admin Side Auth: abc@gmail.com - 1234
Client side Auth: abcd@gmail.com - 1234
