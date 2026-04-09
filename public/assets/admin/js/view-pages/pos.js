"use strict";

$("#order_place").on("keydown", function (e) {
    if (e.keyCode === 13) {
        e.preventDefault();
    }
});
$("#insertPayableAmount").on("keydown", function (e) {
    if (e.keyCode === 13) {
        e.preventDefault();
    }
});

$(document).on("click", ".print-Div", function () {
    let printContents = document.getElementById("printableArea").innerHTML;
    let originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
});

$(document).on("click", ".addon-quantity-input-toggle", function (event) {
    let cb = $(event.target);
    if (cb.is(":checked")) {
        cb.siblings(".addon-quantity-input").css({ visibility: "visible" });
    } else {
        cb.siblings(".addon-quantity-input").css({ visibility: "hidden" });
    }
});

function cartQuantityInitialize() {
    $(".btn-number").click(function (e) {
        e.preventDefault();

        let fieldName = $(this).attr("data-field");
        let type = $(this).attr("data-type");
        let input = $("input[name='" + fieldName + "']");
        let currentVal = parseInt(input.val());

        if (!isNaN(currentVal)) {
            if (type === "minus") {
                if (currentVal > input.attr("min")) {
                    input.val(currentVal - 1).change();
                }
                if (parseInt(input.val()) === input.attr("min")) {
                    $(this).attr("disabled", true);
                }
            } else if (type === "plus") {
                if (currentVal < input.attr("max")) {
                    input.val(currentVal + 1).change();
                }
                if (parseInt(input.val()) === input.attr("max")) {
                    $(this).attr("disabled", true);
                }
            }
        } else {
            input.val(0);
        }
    });

    $(".input-number").focusin(function () {
        $(this).data("oldValue", $(this).val());
    });

    $(".input-number").change(function () {
        let minValue = parseInt($(this).attr("min"));
        let maxValue = parseInt($(this).attr("max"));
        let valueCurrent = parseInt($(this).val());
        let name = $(this).attr("name");
        if (valueCurrent >= minValue) {
            $(
                ".btn-number[data-type='minus'][data-field='" + name + "']"
            ).removeAttr("disabled");
        } else {
            Swal.fire({
                icon: "error",
                title: "Cart",
                text: "Sorry, the minimum value was reached",
            });
            $(this).val($(this).data("oldValue"));
        }
        if (valueCurrent <= maxValue) {
            $(
                ".btn-number[data-type='plus'][data-field='" + name + "']"
            ).removeAttr("disabled");
        } else {
            Swal.fire({
                icon: "error",
                title: "Cart",
                text: "Sorry, stock limit exceeded.",
            });
            $(this).val($(this).data("oldValue"));
        }
    });
    $(".input-number").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if (
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            // Allow: Ctrl+A
            (e.keyCode === 65 && e.ctrlKey === true) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if (
            (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }
    });
}

function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1);
    let sURLVariables = sPageURL.split("&");
    for (let i = 0; i < sURLVariables.length; i++) {
        let sParameterName = sURLVariables[i].split("=");
        if (sParameterName[0] === sParam) {
            return sParameterName[1];
        }
    }
}

$(document).on("click", ".decrease-button", function () {
    let addonId = $(this).data("id");
    let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
    let currentValue = parseInt(addon_quantity_input.val(), 10);
    if (currentValue > 1) {
        addon_quantity_input.val(currentValue - 1);
        getVariantPrice();
    }
});

$(document).on("click", ".increase-button", function () {
    let addonId = $(this).data("id");
    let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
    let currentValue = parseInt(addon_quantity_input.val(), 10);
    addon_quantity_input.val(currentValue + 1);
    getVariantPrice();
});

$(document).on("click", ".decrease-button-cart", function () {
    let addon_quantity_input = $('input[name="quantity"]');
    let currentValue = parseInt(addon_quantity_input.val(), 10);
    if (currentValue > 1) {
        addon_quantity_input.val(currentValue - 1);
        getVariantPrice();
    }
});

$(document).on("click", ".increase-button-cart", function () {
    let addon_quantity_input = $('input[name="quantity"]');
    let currentValue = parseInt(addon_quantity_input.val(), 10);
    let maxValue = parseInt(addon_quantity_input.attr("max"));
    if (maxValue - 1 >= currentValue) {
        addon_quantity_input.val(currentValue + 1);
        getVariantPrice();
    } else {
        Swal.fire({
            icon: "error",
            title: "Cart",
            text: "Sorry, stock limit exceeded.",
        });
    }
});

$(".js-select2-custom").each(function () {
    let select2 = $.HSCore.components.HSSelect2.init($(this));
});
$("#delivery_address").on("click", function () {
    initMap();
});
// initMap();
function posCustomerSelectRawValue($select, select2Event) {
    if (
        select2Event &&
        select2Event.params &&
        select2Event.params.data &&
        select2Event.params.data.id !== undefined &&
        select2Event.params.data.id !== null
    ) {
        return select2Event.params.data.id;
    }
    if ($select && $select.length && $select.data("select2")) {
        var d = $select.select2("data");
        if (d && d[0] && d[0].id !== undefined && d[0].id !== null && d[0].id !== "") {
            return d[0].id;
        }
    }
    return $select.val();
}

function syncPosCustomerHiddenFields($select, select2Event) {
    var v = posCustomerSelectRawValue($select || $("#customer"), select2Event || null);
    var $user = $("#customer_id");
    var $internal = $("#internal_customer_id");
    var $deliveryInternal = $("#delivery_internal_customer_id");
    if (!$user.length) {
        return v;
    }
    if ($internal.length) {
        $user.val("");
        $internal.val("");
        if ($deliveryInternal.length) {
            $deliveryInternal.val("");
        }
        if (v !== undefined && v !== null && String(v) !== "" && String(v) !== "false") {
            if (String(v).indexOf("internal:") === 0) {
                var iid = String(v).replace(/^internal:/, "");
                $internal.val(iid);
                if ($deliveryInternal.length) {
                    $deliveryInternal.val(iid);
                }
            } else {
                $user.val(v);
            }
        }
    } else {
        if (v) {
            $user.val(v);
        }
    }
    return v;
}
var posCustomerDeliveryLoadTimer = null;
function schedulePosCustomerDeliveryLoad($select, select2Event) {
    clearTimeout(posCustomerDeliveryLoadTimer);
    posCustomerDeliveryLoadTimer = setTimeout(function () {
        var raw = syncPosCustomerHiddenFields($select, select2Event);
        if (typeof window.posOnCustomerChanged === "function") {
            window.posOnCustomerChanged(raw);
        }
    }, 15);
}
$("#customer").on("select2:select", function (e) {
    schedulePosCustomerDeliveryLoad($(this), e);
});
$("#customer").on("change", function () {
    schedulePosCustomerDeliveryLoad($(this), null);
});

$("#payment_card").on("change", function () {
    $("#paid_section").hide();
});
$("#payment_cash").on("change", function () {
    $("#paid_section").show();
});

$(document).on("change", "#discount_input_type", function () {
    let discountInput = $("#discount_input");
    let discountInputType = $(this);
    let maxLimit = discountInputType.val() === "percent" ? 100 : 1000000000;
    discountInput.attr("max", maxLimit);
});

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
        browserHasGeolocation
            ? "Error: {{ translate('The Geolocation service failed') }}."
            : "Error: {{ translate('Your browser doesn`t support geolocation') }}."
    );
    infoWindow.open(map);
}
