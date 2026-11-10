Util.createCPObject('cpm.tradingin.ledger');

cpm.tradingin.ledger = {
    init: function(){
        /* AUTO UPDATE INVOICE SEQ CODE FIELD */
        $(".row_invoice_code_vat input[name=invoice_code_vat]").livequery('change', function(){
            var code = $(this).val();
            var invoice_id   = $(this).attr('id');

            var url = '/admin/index.php?module=tradingin_invoiceSequence&_spAction=updateInvSeq&showHTML=0';

            $.get(url, {invoice_id: invoice_id, code: code}, function(){
                Util.hideProgressInd();
            });
        });

        /* AUTO UPDATE INVOICE SEQ DATE FIELD */
        $(".room-invSeq-table .fld_date").livequery('change', function(){
            var invoice_date = $(this).val();
            var invoice_id   = $(this).attr('fldId');

            var url = '/admin/index.php?module=tradingin_invoiceSequence&_spAction=updateInvSeq&showHTML=0';

            $.get(url, {invoice_id: invoice_id, invoice_date: invoice_date}, function(){
                Util.hideProgressInd();
            });
        });
    }
}

