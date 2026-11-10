Util.createCPObject('cpm.pms.orderLink');

cpm.pms.orderLink.init = function(){
    $('.traineeSearchResult').hide();

    $('select#fld_record_type').change(function(){
        var recType = $(this).val();

        if (recType == 'SMS') {
            $('#traineeDetails').slideDown();
        }
    });
}