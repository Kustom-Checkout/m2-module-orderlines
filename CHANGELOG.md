# Changelog

## 3.1.0 / 2026-06-26

### Breaking changes

* None

### Features / changes

* KUSTOM-93: Loosened up PHP version constraint to allow installing the package on Magento 2.4.9
  and PHP 8.5.

### Fixes

* None

## 3.0.15 / 2025-06-03

  * PPP-2089 Updated version because of version dependency updates

## 3.0.14 / 2025-05-21

  * PPP-2055 Compatibility with AC 2.4.8 and PHP 8.4

## 3.0.13 / 2025-04-23

* PPP-2060 Updated version because of new dependencies

## 3.0.12 / 2025-04-03

  * PPP-1995 Fixed negative shipping tax rate
  * PPP-2025 Fix rounded shipping fee during capture

## 3.0.11 / 2025-03-26

  * PPP-2026 Updated dependencies

## 3.0.10 / 2025-02-11

  * PPP-1772 Simplified calculations
  * PPP-1775 Added Integration tests for dynamic bundled products and optimized calculations
  * PPP-1974 Fixed null error at \Klarna\Orderlines\Model\Calculator\Shipping::getTaxRate

## 3.0.9 / 2025-01-22

  * PPP-1849 Refactored \Klarna\Orderlines\Model\Calculator\ItemShippingAttributes
  * PPP-1859 Simplified unit tests by using a helper which includes the mocking logic.

## 3.0.8 / 2025-01-14

  * PPP-1957 Fixed missing product issue

## 3.0.7 / 2024-12-03

  * PPP-1858 Splitted Integration and API tests in tests for the default and website level

## 3.0.6 / 2024-11-05

  * PPP-29 Create missing unit tests for \Klarna\Orderlines\Model\Fpt
  * PPP-1845 Fixed calculation issue on the product weight for the item shipping attributes

## 3.0.5 / 2024-10-18

  * PPP-1714 Simplify composer.json files
  * PPP-1737 Fixed wrong tax calculations for bundled products

## 3.0.4 / 2024-09-26

  * PPP-1708 Updated the version because of new version dependencies

## 3.0.3 / 2024-08-21

* PPP-910 Refactored Item orderline item class
* PPP-1616 Added first API integration test
* PPP-1624 Changed the tax rate calculation for shipping

## 3.0.2 / 2024-08-12

  * PPP-1604 Updated the version because of new versions of the dependencies

## 3.0.1 / 2024-07-26

  * PPP-1553 Make the extension compatible with Adobe Commerce app assurance program requirements

## 3.0.0 / 2024-06-20

* PPP-1437 Updated the admin UX and changed internally the API credentials handling

## 2.0.23 / 2024-07-03

  * PPP-1556 Fix getTaxRate error when the ShippingAddress is null

## 2.0.22 / 2024-05-30

  * PPP-1494 PPP-1385 Increased version because of new Klarna dependencies

## 2.0.21 / 2024-04-24

  * PPP-1391 Added support for Adobe Commerce 2.4.7 and PHP 8.3

## 2.0.20 / 2024-04-11

  * PPP-1385 Increased version because of new Klarna dependencies

## 2.0.19 / 2024-03-30

  * PPP-31 Wrote unit tests for the classes in the namespace Klarna\Orderlines\Model\Items\Surcharge
  * PPP-33 Wrote unit tests for the classes in the namespace Klarna\Orderlines\Model\Items\Reward
  * PPP-1013 Using instead of \Klarna\Base\Helper\ConfigHelper logic from other classes to get back Klarna specific configuration values.

## 2.0.18 / 2024-03-15

  * PPP-1324 Fixed division by zero error

## 2.0.17 / 2024-03-04

  * PPP-28 Added missing tests for Klarna\Orderlines\Test\Unit\Model\Items\Customerbalance
  * PPP-32 Added missing unit tests for the namespace Klarna\Orderlines\Test\Unit\Model\Items\Tax
  * PPP-1130 Fixed calculations when using dynamic shipping tax rates
  * PPP-1298 Increased the version because of dependency updates

## 2.0.15 / 2024-02-01

  * PPP-30 Added unit tests for \Klarna\Orderlines\Model\ProductTypeChecker

## 2.0.14 / 2024-01-19

  * PPP-1059 Increased version because of a dependency version change

## 2.0.13 / 2024-01-19

  * PPP-897 Simplified the calculations ford the order line item shipping post purchase.
  * PPP-913 Resetting orderline item list before fetching them
  * PPP-1025 Shipping method title in the orderline items between the order creation and capture is now having the same value

## 2.0.12 / 2024-01-05

  * PPP-648 Extended qty logging of an item
  * PPP-1023 Added set methods for the calculation classes and simplified the setting for the calculation result.

## 2.0.11 / 2023-11-15

  * PPP-929 Increased the version because of a new version of the Base module

## 2.0.10 / 2023-09-27

  * PPP-704 Fixed the calculation when the tax is calculated before the discount is applied.

## 2.0.9 / 2023-08-25

  * PPP-186 Fixed calculation issue when using a coupon applied to shipping and prices are calculated excluding taxes outside of the US
  * PPP-313 Fixed gift cards were not part of the oderline items
  * PPP-607 Removed the rounding of the item quantity

## 2.0.8 / 2023-08-01

  * MAGE-4283 Changed the way how to check if a item is a virtual item

## 2.0.7 / 2023-07-14

  * MAGE-4228 Removed the composer caret version range for Klarna dependencies
  * MAGE-4291 Fix shipping cost value to match to the Klarna target value on capture requests

## 2.0.6 / 2023-05-24

  * MAGE-4224 Fix PHP 7.4 compatibility issue

## 2.0.5 / 2023-05-22

  * MAGE-4232 Increased the version because of new dependency versions in the composer.json file

## 2.0.4 / 2023-04-21

  * MAGE-4198 Simplified the calculations for the shipping item

## 2.0.3 / 2023-04-03

  * MAGE-4172 Fixed type error at \Klarna\Orderlines\Model\Items\Shipping\PrePurchaseCalculator::calculateSeparateTaxLineData()
  * MAGE-4173 Fix the bundle products, child products total price calculation issue

## 2.0.2 / 2023-03-28

  * MAGE-4162 Added support for PHP 8.2

## 2.0.1 / 2023-03-28

  * MAGE-4118 Fix configurable product image thumbnail usage when sending it through the Klarna API

## 2.0.0 / 2023-03-09

  * MAGE-76 Refactored Model Base/Model/Fpt and moved the logic to new locations and adjusted the calls.
  * MAGE-4062 Removed deprecated methods
  * MAGE-4066 Removed the Objectmanager workaround for public API class contructors
  * MAGE-4073 Moved the input of the orderline items to the class Klarna\Base\Model\Api\Parameter
  * MAGE-4077 Added "declare(strict_types=1);" to all production class files
  * MAGE-4079 Refactored most of the orderline item classes
  * MAGE-4086 Simplified logic when checkingif a sales rule with the rule "apply to shipping" is used
  * MAGE-4087 Moved \Klarna\Base\Model\Api\Parameter to the orderline module and adjusted the calls
  * MAGE-4092 Move the DataHolder class from the Base module to the Orderlines module

## 1.0.11 / 2022-10-24

  * MAGE-4060 Fixed case when a unavailable product was added to the cart

## 1.0.10 / 2022-09-27

  * MAGE-4000 Changed the class in di.xml for generating the orderline item classes

## 1.0.9 / 2022-09-14

  * MAGE-3986 Updated the dependencies

## 1.0.8 / 2022-09-01

  * MAGE-3712 Using constancts instead of magic numbers

## 1.0.7 / 2022-08-18

  * MAGE-3961 Updated the dependencies

## 1.0.6 / 2022-08-12

  * MAGE-3838 Changed the position of the menu item on the admin payment page
  * MAGE-3876 Reordered translations and set of missing translations
  * MAGE-3910 Updated the copyright text
  * MAGE-3952 Do not send the variation of a configurable product through the API

## 1.0.5 / 2022-07-11

  * MAGE-3620 Moved the logic of the orderline item calculation result to a central class

## 1.0.4 / 2022-05-31

  * MAGE-3855 Bump version because of updated dependencies

## 1.0.3 / 2022-06-13

  * MAGE-3785 Fix PHP requirements so that it matches the PHP requirement from Magento 2.4.4

## 1.0.2 / 2022-05-31

  * MAGE-3851 Fix partial capture

## 1.0.1 / 2022-05-09

  * MAGE-3708 Updated the requirements

## 1.0.0 / 2022-03-01

  * Initial Commit
