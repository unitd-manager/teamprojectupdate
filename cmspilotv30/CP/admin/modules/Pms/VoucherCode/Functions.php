<?
class CP_Admin_Modules_Pms_VoucherCode_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_voucherCode');
        $modObj['tableName'] = 'voucher_code';
        $modObj['keyField']  = 'voucher_code_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Voucher Code'
        ));
    }
}