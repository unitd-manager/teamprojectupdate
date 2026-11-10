<?
class CP_Admin_Modules_Tradingsg_DiscountLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $listLinkObj = Zend_Registry::get('listLinkObj');

      }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $sqlProductGroup = "SELECT product_group_id, title FROM product_group";
        $result = $db->sql_query($sqlProductGroup);
        $row    = $db->sql_fetchrow($result);

        $expEdit = array('isEditable' => 0);
        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        if ($row['product_group_id'] != ''){
            $modCat = getCPModuleObj('webBasic_category');
            $sqlCategory = $modCat->model->getCategorySQLByType('Product');
        }

        $discount = '';
        if ($cpCfg['m.tradingsg.discountLink.showDiscount']) {
            $discount = "
            {$formObj->getTBRow('Discount %', 'discount_percent')}
            ";
        }

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                {$formObj->getDDRowBySQL('Product Group', 'product_group_id', $sqlProductGroup)}
                {$formObj->getDDRowBySQL('Category', 'category_id', $sqlCategory)}
                {$formObj->getTBRow('Service Cost %', 'margin')}
                {$discount}
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $expEdit = array('isEditable' => 0);

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('discount', 'discount_id', $id);

        if ($row['product_group_id'] != ''){
            $programGrp = $fn->getRecordRowByID('product_group', 'product_group_id', $row['product_group_id']);
            $programGrpRow = $formObj->getTextBoxRow('Product Group', 'product_group_id', $programGrp['title'], $expEdit);
        }

        $categoryRow = '';
        if ($row['category_id'] != ''){
            $categoryGrp = $fn->getRecordRowByID('category', 'category_id', $row['category_id']);
            $categoryRow = $formObj->getTextBoxRow('Category', 'category_id', $categoryGrp['title'], $expEdit);
        }

        $discount = '';
        if ($cpCfg['m.tradingsg.discountLink.showDiscount']) {
            $discount = "
            {$formObj->getTBRow('Discount %', 'discount_percent', $row['discount_percent'])}
            ";
        }

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                <div class='error_box'>{$formObj->getTBRow('', "error_box", '', $expEdit)}</div>
                {$programGrpRow}
                {$categoryRow}
                {$formObj->getTBRow('Service Cost %', 'margin', $row['margin'])}
                {$discount}
            </fieldset>
            <input type='hidden' name='discount_id' value='{$id}' />
            <input type='hidden' name='company_id' value='{$row['company_id']}' />
        </form>
        ";

        return $text;
    }
}
