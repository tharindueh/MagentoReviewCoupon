<?php
namespace Emmpressit\ReviewCoupon\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Review\Model\Review;
use Emmpressit\ReviewCoupon\Helper\Email;
use Emmpressit\ReviewCoupon\Helper\Coupon;

/**
 * ReviewSaveAfterObserver
 *
 * Observer class to send coupon code after product review is approved
 */
class ReviewSaveAfterObserver implements ObserverInterface
{
    /**
     * @var Email
     */
    private $helperEmail;

    /**
     * @var Coupon
     */
    private $helperCoupon;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * ReviewSaveAfterObserver constructor.
     *
     * @param Email $helperEmail
     * @param Coupon $helperCoupon
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Email $helperEmail,
        Coupon $helperCoupon,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helperEmail = $helperEmail;
        $this->helperCoupon = $helperCoupon;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Send coupon code after product review is approved
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $dataObject = $observer->getEvent()->getDataObject();
        $isEnabled = $this->_scopeConfig->getValue('reviewcoupon/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $ruleId = $this->_scopeConfig->getValue('reviewcoupon/rule_id/rule_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailTemplateId = $this->_scopeConfig->getValue('reviewcoupon/emailtemplate/emailtemplate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($isEnabled && $dataObject->getCustomerId() && $dataObject->getStatusId() == Review::STATUS_APPROVED) {
            
            $customerId = $dataObject->getCustomerId();

            $couponCode = $this->helperCoupon->couponGenerate($ruleId);

            $this->helperEmail->sendEmail($couponCode, $customerId, $emailTemplateId);
            
        }
    }
}
