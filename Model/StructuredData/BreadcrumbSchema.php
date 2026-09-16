<?php

namespace Jose\AiVisibility\Model\StructuredData;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class BreadcrumbSchema
{
    private Registry $registry;
    private StoreManagerInterface $storeManager;
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Build a schema.org BreadcrumbList node: Home > ...categories... > Product.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(): array
    {
        $items   = [];
        $items[] = $this->makeItem(1, 'Home', $this->storeManager->getStore()->getBaseUrl());

        $position = 2;

        $category = $this->registry->registry('current_category');
        if ($category instanceof Category) {
            foreach ($this->getCategoryTrail($category) as $trailCategory) {
                $items[] = $this->makeItem(
                    $position++,
                    (string) $trailCategory->getName(),
                    $trailCategory->getUrl()
                );
            }
        }

        $product = $this->registry->registry('current_product');
        if ($product instanceof Product) {
            $items[] = $this->makeItem($position, (string) $product->getName(), $product->getProductUrl());
        }

        return [
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Return the active category trail (root category excluded) ending at $category.
     *
     * @param Category $category
     * @return Category[]
     */
    private function getCategoryTrail(Category $category): array
    {
        $trail = [];
        $rootId = (int) $this->storeManager->getStore()->getRootCategoryId();

        foreach (array_reverse($category->getPathIds()) as $pathId) {
            // Skip the tree root and the store's own root category
            if ((int) $pathId <= $rootId) {
                continue;
            }

            try {
                $trailCategory = $this->categoryRepository->get((int) $pathId, $this->storeManager->getStore()->getId());
            } catch (NoSuchEntityException $e) {
                continue;
            }

            if ($trailCategory->getIsActive()) {
                $trail[] = $trailCategory;
            }
        }

        return $trail;
    }

    /**
     * @param int $position
     * @param string $name
     * @param string|null $url
     * @return array
     */
    private function makeItem(int $position, string $name, ?string $url): array
    {
        $item = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => $name,
        ];

        if ($url) {
            $item['item'] = $url;
        }

        return $item;
    }
}
