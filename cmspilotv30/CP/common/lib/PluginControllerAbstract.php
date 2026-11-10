<?
abstract class CP_Common_Lib_PluginControllerAbstract
{
    var $view = null;
    var $model = null;
    var $fns   = null;
    var $name  = '';

    /**
     *
     */
    function getView($exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');
        $plugin = $fn->getReqParam('plugin'); // if the plugin is called via url (eg: showing the comment list after submit
        
        if ($plugin != ''){
            foreach($_REQUEST as $key => $value){
                $this->$key = $value;
            }
        } else {

            foreach($exp as $key => $value){
                if(!isset($this->$key) && $key != 'rowsHTML' && $key != 'dataArray'){
                    print "{$key} does not exist in {$this->name}<br> ";
                } else {
                    if ($key == 'dataArray' && is_array($value)){
                        $this->model->dataArray = $value;
                    } else if ($key == 'rowsHTML'){
                        $this->view->rowsHTML = $value;
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }

        if (!is_array($this->model->dataArray)){
            $this->model->dataArray = $this->model->getDataArray();
        }
        
        $text = $this->view->getView();
        $text = $viewHelper->getPluginViewWrapper($text, $this->name, $this);
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
