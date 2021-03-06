<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AbstractAssertTaxWithCrossBorderApplying
 * Abstract class for implementing assert cross border applying
 */
abstract class AbstractAssertTaxWithCrossBorderApplying extends AbstractConstraint
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Catalog product page
     *
     * @var catalogCategoryView
     */
    protected $catalogCategoryView;

    /**
     * Catalog product page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Catalog product page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Implementation assert
     *
     * @param array $actualPrices
     * @return void
     */
    abstract protected function assert($actualPrices);

    /**
     * 1. Login with each customer and get product price on category, product and cart pages
     * 2. Implementation assert
     *
     * @param CatalogProductSimple $product
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param array $customers
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        array $customers
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogCategoryView = $catalogCategoryView;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $actualPrices = $this->getPricesForCustomers($product, $customers);
        $this->assert($actualPrices);
    }

    /**
     * Login with each provided customer and get product prices
     *
     * @param CatalogProductSimple $product
     * @param array $customers
     * @return array
     */
    protected function getPricesForCustomers(CatalogProductSimple $product, $customers)
    {
        $prices = [];
        foreach ($customers as $customer) {
            $this->loginCustomer($customer);
            $productName = $product->getName();
            $this->openCategory($product);
            $actualPrices = [];
            $actualPrices = $this->getCategoryPrice($productName, $actualPrices);
            $this->catalogCategoryView->getListProductBlock()->openProductViewPage($productName);
            $actualPrices = $this->addToCart($product, $actualPrices);
            $actualPrices = $this->getCartPrice($product, $actualPrices);
            $prices[] = $actualPrices;
            $this->clearShoppingCart();
        }
        return $prices;
    }

    /**
     * Open product category
     *
     * @param CatalogProductSimple $product
     * @return void
     */
    protected function openCategory(CatalogProductSimple $product)
    {
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($product->getCategoryIds()[0]);
    }

    /**
     * Get prices on category page
     *
     * @param string $productName
     * @param array $actualPrices
     * @return array
     */
    protected function getCategoryPrice($productName, $actualPrices)
    {
        $actualPrices['category_price_incl_tax'] =
            $this->catalogCategoryView
                ->getListProductBlock()
                ->getProductPriceBlock($productName)
                ->getEffectivePrice();
        return $actualPrices;
    }

    /**
     * Fill options get price and add to cart
     *
     * @param CatalogProductSimple $product
     * @param array $actualPrices
     * @return array
     */
    protected function addToCart(CatalogProductSimple $product, $actualPrices)
    {
        $this->catalogProductView->getViewBlock()->fillOptions($product);
        $actualPrices['product_page_price'] =
            $this->catalogProductView->getViewBlock()->getPriceBlock()->getEffectivePrice();
        $this->catalogProductView->getViewBlock()->clickAddToCart();
        return $actualPrices;
    }

    /**
     * Get cart prices
     *
     * @param CatalogProductSimple $product
     * @param array $actualPrices
     * @return array
     */
    protected function getCartPrice(CatalogProductSimple $product, $actualPrices)
    {
        $actualPrices['cart_item_price_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getPriceInclTax();
        $actualPrices['cart_item_subtotal_incl_tax'] =
            $this->checkoutCart->getCartBlock()->getCartItem($product)->getSubtotalPriceInclTax();
        $actualPrices['grand_total'] =
            $this->checkoutCart->getTotalsBlock()->getGrandTotal();
        return $actualPrices;
    }

    /**
     * Login customer
     *
     * @param $customer
     * @return void
     */
    protected function loginCustomer($customer)
    {
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }

    /**
     * Clear shopping cart
     *
     * @return void
     */
    protected function clearShoppingCart()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->clearShoppingCart();
    }
}
