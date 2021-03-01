<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Sale;

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

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

    public function buyOneClickAction($phoneNumber,$productID)
    {
        $errors = array();

        if (empty($phoneNumber)) {
            array_push($errors, "Вы не ввели номер");
        }
        if (self::validateNumber($phoneNumber) != true) {
            array_push($errors, "Номер введен некоректно");
        }

        array_push($errors, "$productID");

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
        $deliveryID = 1;
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
            Sale\PaySystem\Manager::getObjectById(1)
        );

        $payment->setField("SUM", $order->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());

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