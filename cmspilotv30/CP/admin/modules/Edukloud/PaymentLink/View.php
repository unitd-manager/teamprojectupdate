<?
class CP_Admin_Modules_Edukloud_PaymentLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{

    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');
        $db = Zend_Registry::get('db');
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $mediaArray = Zend_Registry::get('mediaArray');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows       = '';
        $rowCounter = 0;
        
        
        if ($linkRecType == 'notLinked'){
            foreach ($dataArray as $row){
                $rows .= "
                {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
                {$listObj->getListDataCell($row['contact_name'])}
                {$listObj->getListDataCell($row['email'])}
                {$listObj->getListDataCell($row['c_company_name'])}
                {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
                ";
                $rowCounter++ ;
            }
    
            $text = "
            {$listLinkObj->getListHeaderLink()}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'contact_name')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'a.email')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Company Name', 'b.company_name')}
            {$listLinkObj->getListHeaderEndLink($linkRecType)}
            {$rows}
            {$listLinkObj->getListFooterLink()}
            ";
        } else {
            foreach ($dataArray as $row){
                $rows .= "
                {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
                {$listObj->getListDataCell($row['contact_name'])}
                {$listObj->getListDataCell($row['email'])}
                {$listLinkObj->getListRowEndLink($linkRecType, $row['contact_id'])}
                ";
                $rowCounter++ ;
            }
    
            $text = "
            {$listLinkObj->getListHeaderLink()}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Name', 'contact_name')}
            {$listLinkObj->getListHeaderCellLink($linkRecType,'Email', 'a.email')}
            {$listLinkObj->getListHeaderEndLink($linkRecType)}
            {$rows}
            {$listLinkObj->getListFooterLink()}
            ";
        }
        
        return $text;
    }

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
                {$formObj->getDateRow('Payment Date', 'payment_date')}
                {$formObj->getTBRow('Amount', 'amount')}
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
        $row = $fn->getRecordRowByID('payment', 'payment_id', $id);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getDateRow('Payment Date', 'payment_date', $row['payment_date'])}
                {$formObj->getTBRow('Amount', 'amount', $row['amount'])}
            </fieldset>
            <input type='hidden' name='payment_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
