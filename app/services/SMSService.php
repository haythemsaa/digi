<?php

/**
 * SMS Service
 * Handles SMS notifications via Twilio API
 *
 * @author DigiParc Team
 * @version 1.0
 */
class SMSService
{
    private $twilioSid;
    private $twilioToken;
    private $twilioFrom;

    public function __construct()
    {
        // Load Twilio credentials from config
        $this->twilioSid = defined('TWILIO_SID') ? TWILIO_SID : '';
        $this->twilioToken = defined('TWILIO_TOKEN') ? TWILIO_TOKEN : '';
        $this->twilioFrom = defined('TWILIO_FROM') ? TWILIO_FROM : '';
    }

    /**
     * Send SMS via Twilio
     *
     * @param array $data ['to' => phone, 'message' => text]
     * @return array Status
     */
    public function send($data)
    {
        if (!$this->isConfigured()) {
            return [
                'status' => 'failed',
                'error' => 'Twilio not configured',
                'metadata' => null
            ];
        }

        try {
            // Format phone number (international format)
            $to = $this->formatPhoneNumber($data['to']);

            // Twilio API endpoint
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json";

            // Prepare request data
            $postData = [
                'From' => $this->twilioFrom,
                'To' => $to,
                'Body' => $data['message']
            ];

            // Send request
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_USERPWD, $this->twilioSid . ':' . $this->twilioToken);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($httpCode === 201) {
                return [
                    'status' => 'sent',
                    'error' => null,
                    'metadata' => [
                        'message_sid' => $result['sid'] ?? null,
                        'status' => $result['status'] ?? null,
                        'price' => $result['price'] ?? null
                    ]
                ];
            } else {
                return [
                    'status' => 'failed',
                    'error' => $result['message'] ?? 'Unknown error',
                    'metadata' => null
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'metadata' => null
            ];
        }
    }

    /**
     * Check if Twilio is configured
     */
    private function isConfigured()
    {
        return !empty($this->twilioSid) && !empty($this->twilioToken) && !empty($this->twilioFrom);
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add + if not present
        if (!str_starts_with($phone, '+')) {
            // Assume French number if 10 digits and starts with 0
            if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
                $phone = '+33' . substr($phone, 1);
            } else {
                $phone = '+' . $phone;
            }
        }

        return $phone;
    }
}
