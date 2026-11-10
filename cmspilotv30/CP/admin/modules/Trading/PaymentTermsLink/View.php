<?
class CP_Admin_Modules_Trading_PaymentTermsLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $expVl = array('sqlType' => 'OneField');
        
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        
        $expPaymentTerms = getCPModelObj('core_valuelist')
                           ->getValueListFieldParamArr('paymentTerms');
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTARow('Payment Terms', 'description', '', $expPaymentTerms)}
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
        $row = $fn->getRecordRowByID('payment_terms', 'payment_terms_id', $id);
        
        $expPaymentTerms = getCPModelObj('core_valuelist')
                           ->getValueListFieldParamArr('paymentTerms');
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTARow('Payment Terms', 'description', $row['description'], $expPaymentTerms)}
            </fieldset>
            <input type='hidden' name='payment_terms_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
