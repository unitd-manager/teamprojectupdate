Util.createCPObject('cpm.enggCrm.vehicle');

cpm.enggCrm.vehicle = {
    init : function(){
		
		$("a.addActualCharge").livequery('click', function (e){
            var vehicle_id = $(this).attr('vehicle_id');

            var url = 'index.php?_topRm=main&module=enggCrm_vehicle&_spAction=addActualCharge'
                    + '&showHTML=0&vehicle_id='+vehicle_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Fuel charges added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Fuel', 550, 500, exp);
        });

        $("a.addRenewalDate").livequery('click', function (e){
            var vehicle_id = $(this).attr('vehicle_id');

            var url = 'index.php?_topRm=main&module=enggCrm_vehicle&_spAction=addRenewalDate'
                    + '&showHTML=0&vehicle_id='+vehicle_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Insurance added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Insurance', 550, 500, exp);
        });


        $("a.addService").livequery('click', function (e){
            var vehicle_id = $(this).attr('vehicle_id');

            var url = 'index.php?_topRm=main&module=enggCrm_vehicle&_spAction=addService'
                    + '&showHTML=0&vehicle_id='+vehicle_id;
            var exp = {
                url: url
               ,callbackOnSuccess: function(){
                    Util.closeAllDialogs();

                    var mgsalert = 'Service added successfully!';
                    var n = noty({
                        text: mgsalert,
                        type: 'confirm',
                        dismissQueue: true,
                        layout: 'topCenter',
                        theme: 'defaultTheme',
                        timeout: 5000,
                    });
                }
            };

            Util.openFormInDialog.call(this, 'actualChargeForm', 'Add Service', 550, 500, exp);
        });

	}
}