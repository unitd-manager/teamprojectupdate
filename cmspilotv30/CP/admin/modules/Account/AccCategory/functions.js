Util.createCPObject('cpm.account.accCategory');

cpm.account.accCategory.init = function(){
    $("#addNewMenu").live('click', function(e){
        e.preventDefault();

        var expObj = {
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                cpm.account.accCategory.reloadMenu();
            }
        }

        Util.openFormInDialog.call(this, 'menuForm', "New Menu Item", 500, 330, expObj);

    });

    $("#menu .tree li a").live('click', function(e){
        e.preventDefault();

        var id = $(this).attr('id');
        var expObj = {
            callbackOnSuccess: function(){
                Util.closeAllDialogs();
                cpm.account.accCategory.reloadMenu();
            }
            ,onOpenFn: function(){
                $('#dialog').dialog('addbutton', 'Delete Item', function(){
                    var msg = "Are you sure to delete this item?";
                    if (confirm(msg)){
                        Util.showProgressInd();

                        var url = "index.php?room=account_accCategory&_spAction=deleteRecordByID&record_id=" + id + "&showHTML=0";
                        $.get(url, function(){
                            $('#dialog').dialog('close');
                            $('#dialog').dialog('destroy');
                            Util.closeAllDialogs();
                            cpm.account.accCategory.reloadMenu();
                        });
                    }
                });
            }
        }

        var obj = Util.openFormInDialog.call(this, 'menuForm', "Edit Menu Item", 500, 330, expObj);

    });

    $("#menu .tree11 li a").livequery('click', function(e){
        e.preventDefault();

        Util.showProgressInd();
        var url = $(this).attr('href');
        var menu_id = $(this).attr('id');

        $.get(url, function(data){
            Util.initDialog();
            $('#dialog').html(data);

            var xButtons = {};

            xButtons['Submit'] = function() {
                $('#menuForm').submit();
            };

            xButtons['Cancel'] = function() {
                $(this).dialog('close');
                $(this).dialog('destroy');
            };

            xButtons['Delete'] = function() {

                var msg = "Are you sure to delete this item?";
                if (confirm(msg)){
                    Util.showProgressInd();
                    var url = "index.php?_room=menu&_spAction=deleteMenuItem&menu_id=" + menu_id + "&showHTML=0";
                    $.get(url, function(){
                        $('#dialog').dialog('close');
                        $('#dialog').dialog('destroy');
                        Menu.reloadPortal();
                    });
                }
            };

            var x_dialog = $('#dialog').dialog(
                $.extend(Util.dialogDefaults, {
                    width: 500,
                    height: 335,
                    title: 'Edit / Delete Menu Item',
                    buttons: xButtons
                })
                );

            Util.hideProgressInd();
        });


        Dialog.setUpForm('menuForm', function(){
            $('#dialog').dialog('close');
            Menu.reloadPortal();
        });
    });

    $("#menu .tree").livequery(function(){
        $(this).treeview();
    });
}

cpm.account.accCategory.reloadMenu = function(){
    url = "index.php?module=account_accCategory&_spAction=listItems&showHTML=0";

    $.get(url, function(data){
        $('#menuList').hide();
        $('#menuList').html(data);
        $('#menuList').slideDown('slow');
        Util.hideProgressInd();
    });
}