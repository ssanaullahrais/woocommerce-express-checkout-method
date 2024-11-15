/*
* If you are using WooPayments plugin by woocommerce and you don't see the express checkout option on the checkout page, please make sure you copy paste this code in function.php. 
* This code is supported for the woocommerce checkout page using shortcode etc not the block based checkout which introduced recently.
*/

// Register Express Checkout as a payment method
add_filter('woocommerce_payment_gateways', 'add_express_checkout_gateway');

function add_express_checkout_gateway($gateways) {
    class WC_Gateway_Express_Checkout extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'express_checkout';
            $this->title = 'Pay with Apple Pay';
            $this->method_title = 'Express Checkout';
            $this->method_description = 'Pay with Apple Pay or Google Pay';
            $this->has_fields = true;
            $this->supports = array('products');
        }

        public function payment_fields() {
            ?>
            <div class="wcpay-payment-request-wrapper">
                <div id="wcpay-express-checkout-element" class="StripeElement"></div>
            </div>
            <?php
        }
    }

    $gateways[] = 'WC_Gateway_Express_Checkout';
    return $gateways;
}

// Initialize Express Checkout Element
add_action('wp_footer', 'initialize_express_checkout_payment_method');

function initialize_express_checkout_payment_method() {
    if (!is_checkout()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof stripe !== 'undefined' && typeof wcpayConfig !== 'undefined') {
            // Function to initialize express checkout
            function initExpressCheckout() {
                const expressCheckoutConfig = {
                    buttonHeight: 48,
                    layout: {
                        maxColumns: 0,
                        maxRows: 0,
                        overflow: 'never'
                    },
                    buttonTheme: {
                        applePay: 'black',
                        googlePay: 'black'
                    },
                    buttonType: {
                        applePay: 'plain',
                        googlePay: 'plain'
                    },
                    paymentMethods: {
                        applePay: 'always',
                        googlePay: 'always',
                        paypal: 'never',
                        link: 'never',
                        amazonPay: 'never'
                    }
                };

                const elements = stripe.elements();
                const expressCheckoutElement = elements.create('expressCheckout', expressCheckoutConfig);
                
                const mountElement = document.getElementById('wcpay-express-checkout-element');
                if (mountElement) {
                    expressCheckoutElement.mount('#wcpay-express-checkout-element');
                }
            }

            // Initialize on page load
            initExpressCheckout();

            // Re-initialize when payment method changes
            const paymentMethods = document.querySelectorAll('.wc_payment_method input[name="payment_method"]');
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    if (this.value === 'express_checkout') {
                        setTimeout(initExpressCheckout, 100);
                    }
                });
            });
        }
    });
    </script>

    <style>
    .payment_method_express_checkout .wcpay-payment-request-wrapper {
        margin: 10px 0;
        padding: 0;
    }
    
    .payment_method_express_checkout #wcpay-express-checkout-element {
        width: 100%;
    }
    
    .payment_method_express_checkout #wcpay-express-checkout-element iframe {
        border: 0 !important;
        margin: -4px;
        padding: 0 !important;
        width: calc(100% + 8px);
        min-width: 100% !important;
        overflow: hidden !important;
        display: block !important;
        user-select: none !important;
        transform: translate(0) !important;
        color-scheme: light only !important;
        height: 56px;
        transition: height 0.35s, opacity 0.4s 0.1s;
    }

    .payment_box.payment_method_express_checkout {
        background-color: #f8f8f8;
        padding: 15px;
        border-radius: 4px;
    }
        
.wcpay-payment-request-wrapper {
    display: block !important;
}
    </style>
    <?php
}

// Modify payment method order to show Express Checkout first
add_filter('woocommerce_payment_gateways_order', 'reorder_payment_gateways', 10, 1);

function reorder_payment_gateways($gateways) {
    // Move Express Checkout to the beginning
    if (isset($gateways['express_checkout'])) {
        $express = $gateways['express_checkout'];
        unset($gateways['express_checkout']);
        return array_merge(['express_checkout' => $express], $gateways);
    }
    return $gateways;
}
