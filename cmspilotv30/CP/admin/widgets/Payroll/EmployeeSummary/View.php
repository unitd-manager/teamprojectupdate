<?
class CP_Admin_Widgets_Payroll_EmployeeSummary_View extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');

        $total_local_workers = $this->model->getTotalCountOfEmployees('Citizen') + 
                               $this->model->getTotalCountOfEmployees('PR');

        $total_foreign_workers = $this->model->getTotalCountOfEmployees('EP') + 
                               $this->model->getTotalCountOfEmployees('DP') +
                               $this->model->getTotalCountOfEmployees('SP') +
                               $this->model->getTotalCountOfEmployees('WP');

        $text = "
        <h2 class='floatbox ui-widget-header ui-corner-top'>Employee Summary</h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>Total Local employees (Citizen only) :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('Citizen')}</td>
                </tr>

                <tr>
                    <th>Total Local employees (PR only) :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('PR')}</td>
                </tr>

                <tr>
                    <th>Total EP :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('EP')}</td>
                </tr>

                <tr>
                    <th>Total DP :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('DP')}</td>
                </tr>

                <tr>
                    <th>Total WP :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('WP')}</td>
                </tr>

                <tr>
                    <th>Total SP :</th>
                    <td class='txtCenter'>{$this->model->getTotalCountOfEmployees('SP')}</td>
                </tr>

                <tr>
                    <td colspan='2' class='txtCenter summaryRow'><strong>TOTAL SUMMARY</strong></td>
                </tr>

                <tr>
                    <th class='summaryText'>Total no of Local Workers:</th>
                    <td class='txtCenter summaryText'>{$total_local_workers}</td>
                </tr>

                <tr>
                    <th class='summaryText'>Total no of Foreign Workers:</th>
                    <td class='txtCenter summaryText'>{$total_foreign_workers}</td>
                </tr>

            </table>
        </div>
        ";

        return $text;
    }
}