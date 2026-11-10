<?
class CP_Admin_Modules_Logistics_ResourceLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
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

        $sqlStatus = $fn->getValueListSQL('resourceStatus');
        $sqlCategory = $fn->getValueListSQL('resourceCategory');
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Resource Code', 'resource_code')}
                {$formObj->getTBRow('Resource Name', 'resource_name')}
                {$formObj->getTBRow('Role', 'role')}
                {$formObj->getTBRow('Email', 'email')}
                {$formObj->getTBRow('Phone', 'phone')}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, '', $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, '', $exp)}
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

        $sqlStatus = $fn->getValueListSQL('resourceStatus');
        $sqlCategory = $fn->getValueListSQL('resourceCategory');
        $exp = array('sqlType' => 'OneField');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('resource', 'resource_id', $id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Resource Code', 'resource_code', $row['resource_code'])}
                {$formObj->getTBRow('First Name', 'resource_name', $row['resource_name'])}
                {$formObj->getTBRow('Role', 'role', $row['role'])}
                {$formObj->getTBRow('Email', 'email', $row['email'])}
                {$formObj->getTBRow('Phone', 'phone', $row['phone'])}
                {$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $exp)}
                {$formObj->getDDRowBySQL('Category', 'category', $sqlCategory, $row['category'], $exp)}
            </fieldset>
            <input type='hidden' name='resource_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
