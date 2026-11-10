<?
class CP_Admin_Widgets_Tradingsg_ProjectSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $url = "index.php?_topRm=project&module=project_project";
        $target = $fn->getSettingsValueByKey('monthlySalesTarget');

        $totSalesThisMonth = $this->model->getTotalSalesThisMonth();
        $targetPercThisMonth = round(($totSalesThisMonth/$target) * 100);
                
        $totSalesLastMonth = $this->model->getTotalSalesLastMonth();
        $targetPercLastMonth = round(($totSalesLastMonth/$target) * 100);
        
        $monthNum = date('n');
        $totSalesThisYear = $this->model->getTotalSalesThisYear();
        $targetPercThisYear = round(($totSalesThisYear/($target*$monthNum)) * 100);

        $wdInvSummary = getCPWidgetObj('tradingsg_invoiceSummary');
        $totalStillToBill = preg_replace ('/[^\d\s]/', '', $this->model->getTotalValueOfStillToBill());
        $totalOutstanding = $totalStillToBill +  preg_replace ('/[^\d\s]/', '', $wdInvSummary->model->getTotalOutstandingInvoices());

        $text = "
        <h2><a href='{$url}'>Projects & Sales Summary</a></h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>Total value of sales this month:</th>
                    <td>{$this->model->getCurPfx()}" . number_format($totSalesThisMonth) ."&nbsp;&nbsp;&nbsp;% of target: {$targetPercThisMonth}%</td>
                </tr>

                <tr>
                    <th>Total value of sales last month:</th>
                    <td>{$this->model->getCurPfx()}" . number_format($totSalesLastMonth) ."&nbsp;&nbsp;&nbsp;% of target: {$targetPercLastMonth}%</td>
                </tr>

                <tr>
                    <th>Total value of sales this year:</th>
                    <td>{$this->model->getCurPfx()}" . number_format($totSalesThisYear) ."&nbsp;&nbsp;&nbsp;% of target: {$targetPercThisYear}%</td>
                </tr>

                <tr>
                    <th>Total value of projects WIP:</th>
                    <td>{$this->model->getTotalValueOfWIPProjects()}</td>
                </tr>

                <tr>
                    <th>Total value of Still to Bill:</th>
                    <td>{$this->model->getTotalValueOfStillToBill()}</td>
                </tr>

                <tr>
                    <th>Total value of Projects Still to Bill + Outstanding Invoices:</th>
                    <td>{$this->model->getCurPfx()}" . number_format($totalOutstanding) ."</td>
                </tr>
            </table>
        </div>
        ";

        return $text;
    }
}