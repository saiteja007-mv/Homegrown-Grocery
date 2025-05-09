# Homegrown Grocery - E-commerce Platform

Homegrown Grocery is a full-featured e-commerce platform for selling fresh produce and groceries online. Built with PHP and MySQL, it provides a robust solution for managing an online grocery store with both customer and admin interfaces.

## Website Screenshots

### User Interface

#### Home Page
![Home Page](./Website_screenshots/Home%20page%20of%20Users.png)

#### Authentication
![Login Page](./Website_screenshots/Login%20page.png)
![Registration Page](./Website_screenshots/Registration%20page.png)

#### Shopping Experience
![Shopping Cart](./Website_screenshots/User%20cart.png)
![Checkout Page](./Website_screenshots/User%20checkout%20page.png)

#### Order Management
![Orders Page](./Website_screenshots/User%20orders%20page.png)
![Order Success](./Website_screenshots/Order%20successful.png)

#### Customer Support
![Help Center](./Website_screenshots/User%20Help%20center.png)

### Admin Interface

#### Dashboard
![Admin Panel](./Website_screenshots/Admin%20panel%20home%20page.png)

#### Management Sections
![Product Management](./Website_screenshots/Admin%20managing%20products.png)
![Order Management](./Website_screenshots/Admin%20managing%20orders.png)
![Help Ticket Management](./Website_screenshots/Admin%20managing%20help%20tickets.png)
![User Management](./Website_screenshots/Admin%20user%20management.png)

## Database Structure

### Tables Overview

#### User Management (users)
![Users Table](./Database_screenshots/Users%20table.png)

#### Product Management (products)
![Products Table](./Database_screenshots/Products%20table.png)

#### Order Management (orders, order_details)
![Orders Table](./Database_screenshots/Orders%20table.png)
![Order Details Table](./Database_screenshots/Order%20details%20table.png)

#### Customer Support (help_tickets)
![Help Tickets Table](./Database_screenshots/Help%20tickets%20table.png)

#### Shopping Experience (cart)
![Cart Table](./Database_screenshots/Cart%20table.png)

#### Shipping Information (shipping)
![Shipping Table](./Database_screenshots/Shipping%20table.png)

## Features

### Customer Features
- 🛒 User-friendly shopping cart
- 👤 User registration and authentication
- 📦 Order tracking system
- 🎫 Help ticket system for customer support
- 🚚 Shipping information management
- 💳 Order history and status tracking
- 📱 Responsive design for mobile devices

### Admin Features
- 📊 Dashboard for order management
- 📦 Product inventory management
- 👥 Customer management
- 🎫 Help ticket management system
- 📫 Order status updates
- 📈 Basic sales tracking

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- XAMPP/WAMP/MAMP or similar local development environment
- Web browser (Chrome, Firefox, Safari, etc.)

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/Homegrown-Grocery.git
   ```

2. **Database Setup using XAMPP and phpMyAdmin**
   - Start Apache and MySQL in XAMPP Control Panel
   - Open your web browser and go to: `http://localhost/phpmyadmin`
   - Click on 'New' in the left sidebar to create a new database
   - Enter `nestco_homegrown` as the database name and click 'Create'
   - Select the newly created `nestco_homegrown` database
   - Click on the 'Import' tab at the top
   - Click 'Choose File' and select the `nestco_homegrown.sql` file from your project folder
   - Scroll down and click 'Import' to create all the tables

3. **Configure Database Connection**
   - Navigate to `config/db.php`
   - Update the database connection parameters:
   ```php
   $servername = "localhost"; // Usually localhost for XAMPP
   $username = "root";        // Default XAMPP username
   $password = "";           // Default XAMPP password is blank
   $dbname = "nestco_homegrown";
   ```

4. **Set Up Web Server**
   - Copy the entire project folder to:
     - `C:\xampp\htdocs\Homegrown-Grocery` (for Windows)
     - `/Applications/XAMPP/htdocs/Homegrown-Grocery` (for macOS)
     - `/opt/lampp/htdocs/Homegrown-Grocery` (for Linux)

5. **Start Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL modules
   - Wait until both modules show green status

6. **Access the Application**
   - Open your web browser
   - Visit: `http://localhost/Homegrown-Grocery`

## Default Admin Account

Use these credentials to access the admin panel:
- Email: `admin@admin.com`
- Password: `admin123`

## Project Structure

```
Homegrown-Grocery/
├── admin/                  # Admin panel files
├── config/                 # Configuration files
├── includes/              # Common include files
├── assets/                # Static assets (CSS, JS, images)
├── uploads/               # Product images
├── Website_screenshots/   # UI screenshots
├── Database_screenshots/  # Database structure screenshots
├── nestco_homegrown.sql   # Database schema
└── README.md             # This file
```

## Security Features

- Password hashing using bcrypt
- SQL injection prevention using prepared statements
- Session-based authentication
- Admin-only restricted areas
- Input validation and sanitization

## Order Status Flow

1. **Pending** - Initial state when order is placed
2. **Confirmed** - Order verified by admin
3. **Shipped** - Order has been shipped
4. **Delivered** - Order successfully delivered
5. **Cancelled** - Order cancelled (if applicable)

## Help Ticket System

Customers can:
- Create support tickets
- Track ticket status
- Communicate with admin
- Link tickets to specific orders

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify MySQL is running
   - Check database credentials in `config/db.php`
   - Ensure correct port number is used

2. **Images Not Loading**
   - Check file permissions in uploads directory
   - Verify .htaccess configuration

3. **Admin Access Issues**
   - Clear browser cache and cookies
   - Verify admin credentials
   - Check session configuration

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact 

- LinkedIn: [Venkata Sai Teja M](https://www.linkedin.com/in/venkatasaitejam)
- GitHub: [saiteja007-mv](https://github.com/saiteja007-mv)