<?
class CPL_Admin_Widgets_EnggCrm_MaterialDelivered_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $month   = $fn->getReqParam('month');

        $text = "
        <h2 class='ui-widget-header ui-corner-top'>
            <div class='floatbox invoiceSummaryfilter'>
                <div class='float_left'>
                    Material delivered list
                </div>
                
            </div>
        </h2>
        
        {$this->getRowsHTML()}
        ";

        return $text;
    }

    
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil   = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $rows = '';
        $class = '';
        
        $SQL = "
        SELECT po.*
               ,p.project_code
               ,p.title
        FROM purchase_order po
        LEFT JOIN project p ON (po.project_id = p.project_id)
        WHERE po.purchase_order_id != ''
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        
        while ($row = $db->sql_fetchrow($result)){
            $materials_details = $this->getMaterialsDetails($row['purchase_order_id']);
 
            $materialsDetailsRow = "
            <tr>
                <td></td>
                <td class='materialsDetailsMain' colspan='3'>{$materials_details}</td>
            </tr>
            ";
        
            $rows .= "
             <tbody class='projectSummary'>
			   <tr>
                <td>{$row['project_code']}</td>
				<td>{$row['delivery_date']}</td>
                <td>{$row['po_code']}</td>
                <td class='bold'>{$row['title']}</td>
			  </tr>
              {$materialsDetailsRow}
            </tbody>
            ";                
        }
        $text = "
        <div class = 'tableOuter scroll-pane'>
            <table class='thinlist  mt10'>
                <thead>
                    <tr class='even'>
                        <th>Project Code</th>
                        <th>Delivery Date</th>
                        <th>Po Code</th>
                        <th>Project title</th>
                    </tr>
                </thead>
                {$rows}
            </table>
        </div>
        ";


        return $text;
    }

    /**
     *
     */
    function getMaterialsDetails($purchase_order_id = '') {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $fn       = Zend_Registry::get('fn');

        $status  = $fn->getReqParam('status');

        if($purchase_order_id == ''){
            $purchase_order_id = $fn->getReqParam('purchase_order_id');
        }

        $rows  = '';
        $class = '';
   
        $SQL = "
        SELECT pop.*
               ,s.company_name

        FROM po_product pop
        LEFT JOIN supplier s ON (s.supplier_id = pop.supplier_id)
         WHERE pop.purchase_order_id = '{$purchase_order_id}'
        ";
        $result  = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)){
            $rows .= "
            <tr>
                <td>{$row['item_title']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['cost_price']}</td>
                <td>{$row['unit']}</td>
                <td>{$row['qty']}</td>
            </tr>
            ";
        }

        $text = "
        <div class='materialsDetails mt5'>
            <table>
                <tr bgcolor='#dddddd'>
                    <td> Title</td>
                    <td>Name</td>
                    <td>Amount</td>
                    <td>UOM</td>
                    <td>QTY</td>
                </tr>
                {$rows}
            </table>
        </div>
        ";


        return $text;
    }



}