<?php

namespace Emmpressit\ReviewCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Plumrocket\Token\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class DuplicateReviewsSubmission
 *
 * Observer used to prevent customers from submitting duplicate reviews
 */
class DuplicateReviewsSubmission implements ObserverInterface
{

    /**
     * @var CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
  
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * DuplicateReviewsSubmission constructor.
     *
     * @param CollectionFactory $reviewCollectionFactory
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param CustomerRepositoryInterface $tokenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CollectionFactory $reviewCollectionFactory,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        CustomerRepositoryInterface $tokenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->tokenRepository = $tokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Execute Observer to prevent submitting duplicate reviews
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnabled = $this->_scopeConfig->getValue('reviewcoupon/multiplereviews/multiplereviews', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $review = $observer->getEvent()->getObject();
        if (! $review->getCustomerId() || ! $review->getProduct()) {
            return;
        }

        if (is_numeric($review->getProduct())) {
            try {
                $product = $this->productRepository->getById($review->getProduct());
            } catch (NoSuchEntityException $e) {
                return;
            }
        } else {
            $product = $review->getProduct();
        }

        $collection = $this->reviewCollectionFactory->create();

        $collection->addFieldToFilter('entity_pk_value', ['eq' => $product->getId()]);
        $collection->addFieldToFilter('customer_id', ['eq' => $review->getCustomerId()]);
        $collection->addStoreFilter($review->getData('store_id'));

        $countNm = $collection->count();

        if ($collection->count() && $isEnabled) {

            $last = $collection->getLastItem();

            $approvedReviewFlag = false;

            foreach ($collection as $review) {
                if ($review->getStatusId() == \Magento\Review\Model\Review::STATUS_APPROVED) {
                    $approvedReviewFlag = true;
                    continue;
                } elseif ($last->getId() == $review->getId() && ! $approvedReviewFlag) {
                    break;
                } else {
                    $review->setStatusId(\Magento\Review\Model\Review::STATUS_NOT_APPROVED);
                    try {
                        $review->save()->aggregate();
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e);
                    }
                }
            }
            
            
            //$tokenHash = $this->request->getParam('token');
            
            //$this->messageManager->addNotice(__("token: $tokenHash"));

            /*$this->messageManager->addNotice(__("ready to delete"));

            $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('token_hash', $tokenHash, 'eq')
            ->create();

            $tokenSearchResults = $this->tokenRepository->getList($searchCriteria);

            foreach ($tokenSearchResults->getItems() as $token) {
                $this->tokenRepository->delete($token);
            }

            $this->messageManager->addNotice(__("deleted")); */
        }
    }
}
