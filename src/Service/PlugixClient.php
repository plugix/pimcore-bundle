<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class PlugixClient
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiKey;
    private string $apiUrl;
    private string $platform;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $apiKey,
        string $apiUrl,
        string $platform
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->platform = $platform;
    }

    /**
     * Generate AI product descriptions
     */
    public function generateDescriptions(array $products, array $options = []): array
    {
        return $this->request('POST', '/v1/ecommerce/descriptions', [
            'products' => $products,
            'tone' => $options['tone'] ?? 'professional',
            'languages' => $options['languages'] ?? ['en'],
            'maxLength' => $options['maxLength'] ?? 500,
        ]);
    }

    /**
     * Translate content
     */
    public function translate(array $content, string $targetLanguage, array $options = []): array
    {
        return $this->request('POST', '/v1/ecommerce/translate', [
            'content' => $content,
            'targetLanguage' => $targetLanguage,
            'preserveTone' => $options['preserveTone'] ?? true,
            'context' => $options['context'] ?? 'ecommerce',
        ]);
    }

    /**
     * Generate SEO metadata
     */
    public function generateSeo(array $products, array $options = []): array
    {
        return $this->request('POST', '/v1/ecommerce/seo', [
            'products' => $products,
            'generateMetaTitle' => $options['metaTitle'] ?? true,
            'generateMetaDescription' => $options['metaDescription'] ?? true,
            'generateKeywords' => $options['keywords'] ?? true,
        ]);
    }

    /**
     * Chat with AI about products
     */
    public function chat(string $message, array $context = []): array
    {
        return $this->request('POST', '/v1/chat/message', [
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Get API health status
     */
    public function health(): array
    {
        return $this->request('GET', '/v1/health');
    }

    /**
     * Get usage statistics
     */
    public function getUsage(): array
    {
        return $this->request('GET', '/v1/usage');
    }

    /**
     * Make an API request
     */
    private function request(string $method, string $endpoint, array $body = []): array
    {
        $url = $this->apiUrl . $endpoint;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'X-Platform' => $this->platform,
            ],
        ];

        if ($method !== 'GET' && !empty($body)) {
            $options['json'] = $body;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $data = $response->toArray();

            if (!($data['success'] ?? false)) {
                throw new \RuntimeException($data['error']['message'] ?? 'Unknown error');
            }

            return $data['data'] ?? [];
        } catch (\Throwable $e) {
            $this->logger->error('Plugix API error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'method' => $method,
            ]);
            throw $e;
        }
    }
}
