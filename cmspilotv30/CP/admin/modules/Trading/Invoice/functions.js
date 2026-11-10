Util.createCPObject('cpm.trading.invoice');

cpm.trading.invoice = {
    init: function(){
    },

    printInvoice: function() {
        var enquiry_id = parseInt($('#fld_enquiry_id').val());
        //SO not created through Enquiry process
        var reportName = '';

        var invoice_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&record_id='
                + invoice_id + '&showHTML=0&roomName=trading_invoice';
        if (!enquiry_id) {
            var urlInvoiceInventory = url + '&report=invoiceInventory';
            var urlInvoiceInventorySerial = url + '&report=invoiceInventorySerial';
            var text = "<a href='" + urlInvoiceInventory + "'>Print Invoice - No Serial</a><br>" + 
                       "<a href='" + urlInvoiceInventorySerial + "'>Print Invoice - With Serial</a><br>";
            
            Util.alert(text, null, 'Print Invoice');
        } else {
            reportName = 'invoice';
            url += '&report=' + reportName;
            document.location = url;
        }
        
    },
    
    printDeliveryNote: function() {
        var enquiry_id = parseInt($('#fld_enquiry_id').val());
        //SO not created through Enquiry process
        var reportName = 'deliveryNote';
        if (!enquiry_id) {
            reportName = 'deliveryNoteInventory';
        }
        var sales_order_id = $('#record_id').val();
        var url = 'index.php?_spAction=printReport&record_id='
                + sales_order_id + '&showHTML=0&roomName=trading_invoice'
                + '&report=' + reportName;
        document.location = url;
    }
}