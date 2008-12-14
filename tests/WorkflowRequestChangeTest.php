<?php
/**
 * @package cmsworkflow
 * @subpackage tests
 */
class WorkflowRequestChangeTest extends FunctionalTest {
	static $fixture_file = 'cmsworkflow/tests/SiteTreeCMSWorkflowTest.yml';
	
	function testChangesAreTracked() {
		$page = $this->objFromFixture('SiteTree', 'custompublisherpage');

		$custompublishersgroup = $this->objFromFixture('Group', 'custompublishergroup');
		$custompublisher = $this->objFromFixture('Member', 'custompublisher');
		$custompublisher->Groups()->add($custompublishersgroup);

		$customauthorsgroup = $this->objFromFixture('Group', 'customauthorsgroup');
		$customauthor = $this->objFromFixture('Member', 'customauthor');
		$customauthor->Groups()->add($customauthorsgroup);
		
		$page->PublisherGroups()->add($custompublishersgroup);
		
		$this->session()->inst_set('loggedInAs', $customauthor->ID);
		$page->Content = 'edited';
		$page->write();
		$request = $page->requestPublication($customauthor, $page->PublisherMembers());
		$this->assertEquals(
			$request->Changes()->Count(),
			1,
			'Change has been tracked for initial publication request'
		);
		$page->write();
		$this->assertEquals(
			$request->Changes()->Count(),
			1,
			'Changes arent tracked twice without a Status change'
		);
		$change = $request->Changes()->First();
		$this->assertEquals(
			$change->AuthorID,
			$customauthor->ID,
			"Change has the correct author assigned"
		);
		$this->assertEquals(
			$change->PageDraftVersion,
			$page->Version,
			"Change has the correct draft version"
		);
		
		$this->session()->inst_set('loggedInAs', $custompublisher->ID);
		$page->doPublish();
		$request->flushCache();
		$this->assertEquals(
			$request->Changes()->Count(),
			2,
			'Change has been tracked for the publication step'
		);
		$change = $request->Changes()->Last();
		$this->assertEquals(
			$change->AuthorID,
			$custompublisher->ID,
			"Change has the correct author assigned"
		);
		$this->assertEquals(
			$change->PageLiveVersion,
			$page->Version,
			"Change has the corrent draft version"
		);
		
		$this->session()->inst_set('loggedInAs', null);
	}
}
?>