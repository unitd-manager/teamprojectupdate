Util.createCPObject('cpt.pos');

cpt.pos.init = function(){
    if ($('.tplLogin').length > 0){
        var toSubtract = $('#header').outerHeight(true) + $('#footer').outerHeight(true);
        var mainPanelHt = $(window).height() - toSubtract - 35;
        $('#col3_content').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
        $("#col3_content #loginOuter").cp_center();
        
        $('button#btnSmartCard').click(function(e){
            e.preventDefault();
            var opts = {
                url: 'index.php?_theme=pos&_spAction=smartCardLoginForm&showHTML=0'
            }
            Util.setUpAjaxFormGeneral('frmSmartLogin')
            var formName = 'frmSmartLogin';

            $('#' + formName).livequery(function() {
                /****************************************************/
                var extraPar = {};
                var cpCSRFToken = $('#cpCSRFToken').val();
    
                var additionalData = {
                    cpCSRFToken: cpCSRFToken
                };
    
                var options = {
                    success: function(json, statusText, xhr, jqFormObj) {
                        if(json.errorCount){
                            $('#smartIdErr').html(json.errors.smart_card_id.msg);
                        } else {
                            document.location = json.returnUrl;
                        }
                        $('#smartCardId').val('');
                        Util.hideProgressInd();
                    },
                    beforeSubmit: function(frmData) {
                        $('#smartIdErr').html('');
                        Util.showProgressInd();
                    }
                    ,data: additionalData
                    ,dataType: 'json'
                };
    
                $('#' + formName).ajaxForm(options);
    
            });

            Util.openDialogForLink.call(this, 'Swipe your smart card', 200, 120, false, opts);
        });
        
    } else {
        var toSubtract1 = parseInt($('#header').outerHeight(true));
        var toSubtract2 = parseInt($('#main').css('padding-top')) + parseInt($('#main').css('padding-bottom'));
        var toSubtract3 = parseInt($('#col3_content').css('padding-top')) + parseInt($('#col3_content').css('padding-bottom'));
        var mainPanelHt = $(window).height() - toSubtract1 - toSubtract2 - toSubtract3 - 50;
        $('.contentScroller').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
        $('#col1').css({'height' : mainPanelHt + 'px', overflow: 'auto', 'overflow-x': 'hidden'});
    }

    $('.contentScroller, .m-common_dashboard .widget div.tableOuter').addClass('scroll-pane');
    $('.scroll-pane').jScrollPane(
        {autoReinitialise: true}
    );

    $('#settings .header').click(function(){
        var pane = $(this).next();
        $('#settings .pane').not(pane).hide()
        pane.toggle('slow');
    });    
    
    var hash = location.hash;
    if (hash != ""){
        hash = hash.substring(1)
        var hashArr = hash.split("/")
        if (hashArr[0]){
            var groupName = hashArr[0];
            var parent = $("#settings div.outer[group='" + groupName + "']");
            $('.pane', parent).show('slow');
        }
    }
    
    $('a.nav_common_dashboard').closest('li').remove();

    $('select#fld_shop_id').livequery('change', function() {
        var url = 'index.php?_theme=pos&_spAction=terminalsByShopJSON&showHTML=0';
        var shop_id = $(this).val();
        $.get(url, {shop_id: shop_id}, function (data) {
            $('#fld_terminal_id').cp_loadSelect(data);
        }, 'json');
    });
    $('#actBtn_bulkMoveSubCat').click(cpt.pos.bulkMoveSubCat);
    $('#actBtn_delivery').click(cpt.pos.delivery);
    
    $('form#chooseUsergroupForm').livequery(function() {
        Util.setUpAjaxFormGeneral('chooseUsergroupForm', '', function(){
            var shopChosen = $("#chooseUsergroupForm #fld_shop_id option:selected").text();
            var terminalChosen = $("#chooseUsergroupForm #fld_terminal_id option:selected").text();
            
            var msg = 'Are you sure to proceed with the options below?\n\nShop: ' + shopChosen + '\nTerminal: ' + terminalChosen;
            
            if (!confirm(msg)){
                return false;
            }
        });
	});
}

cpt.pos.bulkMoveSubCat = function(e){
    e.preventDefault();
    var title = $(this).attr('dialogTitle');

    var expObj = {
        validate: true
       ,submitBtnText: 'Move to New Category'
       ,callbackOnSuccess: function(){
            var msg = 'Moved successfully.';
            Util.alert(msg, function() {
                Util.closeAllDialogs();
                document.location = document.location;
            });
        }
    }
    Util.openFormInDialog.call(this, 'portalForm', title, 650, 650, expObj);        
}

cpt.pos.delivery = function (){
    var rowID = $('#record_id').val();
    
    var poCode = $('#fld_po_code').val();
    var shopTitle = $("#fld_shop_id option:selected").text();

    var msg = 'Are you sure to delivery the Purchase Order No: ' + poCode + ' to Location ' + shopTitle + '?' + 
              ' Once the Purchase Order is process, NEVER can be Edit or delete';
               
    if (!confirm(msg)){
        return false;
    } else {                
        var url = "index.php?_topRm=coreMenu&module=pos_purchaseOrder&_spAction=deliveryUpdate&showHTML=0&row_id=" + rowID;
        $.get(url,{rowID: rowID}, function(status){
            $('#fld_status span').html(status)
            Util.alert('status updated successfully');
        });
    } 
    
}
