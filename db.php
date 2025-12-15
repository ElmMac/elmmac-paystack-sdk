<?php
// +------------------------------------------------------------------------+
// | @author Misael cruise Mutege (ElmMac Software Developers)
// | @author_url 1: https://elmmac.co.za
// | @author_url 2: https://github.com/ElmMac
// | Call/whatsApp: +27786411181
//
// +------------------------------------------------------------------------+
// | ElmMac Software Developers & Engineers | Web Application Development, iOS/Android/Cross Platform Mobile Application Development, UI/UX App Designs, Custom Software or Systems Development. Linguals: Python - (Django, Flask, Plotly, Pandas, KivyMD, Maplotlib, FastAPi, PyMath, PyGame, PySci, Data Science, Machine Learning, Ai), C#, JavaScript, HTML, CSS, SASS,  DART & Flutter, Kotlin, Java, PHP & Laravel Framework, Git
// +------------------------------------------------------------------------+

/**
 * Simple DB helper & Paystack config
 * Location: /home/USERNAME/test.trumateapp.com/db.php
 */

declare(strict_types=1);

class DB
{
    /** @var ?PDO */
    private static $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            // TODO: put your real credentials here
            $host = 'localhost';
            $dbname = 'your_db_name';
            $user = 'your_db_user';
            $pass = 'your_db_password';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            self::$pdo = new PDO($dsn, $user, $pass, $options);
        }

        return self::$pdo;
    }
}

/**
 * Paystack keys
 * Use *secret* key here. You can later move this into env/ini if you want.
 */
if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', 'sk_live_or_test_xxxxxxxxxxxxxxxxxxc69a6f5ce8f');
}