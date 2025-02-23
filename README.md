# DEV.Query Blog Platform v0.0.1-beta

A dynamic PHP-based blog platform with user authentication, content management, and responsive design.

## Features

- User authentication (Sign up/Sign in)
- Admin dashboard
- Featured posts system
- Category management
- Responsive design
- Post creation and management
- User profile management with avatars
- Comment system

## Tech Stack

- PHP
- MySQL
- HTML/CSS
- JavaScript
- FontAwesome for icons

## Setup Instructions

1. Requirements:

   - WAMP/XAMPP/LAMP server
   - PHP 7.4+
   - MySQL 5.7+
2. Installation:

   ```bash
   # Clone the repository
   git clone [your-repository-url]

   # Import database
   # Import the SQL file from /database/blog.sql to your MySQL server

   # Configure database
   # Update config/database.php with your credentials
   ```
3. Configuration:

   - Navigate to `config/constants.php`
   - Update `ROOT_URL` to match your local environment
   - Set up database credentials in `config/database.php`
4. Run the application:

   - Place the project in your web server's root directory
   - Access through: `http://localhost/Blog-Dambwe`

## Directory Structure

```
Blog-Dambwe/
├── admin/         # Admin dashboard files
├── config/        # Configuration files
├── css/          # Stylesheets
├── images/       # Uploaded images
├── partials/     # Reusable PHP components
├── web-assets/   # Static assets
└── database/     # Database files
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

[Your chosen license]

## Contact

[Your contact information]
