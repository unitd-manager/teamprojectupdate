<?
class CP_Admin_Modules_Accountsg_BalanceSheet_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');

        $text = "
        <table class='balanceSheetOuter'>
            <thead>
                <tr>
                    <th colspan='2' class='txtCenter'>LIABILITY</th>
                    <th colspan='2' class='txtCenter'>ASSET</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    {$this->getLiabilityItems()}
                    {$this->getAssetItems()}
                </tr>
            </tbody>
        </table>
        ";
        return $text;
    }

    /**
     *
     */
    function getAssetItems() {
        $db = Zend_Registry::get('db');

        /*
        $sql = "";
        $result = $db->sql_query($sql);
        $rows = "";
        $total_asset_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td class='assetItem'>ICICI</td>
                <td class='assetAmt txtRight'>6400</td>
            </tr>
            ";
            $total_asset_amount += ;
        }

        $text = "
        <table class='balanceSheetOuter'>
            {$rows}
            <tr>
                <td>TOTAL</td>
                <td></td>
            </tr>
        </table>
        ";
        */


        $text = "
        <td class='assetItem'>ICICI</td>
        <td class='assetAmt txtRight'>6400</td>
        ";
        return $text;
    }

    /**
     *
     */
    function getLiabilityItems() {
        $db = Zend_Registry::get('db');

        /*
        $sql = "";
        $result = $db->sql_query($sql);
        $rows = "";
        $total_asset_amount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr>
                <td class='assetItem'>ICICI</td>
                <td class='assetAmt txtRight'>6400</td>
            </tr>
            ";
            $total_asset_amount += ;
        }

        $text = "
        <table class='balanceSheetOuter'>
            {$rows}
            <tr>
                <td>TOTAL</td>
                <td></td>
            </tr>
        </table>
        ";
        */


        $text = "
        <td class='liabilityItem'>BSNL</td>
        <td class='liabilityAmt txtRight'>0</td>
        ";
        return $text;
    }
}
