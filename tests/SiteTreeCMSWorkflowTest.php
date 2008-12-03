<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class SiteTreeCMSWorkflowTest extends FunctionalTest {
	
	/**
	 * Created in setUp() to ensure defaults are created *before* inserting new fixtures,
	 * as they rely on certain default groups being present.
	 */
	//static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	
	function setUp() {
		parent::setUp();

		// default records are not created in TestRunner by default
		singleton('SiteTreeCMSWorkflow')->augmentDefaultRecords();
		$fixtureFile = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
		$this->fixture = new YamlFixture($fixtureFile);
		$this->fixture->saveIntoDatabase();
	} 
	
	function testAlternateCanPublishLimitsToPublisherGroups() {
		// Check for default record group assignments
		$defaultpublisherspage = $this->objFromFixture('SiteTree', 'defaultpublisherspage');
		$defaultpublishersgroup = DataObject::get_one('Group', "Code = 'site-content-publishers'");
		$defaultpublisher = $this->objFromFixture('Member', 'defaultpublisher');
		
		// Workaround because defaults aren't written in unit tests
		$defaultpublisher->Groups()->add($defaultpublishersgroup);
		
		$gs = $defaultpublisher->Groups();
		$this->assertTrue(
			$defaultpublisherspage->canPublish($defaultpublisher),
			'Default publisher groups are assigned to new records'
		);
		
		// Check for random user publish permissions
		$randomUser = $this->objFromFixture('Member', 'randomuser');
		$this->assertFalse(
			$defaultpublisherspage->canPublish($randomUser),
			'Users which are not in publisher groups cant publish new pages'
		);
		
		// Check for custom page group assignments
		$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);
		$this->assertTrue(
			$custompublisherspage->canPublish($custompublisher),
			'Default publisher groups are assigned to new records'
		);
	}
	
	function testAccessTabOnlyDisplaysWithGrantAccessPermissions() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$fields = $page->getCMSFields();
		$this->assertTrue(
			$fields->dataFieldByName('CanPublishType')->isReadonly(),
			'Users with publish or SITETREE_GRANT_ACCESS permission can change "publish" group assignments in cms fields'
		);
		
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$fields = $page->getCMSFields();
		$this->assertFalse(
			$fields->dataFieldByName('CanPublishType')->isReadonly(),
			'Users without publish or SITETREE_GRANT_ACCESS permission cannot change "publish" group assignments in cms fields'
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
	
	// doesn't work because Member::currentUser() doesn't respect test session data in SiteTree->canEdit()
	/*
	function testCmsActionsLimited() {
		$custompublisherspage = $this->objFromFixture('SiteTree', 'custompublisherpage');
		
		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);
		
		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$unpublishedRecord = new Page();
		$unpublishedRecord->write();
		
		$publishedRecord = new Page();
		$publishedRecord->write();
		$publishedRecord->publish('Stage', 'Live');
		
		$deletedFromLiveRecord = new Page();
		$deletedFromLiveRecord->write();
		$deletedFromLiveRecord->publish('Stage', 'Live');
		$deletedFromLiveRecord->deleteFromStage('Live');
		
		$changedOnStageRecord = new Page();
		$changedOnStageRecord->write();
		$changedOnStageRecord->publish('Stage', 'Live');
		$changedOnStageRecord->Content = 'Changed on Stage';
		$changedOnStageRecord->write();
		
		// test for author
		var_dump($customauthor->Email);
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$c = Member::currentUser();
		var_dump($c);
		return;
		$actions = $unpublishedRecord->getCMSActions();
		$this->assertNotContains(
			'action_publish',
			$unpublishedRecord->getCMSActions(),
			'Author cant trigger publish button'
		);
	}
	*/
	
}
?>