# 📰 Pakistan Times | 📰 پاکستان ٹائمز

A comprehensive, multilingual (English & Urdu) newspaper management system built with PHP and MySQL.
<br> | <br>
پی ایچ پی اور مای ایس کیو ایل کے ساتھ بنایا گیا ایک جامع، کثیر لسانی (انگریزی اور اردو) اخباری نظام۔

---

## 🚀 Features | 🚀 خصوصیات

* **🌐 Multilingual Support** | **🌐 کثیر لسانی سپورٹ**: Seamlessly switch between English and Urdu. | انگریزی اور اردو کے درمیان بغیر کسی رکاوٹ کے سوئچ کریں۔
* **👤 User Authentication** | **👤 صارف کی تصدیق**: Secure login and registration system. | محفوظ لاگ ان اور رجسٹریشن سسٹم۔
* **🔒 Role-Based Access Control** | **🔒 کردار پر مبنی رسائی کنٹرول**: Admin, Editor, and Public roles with distinct permissions. | ایڈمن، ایڈیٹر، اور پبلک کرداروں کے لیے مختلف اجازتیں۔
* **✍️ Article Management** | **✍️ مضامین کا انتظام**: Create, edit, delete, and manage articles with a rich text editor. | رچ ٹیکسٹ ایڈیٹر کے ساتھ مضامین بنائیں، ترمیم کریں، حذف کریں اور ان کا نظم کریں۔
* **📂 Category Management** | **📂 کیٹیگریز کا انتظام**: Organize articles into different categories. | مضامین کو مختلف کیٹیگریز میں منظم کریں۔
* **💬 Comment System** | **💬 تبصرہ سسٹم**: Nested comments with admin approval system. | ایڈمن کی منظوری کے ساتھ نیسٹڈ تبصرے کا نظام۔
* **👍 Likes & Follows** | **👍 لائکس اور فالوز**: Engage users with article likes and author following features. | مضامین پر لائکس اور مصنفین کو فالو کرنے کی خصوصیات کے ساتھ صارفین کو مشغول کریں۔
* **📊 Polls System** | **📊 پولز سسٹم**: Create and manage polls to gather user opinions. | صارفین کی رائے جاننے کے لیے پولز بنائیں اور ان کا نظم کریں۔
* **📈 SEO Friendly** | **📈 SEO دوستانہ**: SEO meta fields for articles to improve search engine visibility. | سرچ انجن کی بہتر درجہ بندی کے لیے مضامین کے لیے SEO میٹا فیلڈز۔
* **🖼️ Image Uploads** | **🖼️ تصویر اپ لوڈز**: Upload and manage images for articles, user avatars, and submissions. | مضامین، صارف کے اوتار، اور جمع کرائے گئے مواد کے لیے تصاویر اپ لوڈ اور ان کا نظم کریں۔
* **🎨 Theme Toggle** | **🎨 تھیم ٹوگل**: Switch between light and dark themes for better user experience. | بہتر صارف کے تجربے کے لیے لائٹ اور ڈارک تھیم کے درمیان سوئچ کریں۔
* **🔧 Admin Dashboard** | **🔧 ایڈمن ڈیش بورڈ**: A comprehensive dashboard to manage all aspects of the application. | ایپلیکیشن کے تمام پہلوؤں کا نظم کرنے کے لیے ایک جامع ڈیش بورڈ۔
* **📤 User Submissions** | **📤 صارف کی گذارشات**: Allows users to submit their own articles for review. | صارفین کو اپنے مضامین نظرثانی کے لیے جمع کرانے کی اجازت دیتا ہے۔
* **💾 Backup & Restore** | **💾 بیک اپ اور بحالی**: Easily back up and restore the entire database with a single click. | ایک کلک کے ساتھ پورے ڈیٹا بیس کا بیک اپ اور بحالی کریں۔
* **🔍 Advanced Search** | **🔍 جدید تلاش**: Filter articles by category, author, and date range. | کیٹیگری، مصنف، اور تاریخ کی حد کے لحاظ سے مضامین فلٹر کریں۔

---

## 📸 Screenshots | 📸 اسکرین شاٹس

| | | |
|:---:|:---:|:---:|
| ![Screenshot 1](pic%20(1).png) | ![Screenshot 2](pic%20(2).png) | ![Screenshot 3](pic%20(3).png) |
| ![Screenshot 4](pic%20(4).png) | ![Screenshot 5](pic%20(5).png) | ![Screenshot 6](pic%20(6).png) |
| ![Screenshot 7](pic%20(7).png) | ![Screenshot 8](pic%20(8).png) | ![Screenshot 9](pic%20(9).png) |
| ![Screenshot 10](pic%20(10).png) | ![Screenshot 11](pic%20(11).png) | |

---

## 🛠️ Technologies Used | 🛠️ استعمال شدہ ٹیکنالوجیز

* **🐘 PHP**
* **🐬 MySQL**
* **🌐 HTML5**
* **🎨 CSS3**
* **📜 JavaScript**
* **🅱️ Bootstrap**

---

## ⚙️ Setup & Installation | ⚙️ سیٹ اپ اور انسٹالیشن

1.  **Clone the repository** | **ریپوزٹری کلون کریں**:
    ```bash
    git clone [https://github.com/your-username/pakistan-times.git](https://github.com/your-username/pakistan-times.git)
    ```
2.  **Navigate to the project directory** | **پروجیکٹ ڈائریکٹری میں جائیں**:
    ```bash
    cd pakistan-times
    ```
3.  **Database Setup** | **ڈیٹا بیس سیٹ اپ**:
    * Open `index.php`. | `index.php` کھولیں۔
    * Update the database credentials on lines 6-8. | لائن 6-8 پر ڈیٹا بیس کی تفصیلات اپ ڈیٹ کریں۔
    ```php
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = 'root';
    ```
4.  **Run the application** | **ایپلیکیشن چلائیں**:
    * Start your local server (e.g., XAMPP, WAMP). | اپنا لوکل سرور (جیسے XAMPP, WAMP) شروع کریں۔
    * Open the project in your browser. The database and tables will be created automatically. | اپنے براؤزر میں پروجیکٹ کھولیں۔ ڈیٹا بیس اور ٹیبلز خود بخود بن جائیں گے۔

---

## 🔑 Admin Credentials | 🔑 ایڈمن کی تفصیلات

* **Username** | **یوزرنیم**: `admin`
* **Password** | **پاس ورڈ**: `admin123`

---

## 🤝 Contributing | 🤝 شراکت داری

Contributions are welcome! Please feel free to submit a pull request.
<br> | <br>
شراکت داری کا خیر مقدم ہے! براہ مہربانی پل ریکوئیسٹ بھیجیں۔

---

## 📄 License | 📄 لائسنس

This project is licensed under the MIT License.
<br> | <br>
یہ پروجیکٹ MIT لائسنس کے تحت لائسنس یافتہ ہے۔