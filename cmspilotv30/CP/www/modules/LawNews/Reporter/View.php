<?
class CP_Www_Modules_LawNews_Reporter_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getList($dataArray) {
        
        $rows = '';
        foreach ($dataArray as $row){
            $rows .= "
            <li class='reporter'>
             
            </li>
            ";
        }

        $text = "
        <div class='reporterList'>
            <ul class='noDefault'>
                {$rows}
            </ul>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDetail($row) {
        $text = "
        ";
        
        return $text;
    }
   
}
