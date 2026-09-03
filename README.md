# 🧳 Roamie

A full-stack travel platform connecting travelers with native local guides — featuring real-time chat, vehicle rental booking, and trip management, all in one place.

## ✨ Features

- 🧭 Browse and book with native local guides
- 🚗 Vehicle rental booking with cancellation options
- 💬 Real-time chat between travelers and guides
- 📝 Add and manage listings
- 🛎️ Booking management — book, cancel, confirm
- 🌍 Culture, about us, careers, and contact pages
- 💳 Checkout flow for bookings

## 🛠️ Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Local Server:** WAMP / XAMPP

## 📁 Project Structure

```
roamie/
├── about_us.php            # About the platform
├── add_listing.php         # Guides/hosts add new listings
├── book_success.php        # Booking confirmation page
├── bookings.php            # View/manage bookings
├── cancel_book.php         # Cancel a booking
├── cancellation_opt.php    # Cancellation options/policy
├── careers.php             # Careers page
├── chat.php                # Real-time chat between users
├── checkout.php            # Checkout flow
├── contact.php             # Contact page
├── culture.php             # Culture/travel content
├── delete_booking.php      # Remove a booking
└── ...
```

## 🚀 Getting Started

### Prerequisites
- [WAMP](https://www.wampserver.com/) / [XAMPP](https://www.apachefriends.org/) installed
- PHP 7.4+ and MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/amruta-thakur05/roamie.git
   ```

2. **Move the project into your server directory**
   - For WAMP: `C:\wamp64\www\`
   - For XAMPP: `C:\xampp\htdocs\`

3. **Import the database**
   - Open phpMyAdmin
   - Create a new database
   - Import the project's SQL file to set up the required tables

4. **Configure database connection**
   - Update the database config file with your database name, username, and password

5. **Start the server**
   - Launch WAMP/XAMPP
   - Visit `http://localhost/roamie/` in your browser

## 📸 Screenshots

**homepage**
<img width="895" height="459" alt="image" src="https://github.com/user-attachments/assets/cbe9297c-4d8f-4326-b59d-77cc5ee537ff" />

**ai bot**
<img width="940" height="480" alt="image" src="https://github.com/user-attachments/assets/7387c1f6-6cb9-4cf7-b9a0-7871d7441b9f" />

**chat**
<img width="930" height="478" alt="image" src="https://github.com/user-attachments/assets/0fbe17f4-ea80-4a37-8be9-75158ab33946" />

**booking**
<img width="929" height="479" alt="image" src="https://github.com/user-attachments/assets/2768f4c5-5d30-4c21-aa2e-f0ebe7cc0a72" />

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🙋‍♀️ Author

**Amruta Thakur**
- GitHub: [@amruta-thakur05](https://github.com/amruta-thakur05)
