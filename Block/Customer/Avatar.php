<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Block\Form\Edit;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Sinfonie\Avatar\Helper\Avatar as AvatarHelper;
use Magento\Customer\Model\Session;

class Avatar extends Edit
{
    /**
     * @var AvatarHelper
     */
    private $avatarHelper;

    /**
     * Block constructor
     *
     * @param AvatarHelper $avatarHelper
     * @param Context $context
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     */
    public function __construct(
        AvatarHelper $avatarHelper,
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->avatarHelper = $avatarHelper;
    }

    /**
     * Returns customer avatar link
     *
     * @return string
     */
    public function getAvatarLink(): string
    {
        $customerId = (int) $this->getCustomer()->getId();
        return $this->avatarHelper->getFrontAvatarUrl($customerId);
    }
}
