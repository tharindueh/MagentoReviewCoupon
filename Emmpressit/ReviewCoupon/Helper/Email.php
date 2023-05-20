<?php
namespace Emmpressit\ReviewCoupon\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Email
 * @package Emmpressit\ReviewCoupon\Helper
 *
 * Helper to Send Email
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Email constructor.
     * @param Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Function to send Email
     *
     * @param string $couponCode
     * @param int $customerId
     * @param int $emailTemplateId
     * @return void
     */
    public function sendEmail($couponCode, $customerId, $emailTemplateId)
    {
        try {
            $store = $this->storeManager->getStore();

            $storeName     = $store->getName();
            $storeEmail    = $store->getConfig('trans_email/ident_general/email');

            $customer = $this->customerRepository->getById($customerId);
            $customerEmail = $customer->getEmail();

            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($storeName),
                'email' => $this->escaper->escapeHtml($storeEmail),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $store->getStoreId(),
                    ]
                )
                ->setTemplateVars([
                    'templateVar'  => $couponCode,
                ])
                ->setFrom($sender)
                ->addTo($customerEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
