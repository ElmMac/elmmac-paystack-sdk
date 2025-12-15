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
 * Initialize Paystack payment
 * POSTed from elmmacpaystack_form.html
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../src/ElmMacPayStack.php';

use \PDO;

header('Content-Type: text/html; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Invalid request method');
    }

    // ---- 1. Read & validate input ----
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $amount   = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $userId   = isset($_POST['user_id']) ? (int) $_POST['user_id'] : null;
    $currency = isset($_POST['currency']) && $_POST['currency'] !== ''
        ? strtoupper(trim($_POST['currency']))
        : 'ZAR';

    if (!$email || $amount <= 0) {
        throw new RuntimeException('Invalid email or amount');
    }

    // amount to lowest units (kobo / cents)
    $amountInKobo = (int) round($amount * 100);

    // ---- 2. Build callback URL ----
    $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');        // /elmmacpaystack
    $callbackUrl = $scheme . '://' . $host . $basePath . '/elmmacpaystack_callback.php';

    // ---- 3. Build payload & create local reference ----
    $reference = 'elmmac_' . bin2hex(random_bytes(8));

    $payload = [
        'email'        => $email,
        'amount'       => $amountInKobo,
        'currency'     => $currency,
        'reference'    => $reference,
        'callback_url' => $callbackUrl,
    ];

    // ---- 4. Insert "initialized" record in DB ----
    $pdo = DB::conn();

    $stmt = $pdo->prepare(
        "INSERT INTO elmmacpaystackpayments
            (user_id, email, amount, currency, reference, status, request_payload)
         VALUES
            (:user_id, :email, :amount, :currency, :reference, 'initialized', :request_payload)"
    );

    $stmt->execute([
        ':user_id'         => $userId ?: null,
        ':email'           => $email,
        ':amount'          => (int) $amount,
        ':currency'        => $currency,
        ':reference'       => $reference,
        ':request_payload' => json_encode($payload),
    ]);

    // ---- 5. Call Paystack via SDK ----
    $sdk      = new ElmMacPayStack(PAYSTACK_SECRET_KEY);
    $response = $sdk->initialize($payload);

    if (empty($response) || empty($response['status'])) {
        throw new RuntimeException('Paystack initialize failed');
    }

    if (empty($response['data']['authorization_url'])) {
        throw new RuntimeException('Missing authorization_url from Paystack');
    }

    // ---- 6. Store init response ----
    $stmt = $pdo->prepare(
        "UPDATE elmmacpaystackpayments
         SET response_payload = :response_payload, updated_at = NOW()
         WHERE reference = :reference"
    );
    $stmt->execute([
        ':response_payload' => json_encode($response),
        ':reference'        => $reference,
    ]);

    // ---- 7. Redirect to Paystack checkout ----
    header('Location: ' . $response['data']['authorization_url']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Initialization error</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
}
