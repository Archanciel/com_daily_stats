<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

/**
 * 
 * @author Jean-Pierre
 *
 */
class DailyStatsDaoExecDailyStatsCronNoAttachmentsTest extends DailyStatsTestBase {
	
	/**
	 * Tests 1 article with only 1 daily stats recs
	 */
	public function testExecDailyStatsCronForArticlesWithNoAttachments() {
		DailyStatsDao::execDailyStatsCron("#__daily_stats_cron_test","#__attachments_cron_test","#__content_cron_test");

     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__daily_stats_cron_test"; 
    	$db->setQuery($query);
    	$count = $db->loadResult();
    			
		$this->assertEquals(0,$count,'0 daily_stats records expected');
// 		$this->assertEquals(5, count($res),'count($res)');
		
// 		$this->assertEquals('20-10',$res[DATE_IDX],'date');
// 		$this->assertEquals(15,$res[LAST_HITS_IDX],'date hits');
// 		$this->assertEquals(150,$res[TOTAL_HITS_IDX],'total hits');
// 		$this->assertEquals(10,$res[LAST_DOWNLOADS_IDX],'date downloads');
// 		$this->assertEquals(100,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
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
		return $this->createXMLDataSet ( dirname ( __FILE__ ) . '\data\dailyStatsCron_test_data_noAttachments.xml' );
	}
}

?>