<?php

declare(strict_types=1);

namespace Sinfonie\Avatar\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;

class CustomerAvatar implements DataPatchInterface
{
    private const ATTRIBUTE_CODE = 'avatar';

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * Avatar attribute constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ModuleDataSetupInterface $setup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ModuleDataSetupInterface $setup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerSetup->getDefaultAttributeSetId($customerEntity->getEntityTypeId());
        $attributeGroup = $customerSetup->getDefaultAttributeGroupId(
            $customerEntity->getEntityTypeId(),
            $attributeSetId
        );
        $customerSetup->addAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE, [
            'type' => 'varchar',
            'input' => 'file',
            'label' => 'Avatar',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'system' => false,
            'is_visible_in_grid' => true,
            'is_used_in_grid' => true,
            'position' => 100
        ]);

        $attribute = $this->attributeRepository->get(Customer::ENTITY, self::ATTRIBUTE_CODE);
        $attribute->addData([
            'used_in_forms' => [
                'adminhtml_checkout',
                'adminhtml_customer',
                'customer_account_edit',
                'customer_account_create'
            ],
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroup
        ]);
        $this->attributeRepository->save($attribute);
        $this->setup->endSetup();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
