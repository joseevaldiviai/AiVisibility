<?php

namespace Jose\AiVisibility\Model\StructuredData;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Store\Model\ScopeInterface;

class ProductSchema
{
    private ScopeConfigInterface $scopeConfig;
    private ImageHelper $imageHelper;
    private ReviewCollectionFactory $reviewCollectionFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ImageHelper $imageHelper,
        ReviewCollectionFactory $reviewCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->imageHelper = $imageHelper;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
    }

    /**
     * @param Product $product
     * @param string $currencyCode
     * @return array
     */
    public function build(Product $product, string $currencyCode): array
    {
        $schema = [
            '@type'       => 'Product',
            'name'        => $product->getName(),
            'sku'         => $product->getSku(),
            'image'       => $this->getImageUrl($product),
            'description' => $this->getDescription($product),
            'offers'      => [
                '@type'         => 'Offer',
                'url'           => $product->getProductUrl(),
                'price'         => (string) $product->getFinalPrice(),
                'priceCurrency' => $currencyCode,
                'availability'  => $product->isAvailable()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => $this->getCondition(),
            ],
        ];

        // Brand (manufacturer attribute, if populated)
        if ($brand = $product->getAttributeText('manufacturer')) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => (string) $brand];
        }

        // GTIN (custom attribute, if present)
        if ($gtin = $product->getData('gtin')) {
            $schema['gtin'] = $gtin;
        }

        // AggregateRating (from approved reviews)
        $rating = $this->getAggregateRating($product);
        if ($rating !== null) {
            $schema['aggregateRating'] = $rating;
        }

        return $schema;
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getImageUrl(Product $product): string
    {
        return $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
    }

    /**
     * @param Product $product
     * @return string|null
     */
    private function getDescription(Product $product): ?string
    {
        $desc = $product->getShortDescription() ?: $product->getDescription();

        return $desc ? trim(strip_tags((string) $desc)) : null;
    }

    /**
     * @return string
     */
    private function getCondition(): string
    {
        return (string) $this->scopeConfig->getValue(
            'ai_visibility/structured_data/default_condition',
            ScopeInterface::SCOPE_STORE
        ) ?: 'https://schema.org/NewCondition';
    }

    /**
     * Build an AggregateRating node from approved product reviews.
     *
     * @param Product $product
     * @return array|null
     */
    private function getAggregateRating(Product $product): ?array
    {
        if (!$this->scopeConfig->isSetFlag(
            'ai_visibility/structured_data/enable_reviews',
            ScopeInterface::SCOPE_STORE
        )) {
            return null;
        }

        $reviews = $this->reviewCollectionFactory->create()
            ->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $product->getId())
            ->addStatusFilter(Review::STATUS_APPROVED);

        $reviewCount = (int) $reviews->getSize();
        if ($reviewCount === 0) {
            return null;
        }

        $reviews->addRateVotes();

        $totalPercent = 0;
        $voteCount = 0;
        foreach ($reviews as $review) {
            foreach ($review->getRateVotes() as $vote) {
                $totalPercent += (int) $vote->getPercent();
                $voteCount++;
            }
        }

        if ($voteCount === 0) {
            return null;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => round(($totalPercent / $voteCount) / 20, 1),
            'reviewCount' => $reviewCount,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }
}
