<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

class DailyStatsDaoTest extends DailyStatsTestBase {
	
	public function setUp() {
		parent::setUp ();
	}
	
	public function testGetLastAndTotalHitsAndDownloadsArr() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,1);
		
		$this->assertEquals(5, count($res),'count($res)');
		
		$this->assertEquals('20-10',$res[DATE_IDX],'date');
		$this->assertEquals(15,$res[LAST_HITS_IDX],'date hits');
		$this->assertEquals(150,$res[TOTAL_HITS_IDX],'total hits');
		$this->assertEquals(10,$res[LAST_DOWNLOADS_IDX],'date downloads');
		$this->assertEquals(100,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
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