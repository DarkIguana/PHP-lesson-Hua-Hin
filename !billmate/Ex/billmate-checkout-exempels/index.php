<?php

require_once('init.php');
require_once('config.php');
require_once('lib/Billmate.php');

/**
 * $eid and $test is defined in config.php
 * $_SESSION['checkoutUrl'] is defined in initcheckout.php
 */

$checkoutUrl = isset($_SESSION['checkoutUrl']) ? $_SESSION['checkoutUrl'] : '';

?>

<?php if ($checkoutUrl != '') : ?>
<html>
    <head>
        <title>Billmate Checkout Example</title>
        <script src="js/jquery.min.js"></script>
        <script src="js/checkout.js"></script>

        <script type="text/javascript">

        $(function() {

            /*
             * Init checkout and listen to 
             */
             checkout.init();
            
            /**
             * Update cart
             */

            $(document).find('#formCart').on('submit', function(event) {

                /**
                 * Simplified example of update cart
                 */

                event.preventDefault();

                /** The store is busy with update cart and lock checkout to prevent customer interaction */
                checkout.lock();

                data = {};
                data.cart_item_amount_1 = $('#cart_item_amount_1').val();

                $.ajax({
                    type: "POST",
                    url: 'updateCheckout.php',
                    data: data,
                    success: function(response) {

                        /*
                         * Update cart AND update checkout
                         * When update cart and checkout is done, update iframe
                         */

                        if (response == 'OK') {
                            /** Expected return when the update was successful */

                            /** Tell checkout to update itself */
                            checkout.update();

                        } else {
                            /** Something went wrong */
                        }

                    },
                });

            });

            $(document).find('#bntLock').on('click', function() {
                checkout.lock();
            });

            $(document).find('#bntUnlock').on('click', function() {
                checkout.unlock();
            });

        });
        </script>

    </head>
    <body style="background: #F9FAF9;">

        <!-- Make some space for scrolling -->
        <div style='width: 100%; height: 70px;'>
            <h2>Header area</h2>
        </div>

        <hr />
        <h2>Cart area</h2>
        Product 1
        <form method="post" id="formCart" action="updateCheckout.php">
            <input type="text" name="cart_item_amount_1" id='cart_item_amount_1' value='1'> st
            <input type="submit" value="Update cart">
        </form>

        <hr />
        <input type="button" id="bntLock" value="Lock checkout">
        <input type="button" id="bntUnlock" value="Unlock checkout">

        <hr />
        <iframe name="checkout_iframe" id="checkout" src="<?php echo $checkoutUrl; ?>" sandbox="allow-same-origin allow-scripts allow-modals allow-popups allow-forms allow-top-navigation" style="width:100%;min-height:800px;border:none;" scrolling="no"></iframe>

        <hr />
        <div id='jsLog'></div>

        <hr />
        <form method="get" action="initCheckout.php">
            <input type="submit" value="initCheckout">
        </form>

    </body>
</html>
<?php else : ?>
<html>
    <head>
        <title>Billmate Checkout Example</title>
        <script src="js/jquery.min.js"></script>
        <script src="js/checkout.js"></script>
    </head>
    <body>
        Initiate Billmate Checkout with example cart
        <form method="get" action="initCheckout.php">
            <input type="submit" value="initCheckout">
        </form>
    </body>
</html>
<?php endif; ?>
