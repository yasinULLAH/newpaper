<div align="center">
  <img src="https://raw.githubusercontent.com/yasinULLAH/newpaper/main/assets/logo.png" alt="Pakistan Times Logo" width="150"/>
  <h1><b>📰 Pakistan Times | پاکستان ٹائمز 📰</b></h1>
  <p>
    <b>A feature-rich, dynamic, and bilingual (English & Urdu) newspaper management system built with native PHP and MySQL.</b>
    <br> | <br>
    <b>پی ایچ پی اور مای ایس کیو ایل میں بنایا گیا ایک خصوصیات سے بھرپور، متحرک، اور دو لسانی (انگریزی و اردو) اخباری نظام۔</b>
  </p>

  [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap)](https://getbootstrap.com/)
  [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

</div>

---

### **📖 Table of Contents | 📖 فہرست**

- **[About The Project](#-about-the-project--منصوبے-کے-بارے-میں)**
- **[📸 Screenshots](#-screenshots--اسکرین-شاٹس)**
- **[🚀 Key Features](#-key-features--اہم-خصوصیات)**
  - [Content Management | مواد کا انتظام](#-content-management--مواد-کا-انتظام)
  - [User Interaction | صارف کا تعامل](#-user-interaction--صارف-کا-تعامل)
  - [Admin & Security | ایڈمن-اور-سیکیورٹی](#-admin--security--ایڈمن-اور-سیکیورٹی)
  - [General Features | عمومی-خصوصیات](#-general-features--عمومی-خصوصیات)
- **[🛠️ Technology Stack](#️-technology-stack--استعمال-شدہ-ٹیکنالوجیز)**
- **[🗄️ Database Schema](#️-database-schema--ڈیٹا-بیس-اسکیما)**
- **[⚙️ Getting Started](#️-getting-started--شروع-کرنے-کا-طریقہ)**
  - [Prerequisites | ضروریات](#prerequisites--ضروریات)
  - [Installation | انسٹالیشن](#installation--انسٹالیشن)
- **[🧑‍💻 Usage](#-usage--استعمال-کا-طریقہ)**
- **[📈 Future Improvements](#-future-improvements--مستقبل-میں-بہتری)**
- **[🤝 Contributing](#-contributing--شراکت-داری)**
- **[📄 License](#-license--لائسنس)**
- **[📞 Contact](#-contact--رابطہ)**

---

### **🌟 About The Project | منصوبے کے بارے میں**

Pakistan Times is a comprehensive, single-file web application that functions as a complete newspaper and content management system. It was built to demonstrate a wide range of web development features in a real-world context, from dynamic content creation and user authentication to advanced administrative controls. The application is fully bilingual, offering a seamless experience for both English and Urdu readers and content creators.
<br> | <br>
پاکستان ٹائمز ایک جامع، سنگل فائل ویب ایپلیکیشن ہے جو ایک مکمل اخباری اور مواد کے انتظام کے نظام کے طور پر کام کرتی ہے۔ اسے حقیقی دنیا کے تناظر میں ویب ڈویلپمنٹ کی وسیع خصوصیات کو ظاہر کرنے کے لیے بنایا گیا تھا، جس میں متحرک مواد کی تخلیق اور صارف کی تصدیق سے لے کر جدید انتظامی کنٹرولز شامل ہیں۔ یہ ایپلیکیشن مکمل طور پر دو لسانی ہے، جو انگریزی اور اردو قارئین اور مواد تخلیق کرنے والوں دونوں کے لیے ایک ہموار تجربہ پیش کرتی ہے۔

---

### **📸 Screenshots | اسکرین شاٹس**

| | | |
|:---:|:---:|:---:|
| **Homepage (Light Mode)** | **Homepage (Dark Mode)** | **Article View** |
| ![Screenshot 1](pic%20(1).png) | ![Screenshot 2](pic%20(2).png) | ![Screenshot 3](pic%20(3).png) |
| **Urdu Language View** | **Admin Dashboard** | **Article Management** |
| ![Screenshot 4](pic%20(4).png) | ![Screenshot 5](pic%20(5).png) | ![Screenshot 6](pic%20(6).png) |
| **Category Management** | **User Management** | **Advanced Search** |
| ![Screenshot 7](pic%20(7).png) | ![Screenshot 8](pic%20(8).png) | ![Screenshot 9](pic%20(9).png) |
| **Profile Page** | **User Submissions** | |
| ![Screenshot 10](pic%20(10).png) | ![Screenshot 11](pic%20(11).png) | |


---

### **🚀 Key Features | اہم خصوصیات**

#### **📰 Content Management | مواد کا انتظام**
* **✍️ Dynamic Articles** | **✍️ متحرک مضامین**: Full CRUD (Create, Read, Update, Delete) functionality for news articles. | اخباری مضامین کے لیے مکمل CRUD (بنانا، پڑھنا، اپ ڈیٹ کرنا، حذف کرنا) کی فعالیت۔
* **📂 Categories** | **📂 زمرے**: Organize articles under customizable categories. | مضامین کو حسب ضرورت زمروں کے تحت منظم کریں۔
* **🖼️ Image Handling** | **🖼️ تصویر کا انتظام**: Upload images for articles, user profiles, and submissions with automatic handling on updates and deletions. | مضامین، صارف پروفائلز، اور گذارشات کے لیے تصاویر اپ لوڈ کریں جو اپ ڈیٹس اور حذف ہونے پر خودکار طور پر منظم ہوتی ہیں۔
* **🔄 Revision History** | **🔄 نظرثانی کی تاریخ**: Automatically saves previous versions of articles upon update and allows admins to revert to any revision. | اپ ڈیٹ ہونے پر مضامین کے پچھلے ورژنز کو خودکار طور پر محفوظ کرتا ہے اور ایڈمن کو کسی بھی نظرثانی پر واپس جانے کی اجازت دیتا ہے۔
* **📤 User Submissions** | **📤 صارف کی گذارشات**: Registered users can submit articles, which admins can approve (publish), reject, or delete. | رجسٹرڈ صارفین مضامین جمع کرا سکتے ہیں، جنہیں ایڈمن منظور (شائع)، مسترد، یا حذف کر سکتے ہیں۔

#### **👥 User Interaction | صارف کا تعامل**
* **💬 Nested Comments** | **💬 نیسٹڈ تبصرے**: A hierarchical comment system with replies and an admin approval queue. | جوابات کے ساتھ ایک درجہ بندی والا تبصرہ نظام اور ایک ایڈمن منظوری کی قطار۔
* **👍 Likes & Follows** | **👍 لائکس اور فالوز**: AJAX-powered system for users to like articles and follow authors. | صارفین کو مضامین پسند کرنے اور مصنفین کو فالو کرنے کے لیے AJAX پر مبنی نظام۔
* **📊 Polls System** | **📊 پولز کا نظام**: Engage the audience by creating polls with multiple options and displaying live results. | متعدد آپشنز کے ساتھ پولز بنا کر اور لائیو نتائج دکھا کر سامعین کو مشغول کریں۔
* **🔍 Advanced Search** | **🔍 جدید تلاش**: A powerful search engine to filter articles by keyword, category, author, and date range. | کلیدی لفظ، زمرہ، مصنف، اور تاریخ کی حد کے لحاظ سے مضامین کو فلٹر کرنے کے لیے ایک طاقتور سرچ انجن۔

#### **🔐 Admin & Security | ایڈمن اور سیکیورٹی**
* **👤 Role-Based Access** | **👤 کردار پر مبنی رسائی**: Three user roles (Admin, Editor, Public) with different permissions for managing content. | مواد کے انتظام کے لیے مختلف اجازتوں کے ساتھ تین صارف کردار (ایڈمن، ایڈیٹر، پبلک)۔
* **🔧 Comprehensive Dashboard** | **🔧 جامع ڈیش بورڈ**: A central hub for managing articles, categories, users, comments, submissions, polls, and ad units. Includes visual statistics using Chart.js. | مضامین، زمرے، صارفین، تبصرے، گذارشات، پولز، اور اشتہاری اکائیوں کے انتظام کے لیے ایک مرکزی مرکز۔ Chart.js کا استعمال کرتے ہوئے بصری اعدادوشمار شامل ہیں۔
* **💾 Backup & Restore** | **💾 بیک اپ اور بحالی**: One-click JSON export of the entire database and a restore feature to import from a backup file. | پورے ڈیٹا بیس کا ایک کلک JSON ایکسپورٹ اور بیک اپ فائل سے درآمد کرنے کے لیے ایک بحالی کی خصوصیت۔
* **🔗 Broken Link Checker** | **🔗 ٹوٹے ہوئے لنکس کی پڑتال**: An admin tool to scan all articles for broken hyperlinks. | تمام مضامین میں ٹوٹے ہوئے ہائپر لنکس کو اسکین کرنے کے لیے ایک ایڈمن ٹول۔
* **📜 Activity Logs** | **📜 سرگرمی کے نوشتہ جات**: Logs important actions performed by users, such as login, article creation, and updates. | صارفین کی طرف سے کی گئی اہم کارروائیوں کو لاگ کرتا ہے، جیسے لاگ ان، مضمون کی تخلیق، اور اپ ڈیٹس۔

#### **✨ General Features | عمومی خصوصیات**
* **🌐 Fully Bilingual** | **🌐 مکمل دو لسانی**: Seamlessly switch between English and Urdu across the entire site. | پوری سائٹ پر انگریزی اور اردو کے درمیان بغیر کسی رکاوٹ کے سوئچ کریں۔
* **🎨 Dual Theme** | **🎨 دوہری تھیم**: A modern user interface with an easy toggle between light and dark modes (saves preference). | لائٹ اور ڈارک موڈز کے درمیان آسان ٹوگل کے ساتھ ایک جدید صارف انٹرفیس (ترجیح محفوظ کرتا ہے)۔
* **⚙️ Automatic Setup** | **⚙️ خودکار سیٹ اپ**: The script automatically creates the database and all necessary tables on the first run. | اسکرپٹ پہلی بار چلنے پر ڈیٹا بیس اور تمام ضروری ٹیبلز خود بخود بنا دیتا ہے۔
* **📈 SEO Optimized** | **📈 SEO آپٹمائزڈ**: Dedicated meta title, description, and keyword fields for each article in both languages. | ہر مضمون کے لیے دونوں زبانوں میں مخصوص میٹا ٹائٹل، تفصیل، اور کلیدی الفاظ کے فیلڈز۔
* **📱 Fully Responsive** | **📱 مکمل طور پر ریسپانسیو**: Built with Bootstrap 5 to ensure a great experience on all devices, from desktops to mobile phones. | ڈیسک ٹاپ سے لے کر موبائل فونز تک تمام آلات پر ایک بہترین تجربہ کو یقینی بنانے کے لیے بوٹسٹریپ 5 کے ساتھ بنایا گیا ہے۔

---

### **🛠️ Technology Stack | استعمال شدہ ٹیکنالوجیز**

| Icon | Technology | Description |
|:---:|---|---|
| 🐘 | **PHP** | Core backend logic, server-side scripting, and database interaction. |
| 🐬 | **MySQL** | Relational database for storing all application data. |
| 🌐 | **HTML5** | The standard markup language for creating web pages. |
| 🎨 | **CSS3** | Styling the application, including the dual-theme functionality. |
| 📜 | **JavaScript** | Client-side scripting for interactive features like AJAX, theme toggling, and modals. |
| 🅱️ | **Bootstrap 5** | Front-end framework for building a responsive and modern UI. |
| 📊 | **Chart.js** | Used in the admin dashboard to render statistical charts. |
| 🏛️ | **Font Awesome**| Provides a wide range of icons used throughout the application. |

---

### **🗄️ Database Schema | ڈیٹا بیس اسکیما**

The application automatically creates 17 tables to manage its data effectively.
<br> | <br>
ایپلیکیشن اپنے ڈیٹا کو مؤثر طریقے سے منظم کرنے کے لیے 17 ٹیبلز خود بخود بناتی ہے۔

| Table Name | Purpose |
|---|---|
| `users` | Stores user credentials, roles, profiles, and avatars. |
| `categories` | Manages news categories (e.g., National, Sports). |
| `articles` | The core table for all news articles, including multilingual content and SEO data. |
| `comments` | Stores user comments with support for nested replies. |
| `revisions` | Keeps a historical record of article edits. |
| `user_submissions`| Holds articles submitted by users awaiting admin approval. |
| `polls` & `poll_votes` | Manages the polling system and user votes. |
| `article_likes` | Tracks which users have liked which articles. |
| `followers` | Manages the author-follower relationships. |
| `activity_logs` | Logs all significant user and system actions. |
| `ad_units` | Stores advertisement code and placement locations. |
| `...and more` | Other tables for tags, subscriptions, etc. |

---

### **⚙️ Getting Started | شروع کرنے کا طریقہ**

Follow these steps to get a local copy up and running.
<br> | <br>
مقامی کاپی حاصل کرنے اور چلانے کے لیے ان اقدامات پر عمل کریں۔

#### **Prerequisites | ضروریات**
Make sure you have a local server environment installed.
<br> | <br>
یقینی بنائیں کہ آپ کے پاس مقامی سرور ماحول نصب ہے۔
* **WAMP**, **XAMPP**, or any other server stack that supports:
    * PHP 7.4 or higher
    * MySQL or MariaDB

#### **Installation | انسٹالیشن**
1.  **Clone the Repository** | **ریپوزٹری کلون کریں**
    ```bash
    git clone [https://github.com/yasinULLAH/newpaper.git](https://github.com/yasinULLAH/newpaper.git)
    ```
2.  **Move to Server Directory** | **سرور ڈائریکٹری میں منتقل کریں**
    * Place the cloned folder inside your server's web root directory (e.g., `htdocs` for XAMPP). | کلون شدہ فولڈر کو اپنے سرور کی ویب روٹ ڈائریکٹری میں رکھیں (مثال کے طور پر XAMPP کے لیے `htdocs` )۔

3.  **Configure Database Connection** | **ڈیٹا بیس کنکشن تشکیل دیں**
    * Open `index.php` in a code editor. | کوڈ ایڈیٹر میں `index.php` کھولیں۔
    * Modify lines 6-8 with your MySQL database credentials. | لائن 6-8 کو اپنی MySQL ڈیٹا بیس کی تفصیلات کے ساتھ تبدیل کریں۔
    ```php
    $db_host = 'localhost';
    $db_user = 'root'; // Your DB username
    $db_pass = '';     // Your DB password
    ```

4.  **Run the Application** | **ایپلیکیشن چلائیں**
    * Start your Apache and MySQL services from your server control panel (e.g., XAMPP Control Panel). | اپنے سرور کنٹرول پینل (جیسے XAMPP کنٹرول پینل) سے اپنی Apache اور MySQL خدمات شروع کریں۔
    * Open your web browser and navigate to `http://localhost/newpaper`. | اپنا ویب براؤزر کھولیں اور `http://localhost/newpaper` پر جائیں۔
    * The application will automatically create the `newspaper_db` database, all tables, and seed initial data (admin user, categories, and sample articles). | ایپلیکیشن خود بخود `newspaper_db` ڈیٹا بیس، تمام ٹیبلز، اور ابتدائی ڈیٹا (ایڈمن صارف، زمرے، اور نمونہ مضامین) بنا دے گی۔

---

### **🧑‍💻 Usage | استعمال کا طریقہ**

* **Public User** | **عوامی صارف**: Browse articles, switch languages, toggle themes, search, comment, and register. | مضامین براؤز کریں، زبانیں تبدیل کریں، تھیمز ٹوگل کریں، تلاش کریں، تبصرہ کریں، اور رجسٹر کریں۔
* **Registered User** | **رجسٹرڈ صارف**: All public actions, plus submit articles for review, vote in polls, like articles, and follow authors. | تمام عوامی کارروائیاں، اس کے علاوہ نظرثانی کے لیے مضامین جمع کرائیں، پولز میں ووٹ دیں، مضامین کو پسند کریں، اور مصنفین کو فالو کریں۔
* **Admin/Editor** | **ایڈمن/ایڈیٹر**: Access the admin panel to manage all site content. | تمام سائٹ کے مواد کا نظم کرنے کے لیے ایڈمن پینل تک رسائی حاصل کریں۔

#### **Default Admin Credentials | ڈیفالٹ ایڈمن کی تفصیلات**
* **Username** | **یوزرنیم**: `admin`
* **Password** | **پاس ورڈ**: `admin123`

---

### **📈 Future Improvements | مستقبل میں بہتری**
- [ ] **Refactor to MVC** | **MVC میں ریفیکٹر کریں**: Separate the application logic, presentation, and data into a Model-View-Controller architecture for better maintainability. | بہتر دیکھ بھال کے لیے ایپلیکیشن کی منطق، پیشکش، اور ڈیٹا کو ماڈل-ویو-کنٹرولر فن تعمیر میں الگ کریں۔
- [ ] **Implement REST API** | **REST API نافذ کریں**: Create dedicated API endpoints for client-side JavaScript interactions instead of full-page reloads. | پورے صفحے کے دوبارہ لوڈ ہونے کے بجائے کلائنٹ سائڈ جاوا اسکرپٹ تعاملات کے لیے وقف شدہ API اینڈ پوائنٹس بنائیں۔
- [ ] **Add Unit Tests** | **یونٹ ٹیسٹ شامل کریں**: Implement PHPUnit tests to ensure code reliability and prevent regressions. | کوڈ کی وشوسنییتا کو یقینی بنانے اور رجعت کو روکنے کے لیے PHPUnit ٹیسٹ نافذ کریں۔
- [ ] **Containerize with Docker** | **ڈاکر کے ساتھ کنٹینرائز کریں**: Create a Dockerfile and Docker Compose setup for easy and consistent deployment. | آسان اور مستقل تعیناتی کے لیے ایک Dockerfile اور Docker Compose سیٹ اپ بنائیں۔

---

### **🤝 Contributing | شراکت داری**

Contributions make the open-source community an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.
<br> | <br>
شراکتیں اوپن سورس کمیونٹی کو سیکھنے، حوصلہ افزائی کرنے اور تخلیق کرنے کے لیے ایک حیرت انگیز جگہ بناتی ہیں۔ آپ کی کسی بھی شراکت کو **بہت سراہا جاتا ہے**۔

1.  Fork the Project | پروجیکٹ کو فورک کریں
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`) | اپنی فیچر برانچ بنائیں (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`) | اپنی تبدیلیاں کمٹ کریں (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`) | برانچ میں پش کریں (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request | ایک پل ریکوئیسٹ کھولیں

---

### **📄 License | لائسنس**

Distributed under the MIT License. See `LICENSE` for more information.
<br> | <br>
MIT لائسنس کے تحت تقسیم کیا گیا ہے۔ مزید معلومات کے لیے `LICENSE` دیکھیں۔

---

### **📞 Contact | رابطہ**

Yasin Ullah - [@yasinULLAH](https://github.com/yasinULLAH) - yasincomps@gmail.com

Project Link: [https://github.com/yasinULLAH/newpaper](https://github.com/yasinULLAH/newpaper)