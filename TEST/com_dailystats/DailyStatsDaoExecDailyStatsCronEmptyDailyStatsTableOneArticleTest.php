<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

/**
 * 
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronEmptyDailyStatsTableOneArticleTest extends DailyStatsTestBase {
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment and in an empty daily stats 
	 * table
	 */
	public function testExecDailyStatsCronEmptyDailyStatsTableOneArticle() {
		DailyStatsDao::execDailyStatsCron("#__daily_stats_cron_test","#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__daily_stats_cron_test"; 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(1,$count,'8 daily_stats records expected');
		
		$todayDayMonth = date("Y-m-d");
		$query = "SELECT * FROM #__daily_stats_cron_test WHERE article_id = 1"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals(8, count($res),'8 daily stats rec fields expected');
		
		$this->assertEquals("$todayDayMonth",$res['date'],'date');
		$this->assertEquals(111,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(11,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\data\dailyStatsCron_empty_dstable_1_article_test_data.xml' );
	}
}

?>