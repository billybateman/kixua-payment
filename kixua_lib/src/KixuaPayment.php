<?php
namespace KixuaLib;

class KixuaPayment {
    private $apiKey;
    private $apiEndpoint = "https://api.kixua.com/v1";

    /**
     * Constructor to set API key dynamically.
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Make a request to the Kixua API.
     */
    private function request($method, $endpoint, $data = []) {
        $url = $this->apiEndpoint . $endpoint;
        $headers = [
            "Authorization: Bearer " . $this->apiKey,
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => $error];
        }

        return json_decode($response, true);
    }

    /**
     * Process a credit card payment.
     */
    public function processPayment($amount, $currency, $cardDetails) {
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'card' => $cardDetails
        ];
        return $this->request('POST', '/payments', $data);
    }

    /**
     * Issue a refund for a transaction.
     */
    public function refundPayment($paymentId) {
        return $this->request('POST', "/payments/$paymentId/refund");
    }

    /**
     * Get transaction details by ID.
     */
    public function getTransactionDetails($transactionId) {
        return $this->request('GET', "/transactions/$transactionId");
    }

    /**
     * Retrieve transactions within a given period.
     */
    public function getTransactions($period) {
        $validPeriods = ['daily', 'weekly', 'monthly', 'yearly'];
        if (!in_array($period, $validPeriods)) {
            return ['success' => false, 'error' => 'Invalid period specified'];
        }
        return $this->request('GET', "/transactions?period=$period");
    }
}
