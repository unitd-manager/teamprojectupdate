<?
class CP_Www_Widgets_Ui_Accordian_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqTools-1.2.5');

    //========================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $text = "
        <div id='{$c->handle}' class='jqToolsAccordian'>
            {$this->getRowsHTML()}
        </div>
        ";


        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            exp = {
                 handle: '{$c->handle}'
                ,toggleOnSecondClick: '{$c->toggleOnSecondClick}'
                ,fadeInSpeed: '{$c->fadeInSpeed}'
            }
            cpw.ui.accordian.run(exp);
        "));

        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $c = &$this->controller;
        $ln = Zend_Registry::get('ln');

        $rows = '';
        if ($c->type == 'byContentDataArray'){
            $counter = 0;
            foreach($this->model->dataArray AS $row) {
                $mContent = getCPModuleObj('webBasic_content');    
                
                $style   = ($counter == 0 && $c->openFirstRow) ? " style='display:block'" : '';
                $current = ($counter == 0 && $c->openFirstRow) ? " class='current'"       : '';

                $rows .= "
            	<h2{$current}>{$ln->gfv($row, 'title')}</h2>
            	<div class='pane'{$style}>
                    {$mContent->view->getStandardFatListRow($row)}
            	</div>
                ";
                
                $counter++;
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
