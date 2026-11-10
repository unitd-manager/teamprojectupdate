<?
class CP_Admin_Modules_Payroll_Dashboard_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];

        $hook = getCPModuleHook('payroll_dashboard', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach($arr as $widgetArr){
            $widget   = $widgetArr['name'];
            $subClass = $widgetArr['subClass'];
            $cssClass = $widgetArr['cssClass'];

            $clsInst = getCPWidgetObj($widget);

            $rows .= "
            <div class='{$cssClass}'>
                <div class='{$subClass} widget' id='wd_{$widget}'>
                    {$clsInst->getWidget()}
                </div>
            </div>
            ";
        }

        $total_local_workers = $this->model->getTotalCountOfEmployees('Citizen') + 
                               $this->model->getTotalCountOfEmployees('PR');

        $total_foreign_workers = $this->model->getTotalCountOfEmployees('EP') + 
                                 $this->model->getTotalCountOfEmployees('DP') +
                                 $this->model->getTotalCountOfEmployees('SP') +
                                 $this->model->getTotalCountOfEmployees('WP');       

        $text = "
        <div id='dashboard' class='subcolumns'>
            <div class='mt10 dashboardSummary floatbox'>
                <div class='ml10 c50l txtCenter revenueSummary'>
                    <div>TOTAL NO OF LOCAL WORKERS</div>
                    {$total_local_workers}
                    <hr>
                    <div class='floatbox dataInner'>
                        <div class='float_left c50l'>
                            <div>Citizen</div>
                            {$this->model->getTotalCountOfEmployees('Citizen')}
                        </div>
                        <div class='float_right c50l'>
                            <div>PR</div>
                            {$this->model->getTotalCountOfEmployees('PR')}
                        </div>
                    </div>
                </div>
                <div class='c50r txtCenter patientVisitSummary'>
                    <div>TOTAL NO OF FOREIGN WORKERS</div>
                    {$total_foreign_workers}
                    <hr>
                    <div class='floatbox dataInner'>
                        <div class='float_left c25l'>
                            <div>EP</div>
                            {$this->model->getTotalCountOfEmployees('EP')}
                        </div>
                        <div class='float_left c25l'>
                            <div>DP</div>
                            {$this->model->getTotalCountOfEmployees('DP')}
                        </div>
                        <div class='float_left c25l'>
                            <div>SP</div>
                            {$this->model->getTotalCountOfEmployees('SP')}
                        </div>
                        <div class=''>
                            <div>WP</div>
                            {$this->model->getTotalCountOfEmployees('WP')}
                        </div>
                    </div>
                </div>
            </div>
            <div class='txtCenter highlight mt10'><strong>Below are reminders for Passport 180 Days and Workpermit for 60 days.</strong></div>
            {$rows}
        </div>
        ";

        return $text;
    }

    function getLeftPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');

        $hook = getCPModuleHook('payroll_dashboard', 'leftPanel', '', $this);
        if($hook['status']){
            return $hook['html'];
        }

        $text = "
        <div class='profilePic'>
            {$media->getMediaPicture('core_staff', 'picture', $_SESSION['staff_id'], array('folder' => 'normal'))}
        </div>
        <h2 class='quickLinks'>Quick Links</h2>
       ";

        return $text;
    }
}
