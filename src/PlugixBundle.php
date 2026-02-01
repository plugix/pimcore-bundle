<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class PlugixBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getNiceName(): string
    {
        return 'Plugix AI Integration';
    }

    public function getDescription(): string
    {
        return 'AI-powered product descriptions, translations, and SEO optimization for Pimcore';
    }

    protected function getComposerPackageName(): string
    {
        return 'plugix/pimcore-bundle';
    }
}
