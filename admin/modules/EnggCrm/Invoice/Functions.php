<?
class CPL_Admin_Modules_EnggCrm_Invoice_Functions extends CP_Admin_Modules_EnggCrm_Invoice_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enggCrm_invoice');
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('export')
           ,'actBtnsDetail' => array()
           ,'actBtnsEdit'   => array('save', 'apply', 'cancel', 'delete')
           ,'relatedTables' => array('media')
           ,'title'         => 'Invoice'
           ,'hasEditInList' => false
        ));
    }

    function getInvoiceValueTotal() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $SQL = "
        SELECT FORMAT(SUM(i.invoice_amount + 
                        ((i.invoice_amount * i.gst_percentage) / 100)
                    ),2)
                AS sum_invoice_amount
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id   = o.order_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        ";

        return $SQL;
    }
}