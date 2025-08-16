<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'newspaper_db';
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
    $conn->select_db($db_name);
}
$conn->set_charset("utf8mb4");
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor', 'public') DEFAULT 'public',
        avatar VARCHAR(255) DEFAULT NULL,
        bio_en TEXT DEFAULT NULL,
        bio_ur TEXT DEFAULT NULL,
        social_links JSON DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_en VARCHAR(100) NOT NULL,
        name_ur VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_en TEXT NOT NULL,
        title_ur TEXT NOT NULL,
        content_en LONGTEXT NOT NULL,
        content_ur LONGTEXT NOT NULL,
        category_id INT,
        author_id INT,
        image VARCHAR(255),
        is_breaking TINYINT(1) DEFAULT 0,
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        status ENUM('draft', 'published') DEFAULT 'published',
        is_sponsored TINYINT(1) DEFAULT 0,
        slug VARCHAR(255) UNIQUE,
        published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        seo_meta_title_en VARCHAR(255) DEFAULT NULL,
        seo_meta_title_ur VARCHAR(255) DEFAULT NULL,
        seo_meta_description_en TEXT DEFAULT NULL,
        seo_meta_description_ur TEXT DEFAULT NULL,
        seo_keywords_en TEXT DEFAULT NULL,
        seo_keywords_ur TEXT DEFAULT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        article_id INT,
        user_id INT,
        name VARCHAR(100),
        email VARCHAR(255),
        comment TEXT NOT NULL,
        parent_comment_id INT DEFAULT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) UNIQUE NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS article_tags (
        article_id INT,
        tag_id INT,
        PRIMARY KEY (article_id, tag_id),
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS article_likes (
        user_id INT NOT NULL,
        article_id INT NOT NULL,
        PRIMARY KEY (user_id, article_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS followers (
        follower_id INT NOT NULL,
        followed_id INT NOT NULL,
        PRIMARY KEY (follower_id, followed_id),
        FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_en TEXT NOT NULL,
        question_ur TEXT NOT NULL,
        options_en JSON NOT NULL,
        options_ur JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL
    )",
    "CREATE TABLE IF NOT EXISTS poll_votes (
        poll_id INT NOT NULL,
        user_id INT NOT NULL,
        option_index INT NOT NULL,
        voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (poll_id, user_id),
        FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS user_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title_en TEXT NOT NULL,
        title_ur TEXT NOT NULL,
        content_en LONGTEXT NOT NULL,
        content_ur LONGTEXT NOT NULL,
        category_id INT,
        image VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS revisions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        article_id INT NOT NULL,
        title_en TEXT NOT NULL,
        title_ur TEXT NOT NULL,
        content_en LONGTEXT NOT NULL,
        content_ur LONGTEXT NOT NULL,
        updated_by_user_id INT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS ad_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('banner', 'inline', 'popup') DEFAULT 'banner',
        code TEXT NOT NULL,
        location VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active'
    )",
    "CREATE TABLE IF NOT EXISTS subscription_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_en VARCHAR(100) NOT NULL,
        name_ur VARCHAR(100) NOT NULL,
        description_en TEXT,
        description_ur TEXT,
        price DECIMAL(10, 2) NOT NULL,
        duration_days INT NOT NULL,
        features JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        end_date TIMESTAMP NOT NULL,
        status ENUM('active', 'inactive', 'canceled', 'expired') DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT
    )"
];
foreach ($tables as $table) {
    if (!$conn->query($table)) {
        die("Error creating table: " . $conn->error . " Query: " . $table);
    }
}
$admin_check = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc();
if ($admin_check['count'] == 0) {
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, email, password, role, bio_en, bio_ur) VALUES ('admin', 'admin@newspaper.pk', '$admin_password', 'admin', 'Administrator of Pakistan Times. Oversees all operations.', 'پاکستان ٹائمز کے منتظم۔ تمام کارروائیوں کی نگرانی کرتے ہیں۔')");
    $categories = [
        ['National', 'قومی', 'national'],
        ['International', 'بین الاقوامی', 'international'],
        ['Sports', 'کھیل', 'sports'],
        ['Technology', 'ٹکنالوجی', 'technology'],
        ['Business', 'کاروبار', 'business'],
        ['Entertainment', 'تفریح', 'entertainment'],
        ['Opinion', 'رائے', 'opinion']
    ];
    foreach ($categories as $cat) {
        $stmt = $conn->prepare("INSERT INTO categories (name_en, name_ur, slug) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $cat[0], $cat[1], $cat[2]);
        $stmt->execute();
    }
    $admin_user_id = $conn->query("SELECT id FROM users WHERE username = 'admin'")->fetch_assoc()['id'];
    $sample_articles = [
        [
            'title_en' => 'Pakistan Economy Shows Growth in Q4 2024',
            'title_ur' => 'پاکستان کی معیشت 2024 کی چوتھی سہ ماہی میں بہتری کا مظاہرہ',
            'content_en' => 'Pakistan\'s economy has shown remarkable growth in the fourth quarter of 2024, with GDP increasing by 3.2%. The growth has been primarily driven by the industrial and services sectors. Experts predict continued positive trends in 2025 as the country implements structural reforms and improves its export capacity. The government has emphasized on fostering a business-friendly environment and attracting foreign investments.',
            'content_ur' => 'پاکستان کی معیشت نے 2024 کی چوتھی سہ ماہی میں قابل ذکر نمو دکھائی ہے، جی ڈی پی میں 3.2 فیصد اضافہ ہوا ہے۔ یہ نمو بنیادی طور پر صنعتی اور خدماتی شعبوں سے آئی ہے۔ ماہرین نے 2025 میں مثبت رجحان جاری رہنے کی پیش گوئی کی ہے کیونکہ ملک ساختی اصلاحات نافذ کر رہا ہے اور اپنی برآمدی صلاحیت بہتر بنا رہا ہے۔ حکومت نے کاروباری دوستانہ ماحول کو فروغ دینے اور غیر ملکی سرمایہ کاری کو راغب کرنے پر زور دیا ہے۔',
            'category_slug' => 'business',
            'is_breaking' => 1,
            'image' => 'https://via.placeholder.com/600x400/1a365d/ffffff?text=Economy+Growth',
            'is_sponsored' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'title_en' => 'Cricket World Cup Preparations Begin',
            'title_ur' => 'کرکٹ ورلڈ کپ کی تیاریاں شروع',
            'content_en' => 'The Pakistan cricket team has begun intensive preparations for the upcoming Cricket World Cup. The team management has announced a 15-member squad with a perfect blend of experienced and young players. Training camps are being organized across major cities, focusing on fitness and strategic gameplay. Fans are eagerly awaiting the tournament.',
            'content_ur' => 'پاکستان کرکٹ ٹیم نے آئندہ کرکٹ ورلڈ کپ کے لیے سخت تیاریاں شروع کر دی ہیں۔ ٹیم منیجمنٹ نے تجربہ کار اور نوجوان کھلاڑیوں کے بہترین امتزاج کے ساتھ 15 رکنی اسکواڈ کا اعلان کیا ہے۔ بڑے شہروں میں ٹریننگ کیمپس کا انعقاد کیا جا رہا ہے، جس میں فٹنس اور حکمت عملی پر توجہ دی جا رہی ہے۔ شائقین ٹورنامنٹ کا بے صبری سے انتظار کر رہے ہیں۔',
            'category_slug' => 'sports',
            'is_breaking' => 0,
            'image' => 'https://via.placeholder.com/600x400/e53e3e/ffffff?text=Cricket+Preparations',
            'is_sponsored' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'title_en' => 'Revolutionary AI Technology Launched in Pakistan',
            'title_ur' => 'پاکستان میں انقلابی AI ٹکنالوجی کا اجراء',
            'content_en' => 'A groundbreaking artificial intelligence platform has been launched in Pakistan, focusing on local language processing and cultural adaptation. The platform promises to revolutionize how businesses and individuals interact with technology in Pakistan. This initiative is expected to boost the local tech industry and create new opportunities.',
            'content_ur' => 'پاکستان میں ایک انقلابی مصنوعی ذہانت کا پلیٹفارم متعارف کرایا گیا ہے جو مقامی زبان کی پروسیسنگ اور ثقافتی موافقت پر توجہ مرکوز کرتا ہے۔ یہ پلیٹفارم پاکستان میں کاروبار اور افراد کے ٹکنالوجی کے ساتھ تعامل میں انقلاب لانے کا وعدہ کرتا ہے۔ اس اقدام سے مقامی ٹیک انڈسٹری کو فروغ ملنے اور نئے مواقع پیدا ہونے کی امید ہے۔',
            'category_slug' => 'technology',
            'is_breaking' => 1,
            'image' => 'https://via.placeholder.com/600x400/38a169/ffffff?text=AI+Technology',
            'is_sponsored' => 1,
            'published_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
        [
            'title_en' => 'Education Reform Initiative Shows Promising Results',
            'title_ur' => 'تعلیمی اصلاحات کی مہم امید افزا نتائج دکھا رہی ہے',
            'content_en' => 'The government\'s education reform initiative has shown promising results with increased literacy rates and improved school infrastructure. Digital learning tools are being introduced in rural areas to bridge the educational gap. This long-term project aims to ensure quality education for all citizens.',
            'content_ur' => 'حکومت کی تعلیمی اصلاحات کی مہم نے خواندگی کی شرح میں اضافے اور اسکولوں کے بنیادی ڈھانچے میں بہتری کے ساتھ امید افزا نتائج دکھائے ہیں۔ تعلیمی فرق کو ختم کرنے کے لیے دیہی علاقوں میں ڈیجیٹل تعلیمی ٹولز متعارف کرائے جا رہے ہیں۔ اس طویل المدتی منصوبے کا مقصد تمام شہریوں کے لیے معیاری تعلیم کو یقینی بنانا ہے۔',
            'category_slug' => 'national',
            'is_breaking' => 0,
            'image' => 'https://via.placeholder.com/600x400/1a365d/ffffff?text=Education+Reform',
            'is_sponsored' => 0,
            'published_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
        ]
    ];
    foreach ($sample_articles as $article) {
        $category_id_res = $conn->query("SELECT id FROM categories WHERE slug = '{$article['category_slug']}'")->fetch_assoc();
        $category_id = $category_id_res ? $category_id_res['id'] : null;
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $article['title_en'])));
        $stmt = $conn->prepare("INSERT INTO articles (title_en, title_ur, content_en, content_ur, category_id, author_id, is_breaking, is_sponsored, slug, image, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiissss", $article['title_en'], $article['title_ur'], $article['content_en'], $article['content_ur'], $category_id, $admin_user_id, $article['is_breaking'], $article['is_sponsored'], $slug, $article['image'], $article['published_at']);
        $stmt->execute();
    }
    $stmt = $conn->prepare("INSERT INTO subscription_plans (name_en, name_ur, description_en, description_ur, price, duration_days, features) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $features_premium_en = json_encode(['Ad-free experience', 'Exclusive content', 'Early access to articles']);
    $features_premium_ur = json_encode(['اشتہار سے پاک تجربہ', 'خصوصی مواد', 'مضامین تک قبل از وقت رسائی']);
    $price_premium = 9.99;
    $duration_premium = 30;
    $stmt->bind_param("ssssdis", 'Premium', 'پریمیم', 'Full access to all content, ad-free.', 'تمام مواد تک مکمل رسائی، اشتہار سے پاک۔', $price_premium, $duration_premium, $features_premium_en);
    $stmt->execute();
    $stmt->bind_param("ssssdis", 'Premium', 'پریمیم', 'Full access to all content, ad-free.', 'تمام مواد تک مکمل رسائی، اشتہار سے پاک۔', $price_premium, $duration_premium, $features_premium_ur);
    $stmt->execute();
}
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';
$_SESSION['lang'] = $lang;
function login($username, $password)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        log_activity($_SESSION['user_id'], 'user_login', 'User ' . $user['username'] . ' logged in.');
        return true;
    }
    return false;
}
function logout()
{
    if (isset($_SESSION['user_id'])) {
        log_activity($_SESSION['user_id'], 'user_logout', 'User ' . $_SESSION['username'] . ' logged out.');
    }
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}
function hasRole($role)
{
    return isLoggedIn() && $_SESSION['role'] === $role;
}
function canEdit()
{
    return hasRole('admin') || hasRole('editor');
}
function log_activity($user_id, $action, $details)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $action, $details);
    $stmt->execute();
}
function get_nested_comments($comments, $parent_id = null)
{
    $nested_comments = [];
    foreach ($comments as $comment) {
        if ($comment['parent_comment_id'] == $parent_id) {
            $children = get_nested_comments($comments, $comment['id']);
            if ($children) {
                $comment['replies'] = $children;
            }
            $nested_comments[] = $comment;
        }
    }
    return $nested_comments;
}
function is_subscribed($user_id)
{
    global $conn;
    if (!$user_id) return false;
    $stmt = $conn->prepare("SELECT COUNT(*) AS active_subscriptions FROM subscriptions WHERE user_id = ? AND status = 'active' AND end_date > NOW()");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['active_subscriptions'] > 0;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        if (login($_POST['username'], $_POST['password'])) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = ($lang === 'ur' ? 'غلط یوزرنیم یا پاس ورڈ' : 'Invalid credentials');
        }
    }
    if (isset($_POST['register'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        if (empty($username) || empty($email) || empty($_POST['password'])) {
            $error = ($lang === 'ur' ? 'تمام فیلڈز درکار ہیں' : 'All fields are required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = ($lang === 'ur' ? 'غلط ای میل فارمیٹ' : 'Invalid email format.');
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'public')");
            $stmt->bind_param("sss", $username, $email, $password);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'رجسٹریشن کامیاب۔ براہ کرم لاگ ان کریں۔' : 'Registration successful. Please login.');
                log_activity($conn->insert_id, 'user_register', 'New user ' . $username . ' registered.');
            } else {
                $error = ($lang === 'ur' ? 'رجسٹریشن ناکام۔ یوزرنیم یا ای میل پہلے سے موجود ہے۔' : 'Registration failed. Username or email already exists.');
            }
        }
    }
    if (isset($_POST['add_article']) && canEdit()) {
        $title_en = trim($_POST['title_en']);
        $title_ur = trim($_POST['title_ur']);
        $content_en = trim($_POST['content_en']);
        $content_ur = trim($_POST['content_ur']);
        $category_id = $_POST['category_id'];
        $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
        $is_sponsored = isset($_POST['is_sponsored']) ? 1 : 0;
        $published_at = $_POST['published_at'] ?: date('Y-m-d H:i:s');
        $seo_meta_title_en = trim($_POST['seo_meta_title_en'] ?? '');
        $seo_meta_title_ur = trim($_POST['seo_meta_title_ur'] ?? '');
        $seo_meta_description_en = trim($_POST['seo_meta_description_en'] ?? '');
        $seo_meta_description_ur = trim($_POST['seo_meta_description_ur'] ?? '');
        $seo_keywords_en = trim($_POST['seo_keywords_en'] ?? '');
        $seo_keywords_ur = trim($_POST['seo_keywords_ur'] ?? '');
        $image_path = null;
        if (empty($title_en) || empty($title_ur) || empty($content_en) || empty($content_ur) || empty($category_id)) {
            $error = ($lang === 'ur' ? 'تمام مطلوبہ فیلڈز پُر کریں' : 'Please fill all required fields.');
        } else {
            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $title_en)));
            $original_slug = $slug;
            $counter = 1;
            while (true) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM articles WHERE slug = ?");
                $check_slug_stmt->bind_param("s", $slug);
                $check_slug_stmt->execute();
                $check_slug_stmt->store_result();
                if ($check_slug_stmt->num_rows == 0) {
                    break;
                }
                $slug = $original_slug . '-' . $counter++;
            }
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_file_type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($image_file_type, $allowed_extensions)) {
                    $image_path = $target_dir . uniqid() . '.' . $image_file_type;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                        $image_path = null;
                        $error = ($lang === 'ur' ? 'تصویر اپ لوڈ کرنے میں ناکامی۔' : 'Failed to upload image.');
                    }
                } else {
                    $error = ($lang === 'ur' ? 'صرف JPG, JPEG, PNG, GIF فائلیں اجازت ہیں۔' : 'Only JPG, JPEG, PNG & GIF files are allowed.');
                }
            }
            $stmt = $conn->prepare("INSERT INTO articles (title_en, title_ur, content_en, content_ur, category_id, author_id, is_breaking, is_sponsored, slug, image, published_at, seo_meta_title_en, seo_meta_title_ur, seo_meta_description_en, seo_meta_description_ur, seo_keywords_en, seo_keywords_ur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiiissssssssss", $title_en, $title_ur, $content_en, $content_ur, $category_id, $_SESSION['user_id'], $is_breaking, $is_sponsored, $slug, $image_path, $published_at, $seo_meta_title_en, $seo_meta_title_ur, $seo_meta_description_en, $seo_meta_description_ur, $seo_keywords_en, $seo_keywords_ur);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'مضمون کامیابی سے شامل کیا گیا' : 'Article added successfully');
                log_activity($_SESSION['user_id'], 'add_article', 'Added article: ' . $title_en);
            } else {
                $error = ($lang === 'ur' ? 'مضمون شامل کرنے میں ناکامی' : 'Failed to add article');
            }
        }
    }
    if (isset($_POST['update_article']) && canEdit()) {
        $article_id_to_update = $_POST['article_id'];
        $title_en = trim($_POST['title_en']);
        $title_ur = trim($_POST['title_ur']);
        $content_en = trim($_POST['content_en']);
        $content_ur = trim($_POST['content_ur']);
        $category_id = $_POST['category_id'];
        $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
        $status = $_POST['status'];
        $is_sponsored = isset($_POST['is_sponsored']) ? 1 : 0;
        $published_at = $_POST['published_at'] ?: date('Y-m-d H:i:s');
        $seo_meta_title_en = trim($_POST['seo_meta_title_en'] ?? '');
        $seo_meta_title_ur = trim($_POST['seo_meta_title_ur'] ?? '');
        $seo_meta_description_en = trim($_POST['seo_meta_description_en'] ?? '');
        $seo_meta_description_ur = trim($_POST['seo_meta_description_ur'] ?? '');
        $seo_keywords_en = trim($_POST['seo_keywords_en'] ?? '');
        $seo_keywords_ur = trim($_POST['seo_keywords_ur'] ?? '');
        $image_path = $_POST['current_image'] ?? null;
        if (empty($title_en) || empty($title_ur) || empty($content_en) || empty($content_ur) || empty($category_id)) {
            $error = ($lang === 'ur' ? 'تمام مطلوبہ فیلڈز پُر کریں' : 'Please fill all required fields.');
        } else {
            $current_article_stmt = $conn->prepare("SELECT title_en, title_ur, content_en, content_ur FROM articles WHERE id = ?");
            $current_article_stmt->bind_param("i", $article_id_to_update);
            $current_article_stmt->execute();
            $current_article_result = $current_article_stmt->get_result();
            if ($current_article_data = $current_article_result->fetch_assoc()) {
                $revision_stmt = $conn->prepare("INSERT INTO revisions (article_id, title_en, title_ur, content_en, content_ur, updated_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
                $revision_stmt->bind_param("issssi", $article_id_to_update, $current_article_data['title_en'], $current_article_data['title_ur'], $current_article_data['content_en'], $current_article_data['content_ur'], $_SESSION['user_id']);
                $revision_stmt->execute();
            }
            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $title_en)));
            $original_slug = $slug;
            $counter = 1;
            while (true) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
                $check_slug_stmt->bind_param("si", $slug, $article_id_to_update);
                $check_slug_stmt->execute();
                $check_slug_stmt->store_result();
                if ($check_slug_stmt->num_rows == 0) {
                    break;
                }
                $slug = $original_slug . '-' . $counter++;
            }
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_file_type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($image_file_type, $allowed_extensions)) {
                    if ($image_path && file_exists($image_path)) {
                        unlink($image_path);
                    }
                    $image_path = $target_dir . uniqid() . '.' . $image_file_type;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                        $image_path = $_POST['current_image'] ?? null;
                        $error = ($lang === 'ur' ? 'تصویر اپ لوڈ کرنے میں ناکامی۔' : 'Failed to upload image.');
                    }
                } else {
                    $error = ($lang === 'ur' ? 'صرف JPG, JPEG, PNG, GIF فائلیں اجازت ہیں۔' : 'Only JPG, JPEG, PNG & GIF files are allowed.');
                }
            } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                $image_path = null;
            }
            $stmt = $conn->prepare("UPDATE articles SET title_en = ?, title_ur = ?, content_en = ?, content_ur = ?, category_id = ?, is_breaking = ?, status = ?, is_sponsored = ?, slug = ?, image = ?, published_at = ?, seo_meta_title_en = ?, seo_meta_title_ur = ?, seo_meta_description_en = ?, seo_meta_description_ur = ?, seo_keywords_en = ?, seo_keywords_ur = ? WHERE id = ?");
            $stmt->bind_param("ssssiiissssssssssi", $title_en, $title_ur, $content_en, $content_ur, $category_id, $is_breaking, $status, $is_sponsored, $slug, $image_path, $published_at, $seo_meta_title_en, $seo_meta_title_ur, $seo_meta_description_en, $seo_meta_description_ur, $seo_keywords_en, $seo_keywords_ur, $article_id_to_update);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'مضمون کامیابی سے اپ ڈیٹ کیا گیا' : 'Article updated successfully');
                log_activity($_SESSION['user_id'], 'update_article', 'Updated article ID: ' . $article_id_to_update . ' - ' . $title_en);
            } else {
                $error = ($lang === 'ur' ? 'مضمون اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update article');
            }
        }
    }
    if (isset($_POST['delete_article']) && canEdit()) {
        $article_id_to_delete = $_POST['article_id'];
        $stmt = $conn->prepare("SELECT image FROM articles WHERE id = ?");
        $stmt->bind_param("i", $article_id_to_delete);
        $stmt->execute();
        $result = $stmt->get_result();
        $article_data = $result->fetch_assoc();
        if ($article_data && $article_data['image'] && file_exists($article_data['image'])) {
            unlink($article_data['image']);
        }
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->bind_param("i", $article_id_to_delete);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'مضمون کامیابی سے حذف کر دیا گیا' : 'Article deleted successfully');
            log_activity($_SESSION['user_id'], 'delete_article', 'Deleted article ID: ' . $article_id_to_delete);
        } else {
            $error = ($lang === 'ur' ? 'مضمون حذف کرنے میں ناکامی' : 'Failed to delete article');
        }
    }
    if (isset($_POST['add_category']) && hasRole('admin')) {
        $name_en = trim($_POST['name_en']);
        $name_ur = trim($_POST['name_ur']);
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $name_en)));
        if (empty($name_en) || empty($name_ur) || empty($slug)) {
            $error = ($lang === 'ur' ? 'تمام کیٹگری فیلڈز درکار ہیں' : 'All category fields are required.');
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name_en, name_ur, slug) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name_en, $name_ur, $slug);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'کیٹگری کامیابی سے شامل کی گئی' : 'Category added successfully');
                log_activity($_SESSION['user_id'], 'add_category', 'Added category: ' . $name_en);
            } else {
                $error = ($lang === 'ur' ? 'کیٹگری شامل کرنے میں ناکامی۔ سلاگ پہلے سے موجود ہے۔' : 'Failed to add category. Slug might already exist.');
            }
        }
    }
    if (isset($_POST['update_category']) && hasRole('admin')) {
        $category_id_to_update = $_POST['category_id'];
        $name_en = trim($_POST['name_en']);
        $name_ur = trim($_POST['name_ur']);
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $name_en)));
        if (empty($name_en) || empty($name_ur) || empty($slug)) {
            $error = ($lang === 'ur' ? 'تمام کیٹگری فیلڈز درکار ہیں' : 'All category fields are required.');
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name_en = ?, name_ur = ?, slug = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name_en, $name_ur, $slug, $category_id_to_update);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'کیٹگری کامیابی سے اپ ڈیٹ کی گئی' : 'Category updated successfully');
                log_activity($_SESSION['user_id'], 'update_category', 'Updated category ID: ' . $category_id_to_update . ' - ' . $name_en);
            } else {
                $error = ($lang === 'ur' ? 'کیٹگری اپ ڈیٹ کرنے میں ناکامی۔ سلاگ پہلے سے موجود ہے۔' : 'Failed to update category. Slug might already exist.');
            }
        }
    }
    if (isset($_POST['delete_category']) && hasRole('admin')) {
        $category_id_to_delete = $_POST['category_id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id_to_delete);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'کیٹگری کامیابی سے حذف کر دی گئی' : 'Category deleted successfully');
            log_activity($_SESSION['user_id'], 'delete_category', 'Deleted category ID: ' . $category_id_to_delete);
        } else {
            $error = ($lang === 'ur' ? 'کیٹگری حذف کرنے میں ناکامی' : 'Failed to delete category');
        }
    }
    if (isset($_POST['update_comment_status']) && hasRole('admin')) {
        $comment_id_to_update = $_POST['comment_id'];
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE comments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $comment_id_to_update);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'تبصرہ کی حیثیت کامیابی سے اپ ڈیٹ کی گئی' : 'Comment status updated successfully');
            log_activity($_SESSION['user_id'], 'update_comment_status', 'Updated comment ID: ' . $comment_id_to_update . ' to ' . $new_status);
        } else {
            $error = ($lang === 'ur' ? 'تبصرہ کی حیثیت اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update comment status');
        }
    }
    if (isset($_POST['delete_comment']) && hasRole('admin')) {
        $comment_id_to_delete = $_POST['comment_id'];
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id_to_delete);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'تبصرہ کامیابی سے حذف کر دیا گیا' : 'Comment deleted successfully');
            log_activity($_SESSION['user_id'], 'delete_comment', 'Deleted comment ID: ' . $comment_id_to_delete);
        } else {
            $error = ($lang === 'ur' ? 'تبصرہ حذف کرنے میں ناکامی' : 'Failed to delete comment');
        }
    }
    if (isset($_POST['update_user_role']) && hasRole('admin')) {
        $user_id_to_update = $_POST['user_id'];
        $new_role = $_POST['role'];
        if ($user_id_to_update == $_SESSION['user_id'] && $new_role !== 'admin') {
            $error = ($lang === 'ur' ? 'آپ اپنی خود کی ایڈمن رول نہیں ہٹا سکتے۔' : 'You cannot demote yourself from admin role.');
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id_to_update);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'صارف کا کردار کامیابی سے اپ ڈیٹ کیا گیا' : 'User role updated successfully');
                log_activity($_SESSION['user_id'], 'update_user_role', 'Updated user ' . $user_id_to_update . ' role to ' . $new_role);
            } else {
                $error = ($lang === 'ur' ? 'صارف کا کردار اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update user role');
            }
        }
    }
    if (isset($_POST['delete_user']) && hasRole('admin')) {
        $user_id_to_delete = $_POST['user_id'];
        if ($user_id_to_delete == $_SESSION['user_id']) {
            $error = ($lang === 'ur' ? 'آپ اپنا اکاؤنٹ حذف نہیں کر سکتے۔' : 'You cannot delete your own account.');
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'صارف کامیابی سے حذف کر دیا گیا' : 'User deleted successfully');
                log_activity($_SESSION['user_id'], 'delete_user', 'Deleted user ID: ' . $user_id_to_delete);
            } else {
                $error = ($lang === 'ur' ? 'صارف حذف کرنے میں ناکامی' : 'Failed to delete user');
            }
        }
    }
    if (isset($_POST['add_comment']) && isLoggedIn()) {
        $article_id = $_POST['article_id'];
        $comment = trim($_POST['comment']);
        $parent_comment_id = $_POST['parent_comment_id'] ?? null;
        $user_id = $_SESSION['user_id'];
        $name = $_SESSION['username'];
        $email = '';
        if (empty($comment)) {
            $error = ($lang === 'ur' ? 'تبصرہ خالی نہیں ہو سکتا۔' : 'Comment cannot be empty.');
        } else {
            $stmt = $conn->prepare("INSERT INTO comments (article_id, user_id, name, comment, parent_comment_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iissi", $article_id, $user_id, $name, $comment, $parent_comment_id);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'تبصرہ کامیابی سے شامل کیا گیا اور منظوری کا منتظر ہے۔' : 'Comment added successfully and awaiting approval.');
                log_activity($user_id, 'add_comment', 'Added comment to article ID: ' . $article_id);
            } else {
                $error = ($lang === 'ur' ? 'تبصرہ شامل کرنے میں ناکامی۔' : 'Failed to add comment.');
            }
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?view=article&id=$article_id&lang=$lang");
        exit;
    }
    if (isset($_POST['like_article']) && isLoggedIn()) {
        $article_id = $_POST['article_id'];
        $user_id = $_SESSION['user_id'];
        $check_like_stmt = $conn->prepare("SELECT * FROM article_likes WHERE user_id = ? AND article_id = ?");
        $check_like_stmt->bind_param("ii", $user_id, $article_id);
        $check_like_stmt->execute();
        $check_like_result = $check_like_stmt->get_result();
        if ($check_like_result->num_rows == 0) {
            $conn->begin_transaction();
            try {
                $insert_like_stmt = $conn->prepare("INSERT INTO article_likes (user_id, article_id) VALUES (?, ?)");
                $insert_like_stmt->bind_param("ii", $user_id, $article_id);
                $insert_like_stmt->execute();
                $update_article_likes_stmt = $conn->prepare("UPDATE articles SET likes = likes + 1 WHERE id = ?");
                $update_article_likes_stmt->bind_param("i", $article_id);
                $update_article_likes_stmt->execute();
                $conn->commit();
                echo json_encode(['success' => true, 'action' => 'liked']);
                log_activity($user_id, 'like_article', 'Liked article ID: ' . $article_id);
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
            }
        } else {
            $conn->begin_transaction();
            try {
                $delete_like_stmt = $conn->prepare("DELETE FROM article_likes WHERE user_id = ? AND article_id = ?");
                $delete_like_stmt->bind_param("ii", $user_id, $article_id);
                $delete_like_stmt->execute();
                $update_article_likes_stmt = $conn->prepare("UPDATE articles SET likes = GREATEST(0, likes - 1) WHERE id = ?");
                $update_article_likes_stmt->bind_param("i", $article_id);
                $update_article_likes_stmt->execute();
                $conn->commit();
                echo json_encode(['success' => true, 'action' => 'unliked']);
                log_activity($user_id, 'unlike_article', 'Unliked article ID: ' . $article_id);
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
            }
        }
        exit;
    }
    if (isset($_POST['follow_user']) && isLoggedIn()) {
        $followed_id = $_POST['followed_id'];
        $follower_id = $_SESSION['user_id'];
        if ($followed_id == $follower_id) {
            echo json_encode(['success' => false, 'message' => ($lang === 'ur' ? 'آپ اپنے آپ کو فالو نہیں کر سکتے۔' : 'You cannot follow yourself.')]);
            exit;
        }
        $check_follow_stmt = $conn->prepare("SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?");
        $check_follow_stmt->bind_param("ii", $follower_id, $followed_id);
        $check_follow_stmt->execute();
        $check_follow_result = $check_follow_stmt->get_result();
        if ($check_follow_result->num_rows == 0) {
            $insert_follow_stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
            $insert_follow_stmt->bind_param("ii", $follower_id, $followed_id);
            if ($insert_follow_stmt->execute()) {
                echo json_encode(['success' => true, 'action' => 'followed']);
                log_activity($follower_id, 'follow_user', 'Followed user ID: ' . $followed_id);
            } else {
                echo json_encode(['success' => false, 'message' => ($lang === 'ur' ? 'فالو کرنے میں ناکامی' : 'Failed to follow user.')]);
            }
        } else {
            $delete_follow_stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
            $delete_follow_stmt->bind_param("ii", $follower_id, $followed_id);
            if ($delete_follow_stmt->execute()) {
                echo json_encode(['success' => true, 'action' => 'unfollowed']);
                log_activity($follower_id, 'unfollow_user', 'Unfollowed user ID: ' . $followed_id);
            } else {
                echo json_encode(['success' => false, 'message' => ($lang === 'ur' ? 'انفالو کرنے میں ناکامی' : 'Failed to unfollow user.')]);
            }
        }
        exit;
    }
    if (isset($_POST['submit_user_article']) && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $title_en = trim($_POST['title_en']);
        $title_ur = trim($_POST['title_ur']);
        $content_en = trim($_POST['content_en']);
        $content_ur = trim($_POST['content_ur']);
        $category_id = $_POST['category_id'];
        $image_path = null;
        if (empty($title_en) || empty($title_ur) || empty($content_en) || empty($content_ur) || empty($category_id)) {
            $error = ($lang === 'ur' ? 'تمام مطلوبہ فیلڈز پُر کریں' : 'Please fill all required fields.');
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "uploads/submissions/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $image_file_type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($image_file_type, $allowed_extensions)) {
                    $image_path = $target_dir . uniqid() . '.' . $image_file_type;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                        $image_path = null;
                        $error = ($lang === 'ur' ? 'تصویر اپ لوڈ کرنے میں ناکامی۔' : 'Failed to upload image.');
                    }
                } else {
                    $error = ($lang === 'ur' ? 'صرف JPG, JPEG, PNG, GIF فائلیں اجازت ہیں۔' : 'Only JPG, JPEG, PNG & GIF files are allowed.');
                }
            }
            $stmt = $conn->prepare("INSERT INTO user_submissions (user_id, title_en, title_ur, content_en, content_ur, category_id, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issssis", $user_id, $title_en, $title_ur, $content_en, $content_ur, $category_id, $image_path);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'آپ کا مضمون کامیابی سے جمع کر دیا گیا ہے اور منظوری کا منتظر ہے۔' : 'Your article has been submitted successfully and is awaiting approval.');
                log_activity($user_id, 'submit_article', 'Submitted article for review: ' . $title_en);
            } else {
                $error = ($lang === 'ur' ? 'مضمون جمع کرنے میں ناکامی۔' : 'Failed to submit article.');
            }
        }
    }
    if (isset($_POST['update_submission_status']) && hasRole('admin')) {
        $submission_id = $_POST['submission_id'];
        $new_status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE user_submissions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $submission_id);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'جمع کردہ مضمون کی حیثیت کامیابی سے اپ ڈیٹ کی گئی' : 'Submission status updated successfully');
            log_activity($_SESSION['user_id'], 'update_submission_status', 'Updated submission ID: ' . $submission_id . ' to ' . $new_status);
        } else {
            $error = ($lang === 'ur' ? 'جمع کردہ مضمون کی حیثیت اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update submission status');
        }
    }
    if (isset($_POST['publish_submission']) && hasRole('admin')) {
        $submission_id = $_POST['submission_id'];
        $stmt_select = $conn->prepare("SELECT * FROM user_submissions WHERE id = ?");
        $stmt_select->bind_param("i", $submission_id);
        $stmt_select->execute();
        $submission = $stmt_select->get_result()->fetch_assoc();
        if ($submission) {
            $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9\s]/', '', $submission['title_en'])));
            $original_slug = $slug;
            $counter = 1;
            while (true) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM articles WHERE slug = ?");
                $check_slug_stmt->bind_param("s", $slug);
                $check_slug_stmt->execute();
                $check_slug_stmt->store_result();
                if ($check_slug_stmt->num_rows == 0) {
                    break;
                }
                $slug = $original_slug . '-' . $counter++;
            }
            $stmt_insert_article = $conn->prepare("INSERT INTO articles (title_en, title_ur, content_en, content_ur, category_id, author_id, image, status, slug, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', ?, NOW())");
            $stmt_insert_article->bind_param("ssssiiss", $submission['title_en'], $submission['title_ur'], $submission['content_en'], $submission['content_ur'], $submission['category_id'], $submission['user_id'], $submission['image'], $slug);
            if ($stmt_insert_article->execute()) {
                $stmt_update_submission = $conn->prepare("UPDATE user_submissions SET status = 'approved' WHERE id = ?");
                $stmt_update_submission->bind_param("i", $submission_id);
                $stmt_update_submission->execute();
                $success = ($lang === 'ur' ? 'مضمون کامیابی سے شائع کیا گیا!' : 'Article published successfully!');
                log_activity($_SESSION['user_id'], 'publish_submission', 'Published submission ID: ' . $submission_id);
            } else {
                $error = ($lang === 'ur' ? 'مضمون شائع کرنے میں ناکامی۔' : 'Failed to publish article.');
            }
        } else {
            $error = ($lang === 'ur' ? 'جمع کردہ مضمون نہیں ملا۔' : 'Submission not found.');
        }
    }
    if (isset($_POST['delete_submission']) && hasRole('admin')) {
        $submission_id = $_POST['submission_id'];
        $stmt_select = $conn->prepare("SELECT image FROM user_submissions WHERE id = ?");
        $stmt_select->bind_param("i", $submission_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $submission_data = $result->fetch_assoc();
        if ($submission_data && $submission_data['image'] && file_exists($submission_data['image'])) {
            unlink($submission_data['image']);
        }
        $stmt = $conn->prepare("DELETE FROM user_submissions WHERE id = ?");
        $stmt->bind_param("i", $submission_id);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'جمع کردہ مضمون کامیابی سے حذف کر دیا گیا' : 'Submission deleted successfully');
            log_activity($_SESSION['user_id'], 'delete_submission', 'Deleted submission ID: ' . $submission_id);
        } else {
            $error = ($lang === 'ur' ? 'جمع کردہ مضمون حذف کرنے میں ناکامی' : 'Failed to delete submission');
        }
    }
    if (isset($_POST['update_profile']) && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $bio_en = trim($_POST['bio_en']);
        $bio_ur = trim($_POST['bio_ur']);
        $social_links = json_encode([
            'facebook' => $_POST['social_facebook'] ?? '',
            'twitter' => $_POST['social_twitter'] ?? '',
            'linkedin' => $_POST['social_linkedin'] ?? ''
        ]);
        $avatar_path = $_POST['current_avatar'] ?? null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/avatars/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_file_type = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($image_file_type, $allowed_extensions)) {
                if ($avatar_path && file_exists($avatar_path)) {
                    unlink($avatar_path);
                }
                $avatar_path = $target_dir . uniqid() . '.' . $image_file_type;
                if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                    $avatar_path = $_POST['current_avatar'] ?? null;
                    $error = ($lang === 'ur' ? 'اواتار اپ لوڈ کرنے میں ناکامی۔' : 'Failed to upload avatar.');
                }
            } else {
                $error = ($lang === 'ur' ? 'صرف JPG, JPEG, PNG, GIF فائلیں اجازت ہیں۔' : 'Only JPG, JPEG, PNG & GIF files are allowed.');
            }
        } elseif (isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1') {
            if ($avatar_path && file_exists($avatar_path)) {
                unlink($avatar_path);
            }
            $avatar_path = null;
        }
        $stmt = $conn->prepare("UPDATE users SET avatar = ?, bio_en = ?, bio_ur = ?, social_links = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $avatar_path, $bio_en, $bio_ur, $social_links, $user_id);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'پروفائل کامیابی سے اپ ڈیٹ کیا گیا' : 'Profile updated successfully');
            log_activity($user_id, 'update_profile', 'Updated profile for user ID: ' . $user_id);
        } else {
            $error = ($lang === 'ur' ? 'پروفائل اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update profile');
        }
    }
    if (isset($_POST['add_ad_unit']) && hasRole('admin')) {
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $code = trim($_POST['code']);
        $location = trim($_POST['location']);
        $status = $_POST['status'];
        if (empty($name) || empty($code) || empty($location)) {
            $error = ($lang === 'ur' ? 'تمام اشتہار یونٹ فیلڈز درکار ہیں' : 'All ad unit fields are required.');
        } else {
            $stmt = $conn->prepare("INSERT INTO ad_units (name, type, code, location, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $type, $code, $location, $status);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'اشتہار یونٹ کامیابی سے شامل کیا گیا' : 'Ad unit added successfully');
                log_activity($_SESSION['user_id'], 'add_ad_unit', 'Added ad unit: ' . $name);
            } else {
                $error = ($lang === 'ur' ? 'اشتہار یونٹ شامل کرنے میں ناکامی' : 'Failed to add ad unit');
            }
        }
    }
    if (isset($_POST['update_ad_unit']) && hasRole('admin')) {
        $ad_id = $_POST['ad_id'];
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $code = trim($_POST['code']);
        $location = trim($_POST['location']);
        $status = $_POST['status'];
        if (empty($name) || empty($code) || empty($location)) {
            $error = ($lang === 'ur' ? 'تمام اشتہار یونٹ فیلڈز درکار ہیں' : 'All ad unit fields are required.');
        } else {
            $stmt = $conn->prepare("UPDATE ad_units SET name = ?, type = ?, code = ?, location = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $name, $type, $code, $location, $status, $ad_id);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'اشتہار یونٹ کامیابی سے اپ ڈیٹ کیا گیا' : 'Ad unit updated successfully');
                log_activity($_SESSION['user_id'], 'update_ad_unit', 'Updated ad unit ID: ' . $ad_id . ' - ' . $name);
            } else {
                $error = ($lang === 'ur' ? 'اشتہار یونٹ اپ ڈیٹ کرنے میں ناکامی' : 'Failed to update ad unit');
            }
        }
    }
    if (isset($_POST['delete_ad_unit']) && hasRole('admin')) {
        $ad_id = $_POST['ad_id'];
        $stmt = $conn->prepare("DELETE FROM ad_units WHERE id = ?");
        $stmt->bind_param("i", $ad_id);
        if ($stmt->execute()) {
            $success = ($lang === 'ur' ? 'اشتہار یونٹ کامیابی سے حذف کر دیا گیا' : 'Ad unit deleted successfully');
            log_activity($_SESSION['user_id'], 'delete_ad_unit', 'Deleted ad unit ID: ' . $ad_id);
        } else {
            $error = ($lang === 'ur' ? 'اشتہار یونٹ حذف کرنے میں ناکامی' : 'Failed to delete ad unit');
        }
    }
    if (isset($_POST['add_poll']) && hasRole('admin')) {
        $question_en = trim($_POST['question_en']);
        $question_ur = trim($_POST['question_ur']);
        $options_en = json_encode(array_values(array_filter($_POST['options_en'])));
        $options_ur = json_encode(array_values(array_filter($_POST['options_ur'])));
        $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        if (empty($question_en) || empty($question_ur) || empty(json_decode($options_en)) || empty(json_decode($options_ur))) {
            $error = ($lang === 'ur' ? 'تمام پول فیلڈز اور کم از کم ایک آپشن درکار ہیں۔' : 'All poll fields and at least one option are required.');
        } else {
            $stmt = $conn->prepare("INSERT INTO polls (question_en, question_ur, options_en, options_ur, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $question_en, $question_ur, $options_en, $options_ur, $expires_at);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'پول کامیابی سے شامل کیا گیا' : 'Poll added successfully');
                log_activity($_SESSION['user_id'], 'add_poll', 'Added poll: ' . $question_en);
            } else {
                $error = ($lang === 'ur' ? 'پول شامل کرنے میں ناکامی' : 'Failed to add poll');
            }
        }
    }
    if (isset($_POST['vote_poll']) && isLoggedIn()) {
        $poll_id = $_POST['poll_id'];
        $option_index = $_POST['option_index'];
        $user_id = $_SESSION['user_id'];
        $check_vote_stmt = $conn->prepare("SELECT * FROM poll_votes WHERE poll_id = ? AND user_id = ?");
        $check_vote_stmt->bind_param("ii", $poll_id, $user_id);
        $check_vote_stmt->execute();
        $check_vote_result = $check_vote_stmt->get_result();
        if ($check_vote_result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO poll_votes (poll_id, user_id, option_index) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $poll_id, $user_id, $option_index);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => ($lang === 'ur' ? 'آپ کا ووٹ کامیابی سے درج ہو گیا ہے۔' : 'Your vote has been cast successfully.')]);
                log_activity($user_id, 'vote_poll', 'Voted in poll ID: ' . $poll_id . ' for option ' . $option_index);
            } else {
                echo json_encode(['success' => false, 'message' => ($lang === 'ur' ? 'ووٹ ڈالنے میں ناکامی۔' : 'Failed to cast vote.')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => ($lang === 'ur' ? 'آپ پہلے ہی اس پول میں ووٹ دے چکے ہیں۔' : 'You have already voted in this poll.')]);
        }
        exit;
    }
    if (isset($_GET['action']) && $_GET['action'] === 'get_poll_results') {
        $poll_id = $_GET['poll_id'];
        $poll_options_query = $conn->prepare("SELECT options_en FROM polls WHERE id = ?");
        $poll_options_query->bind_param("i", $poll_id);
        $poll_options_query->execute();
        $options_data = $poll_options_query->get_result()->fetch_assoc();
        $options_count = count(json_decode($options_data['options_en'], true));
        $results = [];
        $total_votes = 0;
        for ($i = 0; $i < $options_count; $i++) {
            $stmt_votes = $conn->prepare("SELECT COUNT(*) as votes FROM poll_votes WHERE poll_id = ? AND option_index = ?");
            $stmt_votes->bind_param("ii", $poll_id, $i);
            $stmt_votes->execute();
            $vote_count = $stmt_votes->get_result()->fetch_assoc()['votes'];
            $results[] = ['option_index' => $i, 'votes' => $vote_count];
            $total_votes += $vote_count;
        }
        echo json_encode(['success' => true, 'results' => $results, 'total_votes' => $total_votes]);
        exit;
    }
    if (isset($_POST['subscribe_newsletter'])) {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = ($lang === 'ur' ? 'غلط ای میل فارمیٹ۔' : 'Invalid email format.');
        } else {
            $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $success = ($lang === 'ur' ? 'آپ کامیابی سے نیوز لیٹر کو سبسکرائب کر چکے ہیں۔' : 'You have successfully subscribed to our newsletter.');
                log_activity(null, 'newsletter_subscribe', 'New newsletter subscription: ' . $email);
            } else {
                $error = ($lang === 'ur' ? 'سبسکرپشن ناکام۔ یہ ای میل پہلے ہی سبسکرائب ہے۔' : 'Subscription failed. This email is already subscribed.');
            }
        }
    }
    if (isset($_POST['import'])) {
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == UPLOAD_ERR_OK) {
            $file_content = file_get_contents($_FILES['backup_file']['tmp_name']);
            $data = json_decode($file_content, true);
            if ($data) {
                $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                $conn->query("TRUNCATE TABLE article_tags");
                $conn->query("TRUNCATE TABLE comments");
                $conn->query("TRUNCATE TABLE article_likes");
                $conn->query("TRUNCATE TABLE followers");
                $conn->query("TRUNCATE TABLE newsletter_subscribers");
                $conn->query("TRUNCATE TABLE poll_votes");
                $conn->query("TRUNCATE TABLE polls");
                $conn->query("TRUNCATE TABLE user_submissions");
                $conn->query("TRUNCATE TABLE revisions");
                $conn->query("TRUNCATE TABLE activity_logs");
                $conn->query("TRUNCATE TABLE ad_units");
                $conn->query("TRUNCATE TABLE subscriptions");
                $conn->query("TRUNCATE TABLE subscription_plans");
                $conn->query("TRUNCATE TABLE articles");
                $conn->query("TRUNCATE TABLE categories");
                $conn->query("TRUNCATE TABLE users");
                $conn->query("TRUNCATE TABLE tags");
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                if (!empty($data['users'])) {
                    foreach ($data['users'] as $user) {
                        $stmt = $conn->prepare("INSERT INTO users (id, username, email, password, role, avatar, bio_en, bio_ur, social_links, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $social_links = json_encode($user['social_links'] ?? null);
                        $stmt->bind_param("isssssssss", $user['id'], $user['username'], $user['email'], $user['password'], $user['role'], $user['avatar'], $user['bio_en'], $user['bio_ur'], $social_links, $user['created_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['categories'])) {
                    foreach ($data['categories'] as $category) {
                        $stmt = $conn->prepare("INSERT INTO categories (id, name_en, name_ur, slug) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isss", $category['id'], $category['name_en'], $category['name_ur'], $category['slug']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['articles'])) {
                    foreach ($data['articles'] as $article) {
                        $stmt = $conn->prepare("INSERT INTO articles (id, title_en, title_ur, content_en, content_ur, category_id, author_id, image, is_breaking, views, likes, status, is_sponsored, slug, published_at, created_at, updated_at, seo_meta_title_en, seo_meta_title_ur, seo_meta_description_en, seo_meta_description_ur, seo_keywords_en, seo_keywords_ur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param(
                            "issssiiisiissssssssssss",
                            $article['id'],
                            $article['title_en'],
                            $article['title_ur'],
                            $article['content_en'],
                            $article['content_ur'],
                            $article['category_id'],
                            $article['author_id'],
                            $article['image'],
                            $article['is_breaking'],
                            $article['views'],
                            $article['likes'],
                            $article['status'],
                            $article['is_sponsored'],
                            $article['slug'],
                            $article['published_at'],
                            $article['created_at'],
                            $article['updated_at'],
                            $article['seo_meta_title_en'],
                            $article['seo_meta_title_ur'],
                            $article['seo_meta_description_en'],
                            $article['seo_meta_description_ur'],
                            $article['seo_keywords_en'],
                            $article['seo_keywords_ur']
                        );
                        $stmt->execute();
                    }
                }
                if (!empty($data['comments'])) {
                    foreach ($data['comments'] as $comment) {
                        $stmt = $conn->prepare("INSERT INTO comments (id, article_id, user_id, name, email, comment, parent_comment_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iiisssiss", $comment['id'], $comment['article_id'], $comment['user_id'], $comment['name'], $comment['email'], $comment['comment'], $comment['parent_comment_id'], $comment['status'], $comment['created_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['tags'])) {
                    foreach ($data['tags'] as $tag) {
                        $stmt = $conn->prepare("INSERT INTO tags (id, name) VALUES (?, ?)");
                        $stmt->bind_param("is", $tag['id'], $tag['name']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['article_tags'])) {
                    foreach ($data['article_tags'] as $article_tag) {
                        $stmt = $conn->prepare("INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $article_tag['article_id'], $article_tag['tag_id']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['article_likes'])) {
                    foreach ($data['article_likes'] as $like) {
                        $stmt = $conn->prepare("INSERT INTO article_likes (user_id, article_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $like['user_id'], $like['article_id']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['followers'])) {
                    foreach ($data['followers'] as $follow) {
                        $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
                        $stmt->bind_param("ii", $follow['follower_id'], $follow['followed_id']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['newsletter_subscribers'])) {
                    foreach ($data['newsletter_subscribers'] as $subscriber) {
                        $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (id, email, subscribed_at) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $subscriber['id'], $subscriber['email'], $subscriber['subscribed_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['polls'])) {
                    foreach ($data['polls'] as $poll) {
                        $stmt = $conn->prepare("INSERT INTO polls (id, question_en, question_ur, options_en, options_ur, created_at, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("issssss", $poll['id'], $poll['question_en'], $poll['question_ur'], json_encode($poll['options_en']), json_encode($poll['options_ur']), $poll['created_at'], $poll['expires_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['poll_votes'])) {
                    foreach ($data['poll_votes'] as $vote) {
                        $stmt = $conn->prepare("INSERT INTO poll_votes (poll_id, user_id, option_index, voted_at) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("iiis", $vote['poll_id'], $vote['user_id'], $vote['option_index'], $vote['voted_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['user_submissions'])) {
                    foreach ($data['user_submissions'] as $submission) {
                        $stmt = $conn->prepare("INSERT INTO user_submissions (id, user_id, title_en, title_ur, content_en, content_ur, category_id, image, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iissssisss", $submission['id'], $submission['user_id'], $submission['title_en'], $submission['title_ur'], $submission['content_en'], $submission['content_ur'], $submission['category_id'], $submission['image'], $submission['status'], $submission['submitted_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['revisions'])) {
                    foreach ($data['revisions'] as $revision) {
                        $stmt = $conn->prepare("INSERT INTO revisions (id, article_id, title_en, title_ur, content_en, content_ur, updated_by_user_id, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iissssis", $revision['id'], $revision['article_id'], $revision['title_en'], $revision['title_ur'], $revision['content_en'], $revision['content_ur'], $revision['updated_by_user_id'], $revision['updated_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['activity_logs'])) {
                    foreach ($data['activity_logs'] as $log) {
                        $stmt = $conn->prepare("INSERT INTO activity_logs (id, user_id, action, details, timestamp) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("iissa", $log['id'], $log['user_id'], $log['action'], $log['details'], $log['timestamp']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['ad_units'])) {
                    foreach ($data['ad_units'] as $ad) {
                        $stmt = $conn->prepare("INSERT INTO ad_units (id, name, type, code, location, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("isssss", $ad['id'], $ad['name'], $ad['type'], $ad['code'], $ad['location'], $ad['status']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['subscription_plans'])) {
                    foreach ($data['subscription_plans'] as $plan) {
                        $stmt = $conn->prepare("INSERT INTO subscription_plans (id, name_en, name_ur, description_en, description_ur, price, duration_days, features, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("issssdiss", $plan['id'], $plan['name_en'], $plan['name_ur'], $plan['description_en'], $plan['description_ur'], $plan['price'], $plan['duration_days'], json_encode($plan['features']), $plan['created_at']);
                        $stmt->execute();
                    }
                }
                if (!empty($data['subscriptions'])) {
                    foreach ($data['subscriptions'] as $sub) {
                        $stmt = $conn->prepare("INSERT INTO subscriptions (id, user_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("iiisss", $sub['id'], $sub['user_id'], $sub['plan_id'], $sub['start_date'], $sub['end_date'], $sub['status']);
                        $stmt->execute();
                    }
                }
                $success = ($lang === 'ur' ? 'ڈیٹا کامیابی سے بحال کر دیا گیا!' : 'Data restored successfully!');
                log_activity($_SESSION['user_id'] ?? null, 'data_import', 'Database data restored from backup.');
            } else {
                $error = ($lang === 'ur' ? 'غلط بیک اپ فائل فارمیٹ!' : 'Invalid backup file format!');
            }
        } else {
            $error = ($lang === 'ur' ? 'براہ کرم بیک اپ فائل منتخب کریں۔' : 'Please select a backup file.');
        }
    }
    if (isset($_GET['action']) && $_GET['action'] === 'get_revisions' && canEdit()) {
        $article_id = $_GET['article_id'];
        $stmt = $conn->prepare("SELECT r.*, u.username FROM revisions r LEFT JOIN users u ON r.updated_by_user_id = u.id WHERE r.article_id = ? ORDER BY r.updated_at DESC");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $revisions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'revisions' => $revisions]);
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'revert_revision' && canEdit()) {
        $article_id = $_POST['article_id'];
        $revision_id = $_POST['revision_id'];
        $stmt = $conn->prepare("SELECT title_en, title_ur, content_en, content_ur FROM revisions WHERE id = ?");
        $stmt->bind_param("i", $revision_id);
        $stmt->execute();
        $revision_data = $stmt->get_result()->fetch_assoc();
        if ($revision_data) {
            $update_stmt = $conn->prepare("UPDATE articles SET title_en = ?, title_ur = ?, content_en = ?, content_ur = ? WHERE id = ?");
            $update_stmt->bind_param("ssssi", $revision_data['title_en'], $revision_data['title_ur'], $revision_data['content_en'], $revision_data['content_ur'], $article_id);
            if ($update_stmt->execute()) {
                $success = ($lang === 'ur' ? 'مضمون کامیابی سے نظرثانی پر واپس کر دیا گیا!' : 'Article successfully reverted to revision!');
                log_activity($_SESSION['user_id'], 'revert_revision', 'Reverted article ID: ' . $article_id . ' to revision ID: ' . $revision_id);
            } else {
                $error = ($lang === 'ur' ? 'نظرثانی پر واپس کرنے میں ناکامی۔' : 'Failed to revert to revision.');
            }
        } else {
            $error = ($lang === 'ur' ? 'نظرثانی نہیں ملی۔' : 'Revision not found.');
        }
    }
    if (isset($_GET['action']) && $_GET['action'] === 'check_broken_links' && hasRole('admin')) {
        header('Content-Type: application/json');
        function check_url_exists($url)
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($http_code >= 200 && $http_code < 400);
        }
        $broken_links = [];
        $articles_query = $conn->query("SELECT id, title_en, content_en, content_ur FROM articles");
        while ($article = $articles_query->fetch_assoc()) {
            $content_en = $article['content_en'];
            $content_ur = $article['content_ur'];
            $article_id = $article['id'];
            $article_title_en = $article['title_en'];
            preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/', $content_en, $matches_en);
            preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/', $content_ur, $matches_ur);
            $all_urls = array_merge($matches_en[1], $matches_ur[1]);
            foreach (array_unique($all_urls) as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL) && !check_url_exists($url)) {
                    $broken_links[] = [
                        'article_id' => $article_id,
                        'article_title_en' => $article_title_en,
                        'url' => $url
                    ];
                }
            }
        }
        echo json_encode(['success' => true, 'broken_links' => $broken_links]);
        exit;
    }
}
if (isset($_GET['logout'])) {
    logout();
}
if (isset($_GET['action']) && $_GET['action'] === 'export' && hasRole('admin')) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="newspaper_backup_' . date('Y-m-d') . '.json"');
    $tables_to_export = [
        'users',
        'categories',
        'articles',
        'comments',
        'tags',
        'article_tags',
        'article_likes',
        'followers',
        'newsletter_subscribers',
        'polls',
        'poll_votes',
        'user_submissions',
        'revisions',
        'activity_logs',
        'ad_units',
        'subscription_plans',
        'subscriptions'
    ];
    $export_data = [];
    foreach ($tables_to_export as $table_name) {
        $result = $conn->query("SELECT * FROM $table_name");
        $export_data[$table_name] = [];
        while ($row = $result->fetch_assoc()) {
            if ($table_name === 'users' && isset($row['social_links'])) {
                $row['social_links'] = json_decode($row['social_links']);
            }
            if ($table_name === 'polls') {
                $row['options_en'] = json_decode($row['options_en']);
                $row['options_ur'] = json_decode($row['options_ur']);
            }
            if ($table_name === 'subscription_plans' && isset($row['features'])) {
                $row['features'] = json_decode($row['features']);
            }
            $export_data[$table_name][] = $row;
        }
    }
    echo json_encode($export_data, JSON_PRETTY_PRINT);
    exit;
}
$view = $_GET['view'] ?? 'home';
$article_id = $_GET['id'] ?? null;
if ($view === 'article' && $article_id) {
    $stmt_views = $conn->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
    $stmt_views->bind_param("i", $article_id);
    $stmt_views->execute();
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $lang === 'ur' ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Yasin Ullah, Pakistan">
    <?php
    $current_meta_title = ($lang === 'ur' ? 'پاکستان ٹائمز - تازہ خبریں، اردو اور انگریزی میں' : 'Pakistan Times - Latest News in Urdu and English');
    $current_meta_description = "Complete Pakistani Newspaper - Latest News in Urdu and English. Breaking news, sports, technology, and business updates from Pakistan.";
    $current_meta_keywords = "Pakistan News, Urdu News, English News, Breaking News, Sports, Politics, Technology, Business, Entertainment, Opinion, Pakistan Times, Yasin Ullah";
    if ($view === 'article' && $article_id) {
        $stmt_seo = $conn->prepare("SELECT seo_meta_title_en, seo_meta_title_ur, seo_meta_description_en, seo_meta_description_ur, seo_keywords_en, seo_keywords_ur FROM articles WHERE id = ?");
        $stmt_seo->bind_param("i", $article_id);
        $stmt_seo->execute();
        $seo_data = $stmt_seo->get_result()->fetch_assoc();
        if ($seo_data) {
            $current_meta_title = $lang === 'ur' ? ($seo_data['seo_meta_title_ur'] ?: $seo_data['seo_meta_title_en']) : ($seo_data['seo_meta_title_en'] ?: $seo_data['seo_meta_title_ur']);
            $current_meta_description = $lang === 'ur' ? ($seo_data['seo_meta_description_ur'] ?: $seo_data['seo_meta_description_en']) : ($seo_data['seo_meta_description_en'] ?: $seo_data['seo_meta_description_ur']);
            $current_meta_keywords = $lang === 'ur' ? ($seo_data['seo_keywords_ur'] ?: $seo_data['seo_keywords_en']) : ($seo_data['seo_keywords_en'] ?: $seo_data['seo_keywords_ur']);
        }
    }
    ?>
    <title><?= htmlspecialchars($current_meta_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($current_meta_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($current_meta_keywords) ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Urdu:wght@300;400;500;700&family=Roboto:wght@300;400;500;700&display=swap');

        :root {
            --primary-color: #1a365d;
            --secondary-color: #e53e3e;
            --accent-color: #38a169;
            --text-color: #2d3748;
            --bg-color: #ffffff;
            --card-bg: #f7fafc;
            --border-color: #e2e8f0;
            --heading-color: #1a365d;
        }

        [data-theme="dark"] {
            --primary-color: #4a5568;
            --secondary-color: #e53e3e;
            --accent-color: #48bb78;
            --text-color: #e2e8f0;
            --bg-color: #1a202c;
            --card-bg: #2d3748;
            --border-color: #4a5568;
            --heading-color: #cbd5e0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            line-height: 1.6;
        }

        [dir="rtl"] body {
            font-family: 'Noto Sans Urdu', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--heading-color);
            font-weight: 700;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            color: white !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .breaking-news {
            background: var(--secondary-color);
            color: white;
            padding: 10px 0;
            overflow: hidden;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .breaking-ticker {
            display: inline-block;
            animation: scroll-left 30s linear infinite;
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        [dir="rtl"] .breaking-ticker {
            animation: scroll-right 30s linear infinite;
        }

        @keyframes scroll-right {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            width: 100%;
            height: 220px;
            object-fit: cover;
            object-position: center;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--secondary-color);
            border: none;
        }

        .btn-success {
            background: var(--accent-color);
            border: none;
        }

        .category-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 10px;
        }

        .meta-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
            text-align: justify;
        }

        .comment-section {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid var(--border-color);
        }

        .comment {
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-reply-form {
            margin-top: 10px;
            padding-left: 20px;
            border-left: 2px solid var(--primary-color);
        }

        .comment-replies {
            margin-left: 20px;
            border-left: 1px solid var(--border-color);
            padding-left: 15px;
        }

        .social-share {
            margin: 20px 0;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .social-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            color: white;
            opacity: 0.9;
        }

        .whatsapp {
            background: #25D366;
        }

        .twitter {
            background: #1DA1F2;
        }

        .facebook {
            background: #4267B2;
        }

        .copy-link {
            background: #6c757d;
        }

        .sidebar {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .sidebar h5 {
            color: var(--heading-color);
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
        }

        .most-read-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .most-read-item:last-child {
            border-bottom: none;
        }

        .most-read-number {
            background: var(--secondary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        [dir="rtl"] .most-read-number {
            margin-right: 0;
            margin-left: 15px;
        }

        .theme-toggle {
            background: none;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 6px;
            padding: 5px 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .language-toggle {
            background: var(--accent-color);
            border: none;
            color: white;
            border-radius: 6px;
            padding: 5px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .language-toggle:hover {
            background: #2d8c56;
        }

        .search-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 8px 20px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 54, 93, 0.25);
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .page-link {
            color: var(--primary-color);
            border-color: var(--border-color);
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .admin-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-card {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            background: var(--card-bg);
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer h5 {
            margin-bottom: 20px;
            font-weight: 600;
            color: white;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        .form-control,
        .form-select,
        .input-group-text {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 6px;
        }

        .form-control:focus,
        .form-select:focus {
            background: var(--card-bg);
            border-color: var(--primary-color);
            color: var(--text-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 54, 93, 0.25);
        }

        .modal-content {
            background: var(--bg-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 992px) {
            .navbar-nav {
                margin-top: 10px;
            }

            .navbar-collapse .dropdown-menu {
                background-color: var(--primary-color);
            }

            .navbar-collapse .dropdown-item {
                color: white !important;
            }

            .navbar-collapse .dropdown-item:hover {
                background-color: var(--secondary-color);
            }

            .search-box {
                width: 100%;
                margin-top: 10px;
            }

            .d-flex.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .d-flex.align-items-center .me-3,
            .d-flex.align-items-center .me-2 {
                margin-right: 0 !important;
                margin-bottom: 10px;
                width: 100%;
            }

            .language-toggle,
            .theme-toggle,
            .dropdown .btn {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.4rem;
            }

            .card-img-top {
                height: 180px;
            }

            .article-content {
                font-size: 1rem;
            }

            .social-btn {
                display: block;
                margin: 5px auto;
            }

            .footer .col-md-2,
            .footer .col-md-3 {
                margin-bottom: 20px;
            }

            .footer .text-end {
                text-align: start !important;
            }
        }

        .print-hidden {
            display: block;
        }

        @media print {
            .print-hidden {
                display: none !important;
            }

            body {
                background: white !important;
                color: black !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            article .card-body {
                padding: 0;
            }

            article .card-title {
                font-size: 1.8rem;
                color: black;
            }

            article .meta-info {
                color: #555;
            }

            .article-content {
                font-size: 1.1rem;
                line-height: 1.7;
                color: #333;
            }
        }

        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            color: white;
            font-size: 1.5rem;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .reading-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: var(--secondary-color);
            z-index: 1030;
            transition: width 0.1s ease-out;
        }

        #adminTabs .nav-link {
            background-color: var(--primary-color);
            color: white;
            margin-right: 5px;
            border-radius: 8px 8px 0 0;
            transition: background-color 0.3s ease;
        }

        #adminTabs .nav-link.active {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            color: white;
        }

        #adminTabs .nav-link:hover:not(.active) {
            background-color: var(--primary-color);
            opacity: 0.9;
        }

        .tab-content {
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 12px 12px;
            padding: 20px;
            background: var(--card-bg);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            padding: 2px;
            margin-bottom: 15px;
        }

        .like-button {
            border: none;
            background: none;
            cursor: pointer;
            color: var(--text-color);
            transition: color 0.2s ease;
            font-size: 1.2rem;
            padding: 5px 10px;
        }

        .like-button.liked {
            color: var(--secondary-color);
        }

        .poll-options label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
        }

        .poll-options input[type="radio"] {
            margin-right: 8px;
        }

        .poll-results {
            margin-top: 15px;
        }

        .poll-option-bar {
            background-color: #eee;
            border-radius: 5px;
            height: 20px;
            margin-bottom: 5px;
            overflow: hidden;
            position: relative;
        }

        .poll-option-bar-fill {
            height: 100%;
            background-color: var(--accent-color);
            width: 0%;
            transition: width 0.5s ease-out;
        }

        .poll-option-bar-text {
            position: absolute;
            width: 100%;
            text-align: center;
            line-height: 20px;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .ad-unit {
            background-color: #f0f0f0;
            border: 1px dashed #ccc;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            color: #555;
            min-height: 100px;
        }

        [data-theme="dark"] .ad-unit {
            background-color: #2a3447;
            border-color: #4a5568;
            color: #ccc;
        }

        .sponsored-badge {
            background: #ffc107;
            color: #333;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }

        .profile-social-link {
            margin-right: 10px;
            color: var(--primary-color);
        }

        [dir="rtl"] .profile-social-link {
            margin-right: 0;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div id="reading-progress-bar" class="reading-progress-bar print-hidden"></div>
    <nav class="navbar navbar-expand-lg navbar-dark print-hidden">
        <div class="container">
            <a class="navbar-brand" href="?lang=<?= $lang ?>">
                <i class="fas fa-newspaper me-2"></i>
                <?= $lang === 'ur' ? 'پاکستان ٹائمز' : 'Pakistan Times' ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="?lang=<?= $lang ?>">
                            <i class="fas fa-home me-1"></i>
                            <?= $lang === 'ur' ? 'ہوم' : 'Home' ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-list me-1"></i>
                            <?= $lang === 'ur' ? 'اقسام' : 'Categories' ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCategories">
                            <?php
                            $categories = $conn->query("SELECT * FROM categories ORDER BY name_en");
                            while ($cat = $categories->fetch_assoc()):
                            ?>
                                <li>
                                    <a class="dropdown-item" href="?view=category&cat=<?= $cat['slug'] ?>&lang=<?= $lang ?>">
                                        <?= $lang === 'ur' ? $cat['name_ur'] : $cat['name_en'] ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?view=submissions&lang=<?= $lang ?>">
                            <i class="fas fa-upload me-1"></i>
                            <?= $lang === 'ur' ? 'مضامین جمع کروائیں' : 'Submit Article' ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?view=polls&lang=<?= $lang ?>">
                            <i class="fas fa-poll me-1"></i>
                            <?= $lang === 'ur' ? 'پول' : 'Polls' ?>
                        </a>
                    </li>
                    <?php if (canEdit()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?view=admin&lang=<?= $lang ?>">
                                <i class="fas fa-cog me-1"></i>
                                <?= $lang === 'ur' ? 'ایڈمن' : 'Admin' ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-lg-center">
                    <form class="me-lg-3 w-100 w-lg-auto mb-2 mb-lg-0" method="GET" action="">
                        <input type="hidden" name="view" value="search">
                        <input type="hidden" name="lang" value="<?= $lang ?>">
                        <div class="input-group">
                            <input class="search-box form-control" type="search" name="q" placeholder="<?= $lang === 'ur' ? 'تلاش کریں...' : 'Search...' ?>" aria-label="Search">
                            <button class="btn btn-outline-light d-none d-lg-block" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <button class="theme-toggle me-lg-2 mb-2 mb-lg-0" onclick="toggleTheme()" aria-label="Toggle theme">
                        <i class="fas fa-moon"></i>
                    </button>
                    <a href="?lang=<?= $lang === 'ur' ? 'en' : 'ur' ?>&view=<?= $view ?><?= $article_id ? '&id=' . $article_id : '' ?>" class="btn language-toggle me-lg-2 mb-2 mb-lg-0" aria-label="Toggle language">
                        <?= $lang === 'ur' ? 'EN' : 'اردو' ?>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown w-100 w-lg-auto">
                            <button class="btn btn-outline-light dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?view=profile&lang=<?= $lang ?>">
                                        <?= $lang === 'ur' ? 'پروفائل' : 'Profile' ?>
                                    </a></li>
                                <?php if (canEdit()): ?>
                                    <li><a class="dropdown-item" href="?view=admin&lang=<?= $lang ?>">
                                            <?= $lang === 'ur' ? 'ایڈمن پینل' : 'Admin Panel' ?>
                                        </a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="?logout=1">
                                        <?= $lang === 'ur' ? 'لاگ آؤٹ' : 'Logout' ?>
                                    </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-outline-light w-100 w-lg-auto" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            <?= $lang === 'ur' ? 'لاگ ان' : 'Login' ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php
    $breaking_news_query = $conn->query("SELECT * FROM articles WHERE is_breaking = 1 AND status = 'published' AND published_at <= NOW() ORDER BY created_at DESC LIMIT 5");
    if ($breaking_news_query->num_rows > 0):
    ?>
        <div class="breaking-news print-hidden" role="marquee" aria-label="Breaking News">
            <div class="container">
                <strong><?= $lang === 'ur' ? 'بریکنگ نیوز:' : 'BREAKING NEWS:' ?></strong>
                <span class="breaking-ticker" aria-live="polite">
                    <?php
                    $news_items = [];
                    while ($news = $breaking_news_query->fetch_assoc()) {
                        $title = $lang === 'ur' ? $news['title_ur'] : $news['title_en'];
                        $news_items[] = htmlspecialchars($title);
                    }
                    echo implode(' • ', $news_items);
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
    <main class="container my-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="<?php echo ($view === 'admin') ? 'col-12' : 'col-lg-8'; ?>">
                <?php
                function display_ad($location)
                {
                    global $conn, $lang;
                    $stmt = $conn->prepare("SELECT code FROM ad_units WHERE location = ? AND status = 'active' ORDER BY RAND() LIMIT 1");
                    $stmt->bind_param("s", $location);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($ad = $result->fetch_assoc()) {
                        echo '<div class="ad-unit my-3">';
                        echo '<h6>' . ($lang === 'ur' ? 'اشتہار' : 'Advertisement') . '</h6>';
                        echo $ad['code'];
                        echo '</div>';
                    }
                }
                switch ($view) {
                    case 'article':
                        include_article_view();
                        break;
                    case 'category':
                        include_category_view();
                        break;
                    case 'search':
                        include_search_view();
                        break;
                    case 'admin':
                        include_admin_view();
                        break;
                    case 'profile':
                        include_profile_view();
                        break;
                    case 'rss':
                        generate_rss();
                        break;
                    case 'breaking':
                        include_breaking_news_view();
                        break;
                    case 'archive':
                        include_archive_view();
                        break;
                    case 'submissions':
                        include_user_submissions_view();
                        break;
                    case 'polls':
                        include_polls_view();
                        break;
                    case 'subscribe':
                        include_subscribe_view();
                        break;
                    case 'author':
                        include_author_view();
                        break;
                    default:
                        include_home_view();
                }
                function include_home_view()
                {
                    global $conn, $lang;
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'تازہ خبریں' : 'Latest News') . '</h2>';
                    display_ad('home_top');
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY a.published_at DESC 
                        LIMIT ? OFFSET ?
                    ";
                    $stmt = $conn->prepare($articles_query);
                    $stmt->bind_param("ii", $limit, $offset);
                    $stmt->execute();
                    $articles = $stmt->get_result();
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی مضمون دستیاب نہیں ہے۔' : 'No articles available.') . '</div>';
                    }
                    echo '<div class="row">';
                    $article_counter = 0;
                    while ($article = $articles->fetch_assoc()) {
                        $article_counter++;
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at'])) . ' • ';
                        echo '<i class="fas fa-eye me-1"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views') . ' • ';
                        echo '<i class="fas fa-thumbs-up me-1"></i>' . $article['likes'] . ' ' . ($lang === 'ur' ? 'پسندیدگیاں' : 'likes');
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                        if ($article_counter % 2 === 0) {
                            display_ad('home_middle');
                        }
                    }
                    echo '</div>';
                    $total_articles_query = $conn->query("SELECT COUNT(*) as count FROM articles WHERE status = 'published' AND published_at <= NOW()");
                    $total_articles = $total_articles_query->fetch_assoc()['count'];
                    $total_pages = ceil($total_articles / $limit);
                    if ($total_pages > 1) {
                        echo '<nav aria-label="Page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?page=' . $i . '&lang=' . $lang . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                    display_ad('home_bottom');
                }
                function include_article_view()
                {
                    global $conn, $lang, $article_id;
                    $stmt = $conn->prepare("
                        SELECT a.*, c.name_en, c.name_ur, u.username, u.id AS author_user_id
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE a.id = ? AND a.status = 'published' AND a.published_at <= NOW()
                    ");
                    $stmt->bind_param("i", $article_id);
                    $stmt->execute();
                    $article = $stmt->get_result()->fetch_assoc();
                    if (!$article) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'مضمون نہیں ملا' : 'Article not found or not published') . '</div>';
                        return;
                    }
                    $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                    $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                    $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                    echo '<article class="card shadow-sm">';
                    echo '<div class="card-body">';
                    echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                    echo '<h1 class="card-title mt-3 mb-3">' . htmlspecialchars($title);
                    if ($article['is_sponsored']) {
                        echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                    }
                    echo '</h1>';
                    echo '<div class="meta-info mb-4">';
                    echo '<i class="fas fa-user me-2"></i>';
                    echo '<a href="?view=author&id=' . $article['author_user_id'] . '&lang=' . $lang . '">' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . '</a> • ';
                    echo '<i class="fas fa-calendar me-2"></i>' . date('M j, Y g:i A', strtotime($article['published_at'])) . ' • ';
                    echo '<i class="fas fa-eye me-2"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views') . ' • ';
                    $user_liked = false;
                    if (isLoggedIn()) {
                        $check_like_stmt = $conn->prepare("SELECT COUNT(*) FROM article_likes WHERE user_id = ? AND article_id = ?");
                        $check_like_stmt->bind_param("ii", $_SESSION['user_id'], $article_id);
                        $check_like_stmt->execute();
                        $user_liked = $check_like_stmt->get_result()->fetch_row()[0] > 0;
                    }
                    echo '<button class="like-button ' . ($user_liked ? 'liked' : '') . '" data-article-id="' . $article_id . '" aria-label="Like article"><i class="fas fa-thumbs-up me-1"></i> <span class="like-count">' . $article['likes'] . '</span></button>';
                    echo '</div>';
                    if ($article['image']) {
                        echo '<img src="' . htmlspecialchars($article['image']) . '" class="img-fluid rounded mb-4" alt="' . htmlspecialchars($title) . '">';
                    }
                    display_ad('article_middle');
                    echo '<div class="article-content">' . nl2br(htmlspecialchars($content)) . '</div>';
                    $current_url = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    echo '<div class="social-share print-hidden">';
                    echo '<h5>' . ($lang === 'ur' ? 'شیئر کریں:' : 'Share:') . '</h5>';
                    echo '<a href="#" onclick="shareWhatsApp(\'' . htmlspecialchars($title, ENT_QUOTES) . '\', \'' . htmlspecialchars($current_url, ENT_QUOTES) . '\')" class="social-btn whatsapp" aria-label="Share on WhatsApp">';
                    echo '<i class="fab fa-whatsapp me-1"></i>WhatsApp</a>';
                    echo '<a href="#" onclick="shareTwitter(\'' . htmlspecialchars($title, ENT_QUOTES) . '\', \'' . htmlspecialchars($current_url, ENT_QUOTES) . '\')" class="social-btn twitter" aria-label="Share on Twitter">';
                    echo '<i class="fab fa-twitter me-1"></i>Twitter</a>';
                    echo '<a href="#" onclick="shareFacebook(\'' . htmlspecialchars($current_url, ENT_QUOTES) . '\')" class="social-btn facebook" aria-label="Share on Facebook">';
                    echo '<i class="fab fa-facebook me-1"></i>Facebook</a>';
                    echo '<button class="social-btn copy-link" onclick="copyLink(\'' . htmlspecialchars($current_url, ENT_QUOTES) . '\')" aria-label="Copy link">';
                    echo '<i class="fas fa-link me-1"></i>' . ($lang === 'ur' ? 'لنک کاپی' : 'Copy Link') . '</button>';
                    echo '</div>';
                    echo '<div class="mt-3 print-hidden">';
                    echo '<button class="btn btn-outline-primary me-2" onclick="printArticle()" aria-label="Print article">';
                    echo '<i class="fas fa-print me-1"></i>' . ($lang === 'ur' ? 'پرنٹ کریں' : 'Print') . '</button>';
                    echo '<button class="btn btn-outline-secondary" onclick="readArticle(\'' . htmlspecialchars($content, ENT_QUOTES) . '\', \'' . $lang . '\')" aria-label="Text to speech">';
                    echo '<i class="fas fa-volume-up me-1"></i>' . ($lang === 'ur' ? 'پڑھیں' : 'Read Article') . '</button>';
                    echo '</div>';
                    echo '</div></article>';
                    echo '<div class="sidebar mt-4 print-hidden">';
                    echo '<h5><i class="fas fa-newspaper me-2"></i>' . ($lang === 'ur' ? 'متعلقہ مضامین' : 'Related Articles') . '</h5>';
                    $related_articles_query = $conn->prepare("
                        SELECT a.id, a.title_en, a.title_ur, a.image, a.views, c.slug 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.category_id = ? AND a.id != ? AND a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY RAND() LIMIT 3
                    ");
                    $related_articles_query->bind_param("ii", $article['category_id'], $article_id);
                    $related_articles_query->execute();
                    $related_articles = $related_articles_query->get_result();
                    if ($related_articles->num_rows > 0) {
                        echo '<div class="row">';
                        while ($r_article = $related_articles->fetch_assoc()) {
                            $r_title = $lang === 'ur' ? $r_article['title_ur'] : $r_article['title_en'];
                            echo '<div class="col-md-4 mb-3">';
                            echo '<div class="card h-100">';
                            if ($r_article['image']) {
                                echo '<img src="' . htmlspecialchars($r_article['image']) . '" class="card-img-top" style="height: 120px; object-fit: cover;" alt="' . htmlspecialchars($r_title) . '">';
                            } else {
                                echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" style="height: 120px; object-fit: cover;" alt="No image available">';
                            }
                            echo '<div class="card-body p-2">';
                            echo '<h6 class="card-title mb-1"><a href="?view=article&id=' . $r_article['id'] . '&lang=' . $lang . '" class="text-decoration-none text-dark">' . htmlspecialchars(mb_strimwidth($r_title, 0, 40, "...")) . '</a></h6>';
                            echo '<small class="text-muted"><i class="fas fa-eye me-1"></i>' . $r_article['views'] . '</small>';
                            echo '</div></div></div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p class="text-muted">' . ($lang === 'ur' ? 'کوئی متعلقہ مضمون نہیں ملا۔' : 'No related articles found.') . '</p>';
                    }
                    echo '</div>';
                    echo '<div class="comment-section mt-4 print-hidden">';
                    echo '<h4>' . ($lang === 'ur' ? 'تبصرے' : 'Comments') . '</h4>';
                    if (isLoggedIn()) {
                        echo '<form method="POST" class="mb-4 needs-validation" novalidate>';
                        echo '<input type="hidden" name="article_id" value="' . $article_id . '">';
                        echo '<input type="hidden" name="parent_comment_id" id="parentCommentId" value="">';
                        echo '<div class="mb-3">';
                        echo '<label for="commentTextarea" class="form-label visually-hidden">' . ($lang === 'ur' ? 'آپ کا تبصرہ' : 'Your comment') . '</label>';
                        echo '<textarea name="comment" id="commentTextarea" class="form-control" rows="3" placeholder="' . ($lang === 'ur' ? 'اپنا تبصرہ لکھیں...' : 'Write your comment...') . '" required aria-label="Comment textarea"></textarea>';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'براہ کرم تبصرہ لکھیں۔' : 'Please write a comment.') . '</div>';
                        echo '<small id="replyToIndicator" class="text-muted"></small>';
                        echo '</div>';
                        echo '<button type="submit" name="add_comment" class="btn btn-primary">';
                        echo '<i class="fas fa-comment me-1"></i>' . ($lang === 'ur' ? 'تبصرہ شامل کریں' : 'Add Comment');
                        echo '</button>';
                        echo '</form>';
                    } else {
                        echo '<p class="text-muted">' . ($lang === 'ur' ? 'تبصرہ کرنے کے لیے <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">لاگ ان کریں</a>۔' : 'Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to comment.') . '</p>';
                    }
                    $comments_query_raw = $conn->prepare("
                        SELECT c.*, u.username 
                        FROM comments c 
                        LEFT JOIN users u ON c.user_id = u.id 
                        WHERE c.article_id = ? AND c.status = 'approved' 
                        ORDER BY c.created_at ASC
                    ");
                    $comments_query_raw->bind_param("i", $article_id);
                    $comments_query_raw->execute();
                    $comments_raw_result = $comments_query_raw->get_result();
                    $all_comments = [];
                    while ($row = $comments_raw_result->fetch_assoc()) {
                        $all_comments[] = $row;
                    }
                    $nested_comments = get_nested_comments($all_comments);
                    if (empty($nested_comments)) {
                        echo '<p class="text-muted">' . ($lang === 'ur' ? 'ابھی تک کوئی تبصرہ نہیں ہے۔' : 'No comments yet. Be the first to comment!') . '</p>';
                    } else {
                        display_comments($nested_comments, $lang);
                    }
                    echo '</div>';
                }
                function display_comments($comments, $lang, $level = 0)
                {
                    echo '<div class="comment-list ' . ($level > 0 ? 'comment-replies' : '') . '">';
                    foreach ($comments as $comment) {
                        echo '<div class="comment">';
                        echo '<div class="d-flex justify-content-between align-items-center">';
                        echo '<strong>' . htmlspecialchars($comment['username'] ?: ($comment['name'] ?? ($lang === 'ur' ? 'مہمان' : 'Guest'))) . '</strong>';
                        echo '<small class="text-muted" dir="ltr">' . date('M j, Y g:i A', strtotime($comment['created_at'])) . '</small>';
                        echo '</div>';
                        echo '<p class="mt-2 mb-0">' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                        if (isLoggedIn()) {
                            echo '<button class="btn btn-sm btn-link reply-btn" data-comment-id="' . $comment['id'] . '" data-username="' . htmlspecialchars($comment['username'] ?: ($comment['name'] ?? ($lang === 'ur' ? 'مہمان' : 'Guest'))) . '">' . ($lang === 'ur' ? 'جواب دیں' : 'Reply') . '</button>';
                        }
                        if (!empty($comment['replies'])) {
                            display_comments($comment['replies'], $lang, $level + 1);
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                }
                function include_category_view()
                {
                    global $conn, $lang;
                    $category_slug = $_GET['cat'];
                    $stmt_cat = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
                    $stmt_cat->bind_param("s", $category_slug);
                    $stmt_cat->execute();
                    $category = $stmt_cat->get_result()->fetch_assoc();
                    if (!$category) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'کیٹگری نہیں ملی' : 'Category not found') . '</div>';
                        return;
                    }
                    $category_name = $lang === 'ur' ? $category['name_ur'] : $category['name_en'];
                    echo '<h2 class="mb-4">' . htmlspecialchars($category_name) . '</h2>';
                    display_ad('category_top');
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE c.slug = ? AND a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY a.published_at DESC
                        LIMIT ? OFFSET ?
                    ";
                    $stmt_articles = $conn->prepare($articles_query);
                    $stmt_articles->bind_param("sii", $category_slug, $limit, $offset);
                    $stmt_articles->execute();
                    $articles = $stmt_articles->get_result();
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'اس کیٹگری میں کوئی مضمون نہیں ہے۔' : 'No articles in this category.') . '</div>';
                        return;
                    }
                    echo '<div class="row">';
                    $article_counter = 0;
                    while ($article = $articles->fetch_assoc()) {
                        $article_counter++;
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category_display_name = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category_display_name) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at'])) . ' • ';
                        echo '<i class="fas fa-eye me-1"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views');
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                        if ($article_counter % 2 === 0) {
                            display_ad('category_middle');
                        }
                    }
                    echo '</div>';
                    $total_articles_cat_query = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ? AND status = 'published' AND published_at <= NOW()");
                    $total_articles_cat_query->bind_param("i", $category['id']);
                    $total_articles_cat_query->execute();
                    $total_articles_cat = $total_articles_cat_query->get_result()->fetch_assoc()['count'];
                    $total_pages_cat = ceil($total_articles_cat / $limit);
                    if ($total_pages_cat > 1) {
                        echo '<nav aria-label="Category page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages_cat; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?view=category&cat=' . $category_slug . '&page=' . $i . '&lang=' . $lang . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                    display_ad('category_bottom');
                }
                function include_search_view()
                {
                    global $conn, $lang;
                    $query = trim($_GET['q'] ?? '');
                    $filter_category = $_GET['filter_category'] ?? '';
                    $filter_author = $_GET['filter_author'] ?? '';
                    $filter_start_date = $_GET['filter_start_date'] ?? '';
                    $filter_end_date = $_GET['filter_end_date'] ?? '';
                    if (empty($query) && empty($filter_category) && empty($filter_author) && empty($filter_start_date) && empty($filter_end_date)) {
                        echo '<div class="alert alert-warning" role="alert">' . ($lang === 'ur' ? 'تلاش کی شرائط داخل کریں' : 'Please enter search terms or apply filters.') . '</div>';
                        return;
                    }
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'تلاش کے نتائج' : 'Search Results') . '</h2>';
                    echo '<div class="card mb-4 shadow-sm">';
                    echo '<div class="card-header bg-secondary text-white"><h5>' . ($lang === 'ur' ? 'اعلی درجے کی تلاش' : 'Advanced Search') . '</h5></div>';
                    echo '<div class="card-body">';
                    echo '<form method="GET" action="" class="row g-3">';
                    echo '<input type="hidden" name="view" value="search">';
                    echo '<input type="hidden" name="lang" value="' . $lang . '">';
                    echo '<div class="col-md-6">';
                    echo '<label for="searchQuery" class="form-label">' . ($lang === 'ur' ? 'عنوان یا مواد' : 'Title or Content') . '</label>';
                    echo '<input type="text" class="form-control" id="searchQuery" name="q" value="' . htmlspecialchars($query) . '" placeholder="' . ($lang === 'ur' ? 'تلاش کریں...' : 'Search...') . '">';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<label for="filterCategory" class="form-label">' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</label>';
                    echo '<select class="form-select" id="filterCategory" name="filter_category">';
                    echo '<option value="">' . ($lang === 'ur' ? 'تمام کیٹگریز' : 'All Categories') . '</option>';
                    $all_categories = $conn->query("SELECT id, name_en, name_ur FROM categories ORDER BY name_en");
                    while ($cat_option = $all_categories->fetch_assoc()) {
                        $selected = ($filter_category == $cat_option['id']) ? 'selected' : '';
                        echo '<option value="' . $cat_option['id'] . '" ' . $selected . '>' . ($lang === 'ur' ? $cat_option['name_ur'] : $cat_option['name_en']) . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<label for="filterAuthor" class="form-label">' . ($lang === 'ur' ? 'مصنف' : 'Author') . '</label>';
                    echo '<select class="form-select" id="filterAuthor" name="filter_author">';
                    echo '<option value="">' . ($lang === 'ur' ? 'تمام مصنفین' : 'All Authors') . '</option>';
                    $all_authors = $conn->query("SELECT id, username FROM users WHERE role IN ('admin', 'editor') ORDER BY username");
                    while ($author_option = $all_authors->fetch_assoc()) {
                        $selected = ($filter_author == $author_option['id']) ? 'selected' : '';
                        echo '<option value="' . $author_option['id'] . '" ' . $selected . '>' . htmlspecialchars($author_option['username']) . '</option>';
                    }
                    echo '</select>';
                    echo '</div>';
                    echo '<div class="col-md-3">';
                    echo '<label for="filterStartDate" class="form-label">' . ($lang === 'ur' ? 'شروع کی تاریخ' : 'Start Date') . '</label>';
                    echo '<input type="date" class="form-control" id="filterStartDate" name="filter_start_date" value="' . htmlspecialchars($filter_start_date) . '">';
                    echo '</div>';
                    echo '<div class="col-md-3">';
                    echo '<label for="filterEndDate" class="form-label">' . ($lang === 'ur' ? 'اختتامی تاریخ' : 'End Date') . '</label>';
                    echo '<input type="date" class="form-control" id="filterEndDate" name="filter_end_date" value="' . htmlspecialchars($filter_end_date) . '">';
                    echo '</div>';
                    echo '<div class="col-12 text-end">';
                    echo '<button type="submit" class="btn btn-primary">' . ($lang === 'ur' ? 'تلاش کریں' : 'Search') . '</button>';
                    echo '</div>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    $search_query_param = "%" . $query . "%";
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $sql_conditions = ["a.status = 'published'", "a.published_at <= NOW()"];
                    $sql_params = [];
                    $sql_types = "";
                    if (!empty($query)) {
                        $sql_conditions[] = "(a.title_en LIKE ? OR a.title_ur LIKE ? OR a.content_en LIKE ? OR a.content_ur LIKE ?)";
                        $sql_params = array_merge($sql_params, [$search_query_param, $search_query_param, $search_query_param, $search_query_param]);
                        $sql_types .= "ssss";
                    }
                    if (!empty($filter_category)) {
                        $sql_conditions[] = "a.category_id = ?";
                        $sql_params[] = $filter_category;
                        $sql_types .= "i";
                    }
                    if (!empty($filter_author)) {
                        $sql_conditions[] = "a.author_id = ?";
                        $sql_params[] = $filter_author;
                        $sql_types .= "i";
                    }
                    if (!empty($filter_start_date)) {
                        $sql_conditions[] = "DATE(a.published_at) >= ?";
                        $sql_params[] = $filter_start_date;
                        $sql_types .= "s";
                    }
                    if (!empty($filter_end_date)) {
                        $sql_conditions[] = "DATE(a.published_at) <= ?";
                        $sql_params[] = $filter_end_date;
                        $sql_types .= "s";
                    }
                    $where_clause = '';
                    if (!empty($sql_conditions)) {
                        $where_clause = 'WHERE ' . implode(' AND ', $sql_conditions);
                    }
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        $where_clause 
                        ORDER BY a.published_at DESC
                        LIMIT ? OFFSET ?
                    ";
                    $total_articles_query_count = "
                        SELECT COUNT(*) as count 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        $where_clause
                    ";
                    $stmt = $conn->prepare($articles_query);
                    $stmt_count = $conn->prepare($total_articles_query_count);
                    if (!empty($sql_params)) {
                        $all_params = array_merge($sql_params, [$limit, $offset]);
                        $stmt->bind_param($sql_types . "ii", ...$all_params);
                        $stmt_count->bind_param($sql_types, ...$sql_params);
                    } else {
                        $stmt->bind_param("ii", $limit, $offset);
                    }
                    $stmt->execute();
                    $articles = $stmt->get_result();
                    $stmt_count->execute();
                    $total_articles_search = $stmt_count->get_result()->fetch_assoc()['count'];
                    if ($articles->num_rows == 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی نتیجہ نہیں ملا' : 'No results found') . '</div>';
                        return;
                    }
                    echo '<div class="row">';
                    while ($article = $articles->fetch_assoc()) {
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at']));
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                    $total_pages_search = ceil($total_articles_search / $limit);
                    if ($total_pages_search > 1) {
                        echo '<nav aria-label="Search results page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages_search; $i++) {
                            $active = $i == $page ? 'active' : '';
                            $pagination_query = http_build_query(array_merge($_GET, ['page' => $i]));
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?' . $pagination_query . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                }
                function include_admin_view()
                {
                    global $conn, $lang, $success, $error;
                    if (!canEdit()) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'رسائی مسترد' : 'Access Denied: You do not have permission to view this page.') . '</div>';
                        return;
                    }
                    echo '<div class="admin-panel">';
                    echo '<h2><i class="fas fa-cog me-2"></i>' . ($lang === 'ur' ? 'ایڈمن پینل' : 'Admin Panel') . '</h2>';
                    echo '<p>' . ($lang === 'ur' ? 'خوش آمدید، ' : 'Welcome, ') . htmlspecialchars($_SESSION['username']) . '</p>';
                    echo '</div>';
                    $total_articles = $conn->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'];
                    $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                    $total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
                    $pending_comments = $conn->query("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'")->fetch_assoc()['count'];
                    $pending_submissions = $conn->query("SELECT COUNT(*) as count FROM user_submissions WHERE status = 'pending'")->fetch_assoc()['count'];
                    echo '<div class="row mb-4">';
                    echo '<div class="col-md-3 col-sm-6 mb-3"><div class="stats-card shadow-sm"><div class="stats-number">' . $total_articles . '</div><div>' . ($lang === 'ur' ? 'کل مضامین' : 'Total Articles') . '</div></div></div>';
                    echo '<div class="col-md-3 col-sm-6 mb-3"><div class="stats-card shadow-sm"><div class="stats-number">' . $total_users . '</div><div>' . ($lang === 'ur' ? 'کل صارفین' : 'Total Users') . '</div></div></div>';
                    echo '<div class="col-md-3 col-sm-6 mb-3"><div class="stats-card shadow-sm"><div class="stats-number">' . $total_comments . '</div><div>' . ($lang === 'ur' ? 'کل تبصرے' : 'Total Comments') . '</div></div></div>';
                    echo '<div class="col-md-3 col-sm-6 mb-3"><div class="stats-card shadow-sm"><div class="stats-number">' . $pending_comments . '</div><div>' . ($lang === 'ur' ? 'منتظر تبصرے' : 'Pending Comments') . '</div></div></div>';
                    echo '<div class="col-md-3 col-sm-6 mb-3"><div class="stats-card shadow-sm"><div class="stats-number">' . $pending_submissions . '</div><div>' . ($lang === 'ur' ? 'منتظر جمع کردہ مضامین' : 'Pending Submissions') . '</div></div></div>';
                    echo '</div>';
                    echo '<div class="row mb-4">';
                    echo '<div class="col-md-6">';
                    echo '<div class="card shadow-sm"><div class="card-header bg-primary text-white">' . ($lang === 'ur' ? 'مضامین کے مناظر (ماہانہ)' : 'Article Views (Monthly)') . '</div><div class="card-body">';
                    echo '<canvas id="articleViewsChart"></canvas>';
                    echo '</div></div></div>';
                    echo '<div class="col-md-6">';
                    echo '<div class="card shadow-sm"><div class="card-header bg-primary text-white">' . ($lang === 'ur' ? 'صارف رجسٹریشن (ماہانہ)' : 'User Registrations (Monthly)') . '</div><div class="card-body">';
                    echo '<canvas id="userRegistrationsChart"></canvas>';
                    echo '</div></div></div>';
                    echo '</div>';
                    echo '<div class="row">';
                    echo '<div class="col-lg-12">';
                    echo '<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">';
                    echo '<li class="nav-item" role="presentation"><button class="nav-link active" id="articles-tab" data-bs-toggle="tab" data-bs-target="#articles" type="button" role="tab" aria-controls="articles" aria-selected="true"><i class="fas fa-file-alt me-1"></i>' . ($lang === 'ur' ? 'مضامین' : 'Articles') . '</button></li>';
                    echo '<li class="nav-item" role="presentation"><button class="nav-link" id="submissions-tab" data-bs-toggle="tab" data-bs-target="#submissions" type="button" role="tab" aria-controls="submissions" aria-selected="false"><i class="fas fa-inbox me-1"></i>' . ($lang === 'ur' ? 'جمع کردہ مضامین' : 'Submissions') . '</button></li>';
                    echo '<li class="nav-item" role="presentation"><button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false"><i class="fas fa-comments me-1"></i>' . ($lang === 'ur' ? 'تبصرے' : 'Comments') . '</button></li>';
                    echo '<li class="nav-item" role="presentation"><button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab" aria-controls="categories" aria-selected="false"><i class="fas fa-list me-1"></i>' . ($lang === 'ur' ? 'کیٹگریز' : 'Categories') . '</button></li>';
                    echo '<li class="nav-item" role="presentation"><button class="nav-link" id="polls-tab" data-bs-toggle="tab" data-bs-target="#polls" type="button" role="tab" aria-controls="polls" aria-selected="false"><i class="fas fa-poll me-1"></i>' . ($lang === 'ur' ? 'پول' : 'Polls') . '</button></li>';
                    if (hasRole('admin')) {
                        echo '<li class="nav-item" role="presentation"><button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false"><i class="fas fa-users me-1"></i>' . ($lang === 'ur' ? 'صارفین' : 'Users') . '</button></li>';
                        echo '<li class="nav-item" role="presentation"><button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads" type="button" role="tab" aria-controls="ads" aria-selected="false"><i class="fas fa-ad me-1"></i>' . ($lang === 'ur' ? 'اشتہارات' : 'Ads') . '</button></li>';
                        echo '<li class="nav-item" role="presentation"><button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false"><i class="fas fa-search-dollar me-1"></i>SEO</button></li>';
                        echo '<li class="nav-item" role="presentation"><button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab" aria-controls="logs" aria-selected="false"><i class="fas fa-history me-1"></i>' . ($lang === 'ur' ? 'سرگرمی لاگز' : 'Activity Logs') . '</button></li>';
                        echo '<li class="nav-item" role="presentation"><button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab" aria-controls="backup" aria-selected="false"><i class="fas fa-database me-1"></i>' . ($lang === 'ur' ? 'بیک اپ/بحال' : 'Backup/Restore') . '</button></li>';
                    }
                    echo '</ul>';
                    echo '<div class="tab-content" id="adminTabsContent">';
                    echo '<div class="tab-pane fade show active" id="articles" role="tabpanel" aria-labelledby="articles-tab">';
                    echo '<div class="card mb-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'نیا مضمون شامل کریں' : 'Add New Article') . '</h4></div>';
                    echo '<div class="card-body">';
                    echo '<form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="title_en" class="form-label">' . ($lang === 'ur' ? 'انگریزی عنوان' : 'English Title') . '</label>';
                    echo '<input type="text" name="title_en" id="title_en" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="title_ur" class="form-label">' . ($lang === 'ur' ? 'اردو عنوان' : 'Urdu Title') . '</label>';
                    echo '<input type="text" name="title_ur" id="title_ur" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.') . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="category_id" class="form-label">' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</label>';
                    echo '<select name="category_id" id="category_id" class="form-select" required>';
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name_en");
                    while ($cat = $categories->fetch_assoc()) {
                        echo '<option value="' . $cat['id'] . '">' . ($lang === 'ur' ? $cat['name_ur'] : $cat['name_en']) . '</option>';
                    }
                    echo '</select>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'کیٹگری درکار ہے۔' : 'Category is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="articleImage" class="form-label">' . ($lang === 'ur' ? 'تصویر' : 'Image') . '</label>';
                    echo '<input type="file" name="image" id="articleImage" class="form-control" accept="image/*">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="published_at" class="form-label">' . ($lang === 'ur' ? 'شائع ہونے کی تاریخ/وقت' : 'Publish Date/Time') . '</label>';
                    echo '<input type="datetime-local" name="published_at" id="published_at" class="form-control" value="' . date('Y-m-d\TH:i') . '">';
                    echo '<small class="form-text text-muted">' . ($lang === 'ur' ? 'اگر خالی چھوڑ دیا جائے تو فوری طور پر شائع ہو جائے گا۔' : 'Leave empty to publish immediately.') . '</small>';
                    echo '</div>';
                    echo '<div class="row mb-3">';
                    echo '<div class="col-md-6 form-check">';
                    echo '<input class="form-check-input" type="checkbox" name="is_breaking" id="breaking_add_article">';
                    echo '<label class="form-check-label" for="breaking_add_article">' . ($lang === 'ur' ? 'بریکنگ نیوز' : 'Breaking News') . '</label>';
                    echo '</div>';
                    echo '<div class="col-md-6 form-check">';
                    echo '<input class="form-check-input" type="checkbox" name="is_sponsored" id="sponsored_add_article">';
                    echo '<label class="form-check-label" for="sponsored_add_article">' . ($lang === 'ur' ? 'سپانسر شدہ مواد' : 'Sponsored Content') . '</label>';
                    echo '</div>';
                    echo '</div>';
                    echo '<hr>';
                    echo '<h5>' . ($lang === 'ur' ? 'SEO کی تفصیلات' : 'SEO Details') . '</h5>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_meta_title_en" class="form-label">SEO Meta Title (English)</label>';
                    echo '<input type="text" name="seo_meta_title_en" id="seo_meta_title_en" class="form-control" maxlength="60">';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_meta_title_ur" class="form-label">SEO Meta Title (Urdu)</label>';
                    echo '<input type="text" name="seo_meta_title_ur" id="seo_meta_title_ur" class="form-control" maxlength="60">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_meta_description_en" class="form-label">SEO Meta Description (English)</label>';
                    echo '<textarea name="seo_meta_description_en" id="seo_meta_description_en" class="form-control" rows="2" maxlength="160"></textarea>';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_meta_description_ur" class="form-label">SEO Meta Description (Urdu)</label>';
                    echo '<textarea name="seo_meta_description_ur" id="seo_meta_description_ur" class="form-control" rows="2" maxlength="160"></textarea>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_keywords_en" class="form-label">SEO Keywords (English)</label>';
                    echo '<input type="text" name="seo_keywords_en" id="seo_keywords_en" class="form-control" placeholder="comma,separated,keywords">';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="seo_keywords_ur" class="form-label">SEO Keywords (Urdu)</label>';
                    echo '<input type="text" name="seo_keywords_ur" id="seo_keywords_ur" class="form-control" placeholder="کوما،سے،علیحدہ،الفاظ">';
                    echo '</div>';
                    echo '</div>';
                    echo '<hr>';
                    echo '<div class="mb-3">';
                    echo '<label for="content_en" class="form-label">' . ($lang === 'ur' ? 'انگریزی مواد' : 'English Content') . '</label>';
                    echo '<textarea name="content_en" id="content_en" class="form-control" rows="8" required></textarea>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="content_ur" class="form-label">' . ($lang === 'ur' ? 'اردو مواد' : 'Urdu Content') . '</label>';
                    echo '<textarea name="content_ur" id="content_ur" class="form-control" rows="8" required></textarea>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.') . '</div>';
                    echo '</div>';
                    echo '<button type="submit" name="add_article" class="btn btn-primary">';
                    echo '<i class="fas fa-plus me-1"></i>' . ($lang === 'ur' ? 'مضمون شامل کریں' : 'Add Article');
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'مضامین کا انتظام' : 'Manage Articles') . '</h4></div>';
                    echo '<div class="card-body">';
                    $articles = $conn->query("
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        ORDER BY a.created_at DESC
                    ");
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی مضمون نہیں ہے۔' : 'No articles to manage.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'عنوان' : 'Title') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'مصنف' : 'Author') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'تاریخ' : 'Date') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'مناظر' : 'Views') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($article = $articles->fetch_assoc()) {
                            $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                            $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                            $author = htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم' : 'Unknown'));
                            $status_class = $article['status'] === 'published' ? 'bg-success' : 'bg-warning text-dark';
                            echo '<tr>';
                            echo '<td><a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="text-decoration-none">' . htmlspecialchars(mb_strimwidth($title, 0, 50, "...")) . '</a></td>';
                            echo '<td>' . htmlspecialchars($category) . '</td>';
                            echo '<td>' . $author . '</td>';
                            echo '<td dir="ltr">' . date('M j, Y', strtotime($article['created_at'])) . '</td>';
                            echo '<td>' . $article['views'] . '</td>';
                            echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($article['status']) . '</span></td>';
                            echo '<td>';
                            echo '<button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editArticleModal" data-id="' . $article['id'] . '" data-title_en="' . htmlspecialchars($article['title_en'], ENT_QUOTES) . '" data-title_ur="' . htmlspecialchars($article['title_ur'], ENT_QUOTES) . '" data-content_en="' . htmlspecialchars($article['content_en'], ENT_QUOTES) . '" data-content_ur="' . htmlspecialchars($article['content_ur'], ENT_QUOTES) . '" data-category_id="' . $article['category_id'] . '" data-is_breaking="' . $article['is_breaking'] . '" data-status="' . $article['status'] . '" data-image="' . htmlspecialchars($article['image'], ENT_QUOTES) . '" data-is_sponsored="' . $article['is_sponsored'] . '" data-published_at="' . date('Y-m-d\TH:i', strtotime($article['published_at'])) . '" data-seo_meta_title_en="' . htmlspecialchars($article['seo_meta_title_en'] ?? '', ENT_QUOTES) . '" data-seo_meta_title_ur="' . htmlspecialchars($article['seo_meta_title_ur'] ?? '', ENT_QUOTES) . '" data-seo_meta_description_en="' . htmlspecialchars($article['seo_meta_description_en'] ?? '', ENT_QUOTES) . '" data-seo_meta_description_ur="' . htmlspecialchars($article['seo_meta_description_ur'] ?? '', ENT_QUOTES) . '" data-seo_keywords_en="' . htmlspecialchars($article['seo_keywords_en'] ?? '', ENT_QUOTES) . '" data-seo_keywords_ur="' . htmlspecialchars($article['seo_keywords_ur'] ?? '', ENT_QUOTES) . '" aria-label="Edit Article"><i class="fas fa-edit"></i></button>';
                            echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس مضمون کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this article?') . '\');">';
                            echo '<input type="hidden" name="article_id" value="' . $article['id'] . '">';
                            echo '<button type="submit" name="delete_article" class="btn btn-sm btn-danger" aria-label="Delete Article"><i class="fas fa-trash"></i></button>';
                            echo '</form>';
                            echo '<button class="btn btn-sm btn-secondary ms-1" data-bs-toggle="modal" data-bs-target="#revisionHistoryModal" data-article-id="' . $article['id'] . '" aria-label="View Revision History"><i class="fas fa-history"></i></button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="tab-pane fade" id="submissions" role="tabpanel" aria-labelledby="submissions-tab">';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'جمع کردہ مضامین کا انتظام' : 'Manage Submissions') . '</h4></div>';
                    echo '<div class="card-body">';
                    $submissions = $conn->query("
                        SELECT us.*, u.username, c.name_en, c.name_ur
                        FROM user_submissions us
                        LEFT JOIN users u ON us.user_id = u.id
                        LEFT JOIN categories c ON us.category_id = c.id
                        ORDER BY us.submitted_at DESC
                    ");
                    if ($submissions->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی جمع کردہ مضمون نہیں ہے۔' : 'No submissions to manage.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'عنوان' : 'Title') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'جمع کنندہ' : 'Submitter') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'تاریخ' : 'Date') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($submission = $submissions->fetch_assoc()) {
                            $title = $lang === 'ur' ? $submission['title_ur'] : $submission['title_en'];
                            $category = $lang === 'ur' ? ($submission['name_ur'] ?? 'نامعلوم') : ($submission['name_en'] ?? 'Unknown');
                            $submitter = htmlspecialchars($submission['username'] ?? ($lang === 'ur' ? 'نامعلوم' : 'Unknown'));
                            $status_class = '';
                            if ($submission['status'] === 'pending')
                                $status_class = 'bg-warning text-dark';
                            else if ($submission['status'] === 'approved')
                                $status_class = 'bg-success';
                            else
                                $status_class = 'bg-danger';
                            echo '<tr>';
                            echo '<td><button class="btn btn-link p-0 view-submission-btn" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal" data-id="' . $submission['id'] . '" data-title_en="' . htmlspecialchars($submission['title_en'], ENT_QUOTES) . '" data-title_ur="' . htmlspecialchars($submission['title_ur'], ENT_QUOTES) . '" data-content_en="' . htmlspecialchars($submission['content_en'], ENT_QUOTES) . '" data-content_ur="' . htmlspecialchars($submission['content_ur'], ENT_QUOTES) . '" data-image="' . htmlspecialchars($submission['image'], ENT_QUOTES) . '" data-category="' . htmlspecialchars($category) . '" data-submitter="' . htmlspecialchars($submitter) . '" data-status="' . htmlspecialchars($submission['status']) . '">' . htmlspecialchars(mb_strimwidth($title, 0, 50, "...")) . '</button></td>';
                            echo '<td>' . $submitter . '</td>';
                            echo '<td>' . htmlspecialchars($category) . '</td>';
                            echo '<td dir="ltr">' . date('M j, Y', strtotime($submission['submitted_at'])) . '</td>';
                            echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($submission['status']) . '</span></td>';
                            echo '<td>';
                            echo '<form method="POST" class="d-inline-block me-1">';
                            echo '<input type="hidden" name="submission_id" value="' . $submission['id'] . '">';
                            echo '<select name="status" class="form-select form-select-sm d-inline-block w-auto me-1" onchange="this.form.submit()" aria-label="Update submission status">';
                            echo '<option value="pending"' . ($submission['status'] === 'pending' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'منتظر' : 'Pending') . '</option>';
                            echo '<option value="approved"' . ($submission['status'] === 'approved' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'منظور شدہ' : 'Approved') . '</option>';
                            echo '<option value="rejected"' . ($submission['status'] === 'rejected' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'مسترد' : 'Rejected') . '</option>';
                            echo '</select>';
                            echo '<input type="hidden" name="update_submission_status" value="1">';
                            echo '</form>';
                            if ($submission['status'] === 'approved') {
                                echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس مضمون کو شائع کرنا چاہتے ہیں؟' : 'Are you sure you want to publish this submission as a new article?') . '\');">';
                                echo '<input type="hidden" name="submission_id" value="' . $submission['id'] . '">';
                                echo '<button type="submit" name="publish_submission" class="btn btn-sm btn-success me-1" aria-label="Publish submission"><i class="fas fa-check"></i> ' . ($lang === 'ur' ? 'شائع کریں' : 'Publish') . '</button>';
                                echo '</form>';
                            }
                            echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس جمع کردہ مضمون کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this submission?') . '\');">';
                            echo '<input type="hidden" name="submission_id" value="' . $submission['id'] . '">';
                            echo '<button type="submit" name="delete_submission" class="btn btn-sm btn-danger" aria-label="Delete submission"><i class="fas fa-trash"></i></button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'تبصروں کا انتظام' : 'Manage Comments') . '</h4></div>';
                    echo '<div class="card-body">';
                    $comments = $conn->query("
                        SELECT c.*, u.username, a.title_en 
                        FROM comments c 
                        LEFT JOIN users u ON c.user_id = u.id 
                        LEFT JOIN articles a ON c.article_id = a.id 
                        ORDER BY c.created_at DESC
                    ");
                    if ($comments->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی تبصرہ نہیں ہے۔' : 'No comments to manage.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'مضمون' : 'Article') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'مصنف' : 'Author') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'تبصرہ' : 'Comment') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'والدین' : 'Parent') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'تاریخ' : 'Date') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($comment = $comments->fetch_assoc()) {
                            $author = htmlspecialchars($comment['username'] ?: ($comment['name'] ?? ($lang === 'ur' ? 'مہمان' : 'Guest')));
                            $status_class = '';
                            if ($comment['status'] === 'approved')
                                $status_class = 'bg-success';
                            else if ($comment['status'] === 'pending')
                                $status_class = 'bg-warning text-dark';
                            else
                                $status_class = 'bg-danger';
                            $parent_comment_text = 'N/A';
                            if ($comment['parent_comment_id']) {
                                $stmt_parent_comment = $conn->prepare("SELECT comment FROM comments WHERE id = ?");
                                $stmt_parent_comment->bind_param("i", $comment['parent_comment_id']);
                                $stmt_parent_comment->execute();
                                $parent_result = $stmt_parent_comment->get_result();
                                if ($parent_row = $parent_result->fetch_assoc()) {
                                    $parent_comment_text = htmlspecialchars(mb_strimwidth($parent_row['comment'], 0, 30, "..."));
                                }
                            }
                            echo '<tr>';
                            echo '<td><a href="?view=article&id=' . $comment['article_id'] . '&lang=' . $lang . '" class="text-decoration-none">' . htmlspecialchars(mb_strimwidth($comment['title_en'] ?? ($lang === 'ur' ? 'حذف شدہ مضمون' : 'Deleted Article'), 0, 30, "...")) . '</a></td>';
                            echo '<td>' . $author . '</td>';
                            echo '<td>' . htmlspecialchars(mb_strimwidth($comment['comment'], 0, 50, "...")) . '</td>';
                            echo '<td>' . $parent_comment_text . '</td>';
                            echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($comment['status']) . '</span></td>';
                            echo '<td dir="ltr">' . date('M j, Y', strtotime($comment['created_at'])) . '</td>';
                            echo '<td>';
                            echo '<form method="POST" class="d-inline-block me-1">';
                            echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
                            echo '<select name="status" class="form-select form-select-sm d-inline-block w-auto me-1" onchange="this.form.submit()" aria-label="Update comment status">';
                            echo '<option value="pending"' . ($comment['status'] === 'pending' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'منتظر' : 'Pending') . '</option>';
                            echo '<option value="approved"' . ($comment['status'] === 'approved' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'منظور شدہ' : 'Approved') . '</option>';
                            echo '<option value="rejected"' . ($comment['status'] === 'rejected' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'مسترد' : 'Rejected') . '</option>';
                            echo '</select>';
                            echo '<input type="hidden" name="update_comment_status" value="1">';
                            echo '</form>';
                            echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس تبصرہ کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this comment?') . '\');">';
                            echo '<input type="hidden" name="comment_id" value="' . $comment['id'] . '">';
                            echo '<button type="submit" name="delete_comment" class="btn btn-sm btn-danger" aria-label="Delete comment"><i class="fas fa-trash"></i></button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="tab-pane fade" id="categories" role="tabpanel" aria-labelledby="categories-tab">';
                    echo '<div class="card mb-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'نئی کیٹگری شامل کریں' : 'Add New Category') . '</h4></div>';
                    echo '<div class="card-body">';
                    echo '<form method="POST" class="needs-validation" novalidate>';
                    echo '<div class="mb-3">';
                    echo '<label for="categoryNameEn" class="form-label">' . ($lang === 'ur' ? 'کیٹگری کا نام (انگریزی)' : 'Category Name (English)') . '</label>';
                    echo '<input type="text" name="name_en" id="categoryNameEn" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="categoryNameUr" class="form-label">' . ($lang === 'ur' ? 'کیٹگری کا نام (اردو)' : 'Category Name (Urdu)') . '</label>';
                    echo '<input type="text" name="name_ur" id="categoryNameUr" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.') . '</div>';
                    echo '</div>';
                    echo '<button type="submit" name="add_category" class="btn btn-primary">';
                    echo '<i class="fas fa-plus me-1"></i>' . ($lang === 'ur' ? 'کیٹگری شامل کریں' : 'Add Category');
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'کیٹگریز کا انتظام' : 'Manage Categories') . '</h4></div>';
                    echo '<div class="card-body">';
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name_en");
                    if ($categories->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی کیٹگری نہیں ہے۔' : 'No categories to manage.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'نام (انگریزی)' : 'Name (English)') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'نام (اردو)' : 'Name (Urdu)') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'سلاگ' : 'Slug') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($cat = $categories->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($cat['name_en']) . '</td>';
                            echo '<td>' . htmlspecialchars($cat['name_ur']) . '</td>';
                            echo '<td>' . htmlspecialchars($cat['slug']) . '</td>';
                            echo '<td>';
                            echo '<button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-id="' . $cat['id'] . '" data-name_en="' . htmlspecialchars($cat['name_en'], ENT_QUOTES) . '" data-name_ur="' . htmlspecialchars($cat['name_ur'], ENT_QUOTES) . '" aria-label="Edit category"><i class="fas fa-edit"></i></button>';
                            echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس کیٹگری کو حذف کرنا چاہتے ہیں؟ اس سے متعلقہ مضامین کی کیٹگری NULL ہو جائے گی۔' : 'Are you sure you want to delete this category? Related articles will have their category set to NULL.') . '\');">';
                            echo '<input type="hidden" name="category_id" value="' . $cat['id'] . '">';
                            echo '<button type="submit" name="delete_category" class="btn btn-sm btn-danger" aria-label="Delete category"><i class="fas fa-trash"></i></button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="tab-pane fade" id="polls" role="tabpanel" aria-labelledby="polls-tab">';
                    echo '<div class="card mb-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'نیا پول شامل کریں' : 'Add New Poll') . '</h4></div>';
                    echo '<div class="card-body">';
                    echo '<form method="POST" class="needs-validation" novalidate>';
                    echo '<div class="mb-3">';
                    echo '<label for="pollQuestionEn" class="form-label">' . ($lang === 'ur' ? 'سوال (انگریزی)' : 'Question (English)') . '</label>';
                    echo '<input type="text" name="question_en" id="pollQuestionEn" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'سوال درکار ہے۔' : 'Question is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="pollQuestionUr" class="form-label">' . ($lang === 'ur' ? 'سوال (اردو)' : 'Question (Urdu)') . '</label>';
                    echo '<input type="text" name="question_ur" id="pollQuestionUr" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'سوال درکار ہے۔' : 'Question is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label class="form-label">' . ($lang === 'ur' ? 'آپشنز (انگریزی)' : 'Options (English)') . '</label>';
                    echo '<div id="pollOptionsEn">';
                    echo '<div class="input-group mb-2"><input type="text" name="options_en[]" class="form-control" placeholder="Option 1"><button type="button" class="btn btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button></div>';
                    echo '<div class="input-group mb-2"><input type="text" name="options_en[]" class="form-control" placeholder="Option 2"><button type="button" class="btn btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button></div>';
                    echo '</div>';
                    echo '<button type="button" class="btn btn-secondary btn-sm" onclick="addPollOption(\'En\')">' . ($lang === 'ur' ? 'آپشن شامل کریں' : 'Add Option') . '</button>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label class="form-label">' . ($lang === 'ur' ? 'آپشنز (اردو)' : 'Options (Urdu)') . '</label>';
                    echo '<div id="pollOptionsUr">';
                    echo '<div class="input-group mb-2"><input type="text" name="options_ur[]" class="form-control" placeholder="آپشن 1"><button type="button" class="btn btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button></div>';
                    echo '<div class="input-group mb-2"><input type="text" name="options_ur[]" class="form-control" placeholder="آپشن 2"><button type="button" class="btn btn-outline-danger remove-option-btn"><i class="fas fa-times"></i></button></div>';
                    echo '</div>';
                    echo '<button type="button" class="btn btn-secondary btn-sm" onclick="addPollOption(\'Ur\')">' . ($lang === 'ur' ? 'آپشن شامل کریں' : 'Add Option') . '</button>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="pollExpiresAt" class="form-label">' . ($lang === 'ur' ? 'اختتامی تاریخ/وقت' : 'Expires At (Optional)') . '</label>';
                    echo '<input type="datetime-local" name="expires_at" id="pollExpiresAt" class="form-control">';
                    echo '<small class="form-text text-muted">' . ($lang === 'ur' ? 'اگر خالی چھوڑ دیا جائے تو ہمیشہ فعال رہے گا۔' : 'Leave empty to keep active indefinitely.') . '</small>';
                    echo '</div>';
                    echo '<button type="submit" name="add_poll" class="btn btn-primary">';
                    echo '<i class="fas fa-plus me-1"></i>' . ($lang === 'ur' ? 'پول شامل کریں' : 'Add Poll');
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'پول کا انتظام' : 'Manage Polls') . '</h4></div>';
                    echo '<div class="card-body">';
                    $polls_query = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");
                    if ($polls_query->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی پول نہیں ہے۔' : 'No polls to manage.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'سوال' : 'Question') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'بنایا گیا' : 'Created') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'میعاد ختم' : 'Expires') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($poll = $polls_query->fetch_assoc()) {
                            $question = $lang === 'ur' ? $poll['question_ur'] : $poll['question_en'];
                            $expires_at = $poll['expires_at'] ? date('M j, Y H:i', strtotime($poll['expires_at'])) : ($lang === 'ur' ? 'کبھی نہیں' : 'Never');
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars(mb_strimwidth($question, 0, 70, "...")) . '</td>';
                            echo '<td dir="ltr">' . date('M j, Y', strtotime($poll['created_at'])) . '</td>';
                            echo '<td dir="ltr">' . $expires_at . '</td>';
                            echo '<td>';
                            echo '<button class="btn btn-sm btn-info me-1 view-poll-results-btn" data-bs-toggle="modal" data-bs-target="#viewPollResultsModal" data-poll-id="' . $poll['id'] . '" data-question-en="' . htmlspecialchars($poll['question_en'], ENT_QUOTES) . '" data-question-ur="' . htmlspecialchars($poll['question_ur'], ENT_QUOTES) . '" data-options-en="' . htmlspecialchars($poll['options_en'], ENT_QUOTES) . '" data-options-ur="' . htmlspecialchars($poll['options_ur'], ENT_QUOTES) . '" aria-label="View Poll Results"><i class="fas fa-chart-bar"></i></button>';
                            echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس پول کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this poll?') . '\');">';
                            echo '<input type="hidden" name="poll_id" value="' . $poll['id'] . '">';
                            echo '<button type="submit" name="delete_poll" class="btn btn-sm btn-danger" aria-label="Delete poll"><i class="fas fa-trash"></i></button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    if (hasRole('admin')) {
                        echo '<div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">';
                        echo '<div class="card shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'صارفین کا انتظام' : 'Manage Users') . '</h4></div>';
                        echo '<div class="card-body">';
                        $users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
                        if ($users->num_rows === 0) {
                            echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی صارف نہیں ہے۔' : 'No users to manage.') . '</div>';
                        } else {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-hover">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . ($lang === 'ur' ? 'ID' : 'ID') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'یوزرنیم' : 'Username') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'ای میل' : 'Email') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'کردار' : 'Role') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'تاریخ' : 'Created At') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            while ($user = $users->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($user['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                                echo '<td>';
                                echo '<form method="POST" class="d-inline-block me-1">';
                                echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                                echo '<select name="role" class="form-select form-select-sm d-inline-block w-auto me-1" onchange="this.form.submit()" aria-label="Update user role">';
                                echo '<option value="admin"' . ($user['role'] === 'admin' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'ایڈمن' : 'Admin') . '</option>';
                                echo '<option value="editor"' . ($user['role'] === 'editor' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'ایڈیٹر' : 'Editor') . '</option>';
                                echo '<option value="public"' . ($user['role'] === 'public' ? ' selected' : '') . '>' . ($lang === 'ur' ? 'عوامی' : 'Public') . '</option>';
                                echo '</select>';
                                echo '<input type="hidden" name="update_user_role" value="1">';
                                echo '</form>';
                                echo '</td>';
                                echo '<td dir="ltr">' . date('M j, Y', strtotime($user['created_at'])) . '</td>';
                                echo '<td>';
                                if ($user['id'] !== $_SESSION['user_id']) {
                                    echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس صارف کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this user?') . '\');">';
                                    echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                                    echo '<button type="submit" name="delete_user" class="btn btn-sm btn-danger" aria-label="Delete user"><i class="fas fa-trash"></i></button>';
                                    echo '</form>';
                                } else {
                                    echo '<button class="btn btn-sm btn-secondary" disabled aria-label="Cannot delete own account"><i class="fas fa-user-slash"></i></button>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="tab-pane fade" id="ads" role="tabpanel" aria-labelledby="ads-tab">';
                        echo '<div class="card mb-4 shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'نیا اشتہار یونٹ شامل کریں' : 'Add New Ad Unit') . '</h4></div>';
                        echo '<div class="card-body">';
                        echo '<form method="POST" class="needs-validation" novalidate>';
                        echo '<div class="mb-3">';
                        echo '<label for="adName" class="form-label">' . ($lang === 'ur' ? 'نام' : 'Name') . '</label>';
                        echo '<input type="text" name="name" id="adName" class="form-control" required>';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.') . '</div>';
                        echo '</div>';
                        echo '<div class="mb-3">';
                        echo '<label for="adType" class="form-label">' . ($lang === 'ur' ? 'قسم' : 'Type') . '</label>';
                        echo '<select name="type" id="adType" class="form-select" required>';
                        echo '<option value="banner">Banner</option>';
                        echo '<option value="inline">Inline</option>';
                        echo '<option value="popup">Popup</option>';
                        echo '</select>';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'قسم درکار ہے۔' : 'Type is required.') . '</div>';
                        echo '</div>';
                        echo '<div class="mb-3">';
                        echo '<label for="adCode" class="form-label">' . ($lang === 'ur' ? 'اشتہار کوڈ (HTML/JavaScript)' : 'Ad Code (HTML/JavaScript)') . '</label>';
                        echo '<textarea name="code" id="adCode" class="form-control" rows="5" required></textarea>';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'کوڈ درکار ہے۔' : 'Code is required.') . '</div>';
                        echo '</div>';
                        echo '<div class="mb-3">';
                        echo '<label for="adLocation" class="form-label">' . ($lang === 'ur' ? 'مقام (مثال: home_top, article_middle)' : 'Location (e.g., home_top, article_middle)') . '</label>';
                        echo '<input type="text" name="location" id="adLocation" class="form-control" required>';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'مقام درکار ہے۔' : 'Location is required.') . '</div>';
                        echo '</div>';
                        echo '<div class="mb-3">';
                        echo '<label for="adStatus" class="form-label">' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</label>';
                        echo '<select name="status" id="adStatus" class="form-select" required>';
                        echo '<option value="active">Active</option>';
                        echo '<option value="inactive">Inactive</option>';
                        echo '</select>';
                        echo '</div>';
                        echo '<button type="submit" name="add_ad_unit" class="btn btn-primary">';
                        echo '<i class="fas fa-plus me-1"></i>' . ($lang === 'ur' ? 'اشتہار شامل کریں' : 'Add Ad Unit');
                        echo '</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="card shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'اشتہار یونٹس کا انتظام' : 'Manage Ad Units') . '</h4></div>';
                        echo '<div class="card-body">';
                        $ad_units = $conn->query("SELECT * FROM ad_units ORDER BY name");
                        if ($ad_units->num_rows === 0) {
                            echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی اشتہار یونٹ نہیں ہے۔' : 'No ad units to manage.') . '</div>';
                        } else {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-hover">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . ($lang === 'ur' ? 'نام' : 'Name') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'قسم' : 'Type') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'مقام' : 'Location') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'افعال' : 'Actions') . '</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            while ($ad = $ad_units->fetch_assoc()) {
                                $status_class = $ad['status'] === 'active' ? 'bg-success' : 'bg-warning text-dark';
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($ad['name']) . '</td>';
                                echo '<td>' . htmlspecialchars($ad['type']) . '</td>';
                                echo '<td>' . htmlspecialchars($ad['location']) . '</td>';
                                echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($ad['status']) . '</span></td>';
                                echo '<td>';
                                echo '<button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#editAdUnitModal" data-id="' . $ad['id'] . '" data-name="' . htmlspecialchars($ad['name'], ENT_QUOTES) . '" data-type="' . htmlspecialchars($ad['type'], ENT_QUOTES) . '" data-code="' . htmlspecialchars($ad['code'], ENT_QUOTES) . '" data-location="' . htmlspecialchars($ad['location'], ENT_QUOTES) . '" data-status="' . htmlspecialchars($ad['status'], ENT_QUOTES) . '" aria-label="Edit Ad Unit"><i class="fas fa-edit"></i></button>';
                                echo '<form method="POST" class="d-inline-block" onsubmit="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی اس اشتہار یونٹ کو حذف کرنا چاہتے ہیں؟' : 'Are you sure you want to delete this ad unit?') . '\');">';
                                echo '<input type="hidden" name="ad_id" value="' . $ad['id'] . '">';
                                echo '<button type="submit" name="delete_ad_unit" class="btn btn-sm btn-danger" aria-label="Delete Ad Unit"><i class="fas fa-trash"></i></button>';
                                echo '</form>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">';
                        echo '<div class="card shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>SEO ' . ($lang === 'ur' ? 'کا انتظام' : 'Management') . '</h4></div>';
                        echo '<div class="card-body">';
                        echo '<p>' . ($lang === 'ur' ? 'مضامین کے لیے SEO میٹا ڈیٹا کو براہ راست مضمون میں ترمیم کرنے والے سیکشن میں منظم کیا جا سکتا ہے۔ مزید عالمی SEO ترتیبات یہاں شامل کی جا سکتی ہیں۔' : 'SEO meta data for articles can be managed directly in the article editing section. More global SEO settings can be added here.') . '</p>';
                        echo '<p><strong>' . ($lang === 'ur' ? 'بروکن لنک چیکر:' : 'Broken Link Checker:') . '</strong></p>';
                        echo '<button type="button" class="btn btn-info" id="runBrokenLinkCheckerBtn">';
                        echo '<i class="fas fa-link-slash me-1"></i>' . ($lang === 'ur' ? 'بروکن لنکس چیک کریں' : 'Check Broken Links');
                        echo '</button>';
                        echo '<div id="brokenLinkResults" class="mt-3"></div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">';
                        echo '<div class="card shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'سرگرمی لاگز' : 'Activity Logs') . '</h4></div>';
                        echo '<div class="card-body">';
                        $logs_query = $conn->query("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.timestamp DESC LIMIT 100");
                        if ($logs_query->num_rows === 0) {
                            echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی سرگرمی لاگز نہیں ہیں۔' : 'No activity logs.') . '</div>';
                        } else {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-hover">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . ($lang === 'ur' ? 'تاریخ/وقت' : 'Date/Time') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'صارف' : 'User') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'عمل' : 'Action') . '</th>';
                            echo '<th>' . ($lang === 'ur' ? 'تفصیلات' : 'Details') . '</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            while ($log = $logs_query->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td dir="ltr">' . date('M j, Y H:i:s', strtotime($log['timestamp'])) . '</td>';
                                echo '<td>' . htmlspecialchars($log['username'] ?? ($lang === 'ur' ? 'سسٹم' : 'System')) . '</td>';
                                echo '<td>' . htmlspecialchars($log['action']) . '</td>';
                                echo '<td>' . htmlspecialchars($log['details']) . '</td>';
                                echo '</tr>';
                            }
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">';
                        echo '<div class="card shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'ڈیٹا بیک اپ اور بحال' : 'Data Backup & Restore') . '</h4></div>';
                        echo '<div class="card-body">';
                        echo '<div class="mb-3">';
                        echo '<p>' . ($lang === 'ur' ? 'اپنے موجودہ ڈیٹا کا ایک JSON بیک اپ فائل میں ڈاؤن لوڈ کریں۔' : 'Download a JSON backup of your current database data.') . '</p>';
                        echo '<button class="btn btn-success" onclick="exportData()" aria-label="Export Data">';
                        echo '<i class="fas fa-download me-1"></i>' . ($lang === 'ur' ? 'ڈیٹا بیک اپ کریں' : 'Backup Data');
                        echo '</button>';
                        echo '</div>';
                        echo '<hr>';
                        echo '<div class="mb-3">';
                        echo '<p>' . ($lang === 'ur' ? 'JSON بیک اپ فائل سے ڈیٹا بحال کریں۔ یہ موجودہ ڈیٹا کو اووررائڈ کر دے گا!' : 'Restore data from a JSON backup file. This will overwrite existing data!') . '</p>';
                        echo '<form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>';
                        echo '<div class="mb-3">';
                        echo '<label for="backupFile" class="form-label visually-hidden">' . ($lang === 'ur' ? 'بیک اپ فائل منتخب کریں' : 'Choose backup file') . '</label>';
                        echo '<input type="file" name="backup_file" id="backupFile" class="form-control" accept=".json" required aria-label="Backup file input">';
                        echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'براہ کرم JSON بیک اپ فائل منتخب کریں۔' : 'Please select a JSON backup file.') . '</div>';
                        echo '</div>';
                        echo '<button type="submit" name="import" class="btn btn-danger" onclick="return confirm(\'' . ($lang === 'ur' ? 'کیا آپ واقعی ڈیٹا بحال کرنا چاہتے ہیں؟ یہ تمام موجودہ ڈیٹا کو ہٹا دے گا اور اسے بیک اپ سے بدل دے گا۔' : 'Are you sure you want to restore data? This will clear all existing data and replace it with the backup.') . '\');" aria-label="Restore Data">';
                        echo '<i class="fas fa-upload me-1"></i>' . ($lang === 'ur' ? 'ڈیٹا بحال کریں' : 'Restore Data');
                        echo '</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    $views_data = $conn->query("
                        SELECT 
                            DATE_FORMAT(published_at, '%Y-%m') as month, 
                            SUM(views) as total_views 
                        FROM articles 
                        WHERE status = 'published' AND published_at <= NOW()
                        GROUP BY month 
                        ORDER BY month ASC LIMIT 6
                    ")->fetch_all(MYSQLI_ASSOC);
                    $views_labels = [];
                    $views_values = [];
                    foreach ($views_data as $row) {
                        $views_labels[] = $lang === 'ur' ? getUrduMonthName(date('n', strtotime($row['month']))) . ' ' . date('Y', strtotime($row['month'])) : date('M Y', strtotime($row['month']));
                        $views_values[] = $row['total_views'];
                    }
                    $registrations_data = $conn->query("
                        SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month, 
                            COUNT(*) as total_users 
                        FROM users 
                        GROUP BY month 
                        ORDER BY month ASC LIMIT 6
                    ")->fetch_all(MYSQLI_ASSOC);
                    $reg_labels = [];
                    $reg_values = [];
                    foreach ($registrations_data as $row) {
                        $reg_labels[] = $lang === 'ur' ? getUrduMonthName(date('n', strtotime($row['month']))) . ' ' . date('Y', strtotime($row['month'])) : date('M Y', strtotime($row['month']));
                        $reg_values[] = $row['total_users'];
                    }
                ?>
                    <script>
                        const articleViewsCtx = document.getElementById("articleViewsChart").getContext("2d");
                        new Chart(articleViewsCtx, {
                            type: "bar",
                            data: {
                                labels: <?= json_encode($views_labels) ?>,
                                datasets: [{
                                    label: "<?= ($lang === 'ur' ? 'مضامین کے مناظر' : 'Article Views') ?>",
                                    data: <?= json_encode($views_values) ?>,
                                    backgroundColor: "rgba(26, 54, 93, 0.7)",
                                    borderColor: "rgba(26, 54, 93, 1)",
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: "<?= ($lang === 'ur' ? 'کل مناظر' : 'Total Views') ?>"
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: "<?= ($lang === 'ur' ? 'مہینہ' : 'Month') ?>"
                                        }
                                    }
                                }
                            }
                        });
                        const userRegistrationsCtx = document.getElementById("userRegistrationsChart").getContext("2d");
                        new Chart(userRegistrationsCtx, {
                            type: "line",
                            data: {
                                labels: <?= json_encode($reg_labels) ?>,
                                datasets: [{
                                    label: "<?= ($lang === 'ur' ? 'صارف رجسٹریشن' : 'User Registrations') ?>",
                                    data: <?= json_encode($reg_values) ?>,
                                    fill: false,
                                    borderColor: "rgba(229, 62, 62, 0.7)",
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: "<?= ($lang === 'ur' ? 'کل صارفین' : 'Total Users') ?>"
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: "<?= ($lang === 'ur' ? 'مہینہ' : 'Month') ?>"
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php
                }
                function include_profile_view()
                {
                    global $conn, $lang;
                    if (!isLoggedIn()) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'براہ کرم لاگ ان کریں' : 'Please login to view your profile.') . '</div>';
                        return;
                    }
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                    $bio = $lang === 'ur' ? ($user['bio_ur'] ?: $user['bio_en']) : ($user['bio_en'] ?: $user['bio_ur']);
                    $social_links = json_decode($user['social_links'] ?? '{}', true);
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'صارف پروفائل' : 'User Profile') . '</h4></div>';
                    echo '<div class="card-body">';
                    if ($user['avatar']) {
                        echo '<img src="' . htmlspecialchars($user['avatar']) . '" class="profile-avatar" alt="' . htmlspecialchars($user['username']) . ' Avatar">';
                    } else {
                        echo '<img src="https://via.placeholder.com/120x120/ccc/white?text=Avatar" class="profile-avatar" alt="Default Avatar">';
                    }
                    echo '<h5><i class="fas fa-user-circle me-2"></i>' . htmlspecialchars($user['username']) . '</h5>';
                    echo '<p><strong>' . ($lang === 'ur' ? 'ای میل:' : 'Email:') . '</strong> ' . htmlspecialchars($user['email']) . '</p>';
                    echo '<p><strong>' . ($lang === 'ur' ? 'کردار:' : 'Role:') . '</strong> ' . ucfirst($user['role']) . '</p>';
                    echo '<p><strong>' . ($lang === 'ur' ? 'رجسٹریشن کی تاریخ:' : 'Member since:') . '</strong> ' . date('M j, Y', strtotime($user['created_at'])) . '</p>';
                    echo '<p><strong>' . ($lang === 'ur' ? 'بایو:' : 'Bio:') . '</strong> ' . nl2br(htmlspecialchars($bio)) . '</p>';
                    if (!empty($social_links)) {
                        echo '<p><strong>' . ($lang === 'ur' ? 'سوشل میڈیا:' : 'Social Media:') . '</strong> ';
                        if (!empty($social_links['facebook'])) {
                            echo '<a href="' . htmlspecialchars($social_links['facebook']) . '" target="_blank" class="profile-social-link"><i class="fab fa-facebook-f fa-lg"></i></a>';
                        }
                        if (!empty($social_links['twitter'])) {
                            echo '<a href="' . htmlspecialchars($social_links['twitter']) . '" target="_blank" class="profile-social-link"><i class="fab fa-twitter fa-lg"></i></a>';
                        }
                        if (!empty($social_links['linkedin'])) {
                            echo '<a href="' . htmlspecialchars($social_links['linkedin']) . '" target="_blank" class="profile-social-link"><i class="fab fa-linkedin-in fa-lg"></i></a>';
                        }
                        echo '</p>';
                    }
                    echo '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal" data-avatar="' . htmlspecialchars($user['avatar'] ?? '', ENT_QUOTES) . '" data-bio_en="' . htmlspecialchars($user['bio_en'] ?? '', ENT_QUOTES) . '" data-bio_ur="' . htmlspecialchars($user['bio_ur'] ?? '', ENT_QUOTES) . '" data-social_links=\'' . htmlspecialchars(json_encode($social_links), ENT_QUOTES) . '\'>';
                    echo '<i class="fas fa-edit me-1"></i>' . ($lang === 'ur' ? 'پروفائل میں ترمیم کریں' : 'Edit Profile');
                    echo '</button>';
                    echo '</div>';
                    echo '</div>';
                    if (canEdit()) {
                        $my_articles_query = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE author_id = ?");
                        $my_articles_query->bind_param("i", $user_id);
                        $my_articles_query->execute();
                        $my_articles = $my_articles_query->get_result()->fetch_assoc()['count'];
                        echo '<div class="card mt-4 shadow-sm">';
                        echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'میرے مضامین' : 'My Articles') . '</h4></div>';
                        echo '<div class="card-body">';
                        echo '<p><i class="fas fa-file-alt me-2"></i>' . ($lang === 'ur' ? 'کل مضامین: ' : 'Total Articles: ') . $my_articles . '</p>';
                        echo '<a href="?view=admin&lang=' . $lang . '#articles" class="btn btn-info btn-sm">' . ($lang === 'ur' ? 'مضامین کا انتظام کریں' : 'Manage My Articles') . '</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '<div class="card mt-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'میرے تبصرے' : 'My Comments') . '</h4></div>';
                    echo '<div class="card-body">';
                    $user_comments_query = $conn->prepare("
                        SELECT c.*, a.title_en, a.title_ur 
                        FROM comments c 
                        LEFT JOIN articles a ON c.article_id = a.id 
                        WHERE c.user_id = ? 
                        ORDER BY c.created_at DESC
                    ");
                    $user_comments_query->bind_param("i", $user_id);
                    $user_comments_query->execute();
                    $user_comments = $user_comments_query->get_result();
                    if ($user_comments->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'آپ نے ابھی تک کوئی تبصرہ نہیں کیا ہے۔' : 'You have not made any comments yet.') . '</div>';
                    } else {
                        echo '<div class="list-group">';
                        while ($comment = $user_comments->fetch_assoc()) {
                            $article_title = $lang === 'ur' ? ($comment['title_ur'] ?? 'حذف شدہ مضمون') : ($comment['title_en'] ?? 'Deleted Article');
                            $status_text = ($lang === 'ur' ? 'حیثیت: ' : 'Status: ') . ($lang === 'ur' ? getUrduCommentStatus($comment['status']) : ucfirst($comment['status']));
                            $status_badge_class = '';
                            if ($comment['status'] === 'approved')
                                $status_badge_class = 'badge bg-success';
                            else if ($comment['status'] === 'pending')
                                $status_badge_class = 'badge bg-warning text-dark';
                            else
                                $status_badge_class = 'badge bg-danger';
                            echo '<div class="list-group-item list-group-item-action flex-column align-items-start">';
                            echo '<div class="d-flex w-100 justify-content-between">';
                            echo '<h6 class="mb-1"><a href="?view=article&id=' . $comment['article_id'] . '&lang=' . $lang . '">' . htmlspecialchars(mb_strimwidth($article_title, 0, 50, "...")) . '</a></h6>';
                            echo '<small dir="ltr">' . date('M j, Y H:i', strtotime($comment['created_at'])) . '</small>';
                            echo '</div>';
                            echo '<p class="mb-1">' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                            echo '<small><span class="' . $status_badge_class . '">' . $status_text . '</span></small>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card mt-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'مصنفین جنہیں میں فالو کرتا ہوں' : 'Authors I Follow') . '</h4></div>';
                    echo '<div class="card-body">';
                    $followed_authors_query = $conn->prepare("
                        SELECT u.id, u.username, u.avatar 
                        FROM followers f 
                        JOIN users u ON f.followed_id = u.id 
                        WHERE f.follower_id = ? 
                        ORDER BY u.username
                    ");
                    $followed_authors_query->bind_param("i", $user_id);
                    $followed_authors_query->execute();
                    $followed_authors = $followed_authors_query->get_result();
                    if ($followed_authors->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'آپ نے ابھی تک کسی مصنف کو فالو نہیں کیا ہے۔' : 'You are not following any authors yet.') . '</div>';
                    } else {
                        echo '<div class="list-group">';
                        while ($author = $followed_authors->fetch_assoc()) {
                            echo '<a href="?view=author&id=' . $author['id'] . '&lang=' . $lang . '" class="list-group-item list-group-item-action">';
                            if ($author['avatar']) {
                                echo '<img src="' . htmlspecialchars($author['avatar']) . '" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" class="me-2">';
                            } else {
                                echo '<img src="https://via.placeholder.com/30x30/ccc/white?text=A" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;" class="me-2">';
                            }
                            echo htmlspecialchars($author['username']);
                            echo '</a>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                function getUrduCommentStatus($status)
                {
                    switch ($status) {
                        case 'pending':
                            return 'منتظر';
                        case 'approved':
                            return 'منظور شدہ';
                        case 'rejected':
                            return 'مسترد';
                        default:
                            return $status;
                    }
                }
                function getUrduMonthName($monthNumber)
                {
                    $urduMonths = [
                        1 => 'جنوری',
                        2 => 'فروری',
                        3 => 'مارچ',
                        4 => 'اپریل',
                        5 => 'مئی',
                        6 => 'جون',
                        7 => 'جولائی',
                        8 => 'اگست',
                        9 => 'ستمبر',
                        10 => 'اکتوبر',
                        11 => 'نومبر',
                        12 => 'دسمبر'
                    ];
                    return $urduMonths[(int)$monthNumber] ?? '';
                }
                function include_breaking_news_view()
                {
                    global $conn, $lang;
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'بریکنگ نیوز' : 'Breaking News') . '</h2>';
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE a.is_breaking = 1 AND a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY a.published_at DESC 
                        LIMIT ? OFFSET ?
                    ";
                    $stmt = $conn->prepare($articles_query);
                    $stmt->bind_param("ii", $limit, $offset);
                    $stmt->execute();
                    $articles = $stmt->get_result();
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'کوئی بریکنگ نیوز دستیاب نہیں ہے۔' : 'No breaking news available.') . '</div>';
                    }
                    echo '<div class="row">';
                    while ($article = $articles->fetch_assoc()) {
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at'])) . ' • ';
                        echo '<i class="fas fa-eye me-1"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views');
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                    $total_articles_breaking_query = $conn->query("SELECT COUNT(*) as count FROM articles WHERE is_breaking = 1 AND status = 'published' AND published_at <= NOW()");
                    $total_articles_breaking = $total_articles_breaking_query->fetch_assoc()['count'];
                    $total_pages_breaking = ceil($total_articles_breaking / $limit);
                    if ($total_pages_breaking > 1) {
                        echo '<nav aria-label="Breaking news page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages_breaking; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?view=breaking&page=' . $i . '&lang=' . $lang . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                }
                function include_archive_view()
                {
                    global $conn, $lang;
                    $year = $_GET['year'] ?? null;
                    $month = $_GET['month'] ?? null;
                    if (!$year || !$month) {
                        echo '<div class="alert alert-warning" role="alert">' . ($lang === 'ur' ? 'آرکائیو کی تفصیلات نامکمل ہیں۔' : 'Archive details are incomplete.') . '</div>';
                        return;
                    }
                    $month_name_en = date('F', mktime(0, 0, 0, $month, 1, $year));
                    $display_heading = $lang === 'ur' ? "$month_name_en $year کی آرکائیو" : "Archive for $month_name_en $year";
                    echo '<h2 class="mb-4">' . $display_heading . '</h2>';
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE YEAR(a.published_at) = ? AND MONTH(a.published_at) = ? AND a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY a.published_at DESC 
                        LIMIT ? OFFSET ?
                    ";
                    $stmt = $conn->prepare($articles_query);
                    $stmt->bind_param("iiii", $year, $month, $limit, $offset);
                    $stmt->execute();
                    $articles = $stmt->get_result();
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'اس مہینے کے لیے کوئی مضمون دستیاب نہیں ہے۔' : 'No articles available for this month.') . '</div>';
                    }
                    echo '<div class="row">';
                    while ($article = $articles->fetch_assoc()) {
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at'])) . ' • ';
                        echo '<i class="fas fa-eye me-1"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views');
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                    $total_articles_archive_query = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE YEAR(published_at) = ? AND MONTH(published_at) = ? AND status = 'published' AND published_at <= NOW()");
                    $total_articles_archive_query->bind_param("ii", $year, $month);
                    $total_articles_archive_query->execute();
                    $total_articles_archive = $total_articles_archive_query->get_result()->fetch_assoc()['count'];
                    $total_pages_archive = ceil($total_articles_archive / $limit);
                    if ($total_pages_archive > 1) {
                        echo '<nav aria-label="Archive page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages_archive; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?view=archive&year=' . $year . '&month=' . $month . '&page=' . $i . '&lang=' . $lang . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                }
                function include_user_submissions_view()
                {
                    global $conn, $lang;
                    if (!isLoggedIn()) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'براہ کرم لاگ ان کریں' : 'Please login to submit articles.') . '</div>';
                        return;
                    }
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'آپ کا مضمون جمع کروائیں' : 'Submit Your Article') . '</h2>';
                    echo '<div class="card mb-4 shadow-sm">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'نیا مضمون جمع کروائیں' : 'Submit New Article') . '</h4></div>';
                    echo '<div class="card-body">';
                    echo '<form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="submitTitleEn" class="form-label">' . ($lang === 'ur' ? 'انگریزی عنوان' : 'English Title') . '</label>';
                    echo '<input type="text" name="title_en" id="submitTitleEn" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="submitTitleUr" class="form-label">' . ($lang === 'ur' ? 'اردو عنوان' : 'Urdu Title') . '</label>';
                    echo '<input type="text" name="title_ur" id="submitTitleUr" class="form-control" required>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.') . '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="submitCategoryId" class="form-label">' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</label>';
                    echo '<select name="category_id" id="submitCategoryId" class="form-select" required>';
                    $categories = $conn->query("SELECT * FROM categories ORDER BY name_en");
                    while ($cat = $categories->fetch_assoc()) {
                        echo '<option value="' . $cat['id'] . '">' . ($lang === 'ur' ? $cat['name_ur'] : $cat['name_en']) . '</option>';
                    }
                    echo '</select>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'کیٹگری درکار ہے۔' : 'Category is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="col-md-6 mb-3">';
                    echo '<label for="submitArticleImage" class="form-label">' . ($lang === 'ur' ? 'تصویر' : 'Image') . '</label>';
                    echo '<input type="file" name="image" id="submitArticleImage" class="form-control" accept="image/*">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="submitContentEn" class="form-label">' . ($lang === 'ur' ? 'انگریزی مواد' : 'English Content') . '</label>';
                    echo '<textarea name="content_en" id="submitContentEn" class="form-control" rows="8" required></textarea>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.') . '</div>';
                    echo '</div>';
                    echo '<div class="mb-3">';
                    echo '<label for="submitContentUr" class="form-label">' . ($lang === 'ur' ? 'اردو مواد' : 'Urdu Content') . '</label>';
                    echo '<textarea name="content_ur" id="submitContentUr" class="form-control" rows="8" required></textarea>';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.') . '</div>';
                    echo '</div>';
                    echo '<button type="submit" name="submit_user_article" class="btn btn-primary">';
                    echo '<i class="fas fa-paper-plane me-1"></i>' . ($lang === 'ur' ? 'مضمون جمع کروائیں' : 'Submit Article');
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '<h3 class="mb-3 mt-4">' . ($lang === 'ur' ? 'آپ کے جمع کردہ مضامین' : 'Your Submitted Articles') . '</h3>';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-body">';
                    $user_submissions = $conn->prepare("
                        SELECT us.*, u.username, c.name_en, c.name_ur
                        FROM user_submissions us
                        LEFT JOIN users u ON us.user_id = u.id
                        LEFT JOIN categories c ON us.category_id = c.id
                        WHERE us.user_id = ?
                        ORDER BY us.submitted_at DESC
                    ");
                    $user_submissions->bind_param("i", $_SESSION['user_id']);
                    $user_submissions->execute();
                    $submissions_result = $user_submissions->get_result();
                    if ($submissions_result->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'آپ نے ابھی تک کوئی مضمون جمع نہیں کیا ہے۔' : 'You have not submitted any articles yet.') . '</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>' . ($lang === 'ur' ? 'عنوان' : 'Title') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'تاریخ' : 'Date') . '</th>';
                        echo '<th>' . ($lang === 'ur' ? 'حیثیت' : 'Status') . '</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($submission = $submissions_result->fetch_assoc()) {
                            $title = $lang === 'ur' ? $submission['title_ur'] : $submission['title_en'];
                            $category = $lang === 'ur' ? ($submission['name_ur'] ?? 'نامعلوم') : ($submission['name_en'] ?? 'Unknown');
                            $status_class = '';
                            if ($submission['status'] === 'pending')
                                $status_class = 'bg-warning text-dark';
                            else if ($submission['status'] === 'approved')
                                $status_class = 'bg-success';
                            else
                                $status_class = 'bg-danger';
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars(mb_strimwidth($title, 0, 70, "...")) . '</td>';
                            echo '<td>' . htmlspecialchars($category) . '</td>';
                            echo '<td dir="ltr">' . date('M j, Y', strtotime($submission['submitted_at'])) . '</td>';
                            echo '<td><span class="badge ' . $status_class . '">' . htmlspecialchars($submission['status']) . '</span></td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
                function include_polls_view()
                {
                    global $conn, $lang;
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'جاری پولز' : 'Current Polls') . '</h2>';
                    $polls_query = $conn->query("SELECT * FROM polls WHERE expires_at IS NULL OR expires_at > NOW() ORDER BY created_at DESC");
                    if ($polls_query->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'فی الحال کوئی فعال پول نہیں ہے۔' : 'No active polls available at the moment.') . '</div>';
                    } else {
                        while ($poll = $polls_query->fetch_assoc()) {
                            $question = $lang === 'ur' ? $poll['question_ur'] : $poll['question_en'];
                            $options = $lang === 'ur' ? json_decode($poll['options_ur'], true) : json_decode($poll['options_en'], true);
                            echo '<div class="card mb-4 shadow-sm">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . htmlspecialchars($question) . '</h5>';
                            $user_voted = false;
                            $user_vote_index = null;
                            if (isLoggedIn()) {
                                $stmt_check_vote = $conn->prepare("SELECT option_index FROM poll_votes WHERE poll_id = ? AND user_id = ?");
                                $stmt_check_vote->bind_param("ii", $poll['id'], $_SESSION['user_id']);
                                $stmt_check_vote->execute();
                                $vote_result = $stmt_check_vote->get_result();
                                if ($row = $vote_result->fetch_assoc()) {
                                    $user_voted = true;
                                    $user_vote_index = $row['option_index'];
                                }
                            }
                            if ($user_voted || ($poll['expires_at'] && strtotime($poll['expires_at']) <= time())) {
                                $total_votes_query = $conn->prepare("SELECT COUNT(*) as total_votes FROM poll_votes WHERE poll_id = ?");
                                $total_votes_query->bind_param("i", $poll['id']);
                                $total_votes_query->execute();
                                $total_votes = $total_votes_query->get_result()->fetch_assoc()['total_votes'];
                                echo '<div class="poll-results">';
                                if ($total_votes == 0) {
                                    echo '<p class="text-muted">' . ($lang === 'ur' ? 'ابھی تک کوئی ووٹ نہیں ہے۔' : 'No votes cast yet.') . '</p>';
                                } else {
                                    foreach ($options as $index => $option) {
                                        $option_votes_query = $conn->prepare("SELECT COUNT(*) as option_votes FROM poll_votes WHERE poll_id = ? AND option_index = ?");
                                        $option_votes_query->bind_param("ii", $poll['id'], $index);
                                        $option_votes_query->execute();
                                        $option_votes = $option_votes_query->get_result()->fetch_assoc()['option_votes'];
                                        $percentage = ($total_votes > 0) ? round(($option_votes / $total_votes) * 100) : 0;
                                        echo '<div class="mb-2">';
                                        echo '<strong>' . htmlspecialchars($option) . '</strong>';
                                        if ($user_voted && $user_vote_index == $index) {
                                            echo ' <span class="badge bg-info">' . ($lang === 'ur' ? 'آپ کا ووٹ' : 'Your Vote') . '</span>';
                                        }
                                        echo '<div class="poll-option-bar">';
                                        echo '<div class="poll-option-bar-fill" style="width: ' . $percentage . '%;"></div>';
                                        echo '<div class="poll-option-bar-text">' . $percentage . '% (' . $option_votes . ($lang === 'ur' ? ' ووٹ' : ' votes') . ')</div>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                                echo '<p class="text-muted mt-3">' . ($lang === 'ur' ? 'کل ووٹ: ' : 'Total Votes: ') . $total_votes . '</p>';
                                echo '</div>';
                            } else {
                                if (isLoggedIn()) {
                                    echo '<form class="vote-form" data-poll-id="' . $poll['id'] . '">';
                                    echo '<div class="poll-options">';
                                    foreach ($options as $index => $option) {
                                        echo '<label>';
                                        echo '<input type="radio" name="poll_option_' . $poll['id'] . '" value="' . $index . '" required>';
                                        echo htmlspecialchars($option);
                                        echo '</label>';
                                    }
                                    echo '</div>';
                                    echo '<button type="submit" class="btn btn-primary mt-3">' . ($lang === 'ur' ? 'ووٹ ڈالیں' : 'Cast Vote') . '</button>';
                                    echo '</form>';
                                } else {
                                    echo '<p class="text-muted">' . ($lang === 'ur' ? 'ووٹ ڈالنے کے لیے <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">لاگ ان کریں</a>۔' : 'Please <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">login</a> to vote.') . '</p>';
                                }
                            }
                            echo '</div></div>';
                        }
                    }
                }
                function include_subscribe_view()
                {
                    global $conn, $lang;
                    echo '<h2 class="mb-4">' . ($lang === 'ur' ? 'نیوز لیٹر کو سبسکرائب کریں' : 'Subscribe to Newsletter') . '</h2>';
                    echo '<div class="card shadow-sm">';
                    echo '<div class="card-body">';
                    echo '<p>' . ($lang === 'ur' ? 'تازہ ترین خبروں اور اپ ڈیٹس کے لیے ہمارے نیوز لیٹر کو سبسکرائب کریں۔' : 'Subscribe to our newsletter for the latest news and updates delivered to your inbox.') . '</p>';
                    echo '<form method="POST" action="?view=subscribe&lang=' . $lang . '" class="needs-validation" novalidate>';
                    echo '<div class="mb-3">';
                    echo '<label for="newsletterEmail" class="form-label visually-hidden">' . ($lang === 'ur' ? 'ای میل' : 'Email') . '</label>';
                    echo '<input type="email" name="email" id="newsletterEmail" class="form-control" placeholder="' . ($lang === 'ur' ? 'آپ کا ای میل ایڈریس' : 'Your email address') . '" required aria-label="Newsletter email input">';
                    echo '<div class="invalid-feedback">' . ($lang === 'ur' ? 'ایک درست ای میل ایڈریس درکار ہے۔' : 'A valid email address is required.') . '</div>';
                    echo '</div>';
                    echo '<button type="submit" name="subscribe_newsletter" class="btn btn-primary">';
                    echo '<i class="fas fa-envelope me-1"></i>' . ($lang === 'ur' ? 'سبسکرائب کریں' : 'Subscribe');
                    echo '</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                }
                function generate_rss()
                {
                    global $conn;
                    header('Content-Type: application/rss+xml; charset=utf-8');
                    echo '<?xml version="1.0" encoding="UTF-8"?>';
                    echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
                    echo '<channel>';
                    echo '<title>Pakistan Times - Latest News</title>';
                    echo '<link>http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '</link>';
                    echo '<description>Latest news and updates from Pakistan Times, covering National, International, Sports, Technology, Business, Entertainment, and Opinion from Pakistan.</description>';
                    echo '<language>en-us</language>';
                    echo '<atom:link href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?view=rss" rel="self" type="application/rss+xml" />';
                    $articles = $conn->query("
                        SELECT * FROM articles 
                        WHERE status = 'published' AND published_at <= NOW()
                        ORDER BY published_at DESC 
                        LIMIT 20
                    ");
                    while ($article = $articles->fetch_assoc()) {
                        $article_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?view=article&id=' . $article['id'];
                        echo '<item>';
                        echo '<title>' . htmlspecialchars($article['title_en']) . '</title>';
                        echo '<link>' . htmlspecialchars($article_link) . '</link>';
                        echo '<guid>' . htmlspecialchars($article_link) . '</guid>';
                        echo '<pubDate>' . date('r', strtotime($article['published_at'])) . '</pubDate>';
                        echo '<description><![CDATA[';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" alt="' . htmlspecialchars($article['title_en']) . '" style="max-width: 100%; height: auto;"><br/>';
                        }
                        echo htmlspecialchars(mb_strimwidth(strip_tags($article['content_en']), 0, 500, "...")) . ']]>';
                        echo '</description>';
                        echo '</item>';
                    }
                    echo '</channel>';
                    echo '</rss>';
                    exit;
                }
                function include_author_view()
                {
                    global $conn, $lang;
                    $author_id = $_GET['id'] ?? null;
                    if (!$author_id) {
                        echo '<div class="alert alert-warning" role="alert">' . ($lang === 'ur' ? 'مصنف کا پروفائل نہیں ملا۔' : 'Author profile not found.') . '</div>';
                        return;
                    }
                    $stmt_author = $conn->prepare("SELECT id, username, email, role, avatar, bio_en, bio_ur, social_links FROM users WHERE id = ?");
                    $stmt_author->bind_param("i", $author_id);
                    $stmt_author->execute();
                    $author = $stmt_author->get_result()->fetch_assoc();
                    if (!$author) {
                        echo '<div class="alert alert-danger" role="alert">' . ($lang === 'ur' ? 'مصنف نہیں ملا' : 'Author not found') . '</div>';
                        return;
                    }
                    $bio = $lang === 'ur' ? ($author['bio_ur'] ?: ($author['bio_en'] ?: $author['bio_ur'])) : ($author['bio_en'] ?: ($author['bio_ur'] ?: $author['bio_en']));
                    $social_links = json_decode($author['social_links'] ?? '{}', true);
                    echo '<div class="card shadow-sm mb-4">';
                    echo '<div class="card-header bg-primary text-white"><h4>' . ($lang === 'ur' ? 'مصنف کا پروفائل' : 'Author Profile') . '</h4></div>';
                    echo '<div class="card-body text-center">';
                    if ($author['avatar']) {
                        echo '<img src="' . htmlspecialchars($author['avatar']) . '" class="profile-avatar" alt="' . htmlspecialchars($author['username']) . ' Avatar">';
                    } else {
                        echo '<img src="https://via.placeholder.com/120x120/ccc/white?text=Avatar" class="profile-avatar" alt="Default Avatar">';
                    }
                    echo '<h5>' . htmlspecialchars($author['username']) . ' <small class="text-muted">(' . ucfirst($author['role']) . ')</small></h5>';
                    echo '<p class="text-muted">' . htmlspecialchars($author['email']) . '</p>';
                    echo '<p>' . nl2br(htmlspecialchars($bio)) . '</p>';
                    if (!empty($social_links)) {
                        echo '<p>';
                        if (!empty($social_links['facebook'])) {
                            echo '<a href="' . htmlspecialchars($social_links['facebook']) . '" target="_blank" class="profile-social-link"><i class="fab fa-facebook-f fa-lg"></i></a>';
                        }
                        if (!empty($social_links['twitter'])) {
                            echo '<a href="' . htmlspecialchars($social_links['twitter']) . '" target="_blank" class="profile-social-link"><i class="fab fa-twitter fa-lg"></i></a>';
                        }
                        if (!empty($social_links['linkedin'])) {
                            echo '<a href="' . htmlspecialchars($social_links['linkedin']) . '" target="_blank" class="profile-social-link"><i class="fab fa-linkedin-in fa-lg"></i></a>';
                        }
                        echo '</p>';
                    }
                    if (isLoggedIn() && $_SESSION['user_id'] != $author_id) {
                        $is_following_stmt = $conn->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ? AND followed_id = ?");
                        $is_following_stmt->bind_param("ii", $_SESSION['user_id'], $author_id);
                        $is_following_stmt->execute();
                        $is_following = $is_following_stmt->get_result()->fetch_row()[0] > 0;
                        echo '<button class="btn btn-sm btn-outline-primary mt-3 follow-button" data-followed-id="' . $author_id . '">';
                        echo '<i class="fas fa-user-plus me-1"></i> <span class="follow-text">' . ($is_following ? ($lang === 'ur' ? 'فالو کر رہے ہیں' : 'Following') : ($lang === 'ur' ? 'فالو کریں' : 'Follow')) . '</span>';
                        echo '</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<h3 class="mb-4">' . ($lang === 'ur' ? 'مصنف کے دیگر مضامین' : 'Other Articles by ' . htmlspecialchars($author['username'])) . '</h3>';
                    $page = $_GET['page'] ?? 1;
                    $limit = 6;
                    $offset = ($page - 1) * $limit;
                    $articles_query = "
                        SELECT a.*, c.name_en, c.name_ur, u.username 
                        FROM articles a 
                        LEFT JOIN categories c ON a.category_id = c.id 
                        LEFT JOIN users u ON a.author_id = u.id 
                        WHERE a.author_id = ? AND a.status = 'published' AND a.published_at <= NOW()
                        ORDER BY a.published_at DESC 
                        LIMIT ? OFFSET ?
                    ";
                    $stmt_articles = $conn->prepare($articles_query);
                    $stmt_articles->bind_param("iii", $author_id, $limit, $offset);
                    $stmt_articles->execute();
                    $articles = $stmt_articles->get_result();
                    if ($articles->num_rows === 0) {
                        echo '<div class="alert alert-info" role="alert">' . ($lang === 'ur' ? 'اس مصنف کے کوئی مضامین دستیاب نہیں ہیں۔' : 'No articles available from this author.') . '</div>';
                    }
                    echo '<div class="row">';
                    while ($article = $articles->fetch_assoc()) {
                        $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
                        $category = $lang === 'ur' ? ($article['name_ur'] ?? 'نامعلوم') : ($article['name_en'] ?? 'Unknown');
                        echo '<div class="col-md-6 mb-4">';
                        echo '<div class="card h-100 shadow-sm">';
                        if ($article['image']) {
                            echo '<img src="' . htmlspecialchars($article['image']) . '" class="card-img-top" alt="' . htmlspecialchars($title) . '">';
                        } else {
                            echo '<img src="https://via.placeholder.com/600x400/ccc/white?text=No+Image" class="card-img-top" alt="No image available">';
                        }
                        echo '<div class="card-body d-flex flex-column">';
                        echo '<span class="category-badge">' . htmlspecialchars($category) . '</span>';
                        echo '<h5 class="card-title mt-2">' . htmlspecialchars($title);
                        if ($article['is_sponsored']) {
                            echo ' <span class="sponsored-badge">' . ($lang === 'ur' ? 'سپانسر شدہ' : 'Sponsored') . '</span>';
                        }
                        echo '</h5>';
                        echo '<p class="card-text">' . htmlspecialchars(substr(strip_tags($content), 0, 150)) . '...</p>';
                        echo '<div class="meta-info mt-auto">';
                        echo '<i class="fas fa-user me-1"></i>' . htmlspecialchars($article['username'] ?? ($lang === 'ur' ? 'نامعلوم مصنف' : 'Unknown Author')) . ' • ';
                        echo '<i class="fas fa-calendar me-1"></i>' . date('M j, Y', strtotime($article['published_at'])) . ' • ';
                        echo '<i class="fas fa-eye me-1"></i>' . $article['views'] . ' ' . ($lang === 'ur' ? 'مناظر' : 'views');
                        echo '</div>';
                        echo '<a href="?view=article&id=' . $article['id'] . '&lang=' . $lang . '" class="btn btn-primary mt-3">';
                        echo $lang === 'ur' ? 'مکمل پڑھیں' : 'Read More';
                        echo '</a>';
                        echo '</div></div></div>';
                    }
                    echo '</div>';
                    $total_articles_author_query = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE author_id = ? AND status = 'published' AND published_at <= NOW()");
                    $total_articles_author_query->bind_param("i", $author_id);
                    $total_articles_author_query->execute();
                    $total_articles_author = $total_articles_author_query->get_result()->fetch_assoc()['count'];
                    $total_pages_author = ceil($total_articles_author / $limit);
                    if ($total_pages_author > 1) {
                        echo '<nav aria-label="Author page navigation"><ul class="pagination">';
                        for ($i = 1; $i <= $total_pages_author; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<li class="page-item ' . $active . '">';
                            echo '<a class="page-link" href="?view=author&id=' . $author_id . '&page=' . $i . '&lang=' . $lang . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        echo '</ul></nav>';
                    }
                }
                ?>
            </div>
            <?php if ($view !== 'admin'): ?>
                <aside class="col-lg-4">
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-thermometer-half me-2"></i><?= $lang === 'ur' ? 'موجودہ موسم' : 'Current Weather' ?></h5>
                        <div id="weather-widget">
                            <p class="mb-0"><strong><?= $lang === 'ur' ? 'کراچی:' : 'Karachi:' ?></strong> 32°C, <?= $lang === 'ur' ? 'صاف' : 'Clear' ?></p>
                            <p class="mb-0"><strong><?= $lang === 'ur' ? 'لاہور:' : 'Lahore:' ?></strong> 35°C, <?= $lang === 'ur' ? 'جزوی طور پر بادل چھائے ہوئے ہیں' : 'Partly Cloudy' ?></p>
                            <p class="mb-0"><strong><?= $lang === 'ur' ? 'اسلام آباد:' : 'Islamabad:' ?></strong> 28°C, <?= $lang === 'ur' ? 'بارش' : 'Rainy' ?></p>
                            <small class="text-muted"><?= $lang === 'ur' ? 'ڈیٹا: نمائشی (اصل نہیں)' : 'Data: Demo (Not Live)' ?></small>
                        </div>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-fire me-2"></i><?= $lang === 'ur' ? 'سب سے زیادہ پڑھا گیا' : 'Most Read' ?></h5>
                        <?php
                        $most_read_query = $conn->query("
                        SELECT * FROM articles 
                        WHERE status = 'published' AND published_at <= NOW()
                        ORDER BY views DESC 
                        LIMIT 5
                    ");
                        $counter = 1;
                        if ($most_read_query->num_rows === 0) {
                            echo '<p class="text-muted">' . ($lang === 'ur' ? 'کوئی سب سے زیادہ پڑھا گیا مضمون نہیں ہے۔' : 'No most read articles yet.') . '</p>';
                        }
                        while ($article = $most_read_query->fetch_assoc()):
                            $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                        ?>
                            <div class="most-read-item">
                                <div class="most-read-number"><?= $counter ?></div>
                                <div>
                                    <a href="?view=article&id=<?= $article['id'] ?>&lang=<?= $lang ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars(mb_strimwidth($title, 0, 60, "...")) ?>
                                    </a>
                                    <div class="small text-muted" dir="ltr">
                                        <i class="fas fa-eye me-1"></i><span class="view-count"><?= $article['views'] ?></span> <?= $lang === 'ur' ? 'مناظر' : 'views' ?>
                                    </div>
                                </div>
                            </div>
                        <?php $counter++;
                        endwhile; ?>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-list me-2"></i><?= $lang === 'ur' ? 'اقسام' : 'Categories' ?></h5>
                        <?php
                        $categories_sidebar_query = $conn->query("
                        SELECT c.*, COUNT(a.id) as article_count 
                        FROM categories c 
                        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' AND a.published_at <= NOW()
                        GROUP BY c.id 
                        ORDER BY article_count DESC, c.name_en
                    ");
                        if ($categories_sidebar_query->num_rows === 0) {
                            echo '<p class="text-muted">' . ($lang === 'ur' ? 'کوئی کیٹگری نہیں ہے۔' : 'No categories available.') . '</p>';
                        }
                        while ($cat = $categories_sidebar_query->fetch_assoc()):
                            $cat_name = $lang === 'ur' ? $cat['name_ur'] : $cat['name_en'];
                        ?>
                            <a href="?view=category&cat=<?= $cat['slug'] ?>&lang=<?= $lang ?>" class="d-flex justify-content-between align-items-center py-2 border-bottom text-decoration-none text-dark">
                                <span><?= htmlspecialchars($cat_name) ?></span>
                                <span class="badge bg-secondary category-count"><?= $cat['article_count'] ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-archive me-2"></i><?= $lang === 'ur' ? 'آرکائیو' : 'Archive' ?></h5>
                        <?php
                        $archives_query = $conn->query("
                        SELECT 
                            YEAR(published_at) as year, 
                            MONTH(published_at) as month, 
                            MONTHNAME(published_at) as month_name,
                            COUNT(*) as count 
                        FROM articles 
                        WHERE status = 'published' AND published_at <= NOW()
                        GROUP BY YEAR(published_at), MONTH(published_at) 
                        ORDER BY year DESC, month DESC 
                        LIMIT 12
                    ");
                        if ($archives_query->num_rows === 0) {
                            echo '<p class="text-muted">' . ($lang === 'ur' ? 'کوئی آرکائیو نہیں ہے۔' : 'No archives available.') . '</p>';
                        }
                        while ($archive = $archives_query->fetch_assoc()):
                            $month_name_display = $lang === 'ur' ? getUrduMonthName($archive['month']) : $archive['month_name'];
                        ?>
                            <a href="?view=archive&year=<?= $archive['year'] ?>&month=<?= $archive['month'] ?>&lang=<?= $lang ?>" class="d-flex justify-content-between align-items-center py-2 border-bottom text-decoration-none text-dark">
                                <span><?= htmlspecialchars($month_name_display) ?> <?= $archive['year'] ?></span>
                                <span class="badge bg-secondary archive-count"><?= $archive['count'] ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-envelope me-2"></i><?= $lang === 'ur' ? 'نیوز لیٹر' : 'Newsletter' ?></h5>
                        <p><small><?= $lang === 'ur' ? 'تازہ ترین خبروں کے لیے سبسکرائب کریں!' : 'Subscribe for the latest news!' ?></small></p>
                        <form method="POST" action="?view=subscribe&lang=<?= $lang ?>" class="needs-validation" novalidate>
                            <div class="input-group mb-3">
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="<?= $lang === 'ur' ? 'آپ کا ای میل' : 'Your email' ?>" required aria-label="Newsletter subscription email">
                                <button class="btn btn-primary btn-sm" type="submit" name="subscribe_newsletter"><?= $lang === 'ur' ? 'سبسکرائب' : 'Subscribe' ?></button>
                                <div class="invalid-feedback"><?= $lang === 'ur' ? 'درست ای میل درکار ہے۔' : 'Valid email is required.' ?></div>
                            </div>
                        </form>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-rss me-2"></i><?= $lang === 'ur' ? 'آر ایس ایس فیڈ' : 'RSS Feed' ?></h5>
                        <a href="?view=rss" class="btn btn-outline-primary btn-sm w-100" target="_blank" aria-label="Subscribe to RSS Feed">
                            <i class="fas fa-rss me-1"></i><?= $lang === 'ur' ? 'سبسکرائب کریں' : 'Subscribe' ?>
                        </a>
                    </div>
                    <div class="sidebar print-hidden">
                        <h5><i class="fas fa-link me-2"></i><?= $lang === 'ur' ? 'فوری روابط' : 'Quick Links' ?></h5>
                        <div class="d-grid gap-2">
                            <a href="?view=breaking&lang=<?= $lang ?>" class="btn btn-outline-danger btn-sm" aria-label="View Breaking News">
                                <i class="fas fa-bolt me-1"></i><?= $lang === 'ur' ? 'بریکنگ نیوز' : 'Breaking News' ?>
                            </a>
                            <a href="mailto:info@pakistantimes.pk" class="btn btn-outline-primary btn-sm" aria-label="Contact Us via Email">
                                <i class="fas fa-envelope me-1"></i><?= $lang === 'ur' ? 'رابطہ کریں' : 'Contact Us' ?>
                            </a>
                        </div>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </main>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel"><?= $lang === 'ur' ? 'لاگ ان / رجسٹر' : 'Login / Register' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="loginForm" class="needs-validation" novalidate>
                        <h6><?= $lang === 'ur' ? 'لاگ ان' : 'Login' ?></h6>
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label visually-hidden"><?= $lang === 'ur' ? 'صارف نام یا ای میل' : 'Username or Email' ?></label>
                            <input type="text" name="username" id="loginUsername" class="form-control" placeholder="<?= $lang === 'ur' ? 'صارف نام یا ای میل' : 'Username or Email' ?>" required aria-label="Username or Email for login">
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'یوزرنیم یا ای میل درکار ہے۔' : 'Username or email is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label visually-hidden"><?= $lang === 'ur' ? 'پاس ورڈ' : 'Password' ?></label>
                            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="<?= $lang === 'ur' ? 'پاس ورڈ' : 'Password' ?>" required aria-label="Password for login">
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'پاس ورڈ درکار ہے۔' : 'Password is required.' ?></div>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100"><?= $lang === 'ur' ? 'لاگ ان' : 'Login' ?></button>
                    </form>
                    <hr>
                    <form method="POST" id="registerForm" class="needs-validation" novalidate>
                        <h6><?= $lang === 'ur' ? 'نیا اکاؤنٹ' : 'New Account' ?></h6>
                        <div class="mb-3">
                            <label for="registerUsername" class="form-label visually-hidden"><?= $lang === 'ur' ? 'صارف نام' : 'Username' ?></label>
                            <input type="text" name="username" id="registerUsername" class="form-control" placeholder="<?= $lang === 'ur' ? 'صارف نام' : 'Username' ?>" required aria-label="Username for registration">
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'یوزرنیم درکار ہے۔' : 'Username is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label visually-hidden"><?= $lang === 'ur' ? 'ای میل' : 'Email' ?></label>
                            <input type="email" name="email" id="registerEmail" class="form-control" placeholder="<?= $lang === 'ur' ? 'ای میل' : 'Email' ?>" required aria-label="Email for registration">
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'درست ای میل فارمیٹ' : 'Invalid email format.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label visually-hidden"><?= $lang === 'ur' ? 'پاس ورڈ' : 'Password' ?></label>
                            <input type="password" name="password" id="registerPassword" class="form-control" placeholder="<?= $lang === 'ur' ? 'پاس ورڈ' : 'Password' ?>" required aria-label="Password for registration">
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'پاس ورڈ درکار ہے۔' : 'Password is required.' ?></div>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100"><?= $lang === 'ur' ? 'رجسٹر' : 'Register' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editArticleModal" tabindex="-1" aria-labelledby="editArticleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editArticleModalLabel"><?= $lang === 'ur' ? 'مضمون میں ترمیم کریں' : 'Edit Article' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" id="editArticleForm" class="needs-validation" novalidate>
                        <input type="hidden" name="article_id" id="editArticleId">
                        <input type="hidden" name="current_image" id="currentArticleImage">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editTitleEn" class="form-label"><?= $lang === 'ur' ? 'انگریزی عنوان' : 'English Title' ?></label>
                                <input type="text" name="title_en" id="editTitleEn" class="form-control" required>
                                <div class="invalid-feedback"><?= $lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.' ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTitleUr" class="form-label"><?= $lang === 'ur' ? 'اردو عنوان' : 'Urdu Title' ?></label>
                                <input type="text" name="title_ur" id="editTitleUr" class="form-control" required>
                                <div class="invalid-feedback"><?= $lang === 'ur' ? 'عنوان درکار ہے۔' : 'Title is required.' ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editCategoryId" class="form-label"><?= $lang === 'ur' ? 'کیٹگری' : 'Category' ?></label>
                                <select name="category_id" id="editCategoryId" class="form-select" required>
                                    <?php
                                    $categories_edit = $conn->query("SELECT * FROM categories ORDER BY name_en");
                                    while ($cat_edit = $categories_edit->fetch_assoc()) {
                                        echo '<option value="' . $cat_edit['id'] . '">' . ($lang === 'ur' ? $cat_edit['name_ur'] : $cat_edit['name_en']) . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback"><?= $lang === 'ur' ? 'کیٹگری درکار ہے۔' : 'Category is required.' ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editStatus" class="form-label"><?= $lang === 'ur' ? 'حیثیت' : 'Status' ?></label>
                                <select name="status" id="editStatus" class="form-select" required>
                                    <option value="published"><?= $lang === 'ur' ? 'شائع شدہ' : 'Published' ?></option>
                                    <option value="draft"><?= $lang === 'ur' ? 'مسودہ' : 'Draft' ?></option>
                                </select>
                                <div class="invalid-feedback"><?= $lang === 'ur' ? 'حیثیت درکار ہے۔' : 'Status is required.' ?></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editPublishedAt" class="form-label"><?= $lang === 'ur' ? 'شائع ہونے کی تاریخ/وقت' : 'Publish Date/Time' ?></label>
                            <input type="datetime-local" name="published_at" id="editPublishedAt" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editArticleImage" class="form-label"><?= $lang === 'ur' ? 'تصویر اپ ڈیٹ کریں' : 'Update Image' ?></label>
                            <input type="file" name="image" id="editArticleImage" class="form-control" accept="image/*">
                            <div id="editImagePreview" class="mt-2"></div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="removeImageCheckbox" name="remove_image" value="1">
                                <label class="form-check-label" for="removeImageCheckbox">
                                    <?= $lang === 'ur' ? 'تصویر ہٹائیں' : 'Remove Image' ?>
                                </label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 form-check">
                                <input class="form-check-input" type="checkbox" name="is_breaking" id="editIsBreaking">
                                <label class="form-check-label" for="editIsBreaking"><?= $lang === 'ur' ? 'بریکنگ نیوز' : 'Breaking News' ?></label>
                            </div>
                            <div class="col-md-6 form-check">
                                <input class="form-check-input" type="checkbox" name="is_sponsored" id="editIsSponsored">
                                <label class="form-check-label" for="editIsSponsored"><?= $lang === 'ur' ? 'سپانسر شدہ مواد' : 'Sponsored Content' ?></label>
                            </div>
                        </div>
                        <hr>
                        <h5><?= $lang === 'ur' ? 'SEO کی تفصیلات' : 'SEO Details' ?></h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSeoMetaTitleEn" class="form-label">SEO Meta Title (English)</label>
                                <input type="text" name="seo_meta_title_en" id="editSeoMetaTitleEn" class="form-control" maxlength="60">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSeoMetaTitleUr" class="form-label">SEO Meta Title (Urdu)</label>
                                <input type="text" name="seo_meta_title_ur" id="editSeoMetaTitleUr" class="form-control" maxlength="60">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSeoMetaDescriptionEn" class="form-label">SEO Meta Description (English)</label>
                                <textarea name="seo_meta_description_en" id="editSeoMetaDescriptionEn" class="form-control" rows="2" maxlength="160"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSeoMetaDescriptionUr" class="form-label">SEO Meta Description (Urdu)</label>
                                <textarea name="seo_meta_description_ur" id="editSeoMetaDescriptionUr" class="form-control" rows="2" maxlength="160"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSeoKeywordsEn" class="form-label">SEO Keywords (English)</label>
                                <input type="text" name="seo_keywords_en" id="editSeoKeywordsEn" class="form-control" placeholder="comma,separated,keywords">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSeoKeywordsUr" class="form-label">SEO Keywords (Urdu)</label>
                                <input type="text" name="seo_keywords_ur" id="editSeoKeywordsUr" class="form-control" placeholder="کوما،سے،علیحدہ،الفاظ">
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label for="editContentEn" class="form-label"><?= $lang === 'ur' ? 'انگریزی مواد' : 'English Content' ?></label>
                            <textarea name="content_en" id="editContentEn" class="form-control" rows="8" required></textarea>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editContentUr" class="form-label"><?= $lang === 'ur' ? 'اردو مواد' : 'Urdu Content' ?></label>
                            <textarea name="content_ur" id="editContentUr" class="form-control" rows="8" required></textarea>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'مواد درکار ہے۔' : 'Content is required.' ?></div>
                        </div>
                        <button type="submit" name="update_article" class="btn btn-primary"><?= $lang === 'ur' ? 'مضمون اپ ڈیٹ کریں' : 'Update Article' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel"><?= $lang === 'ur' ? 'کیٹگری میں ترمیم کریں' : 'Edit Category' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editCategoryForm" class="needs-validation" novalidate>
                        <input type="hidden" name="category_id" id="editCategoryIdModal">
                        <div class="mb-3">
                            <label for="editCategoryNameEn" class="form-label"><?= $lang === 'ur' ? 'کیٹگری کا نام (انگریزی)' : 'Category Name (English)' ?></label>
                            <input type="text" name="name_en" id="editCategoryNameEn" class="form-control" required>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryNameUr" class="form-label"><?= $lang === 'ur' ? 'کیٹگری کا نام (اردو)' : 'Category Name (Urdu)' ?></label>
                            <input type="text" name="name_ur" id="editCategoryNameUr" class="form-control" required>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.' ?></div>
                        </div>
                        <button type="submit" name="update_category" class="btn btn-primary"><?= $lang === 'ur' ? 'کیٹگری اپ ڈیٹ کریں' : 'Update Category' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSubmissionModalLabel"><?= $lang === 'ur' ? 'جمع کردہ مضمون دیکھیں' : 'View Submitted Article' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 id="submissionTitleEn"></h4>
                    <p id="submissionTitleUr" class="text-muted"></p>
                    <p><strong><?= $lang === 'ur' ? 'جمع کنندہ:' : 'Submitter:' ?></strong> <span id="submissionSubmitter"></span></p>
                    <p><strong><?= $lang === 'ur' ? 'کیٹگری:' : 'Category:' ?></strong> <span id="submissionCategory"></span></p>
                    <p><strong><?= $lang === 'ur' ? 'حیثیت:' : 'Status:' ?></strong> <span id="submissionStatus" class="badge"></span></p>
                    <div id="submissionImage" class="mb-3"></div>
                    <h6><?= $lang === 'ur' ? 'انگریزی مواد:' : 'English Content:' ?></h6>
                    <p id="submissionContentEn"></p>
                    <h6><?= $lang === 'ur' ? 'اردو مواد:' : 'Urdu Content:' ?></h6>
                    <p id="submissionContentUr"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $lang === 'ur' ? 'بند کریں' : 'Close' ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel"><?= $lang === 'ur' ? 'پروفائل میں ترمیم کریں' : 'Edit Profile' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" id="editProfileForm" class="needs-validation" novalidate>
                        <input type="hidden" name="current_avatar" id="currentAvatar">
                        <div class="mb-3 text-center">
                            <label for="profileAvatar" class="form-label"><?= $lang === 'ur' ? 'اواتار اپ ڈیٹ کریں' : 'Update Avatar' ?></label><br>
                            <img id="avatarPreview" src="https://via.placeholder.com/120x120/ccc/white?text=Avatar" class="profile-avatar mb-2" alt="Avatar Preview">
                            <input type="file" name="avatar" id="profileAvatar" class="form-control" accept="image/*">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="removeAvatarCheckbox" name="remove_avatar" value="1">
                                <label class="form-check-label" for="removeAvatarCheckbox">
                                    <?= $lang === 'ur' ? 'اواتار ہٹائیں' : 'Remove Avatar' ?>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bioEn" class="form-label">Bio (English)</label>
                            <textarea name="bio_en" id="bioEn" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="bioUr" class="form-label">Bio (Urdu)</label>
                            <textarea name="bio_ur" id="bioUr" class="form-control" rows="3"></textarea>
                        </div>
                        <h6>Social Links</h6>
                        <div class="mb-3">
                            <label for="socialFacebook" class="form-label">Facebook URL</label>
                            <input type="url" name="social_facebook" id="socialFacebook" class="form-control" placeholder="https://facebook.com/yourprofile">
                        </div>
                        <div class="mb-3">
                            <label for="socialTwitter" class="form-label">Twitter URL</label>
                            <input type="url" name="social_twitter" id="socialTwitter" class="form-control" placeholder="https://twitter.com/yourprofile">
                        </div>
                        <div class="mb-3">
                            <label for="socialLinkedin" class="form-label">LinkedIn URL</label>
                            <input type="url" name="social_linkedin" id="socialLinkedin" class="form-control" placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary"><?= $lang === 'ur' ? 'پروفائل اپ ڈیٹ کریں' : 'Update Profile' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewPollResultsModal" tabindex="-1" aria-labelledby="viewPollResultsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPollResultsModalLabel"><?= $lang === 'ur' ? 'پول کے نتائج' : 'Poll Results' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 id="pollResultsQuestion"></h5>
                    <div id="pollResultsDisplay"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $lang === 'ur' ? 'بند کریں' : 'Close' ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAdUnitModal" tabindex="-1" aria-labelledby="editAdUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdUnitModalLabel"><?= $lang === 'ur' ? 'اشتہار یونٹ میں ترمیم کریں' : 'Edit Ad Unit' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editAdUnitForm" class="needs-validation" novalidate>
                        <input type="hidden" name="ad_id" id="editAdId">
                        <div class="mb-3">
                            <label for="editAdName" class="form-label"><?= $lang === 'ur' ? 'نام' : 'Name' ?></label>
                            <input type="text" name="name" id="editAdName" class="form-control" required>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'نام درکار ہے۔' : 'Name is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAdType" class="form-label"><?= $lang === 'ur' ? 'قسم' : 'Type' ?></label>
                            <select name="type" id="editAdType" class="form-select" required>
                                <option value="banner">Banner</option>
                                <option value="inline">Inline</option>
                                <option value="popup">Popup</option>
                            </select>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'قسم درکار ہے۔' : 'Type is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAdCode" class="form-label"><?= $lang === 'ur' ? 'اشتہار کوڈ (HTML/JavaScript)' : 'Ad Code (HTML/JavaScript)' ?></label>
                            <textarea name="code" id="editAdCode" class="form-control" rows="5" required></textarea>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'کوڈ درکار ہے۔' : 'Code is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAdLocation" class="form-label"><?= $lang === 'ur' ? 'مقام' : 'Location' ?></label>
                            <input type="text" name="location" id="editAdLocation" class="form-control" required>
                            <div class="invalid-feedback"><?= $lang === 'ur' ? 'مقام درکار ہے۔' : 'Location is required.' ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="editAdStatus" class="form-label"><?= $lang === 'ur' ? 'حیثیت' : 'Status' ?></label>
                            <select name="status" id="editAdStatus" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" name="update_ad_unit" class="btn btn-primary"><?= $lang === 'ur' ? 'اشتہار یونٹ اپ ڈیٹ کریں' : 'Update Ad Unit' ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="revisionHistoryModal" tabindex="-1" aria-labelledby="revisionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revisionHistoryModalLabel"><?= $lang === 'ur' ? 'نظرثانی کی تاریخ' : 'Revision History' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="revisionsContent">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $lang === 'ur' ? 'بند کریں' : 'Close' ?></button>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer print-hidden">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5><?= $lang === 'ur' ? 'پاکستان ٹائمز' : 'Pakistan Times' ?></h5>
                    <p><?= $lang === 'ur' ? 'پاکستان کا بہترین آن لائن اخبار، آپ کو تازہ ترین خبروں اور تجزیوں کے ساتھ باخبر رکھتا ہے۔' : 'Pakistan\'s premier online newspaper, keeping you informed with the latest news and analysis.' ?></p>
                    <div class="social-links mt-3">
                        <a href="https://www.facebook.com/PakistanTimesOfficial" target="_blank" class="me-3 text-white" aria-label="Facebook"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="https://twitter.com/PakistanTimes" target="_blank" class="me-3 text-white" aria-label="Twitter"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="https://www.youtube.com/PakistanTimes" target="_blank" class="me-3 text-white" aria-label="YouTube"><i class="fab fa-youtube fa-lg"></i></a>
                        <a href="https://www.instagram.com/PakistanTimes" target="_blank" class="text-white" aria-label="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5><?= $lang === 'ur' ? 'اقسام' : 'Categories' ?></h5>
                    <ul class="list-unstyled">
                        <?php
                        $footer_cats = $conn->query("SELECT * FROM categories ORDER BY name_en LIMIT 5");
                        while ($cat = $footer_cats->fetch_assoc()):
                        ?>
                            <li><a href="?view=category&cat=<?= $cat['slug'] ?>&lang=<?= $lang ?>">
                                    <?= $lang === 'ur' ? $cat['name_ur'] : $cat['name_en'] ?>
                                </a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5><?= $lang === 'ur' ? 'فوری روابط' : 'Quick Links' ?></h5>
                    <ul class="list-unstyled">
                        <li><a href="?lang=<?= $lang ?>"><?= $lang === 'ur' ? 'ہوم' : 'Home' ?></a></li>
                        <li><a href="?view=rss" target="_blank"><?= $lang === 'ur' ? 'آر ایس ایس فیڈ' : 'RSS Feed' ?></a></li>
                        <li><a href="mailto:info@pakistantimes.pk"><?= $lang === 'ur' ? 'رابطہ' : 'Contact' ?></a></li>
                        <li><a href="#"><?= $lang === 'ur' ? 'پرائیویسی پالیسی' : 'Privacy Policy' ?></a></li>
                        <li><a href="#"><?= $lang === 'ur' ? 'ہماری بارے میں' : 'About Us' ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5><?= $lang === 'ur' ? 'رابطہ کی معلومات' : 'Contact Info' ?></h5>
                    <address>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i><a href="mailto:info@pakistantimes.pk" class="text-white">info@pakistantimes.pk</a></p>
                        <p class="mb-1" dir="ltr"><i class="fas fa-phone me-2"></i><a href="tel:+922112345678" class="text-white">+92-21-12345678</a></p>
                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?= $lang === 'ur' ? 'کراچی، سندھ، پاکستان' : 'Karachi, Sindh, Pakistan' ?></p>
                    </address>
                </div>
            </div>
            <hr class="my-4 border-light opacity-50">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= $lang === 'ur' ? 'پاکستان ٹائمز۔ تمام حقوق محفوظ ہیں۔' : 'Pakistan Times. All rights reserved.' ?></p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0"><?= $lang === 'ur' ? 'تیار کردہ: یاسین اللہ، پاکستان' : 'Developed by Yasin Ullah, Pakistan' ?></p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleTheme() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            const icon = document.querySelector('.theme-toggle i');
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            const icon = document.querySelector('.theme-toggle i');
            if (icon) {
                icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('<?= $lang === 'ur' ? 'لنک کاپی ہو گیا!' : 'Link copied!' ?>');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function printArticle() {
            window.print();
        }
        document.addEventListener('DOMContentLoaded', function() {
            const searchBox = document.querySelector('.search-box');
            if (searchBox) {
                searchBox.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.closest('form').submit();
                    }
                });
            }
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.addEventListener('shown.bs.modal', function() {
                    document.getElementById('loginUsername').focus();
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            if (navbarToggler && navbarCollapse) {
                navbarToggler.addEventListener('click', function() {
                    const isExpanded = navbarToggler.getAttribute('aria-expanded') === 'true';
                    navbarToggler.setAttribute('aria-expanded', !isExpanded);
                });
                document.addEventListener('click', function(e) {
                    if (navbarCollapse.classList.contains('show') &&
                        !navbarToggler.contains(e.target) &&
                        !navbarCollapse.contains(e.target)) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        }
                    }
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        });

        function showLoading() {
            let loading = document.getElementById('loading-indicator');
            if (!loading) {
                loading = document.createElement('div');
                loading.className = 'loading';
                loading.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-3"><?= $lang === 'ur' ? 'براہ کرم انتظار کریں...' : 'Please wait...' ?></div>';
                loading.id = 'loading-indicator';
                document.body.appendChild(loading);
            }
            loading.style.display = 'flex';
        }

        function hideLoading() {
            const loading = document.getElementById('loading-indicator');
            if (loading) {
                loading.style.display = 'none';
            }
        }

        function exportData() {
            showLoading();
            fetch('?action=export&lang=<?= $lang ?>')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'newspaper_backup_' + new Date().toISOString().slice(0, 10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    hideLoading();
                })
                .catch(error => {
                    console.error('Export failed:', error);
                    alert('<?= $lang === 'ur' ? 'بیک اپ بنانے میں ناکامی: ' : 'Failed to create backup: ' ?>' + error.message);
                    hideLoading();
                });
        }

        function importData(input) {
            const file = input.files[0];
            if (file) {
                showLoading();
                const formData = new FormData();
                formData.append('backup_file', file);
                formData.append('import', '1');
                formData.append('lang', '<?= $lang ?>');
                fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            if (data.success) {
                                alert('<?= $lang === 'ur' ? 'ڈیٹا کامیابی سے بحال کر دیا گیا!' : 'Data restored successfully!' ?>');
                                location.reload();
                            } else {
                                alert('<?= $lang === 'ur' ? 'بحالی میں خرابی! ' : 'Restore failed! ' ?>' + (data.message || ''));
                            }
                        } catch (e) {
                            console.error("JSON parsing error:", e, "Response text:", text);
                            alert('<?= $lang === 'ur' ? 'بحالی میں خرابی! غلط سرور جواب۔' : 'Restore failed! Invalid server response.' ?>');
                        }
                        hideLoading();
                    })
                    .catch(error => {
                        console.error('Import failed:', error);
                        alert('<?= $lang === 'ur' ? 'بحالی میں خرابی: ' : 'Restore failed: ' ?>' + error.message);
                        hideLoading();
                    });
            }
        }

        function convertToUrduNumbers(str) {
            const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            const urduNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            let newStr = String(str);
            for (let i = 0; i < englishNumbers.length; i++) {
                newStr = newStr.replace(new RegExp(englishNumbers[i], 'g'), urduNumbers[i]);
            }
            return newStr;
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (document.documentElement.lang === 'ur') {
                const numberElements = document.querySelectorAll('.stats-number, .badge, .most-read-number, .view-count, .category-count, .archive-count, .like-count');
                numberElements.forEach(function(element) {
                    element.textContent = convertToUrduNumbers(element.textContent);
                });
            }
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        if ('Notification' in window) {
            function requestNotificationPermission() {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        console.log('Notification permission granted');
                    }
                });
            }
            if (Notification.permission === 'default') {
                setTimeout(requestNotificationPermission, 3000);
            }
        }

        function shareWhatsApp(title, url) {
            const text = encodeURIComponent(title + ' - ' + url);
            window.open('https://wa.me/?text=' + text, '_blank');
        }

        function shareTwitter(title, url) {
            const text = encodeURIComponent(title);
            const encodedUrl = encodeURIComponent(url);
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodedUrl, '_blank');
        }

        function shareFacebook(url) {
            const encodedUrl = encodeURIComponent(url);
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl, '_blank');
        }

        function readArticle(content, lang) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(content);
                utterance.lang = (lang === 'ur' ? 'ur-PK' : 'en-US');
                const voices = speechSynthesis.getVoices();
                let selectedVoice = null;
                if (lang === 'ur') {
                    selectedVoice = voices.find(voice => voice.lang === 'ur-PK' || voice.name.includes('Urdu'));
                }
                if (!selectedVoice) {
                    selectedVoice = voices.find(voice => voice.lang === 'en-US' || voice.name.includes('English'));
                }
                if (selectedVoice) {
                    utterance.voice = selectedVoice;
                }
                speechSynthesis.speak(utterance);
            } else {
                alert('<?= $lang === 'ur' ? 'آپ کا براؤزر ٹیکسٹ ٹو سپیچ کو سپورٹ نہیں کرتا۔' : 'Your browser does not support text-to-speech.' ?>');
            }
        }

        function updateReadingProgress() {
            const articleContent = document.querySelector('.article-content');
            const progressBar = document.getElementById('reading-progress-bar');
            if (articleContent && progressBar) {
                const articleRect = articleContent.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                let progress = 0;
                if (articleRect.top < viewportHeight && articleRect.bottom > 0) {
                    const visibleHeight = Math.min(articleRect.bottom, viewportHeight) - Math.max(articleRect.top, 0);
                    if (articleRect.height > 0) {
                        progress = (visibleHeight / Math.min(articleRect.height, viewportHeight)) * 100;
                    }
                }
                if (articleRect.top <= 0) {
                    progress = ((-articleRect.top + viewportHeight) / articleRect.height) * 100;
                    progress = Math.min(100, progress);
                } else {
                    progress = 0;
                }
                progressBar.style.width = progress + '%';
            }
        }
        if (window.location.search.includes('view=article')) {
            window.addEventListener('scroll', updateReadingProgress);
            window.addEventListener('resize', updateReadingProgress);
            document.addEventListener('DOMContentLoaded', updateReadingProgress);
        }
        const editArticleModal = document.getElementById('editArticleModal');
        if (editArticleModal) {
            editArticleModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const articleId = button.getAttribute('data-id');
                const titleEn = button.getAttribute('data-title_en');
                const titleUr = button.getAttribute('data-title_ur');
                const contentEn = button.getAttribute('data-content_en');
                const contentUr = button.getAttribute('data-content_ur');
                const categoryId = button.getAttribute('data-category_id');
                const isBreaking = button.getAttribute('data-is_breaking');
                const status = button.getAttribute('data-status');
                const imagePath = button.getAttribute('data-image');
                const isSponsored = button.getAttribute('data-is_sponsored');
                const publishedAt = button.getAttribute('data-published_at');
                const seoMetaTitleEn = button.getAttribute('data-seo_meta_title_en');
                const seoMetaTitleUr = button.getAttribute('data-seo_meta_title_ur');
                const seoMetaDescriptionEn = button.getAttribute('data-seo_meta_description_en');
                const seoMetaDescriptionUr = button.getAttribute('data-seo_meta_description_ur');
                const seoKeywordsEn = button.getAttribute('data-seo_keywords_en');
                const seoKeywordsUr = button.getAttribute('data-seo_keywords_ur');
                const modalTitle = editArticleModal.querySelector('.modal-title');
                const form = editArticleModal.querySelector('form');
                const imagePreview = editArticleModal.querySelector('#editImagePreview');
                const removeImageCheckbox = editArticleModal.querySelector('#removeImageCheckbox');
                form.reset();
                form.classList.remove('was-validated');
                modalTitle.textContent = '<?= $lang === 'ur' ? 'مضمون میں ترمیم کریں' : 'Edit Article' ?> (ID: ' + articleId + ')';
                editArticleModal.querySelector('#editArticleId').value = articleId;
                editArticleModal.querySelector('#editTitleEn').value = titleEn;
                editArticleModal.querySelector('#editTitleUr').value = titleUr;
                editArticleModal.querySelector('#editContentEn').value = contentEn;
                editArticleModal.querySelector('#editContentUr').value = contentUr;
                editArticleModal.querySelector('#editCategoryId').value = categoryId;
                editArticleModal.querySelector('#editIsBreaking').checked = (isBreaking === '1');
                editArticleModal.querySelector('#editStatus').value = status;
                editArticleModal.querySelector('#currentArticleImage').value = imagePath;
                editArticleModal.querySelector('#editIsSponsored').checked = (isSponsored === '1');
                editArticleModal.querySelector('#editPublishedAt').value = publishedAt;
                editArticleModal.querySelector('#editSeoMetaTitleEn').value = seoMetaTitleEn;
                editArticleModal.querySelector('#editSeoMetaTitleUr').value = seoMetaTitleUr;
                editArticleModal.querySelector('#editSeoMetaDescriptionEn').value = seoMetaDescriptionEn;
                editArticleModal.querySelector('#editSeoMetaDescriptionUr').value = seoMetaDescriptionUr;
                editArticleModal.querySelector('#editSeoKeywordsEn').value = seoKeywordsEn;
                editArticleModal.querySelector('#editSeoKeywordsUr').value = seoKeywordsUr;
                imagePreview.innerHTML = '';
                removeImageCheckbox.checked = false;
                if (imagePath && imagePath !== 'null' && imagePath !== '') {
                    const img = document.createElement('img');
                    img.src = imagePath;
                    img.alt = 'Current Image';
                    img.style.maxWidth = '150px';
                    img.style.height = 'auto';
                    img.classList.add('img-thumbnail');
                    imagePreview.appendChild(img);
                } else {
                    imagePreview.innerHTML = '<?= $lang === 'ur' ? '<small class="text-muted">کوئی تصویر نہیں</small>' : '<small class="text-muted">No image</small>' ?>';
                }
            });
        }
        const editCategoryModal = document.getElementById('editCategoryModal');
        if (editCategoryModal) {
            editCategoryModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const categoryId = button.getAttribute('data-id');
                const nameEn = button.getAttribute('data-name_en');
                const nameUr = button.getAttribute('data-name_ur');
                const modalTitle = editCategoryModal.querySelector('.modal-title');
                const form = editCategoryModal.querySelector('form');
                form.reset();
                form.classList.remove('was-validated');
                modalTitle.textContent = '<?= $lang === 'ur' ? 'کیٹگری میں ترمیم کریں' : 'Edit Category' ?> (ID: ' + categoryId + ')';
                editCategoryModal.querySelector('#editCategoryIdModal').value = categoryId;
                editCategoryModal.querySelector('#editCategoryNameEn').value = nameEn;
                editCategoryModal.querySelector('#editCategoryNameUr').value = nameUr;
            });
        }
        const viewSubmissionModal = document.getElementById('viewSubmissionModal');
        if (viewSubmissionModal) {
            viewSubmissionModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const titleEn = button.getAttribute('data-title_en');
                const titleUr = button.getAttribute('data-title_ur');
                const contentEn = button.getAttribute('data-content_en');
                const contentUr = button.getAttribute('data-content_ur');
                const image = button.getAttribute('data-image');
                const category = button.getAttribute('data-category');
                const submitter = button.getAttribute('data-submitter');
                const status = button.getAttribute('data-status');
                const statusBadgeClass = (status === 'pending' ? 'bg-warning text-dark' : (status === 'approved' ? 'bg-success' : 'bg-danger'));
                viewSubmissionModal.querySelector('#submissionTitleEn').textContent = titleEn;
                viewSubmissionModal.querySelector('#submissionTitleUr').textContent = titleUr;
                viewSubmissionModal.querySelector('#submissionSubmitter').textContent = submitter;
                viewSubmissionModal.querySelector('#submissionCategory').textContent = category;
                const statusElement = viewSubmissionModal.querySelector('#submissionStatus');
                statusElement.textContent = status;
                statusElement.className = 'badge ' + statusBadgeClass;
                viewSubmissionModal.querySelector('#submissionContentEn').innerHTML = contentEn.replace(/\n/g, '<br>');
                viewSubmissionModal.querySelector('#submissionContentUr').innerHTML = contentUr.replace(/\n/g, '<br>');
                const submissionImageDiv = viewSubmissionModal.querySelector('#submissionImage');
                submissionImageDiv.innerHTML = '';
                if (image && image !== 'null' && image !== '') {
                    const img = document.createElement('img');
                    img.src = image;
                    img.alt = 'Submission Image';
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.classList.add('img-thumbnail');
                    submissionImageDiv.appendChild(img);
                } else {
                    submissionImageDiv.innerHTML = '<?= $lang === 'ur' ? '<small class="text-muted">کوئی تصویر نہیں</small>' : '<small class="text-muted">No image</small>' ?>';
                }
            });
        }
        const editProfileModal = document.getElementById('editProfileModal');
        if (editProfileModal) {
            editProfileModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const avatar = button.getAttribute('data-avatar');
                const bioEn = button.getAttribute('data-bio_en');
                const bioUr = button.getAttribute('data-bio_ur');
                const socialLinks = JSON.parse(button.getAttribute('data-social_links') || '{}');
                const avatarPreview = editProfileModal.querySelector('#avatarPreview');
                const currentAvatarInput = editProfileModal.querySelector('#currentAvatar');
                const removeAvatarCheckbox = editProfileModal.querySelector('#removeAvatarCheckbox');
                editProfileModal.querySelector('#bioEn').value = bioEn;
                editProfileModal.querySelector('#bioUr').value = bioUr;
                editProfileModal.querySelector('#socialFacebook').value = socialLinks.facebook || '';
                editProfileModal.querySelector('#socialTwitter').value = socialLinks.twitter || '';
                editProfileModal.querySelector('#socialLinkedin').value = socialLinks.linkedin || '';
                avatarPreview.src = avatar && avatar !== 'null' ? avatar : 'https://via.placeholder.com/120x120/ccc/white?text=Avatar';
                currentAvatarInput.value = avatar;
                removeAvatarCheckbox.checked = false;
            });
            editProfileModal.querySelector('#profileAvatar').addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        editProfileModal.querySelector('#avatarPreview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    editProfileModal.querySelector('#avatarPreview').src = editProfileModal.querySelector('#currentAvatar').value || 'https://via.placeholder.com/120x120/ccc/white?text=Avatar';
                }
            });
        }
        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', function() {
                if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
                    alert('<?= $lang === 'ur' ? 'پسند کرنے کے لیے لاگ ان کریں۔' : 'Please login to like articles.' ?>');
                    return;
                }
                const articleId = this.dataset.articleId;
                const currentLikes = parseInt(this.querySelector('.like-count').textContent);
                const isLiked = this.classList.contains('liked');
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `like_article=1&article_id=${articleId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'liked') {
                                this.classList.add('liked');
                                this.querySelector('.like-count').textContent = currentLikes + 1;
                            } else if (data.action === 'unliked') {
                                this.classList.remove('liked');
                                this.querySelector('.like-count').textContent = currentLikes - 1;
                            }
                            if (document.documentElement.lang === 'ur') {
                                this.querySelector('.like-count').textContent = convertToUrduNumbers(this.querySelector('.like-count').textContent);
                            }
                        } else {
                            alert(data.message || '<?= $lang === 'ur' ? 'پسند کرنے میں ناکامی۔' : 'Failed to like/unlike.' ?>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?= $lang === 'ur' ? 'پسند کرنے میں خرابی۔' : 'An error occurred during like operation.' ?>');
                    });
            });
        });
        document.querySelectorAll('.follow-button').forEach(button => {
            button.addEventListener('click', function() {
                if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
                    alert('<?= $lang === 'ur' ? 'فالو کرنے کے لیے لاگ ان کریں۔' : 'Please login to follow authors.' ?>');
                    return;
                }
                const followedId = this.dataset.followedId;
                const followTextSpan = this.querySelector('.follow-text');
                const isFollowing = this.classList.contains('following');
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `follow_user=1&followed_id=${followedId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'followed') {
                                this.classList.add('following');
                                followTextSpan.textContent = '<?= $lang === 'ur' ? 'فالو کر رہے ہیں' : 'Following' ?>';
                            } else if (data.action === 'unfollowed') {
                                this.classList.remove('following');
                                followTextSpan.textContent = '<?= $lang === 'ur' ? 'فالو کریں' : 'Follow' ?>';
                            }
                        } else {
                            alert(data.message || '<?= $lang === 'ur' ? 'فالو کرنے میں ناکامی۔' : 'Failed to follow/unfollow.' ?>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?= $lang === 'ur' ? 'فالو کرنے میں خرابی۔' : 'An error occurred during follow operation.' ?>');
                    });
            });
        });
        document.querySelectorAll('.reply-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.dataset.commentId;
                const username = this.dataset.username;
                document.getElementById('parentCommentId').value = commentId;
                document.getElementById('replyToIndicator').textContent = `<?= $lang === 'ur' ? 'جواب دے رہے ہیں: ' : 'Replying to: ' ?>${username}`;
                document.getElementById('commentTextarea').focus();
            });
        });
        const commentTextarea = document.getElementById('commentTextarea');
        if (commentTextarea) {
            commentTextarea.addEventListener('input', function() {
                if (this.value.trim() === '' && document.getElementById('parentCommentId').value !== '') {
                    document.getElementById('parentCommentId').value = '';
                    document.getElementById('replyToIndicator').textContent = '';
                }
            });
        }
        let optionCounterEn = 2;
        let optionCounterUr = 2;

        function addPollOption(langSuffix) {
            const container = document.getElementById(`pollOptions${langSuffix}`);
            const newOptionDiv = document.createElement('div');
            newOptionDiv.classList.add('input-group', 'mb-2');
            const newOptionInput = document.createElement('input');
            newOptionInput.type = 'text';
            newOptionInput.name = `options_${langSuffix.toLowerCase()}[]`;
            newOptionInput.classList.add('form-control');
            if (langSuffix === 'En') {
                optionCounterEn++;
                newOptionInput.placeholder = `Option ${optionCounterEn}`;
            } else {
                optionCounterUr++;
                newOptionInput.placeholder = `آپشن ${optionCounterUr}`;
                newOptionInput.dir = 'rtl';
            }
            newOptionInput.required = true;
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.classList.add('btn', 'btn-outline-danger', 'remove-option-btn');
            removeButton.innerHTML = '<i class="fas fa-times"></i>';
            removeButton.onclick = function() {
                newOptionDiv.remove();
            };
            newOptionDiv.appendChild(newOptionInput);
            newOptionDiv.appendChild(removeButton);
            container.appendChild(newOptionDiv);
        }
        document.querySelectorAll('.remove-option-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.input-group').remove();
            });
        });
        document.querySelectorAll('.vote-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
                    alert('<?= $lang === 'ur' ? 'ووٹ ڈالنے کے لیے لاگ ان کریں۔' : 'Please login to vote.' ?>');
                    return;
                }
                const pollId = this.dataset.pollId;
                const selectedOption = this.querySelector(`input[name="poll_option_${pollId}"]:checked`);
                if (!selectedOption) {
                    alert('<?= $lang === 'ur' ? 'براہ کرم ایک آپشن منتخب کریں۔' : 'Please select an option.' ?>');
                    return;
                }
                const optionIndex = selectedOption.value;
                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `vote_poll=1&poll_id=${pollId}&option_index=${optionIndex}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message || '<?= $lang === 'ur' ? 'ووٹ ڈالنے میں ناکامی۔' : 'Failed to cast vote.' ?>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('<?= $lang === 'ur' ? 'ووٹ ڈالنے میں خرابی۔' : 'An error occurred during voting.' ?>');
                    });
            });
        });
        const viewPollResultsModal = document.getElementById('viewPollResultsModal');
        if (viewPollResultsModal) {
            viewPollResultsModal.addEventListener('show.bs.modal', async event => {
                const button = event.relatedTarget;
                const pollId = button.getAttribute('data-poll-id');
                const questionEn = button.getAttribute('data-question-en');
                const questionUr = button.getAttribute('data-question-ur');
                const optionsEn = JSON.parse(button.getAttribute('data-options-en') || '[]');
                const optionsUr = JSON.parse(button.getAttribute('data-options-ur') || '[]');
                const modalQuestion = viewPollResultsModal.querySelector('#pollResultsQuestion');
                const resultsDisplay = viewPollResultsModal.querySelector('#pollResultsDisplay');
                modalQuestion.textContent = '<?= $lang === 'ur' ? 'سوال: ' : 'Question: ' ?>' + ('<?= $lang ?>' === 'ur' ? questionUr : questionEn);
                resultsDisplay.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                try {
                    const response = await fetch(`?action=get_poll_results&poll_id=${pollId}&lang=<?= $lang ?>`);
                    const data = await response.json();
                    if (data.success) {
                        resultsDisplay.innerHTML = '';
                        if (data.total_votes === 0) {
                            resultsDisplay.innerHTML = '<p class="text-muted text-center">' + ('<?= $lang === 'ur' ? 'ابھی تک کوئی ووٹ نہیں ہے۔' : 'No votes cast yet.' ?>') + '</p>';
                        } else {
                            data.results.forEach((result, index) => {
                                const optionText = '<?= $lang ?>' === 'ur' ? optionsUr[index] : optionsEn[index];
                                const percentage = data.total_votes > 0 ? Math.round((result.votes / data.total_votes) * 100) : 0;
                                resultsDisplay.innerHTML += `
                                    <div class="mb-2">
                                        <strong>${optionText}</strong>
                                        <div class="poll-option-bar">
                                            <div class="poll-option-bar-fill" style="width: ${percentage}%;"></div>
                                            <div class="poll-option-bar-text">${percentage}% (${result.votes} ${'<?= $lang === 'ur' ? 'ووٹ' : 'votes' ?>'})</div>
                                        </div>
                                    </div>
                                `;
                            });
                            resultsDisplay.innerHTML += `<p class="text-muted mt-3 text-center"><?= $lang === 'ur' ? 'کل ووٹ: ' : 'Total Votes: ' ?><span id="totalPollVotes">${('<?= $lang ?>' === 'ur' ? convertToUrduNumbers(data.total_votes) : data.total_votes)}</span></p>`;
                        }
                    } else {
                        resultsDisplay.innerHTML = '<div class="alert alert-danger">' + (data.message || '<?= $lang === 'ur' ? 'نتائج لوڈ کرنے میں ناکامی۔' : 'Failed to load results.' ?>') + '</div>';
                    }
                } catch (error) {
                    console.error('Error fetching poll results:', error);
                    resultsDisplay.innerHTML = '<div class="alert alert-danger">' + ('<?= $lang === 'ur' ? 'نتائج لوڈ کرنے میں خرابی۔' : 'Error fetching poll results.' ?>') + '</div>';
                }
            });
        }
        const editAdUnitModal = document.getElementById('editAdUnitModal');
        if (editAdUnitModal) {
            editAdUnitModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const adId = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const type = button.getAttribute('data-type');
                const code = button.getAttribute('data-code');
                const location = button.getAttribute('data-location');
                const status = button.getAttribute('data-status');
                const modalTitle = editAdUnitModal.querySelector('.modal-title');
                const form = editAdUnitModal.querySelector('form');
                form.reset();
                form.classList.remove('was-validated');
                modalTitle.textContent = '<?= $lang === 'ur' ? 'اشتہار یونٹ میں ترمیم کریں' : 'Edit Ad Unit' ?> (ID: ' + adId + ')';
                editAdUnitModal.querySelector('#editAdId').value = adId;
                editAdUnitModal.querySelector('#editAdName').value = name;
                editAdUnitModal.querySelector('#editAdType').value = type;
                editAdUnitModal.querySelector('#editAdCode').value = code;
                editAdUnitModal.querySelector('#editAdLocation').value = location;
                editAdUnitModal.querySelector('#editAdStatus').value = status;
            });
        }
        const revisionHistoryModal = document.getElementById('revisionHistoryModal');
        if (revisionHistoryModal) {
            revisionHistoryModal.addEventListener('show.bs.modal', async event => {
                const button = event.relatedTarget;
                const articleId = button.getAttribute('data-article-id');
                const revisionsContentDiv = revisionHistoryModal.querySelector('#revisionsContent');
                revisionsContentDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2"><?= $lang === 'ur' ? 'نظرثانی لوڈ ہو رہی ہیں...' : 'Loading revisions...' ?></div></div>';
                try {
                    const response = await fetch(`?action=get_revisions&article_id=${articleId}&lang=<?= $lang ?>`);
                    const data = await response.json();
                    if (data.success) {
                        revisionsContentDiv.innerHTML = '';
                        if (data.revisions.length === 0) {
                            revisionsContentDiv.innerHTML = '<div class="alert alert-info text-center">' + ('<?= $lang === 'ur' ? 'اس مضمون کے لیے کوئی نظرثانی نہیں ملی۔' : 'No revisions found for this article.' ?>') + '</div>';
                        } else {
                            data.revisions.forEach(revision => {
                                const title = '<?= $lang ?>' === 'ur' ? revision.title_ur : revision.title_en;
                                const content = '<?= $lang ?>' === 'ur' ? revision.content_ur : revision.content_en;
                                revisionsContentDiv.innerHTML += `
                                    <div class="card mb-3 shadow-sm">
                                        <div class="card-header bg-light">
                                            <strong><?= $lang === 'ur' ? 'تاریخ/وقت:' : 'Date/Time:' ?></strong> <span dir="ltr">${new Date(revision.updated_at).toLocaleString()}</span><br>
                                            <strong><?= $lang === 'ur' ? 'اپ ڈیٹ کرنے والا:' : 'Updated by:' ?></strong> ${revision.username || '<?= $lang === 'ur' ? 'نامعلوم' : 'Unknown' ?>'}
                                        </div>
                                        <div class="card-body">
                                            <h6><?= $lang === 'ur' ? 'عنوان:' : 'Title:' ?> ${title}</h6>
                                            <p><?= $lang === 'ur' ? 'مواد:' : 'Content:' ?> ${content.substring(0, 200)}...</p>
                                            <button class="btn btn-sm btn-outline-primary view-full-revision-btn" data-revision='${JSON.stringify(revision)}'>
                                                <?= $lang === 'ur' ? 'مکمل نظرثانی دیکھیں' : 'View Full Revision' ?>
                                            </button>
                                            <form method="POST" class="d-inline-block ms-2" onsubmit="return confirm('<?= $lang === 'ur' ? 'کیا آپ واقعی اس نظرثانی کو بحال کرنا چاہتے ہیں؟ موجودہ مواد بدل دیا جائے گا۔' : 'Are you sure you want to revert to this revision? Current content will be overwritten.' ?>');">
                                                <input type="hidden" name="action" value="revert_revision">
                                                <input type="hidden" name="article_id" value="${revision.article_id}">
                                                <input type="hidden" name="revision_id" value="${revision.id}">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <?= $lang === 'ur' ? 'اس نظرثانی پر واپس جائیں' : 'Revert to this Revision' ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                `;
                            });
                            revisionsContentDiv.querySelectorAll('.view-full-revision-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const revisionData = JSON.parse(this.dataset.revision);
                                    const fullTitle = '<?= $lang ?>' === 'ur' ? revisionData.title_ur : revisionData.title_en;
                                    const fullContent = '<?= $lang ?>' === 'ur' ? revisionData.content_ur : revisionData.content_en;
                                    alert(`<?= $lang === 'ur' ? 'عنوان:' : 'Title:' ?> ${fullTitle}\n\n<?= $lang === 'ur' ? 'مواد:' : 'Content:' ?>\n${fullContent}`);
                                });
                            });
                        }
                    } else {
                        revisionsContentDiv.innerHTML = '<div class="alert alert-danger text-center">' + (data.message || '<?= $lang === 'ur' ? 'نظرثانی لوڈ کرنے میں ناکامی۔' : 'Failed to load revisions.' ?>') + '</div>';
                    }
                } catch (error) {
                    console.error('Error fetching revisions:', error);
                    revisionsContentDiv.innerHTML = '<div class="alert alert-danger text-center">' + ('<?= $lang === 'ur' ? 'نظرثانی لوڈ کرنے میں خرابی۔' : 'Error fetching revisions.' ?>') + '</div>';
                }
            });
        }
        const brokenLinkBtn = document.getElementById('runBrokenLinkCheckerBtn');
        if (brokenLinkBtn) {
            brokenLinkBtn.addEventListener('click', async function() {
                const resultsDiv = document.getElementById('brokenLinkResults');
                resultsDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="visually-hidden">Checking links...</span></div><div class="mt-2"><?= $lang === 'ur' ? 'لنکس چیک ہو رہے ہیں، براہ کرم انتظار کریں...' : 'Checking links, please wait...' ?></div></div>';
                showLoading();
                try {
                    const response = await fetch('?action=check_broken_links&lang=<?= $lang ?>');
                    const data = await response.json();
                    hideLoading();
                    if (data.success) {
                        if (data.broken_links.length > 0) {
                            let html = '<h6><?= $lang === 'ur' ? 'بروکن لنکس پائے گئے:' : 'Broken Links Found:' ?></h6><ul>';
                            data.broken_links.forEach(link => {
                                html += `<li><strong><?= $lang === 'ur' ? 'مضمون:' : 'Article:' ?></strong> <a href="?view=article&id=${link.article_id}&lang=<?= $lang ?>">${link.article_title_en}</a><br>`;
                                html += `<strong><?= $lang === 'ur' ? 'بروکن URL:' : 'Broken URL:' ?></strong> ${link.url}</li>`;
                            });
                            html += '</ul>';
                            resultsDiv.innerHTML = '<div class="alert alert-warning">' + html + '</div>';
                        } else {
                            resultsDiv.innerHTML = '<div class="alert alert-success">' + ('<?= $lang === 'ur' ? 'کوئی بروکن لنک نہیں ملا۔' : 'No broken links found.' ?>') + '</div>';
                        }
                    } else {
                        resultsDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || '<?= $lang === 'ur' ? 'لنکس چیک کرنے میں ناکامی۔' : 'Failed to check links.' ?>') + '</div>';
                    }
                } catch (error) {
                    hideLoading();
                    console.error('Error checking broken links:', error);
                    resultsDiv.innerHTML = '<div class="alert alert-danger">' + ('<?= $lang === 'ur' ? 'لنکس چیک کرنے میں خرابی۔' : 'Error checking broken links.' ?>') + '</div>';
                }
            });
        }
    </script>
</body>

</html>