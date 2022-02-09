"use strict";

var sln_customer_fields;
jQuery(function($) {
    if ($(".sln-booking-user-field").length) {
        sln_prepareToValidatingBooking($);
    }
    if ($("#sln_booking-details").length) {
        sln_adminDate($);
    }
    $("#calculate-total").on("click", sln_calculateTotal);
    $("#_sln_booking_amount,#_sln_booking_deposit").on("change", function() {
        var tot = $("#_sln_booking_amount").val();
        var bookingDeposit = $("#_sln_booking_deposit").val();
        $("#_sln_booking_remainedAmount").val(
            (+bookingDeposit > 0.0
                ? tot - bookingDeposit > 0.0
                    ? tot - bookingDeposit
                    : 0
                : 0
            ).toFixed(2)
        );
    });

    $("#_sln_booking_discounts_").on("select2:select", function(evt) {
        var element = evt.params.data.element;
        var $element = $(element);

        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
        sln_calculateTotal();
    });

    $("#_sln_booking_discounts_").on("select2:unselect", function(evt) {
        sln_calculateTotal();
    });

    sln_func_customBookingUser($);
    sln_manageAddNewService($);
    sln_manageCheckServices($);
    if (sln_isShowOnlyBookingElements($)) {
        sln_showOnlyBookingElements($);
    }

    $("#_sln_booking_service_select").on("select2:open", function() {
        sln_checkServices_on_preselection($);
    });
    $("#_sln_booking_attendant_select").on("select2:open", function() {
        sln_checkAttendants_on_preselection($);
    });

    function moreDetails() {
        $("#collapseMoreDetails").on("hide.bs.collapse", function() {
            $("#collapseMoreDetails")
                .parent()
                .removeClass("sln-box__collapsewrp--open");
        });
        $("#collapseMoreDetails").on("show.bs.collapse", function() {
            $("#collapseMoreDetails")
                .parent()
                .addClass("sln-box__collapsewrp--open");
        });
    }
    if ($("#collapseMoreDetails").length) {
        moreDetails();
    }
    sln_selectValueFormatting($);

    var input = document.querySelector("#_sln_booking_phone");

    function getCountryCodeByDialCode(dialCode) {
        var countryData = window.intlTelInputGlobals.getCountryData();
        var countryCode = '';
        countryData.forEach(function(data) {
           if (data.dialCode == dialCode) {
               countryCode = data.iso2;
           }
        });
        return countryCode;
    }

    var iti = window.intlTelInput(input, {
        initialCountry: getCountryCodeByDialCode(($('#_sln_booking_sms_prefix').val() || '').replace('+', '')),
        separateDialCode: true,
        autoHideDialCode: true,
        nationalMode: false,
    });

    input.addEventListener("countrychange", function() {
        if (iti.getSelectedCountryData().dialCode) {
            $('#_sln_booking_sms_prefix').val('+' + iti.getSelectedCountryData().dialCode);
        }
    });

    input.addEventListener("blur", function() {
        if (iti.getSelectedCountryData().dialCode) {
            $(input).val($(input).val().replace("+" + iti.getSelectedCountryData().dialCode, ""));
        }
    });

    $('#_sln_booking_sms_prefix').on('change', function () {
        iti.setCountry(getCountryCodeByDialCode(($(this).val() || '').replace('+', '')));
    });
});

function sln_selectValueFormatting($) {
    $(
        ".sln-booking-service-line .select2-container--sln .select2-selection__rendered"
    ).each(function() {
        $(this).html(function() {
            return (
                "<span>" +
                $(this)
                    .text()
                    .replace(/\, /g, "</span><span>") +
                " " +
                "</span>"
            );
        });
    });
}

function sln_isShowOnlyBookingElements($) {
    return $("#salon-step-date").data("mode") === "sln_editor";
}

function sln_showOnlyBookingElements($) {
    $(".wp-toolbar").css("padding-top", "0");
    $("#adminmenuback").hide();
    $("#adminmenuwrap").hide();
    $("#wpcontent").css("margin-left", "0");
    $("#wpadminbar").hide();
    $("#wpbody-content").css("padding-bottom", "0");
    $("#screen-meta").hide();
    $("#screen-meta-links").hide();
    $(".wrap").css("margin-top", "0");
    $("#post")
        .prevAll()
        .hide();
    $("#poststuff").css("padding-top", "0");
    $("#post-body-content").css("margin-bottom", "0");
    $("#postbox-container-1").hide();
    $("#post-body").css("width", "100%");
    $("#wpfooter").hide();
}

function sln_validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}

function sln_prepareToValidatingBooking($) {
    var form = $(".sln-booking-user-field").closest("form");
    $(form).on("submit", sln_validateBooking);
}

function sln_validateBooking() {
    var $ = jQuery;
    $(".sln-invalid").removeClass("sln-invalid");
    $(".sln-error").remove();
    var hasErrors = false;

    var toValidate = ["#_sln_booking_service_select"];
    sln_customer_fields =
        sln_customer_fields !== undefined
            ? sln_customer_fields
            : jQuery("#salon-step-date")
                  .attr("data-customer_fields")
                  .split(",");
    var fields = $("#salon-step-date")
        .attr("data-required_user_fields")
        .split(",");
    $.each(fields, function(k, val) {
        if (val !== "")
            toValidate.push(
                (sln_customer_fields.indexOf(val) !== -1
                    ? "#_sln_"
                    : "#_sln_booking_") + val
            );
    });

    $.each(toValidate, function(k, val) {
        if (val == "#_sln_booking_email" || val == "#_sln_email") {
        } else if (val == "#_sln_booking_service_select") {
            if (!$(".sln-booking-service-line").length) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if ($(val).attr("type") === "checkbox") {
            if (!$(val).is(":checked")) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if ($(val).prop("tagName") === "SELECT") {
            if (!$(val).find("option:selected").length) {
                $(val)
                    .addClass("sln-invalid")
                    .parent()
                    .append(
                        '<div class="sln-error error">This field is required</div>'
                    );
                if (!hasErrors) $(val).trigger("focus");
                hasErrors = true;
            }
        } else if (!$(val).val()) {
            $(val)
                .addClass("sln-invalid")
                .parent()
                .append(
                    '<div class="sln-error error">This field is required</div>'
                );
            if (!hasErrors) $(val).trigger("focus");
            hasErrors = true;
        }
    });
    return !hasErrors;
}

function sln_func_customBookingUser($) {
    $("#sln-update-user-field").select2({
        containerCssClass: "sln-select-rendered",
        dropdownCssClass: "sln-select-dropdown",
        theme: "sln",
        width: "100%",
        placeholder: $("#sln-update-user-field").data("placeholder"),
        language: {
            noResults: function() {
                return $("#sln-update-user-field").data("nomatches");
            },
        },

        ajax: {
            url:
                salon.ajax_url +
                "&action=salon&method=SearchUser&security=" +
                salon.ajax_nonce,
            dataType: "json",
            delay: 250,
            data: function(params) {
                return {
                    s: params.term,
                };
            },
            minimumInputLength: 3,
            processResults: function(data, page) {
                return {
                    results: data.result,
                };
            },
        },
    });

    $("#sln-update-user-field").on("select2:select", function() {
        var message = '<div class="alert alert-loading">Loading</div>';

        var data =
            "&action=salon&method=UpdateUser&s=" +
            $("#sln-update-user-field").val() +
            "&security=" +
            salon.ajax_nonce;
        $("#sln-update-user-message")
            .html(message)
            .fadeIn(500);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                sln_customer_fields =
                    sln_customer_fields !== undefined
                        ? sln_customer_fields
                        : jQuery("#salon-step-date")
                              .attr("data-customer_fields")
                              .split(",");
                if (!data.success) {
                    var alertBox = $('<div class="alert alert-danger"></div>');
                    $(data.errors).each(function() {
                        alertBox.append("<p>").html(this);
                    });
                    $("#sln-update-user-message")
                        .html(alertBox)
                        .fadeIn(500);
                } else {
                    var alertBox = $(
                        '<div class="alert alert-success">' +
                            data.message +
                            "</div>"
                    );
                    $("#sln-update-user-message")
                        .html(alertBox)
                        .fadeIn(500);
                    $.each(data.result, function(key, value) {
                        if (key == "id") $("#post_author").val(value);
                        else {
                            var el = $(
                                (sln_customer_fields.indexOf(key) === -1
                                    ? "#_sln_booking_"
                                    : "#_sln_") + key
                            );
                            el.is(":checkbox")
                                ? el.prop("checked", value)
                                : el.val(value);
                            if (el.is("select")) {
                                el.trigger("change");
                            }
                        }
                    });
                    $('[name="_sln_booking_createuser"]').attr(
                        "checked",
                        false
                    );
                    if ($('#_sln_booking_sms_prefix').val() == '') {
                        $('#_sln_booking_sms_prefix').val($('#_sln_booking_default_sms_prefix').val());
                    }
                    $('#_sln_booking_sms_prefix').trigger('change');
                }
            },
        });
        setTimeout(function() {
            $("#sln-update-user-message").fadeOut(500);
        }, 3000);
        return false;
    });
    $('.sln-booking__customer button[data-collection="reset"]').on(
        "click",
        function(e) {
            e.stopPropagation();
            e.preventDefault();
            $("#sln-update-user-field")
                .val(null)
                .trigger("change");
            $(".sln-booking__customer :input").val("");
            $("#sln-update-user-message").html("");

            $('#_sln_booking_sms_prefix').val($('#_sln_booking_default_sms_prefix').val());
            $('#_sln_booking_sms_prefix').trigger('change');
        }
    );
}

function sln_calculateTotal() {
    var loading =
        '<img src="' +
        salon.loading +
        '" alt="loading .." width="16" height="16" /> ';
    var form = jQuery("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CalcBookingTotal&security=" +
        salon.ajax_nonce;
    jQuery(".sln-calc-total-loading").html(loading);
    jQuery.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            jQuery(".sln-calc-total-loading").html("");
            jQuery("#_sln_booking_amount").val(data.total);
            jQuery("#_sln_booking_deposit").val(data.deposit);
            jQuery("#sln-duration").val(data.duration);
            jQuery(".sln-booking-discounts").remove();
            jQuery("#calculate-total")
                .parent()
                .after(data.discounts);

            jQuery('select[name="_sln_booking[services][]"][disabled]').each(
                function(i, e) {
                    var value = jQuery(e).val();
                    if (typeof data.services[value] !== "undefined") {
                        jQuery(e)
                            .data("select2")
                            .$selection.find(".select2-selection__rendered")
                            .html(data.services[value])
                            .attr("title", data.services[value]);
                    }
                }
            );

            jQuery("#_sln_booking_deposit").trigger("change"); //recalc amount to be paid
        },
    });
    return false;
}

function sln_calculateTotalDuration() {
    var $ = jQuery;
    var duration = 0;
    $(".sln-booking-service-line select[data-duration]").each(function() {
        duration += parseInt($(this).data("duration"));
    });
    var i = duration % 60;
    var h = (duration - i) / 60;
    if (i < 10) {
        i = "0" + i;
    }
    if (h < 10) {
        h = "0" + h;
    }

    $("#sln-duration").val(h + ":" + i);
}

function sln_adminDate($) {
    var items = $("#salon-step-date").data("intervals");
    var doingFunc = false;

    var func = function() {
        if (doingFunc) return;
        setTimeout(function() {
            doingFunc = true;
            $("[data-ymd]").removeClass("disabled");
            $("[data-ymd]").addClass("red");
            $.each(items.dates, function(key, value) {
                $('.day[data-ymd="' + value + '"]').removeClass("red");
            });
            $(".day[data-ymd]").removeClass("full");
            $.each(items.fullDays, function(key, value) {
                console.log(value);
                $('.day[data-ymd="' + value + '"]').addClass("red full");
            });

            $.each(items.times, function(key, value) {
                $('.hour[data-ymd="' + value + '"]').removeClass("red");
                $('.minute[data-ymd="' + value + '"]').removeClass("red");
                $(
                    '.hour[data-ymd="' + value.split(":")[0] + ':00"]'
                ).removeClass("red");
            });
            doingFunc = false;
        }, 200);
        return true;
    };
    func();
    $("body").on("sln_date", func);
    var firstValidate = true;

    function validate(obj) {
        var form = $(obj).closest("form");
        var validatingMessage =
            '<div class="alert alert-loading">' +
            salon.txt_validating +
            "</div>";
        var data = form.serialize();
        data += "&action=salon&method=checkDate&security=" + salon.ajax_nonce;
        $("#sln-notifications").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (firstValidate) {
                    $("#sln-notifications")
                        .html("")
                        .fadeIn(500);
                    firstValidate = false;
                } else if (!data.success) {
                    var alertBox = $('<div class="alert alert-danger"></div>');
                    $(data.errors).each(function() {
                        alertBox.append("<p>").html(this);
                    });
                    $("#sln-notifications")
                        .html("")
                        .append(alertBox)
                        .fadeIn(500);
                } else {
                    $("#sln-notifications")
                        .html("")
                        .append(
                            '<div class="alert alert-success">' +
                                $("#sln-notifications").data("valid-message") +
                                "</div>"
                        )
                        .fadeIn(500);
                    setTimeout(function() {
                        $("#sln-notifications .alert-success").fadeOut(500);
                    }, 3000);
                }
                bindIntervals(data.intervals);
                sln_checkServices($);
            },
        });
    }

    function bindIntervals(intervals) {
        items = intervals;
        func();
    }

    function putOptions(selectElem, value) {
        selectElem.val(value);
    }

    $("#_sln_booking_date, #_sln_booking_time").on("change", function() {
        validate(this);
    });
    validate($("#_sln_booking_date"));
    sln_initDatepickers($);
    sln_initTimepickers($);
    sln_initResendNotification();
    sln_initResendPaymentSubmit();
}

function sln_manageAddNewService($) {
    function getNewBookingServiceLineString(serviceId, attendantId) {
        var line = lineItem;
        line = line.replace(/__service_id__/g, serviceId);
        line = line.replace(/__attendant_id__/g, attendantId);
        line = line.replace(
            /__service_title__/g,
            servicesData[serviceId].title
        );
        line = line.replace(/__attendant_name__/g, attendantsData[attendantId]);
        line = line.replace(
            /__service_price__/g,
            servicesData[serviceId].price
        );
        line = line.replace(
            /__service_duration__/g,
            servicesData[serviceId].duration
        );
        line = line.replace(
            /__service_break_duration__/g,
            servicesData[serviceId].break_duration
        );
        return line;
    }

    $('button[data-collection="addnewserviceline"]').on("click", function(e) {
        e.stopPropagation();
        e.preventDefault();
        var serviceVal = Number($("#_sln_booking_service_select").val());
        var attendantVal = $("#_sln_booking_attendant_select").val();
        if (
            ((attendantVal == undefined || attendantVal == "") &&
                $("#_sln_booking_attendant_select option").length > 1) ||
            $(".sln-booking-service-line select").find(
                'option[value="' + serviceVal + '"]:selected'
            ).length
        ) {
            return false;
        }
        $(".sln-booking-service-line label.time").html("");

        var line = getNewBookingServiceLineString(serviceVal, attendantVal);
        $(
            ".sln-booking-service-line.sln-booking-service-line-last-added"
        ).removeClass("sln-booking-service-line-last-added");
        line = $(line)
            .addClass("sln-booking-service-line-last-added")
            .get(0);
        var added = false;
        $(".sln-booking-service-line #_sln_booking_service_select").each(
            function() {
                var val = Number($(this).val());
                if (typeof servicesData[val] !== "undefined") {
                    if (
                        !added &&
                        val &&
                        servicesData[serviceVal].exec_order <=
                            servicesData[val].exec_order
                    ) {
                        $(this)
                            .closest(".sln-booking-service-line")
                            .before(line);
                        added = true;
                    }
                }
            }
        );

        if (!added) {
            $(".sln-booking-service-action").before(line);
        }

        var selectHtml = "";
        if (servicesData[serviceVal].attendants.length) {
            $.each(servicesData[serviceVal].attendants, function(index, value) {
                selectHtml +=
                    '<option value="' +
                    value +
                    '" ' +
                    (value == attendantVal ? "selected" : "") +
                    " >" +
                    attendantsData[value] +
                    "</option>";
            });
        } else {
            selectHtml += '<option value="" selected >n.d.</option>';
        }

        $("#_sln_booking_attendants_" + serviceVal)
            .html(selectHtml)
            .trigger("change");

        sln_calculateTotal();

        sln_createServiceLineSelect2();
        sln_bindRemoveBookingsServices();
        sln_bindChangeAttendantSelects();
        sln_checkServices($);
        $("#_sln_booking_service_select")
            .val($("#_sln_booking_service_select option:eq(0)").val())
            .trigger("change");
        $(this)
            .addClass("sln-btn--disabled")
            .removeClass("sln-btn--blink")
            .prop("disabled", true);
        sln_selectValueFormatting($);
        return false;
    });
}
if (jQuery(".sln-booking-service-line").length) {
    jQuery("#sln-alert-noservices").fadeOut();
}
function sln_checkServices($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckServices&part=allServices&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (!data.success) {
                var alertBox = $('<div class="alert alert-danger"></div>');
                $.each(data.errors, function() {
                    alertBox.append("<p>").html(this);
                });
            } else {
                $("#sln_booking_services")
                    .find(".alert")
                    .remove();
                sln_processServices($, data.services);
                if (!$(".sln-booking-service-line").length) {
                    $("#sln-alert-noservices").fadeIn();
                } else {
                    $("#sln-alert-noservices").fadeOut();
                }
            }
        },
    });
}

function sln_checkServices_on_preselection($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckServices&part=allServices&all_services=true&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (data.services) {
                var options_ids = Object.keys(data.services).filter(function(
                    i
                ) {
                    return data.services[i];
                });
                var options = options_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  options_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                if (options) {
                    options
                        .html(function() {
                            return (
                                "<span>" +
                                $(this)
                                    .text()
                                    .replace(/\, /g, "</span><span>") +
                                " " +
                                "</span>"
                            );
                        })
                        .parent()
                        .addClass("select2-results__option--stl");
                }
                //
                var error_ids = Object.keys(data.services).filter(function(i) {
                    return data.services[i].errors.length;
                });
                var elems = error_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  error_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                if (elems)
                    elems
                        .html(function() {
                            return (
                                $(this).html() +
                                " " +
                                "<span class='sln-select__wrn'>" +
                                sln_customBookingUser.not_available_string +
                                "</span>"
                            );
                        })
                        .parent()
                        .addClass("select2-results__option--unavailable");
            }
        },
    });
}

function sln_checkAttendants_on_preselection($) {
    var form = $("#post");
    var data =
        form.serialize() +
        "&action=salon&method=CheckAttendants&all_attendants=true&security=" +
        salon.ajax_nonce;
    $.ajax({
        url: salon.ajax_url,
        data: data,
        method: "POST",
        dataType: "json",
        success: function(data) {
            if (data.attendants) {
                var error_ids = Object.keys(data.attendants).filter(function(
                    i
                ) {
                    return data.attendants[i].errors.length;
                });
                var elems = error_ids.length
                    ? $(".select2-results__option span[data-value]").filter(
                          function(el) {
                              return (
                                  error_ids.indexOf(
                                      $(this).attr("data-value")
                                  ) !== -1
                              );
                          }
                      )
                    : false;
                if (elems)
                    elems
                        .text(function() {
                            return (
                                $(this).text() +
                                " " +
                                sln_customBookingUser.not_available_string
                            );
                        })
                        .parent()
                        .css({ backgroundColor: "#ffa203", color: "#fff" });
            }
        },
    });
}

function sln_processServices($, services) {
    if (!services) return;
    $.each(services, function(index, value) {
        var serviceItem = $("#_sln_booking_service_" + index);
        if (value.status == -1) {
            $.each(value.errors, function(index, value) {
                var alertBox = $(
                    '<div class="row col-xs-12 col-sm-12 col-md-12"><div class="' +
                        ($("#salon-step-date").attr("data-m_attendant_enabled")
                            ? "col-md-offset-2 col-md-6"
                            : "col-md-8") +
                        '"><p class="alert alert-danger">' +
                        value +
                        "</p></div></div>"
                );
                serviceItem
                    .parent()
                    .parent()
                    .next()
                    .after(alertBox);
            });
        }
        serviceItem
            .parent()
            .parent()
            .find("label.time:first")
            .html(value.startsAt);
        serviceItem
            .parent()
            .parent()
            .find("label.time:last")
            .html(value.endsAt);
    });
}

function sln_manageCheckServices($) {
    if (typeof servicesData == "string") {
        servicesData = JSON.parse(servicesData);
    }
    if (typeof attendantsData == "string") {
        attendantsData = JSON.parse(attendantsData);
    }
    $("#_sln_booking_service_select")
        .on("change", function() {
            var html = "";
            if (servicesData[$(this).val()] != undefined) {
                $.each(servicesData[$(this).val()].attendants, function(
                    index,
                    value
                ) {
                    html +=
                        '<option value="' +
                        value +
                        '">' +
                        attendantsData[value] +
                        "</option>";
                });
                $(
                    "#_sln_booking_service_select + .select2-container--sln .select2-selection__rendered"
                ).html(function() {
                    return (
                        "<span>" +
                        $(this)
                            .text()
                            .replace(/\, /g, "</span><span>") +
                        " " +
                        "</span>"
                    );
                });
            }
            $("#_sln_booking_attendant_select option:not(:first)").remove();
            $("#_sln_booking_attendant_select")
                .append(html)
                .trigger("change");
        })
        .trigger("change");
    if ($("#_sln_booking_attendant_select").length) {
        $("#_sln_booking_service_select").on("select2:select", function(e) {
            var data = e.params.data;
            //console.log(data);
            if (data.id) {
                console.log(data.id);
                $(this).addClass("filled");
            } else {
                $(this).removeClass("filled");
            }
            if (
                data.id &&
                $("#_sln_booking_attendant_select").hasClass("filled")
            ) {
                $("#sln-addservice")
                    .removeClass("sln-btn--disabled")
                    .removeClass("sln-btn--hidden")
                    .addClass("sln-btn--blink")
                    .prop("disabled", false);
                $("#save-post").prop("disabled", false);
            } else {
                $("#sln-addservice")
                    .addClass("sln-btn--disabled")
                    .removeClass("sln-btn--blink")
                    .prop("disabled", true);
                $("#save-post").prop("disabled", true);
            }
        });
        $("#_sln_booking_attendant_select").on("select2:select", function(e) {
            var data = e.params.data;
            if (data.id) {
                $(this).addClass("filled");
            } else {
                $(this).removeClass("filled");
            }
            if (
                data.id &&
                $("#_sln_booking_attendant_select").hasClass("filled")
            ) {
                $("#sln-addservice")
                    .removeClass("sln-btn--disabled")
                    .removeClass("sln-btn--hidden")
                    .addClass("sln-btn--blink")
                    .prop("disabled", false);
                $("#save-post").prop("disabled", false);
            } else {
                $("#sln-addservice")
                    .addClass("sln-btn--disabled")
                    .removeClass("sln-btn--blink")
                    .prop("disabled", true);
                $("#save-post").prop("disabled", true);
            }
        });
    } else {
        $("#_sln_booking_service_select").on("select2:select", function(e) {
            var data = e.params.data;
            if (data.id) {
                console.log(data.id);
                $(this).addClass("filled");
                $("#sln-addservice")
                    .removeClass("sln-btn--disabled")
                    .removeClass("sln-btn--hidden")
                    .addClass("sln-btn--blink")
                    .prop("disabled", false);
                $("#save-post").prop("disabled", false);
            } else {
                $(this).removeClass("filled");
                $("#sln-addservice")
                    .addClass("sln-btn--disabled")
                    .removeClass("sln-btn--blink")
                    .prop("disabled", true);
                $("#save-post").prop("disabled", true);
            }
        });
    }

    sln_bindRemoveBookingsServices();
    sln_bindChangeAttendantSelects();
}

function sln_bindRemoveBookingsServices() {
    function sln_bindRemoveBookingsServicesFunction() {
        sln_calculateTotal();
        if (jQuery("#_sln_booking_service_select").length) {
            sln_checkServices(jQuery);
        }
        return false;
    }

    sln_bindRemove();
    jQuery('button[data-collection="remove"]')
        .off("click", sln_bindRemoveBookingsServicesFunction)
        .on("click", sln_bindRemoveBookingsServicesFunction);
}

function sln_bindChangeAttendantSelects() {
    function bindChangeAttendantSelectsFunction() {
        sln_checkServices(jQuery);
    }

    jQuery("select[data-attendant]")
        .off("change", bindChangeAttendantSelectsFunction)
        .on("change", bindChangeAttendantSelectsFunction);
}

function sln_initResendNotification() {
    var $ = jQuery;
    $("#resend-notification-submit").on("click", function() {
        var data =
            "post_id=" +
            $("#post_ID").val() +
            "&emailto=" +
            $("#resend-notification").val() +
            "&message=" +
            $("#resend-notification-text").val() +
            "&action=salon&method=ResendNotification&security=" +
            salon.ajax_nonce +
            "&" +
            $.param(salonCustomBookingUser.resend_notification_params);
        var validatingMessage =
            '<img src="' +
            salon.loading +
            '" alt="loading .." width="16" height="16" /> ';
        $("#resend-notification-message").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (data.success)
                    $("#resend-notification-message").html(
                        '<div class="alert alert-success">' +
                            data.success +
                            "</div>"
                    );
                else if (data.error)
                    $("#resend-notification-message").html(
                        '<div class="alert alert-danger">' +
                            data.error +
                            "</div>"
                    );
            },
        });
        return false;
    });
}

function sln_initResendPaymentSubmit() {
    var $ = jQuery;
    $("#resend-payment-submit").on("click", function() {
        var data =
            "post_id=" +
            $("#post_ID").val() +
            "&emailto=" +
            $("#resend-payment").val() +
            "&action=salon&method=ResendPaymentNotification&security=" +
            salon.ajax_nonce +
            "&" +
            $.param(salonCustomBookingUser.resend_payment_params);
        var validatingMessage =
            '<img src="' +
            salon.loading +
            '" alt="loading .." width="16" height="16" /> ';
        $("#resend-payment-message").html(validatingMessage);
        $.ajax({
            url: salon.ajax_url,
            data: data,
            method: "POST",
            dataType: "json",
            success: function(data) {
                if (data.success)
                    $("#resend-payment-message").html(
                        '<div class="alert alert-success">' +
                            data.success +
                            "</div>"
                    );
                else if (data.error)
                    $("#resend-payment-message").html(
                        '<div class="alert alert-danger">' +
                            data.error +
                            "</div>"
                    );
            },
        });
        return false;
    });
}
