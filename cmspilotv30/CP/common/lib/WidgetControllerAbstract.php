<?
abstract class CP_Common_Lib_WidgetControllerAbstract
{
    var $model = null;
    var $fns = null;
    var $name = '';
    var $widgetCssClass = '';
    var $helperFn = '';
    var $returnDataOnly = false;
    var $wrapWidgetForEmptyFoundset = false;
    var $wrapWidget = true;
    var $hideWidgetOnLoading = false;
    var $rowsHTMLPassed = false; // set this to true when you pass the rowshtml explicitly

    /**
     *
     */
    function __construct() {
    }

    /**
     *
     */
    function getWidget($exp = array()){
        foreach($exp as $key => $value){
            //if(!isset($this->$key) &&
            if(!property_exists($this, $key) &&
               $key != 'rowsHTML' &&
               $key != 'dataArray' &&
               $key != 'dataArrayPostCallback'
            ){
                print "{$key} does not exist in {$this->name}<br>";
            } else {
                if ($key == 'dataArray' && is_array($value)){
                    $this->model->dataArray = $value;
                } else if ($key == 'rowsHTML'){
                    $this->view->rowsHTML = $value;
                    $this->rowsHTMLPassed = true;
                } else {
                    $this->$key = $value;
                }
            }
        }

        // if the helper function is called then do not run the below auto setup
        if ($this->helperFn == ''){
            //if (!is_array($this->model->dataArray)){
            if (count($this->model->dataArray) == 0){
                $this->model->dataArray = $this->model->getDataArray();
            }

            if ($this->view->rowsHTML == ''){
                //$this->view->rowsHTML = $this->view->getRowsHTML();
            }
        }

        if ($this->returnDataOnly){
            return $this->model->dataArray;
        }
        $viewHelper = Zend_Registry::get('viewHelper');

        // if the helper function is passed then call that otherwise the default getWidget
        if ($this->helperFn != ''){
            $helperFn = $this->helperFn;
            $text = $this->view->$helperFn();
        } else {
            $text = $this->view->getWidget();
        }

        if (!in_array($this->name, Zend_Registry::get('widgetsOnPage'))){
            CP_Common_Lib_Registry::arrayMerge('widgetsOnPage', array($this->name));
        }

        if($this->wrapWidget){
            $text = $viewHelper->getWidgetWrapper($text, $this->name, $this->widgetCssClass, $this);
        }

        return $text;
    }

    /**
     *
     */
    function getAdd(){
        return $this->model->getAdd();
    }

    /**
     *
     */
    function getSave(){
        return $this->model->getSave();
    }

}
