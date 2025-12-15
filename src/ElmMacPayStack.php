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
 * ElmMacPayStack
 *
 * Generic Paystack wrapper
 * - No QuickDate logic
 * - No DB logic
 * - No session logic
 * - Pure HTTP client
 */
 

class ElmMacPayStack
{
    private $secretKey;
    private $baseUrl = 'https://api.paystack.co';

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Internal HTTP helper
     */
    private function request(string $method, string $endpoint, array $payload = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
        ];

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        $method = strtoupper($method);

        if ($method === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        } elseif (!empty($payload)) {
            // GET with query string
            $url               = $url . '?' . http_build_query($payload);
            $opts[CURLOPT_URL] = $url;
        }

        curl_setopt_array($ch, $opts);

        $raw      = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($raw === false) {
            return [
                'status'      => false,
                'message'     => 'Curl error: ' . $curlErr,
                'http_status' => $httpCode,
            ];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [
                'status'      => false,
                'message'     => 'Invalid JSON from Paystack',
                'raw'         => $raw,
                'http_status' => $httpCode,
            ];
        }

        $decoded['_http_status'] = $httpCode;

        return $decoded;
    }

    /**
     * Initialize a Paystack transaction
     */
    public function initialize(array $payload): array
    {
        return $this->request('POST', '/transaction/initialize', $payload);
    }

    /**
     * Verify a transaction by reference
     */
    public function verify(string $reference): array
    {
        return $this->request('GET', '/transaction/verify/' . urlencode($reference));
    }
}
