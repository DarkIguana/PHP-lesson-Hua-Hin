var checkout = new function() {

    var self = this;

    this.init = function () {
        jQuery(document).ready(function () {
            window.addEventListener("message", self.handleEvent);
        });
    }

    this.update = function(){

        /**
         * Refresh checkout in iframe
         * In this example the name of iframe that contain Billmate Checkout have the name checkout_iframe
         */
        checkout_iframe.postMessage('update', '*');
    }

    this.lock = function() {

        /**
         * Lock Billmate checkout from customer interactions
         * Will display loading animation and is used when the store is working on the order, for example when cart is updated
         */
        checkout_iframe.postMessage('lock', '*');
    }

    this.unlock = function() {

        /**
         * If checkout is locked, remove loading animation and customer can continue to interact with Billmate Checkout
         * Is used when store is done with work that affect the order and Billmate Checkout, for example when cart is updated
         */
        checkout_iframe.postMessage('unlock', '*');
    }
 
    this.handleEvent = function(event) {

        if(event.origin != "") {
            try {
                var json = JSON.parse(event.data);
            } catch (e) {
                return;
            }

            switch (json.event) {
                case 'address_selected':

                    /**
                     * Customer adress have been set or updated
                     */
                     $('#jsLog').append('address_selected<br />');

                    break;
                case 'payment_method_selected':

                    /**
                     * Payment method is selected
                     */
                     $('#jsLog').append('payment_method_selected<br />');

                    break;
                case 'checkout_success':

                    /**
                     * Order is paid and customer are not already redirected to accepturl
                     * Store decideds what to do with order and if redirect customer
                     */
                     $('#jsLog').append('checkout_success<br />');

                    break;
                case 'content_height':

                    /**
                     * The height of checkout, parent can use height to set iframe height when height changes
                     */
                    $(document).find('#checkout').height(json.data);
                    $('#jsLog').append('content_height: ' + json.data + '<br />');

                    break;
                case 'content_scroll_position':

                    /**
                     * When checkout iframe page is scrolled 
                     * If iframe have same height as Billmate Checkout the store can do the scrolling 
                     */
                    window.latestScroll = $(document).find( "#checkout" ).offset().top + json.data;
                    $('html, body').animate({scrollTop: $(document).find( "#checkout" ).offset().top + json.data}, 400);

                    $('#jsLog').append('content_scroll_position: ' + json.data + '<br />');

                    break;
                case 'checkout_loaded':

                    /** Checkout done loading , unlock it just in case it is locked*/
                    self.unlock();

                    $('#jsLog').append('checkout_loaded<br />');

                    break;
                default:
                    break;

            }
        }
    }
};
