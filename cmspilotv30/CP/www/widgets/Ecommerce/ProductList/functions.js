Util.createCPObject('cpw.ecommerce.productList');

cpw.ecommerce.productList.decimals = 2;

cpw.ecommerce.productList.init = function(){
    $(function () {
        cpw.ecommerce.productList.setValues();
        
        $('.w-ecommerce-productList .select input, .w-ecommerce-productList td.qty .input').click(function(e){
            cpw.ecommerce.productList.setValues();
        });
        
        $('.w-ecommerce-productList .select input, .w-ecommerce-productList td.qty .input, .w-ecommerce-productList .notes').change(function(e){
            cpw.ecommerce.productList.addRemoveItems.call(this);
        });
    });
}

cpw.ecommerce.productList.addRemoveItems = function(e){
    var parent = $(this).closest('tr');
    var cboxObj  = $('input[type=checkbox]', parent);
    var qtyObj = $('td.qty .input', parent);
    var url = $('#addToCartUrl').val() + '&record_id=' + cboxObj.val();
    var qty = (!cboxObj.attr('checked')) ? 0 :  qtyObj.val();
    var notesObj = $('textarea.notes', parent);
    var notes = (notesObj.length > 0) ? notesObj.val() : '';
    
    $.get(url, {qty: qtyObj.val(), notes: notes}, function(){
    });
}

cpw.ecommerce.productList.setValues = function(){
    var grossTotal = 0;
    var decimals = cpw.ecommerce.productList.decimals;

    $('.w-ecommerce-productList input[type=checkbox]').each(function(){
        var parent    = $(this).closest('tr');
        var cboxObj   = $(this);
        var qtyObj    = $('td.qty .input', parent);
        var priceObj  = $('.total', parent);
        var notesWrap = $('.notesWrap', parent);

        var unitPrice = parent.attr('unitPrice');
        if (!cboxObj.attr('checked')){
            qtyObj.val(0);
            /** if there is a notes field per item, toggle the display **/
            if (notesWrap.length > 0){
                notesWrap.hide();
            }
        } else if (qtyObj.val() == 0) {
            qtyObj.val(1);
        }

        if (qtyObj.val() > 0 && notesWrap.length > 0){
            notesWrap.show();
        }
        
        var qty = qtyObj.val();
        var price = qty * unitPrice;
        
        total = Util.addCommasToNumber(parseFloat(price).toFixed(decimals));
        priceObj.html(total);

        grossTotal += price;
    });

    grossTotal = Util.addCommasToNumber(parseFloat(grossTotal).toFixed(decimals));
    $('#grossTotal').html(grossTotal);
}
