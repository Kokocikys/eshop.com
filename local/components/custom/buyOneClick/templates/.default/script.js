$(document).ready(function () {
    $(".OneClickSubmit").on('click', function () {
        $(".OneClickButton").hide();
        $(".OneClickInput").show();
        $(".FormSubmit").on('click', function () {
            let func;
            if (document.location.pathname == "/personal/cart/") {
                func = "buyOneClickBasket";
            } else {
                func = "buyOneClick";
            }
            let request = BX.ajax.runComponentAction('custom:buyOneClick',
                func, {
                    mode: 'class',
                    data: {
                        phoneNumber: $(".OneClickPhone").val(),
                        productID: $(".FormSubmit").attr("data-id-element"),
                    },
                });
            request.then(function (response) {
                if (response.status === "success" && response.data.errors.length == 0) {
                    $(".OneClickInput").hide();
                    $(".OneClickMessage").text("Заказ принят!").show();
                } else {
                    $(".OneClickInput").hide();
                    $(".OneClickMessage").text(response.data.errors).show();
                }
            });
        })
    })
});