Util.createCPObject('cpm.tradingsg.booking');

cpm.tradingsg.booking = {
    init : function(){
        $("input[name='c_company_name']")
        .livequery(cpm.tradingsg.booking.customerSearch);

        $("input[name='employee_name']")
        .livequery(cpm.tradingsg.booking.employeeSearch);
    },

    customerSearch: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_booking&_spAction=searchClientName&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var customer_id     = selectedObj.id;

                $("input[name='customer_id']").val(customer_id);
                //$(this).after("<input type='hidden' name='customer_id' value=" + customer_id + ">");
            }
        });
    },

    employeeSearch: function() {
        var titleObj = this;
        $(titleObj).autocomplete({
             source : 'index.php?module=tradingsg_booking&_spAction=searchEmployeeName&showHTML=0'
            ,minLength : 1
            ,selectFirst: true
            ,autoFocus: true
            ,select: function(event, ui) {
                var selectedObj    = ui.item;
                var employee_id     = selectedObj.id;

                $("input[name='employee_id']").val(employee_id);
                //$(this).after("<input type='hidden' name='employee_id' value=" + employee_id + ">");
            }
        });
    },
}
cpm.tradingsg.booking.afterNewCustomer = function(data){
    Util.closeAllDialogs();
    //alert(data.returnUrl);
    var returnVal = data.returnUrl;
    var currFldArr = returnVal.split('_');
    var customer_id = currFldArr[0];
    var customer_name = currFldArr[1];
    $("input[name='customer_id']").val(customer_id);
    $("input[name='c_company_name']").val(customer_name);
    var mgsalert = 'Customer updated successfully!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
    /*Util.alert('Customer updated successfully.', function(){
    });*/
}
cpm.tradingsg.booking.afterEditCustomer = function(){
    Util.closeAllDialogs();
    var customer_id     = $("input[name=customer_id]").val();
    var url = 'index.php?module=tradingsg_booking&_spAction=clientFields&showHTML=0';
    Util.showProgressInd();
    $.get(url, {customer_id: customer_id}, function(html){
        Util.hideProgressInd();
        $('#clientFields').html(html);
    });
    var mgsalert = 'Customer updated successfully!';
    var n = noty({
        text: mgsalert,
        type: 'confirm',
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'defaultTheme',
        timeout: 5000,
    });
}
