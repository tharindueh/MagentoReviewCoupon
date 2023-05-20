<?php

namespace Emmpressit\ReviewCoupon\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class EmailTemplate
 *
 * Provides Email Template options for config settings
 *
 * @package Emmpressit\ReviewCoupon\Model\Config\Source
 */
class EmailTemplate implements OptionSourceInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Email\Template
     */
    protected $emailTemplate;

    /**
     * EmailTemplate constructor.
     *
     * @param \Magento\Config\Model\Config\Source\Email\Template $emailTemplate
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Email\Template $emailTemplate
    ) {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->emailTemplate->toOptionArray();
    }
}
