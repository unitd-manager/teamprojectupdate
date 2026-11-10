Util.createCPObject('cpm.enterpriseIms.giroPayment');

cpm.enterpriseIms.giroPayment = {
    init: function(){
        $('.click-all-top .check-all').click(cpm.enterpriseIms.giroPayment.checkAllCol);
        $('.click-all-top .uncheck-all').click(cpm.enterpriseIms.giroPayment.uncheckAllCol);
    },

    checkAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-giroPayment-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.attr('checked', 'checked'); 
            }
        });
    },
    
    uncheckAllCol: function(e){
        var colPos = $(this).parent().index();
        $('.room-giroPayment-table tr').each(function(rowIndex, trObj) {
            if (rowIndex > 1) {
                var checkbox = $(trObj).find('td:eq(' + colPos + ') input');
                checkbox.removeAttr('checked'); 
            }
        });
    },
}

$('#btnGiroSubmit').livequery('click', function (e){
    e.preventDefault();
    msg = "Do you like to create receipts";
    Util.confirm(msg, function(){
        var expObj = {
            validate: false
           ,callbackNoValidateFn: function(){
                Util.hideProgressInd();
                var msg = 'Receipt created successfully';
                Util.alert(msg, function(){
                    window.location.reload(true);
                });
            }
        }
        Util.setUpAjaxFormGeneral('frmGiro', '', '', expObj);
        $('#frmGiro').submit();
    });
});

$('a.cancelReceipt').livequery('click', function (e){
    msg = "Do you like to cancel the Receipt?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=enterpriseIms_order&_spAction=cancelReceipt&showHTML=0';
        Util.showProgressInd();
        var receipt_code = $(this).attr('receipt_code');
        $.get(url,{receipt_code: receipt_code}, function(){
            alert ('Receipt Cancelled Succesfully');
            Util.hideProgressInd();
            window.location.reload(true);
        });
    }
});

$('#btnGenerateDBSTxtFile').livequery('click', function (e){
    msg = "Do you like to generate txt file for DBS?";
    if (!confirm(msg)){
        return false;
    }
    else {
        var url = 'index.php?_topRm=finance&module=enterpriseIms_giroPayment&_spAction=generateDBSTxtFile&showHTML=0';
        Util.showProgressInd();
        $.get(url,{}, function(){
            Util.hideProgressInd();
        });
    }
});

/* Setting the invoice code in session */
$('.m-enterpriseIms_giroPayment form.giroForm .invoiceCode').livequery('click', function(e){
    var invoice_code  = $(this).val();
    var is_checked  = $(this).is(':checked');

    var url = 'index.php?module=enterpriseIms_giroPayment&_spAction=setInvoiceCodeForSession&showHTML=0';
 
    Util.showProgressInd();
    $.get(url, {invoice_code: invoice_code, is_checked: is_checked}, function(html){
        Util.hideProgressInd();
    });
    
});

/* Setting the invoice code in session to print invoice pdf */
$('.m-enterpriseIms_giroPayment form.giroForm .invoiceCodeToPrint').livequery('click', function(e){
    var invoice_code  = $(this).val();
    var is_checked  = $(this).is(':checked');

    var url = 'index.php?module=enterpriseIms_giroPayment&_spAction=setInvoiceCodeToPrintForSession&showHTML=0';
 
    Util.showProgressInd();
    $.get(url, {invoice_code: invoice_code, is_checked: is_checked}, function(html){
        Util.hideProgressInd();
    });
    
});

$('.m-enterpriseIms_giroPayment #giroSearch a.selectedStudents').livequery('click', function (e){
    e.preventDefault();
    var month_val = $(this).attr('month_val');
    
    var url = 'index.php?module=enterpriseIms_giroPayment&_spAction=displayGiroFailures&month_val=' + month_val + '&showHTML=0';
    Util.showProgressInd();
    $.get(url, function(html){
        Util.hideProgressInd();
        $('.m-enterpriseIms_giroPayment .room-giroPayment-table').html(html);
    });
});