# Library Management System

A web-based library management system built with PHP that allows students to manage books in a library. The system provides features for book management including adding, editing, deleting, and viewing books with cover images.

## Features

### User Management
- Student registration with detailed information
- Secure login system with session management
- Profile image upload functionality
- Remember me functionality

### Book Management
- Complete CRUD operations for books
- Book cover image upload
- Detailed book information including:
  - Title, Author, ISBN
  - Publication Year, Genre
  - Publisher, Edition
  - Number of Pages
  - Language
  - Description
  - Stock Quantity
  - Location
- Real-time book list updates
- Image preview functionality

## Technical Requirements

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Dependencies
- Modern web browser
- Google Fonts (Poppins)

## Installation

1. Clone the repository or download the source code

2. Create a MySQL database:
```sql
CREATE DATABASE library_db;
```

3. Import the database schema:
```bash
mysql -u your_username -p library_db < database.sql
```

4. Configure the database connection:
   - Open `koneksi.php`
   - Update the following constants with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'library_db');
```

5. Set up the upload directories:
```bash
mkdir -p uploads/covers
mkdir -p uploads/profiles
chmod 777 uploads/covers uploads/profiles
```

6. Configure your web server to point to the project directory

## Project Structure

```
library-management/
├── class/
│   └── Book.php
├── uploads/
│   ├── covers/
│   └── profiles/
├── cms.php
├── database.sql
├── get_book.php
├── index.php
├── koneksi.php
├── login.php
├── proses.php
├── register.php
└── styles.css
```

## Security Features

- Password hashing using PHP's password_hash()
- Input sanitization and validation
- Prepared statements for database queries
- Session-based authentication
- XSS prevention using htmlspecialchars()
- CSRF protection for forms
- Secure file upload handling

## User Interface

The system features a modern, responsive interface with:
- Clean and intuitive navigation
- Modal-based forms for adding/editing books
- Confirmation dialogs for critical actions
- Responsive tables with sorting capability
- Image preview functionality
- Notification system for success/error messages

## CSS Features

- Custom color scheme with CSS variables
- Responsive design with media queries
- Modern animations and transitions
- Consistent spacing and typography
- Interactive hover effects
- Mobile-friendly layouts

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Future Improvements

- Add book borrowing system
- Implement book reservation feature
- Add search functionality
- Include barcode scanning
- Generate reports and analytics
- Add admin dashboard
- Implement email notifications
- Add multi-language support

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Font Awesome for icons
- Google Fonts for Poppins font family
- Contributors and testers

## Support

For support, please email [lois.122140098@student.itera.ac.id](mailto:lois.122140098@student.itera.ac.id) or open an issue in the repository.