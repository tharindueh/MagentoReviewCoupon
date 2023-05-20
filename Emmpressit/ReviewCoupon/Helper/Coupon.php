<?php
namespace Emmpressit\ReviewCoupon\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Class Coupon
 *
 * @package Emmpressit\ReviewCoupon\Helper
 */
class Coupon extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * RuleFactory instance
     *
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * Coupon constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Generates a coupon code
     *
     * @param int $ruleId
     * @return string
     */
    public function couponGenerate($ruleId)
    {
        //$ruleId = 5;
        $rule = $this->ruleFactory->create()->load($ruleId);
        $couponCode = $rule->getCouponCode();
        if ($rule->getUsesPerCoupon() > 0) {
            $couponCollection = $rule->getCoupons();
            foreach ($couponCollection as $coupon) {
                if ($coupon->getTimesUsed() == 0) {
                    $couponCode = $coupon->getCode();
                    $coupon->setTimesUsed(1);
                    $coupon->save();
                    return $couponCode;
                }
            }
        }
    }
}
