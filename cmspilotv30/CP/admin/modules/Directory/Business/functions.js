Util.createCPObject('cpm.directory.business');

cpm.directory.business = {
    init: function(){
        $('.directory_business__directory_businessHoursLink .copyHours a')
        .click(cpm.directory.business.copyBusinessHours);
        $('#actBtn_bulkPromotion').click(cpm.directory.business.bulkPromotion);
        $('#actBtn_bulk3rdPartyPromotion').click(cpm.directory.business.bulk3rdPartyPromotion);
        $('#fld_address_id_hidden').change(cpm.directory.business.getAddressRecord);

        var exp = {extraDestFlds: ['fld_sub_category2_id', 'fld_sub_category3_id']}
        $('#frmEdit select#fld_category_id').livequery('change', function(){
            Util.loadDropdownByJSON('category_id', $(this).val(),
                                    'fld_sub_category_id',
                                    'webBasic_subCategory', '', exp);
        });

        cpm.directory.business.showHideFields();
    },

    showHideFields: function(){
        //------------------//
        var sel = '#frmEdit input[name=feature_parking],'
                + '#frmEdit input[name=feature_private_room],'
                + '#frmEdit input[name=feature_dress_code],'
                + '#frmEdit input[name=feature_byob],'
                + '#frmEdit input[name=feature_tv]';
        $(sel).click(function(){
            var selected = $(this).val();
            var seltr = $(this).parents('div.form-row-wrapper').next();
            if (selected == 1) {
                $(seltr).slideDown();
            } else {
                $(seltr).find('select').val('');
                $(seltr).find('input').val('');
                $(seltr).slideUp();
            }
        });
    },

    getAddressRecord: function(e){
        var address_id_hidden = $(this).val();

        var url = 'index.php?module=directory_address&_spAction=addressRecord&showHTML=0';
        $.get(url, {record_id: address_id_hidden}, function (data) {
            $('#fld_address_id_hidden').val(data.address_id);
            $('#fld_address_id .value').html(data.country_code_address_id);
            $('#fld_country_name .value').html(data.country_title);
            $('#fld_state_name .value').html(data.state_title);
            $('#fld_city_name .value').html(data.city_title);
            $('#fld_borough_name .value').html(data.borough_title);
            $('#fld_area_name .value').html(data.area_title);
            $('#fld_street_name .value').html(data.street_title);
            $('#fld_shop_center_name .value').html(data.shop_center_title);
            $('#fld_address_street_no_from .value').html(data.address_street_no_from);
            $('#fld_address_street_no_to .value').html(data.address_street_no_to);
            $('#fld_address_building_name .value').html(data.address_building_name);
            $('#fld_address_block .value').html(data.address_block);
            $('#fld_address_floor_from .value').html(data.address_floor_from);
            $('#fld_address_floor_to .value').html(data.address_floor_to);
            $('#fld_address_unit_from .value').html(data.address_unit_from);
            $('#fld_address_unit_to .value').html(data.address_unit_to);
            $('#fld_address_po_code .value').html(data.address_po_code);
        });
    },

    copyBusinessHours: function(e){
        e.preventDefault();
        var firstRow = '.directory_business__directory_businessHoursLink ' +
                       '.linkPortalDataWrapper table tbody tr:first';
        var start_time = $(firstRow).find('.start-time input').val();
        var start_time2 = $(firstRow).find('.start-time2 input').val();
        var end_time2 = $(firstRow).find('.end-time2 input').val();
        var end_time = $(firstRow).find('.end-time input').val();

        var otherRows = '.directory_business__directory_businessHoursLink ' +
                        '.linkPortalDataWrapper table tbody tr:gt(0)';
        $(otherRows).each(function() {
            var x = $(this).find('.start-time input');
            $(this).find('.start-time input').val(start_time);
            $(this).find('.start-time2 input').val(start_time2);
            $(this).find('.end-time2 input').val(end_time2);
            $(this).find('.end-time input').val(end_time).change();
        });
    },

    duplicateAndClose: function() {
        if (!confirm("Are you sure you want to move this business?")){
            return;
        }

        var business_id = $('#record_id').val();
        var room = $('#cpRoom').val();
        var topRoom = $('#cpTopRoom').val();
        var url = 'index.php?module=directory_business&_spAction=duplicateAndCloseBusiness&showHTML=0';

        var data = {
             record_id: business_id
            ,room: room
            ,topRoom: topRoom
        };
        Util.showProgressInd();
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                Util.hideProgressInd();
                return;
            }
            document.location = json.returnUrl;
        }, 'json');
    },

    close: function() {
        if (!confirm("Are you sure you want to close the business?")){
            return;
        }

        var business_id = $('#record_id').val();
        var room = $('#cpRoom').val();
        var topRoom = $('#cpTopRoom').val();
        var url = 'index.php?module=directory_business&_spAction=archiveBusiness&showHTML=0';

        var data = {
             record_id: business_id
            ,room: room
            ,topRoom: topRoom
        };
        Util.showProgressInd();
        $.post(url, data, function (json) {
            if (json.status == 'error') {
                Util.alert(json.errorMsg);
                Util.hideProgressInd();
                return;
            }
            document.location = json.returnUrl;
        }, 'json');
    },

    bulkPromotion: function(e) {
        e.preventDefault();
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true
           ,submitBtnText: 'Create Promotions'
           ,callbackOnSuccess: function(){
                var msg = 'Promotions created successfully.';
                Util.alert(msg, function() {
                    Util.closeAllDialogs();
                    document.location = document.location;
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 650, 650, expObj);
    },

    bulk3rdPartyPromotion: function(e) {
        e.preventDefault();
        var title = $(this).attr('dialogTitle');

        e.preventDefault();
        var expObj = {
            validate: true
           ,submitBtnText: 'Create 3rd Party Promotions'
           ,callbackOnSuccess: function(){
                var msg = 'Promotions created successfully.';
                Util.alert(msg, function() {
                    Util.closeAllDialogs();
                    document.location = document.location;
                });
            }
        }
        Util.openFormInDialog.call(this, 'portalForm', title, 650, 650, expObj);
    }

}