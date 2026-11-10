Util.createCPObject('cpm.hms.expense');

cpm.hms.expense = {
    init: function(){
        $(".addProduct input[name='product_title']")
        .livequery(cpm.hms.expense.expenseProductTitle);
        
        $("#orderItems input[name='expense_qty']").live('change', function(){
            var qty = $(this).val();
            var expense_id = $(this).attr('expense_id');
            var expense_product_id = $(this).attr('expense_product_id');
            var previousQtyValue = $(this).attr('previousQtyValue');
            var stock = parseInt($(this).attr('stock'), 10);
            
            if(stock < qty){
                alert('The qty should be less than the stock qty');
                //cpm.hms.expense.reloadOrderItems(expense_id);
                $('#fld_Expense_qty_'+expense_product_id).val(previousQtyValue);
                $('#fld_Expense_qty_'+expense_product_id).focus();
            } else {
                Util.showProgressInd();
                var url = 'index.php?module=hms_expense&_spAction=updateQtyOrderItem&showHTML=0';
                $.get(url, {qty: qty, expense_product_id: expense_product_id, expense_id: expense_id}, function(html){
                  Util.hideProgressInd();
                  cpm.hms.expense.reloadOrderItems(expense_id); 
                });
            }
        });

        /*$(".locationStatus select[name='status']").live('change', function(){
            var status = $(this).val();
            var expense_id = $('#record_id').val();
            var site_id    = $("input[name='site_id']").val();

            Util.showProgressInd();
            var url = 'index.php?module=hms_expense&_spAction=updateStatusExpense&showHTML=0';
            $.get(url, {status: status, expense_id: expense_id}, function(html){
              cpm.hms.expense.reloadEditDisplay(expense_id, site_id); 
            });
            
        });*/

        $("#orderItems select[name='product_status']").live('change', function(){
            var product_status = $(this).val();
            var parent = $(this).closest('.locationStatus');
            var expense_id = $('.expense_id_row',parent).val();
            var expense_product_id = $('.expense_product_id_row',parent).val();
            var site_id    = $("input[name='site_id']").val();
            
            Util.showProgressInd();
            var url = 'index.php?module=hms_expense&_spAction=updateStatusOrderItem&showHTML=0';
            $.get(url, {product_status: product_status, expense_product_id: expense_product_id, expense_id: expense_id}, function(html){
                cpm.hms.expense.reloadEditDisplay(expense_id, site_id); 
            });
            
        });

        $('.completeTransaction').livequery('click', function (){
            var expense_id      = $(this).attr('expense_id');
            var site_id         = $(this).attr('site_id');
            var expense_product = $('.expense_product_count').val();

            if(expense_product == 0 || expense_product == undefined){
                alert('Please add some products!');
                $('#fld_product_title').focus();

            }else{

                var msg = "do you like to complete the transaction?";
                if (confirm(msg)){
                    Util.showProgressInd();
                    var url = 'index.php?module=hms_expense&_spAction=updateCompleteTransactionProduct&showHTML=0';
                    $.get(url, {expense_id: expense_id}, function(html){
                      cpm.hms.expense.reloadEditDisplay(expense_id, site_id); 
                    });
                }
            }
        });

        $('.rollbackChanges').livequery('click', function (){
            var expense_id      = $(this).attr('expense_id');
            var site_id         = $(this).attr('site_id');
            var expense_product = $('.expense_product_count').val();

            var msg = "do you like to rollback the transaction?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=hms_expense&_spAction=rollbackCompleteTransactionProduct&showHTML=0';
                $.get(url, {expense_id: expense_id}, function(html){
                  cpm.hms.expense.reloadEditDisplay(expense_id, site_id); 
                });
            }
        });

        $('.deductFromStock').livequery('click', function (){
            var expense_id = $(this).attr('expense_id');
            var site_id = $(this).attr('site_id');

            var msg = "This action will deduct item(s) from the stock \n\n Would you like to continue?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=hms_expense&_spAction=updateDeductStockProduct&showHTML=0';
                $.get(url, {expense_id: expense_id}, function(html){
                  cpm.hms.expense.reloadEditDisplay(expense_id, site_id);
                });
            }
        });

        $('.cancelExpenseProduct').livequery('click', function (){
            var expense_id = $(this).attr('expense_id');
            var expense_product_id = $(this).attr('expense_product_id');
            var product_status = 'Cancelled';

            var msg = "Are you sure to cancel this item?";
            if (confirm(msg)){
                Util.showProgressInd();
                var url = 'index.php?module=hms_expense&_spAction=updateStatusOrderItem&showHTML=0';
                $.get(url, {product_status: product_status, expense_product_id: expense_product_id, expense_id: expense_id}, function(html){
                  cpm.hms.expense.reloadOrderItems(expense_id); 
                });
            }
        });
    },

    expenseProductTitle: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=hms_expense&_spAction=searchProductTitle&showHTML=0'
            ,minLength : 2
            ,selectFirst: true
            ,autoFocus: true
            ,focus: function(event, ui) {
                var len = $('.ui-autocomplete > li').length;
                if(len === 1){
                    var selectedObj = ui.item;
        			var product_id = selectedObj.id
                    var expense_id = $(this).attr('expense_id');
        			$(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                    //--------------------------------------------
                    Util.showProgressInd();
    	           	var url = 'index.php?module=hms_expense&_spAction=updateOrderLineItems&showHTML=0';
                    $.get(url, {product_id: product_id, expense_id: expense_id}, function(json){
    	                cpm.hms.expense.reloadOrderItems(expense_id);
    	                $(".addProduct input[name='product_title']").val('');
                        Util.hideProgressInd();
                    });
                    $(titleObj).autocomplete( "close" );
                }
            }
            ,select: function(event, ui) {
                var selectedObj = ui.item;
                var product_id = selectedObj.id
                var expense_id = $(this).attr('expense_id');
                //alert (product_id);
                $(this).after("<input type='hidden' name='product_id' value=" + product_id + ">");

                //--------------------------------------------
                Util.showProgressInd();
                var url = 'index.php?module=hms_expense&_spAction=updateOrderLineItems&showHTML=0';
                $.get(url, {product_id: product_id, expense_id: expense_id}, function(json){
                    cpm.hms.expense.reloadOrderItems(expense_id);
                    if (json.msg =='Please note the product is already added') {
                        Util.hideProgressInd();
                        Util.alert(json.msg);
                        $('input[name=product_title]').val('');
                        return;
                    }
                    $(".addProduct input[name='product_title']").val('');
                    Util.hideProgressInd();
                });
    		}
    	});
    },

    reloadOrderItems: function(expense_id){
        var url = 'index.php?module=hms_expense&_spAction=orderItems&showHTML=0';
        $.get(url, {expense_id: expense_id}, function(html){
            $('#orderItems').html(html);
            Util.hideProgressInd();
        });
    },

    reloadEditDisplay: function(expense_id, site_id){
        var url = 'index.php?module=hms_expense&_spAction=editDisplay&showHTML=0';
        $.get(url, {expense_id: expense_id, site_id:site_id}, function(html){
            $('#editDisplayLoad').html(html);
            Util.hideProgressInd();
        });
    }

}
