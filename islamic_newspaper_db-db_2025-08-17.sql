-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 17, 2025 at 10:53 AM
-- Server version: 8.2.0
-- PHP Version: 8.3.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `islamic_newspaper_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad_units`
--

CREATE TABLE `ad_units` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('banner','inline','popup') DEFAULT 'banner',
  `code` text NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `title_en` text NOT NULL,
  `title_ur` text NOT NULL,
  `content_en` longtext NOT NULL,
  `content_ur` longtext NOT NULL,
  `category_id` int DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_breaking` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `likes` int DEFAULT '0',
  `status` enum('draft','published') DEFAULT 'published',
  `is_sponsored` tinyint(1) DEFAULT '0',
  `slug` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `seo_meta_title_en` varchar(255) DEFAULT NULL,
  `seo_meta_title_ur` varchar(255) DEFAULT NULL,
  `seo_meta_description_en` text,
  `seo_meta_description_ur` text,
  `seo_keywords_en` text,
  `seo_keywords_ur` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title_en`, `title_ur`, `content_en`, `content_ur`, `category_id`, `author_id`, `image`, `is_breaking`, `views`, `likes`, `status`, `is_sponsored`, `slug`, `published_at`, `created_at`, `updated_at`, `seo_meta_title_en`, `seo_meta_title_ur`, `seo_meta_description_en`, `seo_meta_description_ur`, `seo_keywords_en`, `seo_keywords_ur`) VALUES
(1, 'The Importance of Seeking Knowledge in Islam', 'اسلام میں علم حاصل کرنے کی اہمیت', 'In Islam, seeking knowledge is not merely encouraged but is considered a sacred duty for every Muslim. The Quran and Hadith repeatedly emphasize the virtues of knowledge, referring to it as a means to understand Allah\'s creation, His commands, and to live a purposeful life. From the earliest days of Islam, great emphasis was placed on education and intellectual pursuits, leading to significant advancements in various fields of science, medicine, and philosophy. This article explores the foundational texts and historical context that highlight this profound emphasis on learning and its impact on the development of Islamic civilization. It discusses how knowledge serves as a light, dispelling ignorance and guiding believers towards truth and righteousness. The pursuit of knowledge encompasses both religious and worldly sciences, as both are seen as pathways to understanding and appreciating the divine wisdom embedded in the universe. Learning is a continuous journey from cradle to grave, fostering intellectual growth and moral development within the individual and the community. By engaging in scholarly endeavors, Muslims can contribute to the betterment of humanity, fulfilling their role as stewards of the Earth and spreading the message of peace and justice. This emphasis on knowledge has historically propelled Muslim societies to be pioneers in numerous scientific and philosophical fields, contributing immensely to global civilization. Libraries and learning centers flourished, attracting scholars from all corners of the world, eager to contribute to and benefit from the rich intellectual environment. This intellectual legacy continues to inspire generations, underscoring that the pursuit of knowledge remains a vital component of the Islamic way of life. It reminds us that wisdom is the lost property of the believer, and wherever he finds it, he is most deserving of it. Thus, education is not just a personal endeavor but a communal responsibility, ensuring that the light of knowledge continues to illuminate paths for future generations and contribute to global prosperity.', 'اسلام میں علم کا حصول صرف حوصلہ افزائی نہیں بلکہ ہر مسلمان کے لیے ایک مقدس فریضہ سمجھا جاتا ہے۔ قرآن و حدیث میں بار بار علم کی فضیلت پر زور دیا گیا ہے، اسے اللہ کی تخلیق، اس کے احکامات کو سمجھنے اور ایک بامقصد زندگی گزارنے کا ذریعہ قرار دیا گیا ہے۔ اسلام کے ابتدائی ایام سے ہی تعلیم اور فکری سرگرمیوں پر بہت زور دیا گیا، جس سے سائنس، طب اور فلسفہ کے مختلف شعبوں میں اہم ترقی ہوئی، یہ مضمون ان بنیادی نصوص اور تاریخی سیاق و سباق کو تلاش کرتا ہے جو علم پر اس گہرے زور اور اسلامی تہذیب کی ترقی پر اس کے اثرات کو نمایاں کرتے ہیں۔ یہ بحث کرتا ہے کہ علم ایک روشنی کے طور پر کس طرح کام کرتا ہے، جہالت کو دور کرتا ہے اور مومنوں کو سچائی اور راستبازی کی طرف رہنمائی کرتا ہے۔ علم کا حصول مذہبی اور دنیاوی دونوں علوم پر محیط ہے، کیونکہ دونوں کو کائنات میں موجود الہی حکمت کو سمجھنے اور سراہنے کے راستے کے طور پر دیکھا جاتا ہے۔ علم کا سفر گہوارے سے لحد تک جاری رہتا ہے، جو فرد اور معاشرے میں فکری نشوونما اور اخلاقی ترقی کو فروغ دیتا ہے۔ علمی کوششوں میں مشغول ہو کر، مسلمان انسانیت کی بہتری میں حصہ ڈال سکتے ہیں، زمین کے رکھوالے کے طور پر اپنا کردار ادا کر سکتے ہیں اور امن و انصاف کا پیغام پھیلا سکتے ہیں۔ علم پر اس زور نے تاریخی طور پر مسلم معاشروں کو متعدد سائنسی اور فلسفیانہ شعبوں میں پیشرو بننے پر مجبور کیا، جس سے عالمی تہذیب میں بے پناہ حصہ ڈالا۔ کتب خانے اور تعلیمی مراکز پھلے پھولے، دنیا کے کونے کونے سے علماء کو اپنی طرف متوجہ کیا، جو بھرپور فکری ماحول میں حصہ لینے اور اس سے مستفید ہونے کے لیے بے تاب تھے۔ یہ فکری وراثت نسلوں کو متاثر کرتی رہتی ہے، یہ اس بات پر زور دیتی ہے کہ علم کا حصول اسلامی طرز زندگی کا ایک اہم جزو ہے۔ یہ ہمیں یاد دلاتا ہے کہ حکمت مومن کی گمشدہ میراث ہے، اور جہاں کہیں اسے ملے وہ اس کا سب سے زیادہ حقدار ہے۔ اس طرح، تعلیم صرف ایک ذاتی کوشش نہیں بلکہ ایک اجتماعی ذمہ داری ہے، جو اس بات کو یقینی بناتی ہے کہ علم کی روشنی آنے والی نسلوں کے لیے راستوں کو روشن کرتی رہے اور عالمی خوشحالی میں حصہ ڈالے۔', 1, 1, 'new.jpg', 1, 0, 0, 'published', 0, 'the-importance-of-seeking-knowledge-in-islam', '2025-08-16 05:52:42', '2025-08-17 09:52:42', '2025-08-17 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Challenges and Opportunities for Muslim Youth in the West', 'مغرب میں مسلم نوجوانوں کے لیے چیلنجز اور مواقع', 'Muslim youth in Western societies face a unique set of challenges and opportunities. Navigating their religious identity within a secular framework, dealing with Islamophobia, and maintaining cultural heritage while integrating into broader society are common struggles. However, these environments also offer unparalleled opportunities for education, professional growth, and active participation in civil society. Many young Muslims are leveraging these opportunities to bridge cultural divides, contribute positively to their communities, and present an authentic image of Islam. This article explores the various dimensions of their experiences, focusing on strategies for resilience, advocacy, and community building. It highlights successful initiatives led by Muslim youth in education, entrepreneurship, and social activism, demonstrating their dynamic role in shaping both their own future and the broader societal landscape. The role of strong community support, religious education, and interfaith dialogue is crucial in empowering these young individuals. Addressing issues like identity crisis, mental health, and discrimination requires a multi-faceted approach involving families, religious institutions, and educational bodies. Conversely, the opportunities presented by Western societies, such as freedom of expression and access to diverse educational resources, can foster critical thinking and intellectual development among Muslim youth. Their active engagement in academia, arts, and politics enriches the multicultural fabric of these nations. By fostering a strong sense of self and community, these young Muslims can become powerful agents of positive change, contributing to a more inclusive and understanding world. Their unique position allows them to act as cultural ambassadors, dispelling misconceptions and building bridges of understanding between different communities. This dual perspective enables them to draw strength from their faith and heritage while embracing the advancements and freedoms offered by modern societies. The narrative of Muslim youth in the West is one of resilience, innovation, and a powerful commitment to both their faith and their societies.', 'مغربی معاشروں میں مسلم نوجوانوں کو منفرد چیلنجز اور مواقع کا سامنا ہے۔ سیکولر ڈھانچے میں اپنی مذہبی شناخت کو قائم رکھنا، اسلامو فوبیا سے نمٹنا، اور وسیع تر معاشرے میں ضم ہوتے ہوئے ثقافتی ورثے کو برقرار رکھنا عام جدوجہد ہیں۔ تاہم، یہ ماحول تعلیم، پیشہ ورانہ ترقی، اور سول سوسائٹی میں فعال شرکت کے لیے بے مثال مواقع بھی فراہم کرتے ہیں۔ بہت سے نوجوان مسلمان ان مواقع کو ثقافتی خلیجوں کو پاٹنے، اپنی برادریوں میں مثبت حصہ ڈالنے، اور اسلام کی ایک مستند تصویر پیش کرنے کے لیے استعمال کر رہے ہیں۔ یہ مضمون ان کے تجربات کے مختلف پہلوؤں کو تلاش کرتا ہے، جس میں لچک، وکالت، اور کمیونٹی کی تعمیر کی حکمت عملیوں پر توجہ مرکوز کی گئی ہے۔ یہ مسلم نوجوانوں کی قیادت میں تعلیم، کاروبار، اور سماجی سرگرمیوں میں کامیاب اقدامات کو نمایاں کرتا ہے، جو ان کے اپنے مستقبل اور وسیع تر سماجی منظر نامے کو تشکیل دینے میں ان کے متحرک کردار کو ظاہر کرتا ہے۔ مضبوط کمیونٹی سپورٹ، مذہبی تعلیم، اور بین المذاہب مکالمے کا کردار ان نوجوانوں کو بااختیار بنانے میں اہم ہے۔ شناخت کے بحران، دماغی صحت، اور امتیازی سلوک جیسے مسائل سے نمٹنے کے لیے خاندانوں، مذہبی اداروں، اور تعلیمی اداروں کو شامل کرتے ہوئے ایک کثیر جہتی نقطہ نظر کی ضرورت ہے۔ اس کے برعکس، مغربی معاشروں کی طرف سے پیش کردہ مواقع، جیسے اظہار رائے کی آزادی اور متنوع تعلیمی وسائل تک رسائی، مسلم نوجوانوں میں تنقیدی سوچ اور فکری ترقی کو فروغ دے سکتی ہے۔ ان کی تعلیمی، فنون لطیفہ، اور سیاسی شعبوں میں فعال شرکت ان اقوام کے کثیر الثقافتی ڈھانچے کو تقویت بخشتی ہے۔ خود اور کمیونٹی کے ایک مضبوط احساس کو فروغ دے کر، یہ نوجوان مسلمان مثبت تبدیلی کے طاقتور ایجنٹ بن سکتے ہیں، جو ایک زیادہ جامع اور باہم سمجھوتہ کرنے والی دنیا میں حصہ ڈال سکتے ہیں۔ ان کی منفرد پوزیشن انہیں اپنے ایمان اور ورثے سے طاقت حاصل کرنے کی اجازت دیتی ہے جبکہ جدید معاشروں کی پیش کردہ ترقیوں اور آزادیوں کو قبول کرتے ہیں۔ مغرب میں مسلم نوجوانوں کا بیانیہ لچک، جدت، اور اپنے ایمان اور اپنے معاشروں دونوں کے لیے ایک مضبوط عزم کا ہے۔', 6, 1, 'new.jpg', 0, 0, 0, 'published', 0, 'challenges-and-opportunities-for-muslim-youth-in-the-west', '2025-08-15 05:52:42', '2025-08-17 09:52:42', '2025-08-17 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Understanding Islamic Finance: Principles and Practices', 'اسلامی مالیات کو سمجھنا: اصول اور طریقے', 'Islamic finance is a rapidly growing sector that operates on principles derived from Islamic law (Sharia). Unlike conventional finance, it prohibits interest (riba), excessive uncertainty (gharar), and gambling (maysir). Instead, it promotes risk-sharing, ethical investments, and tangible asset-backed transactions. Key instruments include Mudarabah (profit-sharing), Musharakah (joint venture), Murabaha (cost-plus financing), and Ijarah (leasing). This article provides an overview of these fundamental principles and explores how Islamic financial institutions operate globally. It delves into the ethical dimensions of Islamic finance, emphasizing social justice, equitable distribution of wealth, and avoidance of exploitative practices. The growth of Islamic banking, sukuk (Islamic bonds), and Takaful (Islamic insurance) demonstrates the increasing demand for Sharia-compliant financial products. Challenges include standardization across different jurisdictions and the need for greater innovation in product development. However, the focus on ethical investing and social responsibility makes Islamic finance an attractive alternative for both Muslims and non-Muslims seeking sustainable and values-driven financial solutions. The concept of \"Maqasid al-Sharia\" (objectives of Islamic law) plays a crucial role in guiding the development of Islamic financial products, ensuring they contribute to the welfare of society. This includes promoting real economic activity, discouraging speculative transactions, and fostering financial inclusion. The integration of technology, particularly FinTech, is also opening new avenues for Islamic finance to reach a wider audience and offer more accessible services. This expansion highlights a commitment to providing ethical financial solutions that align with the moral framework of Islam.', 'اسلامی مالیات ایک تیزی سے ترقی پذیر شعبہ ہے جو اسلامی قانون (شریعت) سے ماخوذ اصولوں پر کام کرتا ہے۔ روایتی مالیات کے برعکس، یہ سود (ربا)، ضرورت سے زیادہ غیر یقینی (غرر)، اور جوئے (میسر) کو ممنوع قرار دیتا ہے۔ اس کے بجائے، یہ خطرے کی شراکت، اخلاقی سرمایہ کاری، اور ٹھوس اثاثہ جات پر مبنی لین دین کو فروغ دیتا ہے۔ اہم آلات میں مضاربہ (منافع کی شراکت)، مشارکہ (مشترکہ منصوبہ)، مرابحہ (لاگت جمع منافع پر مالیات)، اور اجارہ (لیزنگ) شامل ہیں۔ یہ مضمون ان بنیادی اصولوں کا ایک جائزہ فراہم کرتا ہے اور یہ بھی بتاتا ہے کہ اسلامی مالیاتی ادارے عالمی سطح پر کیسے کام کرتے ہیں۔ یہ اسلامی مالیات کے اخلاقی پہلوؤں کو گہرائی سے بیان کرتا ہے، جس میں سماجی انصاف، دولت کی مساوی تقسیم، اور استحصال پر مبنی طریقوں سے گریز پر زور دیا گیا ہے۔ اسلامی بینکاری، صکوک (اسلامی بانڈز)، اور تکافل (اسلامی بیمہ) کی ترقی شریعت کے مطابق مالیاتی مصنوعات کی بڑھتی ہوئی مانگ کو ظاہر کرتی ہے۔ چیلنجز میں مختلف دائرہ اختیار میں معیاری کاری اور مصنوعات کی ترقی میں زیادہ جدت کی ضرورت شامل ہے۔ تاہم، اخلاقی سرمایہ کاری اور سماجی ذمہ داری پر توجہ اسلامی مالیات کو مسلمانوں اور غیر مسلموں دونوں کے لیے ایک پرکشش متبادل بناتی ہے جو پائیدار اور اقدار پر مبنی مالیاتی حل تلاش کر رہے ہیں۔ \"مقاصد الشریعہ\" (اسلامی قانون کے مقاصد) کا تصور اسلامی مالیاتی مصنوعات کی ترقی کی رہنمائی میں اہم کردار ادا کرتا ہے، اس بات کو یقینی بناتا ہے کہ وہ معاشرے کی فلاح و بہبود میں حصہ ڈالیں۔ اس میں حقیقی اقتصادی سرگرمیوں کو فروغ دینا، قیاس آرائی پر مبنی لین دین کی حوصلہ شکنی کرنا، اور مالی شمولیت کو فروغ دینا شامل ہے۔ ٹیکنالوجی کا انضمام، خاص طور پر فن ٹیک، اسلامی مالیات کے لیے ایک وسیع سامعین تک پہنچنے اور زیادہ قابل رسائی خدمات پیش کرنے کے نئے راستے بھی کھول رہا ہے۔ یہ توسیع اخلاقی مالیاتی حل فراہم کرنے کے عزم کو نمایاں کرتی ہے جو اسلام کے اخلاقی ڈھانچے کے مطابق ہیں۔', 3, 1, 'new.jpg', 1, 0, 0, 'published', 1, 'understanding-islamic-finance-principles-and-practices', '2025-08-14 05:52:42', '2025-08-17 09:52:42', '2025-08-17 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'The Architectural Grandeur of Islamic Civilizations', 'اسلامی تہذیبوں کی تعمیراتی عظمت', 'Islamic civilization has left an indelible mark on the world through its magnificent architectural achievements. From the majestic mosques of Istanbul and Cairo to the intricate palaces of Alhambra and the innovative urban planning of Baghdad, Islamic architecture reflects a rich blend of artistic brilliance, scientific knowledge, and spiritual symbolism. This article explores the key elements that define Islamic architecture, including geometric patterns, calligraphy, arches, domes, and courtyards, and their evolution across different regions and dynasties. It highlights how these structures were not merely buildings but living expressions of Islamic faith, culture, and societal values. The fusion of diverse influences, including Persian, Byzantine, and Roman, led to a unique architectural language that emphasized harmony, symmetry, and divine beauty. The use of water features, gardens, and light further enhanced the spiritual and aesthetic experience. Examples such as the Dome of the Rock, the Great Mosque of Cordoba, and the Taj Mahal stand as testaments to the enduring legacy of Islamic architectural grandeur, continuing to inspire awe and admiration worldwide. These buildings often served multiple purposes, acting as centers for worship, education, commerce, and social gathering, reflecting the holistic nature of Islamic society. The skilled craftsmanship and innovative engineering techniques employed in their construction are a testament to the advanced knowledge of the time. The emphasis on intricate details, vibrant colors, and natural elements created environments that were both functional and spiritually uplifting. This rich architectural heritage offers invaluable insights into the artistic and scientific achievements of Islamic civilizations, inspiring contemporary architects and artists to this day. The preservation and study of these historical sites are crucial for understanding the profound cultural and spiritual contributions of Islam to global heritage.', 'اسلامی تہذیب نے اپنی شاندار تعمیراتی کامیابیوں کے ذریعے دنیا پر ایک انمٹ نقش چھوڑا ہے۔ استنبول اور قاہرہ کی پرشکوہ مساجد سے لے کر الحمرا کے پیچیدہ محلات اور بغداد کی جدید شہری منصوبہ بندی تک، اسلامی فن تعمیر فنکارانہ ذہانت، سائنسی علم، اور روحانی علامت کا ایک بھرپور امتزاج ظاہر کرتا ہے۔ یہ مضمون اسلامی فن تعمیر کو متعین کرنے والے کلیدی عناصر کو تلاش کرتا ہے، جن میں ہندسی نمونے، خطاطی، محرابیں، گنبد، اور صحن شامل ہیں، اور مختلف علاقوں اور خاندانوں میں ان کے ارتقاء کو بیان کرتا ہے۔ یہ اس بات پر روشنی ڈالتا ہے کہ یہ ڈھانچے صرف عمارتیں نہیں تھے بلکہ اسلامی عقیدے، ثقافت، اور سماجی اقدار کا ایک زندہ اظہار تھے۔ فارسی، بازنطینی، اور رومن سمیت مختلف اثرات کے امتزاج نے ایک منفرد تعمیراتی زبان کو جنم دیا جس میں ہم آہنگی، توازن، اور الہی خوبصورتی پر زور دیا گیا تھا۔ پانی کی خصوصیات، باغات، اور روشنی کے استعمال نے روحانی اور جمالیاتی تجربے کو مزید بڑھایا۔ گنبد الصخرہ، قرطبہ کی عظیم مسجد، اور تاج محل جیسی مثالیں اسلامی تعمیراتی عظمت کی پائیدار وراثت کے ثبوت کے طور پر کھڑی ہیں، جو دنیا بھر میں حیرت اور تعریف کو متاثر کرتی رہتی ہیں۔ یہ عمارتیں اکثر متعدد مقاصد کی تکمیل کرتی تھیں، عبادت، تعلیم، تجارت، اور سماجی اجتماعات کے مراکز کے طور پر کام کرتی تھیں، جو اسلامی معاشرے کی جامع نوعیت کو ظاہر کرتی ہیں۔ ان کی تعمیر میں استعمال ہونے والی ہنر مند کاریگری اور جدید انجینئرنگ کی تکنیکیں اس وقت کے جدید علم کا ثبوت ہیں۔ پیچیدہ تفصیلات، وشد رنگوں، اور قدرتی عناصر پر زور نے ایسے ماحول پیدا کیے جو عملی اور روحانی طور پر بلند کرنے والے تھے۔ یہ بھرپور تعمیراتی ورثہ اسلامی تہذیبوں کے فنکارانہ اور سائنسی کارناموں کے بارے میں انمول بصیرت پیش کرتا ہے، جو آج بھی عصری معماروں اور فنکاروں کو متاثر کرتا ہے۔ ان تاریخی مقامات کا تحفظ اور مطالعہ عالمی ورثے میں اسلام کے گہرے ثقافتی اور روحانی شراکت کو سمجھنے کے لیے بہت اہم ہے۔', 4, 1, 'new.jpg', 0, 0, 0, 'published', 0, 'the-architectural-grandeur-of-islamic-civilizations', '2025-08-13 05:52:42', '2025-08-17 09:52:42', '2025-08-17 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Ramadan: A Month of Spiritual Reflection and Community Bonding', 'رمضان: روحانی غور و فکر اور برادری کے تعلق کا مہینہ', 'Ramadan, the ninth month of the Islamic calendar, is observed by Muslims worldwide as a month of fasting, prayer, reflection, and community. It commemorates the first revelation of the Quran to Prophet Muhammad (peace be upon him). During this month, Muslims abstain from food and drink from dawn until sunset, a practice known as Sawm. Beyond mere abstinence, Ramadan encourages heightened spiritual awareness, self-discipline, and devotion to Allah. It is a time for increased recitation of the Quran, extra prayers (Tarawih), and charitable acts (Sadaqah and Zakat). The spirit of generosity and compassion flourishes, with Muslims often breaking their fasts together (Iftar) and engaging in communal worship. This communal aspect strengthens bonds within families and the wider Muslim community, fostering a sense of unity and shared purpose. Ramadan is also a period of introspection, allowing individuals to evaluate their lives, seek forgiveness, and renew their commitment to Islamic teachings. The last ten nights of Ramadan are particularly significant, as one of them is Laylat al-Qadr (the Night of Power), believed to be when the first verses of the Quran were revealed. Observing Ramadan culminates in the joyous celebration of Eid al-Fitr, a day of gratitude and togetherness. The teachings and practices of Ramadan aim to instill virtues that last beyond the holy month, promoting a continuous state of mindfulness, empathy, and devotion throughout the year. It serves as an annual spiritual training ground, enabling believers to purify their souls and draw closer to their Creator. The communal meals, shared prayers, and collective charitable efforts during Ramadan embody the core Islamic values of solidarity and mutual support, making it a truly transformative experience for millions.', 'رمضان، اسلامی کیلنڈر کا نواں مہینہ، دنیا بھر کے مسلمانوں کی طرف سے روزے، دعا، غور و فکر، اور برادری کے مہینے کے طور پر منایا جاتا ہے۔ یہ نبی اکرم صلی اللہ علیہ وسلم پر قرآن کے پہلے نزول کی یادگار ہے۔ اس مہینے کے دوران، مسلمان فجر سے غروب آفتاب تک کھانے پینے سے پرہیز کرتے ہیں، جسے صوم کہتے ہیں۔ محض پرہیز کے علاوہ، رمضان روحانی بیداری، خود انظباط، اور اللہ کے ساتھ لگاؤ کو بڑھاتا ہے۔ یہ قرآن کی تلاوت، اضافی نمازوں (تراویح)، اور خیراتی کاموں (صدقہ اور زکوٰۃ) میں اضافے کا وقت ہے۔ سخاوت اور ہمدردی کا جذبہ پھلتا پھولتا ہے، مسلمان اکثر اپنی افطاری ایک ساتھ کرتے ہیں اور اجتماعی عبادت میں مشغول ہوتے ہیں۔ یہ اجتماعی پہلو خاندانوں اور وسیع تر مسلم برادری کے اندر تعلقات کو مضبوط کرتا ہے، جس سے اتحاد اور مشترکہ مقصد کا احساس پیدا ہوتا ہے۔ رمضان خود احتسابی کا بھی ایک دور ہے، جو افراد کو اپنی زندگیوں کا جائزہ لینے، معافی مانگنے، اور اسلامی تعلیمات کے ساتھ اپنے عزم کی تجدید کرنے کی اجازت دیتا ہے۔ رمضان کی آخری دس راتیں خاص طور پر اہم ہیں، کیونکہ ان میں سے ایک لیلۃ القدر (طاقت کی رات) ہے، جس کے بارے میں خیال کیا جاتا ہے کہ اس رات قرآن کی پہلی آیات نازل ہوئیں۔ رمضان کا اختتام عید الفطر کی پرمسرت تقریب میں ہوتا ہے، جو شکرگزاری اور یکجہتی کا دن ہے۔ رمضان کی تعلیمات اور طریقوں کا مقصد ایسی خوبیوں کو فروغ دینا ہے جو مقدس مہینے سے آگے تک جاری رہیں، جو پورے سال شعور، ہمدردی، اور لگن کی مسلسل حالت کو فروغ دیتی ہیں۔ یہ ایک سالانہ روحانی تربیتی میدان کے طور پر کام کرتا ہے، جو مومنوں کو اپنی روحوں کو پاک کرنے اور اپنے خالق کے قریب ہونے کے قابل بناتا ہے۔ رمضان کے دوران اجتماعی کھانوں، مشترکہ نمازوں، اور اجتماعی خیراتی کوششوں میں یکجہتی اور باہمی تعاون کی بنیادی اسلامی اقدار شامل ہیں، جو اسے لاکھوں لوگوں کے لیے ایک حقیقی تبدیلی کا تجربہ بناتی ہے۔', 5, 1, 'new.jpg', 0, 0, 0, 'published', 0, 'ramadan-a-month-of-spiritual-reflection-and-community-bonding', '2025-08-12 05:52:42', '2025-08-17 09:52:42', '2025-08-17 09:52:42', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `article_likes`
--

CREATE TABLE `article_likes` (
  `user_id` int NOT NULL,
  `article_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ur` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_en`, `name_ur`, `slug`) VALUES
(1, 'Islamic Studies', 'اسلامی علوم', 'islamic-studies'),
(2, 'Current Affairs (Islamic Perspective)', 'حالیہ مسائل (اسلامی نقطہ نظر)', 'current-affairs-islamic'),
(3, 'Fiqh & Sharia', 'فقہ و شریعت', 'fiqh-sharia'),
(4, 'History of Islam', 'تاریخ اسلام', 'history-islam'),
(5, 'Spirituality & Ethics', 'روحانیت و اخلاق', 'spirituality-ethics'),
(6, 'Muslim World News', 'عالم اسلام کی خبریں', 'muslim-world-news'),
(7, 'Opinion (Islamic)', 'رائے (اسلامی)', 'opinion-islamic');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `article_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `follower_id` int NOT NULL,
  `followed_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int NOT NULL,
  `question_en` text NOT NULL,
  `question_ur` text NOT NULL,
  `options_en` json NOT NULL,
  `options_ur` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `poll_votes`
--

CREATE TABLE `poll_votes` (
  `poll_id` int NOT NULL,
  `user_id` int NOT NULL,
  `option_index` int NOT NULL,
  `voted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisions`
--

CREATE TABLE `revisions` (
  `id` int NOT NULL,
  `article_id` int NOT NULL,
  `title_en` text NOT NULL,
  `title_ur` text NOT NULL,
  `content_en` longtext NOT NULL,
  `content_ur` longtext NOT NULL,
  `updated_by_user_id` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `start_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL,
  `status` enum('active','inactive','canceled','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ur` varchar(100) NOT NULL,
  `description_en` text,
  `description_ur` text,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int NOT NULL,
  `features` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','editor','public') DEFAULT 'public',
  `avatar` varchar(255) DEFAULT NULL,
  `bio_en` text,
  `bio_ur` text,
  `social_links` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `bio_en`, `bio_ur`, `social_links`, `created_at`) VALUES
(1, 'admin', 'admin@islamictimes.pk', '$2y$10$ZaAesmo0AYTYYzUz6wYqFeaCFiOJXViDA2HIHunY/t344Qb5rg7G6', 'admin', NULL, 'Administrator of Islamic Times. Oversees all operations related to Islamic scholarship and news.', 'اسلامک ٹائمز کے منتظم۔ اسلامی علوم اور خبروں سے متعلق تمام کارروائیوں کی نگرانی کرتے ہیں۔', NULL, '2025-08-17 09:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `user_submissions`
--

CREATE TABLE `user_submissions` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title_en` text NOT NULL,
  `title_ur` text NOT NULL,
  `content_en` longtext NOT NULL,
  `content_ur` longtext NOT NULL,
  `category_id` int DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ad_units`
--
ALTER TABLE `ad_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `article_likes`
--
ALTER TABLE `article_likes`
  ADD PRIMARY KEY (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `poll_votes`
--
ALTER TABLE `poll_votes`
  ADD PRIMARY KEY (`poll_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `revisions`
--
ALTER TABLE `revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_submissions`
--
ALTER TABLE `user_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_units`
--
ALTER TABLE `ad_units`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisions`
--
ALTER TABLE `revisions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_submissions`
--
ALTER TABLE `user_submissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_likes`
--
ALTER TABLE `article_likes`
  ADD CONSTRAINT `article_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_likes_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `poll_votes`
--
ALTER TABLE `poll_votes`
  ADD CONSTRAINT `poll_votes_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poll_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `revisions`
--
ALTER TABLE `revisions`
  ADD CONSTRAINT `revisions_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revisions_ibfk_2` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `user_submissions`
--
ALTER TABLE `user_submissions`
  ADD CONSTRAINT `user_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_submissions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
