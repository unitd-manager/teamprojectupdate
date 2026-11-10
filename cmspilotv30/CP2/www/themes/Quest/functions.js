Util.createCPObject('cpt.quest');

cpt.quest = {
    init: function(){
        $('.rt-home .highlights .w-content-record ul li:nth-child(3n)').css('margin-right', '0');

        $('.rt-home .highlights .w-content-record ul li').each(function(i){
            numWord = Util.getNumberToWord(i + 1);
            $(this).addClass(numWord);
        });

        $("#col1 .w-core-mainNav ul li:last-child")
        .livequery(function() {
            $(this).addClass('last');
        });

        $("#btnAddTrainee").click(function(e) {
            e.preventDefault();
            var qty = prompt("Please enter the number of trainee","5");
            var url = '/index.php?_theme=quest&_spAction=totalTraineeToAdd&showHTML=0';
            Util.showProgressInd();
            $.post(url, {qty: qty}, function(html){
                $('#trainees').append(html);
                Util.hideProgressInd();
            });
        });

        $("a.removeTrainee")
        .livequery('click', function(e) {
            e.preventDefault();
            var fldSet = $(this).closest('.traineeFldsetWrapper')
            Util.alert('Are you sure to remove this trainee?', function(){
                fldSet.remove();
            })
        });

        $('.btnCancel a').click(function(e){
            e.preventDefault();
            document.getElementById("frmShippingDetails").reset();
        });

        $('.btnApply a').click(function(e){
            e.preventDefault();
            $('form#frmShippingDetails input[name=apply]').val(1);
            $('form#frmShippingDetails').submit();
        });

        $('.btnProceedToConfirm1 a').click(function(e){
            e.preventDefault();
            $('form#frmShippingDetails').submit();
            $('form#frmShippingDetails input[name=apply]').val(0);
        });

        $('form#frmShippingDetails').livequery(function() {
            var returnUrl = $('form#frmShippingDetails input[name=returnUrl]').val();
            Util.setUpAjaxFormGeneral('frmShippingDetails', '', function(frmData){
                var apply = $('form#frmShippingDetails input[name=apply]').val();
                if (apply == 1){
                    var url = document.location;
                    Util.setJqFormFldValue(frmData, 'returnUrl', url);
                } else {
                    Util.setJqFormFldValue(frmData, 'returnUrl', returnUrl);
                }
            });
    	});

        $('#frmShippingDetails #fld_course_id').livequery('change', function(){
            var courseId = $(this).val();
            var wrapper = $(this).closest('#frmShippingDetails')
            cpt.quest.showHideFlds.call(this, courseId, wrapper);
        });

        $('#trainees .courseWrapper select').livequery('change', function(){
            var courseId = $(this).val();
            var wrapper = $(this).closest('.traineeFldsetWrapper')
            cpt.quest.showHideFlds.call(this, courseId, wrapper);
        });

        $('#trainees .courseWrapper').each(function(){
            var courseId = $(this).attr('course_id');
            var wrapper = $(this).closest('.traineeFldsetWrapper')
            cpt.quest.showHideFlds.call(this, courseId, wrapper);
        });

        $('.courseWrapperStudent').each(function(){
            var courseId = $(this).attr('course_id');
            var wrapper = $(this).closest('#frmShippingDetails')
            cpt.quest.showHideFlds.call(this, courseId, wrapper);
        });

        $('#frmShippingDetails .existingContact select').livequery('change', function(){
            Util.showProgressInd();
            var courseId = $(this).val();
            var wrapper = $(this).closest('.wrapper')
            var rand = $(wrapper).attr('id');
            var url = '/index.php?_theme=quest&_spAction=existingContactInfo&showHTML=0';
            $.getJSON(url, {contactId: courseId}, function(json){
                $('#fld_' + rand + 'first_name', wrapper).val(json.first_name);
                $('#fld_' + rand + 'last_name', wrapper).val(json.last_name);
                $('#fld_' + rand + 'email', wrapper).val(json.email);
                $('#fld_' + rand + 'gender', wrapper).val(json.gender);
                $('#fld_' + rand + 'id_card_no', wrapper).val(json.id_card_no);
                $('#fld_' + rand + 'nationality', wrapper).val(json.nationality);
                $('#fld_' + rand + 'race', wrapper).val(json.race);
                $('#fld_' + rand + 'date_of_birth', wrapper).val(json.date_of_birth);
                $('#fld_' + rand + 'school_highest_qual', wrapper).val(json.school_highest_qual);
                $('#fld_' + rand + 'position', wrapper).val(json.position);
                $('#fld_' + rand + 'salary_range', wrapper).val(json.salary_range);
                Util.hideProgressInd();
            });
        });
    },

    showHideFlds: function(courseId, wrapper){
        Util.showProgressInd();
        var url = '/index.php?_theme=quest&_spAction=courseTypeById&showHTML=0';
        $.getJSON(url, {courseId: courseId}, function(json){
            var group = json.group;
            var course_code = json.course_code;
            if (!group){
                return;
            }

            var langObj = $('.language select', wrapper);

            if (course_code == 'FHC') {
                var thevalue = 'Malay';
                var exists = 0 != $('option[value='+thevalue+']', langObj).length;
                
                if (!exists){
                    langObj.append(
                        $('<option></option>').val('Malay').text('Malay')
                    );
                }
            } else {
                $('option[value=Malay]', langObj).remove();
            }
            
            if (group == 'CMI'){
                $('.wsqOnly', wrapper).hide();
                $('.cmiOnly', wrapper).show();
            }

            if (group == 'WSQ'){
                $('.wsqOnly', wrapper).show();
                $('.cmiOnly', wrapper).hide();
            }
        });
        Util.hideProgressInd();
    }
}