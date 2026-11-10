<?
class CPL_Admin_Widgets_EnggCrm_ProjectMaterialTransferred_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        return $text;
    }

    /**
     *
     */
    function getMaterialTransferredPortal($project_id='') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == "") {
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT st.*
              ,p.title
              ,p.price
        FROM stock_transfer st
        LEFT JOIN (product p) ON (p.product_id = st.product_id)
        WHERE st.to_project_id = {$project_id}
        ORDER BY st.creation_date ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $updation_details = $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            $projRec = $fn->getRecordRowByID('project', 'project_id', $row['from_project_id']);
            $proTitle = "<a href='index.php?module=enggCrm_project&record_id={$row['from_project_id']}&_action=edit' target='_blank'><u>{$projRec['title']}</u></a>";

            $rows .= "
            <tr>
                <td>{$proTitle}</td>
                <td>{$row['title']}</td>
                <td>{$row['quantity']}</td>
                <td>{$updation_details}</td>
            </tr>
            ";
        }

        $text = "
        <div id='materialsTransferPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='9' align='left' class='rightPanelHeading'>Materials Transferred From Other Projects</th>
                    </tr>
                    <tr>
                        <th>Ref Project</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Updated By</th>
                    </tr>
                </thead>
                <tbody class='materialsDetailRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }
}