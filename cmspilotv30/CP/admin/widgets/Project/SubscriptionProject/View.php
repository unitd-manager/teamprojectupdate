<?
class CP_Admin_Widgets_Project_SubscriptionProject_View extends CP_Common_Lib_WidgetViewAbstract
{

    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $sqlMaster = Zend_Registry::get('sqlMaster');

        $still_to_bill_sql = "(
            SELECT SUM(invoice_amount)
            FROM invoice i
            WHERE i.project_id = p.project_id
              AND LOWER(i.status) != 'cancelled'
        )
        ";

        $SQL = "
        SELECT p.project_id
              ,p.title
              ,p.project_code
              ,c.company_name
              ,(p.project_value - (IF(ISNULL({$still_to_bill_sql}),0, {$still_to_bill_sql}))) AS still_to_bill
              ,i.invoice_code
              ,i.invoice_id
        FROM invoice i
        LEFT JOIN (project p)   ON (p.project_id  = i.project_id)
        LEFT JOIN (company c)   ON (p.company_id  = c.company_id)
        WHERE (p.category LIKE '%Subscription%' OR p.category = 'Monthly AMC')
        AND LOWER(i.status) IN('due', 'late')
        ";

        /*$SQL = "
        SELECT p.*
            ,c.company_name
            ,(p.project_value - (IF(ISNULL({$still_to_bill_sql}),0, {$still_to_bill_sql}))) AS still_to_bill
            ,i.invoice_code
            ,i.invoice_id

        FROM project p
        LEFT JOIN (company c)   ON (p.company_id  = c.company_id)
        LEFT JOIN (invoice i)   ON (i.project_id  = p.project_id)
        WHERE p.category LIKE '%Subscription%' 
        ";*/
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {

            $still_to_bill = number_format($row['still_to_bill'], 2);
            $urlInvoice = "index.php?_topRm=project&module=project_invoice&_action=edit&record_id={$row['invoice_id']}";
            $urlProject = "index.php?_topRm=project&module=project_project&_action=detail&record_id={$row['project_id']}";
            $CreateInvoice = "<a class='createInvoice' project_id='{$row['project_id']}'>Create Invoice</a>";

        $urlInvoicelink = "index.php?_topRm=project&module=project_invoice&_action=edit&invoice_id={$row['invoice_id']}";

        $SQLinvoiceCheck ="
        SELECT invoice_id
        FROM invoice
        WHERE project_id = {$row['project_id']}
        AND invoice_id > {$row['invoice_id']}
        ";
        $resultinvoiceCheck  = $db->sql_query($SQLinvoiceCheck);
        $numRowsinvoiceCheck = $db->sql_numrows($resultinvoiceCheck);
        
        if($numRowsinvoiceCheck != 0){
            $CreateInvoice ="<a target='_blank' href='{$urlInvoicelink}'>Go To Invoice</a>";
        }


            $rows .= "
            <tr>
                <td>{$row['project_code']}</td>
                <td><a target='_blank' href='{$urlProject}'>{$row['title']}</a></td>
                <td>{$row['company_name']}</td>
                <td class='txtRight'>{$still_to_bill}</td>
                <td>{$CreateInvoice}</td>
                <td><a target='_blank' href='{$urlInvoice}'>{$row['invoice_code']}</a></td>
            </tr>
            ";
        }

        $text = "
        <h2 class='floatbox pbt10 ui-widget-header ui-corner-top'>
            <div class='ml10'>
                Subscription Project
            </div>
        </h2>
        <table>
            <thead>
            <tr>
                <th>Project Code</th>
                <th>Title</th>
                <th>Client</th>
                <th>Amount</th>
                <th></th>
                <th>Invoice Code</th>
            </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
        return $text;
    }
    /**
     *
     */
    function getRaiseInvoice() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil'); 
        $tv = Zend_Registry::get('tv');

        $project_id = $fn->getReqParam('project_id');

        $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $compRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

        $totalPriorInvSQL = "
        SELECT SUM(invoice_amount) AS total_pi_amount
        FROM invoice
        WHERE project_id = {$project_id}
        AND status != LOWER('Cancelled')
        ";
        $resultPI = $db->sql_query($totalPriorInvSQL);
        $rowPI    = $db->sql_fetchrow($resultPI);

        $invoice_sequence = $this->getNextInvoiceSeq($project_id);

        $fa = array();
        $fa['creation_date']     = date("Y-m-d H:i:s");
        $fa['invoice_date']      = date("Y-m-d");
        $fa['project_id']        = $project_id;
        $fa['invoice_amount']    = ($rowPI['total_pi_amount'] > 0) ?  ($projRec['project_value'] - $rowPI['total_pi_amount']) : $projRec['project_value'];
        $fa['status']            = "Due";
        $fa['invoice_due_date']  = date('Y-m-d', strtotime("+7 days"));
        $fa['invoice_sequence']  = $invoice_sequence;
        $fa['inv_currency']      = $projRec['currency'];

        $id = $fn->addRecord($fa, 'invoice');

        $this->getUpdateInvoiceCode($id, $fa['project_id'], $invoice_sequence);

        //$cpUtil->redirect("index.php?_topRm={$tv['topRm']}&module={$tv['module']}&_action=edit&record_id={$id}");
    }
    /**
     *
     */

    function getNextInvoiceSeq($project_id) {
        $db = Zend_Registry::get('db');

        $SQL    = "
        SELECT MAX(invoice_sequence)
        FROM invoice
        WHERE project_id = {$project_id}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0]+1;
    }    
    /**
     *
     */
    function getUpdateInvoiceCode($invoice_id, $project_id, $sequence) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($project_id != "") {
            $projRec = $fn->getRecordRowByID('project', 'project_id', $project_id);
            $project_code = $projRec['project_code'];

            $invoice_prefix = $fn->getSettingsValueByKey("invoiceCodePrefix");
            $project_prefix = $fn->getSettingsValueByKey("projectCodePrefix");
            $invCodeStartIndex = strlen($project_prefix) + 1;

            if ($cpCfg['m.project.invoice.hasAutoAffix'] == 0){
                $SQL = "
                UPDATE invoice
                SET invoice_code = CONCAT_WS('', '{$invoice_prefix}'
                                                ,SUBSTRING('{$projRec['project_code']}' FROM {$invCodeStartIndex})
                                                ,'-'
                                                , '{$sequence}'
                                            )
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            } else {
                $prefixZeros    = "000000";
                $invoiceSerial  = $fn->getSettingsValueByKey("nextInvoiceCode");
                
                $SQL     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
                $result  = $db->sql_query($SQL);
                $project_code = substr($project_code, strlen($project_prefix));
                
                $invoiceCode = $invoice_prefix. $project_code ."-".  substr ($prefixZeros, 0, 6 - strlen($invoiceSerial)) . $invoiceSerial;
                
                $SQL = "
                UPDATE invoice 
                SET invoice_code = '{$invoiceCode}' 
                WHERE invoice_id = {$invoice_id}
                ";
                $result = $db->sql_query($SQL);
            }
        }
    }

}