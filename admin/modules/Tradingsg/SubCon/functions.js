Util.createCPObject('cpm.tradingsg.subCon');

cpm.tradingsg.subCon = {
    init: function(){
        $('#createLogin').live('click', function (e){
                var title = "Create Login";
                var supplier_id = $(this).attr('supplier_id');
                var email = $(this).attr('email');
                e.preventDefault();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Login Created Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'createLoginForm', title, 450, 350, expObj);
        });

        $('#generatePO').live('click', function (e){
                var title = "Create Subcon Payment";
                //var supplier_id = $(this).attr('supplier_id');
                e.preventDefault();
                var supplier_id = $('#record_id').val();
                var expObj = {
                    validate: true
                   ,callbackOnSuccess: function(){
                        var msg = 'Payment Created Successfully';
                        Util.alert(msg, function(){
                            Util.closeAllDialogs();
                            window.location.reload(true);
                        });
                    }
                }
            Util.openFormInDialog.call(this, 'portalForm', title, 450, 350, expObj);
        });

        $('.m-tradingsg_subCon input.poCode').livequery('click', function (e){
            Util.showProgressInd();
            po_code = $(this).val();
            var sub_con_work_order_id = $(this).attr('sub_con_work_order_id');
            var checked    = $(this).attr('checked') ? 'checked' : '';
            var checkedVal = checked == 'checked' ? 1 : 0;

            var url = 'index.php?_topRm=inventory&module=tradingsg_subCon&_spAction=populatePOAmount&showHTML=0';
            $.get(url,{po_code: po_code, sub_con_work_order_id: sub_con_work_order_id, checkedVal: checkedVal}, function(html){
                $('input[id=fld_amount]').val(html);
                Util.hideProgressInd();
            });
        });

        $(".receiptViewHistory").live('click', function (e){
            var sub_con_work_order_id = $(this).attr('sub_con_work_order_id');
            e.preventDefault();

            var expObj = {
                beforeCloseFn: function(){
                    Util.closeAllDialogs();
                }
            }
            Util.openDialogForLink.call(this, 'Sub Con Payment History', 700, 500, expObj);
        });

        $('.cancelSupplierReceipt').livequery('click', function (e){
            msg = "Do you like to cancel the Receipt?";
            if (!confirm(msg)){
                return false;
            }
            else {
                var url = 'index.php?module=tradingsg_subCon&_spAction=cancelSupplierReceipt&showHTML=0';
                Util.showProgressInd();
                var sub_con_payments_id = $(this).attr('sub_con_payments_id');
                var sub_con_work_order_id = $(this).attr('sub_con_work_order_id');
                var sub_con_id = $(this).attr('sub_con_id');
                $.get(url,{sub_con_payments_id: sub_con_payments_id, sub_con_work_order_id:sub_con_work_order_id}, function(html){
                    alert ('Receipt Cancelled Succesfully');
                    Util.hideProgressInd();
                    Util.closeAllDialogs();
                    cpm.tradingsg.subCon.reloadPurchaseOrderDetail(sub_con_id);
                });
            }
        });
    }
}