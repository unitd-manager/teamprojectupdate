Util.createCPObject('cpw.museum.booking');

cpw.museum.booking = {
    init: function(){
        $('.row_number_of_students').hide();
        $('.row_number_of_elderly').hide();
        $('.row_number_of_youth').hide();

        $("#fld_organisation_type").livequery('change', function (e){
            var orgType = $(this).val();
            if(orgType =='Primary School' || orgType == 'Secondary School'){
                $('.row_number_of_students').show();
                $('.row_number_of_elderly').hide();
                $('.row_number_of_youth').hide();
            } else if(orgType =='Community Group - Elderly'){
                $('.row_number_of_students').hide();
                $('.row_number_of_elderly').show();
                $('.row_number_of_youth').hide();
            } else if(orgType =='Community Group - Youth'){
                $('.row_number_of_students').hide();
                $('.row_number_of_elderly').hide();
                $('.row_number_of_youth').show();
            } 
        });

        $('.row_date_pre_visit').hide();
        $("input[name='pre_visit']").livequery('change', function (e){
            var pre_visit = $("input[name='pre_visit']:checked", '#spExBookingForm').val();
            
            if(pre_visit == '1'){
                $('.row_date_pre_visit').show();
            } else {
                $('.row_date_pre_visit').hide();
            }
        });

        $('#venueHireForm #fld_interested_venue_other').parent().hide();
        $("#venueHireForm input[name='interested_venue[]']").change(function(){
            var intVenue = $(this).val();
            if(intVenue == 'Others' || intVenue == 'Other') {
                if($(this).is(":checked")){
                    $('#fld_interested_venue_other').parent().show();

                } else {
                    $('#fld_interested_venue_other').parent().hide();
                }
            } 
        });
    }    
}
