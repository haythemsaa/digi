<?php

use PHPUnit\Framework\TestCase;

/**
 * API Integration Test
 * Tests REST API endpoints
 */
class APITest extends TestCase
{
    private $baseUrl = 'http://localhost/api/v1';
    private $token;

    protected function setUp(): void
    {
        // Get auth token for testing
        $this->token = $this->getAuthToken();
    }

    private function getAuthToken()
    {
        $ch = curl_init($this->baseUrl . '/auth/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => 'test@pakiparc.com',
            'password' => 'test123'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['data']['token'] ?? null;
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $ch = curl_init($this->baseUrl . $endpoint);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'body' => json_decode($response, true),
            'code' => $httpCode
        ];
    }

    public function testAuthenticationRequired()
    {
        $ch = curl_init($this->baseUrl . '/vehicles');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(401, $httpCode);
    }

    public function testGetVehicles()
    {
        if (!$this->token) {
            $this->markTestSkipped('No auth token available');
        }

        $response = $this->makeRequest('GET', '/vehicles');

        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('pagination', $response['body']);
    }

    public function testGetAnalyticsDashboard()
    {
        if (!$this->token) {
            $this->markTestSkipped('No auth token available');
        }

        $response = $this->makeRequest('GET', '/analytics/dashboard?period=month');

        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
    }

    public function testGetCarbonFootprint()
    {
        if (!$this->token) {
            $this->markTestSkipped('No auth token available');
        }

        $response = $this->makeRequest('GET', '/carbon/footprint?period=month');

        $this->assertEquals(200, $response['code']);
        $this->assertArrayHasKey('data', $response['body']);
    }

    public function testRateLimiting()
    {
        if (!$this->token) {
            $this->markTestSkipped('No auth token available');
        }

        // Make multiple requests to trigger rate limit
        for ($i = 0; $i < 110; $i++) {
            $response = $this->makeRequest('GET', '/vehicles');
        }

        // Should get 429 Too Many Requests
        $this->assertEquals(429, $response['code']);
    }
}
