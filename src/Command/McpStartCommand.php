<?php

declare(strict_types=1);

namespace Plugix\PimcoreBundle\Command;

use Plugix\PimcoreBundle\Service\McpService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'plugix:mcp:start',
    description: 'Start the Plugix MCP (Model Context Protocol) server connection',
)]
class McpStartCommand extends Command
{
    public function __construct(
        private McpService $mcpService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Run as daemon in background')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Suppress deprecation warnings (PHP 8.4 compatibility issues in Pimcore core)
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $io = new SymfonyStyle($input, $output);

        $io->title('Plugix MCP Server');

        if (!$this->mcpService->isEnabled()) {
            $io->error('MCP is disabled in configuration. Enable it in config/packages/plugix.yaml');
            return Command::FAILURE;
        }

        $io->info('Connecting to Plugix API...');

        try {
            $this->mcpService->connect();
            $io->success('MCP server connected successfully!');

            if ($input->getOption('daemon')) {
                $io->note('Running in daemon mode. Press Ctrl+C to stop.');
                $this->mcpService->runLoop();
            } else {
                $io->info('Tools available: ' . implode(', ', $this->mcpService->getAvailableTools()));
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Failed to connect: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
