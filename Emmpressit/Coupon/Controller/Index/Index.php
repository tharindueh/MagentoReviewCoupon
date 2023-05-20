<?php 
namespace Emmpressit\Coupon\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\SalesRule\Model\RuleFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $ruleFactory;

    public function __construct(
        Context $context,
        RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        $ruleId = 5; // your rule id 
        $rule = $this->ruleFactory->create()->load($ruleId);
        $couponCode = $rule->getCouponCode();
        if ($rule->getUsesPerCoupon() > 0) {
            // check for unused coupon code
            $couponCollection = $rule->getCoupons();
            foreach ($couponCollection as $coupon) {
                if ($coupon->getTimesUsed() == 0) {
                    $couponCode = $coupon->getCode();
                    break;
                }
            }
        }
        echo $couponCode;
        exit;
    }
}