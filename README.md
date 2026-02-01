# Plugix Pimcore Bundle

AI-powered product descriptions, translations, and SEO optimization for Pimcore.

[![Latest Version](https://img.shields.io/packagist/v/plugix/pimcore-bundle.svg)](https://packagist.org/packages/plugix/pimcore-bundle)
[![License](https://img.shields.io/packagist/l/plugix/pimcore-bundle.svg)](https://packagist.org/packages/plugix/pimcore-bundle)

## Installation

```bash
composer require plugix/pimcore-bundle
```

Or use the interactive installer:

```bash
curl -fsSL "https://api.plugix.ai/v1/onboarding/installer/pimcore?apiKey=YOUR_API_KEY" | bash
```

## Configuration

Create `config/packages/plugix.yaml`:

```yaml
plugix:
  api_key: '%env(PLUGIX_API_KEY)%'
  api_url: 'https://api.plugix.ai'
  platform: 'pimcore'

  mcp:
    enabled: true
    auto_connect: true

  features:
    product_descriptions: true
    translations: true
    seo_optimization: true

  languages:
    - en
    - de
    - fr
```

Add your API key to `.env.local`:

```
PLUGIX_API_KEY=sk_live_xxxxx
```

## Usage

### Generate Product Descriptions

```php
use Plugix\PimcoreBundle\Service\PlugixClient;

class ProductController
{
    public function __construct(private PlugixClient $plugix) {}

    public function generateDescriptions()
    {
        $products = [
            ['id' => 1, 'name' => 'Product 1', 'attributes' => ['color' => 'red']],
        ];

        $descriptions = $this->plugix->generateDescriptions($products, [
            'tone' => 'luxury',
            'languages' => ['en', 'de'],
        ]);

        return $descriptions;
    }
}
```

### Translate Content

```php
$translations = $this->plugix->translate(
    ['Welcome to our store', 'High quality products'],
    'de',
    ['preserveTone' => true]
);
```

### Generate SEO Metadata

```php
$seo = $this->plugix->generateSeo($products, [
    'metaTitle' => true,
    'metaDescription' => true,
    'keywords' => true,
]);
```

### MCP Server

Start the MCP (Model Context Protocol) server for real-time AI integration:

```bash
bin/console plugix:mcp:start
```

Run as daemon:

```bash
bin/console plugix:mcp:start --daemon
```

## Available MCP Tools

- `get_products` - Fetch products from Pimcore
- `get_categories` - Fetch categories
- `save_descriptions` - Save AI-generated descriptions
- `save_translations` - Save translations
- `get_stats` - Get catalog statistics

## Requirements

- PHP 8.1+
- Pimcore 10.x or 11.x
- Plugix API key ([Get one free](https://plugix.ai))

## Support

- Documentation: https://docs.plugix.ai/integrations/pimcore
- Issues: https://github.com/plugix/pimcore-bundle/issues
- Email: support@plugix.ai

## License

MIT License. See [LICENSE](LICENSE) for details.
