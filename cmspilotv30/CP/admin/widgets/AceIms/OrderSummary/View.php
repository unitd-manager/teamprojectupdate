<?
class CP_Admin_Widgets_AceIms_OrderSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $url = '';
        $title = 'Invoice Summary';
        
        $textInvoiceSummary = "
        <h2><a href='{$url}'>$title</a></h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr> 
                    <th>Total outstanding invoices:</th>
                    <td class='txtRight'>{$this->model->getTotalOutstandingInvoices($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices due this month:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesDueThisMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total late invoices:</th>
                    <td class='txtRight'>{$this->model->getTotalLateInvoices($title)}</td>
                </tr>

                <tr>
                    <th>Total late invoice (> 90 days):</th>
                    <td class='txtRight'>{$this->model->getTotalOverDueInvoices($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices raised this month:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesThisMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices raised last month:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoiceLastMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices paid this month:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesPaidThisMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices paid last month:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesPaidLastMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices paid last 3 months:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesPaidLastThreeMonth($title)}</td>
                </tr>

                <tr>
                    <th>Total invoices paid this year:</th>
                    <td class='txtRight'>{$this->model->getTotalInvoicesPaidThisYear($title)}</td>
                </tr>
            </table>
        </div>
        ";

        $textSubsidySummary = '';
        if ($cpCfg['w.aceIms.orderSummary.hasSubsidySummary']) {
            $title = 'Subsidy Summary';
            $textSubsidySummary = "
            <h2><a href='{$url}'>$title</a></h2>
            <div class='tableOuter'>
                <table class='thinList list'>
                    <tr>
                        <th>Total outstanding invoices:</th>
                        <td class='txtRight'>{$this->model->getTotalOutstandingInvoices($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total invoices due this month:</th>
                        <td class='txtRight'>{$this->model->getTotalInvoicesDueThisMonth($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total late invoices:</th>
                        <td class='txtRight'>{$this->model->getTotalLateInvoices($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total late invoice (> 90 days):</th>
                        <td class='txtRight'>{$this->model->getTotalOverDueInvoices($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total invoices raised this month:</th>
                        <td class='txtRight'>{$this->model->getTotalInvoicesThisMonth($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total invoices raised last month:</th>
                        <td class='txtRight'>{$this->model->getTotalInvoiceLastMonth($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total invoices paid this month:</th>
                        <td class='txtRight'>{$this->model->getTotalInvoicesPaidThisMonth($title)}</td>
                    </tr>
            
                    <tr>
                        <th>Total invoices paid last month:</th>
                        <td class='txtRight'>{$this->model->getTotalInvoicesPaidLastMonth($title)}</td>
                    </tr>
                </table>
            </div>
            ";
        }
        
        if ($cpCfg['w.aceIms.orderSummary.alignRightForInstitute']) {
            $addClass = '';
        } else {
            $addClass = 'c50l';
        }
        
        $text ="
        <div class='{$addClass} mt10 mb5'>
            <div class='subcl ml10 mr10'>
                {$textInvoiceSummary}
            </div>
        </div>
        <div class='c50l mt10'>
            <div class='subcl'>
                {$textSubsidySummary}
            </div>
        </div>
        ";
        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $text = '';

        return $text;
    }

}