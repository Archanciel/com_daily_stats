<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

/**
 * This dummy test class is run (as PHPUnit test) for the unique purpose of populating the database 
 * with read only test data used by the real DailyStatsDao test classes.
 * 
 * @author Jean-Pierre
 *
 */
class PopulateDailyStatsDaoReadOnlyTestDataTest extends DailyStatsTestBase {
	
	public function setUp() {
		parent::setUp ();
	}
	
	/**
	 * Uncomment out the body when you want regenerate the read only test data !
	 */
	public function tearDown() {
// 		parent::tearDown();
	}
	
	/**
	 * Tests last date hits and downloads and total hits and downloads obtention
	 * for one article, one category and all categories
	 */
	public function testDummy() {
		// empty dummy test case
		$this->assertTrue(TRUE);
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