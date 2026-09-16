<?php

namespace Jose\AiVisibility\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\ScopeInterface;
use Jose\AiVisibility\Model\StructuredData\ProductSchema;
use Jose\AiVisibility\Model\StructuredData\OrganizationSchema;
use Jose\AiVisibility\Model\StructuredData\BreadcrumbSchema;

class StructuredData extends Template
{
    private Registry $registry;
    private ProductSchema $productSchema;
    private OrganizationSchema $orgSchema;
    private BreadcrumbSchema $breadcrumbSchema;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        ProductSchema $productSchema,
        OrganizationSchema $orgSchema,
        BreadcrumbSchema $breadcrumbSchema,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->productSchema = $productSchema;
        $this->orgSchema = $orgSchema;
        $this->breadcrumbSchema = $breadcrumbSchema;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getGraph(): array
    {
        $graph = [];

        // Organization (all pages)
        if ($this->isEnabled('enable_organization')) {
            $graph[] = $this->orgSchema->build();
        }

        // Breadcrumb (all pages)
        if ($this->isEnabled('enable_breadcrumb')) {
            $graph[] = $this->breadcrumbSchema->build();
        }

        // Product (PDP only)
        $product = $this->registry->registry('current_product');
        if ($product instanceof Product && $this->isEnabled('enable_product')) {
            $graph[] = $this->productSchema->build(
                $product,
                $this->_storeManager->getStore()->getCurrentCurrencyCode()
            );
        }

        return $graph;
    }

    /**
     * @return string
     */
    public function getJsonLd(): string
    {
        $graph = $this->getGraph();
        if (empty($graph)) {
            return '';
        }

        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@graph'   => $graph,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isEnabled(string $key): bool
    {
        return $this->scopeConfig->isSetFlag(
            "ai_visibility/structured_data/{$key}",
            ScopeInterface::SCOPE_STORE
        );
    }
}
