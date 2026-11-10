<?
class CP_Admin_Modules_Project_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('project_invoice');
        $modules->registerModule($modObj, array(
            //'actBtnsList'   => array('new', 'search', 'export', 'printListPDF', 'printListPDFInvoice')
            'actBtnsList'   => array('new', 'search', 'export', 'printListPDF')
           ,'actBtnsDetail' => array('edit', 'delete', 'printDetailMenu')
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Invoice'
        ));
    }

    /**
     *
     */
    function getInvoiceValueTotal() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
            $SQL = "
            SELECT FORMAT(SUM(invoice_amount), 0) AS sum_invoice_amount
            FROM invoice i
            LEFT JOIN (project p)         ON (i.project_id = p.project_id)
            LEFT JOIN (contact cont)      ON (p.contact_id = cont.contact_id)
            LEFT JOIN (company c)         ON (p.company_id = c.company_id)
            LEFT JOIN (company_address ca)ON (cont.company_address_id = ca.company_address_id)
            ";

        } else {
            $SQL = "
            SELECT FORMAT(SUM(invoice_amount), 0) AS sum_invoice_amount
            FROM invoice i
            LEFT JOIN (project p)    ON (p.project_id = i.project_id    )
            LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id )
            LEFT JOIN (company c)    ON (c.company_id = p.company_id    )
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function getInvoiceValueTotalRef() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        if ($cpCfg['m.project.hasMultipleCompanyAddress'] == 1) {
            $SQL = "
            SELECT FORMAT(SUM(invoice_amount_ref), 0) AS sum_invoice_ref_amount
            FROM invoice i
            LEFT JOIN (project p)         ON (i.project_id = p.project_id)
            LEFT JOIN (contact cont)      ON (p.contact_id = cont.contact_id)
            LEFT JOIN (company c)         ON (p.company_id = c.company_id)
            LEFT JOIN (company_address ca)ON (cont.company_address_id = ca.company_address_id)
            ";

        } else {
            $SQL = "
            SELECT FORMAT(SUM(invoice_amount_ref), 0) AS sum_invoice_ref_amount
            FROM invoice i
            LEFT JOIN (project p)    ON (p.project_id = i.project_id    )
            LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id )
            LEFT JOIN (company c)    ON (c.company_id = p.company_id    )
            ";
        }

        return $SQL;
    }

    /**
     *
     */
    function setReportsArray($repInst) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $record_id = (int) $fn->getReqParam('record_id', 0);
        $report = $fn->getReqParam('report');
        
        $repInst->setReportArrayObj('project_invoice', "invoice");
        $arr = &$repInst->reportsArray['project_invoice']['invoice'];

        $arr['jasperFileName'] = 'invoice.jasper';
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceOther");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceOther'];
        $arr['jasperFileName'] = 'invoice_other.jasper';
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceNoCategory");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceNoCategory'];
        $arr['jasperFileName'] = 'invoiceNoCategory.jasper';
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceNoItems");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceNoItems'];
        $arr['jasperFileName'] = 'invoice_no_line_item.jasper';
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceWOLogo");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceWOLogo'];
        $arr['jasperFileName'] = 'invoice.jasper';
        $arr['printInLetterhead']  = true;
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceOtherWOLogo");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceOtherWOLogo'];
        $arr['jasperFileName'] = 'invoice_other.jasper';
        $arr['printInLetterhead']  = true;
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceNoItemsWOLogo");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceNoItemsWOLogo'];
        $arr['jasperFileName'] = 'invoice_no_line_item.jasper';
        $arr['printInLetterhead']  = true;
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceWOQuote");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceWOQuote'];
        $arr['jasperFileName'] = 'invoiceWOQuote.jasper';
        $arr['depModules']     = array('project_contact', 'project_project');

        $repInst->setReportArrayObj('project_invoice', "invoiceWOQuoteWOLogo");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceWOQuoteWOLogo'];
        $arr['jasperFileName'] = 'invoiceWOQuote.jasper';
        $arr['printInLetterhead']  = true;
        $arr['depModules']     = array('project_contact', 'project_project');

        if ($record_id > 0){
            $SQL = "
            SELECT i.invoice_code
                  ,i.invoice_type
                  ,c.company_name
            FROM invoice i
            LEFT JOIN (project p) ON (i.project_id = p.project_id)
            LEFT JOIN (company c) ON (p.company_id = c.company_id)
            WHERE i.invoice_id = {$record_id}
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            $arr = &$repInst->reportsArray['invoice'][$report];
            $arr['outputFileName'] = $row['company_name'] . '-' . $row['invoice_type'] . '-' . $row['invoice_code'];
        }

        $repInst->setReportArrayObj('project_invoice', "invoiceList");
        $arr = &$repInst->reportsArray['project_invoice']['invoiceList'];
        $arr['jasperFileName'] = 'invoice_list.jasper';
        $arr['sendRecIds']     = true;
        $arr['sendSortOrder']  = true;
        $arr['outputFileName'] = $cpCfg['cp.companyName'] . '-Invoices-' . date('Ymd');
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('project_invoice', 'project_invoiceItem');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'    => 'invoice_items'
           ,'linkingType'         => 'portal'
           ,'showLinkPanelInNew'  => 0
           ,'showLinkPanelInEdit' => 1
           ,'hasPortalEdit'       => 1
           ,'hasPortalDelete'     => 1
           ,'fieldlabel'          => array('Description', 'Amount')
           ,'portalDialogWidth'  => 450
           ,'portalDialogHeight' => 450
        ));

        //------------------------------------------------------------------------------//
    }
}