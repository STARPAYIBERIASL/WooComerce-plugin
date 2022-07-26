(function ($) {
        $(document).ready(function () {
            checkStatus();
        });

        function checkStatus() {
            $.ajax({
                type: "POST",
                url: wc_checkout_starpay_params.ajax_url,
                data: {
                    orderId: wc_checkout_starpay_params.order_id
                }
            }).done(function (data) {
                data = JSON.parse(data);
                if (data && data.status === "paid") {
                    location.href = data.url;
                }
            });
            setTimeout(checkStatus, 3000);
        }
    }
)(jQuery);