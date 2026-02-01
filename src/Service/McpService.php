<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle\Service;

use Psr\Log\LoggerInterface;

class McpService
{
    private PlugixClient $client;
    private LoggerInterface $logger;
    private bool $enabled;
    private bool $autoConnect;
    private int $reconnectInterval;
    private bool $connected = false;
    private array $tools = [];

    public function __construct(
        PlugixClient $client,
        LoggerInterface $logger,
        bool $enabled,
        bool $autoConnect,
        int $reconnectInterval
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->enabled = $enabled;
        $this->autoConnect = $autoConnect;
        $this->reconnectInterval = $reconnectInterval;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function connect(): void
    {
        if (!$this->enabled) {
            throw new \RuntimeException('MCP is disabled');
        }

        $this->logger->info('Connecting to Plugix MCP server...');

        // Verify API connection first
        $health = $this->client->health();

        if (($health['status'] ?? '') !== 'ok') {
            throw new \RuntimeException('API health check failed');
        }

        $this->connected = true;
        $this->tools = $this->getDefaultTools();

        $this->logger->info('MCP connected successfully', [
            'tools' => count($this->tools),
        ]);
    }

    public function disconnect(): void
    {
        $this->connected = false;
        $this->tools = [];
        $this->logger->info('MCP disconnected');
    }

    public function getAvailableTools(): array
    {
        return array_keys($this->tools);
    }

    public function executeTool(string $name, array $params = []): mixed
    {
        if (!$this->connected) {
            throw new \RuntimeException('MCP not connected');
        }

        if (!isset($this->tools[$name])) {
            throw new \RuntimeException("Tool not found: $name");
        }

        $this->logger->info("Executing MCP tool: $name", $params);

        return ($this->tools[$name])($params);
    }

    public function runLoop(): void
    {
        while ($this->connected) {
            // Keep connection alive, handle reconnection
            sleep(1);
        }
    }

    private function getDefaultTools(): array
    {
        return [
            'get_products' => fn(array $params) => $this->getProducts($params),
            'get_categories' => fn(array $params) => $this->getCategories($params),
            'save_descriptions' => fn(array $params) => $this->saveDescriptions($params),
            'save_translations' => fn(array $params) => $this->saveTranslations($params),
            'get_stats' => fn(array $params) => $this->getStats($params),
        ];
    }

    private function getProducts(array $params): array
    {
        $listing = new \Pimcore\Model\DataObject\Product\Listing();

        if (isset($params['category'])) {
            $listing->addConditionParam('category = ?', [$params['category']]);
        }

        if (isset($params['limit'])) {
            $listing->setLimit((int)$params['limit']);
        }

        $products = [];
        foreach ($listing as $product) {
            $products[] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
            ];
        }

        return $products;
    }

    private function getCategories(array $params): array
    {
        $listing = new \Pimcore\Model\DataObject\Category\Listing();

        $categories = [];
        foreach ($listing as $category) {
            $categories[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'path' => $category->getFullPath(),
            ];
        }

        return $categories;
    }

    private function saveDescriptions(array $params): array
    {
        $saved = 0;

        foreach ($params['items'] ?? [] as $item) {
            $product = \Pimcore\Model\DataObject\Product::getById($item['id']);
            if ($product) {
                $product->setDescription($item['description']);
                $product->save();
                $saved++;
            }
        }

        return ['saved' => $saved];
    }

    private function saveTranslations(array $params): array
    {
        $saved = 0;
        $language = $params['language'] ?? 'en';

        foreach ($params['items'] ?? [] as $item) {
            $product = \Pimcore\Model\DataObject\Product::getById($item['id']);
            if ($product) {
                $product->setName($item['name'], $language);
                $product->setDescription($item['description'], $language);
                $product->save();
                $saved++;
            }
        }

        return ['saved' => $saved, 'language' => $language];
    }

    private function getStats(array $params): array
    {
        $productListing = new \Pimcore\Model\DataObject\Product\Listing();

        return [
            'totalProducts' => $productListing->count(),
            'timestamp' => date('c'),
        ];
    }
}
