<?php

namespace Addon\Controllers;

use App\Core\Http\JsonResponse;
use App\Core\Http\Request;
use App\Core\Http\Response;

class ApiController
{
  // API Configuration
  private string $apiUrl;
  private string $apiKey;

  public function __construct()
  {
    $isProduction = isProduction();
    $this->apiUrl = $isProduction ? 'https://siakad.univeral.ac.id/api/ruangan' : 'http://localhost:8888/siakad-univeral/api/ruangan';
    $this->apiKey = 'c5a9e9f03b7d4a12e8f60b3c9d4a7e2f1b2c3d4e5f60718293a4b5c6d7e8f901';
  }

  /**
   * Generic API fetch function
   * @param string $endpoint - API endpoint (default: ruangan)
   * @param array $params - Query parameters
   * @param array $headers - Additional headers
   * @return array - API response data
   */
  public function getRuanganApi(array $params = [], array $headers = []): array
  {
    try {
      // Build URL with parameters
      $endpoint = $params['endpoint'] ?? 'ruangan';
      $baseUrl = str_replace('/ruangan', '', $this->apiUrl);
      $fullUrl = $baseUrl . '/' . $endpoint;

      // Add query parameters
      if (!empty($params)) {
        $queryParams = [];
        foreach ($params as $key => $value) {
          if ($key !== 'endpoint') {
            $queryParams[] = $key . '=' . urlencode($value);
          }
        }
        if (!empty($queryParams)) {
          $fullUrl .= '?' . implode('&', $queryParams);
        }
      }

      // Setup cURL
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $fullUrl);

      // Default headers + custom headers
      $defaultHeaders = [
        'X-API-KEY: ' . $this->apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
      ];
      $allHeaders = array_merge($defaultHeaders, $headers);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

      // cURL options
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

      // Execute request
      $result = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $error = curl_error($ch);
      curl_close($ch);

      // Handle errors
      if ($error) {
        throw new \Exception('cURL Error: ' . $error);
      }

      if ($httpCode !== 200) {
        throw new \Exception('HTTP Error: ' . $httpCode . ' - ' . $result);
      }

      // Parse response
      $data = json_decode($result, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('JSON Parse Error: ' . json_last_error_msg());
      }

      return [
        'success' => true,
        'data' => $data,
        'http_code' => $httpCode
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'error' => $e->getMessage(),
        'http_code' => $httpCode ?? 0
      ];
    }
  }

  /**
   * Get Ruangan dari API SIAKAD
   */
  public function getRuangan(Request $request, Response $response): JsonResponse
  {
    // Prepare parameters
    $params = [
      'perPage' => $request->get('perPage', 100)
    ];

    // Call generic API function
    $apiResult = $this->getRuanganApi($params);

    if (!$apiResult['success']) {
      return $response->json([
        'status' => 'error',
        'message' => $apiResult['error']
      ], $apiResult['http_code'] ?: 500);
    }

    // Extract data from API response
    $apiData = $apiResult['data'];

    return $response->json([
      'status' => 'success',
      'message' => 'Data ruangan berhasil diambil',
      'total' => count($apiData['data'] ?? []),
      'data' => $apiData['data'] ?? []
    ]);
  }
}
