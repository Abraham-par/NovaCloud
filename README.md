# 🌐 NovaCloud – Secure File Storage & Sharing Platform  

## 📋 Project Overview  
NovaCloud is a fully functional web-based file storage and sharing platform developed as part of the **Internet Programming II** course at **Ambo University, Department of Information Technology**.  

Built as an educational project, NovaCloud demonstrates real-world web development principles—including frontend-backend integration, database management, session handling, and file operations—within a local XAMPP environment.  

---

## 🎓 Academic Context  
| Detail | Information |
|--------|------------|
| **Course** | Internet Programming II |
| **Department** | Information Technology |
| **University** | Ambo University |
| **Project Type** | Course Project |
| **Developer** | Abraham Mekonnen |

---

## 🛠️ Technology Stack  

### **Frontend**  
- HTML5  
- CSS3  
- JavaScript  
- Bootstrap 5 (CDN)  

### **Backend**  
- PHP  

### **Database**  
- MySQL (via phpMyAdmin)  

### **Local Server**  
- XAMPP (Apache + MySQL + PHP)  

---

## ✨ Core Features  
- 🔐 **User Authentication** – Secure login and registration system  
- 📤 **File Upload & Storage** – Supports multiple file types  
- 👁️ **File Preview & Download** – Easy access to uploaded content  
- 📊 **Dashboard Interface** – Clean, organized file management view  
- 📱 **Responsive Design** – Mobile-friendly with Bootstrap  
- 🔒 **Session Management** – Basic security for user data  
- 🧠 **Scalable Structure** – Ready for future admin/role-based features  

---

## 📁 Project Structure  
```
NovaCloud/
│
├── admin/                          # Admin-only area
│   └── dashboard.php               # Admin dashboard (manage users, storage, activity)
│
├── api/                            # Backend API endpoints (AJAX / Fetch)
│   ├── clear-storage.php           # Clears unused or selected user storage
│   ├── create-share-link.php       # Generates a shareable file link
│   ├── deactivate-account.php     # Deactivates a user account
│   ├── delete-file.php             # Deletes a file from server & database
│   ├── get-activity.php            # Returns user activity logs
│   ├── search.php                  # Searches files/folders
│   ├── share-file.php              # Handles file sharing permissions
│   ├── update-language.php         # Saves user language preference
│   ├── update-preferences.php      # Updates user settings (theme, options)
│   └── upload.php                  # Handles secure file uploads
│
├── assets/                         # Frontend static assets
│   ├── css/
│   │   └── style.css               # Main website styles
│   │
│   ├── images/                     # Icons, logos, illustrations
│   │
│   ├── js/
│   │   ├── language-switcher.js    # Changes UI language dynamically
│   │   └── main.js                 # Main frontend logic (AJAX, UI actions)
│   │
│   └── json/
│       └── languages.json          # Translation strings for multi-language support
│
├── errors/                         # Custom error pages
│   ├── 403.php                     # Access denied page
│   ├── 404.php                     # Page not found
│   └── 500.php                     # Server error page
│
├── includes/                       # Core backend logic (used everywhere)
│   ├── config.php                  # Global configuration (DB, constants)
│   ├── database.php                # Database connection (PDO/MySQLi)
│   ├── header.php                  # Shared page header (navbar, meta)
│   ├── footer.php                  # Shared page footer
│   ├── functions.php               # Reusable application functions
│   ├── helpers.php                 # Utility helper functions
│   └── session.php                 # Session handling & authentication
│
├── logs/                           # Application logs
│   ├── error.log                   # PHP and system errors
│   └── share-file.log              # Logs file sharing actions
│
├── uploads/                        # User uploaded files (protected directory)
│
├── .htaccess                       # Apache rules (security, routing, access control)
│
├── about.php                       # About NovaCloud page
├── auth.php                        # Authentication logic & access checks
├── create_admin.php                # Creates an admin account (setup-only file)
├── dashboard.php                   # User dashboard (files, uploads, activity)
├── download.php                    # Secure file download handler
├── favicon.png                     # Website favicon
├── forgot-password.php             # Password reset request page
├── index.php                       # Landing / home page
├── logout.php                      # Ends user session
├── nova_cloud (1).sql              # Database schema & sample data
├── privacy.php                     # Privacy policy page
├── profile.php                     # User profile management
├── README.md                       # Project documentation
├── register.php                    # User registration page
├── reset-password.php              # Password reset confirmation
├── settings.php                    # User settings (preferences, deactivation)
├── terms.php                       # Terms and Conditions page
└── test.php                        # Testing / debugging file

```

---

## 🚀 Local Installation Guide  

### **1. Prerequisites**  
- Install [XAMPP](https://www.apachefriends.org/) (Windows/macOS/Linux)  
- Ensure **Apache** and **MySQL** are running via XAMPP Control Panel  

### **2. Project Setup**  
1. Clone or extract the project folder into:  
   ```
   C:\xampp\htdocs\novacloud\  # Windows
   /opt/lampp/htdocs/novacloud/ # Linux
   ```
2. Start Apache and MySQL from XAMPP.

### **3. Database Configuration**  
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)  
2. Create a new database: `nova_cloud`  
3. Click **Import** and select the provided `nova_cloud.sql` file  
4. Update database credentials in `includes/config.php` if necessary  

### **4. Launch the Application**  
Visit in your browser:  
👉 [http://localhost/novacloud](http://localhost/novacloud)  

---

## 👤 Getting Started  
1. **Register** – Create a new account via the authentication page  
2. **Login** – Access your personalized dashboard  
3. **Upload Files** – Use the upload interface in the dashboard  
4. **Manage Files** – View, download, or organize your stored files  
5. **Logout** – Securely end your session  

---

## ⚠️ Important Notes  
- This is a **learning project**—not intended for production use  
- Basic security implementations are for educational demonstration only  
- Requires a local server (XAMPP/LAMP/WAMP) to function  
- File size limits depend on PHP configuration (`php.ini`)  

---

## 📚 Learning Outcomes  
Through building NovaCloud, the following competencies were developed:  
- ✅ Full-stack web application architecture  
- ✅ PHP & MySQL integration (CRUD operations)  
- ✅ Session-based authentication and security basics  
- ✅ Frontend-backend communication patterns  
- ✅ Responsive UI design with Bootstrap  
- ✅ File handling and server-side storage management  
- ✅ Structured, maintainable code practices  

---

## ✍️ Author  
**Abraham Mekonnen**  
Information Technology Student  
Ambo University  

---

*This project is submitted in partial fulfillment of the Internet Programming II course requirements.*  

---
*Made with ❤️ for learning and sharing knowledge.*  

---

### 🔗 Quick Links  
- [Report an Issue](#)  
- [View Source Code](#)  
- [Course Syllabus](#)  

---

**📌 Tip:** Always stop Apache/MySQL via XAMPP after use to free system resources.  

---

*Last Updated: December 2025* 

---