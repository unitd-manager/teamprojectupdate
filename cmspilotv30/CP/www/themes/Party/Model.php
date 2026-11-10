<?
class CP_Www_Themes_Party_Model extends CP_Www_Lib_ThemeModelAbstract
{
    function __construct() {
        $fn = Zend_Registry::get('fn');
        $fn->addLangKey('m.party.invitation.msg.sendAlertMessage');
        $fn->addLangKey('m.party.invitation.msg.sendInviteProcessMessage');
        $fn->addLangKey('m.party.invitation.msg.confirmDeleteMessage');
        $fn->addLangKey('m.party.invitation.msg.sendTestProcessMessage');
        $fn->addLangKey('m.party.summary.msg.closePartyAlert');
        $fn->addLangKey('m.party.summary.msg.closePartyResponse');
        $fn->addLangKey('m.party.summary.msg.resendConfirmMessage');
        $fn->addLangKey('m.party.summary.msg.resendProcessMessage');
        $fn->addLangKey('m.party.message.msg.confirmDeleteMessage');
        $fn->addLangKey('m.party.message.msg.sendConfirmMessage');
        $fn->addLangKey('m.party.message.msg.sendProcessMessage');
        $fn->addLangKey('m.party.message.msg.thankyouCardUploadError');
        $fn->addLangKey('m.party.summary.addGuest.statusClosedAlready.err');
        $fn->addLangKey('m.party.summary.reschedule.form.fld.statusClosedAlready.err');
        $fn->addLangKey('m.party.summary.cancelParty.form.fld.statusClosedAlready.err');
        $fn->addLangKey('m.party.summary.resendInvite.statusClosedAlready.err');
        $fn->addLangKey('m.party.summary.closeParty.form.fld.statusClosedAlready.err');


    }

    function checkAndRedirect() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        $card_id    = $fn->getSessionParam('card_id');
        $charity_id = $fn->getSessionParam('charity_id');
        $party_setup_id = $fn->getSessionParam('party_setup_id');
        $upload_your_own = $fn->getSessionParam('upload_your_own');

        if ($tv['secType'] == 'Party'){
            if ($tv['catType'] == 'Select Charity' && $card_id == '' && $upload_your_own == ''){
                $url = $cpUrl->getUrlByCatType('Select Card', 'Party');
                $url = $cpUrl->getUrlBySubCatType('Content', 'Select Card', 'Party');
                $cpUtil->redirect($url);

            } else if($tv['catType'] == 'Party Detail' && $charity_id == '' && $upload_your_own == ''){
                $url = $cpUrl->getUrlByCatType('Select Charity', 'Party');
                $cpUtil->redirect($url);

            } else if(($tv['catType'] == 'Guest List'
                     || $tv['catType'] == 'Party Summary'
                     || $tv['catType'] == 'Message')
                    && $party_setup_id == ''){
                //going the Send Invitation, Summary or Message but the party_setup_id is not set
                // if (isLoggedInWWW()) {
                //     $url = $cpUrl->getUrlByCatType('Party List', 'Party');
                // } else {
                //     $url = $cpUrl->getUrlByCatType('Party Detail', 'Party');
                // }
                $url = '/';
                $cpUtil->redirect($url);
            }
        } else { //$tv['secType'] is Dashboard
            //if browser crashed and party detail / send invitaion not complete then take
            //to the party detail in the Get Started
            $forReschedule = $fn->getReqParam('forReschedule');
            $forReschedule = $forReschedule == 1 ? true : false;

            $invitationCount = getCPModelObj('party_invitation')->getInvitationCount($forReschedule);
            if ($invitationCount == 0) {
                $party_setup_id = $fn->getSessionParam('party_setup_id');
                $fn->setSessionParam('party_setup_id', '');
                $fn->setSessionParam('summary_arrived', '');
                $url = $cpUrl->getUrlByCatType('Party Detail', 'Party');
                $url .= '?party_setup_id=' . $party_setup_id;
                $cpUtil->redirect($url);
            }
        }

        return true;
    }
}