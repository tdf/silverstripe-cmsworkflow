<?php
class LeftAndMainCMSWorkflow extends LeftAndMainDecorator {
	
	public static $allowed_actions = array(
		'cms_requestpublication',
		'cms_requestdeletefromlive',
	);
	
	function init() {
		Requirements::javascript('cmsworkflow/javascript/LeftAndMainCMSWorkflow.js');
	}
	
	/**
	 * Handler for the CMS button
	 */
	public function cms_requestpublication($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		// request publication
		$request = $page->requestPublication();
		if(!$request) return false;
		
		// gather members for status output
		$members = $page->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_PUBLICATION_SUCCESS_MESSAGE','Emailed %s requesting publication'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}
	
	public function cms_requestdeletefromlive($urlParams, $form) {
		$id = $urlParams['ID'];
		$page = DataObject::get_by_id("SiteTree", $id);
		
		if(!$page->canCreateDeletionRequest()) {
			return false;
		}
		
		// request publication
		$request = $page->requestDeletion();
		if(!$request) return false;
		
		// gather members for status output
		$members = $record->PublisherMembers();
		foreach($members as $member) {
			$emails[] = $member->Email;
		}
		$strEmails = implode(", ", $emails);
		
		FormResponse::status_message(
			sprintf(
				_t('SiteTreeCMSWorkflow.REQUEST_DELETEFROMLIVE_SUCCESS_MESSAGE','Emailed %s requesting deletion'), 
				$strEmails
			), 
			'good'
		);
		return FormResponse::respond();	
	}

}
?>