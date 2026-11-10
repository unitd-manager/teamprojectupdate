Util.createCPObject('cpm.agileIms.orderLink');

cpm.agileIms.orderLink.init = function(){
    $('.traineeSearchResult').hide();

    $('select#fld_record_type').change(function(){
        var recType = $(this).val();

        if (recType == 'SMS') {
            $('#traineeDetails').slideDown();
        }
    });
}