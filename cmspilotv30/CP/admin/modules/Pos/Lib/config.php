<?
$cpCfg = array();
$cpCfg['gotAuthorisation'] = 0;

$cpCfg['m.pos.globalSettings.groups'] = array(
    'Global' => array(
        'Numeric Screen' => array(
            'flds' => array('description', 'value')
        )
        ,'Date Format'  => array(
            'flds' => array('description', 'value')
        )
        ,'Document Prefix Setup' => array(
            'flds' => array('description', 'prefix', 'starting_no', 'length', 'add_shop_code', 'auto_generate_no', 'add_separator', 'reset_next_year')
        )
        ,'Member' => array(
            'flds' => array('description', 'prefix', 'length', 'auto_generate_no', 'add_separator')
        )
        ,'Vendor' => array(
            'flds' => array('description', 'prefix', 'starting_no', 'length', 'auto_generate_no', 'add_separator')
        )
        ,'Invoice' => array(
            'flds' => array('description', 'value')
        )
        /*,'Minimum Wage Ordinance (MWO) Setup' => array(
            'flds' => array('description', 'value')
        )*/
    )
    
    ,'System' => array(
        'Interface Setup' => array(
            'flds' => array('description', 'value')
        )
        ,'Product'  => array(
            'flds' => array('description', 'value')
        )
        ,'Invoice' => array(
            'flds' => array('description', 'value')
        )
        ,'Invoice Printout' => array(
            'flds' => array('description', 'value')
        )
        ,'Member' => array(
            'flds' => array('description', 'value')
        )
        ,'Day End Time Control Setting' => array(
            'flds' => array('description', 'value')
        )
    )
);

$cpCfg['m.pos.globalSettings.flds'] = array(
    'description' => array(
         'fldKey' => 'description'
        ,'title' => 'Description'
    )
    ,'value' => array(
         'fldKey' => 'value'
        ,'title' => 'Value'
    )
    ,'prefix' => array(
         'fldKey' => 'value'
        ,'title' => 'Prefix'
    )
    ,'starting_no' => array(
         'fldKey' => 'starting_no'
        ,'title' => 'Starting No'
    )
    ,'length' => array(
         'fldKey' => 'length'
        ,'title' => 'Length'
    )
    ,'add_shop_code' => array(
         'fldKey' => 'add_shop_code'
        ,'title' => 'Add Shop Code'
    )
    ,'add_separator' => array(
         'fldKey' => 'add_separator'
        ,'title' => 'Add Separator'
    )
    ,'reset_next_year' => array(
         'fldKey' => 'reset_next_year'
        ,'title' => 'Reset at next year'
    )
    ,'auto_generate_no' => array(
         'fldKey' => 'auto_generate_no'
        ,'title' => 'Auto Generate Number'
    )
);


$cpCfg['m.pos.globalSettings.valueTypeArr'] = array(
     'Cost Method'
    ,'Currency'
    ,'Date Format'
    ,'Days'
    ,'Enable or Disable'
    ,'Hours'
    ,'Multi Payments'
    ,'Operator'
    ,'Print or Not Print'
    ,'Rounding'
    ,'Text Area'
    ,'Text Field'
    ,'Time Format'
    ,'Yes or No'
    ,'Minutes'
);

$cpCfg['m.pos.valuelist.recordTypeArr'] = array(
     'style'        => 'Style'
    ,'color'        => 'Color'
    ,'size'         => 'Size'
    ,'season'       => 'Season'
    ,'brand'        => 'Brand'
    ,'element'      => 'Element'
    ,'shopStatus'   => 'Shop Status'
    ,'discountType' => 'Discount Type'
    ,'giftType'     => 'Gift Type'
    ,'packageType'  => 'Package Type'
    ,'packageStatus'=> 'Package Status'
    ,'gender'       => 'Gender'
    ,'regional'     => 'Regional'
    ,'productStatus' => 'Product Status'
    ,'operator'     => 'Operator'
    ,'mixRules'     => 'Mix Rules'
    ,'mixAmountRequired' => 'Mix Amount Required'
    ,'paymentType'  => 'Payment Type'
    ,'vendorStatus' => 'Vendor Status'
    ,'cash' => 'Cash'
    ,'discountOption' => 'Discount Option'
    ,'purchaseOrderStatus' => 'Purchase Order Status'
);

return $cpCfg;

