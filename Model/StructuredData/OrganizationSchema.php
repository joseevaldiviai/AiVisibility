<?php

namespace Jose\AiVisibility\Model\StructuredData;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class OrganizationSchema
{
    private StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Build the schema.org Organization node for the current store.
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function build(): array
    {
        $store = $this->storeManager->getStore();

        return [
            '@type' => 'Organization',
            'name'  => $store->getFrontendName(),
            'url'   => $store->getBaseUrl(UrlInterface::URL_TYPE_LINK),
        ];
    }
}
