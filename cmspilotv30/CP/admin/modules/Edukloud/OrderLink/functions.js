Util.createCPObject('cpm.edukloud.orderLink');

cpm.edukloud.orderLink.init = function(){
    $('.traineeSearchResult').hide();

    $('select#fld_record_type').change(function(){
        var recType = $(this).val();

        if (recType == 'SMS') {
            $('#traineeDetails').slideDown();
        }
    });
}