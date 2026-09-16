<?php

namespace Jose\AiVisibility\Cron;

use Psr\Log\LoggerInterface;
use Jose\AiVisibility\Model\LlmsTxtGenerator;

class GenerateLlmsTxt
{
    private LlmsTxtGenerator $generator;
    private LoggerInterface $logger;

    public function __construct(
        LlmsTxtGenerator $generator,
        LoggerInterface $logger
    ) {
        $this->generator = $generator;
        $this->logger = $logger;
    }

    /**
     * Regenerate the llms.txt content on schedule.
     *
     * The generated content is served dynamically by the controller; this
     * cron exists to warm/refresh any cached copy (e.g. Varnish/CDN) and to
     * keep generation logic exercised on schedule.
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $content = $this->generator->generate();
            $this->logger->info(
                'AI Visibility: llms.txt regenerated (' . strlen($content) . ' bytes)'
            );
        } catch (\Throwable $e) {
            $this->logger->error('AI Visibility llms.txt generation failed: ' . $e->getMessage());
        }
    }
}
