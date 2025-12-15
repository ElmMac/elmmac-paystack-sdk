# ElmMacPaystack – PHP Paystack SDK

ElmMacPaystack is a lightweight PHP SDK for integrating Paystack into classic/procedural PHP projects. It handles initialize, callback and webhook flows, and logs full request/response payloads into a generic `elmmacpaystackpayments` table.

## Features

- Simple PHP class wrapper around Paystack REST API
- Initialize payments with custom metadata
- Callback handler for success/failed payments
- Optional webhook listener with signature verification
- Request / response payload logging for debugging & analytics

## Requirements

- PHP 7.4+ (or higher)
- cURL extension enabled
- MySQL / MariaDB (for the example payments table)
- A Paystack account with secret & public keys

## Installation

1. Copy `src/ElmMacPayStack.php` into your project (for example into a `src/` or `lib/` folder).
2. Include the class in your bootstrap / init script:

```php
require_once __DIR__ . '/src/ElmMacPayStack.php';

use ElmMacPaystack\ElmMacPayStack;

$paystack = new ElmMacPayStack('YOUR_PAYSTACK_SECRET_KEY');
```

3. Create the `elmmacpaystackpayments` table using the SQL provided in `database_table.sql` (or adapt it to your schema).

## Quick start

```php
$payload = [
    'email'       => 'customer@example.com',
    'amount'      => 2500 * 100, // kobo
    'reference'   => uniqid('elmmac_', true),
    'callback_url'=> 'https://yourdomain.com/elmmacpaystack_callback.php',
    'metadata'    => ['user_id' => 123],
];

$response = $paystack->initialize($payload);

if ($response['status']) {
    header('Location: ' . $response['data']['authorization_url']);
    exit;
} else {
    // handle error
}
```

Then in your callback script you can:

```php
// verify reference and update elmmacpaystackpayments
```

(See the example files in `examples/` for `init`, `callback`, and `webhook` handlers.)

## Roadmap

- More helper methods (verify, refund, list transactions)
- Framework-agnostic middleware snippets (Laravel, Symfony, etc.)
- Better error handling & typed responses

## Contributing

Pull requests and issues are welcome. Please open an issue first if you plan a bigger change so we can discuss direction.

## License

This project is open-source. You can use the MIT license below or choose a different license that fits your needs.

```
## 🙏 Credits

Developed with 🚀 by **ElmMac Pty Ltd**\
Maintained by @ElmMac - **Misael Cruise Mutege** — [WhatsApp: +27786411181](https://web.whatsapp.com/send?phone=27786411181)  Durban, South Africa\
Digital Dev | Hustler Mode: `ON 💼`

---
```
