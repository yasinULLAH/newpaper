<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'newspaper_app';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if not exist
$tables = "
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'public') DEFAULT 'public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_en VARCHAR(100) NOT NULL,
    name_ur VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title_en VARCHAR(255) NOT NULL,
    title_ur VARCHAR(255) NOT NULL,
    content_en TEXT NOT NULL,
    content_ur TEXT NOT NULL,
    excerpt_en TEXT,
    excerpt_ur TEXT,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category_id INT,
    author_id INT,
    featured_image VARCHAR(255),
    gallery TEXT,
    tags VARCHAR(500),
    is_breaking BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS article_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

INSERT IGNORE INTO users (username, email, password, role) VALUES 
('admin', 'admin@newspaper.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin'),
('editor1', 'editor@newspaper.com', '" . password_hash('editor123', PASSWORD_DEFAULT) . "', 'editor');

INSERT IGNORE INTO categories (name_en, name_ur, slug) VALUES 
('National', 'قومی', 'national'),
('International', 'بین الاقوامی', 'international'),
('Sports', 'کھیل', 'sports'),
('Technology', 'ٹیکنالوجی', 'technology'),
('Business', 'کاروبار', 'business'),
('Entertainment', 'تفریح', 'entertainment'),
('Opinion', 'رائے', 'opinion');

INSERT IGNORE INTO articles (title_en, title_ur, content_en, content_ur, excerpt_en, excerpt_ur, slug, category_id, author_id, is_breaking, status, published_at) VALUES 
('Pakistan Wins Cricket Championship', 'پاکستان نے کرکٹ چیمپئن شپ جیتی', 'Pakistan cricket team has won the international championship in a thrilling final match. The team showed exceptional performance throughout the tournament.', 'پاکستان کرکٹ ٹیم نے ایک دلچسپ فائنل میچ میں بین الاقوامی چیمپئن شپ جیتی ہے۔ ٹیم نے پورے ٹورنامنٹ میں شاندار کارکردگی کا مظاہرہ کیا۔', 'Pakistan cricket team wins international championship', 'پاکستان کرکٹ ٹیم کی کامیابی', 'pakistan-wins-cricket-championship', 3, 1, TRUE, 'published', NOW()),
('New Technology Breakthrough in AI', 'مصنوعی ذہانت میں نئی پیش قدمی', 'Scientists have achieved a major breakthrough in artificial intelligence technology that could revolutionize various industries.', 'سائنسدانوں نے مصنوعی ذہانت کی ٹیکنالوجی میں ایک بڑی کامیابی حاصل کی ہے جو مختلف صنعتوں میں انقلاب لا سکتی ہے۔', 'Major AI breakthrough by scientists', 'سائنسدانوں کی AI میں کامیابی', 'new-technology-breakthrough-ai', 4, 2, FALSE, 'published', NOW()),
('Economic Growth Shows Positive Trends', 'اقتصادی ترقی میں مثبت رجحانات', 'The latest economic indicators show positive growth trends in various sectors of the economy, providing hope for future development.', 'تازہ اقتصادی اشاریے معیشت کے مختلف شعبوں میں مثبت ترقی کے رجحانات دکھاتے ہیں، جو مستقبل کی ترقی کے لیے امید فراہم کرتے ہیں۔', 'Economy shows positive growth indicators', 'معیشت میں مثبت نشاندہی', 'economic-growth-positive-trends', 5, 1, FALSE, 'published', NOW()),
('Cultural Festival Celebrates Heritage', 'ثقافتی میلہ ورثے کا جشن', 'The annual cultural festival showcased the rich heritage and traditions of Pakistan, attracting thousands of visitors from across the country.', 'سالانہ ثقافتی میلے نے پاکستان کی بھرپور ورثے اور روایات کا مظاہرہ کیا، جس نے ملک بھر سے ہزاروں زائرین کو اپنی طرف کھینچا۔', 'Annual cultural festival showcases heritage', 'سالانہ ثقافتی میلہ', 'cultural-festival-celebrates-heritage', 6, 2, FALSE, 'published', NOW());
";

try {
    $pdo->exec($tables);
} catch(PDOException $e) {
    // Tables already exist or error occurred
}

// CSRF Token generation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Language settings
$lang = $_SESSION['lang'] ?? 'en';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ur'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $lang = $_GET['lang'];
}

// Theme settings
$theme = $_SESSION['theme'] ?? 'light';
if (isset($_GET['theme']) && in_array($_GET['theme'], ['light', 'dark'])) {
    $_SESSION['theme'] = $_GET['theme'];
    $theme = $_GET['theme'];
}

// Handle requests
$action = $_GET['action'] ?? 'home';
$page = $_GET['page'] ?? 1;

// Authentication functions
function login($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function canEdit() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'editor']);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed');
    }
    
    switch ($action) {
        case 'login':
            if (login($_POST['username'], $_POST['password'], $pdo)) {
                header('Location: ?action=dashboard');
                exit;
            } else {
                $error = 'Invalid credentials';
            }
            break;
            
        case 'register':
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password]);
                $success = 'Registration successful. Please login.';
            } catch(PDOException $e) {
                $error = 'Username or email already exists';
            }
            break;
            
        case 'add_article':
            if (canEdit()) {
                $title_en = $_POST['title_en'];
                $title_ur = $_POST['title_ur'];
                $content_en = $_POST['content_en'];
                $content_ur = $_POST['content_ur'];
                $excerpt_en = $_POST['excerpt_en'];
                $excerpt_ur = $_POST['excerpt_ur'];
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $title_en));
                $category_id = $_POST['category_id'];
                $tags = $_POST['tags'];
                $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("INSERT INTO articles (title_en, title_ur, content_en, content_ur, excerpt_en, excerpt_ur, slug, category_id, author_id, tags, is_breaking, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
                $stmt->execute([$title_en, $title_ur, $content_en, $content_ur, $excerpt_en, $excerpt_ur, $slug, $category_id, $_SESSION['user_id'], $tags, $is_breaking, $status, $published_at]);
                
                $success = 'Article added successfully';
            }
            break;
            
        case 'add_comment':
            if (isset($_POST['article_id'])) {
                $article_id = $_POST['article_id'];
                $comment = $_POST['comment'];
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
                
                $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, name, email, comment) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$article_id, $user_id, $name, $email, $comment]);
                
                $success = 'Comment submitted for moderation';
            }
            break;
    }
}

// Handle logout
if ($action === 'logout') {
    session_destroy();
    header('Location: ?');
    exit;
}

// Track article views
if ($action === 'article' && isset($_GET['slug'])) {
    $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
    $stmt->execute([$_GET['slug']]);
    $article = $stmt->fetch();
    
    if ($article) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        // Check if view already recorded in last 24 hours
        $stmt = $pdo->prepare("SELECT id FROM article_views WHERE article_id = ? AND ip_address = ? AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([$article['id'], $ip]);
        
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO article_views (article_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([$article['id'], $ip, $user_agent]);
            
            $stmt = $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
            $stmt->execute([$article['id']]);
        }
    }
}

// Export/Import functionality
if ($action === 'export' && hasRole('admin')) {
    $stmt = $pdo->prepare("SELECT * FROM articles ORDER BY created_at DESC");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="newspaper_backup_' . date('Y-m-d') . '.json"');
    echo json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'import' && hasRole('admin') && isset($_FILES['backup_file'])) {
    $json = file_get_contents($_FILES['backup_file']['tmp_name']);
    $data = json_decode($json, true);
    
    if ($data) {
        foreach ($data as $article) {
            try {
                $stmt = $pdo->prepare("INSERT INTO articles (title_en, title_ur, content_en, content_ur, excerpt_en, excerpt_ur, slug, category_id, author_id, tags, is_breaking, status, published_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $article['title_en'], $article['title_ur'], $article['content_en'], 
                    $article['content_ur'], $article['excerpt_en'], $article['excerpt_ur'],
                    $article['slug'] . '-imported', $article['category_id'], 
                    $_SESSION['user_id'], $article['tags'], $article['is_breaking'],
                    $article['status'], $article['published_at'], $article['created_at']
                ]);
            } catch(PDOException $e) {
                // Skip duplicates
            }
        }
        $success = 'Data imported successfully';
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ur' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Yasin Ullah, Pakistan">
    <meta name="description" content="Professional Newspaper Web Application with Urdu and English Support">
    <meta name="keywords" content="newspaper, news, urdu news, english news, pakistan news, breaking news">
    <title><?php echo $lang === 'ur' ? 'پاکستان اخبار' : 'Pakistan Newspaper'; ?></title>
    
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #3f51b5;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --dark-color: #212121;
            --light-color: #f5f5f5;
            --border-color: #e0e0e0;
            --text-color: #333;
            --bg-color: #ffffff;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #ffffff;
            --border-color: #333;
            --light-color: #1e1e1e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: all 0.3s ease;
        }
        
        [dir="rtl"] {
            font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .nav {
            background-color: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 0.5rem;
        }
        
        .nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav a:hover, .nav a.active {
            background-color: rgba(255,255,255,0.2);
        }
        
        .breaking-news {
            background-color: var(--danger-color);
            color: white;
            padding: 0.5rem 0;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .breaking-scroll {
            display: inline-block;
            animation: scroll 30s linear infinite;
        }
        
        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        main {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }
        
        .article-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .article-card {
            background: var(--light-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .article-content {
            padding: 1.5rem;
        }
        
        .article-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .article-meta {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .article-excerpt {
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.2);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .sidebar {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .sidebar h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar a {
            color: var(--text-color);
            text-decoration: none;
        }
        
        .sidebar a:hover {
            color: var(--primary-color);
        }
        
        .search-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .search-form input {
            flex: 1;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 4px;
        }
        
        .page-link:hover, .page-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .comments-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        .comment {
            background: var(--light-color);
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .comment-meta {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .social-share {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .share-btn {
            padding: 0.5rem;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        
        .share-whatsapp { background-color: #25d366; }
        .share-twitter { background-color: #1da1f2; }
        .share-facebook { background-color: #4267b2; }
        .share-copy { background-color: #666; }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: var(--shadow);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border: none;
            background: none;
            color: var(--text-color);
            border-bottom: 3px solid transparent;
        }
        
        .tab.active, .tab:hover {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav ul {
                justify-content: center;
            }
            
            .article-grid {
                grid-template-columns: 1fr;
            }
            
            .controls {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: var(--bg-color);
            margin: 5% auto;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: block;
            }
            
            body {
                background: white;
                color: black;
            }
        }
    </style>
</head>
<body data-theme="<?php echo $theme; ?>">
    <header class="no-print">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <?php echo $lang === 'ur' ? 'پاکستان اخبار' : 'Pakistan News'; ?>
                </div>
                <div class="controls">
                    <a href="?lang=<?php echo $lang === 'en' ? 'ur' : 'en'; ?>" class="btn btn-secondary">
                        <?php echo $lang === 'en' ? 'اردو' : 'English'; ?>
                    </a>
                    <a href="?theme=<?php echo $theme === 'light' ? 'dark' : 'light'; ?>" class="btn btn-secondary">
                        <?php echo $theme === 'light' ? '🌙' : '☀️'; ?>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="?action=dashboard" class="btn btn-primary">
                            <?php echo $lang === 'ur' ? 'ڈیش بورڈ' : 'Dashboard'; ?>
                        </a>
                        <a href="?action=logout" class="btn btn-danger">
                            <?php echo $lang === 'ur' ? 'لاگ آؤٹ' : 'Logout'; ?>
                        </a>
                    <?php else: ?>
                        <button onclick="showModal('loginModal')" class="btn btn-primary">
                            <?php echo $lang === 'ur' ? 'لاگ ان' : 'Login'; ?>
                        </button>
                        <button onclick="showModal('registerModal')" class="btn btn-success">
                            <?php echo $lang === 'ur' ? 'رجسٹر' : 'Register'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <nav class="nav">
                <ul>
                    <li><a href="?" class="<?php echo $action === 'home' ? 'active' : ''; ?>">
                        <?php echo $lang === 'ur' ? 'ہوم' : 'Home'; ?>
                    </a></li>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
                    while ($category = $stmt->fetch()):
                    ?>
                    <li><a href="?action=category&id=<?php echo $category['id']; ?>">
                        <?php echo $lang === 'ur' ? $category['name_ur'] : $category['name_en']; ?>
                    </a></li>
                    <?php endwhile; ?>
                    <li><a href="?action=search">
                        <?php echo $lang === 'ur' ? 'تلاش' : 'Search'; ?>
                    </a></li>
                </ul>
            </nav>
        </div>
        
        <?php
        $stmt = $pdo->query("SELECT * FROM articles WHERE is_breaking = 1 AND status = 'published' ORDER BY published_at DESC LIMIT 5");
        $breaking_news = $stmt->fetchAll();
        if ($breaking_news):
        ?>
        <div class="breaking-news">
            <div class="container">
                <strong><?php echo $lang === 'ur' ? 'بریکنگ نیوز: ' : 'BREAKING NEWS: '; ?></strong>
                <span class="breaking-scroll">
                    <?php foreach ($breaking_news as $news): ?>
                        <?php echo $lang === 'ur' ? $news['title_ur'] : $news['title_en']; ?> • 
                    <?php endforeach; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </header>

    <main>
        <div class="container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php
            switch ($action) {
                case 'home':
                    include_once 'home_section.php';
                    break;
                case 'article':
                    include_once 'article_section.php';
                    break;
                case 'category':
                    include_once 'category_section.php';
                    break;
                case 'search':
                    include_once 'search_section.php';
                    break;
                case 'dashboard':
                    if (isLoggedIn()) {
                        include_once 'dashboard_section.php';
                    } else {
                        echo '<p>Please login to access dashboard</p>';
                    }
                    break;
                default:
                    include_once 'home_section.php';
            }
            ?>
        </div>
    </main>

    <footer class="no-print">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $lang === 'ur' ? 'پاکستان اخبار - تمام حقوق محفوظ' : 'Pakistan Newspaper - All Rights Reserved'; ?></p>
            <p><?php echo $lang === 'ur' ? 'تیار کردہ: یاسین اللہ، پاکستان' : 'Developed by: Yasin Ullah, Pakistan'; ?></p>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('loginModal')">&times;</span>
            <h2><?php echo $lang === 'ur' ? 'لاگ ان' : 'Login'; ?></h2>
            <form method="POST" action="?action=login">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label><?php echo $lang === 'ur' ? 'صارف نام یا ای میل' : 'Username or Email'; ?></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang === 'ur' ? 'پاسورڈ' : 'Password'; ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <?php echo $lang === 'ur' ? 'لاگ ان' : 'Login'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('registerModal')">&times;</span>
            <h2><?php echo $lang === 'ur' ? 'رجسٹر' : 'Register'; ?></h2>
            <form method="POST" action="?action=register">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="form-group">
                    <label><?php echo $lang === 'ur' ? 'صارف نام' : 'Username'; ?></label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang === 'ur' ? 'ای میل' : 'Email'; ?></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?php echo $lang === 'ur' ? 'پاسورڈ' : 'Password'; ?></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">
                    <?php echo $lang === 'ur' ? 'رجسٹر' : 'Register'; ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Theme and Language Functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Tab functionality
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            document.querySelector(`[onclick="showTab('${tabName}')"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        // Social sharing functions
        function shareWhatsApp(url, text) {
            window.open(`https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`, '_blank');
        }

        function shareTwitter(url, text) {
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank');
        }

        function shareFacebook(url) {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        }

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('<?php echo $lang === "ur" ? "لنک کاپی ہو گیا" : "Link copied!"; ?>');
            });
        }

        // Print article
        function printArticle() {
            window.print();
        }

        // Auto-refresh breaking news
        setInterval(() => {
            const breakingNews = document.querySelector('.breaking-scroll');
            if (breakingNews) {
                breakingNews.style.animationDuration = '30s';
            }
        }, 30000);

        // Search suggestions
        function showSearchSuggestions(query) {
            if (query.length < 2) return;
            
            fetch(`?action=search_suggestions&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    // Implementation for search suggestions
                })
                .catch(error => console.error('Error:', error));
        }

        // Lazy loading for images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));

        // Real-time character count for forms
        function updateCharCount(input, counterId) {
            const count = input.value.length;
            document.getElementById(counterId).textContent = count;
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Show first tab if tabs exist
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                firstTab.click();
            }
            
            // Set up form validations
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = 'var(--danger-color)';
                            isValid = false;
                        } else {
                            field.style.borderColor = 'var(--border-color)';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('<?php echo $lang === "ur" ? "تمام فیلڈز بھریں" : "Please fill all required fields"; ?>');
                    }
                });
            });
        });
    </script>

    <?php
    // Include sections based on action
    if ($action === 'home' || !$action) {
        // Home page content
        echo '<div class="article-grid">';
        
        $stmt = $pdo->prepare("SELECT a.*, c.name_en, c.name_ur, u.username FROM articles a 
                              LEFT JOIN categories c ON a.category_id = c.id 
                              LEFT JOIN users u ON a.author_id = u.id 
                              WHERE a.status = 'published' 
                              ORDER BY a.published_at DESC LIMIT 12");
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
            $excerpt = $lang === 'ur' ? $article['excerpt_ur'] : $article['excerpt_en'];
            $category = $lang === 'ur' ? $article['name_ur'] : $article['name_en'];
            
            echo '<div class="article-card">';
            if ($article['featured_image']) {
                echo '<img src="' . $article['featured_image'] . '" alt="' . htmlspecialchars($title) . '" class="article-image">';
            }
            echo '<div class="article-content">';
            echo '<h3 class="article-title"><a href="?action=article&slug=' . $article['slug'] . '">' . htmlspecialchars($title) . '</a></h3>';
            echo '<div class="article-meta">';
            echo '<span>' . ($category ?: '') . '</span> • ';
            echo '<span>' . date('M j, Y', strtotime($article['published_at'])) . '</span> • ';
            echo '<span>' . $article['views'] . ' ' . ($lang === 'ur' ? 'ویوز' : 'views') . '</span>';
            echo '</div>';
            echo '<p class="article-excerpt">' . htmlspecialchars(substr($excerpt, 0, 150)) . '...</p>';
            echo '<a href="?action=article&slug=' . $article['slug'] . '" class="btn btn-primary">' . ($lang === 'ur' ? 'مزید پڑھیں' : 'Read More') . '</a>';
            echo '</div></div>';
        }
        
        echo '</div>';
        
        // Sidebar
        echo '<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">';
        echo '<div></div>'; // Main content area
        echo '<div class="sidebar">';
        echo '<h3>' . ($lang === 'ur' ? 'مقبول خبریں' : 'Popular News') . '</h3>';
        
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE status = 'published' ORDER BY views DESC LIMIT 5");
        $stmt->execute();
        $popular = $stmt->fetchAll();
        
        echo '<ul>';
        foreach ($popular as $pop) {
            $title = $lang === 'ur' ? $pop['title_ur'] : $pop['title_en'];
            echo '<li><a href="?action=article&slug=' . $pop['slug'] . '">' . htmlspecialchars(substr($title, 0, 60)) . '...</a></li>';
        }
        echo '</ul>';
        
        echo '<h3>' . ($lang === 'ur' ? 'کیٹگریز' : 'Categories') . '</h3>';
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
        echo '<ul>';
        while ($cat = $stmt->fetch()) {
            $catName = $lang === 'ur' ? $cat['name_ur'] : $cat['name_en'];
            echo '<li><a href="?action=category&id=' . $cat['id'] . '">' . $catName . '</a></li>';
        }
        echo '</ul>';
        echo '</div></div>';
        
    } elseif ($action === 'article') {
        // Article page
        $slug = $_GET['slug'] ?? '';
        $stmt = $pdo->prepare("SELECT a.*, c.name_en, c.name_ur, u.username FROM articles a 
                              LEFT JOIN categories c ON a.category_id = c.id 
                              LEFT JOIN users u ON a.author_id = u.id 
                              WHERE a.slug = ? AND a.status = 'published'");
        $stmt->execute([$slug]);
        $article = $stmt->fetch();
        
        if ($article) {
            $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
            $content = $lang === 'ur' ? $article['content_ur'] : $article['content_en'];
            $category = $lang === 'ur' ? $article['name_ur'] : $article['name_en'];
            
            echo '<article>';
            echo '<div class="print-only"><h1>Pakistan Newspaper</h1><hr></div>';
            echo '<h1>' . htmlspecialchars($title) . '</h1>';
            echo '<div class="article-meta no-print">';
            echo '<span>' . ($category ?: '') . '</span> • ';
            echo '<span>' . $article['username'] . '</span> • ';
            echo '<span>' . date('M j, Y H:i', strtotime($article['published_at'])) . '</span> • ';
            echo '<span>' . $article['views'] . ' ' . ($lang === 'ur' ? 'ویوز' : 'views') . '</span>';
            echo '</div>';
            
            if ($article['featured_image']) {
                echo '<img src="' . $article['featured_image'] . '" alt="' . htmlspecialchars($title) . '" style="width: 100%; height: 300px; object-fit: cover; margin: 1rem 0;">';
            }
            
            echo '<div class="article-content">' . nl2br(htmlspecialchars($content)) . '</div>';
            
            if ($article['tags']) {
                echo '<div class="article-tags no-print" style="margin: 2rem 0;">';
                echo '<strong>' . ($lang === 'ur' ? 'ٹیگز: ' : 'Tags: ') . '</strong>';
                $tags = explode(',', $article['tags']);
                foreach ($tags as $tag) {
                    echo '<span style="background: var(--primary-color); color: white; padding: 0.2rem 0.5rem; margin-right: 0.5rem; border-radius: 3px; font-size: 0.8rem;">' . trim($tag) . '</span>';
                }
                echo '</div>';
            }
            
            $currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            echo '<div class="social-share no-print">';
            echo '<a href="#" onclick="shareWhatsApp(\'' . $currentUrl . '\', \'' . htmlspecialchars($title) . '\')" class="share-btn share-whatsapp">📱</a>';
            echo '<a href="#" onclick="shareTwitter(\'' . $currentUrl . '\', \'' . htmlspecialchars($title) . '\')" class="share-btn share-twitter">🐦</a>';
            echo '<a href="#" onclick="shareFacebook(\'' . $currentUrl . '\')" class="share-btn share-facebook">📘</a>';
            echo '<a href="#" onclick="copyLink(\'' . $currentUrl . '\')" class="share-btn share-copy">📋</a>';
            echo '<button onclick="printArticle()" class="btn btn-secondary" style="margin-left: 1rem;">🖨️ ' . ($lang === 'ur' ? 'پرنٹ' : 'Print') . '</button>';
            echo '</div>';
            
            echo '</article>';
            
            // Comments section
            echo '<div class="comments-section no-print">';
            echo '<h3>' . ($lang === 'ur' ? 'تبصرے' : 'Comments') . '</h3>';
            
            // Display comments
            $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c 
                                  LEFT JOIN users u ON c.user_id = u.id 
                                  WHERE c.article_id = ? AND c.status = 'approved' 
                                  ORDER BY c.created_at DESC");
            $stmt->execute([$article['id']]);
            $comments = $stmt->fetchAll();
            
            foreach ($comments as $comment) {
                echo '<div class="comment">';
                echo '<div class="comment-meta">';
                echo '<strong>' . ($comment['username'] ?: $comment['name']) . '</strong> • ';
                echo '<span>' . date('M j, Y H:i', strtotime($comment['created_at'])) . '</span>';
                echo '</div>';
                echo '<p>' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                echo '</div>';
            }
            
            // Comment form
            echo '<h4>' . ($lang === 'ur' ? 'تبصرہ شامل کریں' : 'Add Comment') . '</h4>';
            echo '<form method="POST" action="?action=add_comment">';
            echo '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
            echo '<input type="hidden" name="article_id" value="' . $article['id'] . '">';
            
            if (!isLoggedIn()) {
                echo '<div class="form-group">';
                echo '<label>' . ($lang === 'ur' ? 'نام' : 'Name') . '</label>';
                echo '<input type="text" name="name" class="form-control" required>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label>' . ($lang === 'ur' ? 'ای میل' : 'Email') . '</label>';
                echo '<input type="email" name="email" class="form-control" required>';
                echo '</div>';
            }
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'تبصرہ' : 'Comment') . '</label>';
            echo '<textarea name="comment" class="form-control" rows="4" required></textarea>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary">' . ($lang === 'ur' ? 'تبصرہ بھیجیں' : 'Submit Comment') . '</button>';
            echo '</form>';
            echo '</div>';
            
        } else {
            echo '<p>' . ($lang === 'ur' ? 'آرٹیکل نہیں ملا' : 'Article not found') . '</p>';
        }
        
    } elseif ($action === 'category') {
        // Category page
        $category_id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch();
        
        if ($category) {
            $categoryName = $lang === 'ur' ? $category['name_ur'] : $category['name_en'];
            echo '<h2>' . htmlspecialchars($categoryName) . '</h2>';
            
            $stmt = $pdo->prepare("SELECT a.*, c.name_en, c.name_ur, u.username FROM articles a 
                                  LEFT JOIN categories c ON a.category_id = c.id 
                                  LEFT JOIN users u ON a.author_id = u.id 
                                  WHERE a.category_id = ? AND a.status = 'published' 
                                  ORDER BY a.published_at DESC");
            $stmt->execute([$category_id]);
            $articles = $stmt->fetchAll();
            
            echo '<div class="article-grid">';
            foreach ($articles as $article) {
                $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                $excerpt = $lang === 'ur' ? $article['excerpt_ur'] : $article['excerpt_en'];
                
                echo '<div class="article-card">';
                if ($article['featured_image']) {
                    echo '<img src="' . $article['featured_image'] . '" alt="' . htmlspecialchars($title) . '" class="article-image">';
                }
                echo '<div class="article-content">';
                echo '<h3 class="article-title"><a href="?action=article&slug=' . $article['slug'] . '">' . htmlspecialchars($title) . '</a></h3>';
                echo '<div class="article-meta">';
                echo '<span>' . $article['username'] . '</span> • ';
                echo '<span>' . date('M j, Y', strtotime($article['published_at'])) . '</span>';
                echo '</div>';
                echo '<p class="article-excerpt">' . htmlspecialchars(substr($excerpt, 0, 150)) . '...</p>';
                echo '<a href="?action=article&slug=' . $article['slug'] . '" class="btn btn-primary">' . ($lang === 'ur' ? 'مزید پڑھیں' : 'Read More') . '</a>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        
    } elseif ($action === 'search') {
        // Search page
        $query = $_GET['q'] ?? '';
        
        echo '<h2>' . ($lang === 'ur' ? 'تلاش' : 'Search') . '</h2>';
        echo '<form method="GET" action="?" class="search-form">';
        echo '<input type="hidden" name="action" value="search">';
        echo '<input type="text" name="q" class="form-control" placeholder="' . ($lang === 'ur' ? 'تلاش کریں...' : 'Search...') . '" value="' . htmlspecialchars($query) . '">';
        echo '<button type="submit" class="btn btn-primary">' . ($lang === 'ur' ? 'تلاش' : 'Search') . '</button>';
        echo '</form>';
        
        if ($query) {
            $stmt = $pdo->prepare("SELECT a.*, c.name_en, c.name_ur, u.username FROM articles a 
                                  LEFT JOIN categories c ON a.category_id = c.id 
                                  LEFT JOIN users u ON a.author_id = u.id 
                                  WHERE (a.title_en LIKE ? OR a.title_ur LIKE ? OR a.content_en LIKE ? OR a.content_ur LIKE ? OR a.tags LIKE ?) 
                                  AND a.status = 'published' 
                                  ORDER BY a.published_at DESC");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();
            
            echo '<p>' . count($results) . ' ' . ($lang === 'ur' ? 'نتائج ملے' : 'results found') . '</p>';
            
            echo '<div class="article-grid">';
            foreach ($results as $article) {
                $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
                $excerpt = $lang === 'ur' ? $article['excerpt_ur'] : $article['excerpt_en'];
                $category = $lang === 'ur' ? $article['name_ur'] : $article['name_en'];
                
                echo '<div class="article-card">';
                echo '<div class="article-content">';
                echo '<h3 class="article-title"><a href="?action=article&slug=' . $article['slug'] . '">' . htmlspecialchars($title) . '</a></h3>';
                echo '<div class="article-meta">';
                echo '<span>' . ($category ?: '') . '</span> • ';
                echo '<span>' . $article['username'] . '</span> • ';
                echo '<span>' . date('M j, Y', strtotime($article['published_at'])) . '</span>';
                echo '</div>';
                echo '<p class="article-excerpt">' . htmlspecialchars(substr($excerpt, 0, 200)) . '...</p>';
                echo '<a href="?action=article&slug=' . $article['slug'] . '" class="btn btn-primary">' . ($lang === 'ur' ? 'مزید پڑھیں' : 'Read More') . '</a>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        
    } elseif ($action === 'dashboard' && isLoggedIn()) {
        // Dashboard
        echo '<h2>' . ($lang === 'ur' ? 'ڈیش بورڈ' : 'Dashboard') . '</h2>';
        
        // Statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM articles WHERE author_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userArticles = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT SUM(views) as total_views FROM articles WHERE author_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $totalViews = $stmt->fetchColumn() ?: 0;
        
        echo '<div class="dashboard-stats">';
        echo '<div class="stat-card">';
        echo '<div class="stat-number">' . $userArticles . '</div>';
        echo '<div>' . ($lang === 'ur' ? 'آرٹیکلز' : 'Articles') . '</div>';
        echo '</div>';
        echo '<div class="stat-card">';
        echo '<div class="stat-number">' . number_format($totalViews) . '</div>';
        echo '<div>' . ($lang === 'ur' ? 'کل ویوز' : 'Total Views') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Tabs
        echo '<div class="tabs">';
        echo '<button class="tab active" onclick="showTab(\'articles\')">' . ($lang === 'ur' ? 'آرٹیکلز' : 'Articles') . '</button>';
        if (canEdit()) {
            echo '<button class="tab" onclick="showTab(\'add-article\')">' . ($lang === 'ur' ? 'نیا آرٹیکل' : 'Add Article') . '</button>';
        }
        if (hasRole('admin')) {
            echo '<button class="tab" onclick="showTab(\'comments\')">' . ($lang === 'ur' ? 'تبصرے' : 'Comments') . '</button>';
            echo '<button class="tab" onclick="showTab(\'backup\')">' . ($lang === 'ur' ? 'بیک اپ' : 'Backup') . '</button>';
        }
        echo '</div>';
        
        // Articles tab
        echo '<div id="articles" class="tab-content active">';
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE author_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $userArticlesData = $stmt->fetchAll();
        
        echo '<table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">';
        echo '<thead>';
        echo '<tr style="background: var(--light-color);">';
        echo '<th style="padding: 1rem; border: 1px solid var(--border-color);">' . ($lang === 'ur' ? 'عنوان' : 'Title') . '</th>';
        echo '<th style="padding: 1rem; border: 1px solid var(--border-color);">' . ($lang === 'ur' ? 'حالت' : 'Status') . '</th>';
        echo '<th style="padding: 1rem; border: 1px solid var(--border-color);">' . ($lang === 'ur' ? 'ویوز' : 'Views') . '</th>';
        echo '<th style="padding: 1rem; border: 1px solid var(--border-color);">' . ($lang === 'ur' ? 'تاریخ' : 'Date') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($userArticlesData as $article) {
            $title = $lang === 'ur' ? $article['title_ur'] : $article['title_en'];
            echo '<tr>';
            echo '<td style="padding: 1rem; border: 1px solid var(--border-color);"><a href="?action=article&slug=' . $article['slug'] . '">' . htmlspecialchars(substr($title, 0, 50)) . '...</a></td>';
            echo '<td style="padding: 1rem; border: 1px solid var(--border-color);">' . ucfirst($article['status']) . '</td>';
            echo '<td style="padding: 1rem; border: 1px solid var(--border-color);">' . $article['views'] . '</td>';
            echo '<td style="padding: 1rem; border: 1px solid var(--border-color);">' . date('M j, Y', strtotime($article['created_at'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
        
        // Add article tab
        if (canEdit()) {
            echo '<div id="add-article" class="tab-content">';
            echo '<h3>' . ($lang === 'ur' ? 'نیا آرٹیکل شامل کریں' : 'Add New Article') . '</h3>';
            echo '<form method="POST" action="?action=add_article">';
            echo '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'عنوان (انگریزی)' : 'Title (English)') . '</label>';
            echo '<input type="text" name="title_en" class="form-control" required>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'عنوان (اردو)' : 'Title (Urdu)') . '</label>';
            echo '<input type="text" name="title_ur" class="form-control" required>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'کیٹگری' : 'Category') . '</label>';
            echo '<select name="category_id" class="form-control" required>';
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_en");
            while ($cat = $stmt->fetch()) {
                $catName = $lang === 'ur' ? $cat['name_ur'] : $cat['name_en'];
                echo '<option value="' . $cat['id'] . '">' . $catName . '</option>';
            }
            echo '</select>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'خلاصہ (انگریزی)' : 'Excerpt (English)') . '</label>';
            echo '<textarea name="excerpt_en" class="form-control" rows="3"></textarea>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'خلاصہ (اردو)' : 'Excerpt (Urdu)') . '</label>';
            echo '<textarea name="excerpt_ur" class="form-control" rows="3"></textarea>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'مضمون (انگریزی)' : 'Content (English)') . '</label>';
            echo '<textarea name="content_en" class="form-control" rows="10" required></textarea>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'مضمون (اردو)' : 'Content (Urdu)') . '</label>';
            echo '<textarea name="content_ur" class="form-control" rows="10" required></textarea>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'ٹیگز (کاما سے الگ کریں)' : 'Tags (comma separated)') . '</label>';
            echo '<input type="text" name="tags" class="form-control">';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'حالت' : 'Status') . '</label>';
            echo '<select name="status" class="form-control">';
            echo '<option value="draft">' . ($lang === 'ur' ? 'ڈرافٹ' : 'Draft') . '</option>';
            echo '<option value="published">' . ($lang === 'ur' ? 'شائع' : 'Published') . '</option>';
            echo '</select>';
            echo '</div>';
            
            echo '<div class="form-group">';
            echo '<label>';
            echo '<input type="checkbox" name="is_breaking"> ';
            echo ($lang === 'ur' ? 'بریکنگ نیوز' : 'Breaking News');
            echo '</label>';
            echo '</div>';
            
            echo '<button type="submit" class="btn btn-success">' . ($lang === 'ur' ? 'آرٹیکل شامل کریں' : 'Add Article') . '</button>';
            echo '</form>';
            echo '</div>';
        }
        
        // Comments management (admin only)
        if (hasRole('admin')) {
            echo '<div id="comments" class="tab-content">';
            echo '<h3>' . ($lang === 'ur' ? 'تبصروں کا انتظام' : 'Manage Comments') . '</h3>';
            
            $stmt = $pdo->query("SELECT c.*, a.title_en, a.title_ur FROM comments c 
                                LEFT JOIN articles a ON c.article_id = a.id 
                                WHERE c.status = 'pending' 
                                ORDER BY c.created_at DESC");
            $pending_comments = $stmt->fetchAll();
            
            foreach ($pending_comments as $comment) {
                $articleTitle = $lang === 'ur' ? $comment['title_ur'] : $comment['title_en'];
                echo '<div class="comment" style="border-left: 4px solid var(--warning-color);">';
                echo '<div class="comment-meta">';
                echo '<strong>' . ($comment['name'] ?: 'Anonymous') . '</strong> • ';
                echo '<span>' . date('M j, Y H:i', strtotime($comment['created_at'])) . '</span>';
                echo '<br><small>' . ($lang === 'ur' ? 'آرٹیکل: ' : 'Article: ') . htmlspecialchars(substr($articleTitle, 0, 50)) . '...</small>';
                echo '</div>';
                echo '<p>' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                echo '<div style="margin-top: 0.5rem;">';
                echo '<a href="?action=approve_comment&id=' . $comment['id'] . '" class="btn btn-success btn-sm">' . ($lang === 'ur' ? 'منظور' : 'Approve') . '</a> ';
                echo '<a href="?action=reject_comment&id=' . $comment['id'] . '" class="btn btn-danger btn-sm">' . ($lang === 'ur' ? 'مسترد' : 'Reject') . '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            if (empty($pending_comments)) {
                echo '<p>' . ($lang === 'ur' ? 'کوئی انتظار میں تبصرہ نہیں' : 'No pending comments') . '</p>';
            }
            echo '</div>';
        }
        
        // Backup tab (admin only)
        if (hasRole('admin')) {
            echo '<div id="backup" class="tab-content">';
            echo '<h3>' . ($lang === 'ur' ? 'بیک اپ اور ریسٹور' : 'Backup & Restore') . '</h3>';
            
            echo '<div style="margin-bottom: 2rem;">';
            echo '<h4>' . ($lang === 'ur' ? 'ڈیٹا ایکسپورٹ' : 'Export Data') . '</h4>';
            echo '<a href="?action=export" class="btn btn-primary">' . ($lang === 'ur' ? 'بیک اپ ڈاؤن لوڈ کریں' : 'Download Backup') . '</a>';
            echo '</div>';
            
            echo '<div>';
            echo '<h4>' . ($lang === 'ur' ? 'ڈیٹا امپورٹ' : 'Import Data') . '</h4>';
            echo '<form method="POST" action="?action=import" enctype="multipart/form-data">';
            echo '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
            echo '<div class="form-group">';
            echo '<label>' . ($lang === 'ur' ? 'بیک اپ فائل منتخب کریں' : 'Select Backup File') . '</label>';
            echo '<input type="file" name="backup_file" class="form-control" accept=".json" required>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-success">' . ($lang === 'ur' ? 'ڈیٹا امپورٹ کریں' : 'Import Data') . '</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }
        
    } else {
        // Default to home if action not recognized
        header('Location: ?');
        exit;
    }
    ?>
</body>
</html>
