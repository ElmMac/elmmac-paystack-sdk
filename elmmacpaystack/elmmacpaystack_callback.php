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
 * Paystack callback URL
 * Called by Paystack after customer pays.
 */

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../src/ElmMacPayStack.php';

use \PDO;

header('Content-Type: text/html; charset=utf-8');

try {
    $reference = $_GET['reference'] ?? '';
    if ($reference === '') {
        throw new RuntimeException('Missing transaction reference');
    }

    $pdo = DB::conn();

    // Fetch record so we can show info back if needed
    $stmt = $pdo->prepare(
        "SELECT * FROM elmmacpaystackpayments WHERE reference = :reference LIMIT 1"
    );
    $stmt->execute([':reference' => $reference]);
    $payment = $stmt->fetch();

    if (!$payment) {
        throw new RuntimeException('Local payment record not found');
    }

    // Verify with Paystack
    $sdk      = new ElmMacPayStack(PAYSTACK_SECRET_KEY);
    $result   = $sdk->verify($reference);

    $status   = 'failed';
    $psStatus = $result['data']['status'] ?? null;

    if ($psStatus === 'success') {
        $status = 'success';
    }

    // Update record
    $stmt = $pdo->prepare(
        "UPDATE elmmacpaystackpayments
         SET status = :status,
             response_payload = :response_payload,
             updated_at = NOW()
         WHERE reference = :reference"
    );
    $stmt->execute([
        ':status'           => $status,
        ':response_payload' => json_encode($result),
        ':reference'        => $reference,
    ]);

    // Simple UI
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>ElmMac Paystack Result</title>
    </head>
    <body>
        <!--you can create your own views and redirect callback there-->
        <h2>Payment <?php echo $status === 'success' ? 'Successful ✅' : 'Failed ❌'; ?></h2>
        <p>Reference: <strong><?php echo htmlspecialchars($reference, ENT_QUOTES, 'UTF-8'); ?></strong></p>
        <p>Amount: <?php echo htmlspecialchars($payment['amount'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            <?php echo htmlspecialchars($payment['currency'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
        <p>Status from Paystack: <code><?php echo htmlspecialchars($psStatus ?? 'unknown', ENT_QUOTES, 'UTF-8'); ?></code></p>
    </body>
    </html>
    <?php

} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Callback error</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
}
