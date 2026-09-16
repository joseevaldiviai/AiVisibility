<?php

namespace Jose\AiVisibility\Controller\LlmsTxt;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Store\Model\ScopeInterface;
use Jose\AiVisibility\Model\LlmsTxtGenerator;

class Index implements HttpGetActionInterface
{
    private ResultFactory $resultFactory;
    private LlmsTxtGenerator $generator;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ResultFactory $resultFactory,
        LlmsTxtGenerator $generator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultFactory = $resultFactory;
        $this->generator = $generator;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Serve /ai/llms_txt as text/plain.
     *
     * @return Raw
     */
    public function execute(): Raw
    {
        /** @var Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if (!$this->scopeConfig->isSetFlag(
            'ai_visibility/llms_txt/enable',
            ScopeInterface::SCOPE_STORE
        )) {
            $result->setHttpResponseCode(404);
            return $result;
        }

        $result->setContents($this->generator->generate());
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $result->setHeader('Cache-Control', 'public, max-age=3600');

        return $result;
    }
}
