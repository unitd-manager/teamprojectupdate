Util.createCPObject('cpm.trading.shipment');

cpm.trading.shipment = {
    init: function(){
        $('.m-trading_shipment #actBtn_apply, .m-trading_shipment #actBtn_save')
        .click(cpm.trading.shipment.validateChangeInventoryStatus);

        $('#showInventory').click(cpm.trading.shipment.editInventoryForm);

    },

    validateChangeInventoryStatus: function() {
        var fld_status = $('#fld_status').val();
        var fld_status_prev = $('#fld_status_prev').val();
        var msg = '';
        if (fld_status != fld_status_prev) {
            if (fld_status == 'confirmed') {
                msg = "Changing status to 'confirmed' will update the Inventory records location " +
                      "to 'in shipment'. Are you sure to continue?";
                if (!confirm(msg)){
                    return false;
                }
            }
        }
    },

    shipmentReceived: function() {
        if (!confirm("Are you sure you want to receive shipment?")){
            return;
        }

        var url = 'index.php?module=trading_shipment&_spAction=shipmentReceived&showHTML=0';
        var shipment_id = $('#record_id').val();
        var params = {
             shipment_id: shipment_id
        };
        $.get(url, params, function (data) {
            var msg = 'Shipment successfully received. Inventory status changed to "in warehouse"';
            Util.alert(msg, function() {
                document.location = document.location;
            });
        });
    },

    printLabels: function() {
        var reportName = 'shipmentProductLabel';
        var shipment_id = $('#record_id').val();

        var url = 'index.php?_spAction=printReport&record_id='
                + shipment_id + '&showHTML=0&roomName=trading_salesOrder'
                + '&report=' + reportName;
        document.location = url;
    },
    
    editInventoryForm: function(e) {
        e.preventDefault();
        var shipment_id = $(this).attr('shipment_id');
        var url = 'index.php?module=trading_shipment&_spAction=editInventoryForm' +
                  '&shipment_id=' + shipment_id +
                  '&showHTML=0';
        var exp = {
            url: url
           ,afterOpen: function() {
                $('#btnUpdateInventoryCancel').click(function() {
                    $('#dialog').dialog('destroy');
                    $('#dialog').remove();
                });
                $('#btnUpdateInventory').click(cpm.trading.shipment.saveInventoryForm);
                $('#location_common').change(function() {
                    $('#updateInventory .location').val($(this).val());
                });
                $('#status_common').change(function() {
                    $('#updateInventory .status').val($(this).val());
                });
            }
        };
        Util.openDialogForLink('Edit Inventory',  900, 500, 0, exp);
    },

    saveInventoryForm: function() {
        var url = "index.php?module=trading_shipment"
                + "&_spAction=saveInventory"
                + "&showHTML=0";
        var values = $('#updateInventory input, #updateInventory select').serialize();

        Util.showProgressInd();
        $.post(url, values, function(json) {
            Util.alert(json.html, function() {
                Util.hideProgressInd();
                $('#dialog').dialog('destroy');
                $('#dialog').remove();

                var enquiry_id = parseInt($('#fld_enquiry_id').val());
                //if inventory sales order
                if (!enquiry_id) {
                    document.location = document.location;
                }
            });
        }, 'json');
    }


}