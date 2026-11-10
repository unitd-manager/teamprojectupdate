<?
class CP_Admin_Widgets_Project_InvoiceSummary_View extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');

        $url = "index.php?_topRm=project&module=project_invoice";

        $text = "
        <h2 class='floatbox ui-widget-header ui-corner-top'><a href='{$url}'>Invoice Summary</a></h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>Total outstanding invoices:</th>
                    <td>{$this->model->getTotalOutstandingInvoices()}</td>
                </tr>

                <tr>
                    <th>Total invoices due this month:</th>
                    <td>{$this->model->getTotalInvoicesDueThisMonth()}</td>
                </tr>

                <tr>
                    <th>Total late invoices:</th>
                    <td>{$this->model->getTotalLateInvoices()}</td>
                </tr>

                <tr>
                    <th>Total late invoice (> 90 days):</th>
                    <td>{$this->model->getTotalOverDueInvoices()}</td>
                </tr>

                <tr>
                    <th>Total invoices raised this month:</th>
                    <td>{$this->model->getTotalInvoicesThisMonth()}</td>
                </tr>

                <tr>
                    <th>Total invoices raised last month:</th>
                    <td>{$this->model->getTotalInvoiceLastMonth()}</td>
                </tr>

                <tr>
                    <th>Total invoices paid this month:</th>
                    <td>{$this->model->getTotalInvoicesPaidThisMonth()}</td>
                </tr>

                <tr>
                    <th>Total invoices paid last month:</th>
                    <td>{$this->model->getTotalInvoicesPaidLastMonth()}</td>
                </tr>
            </table>
        </div>
        ";

        return $text;
    }
}