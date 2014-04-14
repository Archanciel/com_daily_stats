<?php

require_once dirname ( __FILE__ ) . '\DailyStatsTestBase.php';
require_once COM_DAILYSTATS_PATH . '\dao\dailyStatsDao.php';
require_once COM_DAILYSTATS_PATH . '\dailyStatsConstants.php';

class DailyStatsDaoTest extends DailyStatsTestBase {
	
	public function setUp() {
		parent::setUp ();
	}
	
	/**
	 * Tests last date hits and downloads and total hits and downloads obtention
	 * for one article, one category and all categories
	 */
	public function testGetLastAndTotalHitsAndDownloadsArr() {
		// for one article
		$this->getLastAndTotalHitsAndDownloadsArrForOneArtticle_1_dailyStats();
		$this->getLastAndTotalHitsAndDownloadsArrForOneArtticle_2_dailyStats();
		$this->getLastAndTotalHitsAndDownloadsArrForOneArtticle_no_dailyStats();
	}
	
	/**
	 * Tests 1 article with only 1 daily stats recs
	 */
	private function getLastAndTotalHitsAndDownloadsArrForOneArtticle_1_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,1);
		
		$this->assertEquals(5, count($res),'count($res)');
		
		$this->assertEquals('20-10',$res[DATE_IDX],'date');
		$this->assertEquals(15,$res[LAST_HITS_IDX],'date hits');
		$this->assertEquals(150,$res[TOTAL_HITS_IDX],'total hits');
		$this->assertEquals(10,$res[LAST_DOWNLOADS_IDX],'date downloads');
		$this->assertEquals(100,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
	}
	
	/**
	 * Tests 1 article with 2 daily stats recs
	 */
	private function getLastAndTotalHitsAndDownloadsArrForOneArtticle_2_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,2);
	
		$this->assertEquals(5, count($res),'count($res)');
	
		$this->assertEquals('21-11',$res[DATE_IDX],'date');
		$this->assertEquals(150,$res[LAST_HITS_IDX],'date hits');
		$this->assertEquals(1500,$res[TOTAL_HITS_IDX],'total hits');
		$this->assertEquals(100,$res[LAST_DOWNLOADS_IDX],'date downloads');
		$this->assertEquals(1000,$res[TOTAL_DOWNLOADS_IDX],'total downloads');
	}
	
	/**
	 * Tests 1 article with no daily stats rec
	 */
	private function getLastAndTotalHitsAndDownloadsArrForOneArtticle_no_dailyStats() {
		$res = DailyStatsDao::getLastAndTotalHitsAndDownloadsArr(CHART_MODE_ARTICLE,3);
		
		$this->assertEquals(5, count($res),'count($res)');
		
		$this->assertNull($res[DATE_IDX],'date');
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