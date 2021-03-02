<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="OneClick">
    <div class="OneClickButton">
        <button class="OneClickSubmit">Купить в один клик</button>
    </div>
    <div class="OneClickInput">
        <lable>Введите ваш номер телефона:</lable>
        <br>
        <input class="OneClickPhone">
        <button class="FormSubmit" data-id-element="<?=$arParams["PRODUCT_ID"]?>">Заказать</button>
    </div>
    <div class="OneClickMessage"></div>
</div>
