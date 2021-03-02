<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Sale;

class CBuyOneClick extends CBitrixComponent implements Controllerable
{
    public function configureActions()
    {
        return [
            'buyOneClick' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }

    public function buyOneClickAction($phoneNumber, $productID)
    {
        $errors = array();

        if ($phoneNumber == "") {
            array_push($errors, "Вы не ввели номер");
        }
        if (self::validateNumber($phoneNumber) != true) {
            array_push($errors, "Номер введен некоректно");
        }
        if ($productID == null) {
            array_push($errors, "Неверно настроен компонент");
        }

        $userID = Sale\Fuser::getId();
        $deliveryID = 2;
        $paymentID = 1;

        $basket = Bitrix\Sale\Basket::create(SITE_ID);

        $products = array(
            array('PRODUCT_ID' => $productID, 'QUANTITY' => 1, "PRODUCT_PROVIDER_CLASS" => "\Bitrix\Catalog\Product\CatalogProvider")
        );

        foreach ($products as $product) {
            $item = $basket->createItem("catalog", $product["PRODUCT_ID"]);
            unset($product["PRODUCT_ID"]);
            $item->setFields($product);
        }

        $order = Bitrix\Sale\Order::create(SITE_ID, $userID);
        $order->setPersonTypeId($userID);
        $order->setBasket($basket);

        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem(
            Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryID)
        );

        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        foreach ($basket as $basketItem) {
            $item = $shipmentItemCollection->createItem($basketItem);
            $item->setQuantity($basketItem->getQuantity());
        }

        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(
            Bitrix\Sale\PaySystem\Manager::getObjectById($paymentID)
        );

        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());

        $propertyCollection = $order->getPropertyCollection();
        $phoneProp = $propertyCollection->getPhone();
        $phoneProp->setValue($phoneNumber);

        $result = $order->save();

        if (!$result->isSuccess()) {
            array_push($errors, "Ошибка при оформлении заказа!");
        }

        return [
            'errors' => $errors,
        ];
    }

    public function buyOneClickBasketAction($phoneNumber)
    {
        $errors = array();

        if (empty($phoneNumber)) {
            array_push($errors, "Вы не ввели номер");
        }
        if (self::validateNumber($phoneNumber) != true) {
            array_push($errors, "Номер введен некоректно");
        }

        $userID = Sale\Fuser::getId();
        $deliveryID = 2;
        $paymentID = 1;

        $basket = Sale\Basket::loadItemsForFUser($userID, Bitrix\Main\Context::getCurrent()->getSite());

        $order = Sale\Order::create(SITE_ID, $userID);
        $order->setPersonTypeId($userID);
        $order->setBasket($basket);

        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem(
            Sale\Delivery\Services\Manager::getObjectById($deliveryID)
        );

        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        foreach ($basket as $basketItem) {
            $item = $shipmentItemCollection->createItem($basketItem);
            $item->setQuantity($basketItem->getQuantity());
        }

        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(
            Sale\PaySystem\Manager::getObjectById($paymentID)
        );

        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());

        $propertyCollection = $order->getPropertyCollection();
        $phoneProp = $propertyCollection->getPhone();
        $phoneProp->setValue($phoneNumber);

        $result = $order->save();

        if (!$result->isSuccess()) {
            array_push($errors, "Ошибка при оформлении заказа!");
        }

        return [
            'errors' => $errors,
        ];
    }

    public function validateNumber($num)
    {
        return (bool)preg_match('/^\+?([0-9]{1,3})-?([0-9]{2})-?([0-9]{7})$/', $num);
    }

    public function executeComponent()
    {
        $this->IncludeComponentTemplate();
    }
}