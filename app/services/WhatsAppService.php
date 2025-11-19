<?php

/**
 * WhatsApp Service
 * Handles WhatsApp messages via Twilio WhatsApp Business API
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class WhatsAppService
{
    private $twilioSid;
    private $twilioToken;
    private $whatsappFrom;

    public function __construct()
    {
        $this->twilioSid = defined('TWILIO_SID') ? TWILIO_SID : '';
        $this->twilioToken = defined('TWILIO_TOKEN') ? TWILIO_TOKEN : '';
        $this->whatsappFrom = defined('WHATSAPP_FROM') ? WHATSAPP_FROM : '';
    }

    /**
     * Send WhatsApp message
     *
     * @param array $data
     * @return array
     */
    public function send($data)
    {
        if (!$this->isConfigured()) {
            return [
                'status' => 'failed',
                'error' => 'WhatsApp/Twilio not configured',
                'metadata' => null
            ];
        }

        try {
            $to = $this->formatWhatsAppNumber($data['to']);

            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json";

            $postData = [
                'From' => 'whatsapp:' . $this->whatsappFrom,
                'To' => 'whatsapp:' . $to,
                'Body' => $data['message']
            ];

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
                        'status' => $result['status'] ?? null
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
     * Check if configured
     */
    private function isConfigured()
    {
        return !empty($this->twilioSid) && !empty($this->twilioToken) && !empty($this->whatsappFrom);
    }

    /**
     * Format WhatsApp number
     */
    private function formatWhatsAppNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            $phone = '+33' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
