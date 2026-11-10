<?
class CP_Admin_Modules_EzTrade_BankLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{

    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('description', 'Please enter the description');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getNewPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addPortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTextAreaRow('Customer Bank', 'description')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=savePortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('bank', 'bank_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTextAreaRow('Customer Bank', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='bank_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'description');

        return $fa;
    }
}
