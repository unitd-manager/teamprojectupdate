<?
include_once(CP_MODULES_PATH . 'WebBasic/Content/Model.php');
class CP_Www_Modules_WebBasic_Home_Model extends CP_Www_Modules_WebBasic_Content_Model
{
    function getSQL($exp = array()) {
        $modObj = getCPModuleObj('webBasic_content');
        return $modObj->model->getSQL($exp);
    }

    //==================================================================//
    function setSearchVar($linkRecType = '') {
        $modObj = getCPModuleObj('webBasic_content');
        $modObj->model->setSearchVar($linkRecType);
    }

    //==================================================================//
    function getTwigParams() {
        $dataArray = getCPWidgetObj('content_record')->getWidget(array(
             'contentType' => 'Callout'
            ,'returnDataOnly' => true
            ,'global' => false
            ,'includeMediaRec' => true
        ));

        $arr = array();
        foreach($dataArray AS $row){
            $url = '';
            if ($row['external_link'] != '') {
                $url = $row['external_link'];
            } else if ($row['internal_link'] != '') {
                $url = $row['internal_link'];
            }

            $row['url'] = $url;
            $arr[] = $row;
        }

        $params = array(
            'calloutArr' => $arr
        );

        return $params;
    }
}
