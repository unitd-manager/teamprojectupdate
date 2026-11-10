<?
class CP_Admin_Modules_Logistics_VehicleLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList() {
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $expVl = array('sqlType' => 'OneField');
        $sqlModel       = $fn->getValueListSQL('vehicleModel');
        $sqlStatus       = $fn->getValueListSQL('vehicleStatus');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Vehicle Code', 'vehicle_code')}
                {$formObj->getTBRow('Vehicle Name', 'vehicle_name')}
                {$formObj->getDateRow('date', 'vehicle_date')}
                {$formObj->getDDRowBySQL('Model', 'vehicle_model', $sqlModel, '', $expVl)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $expVl)}
            </fieldset>
            <input type='hidden' name='booking_id' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $expVl = array('sqlType' => 'OneField');
        $sqlModel       = $fn->getValueListSQL('vehicleModel');
        $sqlStatus       = $fn->getValueListSQL('vehicleStatus');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('vehicle', 'vehicle_id', $id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Vehicle Code', 'vehicle_code', $row['vehicle_code'])}
                {$formObj->getTBRow('Vehicle Name', 'vehicle_name', $row['vehicle_name'])}
                {$formObj->getDateRow('Date', 'vehicle_date', $row['vehicle_date'])}
                {$formObj->getDDRowBySQL('Model', 'vehicle_model', $sqlModel, $row['vehicle_model'], $expVl)}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expVl)}
            </fieldset>
            <input type='hidden' name='vehicle_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
