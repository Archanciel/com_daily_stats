<?php

require_once dirname ( __FILE__ ) . '..\..\baseclass\DailyStatsCronTestBase.php';
require_once COM_DAILYSTATS_PATH . '..\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '..\dailyStatsConstants.php';

/**
 * This class nsures that when executing a daily stats cron on a db containing 1 published article
 * with one attachment , in an empty daily stats table, 1 daily stats rec is generated.
 * 
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronEmptyDailyStatsTableOneArticleTest extends DailyStatsCronTestBase {
	private $daily_stats_table_name = "daily_stats_cron_test";
	
	/**
	 * Tests daily stats rec generation for 1 article with 1 attachment in an empty daily stats 
	 * table
	 */
	public function testExecDailyStatsCronEmptyDailyStatsTableOneArticle() {
		DailyStatsDao::execDailyStatsCron("#__" . $this->daily_stats_table_name,"#__attachments_cron_test","#__content_cron_test");
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__" . $this->daily_stats_table_name; 
    	$db->setQuery($query);
    	$count = $db->loadResult();

		$this->assertEquals(1,$count,'1 daily_stats records expected');
		
		$today = date("Y-m-d");
		$query = "SELECT * FROM #__" . $this->daily_stats_table_name . " WHERE article_id = 1"; 
    	$db->setQuery($query);
    	$res = $db->loadAssoc();
		
		$this->assertEquals("$today",$res['date'],'date');
		$this->assertEquals(111,$res['date_hits'],'date hits');
		$this->assertEquals(111,$res['total_hits_to_date'],'total hits');
		$this->assertEquals(11,$res['date_downloads'],'date downloads');
		$this->assertEquals(11,$res['total_downloads_to_date'],'total downloads');
		
		$this->checkEntryExistInLog("daily_stats table successfully bootstraped. 1 rows inserted.");
	}
	
	public function setUp() {
		parent::setUp ();
	}
	
	public function tearDown() {
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "TRUNCATE TABLE #__" . $this->daily_stats_table_name; 
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\..\data\dailyStatsCron_empty_dstable_1_article_test_data.xml' );
	}
}

?>