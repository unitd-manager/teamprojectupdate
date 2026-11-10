<?
class CP_Admin_Modules_AgileIms_BatchDateLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
    */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'batch_date')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
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

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('batch_date', 'batch_date_id', $id);
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Date', 'batch_date', $row['batch_date'])}
            </fieldset>
            <input type='hidden' name='batch_date_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
