<?
class CP_Admin_Widgets_Project_QuickLinks extends CP_Common_Lib_WidgetViewAbstract
{

    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        
        $newTaskUrl  = "index.php?_topRm=project&module=project_task&_action=new&lang=eng";
        $newOppUrl   = "index.php?_topRm=project&module=project_opportunity&_action=new&lang=eng";
        $newProjUrl  = "index.php?_topRm=project&module=project_project&_action=new&lang=eng";
        $taskSummary = "index.php?_topRm=project&module=project_project&_spAction=taskSummaryByProject&showHTML=0&hasDB=1&status=WIP";
        
        $text = "
        <h2>Quick Links</h2>
        <div class='p10'>
            <div><a class='button' href='{$newTaskUrl}'>New Task</a></div>
            <div class='mt5'><a class='button' href='{$newOppUrl}'>New Opportunity</a></div>
            <div class='mt5'><a class='button' href='{$newProjUrl}'>New Project</a></div>
            <div class='mt5'><a class='button' href='{$taskSummary}'>Download Task History</a></div>
        </div>
        ";

        return $text;
    }

}