<?
class CP_Admin_Modules_Ecommerce_ProductItemLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNewPortal(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=addPortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');

        $row = $fn->getRecordRowByID('product_item', 'product_item_id', $id);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('SKU No', 'sku_no', $row['sku_no'])}
                {$formObj->getTBRow('Color', 'color_code', $row['color_code'])}
                {$formObj->getTBRow('Size', 'size', $row['size'])}
                {$formObj->getTBRow('Stock', 'stock', $row['stock'])}
            </fieldset>
            <input type='hidden' name='product_item_id' value='{$id}' />
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

        $formAction = "index.php?_spAction=savePortal&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $id = $fn->getReqParam('id');

        $row = $fn->getRecordRowByID('product_item', 'product_item_id', $id);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('SKU No', 'sku_no', $row['sku_no'])}
                {$formObj->getTBRow('Color', 'color_code', $row['color_code'])}
                {$formObj->getTBRow('Size', 'size', $row['size'])}
                {$formObj->getTBRow('Stock', 'stock', $row['stock'])}
            </fieldset>
            <input type='hidden' name='product_item_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

}