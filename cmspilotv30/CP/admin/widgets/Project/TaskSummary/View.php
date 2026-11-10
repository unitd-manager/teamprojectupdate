<?
class CP_Admin_Widgets_Project_TaskSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $url = "index.php?_topRm=project&module=project_task";

        $text = "
        <h2 class='floatbox ui-widget-header ui-corner-top'><a href='{$url}'>Tasks & Timesheets Summary</a></h2>
        <div class='tableOuter'>
            <table class='thinList list'>
                <tr>
                    <th>Total tasks raised today:</th>
                    <td>{$this->model->getTotalTasksRaisedToday()}</td>
                </tr>

                <tr>
                    <th>Total tasks raised this week:</th>
                    <td>{$this->model->getTotalTasksRaisedThisWeek()}</td>
                </tr>

                <tr>
                    <th>Total timesheets raised today:</th>
                    <td>{$this->model->getTotalHrsRaisedToday()}</td>
                </tr>

                <tr>
                    <th>Total timesheets raised this week:</th>
                    <td>{$this->model->getTotalHrsRaisedThisWeek()}</td>
                </tr>
            </table>
        </div>
        ";

        return $text;
    }
}