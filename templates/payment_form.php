<?php include dirname(__FILE__).'/standalone_css.php'; ?>

<script>
if ('function' != typeof(jQuery)) {
    console.log('ERROR: No jQuery? Are you serious?!');
}
var $p = jQuery;
var paay_refresh = function(selector, ttl) {
    var $el = $p(selector);
    var text = 'Refreshing in ';
    var refresh = function() {
        $el.text(text + ttl + '...');
        ttl = ttl - 1;
        if (ttl > 0) {
            setTimeout(refresh, 1000);
        } else {
            window.location.href = window.location.href;
        }
    }
    refresh(ttl);
}

<?php if (in_array($standalone_strategy, array('modal_manual', 'modal_auto'))): ?>
var paay_show_iframe = function() {
    $p('.paay-modal .paay-box.paay-initial').removeClass('paay-initial');
    $p('.paay_blast_loader').hide();
    $p('.paay-modal .paay-box iframe').show();
};
var paay_detect_iframe = function(url) {
    window.location.href = url;
};
$p(document).ready(function() {
    $p('.paay form').on('submit', function(e) {
        e.preventDefault();
        $p.ajax({
            url: $p(this).attr('action') + "?modal=true",
            type: "POST",
            data: $p(this).serialize(),
            beforeSend: function() {
                if ($p('.paay-modal').length == 0) {
                    $p('body').append('<div class="paay-modal"></div>');
                }
                var $modal = $p('.paay-modal');
                var box = '<div class="paay-box paay-initial"><div class="paay_blast_loader" /></div>';
                $modal.html(box);
            },
            success: function(response) {
                var $box = $p('.paay-modal .paay-box .paay_blast_loader');
                var iframe = '<iframe src="' + response.url + '" onload="paay_show_iframe();" />'
                $box.after(iframe);
            },
        });
    });
});
<?php endif; ?>

<?php if (in_array($standalone_strategy, array('landing_page_auto', 'modal_auto'))): ?>
$p(document).ready(function() {
    $p('#paay_form').submit();
});
<?php endif; ?>
</script>

<div class="paay">
    <?php if ('on-hold' === $order->get_status() && $paayonhold): ?>
    <div class="paay-info paay-green">
        Payment has been sent to the gateway. PAAY is now processing it - visit this page again or refresh the page to see if the status has been updated.
        <br /><br />
        <div class="paay-refresher"></div>
        <br />
        If you encountered any trouble with the payment and would like to PAAY again - click <a href="<?php echo str_replace('&paayonhold', '', $transaction['returnUrl']); ?>">here</a>
        <script>
            if (window.self !== window.top) {
                window.parent.paay_detect_iframe(window.location.href);
            } else {
                paay_refresh('.paay-refresher', 10);
            }
        </script>
    </div>
    <?php elseif ('on-hold' === $order->get_status() && $paaydeclined): ?>
    <div class="paay-info paay-green">
        Your payment has been declined.
        <br><br>
        Please try again with different card - click <a href="<?php echo str_replace('&paaydeclined', '', $transaction['cancelUrl']); ?>">here</a>
        <script>
            if (window.self !== window.top) {
                window.parent.paay_detect_iframe(window.location.href);
            }
        </script>
    </div>
    <?php elseif ('on-hold' === $order->get_status()): ?>
    <div class="paay-info">
        Thank you for your order, click the button below to pay with credit card using PAAY
    </div>
    <form id="paay_form" action="<?php echo $api_host; ?>/standalone-transactions" method="post">
        <!-- Customer -->
        <input type="hidden" id="email" name="email" value="<?php echo $transaction['email']; ?>" />

        <!-- Transaction -->
        <input type="hidden" id="amount" name="amount" value="<?php echo $transaction['amount']; ?>" />
        <textarea style="display: none;" id="details" name="details"><?php echo $transaction['details']; ?></textarea>
        <input type="hidden" id="orderId" name="orderId" value="<?php echo $transaction['orderId']; ?>" />
        <input type="hidden" id="returnUrl" name="returnUrl" value="<?php echo $transaction['returnUrl']; ?>" />
        <input type="hidden" id="cancelUrl" name="cancelUrl" value="<?php echo $transaction['cancelUrl']; ?>" />
        <input type="hidden" id="statusUrl" name="statusUrl" value="<?php echo $transaction['statusUrl']; ?>" />

        <!-- Billing -->
        <input type="hidden" id="billingFirstName" name="billingFirstName" value="<?php echo $transaction['billingFirstName']; ?>" />
        <input type="hidden" id="billingLastName" name="billingLastName" value="<?php echo $transaction['billingLastName']; ?>" />
        <input type="hidden" id="billingCompany" name="billingCompany" value="<?php echo $transaction['billingCompany']; ?>" />
        <input type="hidden" id="billingEmail" name="billingEmail" value="<?php echo $transaction['billingEmail']; ?>" />
        <input type="hidden" id="billingAddress1" name="billingAddress1" value="<?php echo $transaction['billingAddress1']; ?>" />
        <input type="hidden" id="billingAddress2" name="billingAddress2" value="<?php echo $transaction['billingAddress2']; ?>" />
        <input type="hidden" id="billingCity" name="billingCity" value="<?php echo $transaction['billingCity']; ?>" />
        <input type="hidden" id="billingPostcode" name="billingPostcode" value="<?php echo $transaction['billingPostcode']; ?>" />
        <input type="hidden" id="billingState" name="billingState" value="<?php echo $transaction['billingState']; ?>" />
        <input type="hidden" id="billingCountry" name="billingCountry" value="<?php echo $transaction['billingCountry']; ?>" />
        <input type="hidden" id="billingPhone" name="billingPhone" value="<?php echo $transaction['billingPhone']; ?>" />

        <!-- Shipping -->
        <input type="hidden" id="shippingFirstName" name="shippingFirstName" value="<?php echo $transaction['shippingFirstName']; ?>" />
        <input type="hidden" id="shippingLastName" name="shippingLastName" value="<?php echo $transaction['shippingLastName']; ?>" />
        <input type="hidden" id="shippingCompany" name="shippingCompany" value="<?php echo $transaction['shippingCompany']; ?>" />
        <input type="hidden" id="shippingAddress1" name="shippingAddress1" value="<?php echo $transaction['shippingAddress1']; ?>" />
        <input type="hidden" id="shippingAddress2" name="shippingAddress2" value="<?php echo $transaction['shippingAddress2']; ?>" />
        <input type="hidden" id="shippingCity" name="shippingCity" value="<?php echo $transaction['shippingCity']; ?>" />
        <input type="hidden" id="shippingPostcode" name="shippingPostcode" value="<?php echo $transaction['shippingPostcode']; ?>" />
        <input type="hidden" id="shippingState" name="shippingState" value="<?php echo $transaction['shippingState']; ?>" />
        <input type="hidden" id="shippingCountry" name="shippingCountry" value="<?php echo $transaction['shippingCountry']; ?>" />
        <input type="hidden" id="shippingPhone" name="shippingPhone" value="<?php echo $transaction['shippingPhone']; ?>" />

        <!-- 3DS -->
        <input type="hidden" name="threeds_visibility" value="<?php echo $transaction['threeds_visibility']; ?>" />

        <!-- Auth -->
        <input type="hidden" name="api_key" value="<?php echo $api_key; ?>" />
        <input type="hidden" name="signature" value="<?php echo $signature; ?>" />

        <!-- Guess what? -->
        <input class="paay-button" type="submit" value="PAAY" />
    </form>
    <?php else: ?>
    <div class="paay-info">
        Paid with <div class="paay-button">PAAY</div>
    </div>
    <script>
        if (window.self !== window.top) {
            window.parent.paay_detect_iframe(window.location.href);
        }
    </script>
    <?php endif; ?>
</div>