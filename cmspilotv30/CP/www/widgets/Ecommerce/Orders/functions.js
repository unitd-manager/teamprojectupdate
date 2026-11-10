Util.createCPObject('cpw.ecommerce.orders');

cpw.ecommerce.orders.init = function(){
    $(function () {
        $('.w-ecommerce-orders .amtPaid').click(function(){
            var amount = parseInt($(this).attr('amount'));
            var order_id = $(this).attr('order_id');

            var amountPaid = prompt('Enter the paid amount', amount);

            if (amountPaid){
                if (amountPaid > amount){
                    Util.alert('The amount entered is higher than the order amount');
                } else {
                    Util.showProgressInd();
                    var url = '/index.php?widget=ecommerce_orders&_spAction=updateAmountPaid&showHTML=0';
                    
                    $.get(url, {order_id: order_id, amount_paid: amountPaid}, function(data){
                        document.location = document.location;
                    });
                }
            }
        });

        $('.w-ecommerce-orders .printOrder').click(function(){
            var order_id = $(this).attr('order_id');

            var url = '/index.php?widget=ecommerce_orders&_spAction=printOrder&showHTML=1&order_id=' + order_id;

            w = 700;
            h = 600;
            windowString = "height=" + h + ",width=" + w + ",scrollbars=yes," +
            "resizable=yes,left=" + (screen.width-w)/2 + ",top=" +
            (screen.height-h)/2
            wind = window.open( url, "print", windowString);
        });
    });
}
