🍽️ Food Ordering System
This is a simple Food Ordering System that allows users to browse and order food items, while the admin manages the platform's products, orders, and users. The system is powered by a backend database and includes essential functionalities like cart management, user messaging, and admin controls.

🚀 Features
User registration and login

Product listing with images, prices, and descriptions

Cart functionality with add/remove/update features

Order placement and tracking

Admin panel for managing users, products, and orders

Messaging system for user queries and feedback

🔐 Admin Login
Username: ahmed

Password: 111

Use these credentials to access the admin panel and manage the platform.

🗃️ Database Structure
The system is backed by a relational database containing 6 key tables:

user

Stores user account information such as username, email, and password.

admin

Contains admin login credentials and related metadata.

product

Lists all food items including name, category, price, and availability.

orders

Records all order details including user ID, product ID, quantity, status, and timestamps.

cart

Manages items users have added to their shopping cart prior to ordering.

messages

Stores messages or feedback from users, along with timestamps and user IDs.

💡 Setup Instructions
Clone the repository:

bash
Copy
Edit
git clone https://github.com/yourusername/food-ordering-system.git
cd food-ordering-system
Import the database:

Open your SQL tool (e.g., phpMyAdmin or MySQL Workbench).

Import the provided .sql file (if included) to set up the tables.

Configure database connection:

Update the database credentials in your config or db.php file.

Run the application:

Launch your preferred local server (e.g., XAMPP, WAMP, or a Node/PHP server).

Navigate to localhost/food-ordering-system in your browser.
