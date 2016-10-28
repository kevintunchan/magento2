<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetProductFinalPriceIfOneOfChildIsDisabled()
    {
        $product = $this->productRepository->get('configurable');
        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->get(CollectionFactory::class)
            ->create();
        $configurableProduct = $collection
            ->addIdFilter([$product->getId()])
            ->addMinimalPrice()
            ->load()
            ->getFirstItem();
        $this->assertEquals(10, $configurableProduct->getMinimalPrice());

        $childProduct = $this->productRepository->getById(10, false, null, true);
        $childProduct->setStatus(Status::STATUS_DISABLED);
        // update in global scope
        $currentStoreId = $this->storeManager->getStore()->getId();
        $this->storeManager->setCurrentStore(Store::ADMIN_CODE);
        $this->productRepository->save($childProduct);
        $this->storeManager->setCurrentStore($currentStoreId);

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->get(CollectionFactory::class)
            ->create();
        $configurableProduct = $collection
            ->addIdFilter([$product->getId()])
            ->addMinimalPrice()
            ->load()
            ->getFirstItem();
        $this->assertEquals(20, $configurableProduct->getMinimalPrice());
    }
}
