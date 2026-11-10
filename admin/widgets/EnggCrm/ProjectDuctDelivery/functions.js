$(function(){
    $('.deliveryOrderEdit').livequery('click', function (e){
        var title      = "Edit Delivery Order";
        var delivery_order_id = $(this).attr('delivery_order_id');

        e.preventDefault();
        var expObj = {
            validate: true,
            callbackOnSuccess: function(data){
                Util.closeAllDialogs();
                var mgsalert = 'Updated delivery order successfully!';
                var n = noty({
                    text: mgsalert,
                    type: 'confirm',
                    dismissQueue: true,
                    layout: 'topCenter',
                    theme: 'defaultTheme',
                    timeout: 5000,
                });
                Util.hideProgressInd();
            }
        }

        Util.openFormInDialog.call(this, 'editForDO', title, 700, 500, expObj);
    });

});

var projectDuctDelivery = {
}