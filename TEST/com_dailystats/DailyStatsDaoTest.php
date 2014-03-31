<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once PLG_XLINK_ARTICLES_PATH . '\helper.php';

class DailyStatsDaoTest extends DailyStatsTestBase {
	
	private $linkSectionStartString;
	private $isSpaceAddedlinkSectionStartString;
	private $linkSeparator;
	private $isSpaceAddedLinkSeparator;
	
	/**
	 * Array receiving the msg to be displayed to the user after the plugin has finished 
	 * processing.
	 */
	private $userMessageArray;
	
	public function setUp() {
		parent::setUp ();
		
		$this->linkSectionStartString = "Ecouter également";
		$this->isSpaceAddedlinkSectionStartString = 1;
		$this->linkSectionStartStringSepOneSpace = " ";
		$this->linkSeparator = ",";
		$this->isSpaceAddedLinkSeparator = 1;
	}
	
	public function testGetLinkedArticlesIdsMultipleLinksAllKinds() {
		$sourceArticle_A = JTable::getInstance ( 'content' );
		$sourceArticle_A->load ( 1 );
		$linksArray = XLinkArticlesHelper::getLinksArray($sourceArticle_A, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSeparator, $this->isSpaceAddedLinkSeparator, $this->userMessageArray);
		
		$res = XLinkArticlesHelper::getLinkedArticlesIds( $linksArray );
		
		$this->assertEquals(3, count($res),'count($res)');
		
		$linkIds = $res[0];
		$unlinkIds = $res[1];
		$skippedLinkIds = $res[2];
		$this->assertEquals(array(10,8,4,3,2,13),$linkIds,'$linkIds');
		$this->assertEquals(array(11,9,7,6,5),$unlinkIds,'$unlinkIds');
		$this->assertEquals(array(12),$skippedLinkIds,'$skippedLinkIds');
	}
	
	public function testGetLinkedArticlesIdsOneLinkUL() {
		$sourceArticle_C = JTable::getInstance ( 'content' );
		$sourceArticle_C->load ( 3 );
		$linksArray = XLinkArticlesHelper::getLinksArray($sourceArticle_C, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->userMessageArray);
		
		$res = XLinkArticlesHelper::getLinkedArticlesIds( $linksArray );
				
		$this->assertEquals(3, count($res),'count($res)');
		
		$linkIds = $res[0];
		$unlinkIds = $res[1];
		$skippedLinkIds = $res[2];
		
		$this->assertEquals(array(2),$linkIds,'$linkIds');
		$this->assertEquals(array(),$unlinkIds,'$unlinkIds');
		$this->assertEquals(array(),$skippedLinkIds,'$skippedLinkIds');
	}
	
	public function testGetLinkedArticlesIdsNoLinks() {
		$sourceArticle_B = JTable::getInstance ( 'content' );
		$sourceArticle_B->load ( 2 );
		$linksArray = XLinkArticlesHelper::getLinksArray($sourceArticle_B, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSeparator, $this->isSpaceAddedLinkSeparator, $this->userMessageArray);
		
		$res = XLinkArticlesHelper::getLinkedArticlesIds( $linksArray );
				
		$this->assertEquals(3,count($res),"count(res)");
		
		$linkIds = $res[0];
		$unlinkIds = $res[1];
		$skippedLinkIds = $res[2];
				
		$this->assertEquals(0,count($linkIds),'count($linkIds)');
		$this->assertEquals(0,count($unlinkIds),'count($$unlinkIds)');
		$this->assertEquals(0,count($skippedLinkIds),'count($$skippedLinkIds)');
	}
	
	public function testGetLinkedArticlesIdsNoLinksM() {
		$sourceArticle_M = JTable::getInstance ( 'content' );
		$sourceArticle_M->load ( 13 );
		$linksArray = XLinkArticlesHelper::getLinksArray($sourceArticle_M, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSeparator, $this->isSpaceAddedLinkSeparator, $this->userMessageArray);
		
		$res = XLinkArticlesHelper::getLinkedArticlesIds( $linksArray );
				
		$this->assertEquals(3,count($res),"count(res)");
		
		$linkIds = $res[0];
		$unlinkIds = $res[1];
		$skippedLinkIds = $res[2];
				
		$this->assertEquals(0,count($linkIds),'count($linkIds)');
		$this->assertEquals(0,count($unlinkIds),'count($$unlinkIds)');
		$this->assertEquals(0,count($skippedLinkIds),'count($$skippedLinkIds)');
	}
	
	public function testRebuidLinksWithoutLinkOnSourceArticleTwoLinksWithOneLinkOnA() {
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", array("<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a>", "<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=2:b\">B</a>"), $this->linkSeparator);
		
		$this->assertEquals("<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=2:b\">B</a>", $newLinkString);
	}
	
	public function testRebuidLinksWithoutLinkOnSourceArticleOneLinkOnA() {
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", array("<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a>"), $this->linkSeparator);
		
		$this->assertEquals("", $newLinkString);
	}
	
	public function testRebuidLinksWithoutLinkOnSourceArticleOneLinkOnB() {
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", array("<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=2:b\">B</a>"), $this->linkSeparator);
		
		$this->assertEquals("<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=2:b\">B</a>", $newLinkString);
	}
	
	public function testRebuidLinksWithoutLinkOnSourceArticleNoLinks() {
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", array(), $this->linkSeparator);
		
		$this->assertEquals("", $newLinkString);
	}
	
	public function testRebuidLinksWithoutLinkOnArticleG() {
		$targetArticle_G = JTable::getInstance ( 'content' );
		$targetArticle_G->load ( 7 );
		$linksArray = XLinkArticlesHelper::getLinksArray($targetArticle_G, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSeparator, $this->isSpaceAddedLinkSeparator, $this->userMessageArray);	
		
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", $linksArray, $this->linkSeparator);
		
		$this->assertEquals("<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=2:b\">B</a>", $newLinkString);
	}
	
	public function testRebuidLinksWithoutLinkOnArticleF() {
		$targetArticle_F = JTable::getInstance ( 'content' );
		$targetArticle_F->load ( 6 );
		$linksArray = XLinkArticlesHelper::getLinksArray($targetArticle_F, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, $this->linkSeparator, $this->isSpaceAddedLinkSeparator, $this->userMessageArray);	
		
		$newLinkString = XLinkArticlesHelper::rebuidLinksWithoutLinkOnSourceArticle("id=1:a", $linksArray, $this->linkSeparator);
		
		$this->assertEquals("<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=3:c\">C</a>", $newLinkString);
	}
	
	public function testGetLinkSectionComponentsWhereClassLK() {
		$targetArticle_J = JTable::getInstance ( 'content' );
		$targetArticle_J->load ( 10 );
		$components = XLinkArticlesHelper::getLinkSectionComponents($targetArticle_J, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, "not found", $this->userMessageArray);

		$this->assertEquals(4, count($components));
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace . "<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a></p>",$components[0],'$components[0]');
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace,$components[1],'$components[1]');
		$this->assertEquals("<a class=\"LK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a>",$components[2],'$components[2]');
		$this->assertEquals("</p>",$components[3],'$components[3]');
	}
		
	public function testGetLinkSectionComponentsWhereClassULK() {
		$targetArticle_K = JTable::getInstance ( 'content' );
		$targetArticle_K->load ( 11 );
		$components = XLinkArticlesHelper::getLinkSectionComponents($targetArticle_K, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, "not found", $this->userMessageArray);

		$this->assertEquals(4, count($components));
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace . "<a class=\"ULK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a></p>",$components[0],'$components[0]');
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace,$components[1],'$components[1]');
		$this->assertEquals("<a class=\"ULK\" href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a>",$components[2],'$components[2]');
		$this->assertEquals("</p>",$components[3],'$components[3]');
	}
		
	public function testGetLinkSectionComponentsWhereNoClass() {
		$targetArticle_F = JTable::getInstance ( 'content' );
		$targetArticle_F->load ( 6 );
		$components = XLinkArticlesHelper::getLinkSectionComponents($targetArticle_F, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, "not found", $this->userMessageArray);

		$this->assertEquals(4, count($components));
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace . "<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=3:c\">C</a>, <a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a></p>",$components[0],'$components[0]');
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace,$components[1],'$components[1]');
		$this->assertEquals("<a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=3:c\">C</a>, <a href=\"index.php?option=com_content&amp;view=article&amp;catid=103:francais&amp;id=1:a\">A</a>",$components[2],'$components[2]');
		$this->assertEquals("</p>",$components[3],'$components[3]');
	}
		
	public function testGetLinkSectionComponentsWhereEmptyLinkSection() {
		$targetArticle_E = JTable::getInstance ( 'content' );
		$targetArticle_E->load ( 5 );
		$components = XLinkArticlesHelper::getLinkSectionComponents($targetArticle_E, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, "No change performed !", $this->userMessageArray);

		$this->assertEquals(4, count($components));
		$this->assertEquals($this->linkSectionStartString . "</p>",$components[0],'$components[0]');
		$this->assertEquals($this->linkSectionStartString . $this->linkSectionStartStringSepOneSpace,$components[1],'$components[1]');
		$this->assertEquals("",$components[2],'$components[2]');
		$this->assertEquals("</p>",$components[3],'$components[3]');
		$this->assertEquals ( 0 , count ( $this->userMessageArray), 'count ( $this->userMessageArray)' );
	}
		
	public function testGetLinkSectionComponentsWhereNoLinkSection() {
		JPlugin::loadLanguage( 'plg_content_xlink_articles', JPATH_ADMINISTRATOR);
		$targetArticle_H = JTable::getInstance ( 'content' );
		$targetArticle_H->load ( 8 );
		$components = XLinkArticlesHelper::getLinkSectionComponents($targetArticle_H, $this->linkSectionStartString, $this->isSpaceAddedlinkSectionStartString, "No change performed", $this->userMessageArray);

		$this->assertEquals(0, count($components));
		$this->assertEquals ( 1 , count ( $this->userMessageArray), 'count ( $this->userMessageArray)' );
		$this->assertEquals ( "!!! 'Ecouter également ' section not found in article 8 - H. No change performed !", $this->userMessageArray[0], '$this->userMessageArray[0]' );
	}
	
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet() {
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\data\1_category_1_article_test_data.xml' );
	}
}

?>