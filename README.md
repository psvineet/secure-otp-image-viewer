# 🔐 Secure OTP Image Viewer (PHP)

## 📌 Overview
Secure OTP Image Viewer is a lightweight PHP-based application that provides **controlled, one-time access to images** using a password mechanism. The system ensures that access is **temporary, restricted, and monitored**, making it suitable for sharing sensitive visual content securely.

The application uses **session management, logging, and access control techniques** to prevent unauthorized reuse and to track user activity.

---

## 🚀 Features

### 🔑 One-Time Password Access
- Single-use password authentication
- Prevents reuse after successful login
- Blocks repeated or invalid attempts

### ⏱ Session Control
- Automatic session expiration (default: 40 seconds)
- Session invalidation on tab switch or inactivity
- Redirects user after session timeout

### 📊 Activity Logging
- Logs stored in `login_attempts.log`
- Records:
  - IP address
  - Browser/User-Agent
  - Timestamp
  - Status (SUCCESS, FAILED, BLOCKED, EXPIRED)

### 🖼 Secure Image Display
- Displays images from a protected directory
- Adds dynamic watermark (IP + timestamp)
- Disables:
  - Right-click
  - Dragging
  - Selection

### 🔒 Access Restriction
- Lock mechanism using `lock.txt`
- Ensures password is used only once

---

## 📂 Project Structure

```
project/
├── index.php              # Main application logic
├── lock.txt               # Tracks password usage
├── login_attempts.log     # Stores login logs
└── psim/                  # Image directory
    ├── 1.jpg
    ├── 2.jpg
    └── ...
```

---

## ⚙️ Configuration

Modify the following variables inside `index.php`:

```php
$PASSWORD = "1234";        // Default access password
$SESSION_TTL = 40;         // Session timeout (seconds)
$IMG_BASE = "psim/";       // Image directory path
```

---
## ⚠️ Limitations

This project is designed for educational and controlled-use scenarios. The following limitations should be considered:

- The password is static (`1234`) and not dynamically generated like a real OTP system  
- No database integration; all tracking is file-based (`.txt` and `.log`)  
- Single-user design; does not support multiple concurrent users  
- Frontend restrictions (disable right-click, drag, etc.) can be bypassed by advanced users  
- Images are not fully protected against manual extraction (e.g., screenshots or network inspection)  
- No encryption is applied to image delivery or storage  
- Session handling is basic and may not be secure for production-grade applications  
- No HTTPS enforcement (depends on server configuration)  

> Note: This project is intended for learning purposes and basic security demonstration, not for deployment in high-security or production environments.
---
## 📄 License

This project is licensed under the MIT License.
