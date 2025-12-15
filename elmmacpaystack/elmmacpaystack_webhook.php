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
 * Paystack webhook listener (optional)
 * Set this URL in your Paystack dashboard.
 */

//  Optional webhook endpoint. Make sure you configure this URL in Paystack dashboard same place where you find your API keys.
// This code verifies the X-Paystack-Signature and updates the payment row.

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

use \PDO;

header('Content-Type: application/json; charset=utf-8');

$secret = PAYSTACK_SECRET_KEY; // same as used for API

// Get raw payload
$input = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Empty body']);
    exit;
}

// Verify signature (from Paystack docs)
$computed = hash_hmac('sha512', $input, $secret);
if (!hash_equals($computed, $signature)) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Invalid signature']);
    exit;
}

$event = json_decode($input, true);
if (!is_array($event)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid JSON']);
    exit;
}

try {
    $data = $event['data'] ?? [];
    $reference = $data['reference'] ?? null;
    $psStatus = $data['status'] ?? null;

    if (!$reference) {
        throw new RuntimeException('Missing reference in webhook');
    }

    $pdo = DB::conn();

    $status = ($psStatus === 'success') ? 'success' : 'failed';

    $stmt = $pdo->prepare(
        "UPDATE elmmacpaystackpayments
         SET status = :status,
             response_payload = :response_payload,
             updated_at = NOW()
         WHERE reference = :reference"
    );
    $stmt->execute([
        ':status' => $status,
        ':response_payload' => json_encode($event),
        ':reference' => $reference,
    ]);

    echo json_encode(['status' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}