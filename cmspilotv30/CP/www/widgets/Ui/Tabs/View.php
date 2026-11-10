<?
class CP_Www_Widgets_Ui_Tabs_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqTools-1.2.5');

    //========================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $text = "
        <div class='jqToolsTabsWrap'>
            <!-- the tabs -->
            <ul id='{$c->handle}' class='jqToolsTabs'>
                {$this->getTabHeader()}
            </ul>
            
            <!-- tab 'panes' -->
            <div id='{$c->panesId}' class='jqToolsPanes'>
                {$this->getRowsHTML()}
            </div>
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,panesId: '{$c->panesId}'
            }
            cpw.ui.tabs.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getTabHeader() {
        $c = &$this->controller;

        $rows = '';
        foreach($c->lblArray AS $lbl) {
            $rows .="
            <li><a href='#'>{$lbl}</a></li>
            ";
        }

        return $rows;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;

        $rows = '';
        if ($c->type == 'byContentDataArray'){
            foreach($this->model->dataArray AS $row) {
                $mContent = getCPModuleObj('webBasic_content');    
                $rows .= "
                <div>
                    {$mContent->view->getStandardFatListRow($row)}
                </div>
                ";
            }
        } else {
            foreach($this->model->dataArray AS $row) {
                $rows .="
                <div>
                    {$row['description']}
                </div>
                ";
            }
        }

        return $rows;
    }
}
