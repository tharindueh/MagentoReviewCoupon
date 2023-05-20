<?php
namespace Emmpressit\ReviewCoupon\Model\Config\Source;

class RuleId implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * RuleId constructor.
     *
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     */
    public function __construct(\Magento\SalesRule\Model\RuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $ruleCollection = $this->ruleFactory->create()->getCollection();
        foreach ($ruleCollection as $rule) {
            $options[] = [
                'label' => $rule->getName(),
                'value' => $rule->getId()
            ];
        }
        return $options;
    }
}
