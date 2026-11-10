<?
class CP_Admin_Modules_EnggCrm_ScheduleLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
 	 		    {$formObj->getTBRow('Title', 'title')}
 	 		    {$formObj->getDateRow('Start Date', 'start_date_mod')}
 	 		    {$formObj->getDateRow('End Date', 'end_date')}
 	 		    {$formObj->getTARow('Description', 'description')}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('schedule', 'schedule_id', $id);

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
 	 		    {$formObj->getTBRow('Title', 'title', $row['title'])}
 	 		    {$formObj->getDateRow('Start Date', 'start_date_mod', $row['start_date'])}
 	 		    {$formObj->getDateRow('End Date', 'end_date', $row['end_date'])}
 	 		    {$formObj->getTARow('Description', 'description', $row['description'])}
            </fieldset>
            <input type='hidden' name='schedule_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
