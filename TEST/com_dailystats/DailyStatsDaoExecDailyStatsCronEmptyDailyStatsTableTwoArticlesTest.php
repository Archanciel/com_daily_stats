<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

/**
 * 
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronEmptyDailyStatsTableTwoArticlesTest extends DailyStatsTestBase {
	
	/**
	 * Tests daily stats rec generation for 2 articles with 1 attachment each and 1 unpublished article
	 * with 1 attachment in an empty daily stats table
	 */
	public function testExecDailyStatsCronEmptyDailyStatsTableTwoArticles() {
		DailyStatsDao::execDailyStatsCron("#__daily_stats_cron_test","#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__daily_stats_cron_test"; 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(2,$count,'2 daily_stats records expected');
		
		$today = date("Y-m-d");
		
		// article 1
		$query = "SELECT * FROM #__daily_stats_cron_test WHERE article_id = 1";
		$db->setQuery($query);
		$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(111,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(11,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		// article 2
		$query = "SELECT * FROM #__daily_stats_cron_test WHERE article_id = 2"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(112,$res['date_hits'],'date hits');
		$this->assertEquals(112,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(12,$res['date_downloads'],'date downloads');
		$this->assertEquals(12,$res['total_downloads_to_date'],'total downloads');
	}
	
	public function setUp() {
		parent::setUp ();
	}
	
	public function tearDown() {
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "TRUNCATE TABLE #__daily_stats_cron_test"; 
    	$db->setQuery($query);
		$db->query();
		
		parent::tearDown();
	}
	
	/**
	 * Gets the data set to be loaded into the database during setup.
	 * 
	 * @return xml dataset
	 */
	protected function getDataSet() {
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\data\dailyStatsCron_empty_dstable_2_articles_test_data.xml' );
	}
}

?>