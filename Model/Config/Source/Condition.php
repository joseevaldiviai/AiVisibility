<?php

namespace Jose\AiVisibility\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Condition implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'https://schema.org/NewCondition', 'label' => __('New')],
            ['value' => 'https://schema.org/UsedCondition', 'label' => __('Used')],
            ['value' => 'https://schema.org/RefurbishedCondition', 'label' => __('Refurbished')],
            ['value' => 'https://schema.org/DamagedCondition', 'label' => __('Damaged')],
        ];
    }
}
