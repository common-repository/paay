<?php include dirname(__FILE__).'/standalone_css.php'; ?>

<div class="paay">
    <div class="paay-info">
        Help me out, pull my billing info from PAAY:
        <input type="email" name="paay_email" placeholder="PAAY email" />
        <div class="paay-button">PAAY</div>
        <select id="paay_customer_hint" style="display: none;"></select>
    </div>
    <script>
    var $j = $ || jQuery;
    var customers = [];
    var selected_customer = 0;
    $j('.paay-button').on('click', function(e) {
        var url = '<?php echo $api_url; ?>/customers/search/';
        var email = $j('input[name="paay_email"]').val();
        $j.get(url + email, function(data) {
            if (data.length > 0) {
                customers = data;
                var select = '';
                $j(customers).each(function(index, item) {
                    select += '<option value="' + index + '">' + item.billingState + ' / ' + item.billingCity + ' / ' + item.billingAddress1 + ' ' + item.billingAddress2 + '</option>';
                });

                $j('#paay_customer_hint').html(select);
                $j('#paay_customer_hint').show();
                $j('body').trigger('paay_fill_customer');
            }
        });
    });
    $j('#paay_customer_hint').on('change', function(e) {
        selected_customer = $j(this).val();
        $j('body').trigger('paay_fill_customer');
    });
    $j('body').on('paay_fill_customer', function(e) {
        var customer = customers[selected_customer];
        //Billing
        $j('#billing_first_name').val(customer.billingFirstName);
        $j('#billing_last_name').val(customer.billingLastName);
        $j('#billing_company').val(customer.billingCompany);
        $j('#billing_email').val(customer.billingEmail);
        $j('#billing_address_1').val(customer.billingAddress1);
        $j('#billing_address_2').val(customer.billingAddress2);
        $j('#billing_city').val(customer.billingCity);
        $j('#billing_postcode').val(customer.billingPostcode);
        $j('#billing_state').val(customer.billingState).change();
        $j('#billing_country').val(customer.billingCountry).change();
        $j('#billing_phone').val(customer.billingPhone);
        //Shipping
        $j('#shipping_first_name').val(customer.shippingFirstName);
        $j('#shipping_last_name').val(customer.shippingLastName);
        $j('#shipping_company').val(customer.shippingCompany);
        $j('#shipping_address_1').val(customer.shippingAddress1);
        $j('#shipping_address_2').val(customer.shippingAddress2);
        $j('#shipping_city').val(customer.shippingCity);
        $j('#shipping_postcode').val(customer.shippingPostcode);
        $j('#shipping_state').val(customer.shippingState).change();
        $j('#shipping_country').val(customer.shippingCountry).change();
        $j('#shipping_phone').val(customer.shippingPhone);
    });
    </script>
</div>