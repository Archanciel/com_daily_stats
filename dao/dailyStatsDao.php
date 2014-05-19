<?php 
/********************************************************************
 Product    : Daily Stats
Date       : March 2013
Copyright  : jps.dev
Contact    : http://jps.dev
Licence    : GNU General Public License
Description: displays article and attached audio file access and usage
daily stats on a chart
Based on   : SimplePlot from Les Arbres Design
(http://extensions.lesarbresdesign.info)
*********************************************************************/

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/dailyStatsConstants.php';

class DailyStatsDao {
     
     /**
      * This method is called when the following request is made on plusconscient:
      * http://localhost/plusconscient15_dev/index.php?option=com_dailystats&cron=yes. 
      * The request is performed daily by a cron job.
      * 
      * The method inserts daily stats in the jox_daily_stats table.
	  * 
	  * @param String $dailyStatsTableName. Only supplied when unit testing the method since
	  *                                     we need to test it against an empty daily_stats table !
	  * @param unknown_type $attachmentsTableName. Only supplied when unit testing.
	  * @param unknown_type $contentTableName. Only supplied when unit testing.
	  */
     public static function execDailyStatsCron($dailyStatsTableName,$attachmentsTableName,$contentTableName) {
		jimport('joomla.error.log');
		define(MAX_DAY_INTERVAL, 20);
		
		if (!isset($dailyStatsTableName)) {
			$dailyStatsTableName = "#__daily_stats";
			$attachmentsTableName = "#__attachments";
			$contentTableName = "#__content";
		}
     	
     	/* @var $db JDatabase */
    	$db = JFactory::getDBO();
    	$log = JLog::getInstance("com_dailystats_log.php");
		$query = "SELECT COUNT(id) 
				  FROM $dailyStatsTableName;";
    	
    	$count = self::loadResult($db, $query);
    	
    	if ($count > 0) {
    		// daily_stats table not empty
    		$query = "SELECT DATE_FORMAT(MAX(date),'%Y-%m-%d') 
    				  FROM $dailyStatsTableName;";
    		$maxDate = self::loadResult($db, $query);
    		$today = date("Y-m-d");
    		
    		if (strcmp($maxDate,$today) == 0) {
    			// protecting for duplicate insertion of daily stats data
				$entry = array ('LEVEL' => '1', 'STATUS' => 'INFO:', 'COMMENT' => "Stats for today already exist in daily_stats table. No data inserted." );
				$log->addEntry($entry);
    			return;
    		}
    		
    		// inserting daily_stats for existing attachments
    		
    		$gap = 1;	// used to handle the case where cron execution was skipped the day(S) before 
    		$rowsNumberForExistingAttachments = 0;
    		
    		while ( $rowsNumberForExistingAttachments == 0	&&
    				$gap <= MAX_DAY_INTERVAL) {
    			$dailyStatsQuery = "INSERT INTO $dailyStatsTableName 
      									(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads) 
									SELECT T2.id AS article_id, T1.id as attachment_id, CURRENT_DATE, T2.hits, T2.hits - T3.total_hits_to_date, T1.download_count,  T1.download_count - T3.total_downloads_to_date
									FROM $attachmentsTableName T1, $contentTableName T2, $dailyStatsTableName T3
									WHERE T1.article_id = T2.id AND T2.id = T3.article_id AND T1.id = T3.attachment_id AND DATE_SUB(CURRENT_DATE,INTERVAL $gap DAY) = T3.date;";
	    		
		    	$rowsNumberForExistingAttachments = self::executeInsertQuery($db, $dailyStatsQuery, $log);
		    	
		    	if ($rowsNumberForExistingAttachments == 0) {
			    	$gap++;
		    	}
    		}
    		
    		// inserting daily_stats for new attachments
    		
    		$query = "INSERT INTO $dailyStatsTableName
			    		(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads)
				    		SELECT T1.article_id, T1.id, CURRENT_DATE, T2.hits, T2.hits, T1.download_count, T1.download_count
				    		FROM $attachmentsTableName T1, $contentTableName T2
				    		WHERE T1.article_id = T2.id AND T2.state = 1 AND T1.id IN (
					    		SELECT T1.id
					    		FROM $attachmentsTableName T1 LEFT JOIN $dailyStatsTableName ON T1.id = $dailyStatsTableName" . ".attachment_id
					    		WHERE $dailyStatsTableName" . ".attachment_id IS NULL);";
    		$rowsNumberForNewAttachments = self::executeInsertQuery($db, $query, $log);
    		
    		if ($gap > MAX_DAY_INTERVAL) {
    			$entry = array ('LEVEL' => '1', 'STATUS' => 'ERROR:', 'COMMENT' => "Stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments. GAP EXCEEDS MAX INTERVAL OF " . MAX_DAY_INTERVAL . " DAYS !" );
       		} else {
				$entry = array ('LEVEL' => '1', 'STATUS' => 'INFO:', 'COMMENT' => "Stats for $today added in DB. $rowsNumberForNewAttachments rows inserted for new attachment(s). $rowsNumberForExistingAttachments rows inserted for existing attachments (gap filled: $gap day(s)). " );
    		}
    		
			$log->addEntry($entry);
    	} else {
       		// daily_stats table is empty and must be bootstraped
       		$query= "INSERT INTO $dailyStatsTableName 
         				(article_id, attachment_id, date, total_hits_to_date, date_hits, total_downloads_to_date, date_downloads)
					SELECT T1.article_id, T1.id, CURRENT_DATE, T2.hits, T2.hits, T1.download_count, T1.download_count
					FROM $attachmentsTableName T1, $contentTableName T2
					WHERE T1.article_id = T2.id AND T2.state = 1;";
	    	$rowsNumber = self::executeInsertQuery($db, $query, $log);
//    		self::executeQuery ( $db, "UPDATE $dailyStatsTableName SET date=DATE_SUB(date,INTERVAL 1 DAY);" ); only for creating test data !!
	    	
			$entry = array ('LEVEL' => '1', 'STATUS' => 'INFO:', 'COMMENT' => "daily_stats table successfully bootstraped. $rowsNumber rows inserted.");
			$log->addEntry($entry);
    	}
     }
	
	 private static function executeInsertQuery(JDatabase $db, $query, $log) {
		$db->setQuery ( $query );
		$db->query ();
		
		if ($db->getErrorNum ()) {
			$errorMsg = $db->getErrorMsg ();
			//print_r( $e );
			$entry = array ('LEVEL' => '1', 'STATUS' => 'ERROR:', 'COMMENT' => "INVALID DAILY_STATS RECORD ENCOUNTERED. CRON JOB ABORTED. NO DATA INSERTED. NEEDS IMMEDIATE FIX !\r\n\r\nERROR MSG FOLLOWS:\r\n\r\n$errorMsg" );
			$log->addEntry($entry);
//			JError::raiseError ( 500, $errorMsg );
			throw new Exception($errorMsg);
			return;
		}
		
		return $db->getAffectedRows();
	 }

     private static function loadResult(JDatabase $db, $query) {
    	$db->setQuery($query);
    	$res = $db->loadResult();
    	
    	if( $db->getErrorNum () ) {
			$e = $db->getErrorMsg();
			//print_r( $e );
			JError::raiseError( 500, $e );
			return null;
    	}
     	
    	return $res;
     }
	
	/**
	 * Explaining the MAX($yValName) in the CHART_MODE_ARTICLE query below:
	 * 
	 * Without it, for an article with more than one attachments, the query returns
	 * 
	 * 	     downl hits 
	 * 23-04-2013 	0 	0
	 * 24-04-2013 	1 	33
	 * 24-04-2013 	0 	0
	 * 24-04-2013 	0 	0
	 * 25-04-2013 	11 	36
	 * 25-04-2013 	0 	36
	 * 25-04-2013 	0 	36
	 * 
	 * which results in ploting a 0 instead of a 33 value for 24-04-2013 !
	 * 
	 * with the MAX($yValName) and GRUUP BY date addition, the result is
	 * 
	 * 23-04-2013 	0 	0
	 * 24-04-2013 	1 	33
	 * 25-04-2013 	11 	36
	 * 
	 * @param unknown_type $articleId
	 * @param unknown_type $categoryId
	 * @param unknown_type $yValName	either date_hits or date_downloads
	 * @param unknown_type $chartMode
	 * @return string
	 */
	public static function buildPlotDataQuery($articleId, $categoryId, $yValName, $chartMode) {
		switch ($chartMode) {
			case CHART_MODE_ARTICLE:
				$qu =  "SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.{$yValName}
				FROM (
					SELECT date, MAX($yValName) as $yValName
					FROM #__daily_stats
					WHERE article_id = $articleId
					GROUP BY date
					ORDER BY date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			case CHART_MODE_CATEGORY:
				$qu =	"SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.sum AS {$yValName}
				FROM (
					SELECT s.date, SUM(s.{$yValName}) AS sum
					FROM #__daily_stats AS s, #__content as c
					WHERE s.article_id = c.id AND c.sectionid = $categoryId
					GROUP BY s.date
					ORDER BY s.date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			case CHART_MODE_CATEGORY_ALL:
				if(version_compare(JVERSION,'1.6.0','ge')) {
					$excludedCategories = EXCLUDED_J16_CATEGORIES_SET;
				} else {
					$excludedCategories = EXCLUDED_J15_SECTIONS_SET;
				}

				// plotting total site (all categries activity
				$qu =	"SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.sum AS {$yValName}
				FROM (
					SELECT s.date, SUM(s.{$yValName}) AS sum
					FROM #__daily_stats AS s, #__content as c
					WHERE s.article_id = c.id 
					AND c.sectionid NOT IN ($excludedCategories)
					GROUP BY s.date
					ORDER BY s.date DESC
					LIMIT " . MAX_PLOT_POINTS . "
				) T1
				ORDER BY T1.date";
				return $qu;
				break;
			default:
				return '';
				break;
		}
	}
	
	/**
	 * Returns the list of top level content categories (Joonla 1.6 +) 
	 * or sections (Joomla 1.5)
	 */
	public static function getCategoriesOrSections() {
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$query = self::getCategoryQuery();
		} else {	// Joomla 1.5
			$query = self::getSectionQuery();
		}
		
		return self::executeQuery($query);
	}
	
	private static function executeQuery($query) {
		$db	= JFactory::getDBO();
		$db->setQuery($query);
		
		return $db->loadObjectList();
	}
	
	/**
	 * Adapted to Joomla 1.6 +
	 * 
	 * @return string
	 */
	private static function getCategoryQuery() {
		return "SELECT id,title FROM #__categories WHERE extension LIKE 'com_content' AND level = " . J16_SECTION_LEVEL . " AND id NOT IN (" . EXCLUDED_J16_CATEGORIES_SET . ") ORDER BY title";
	}
	
	/**
	 * Adapted to Joomla 1.5
	 * 
	 * @return string
	 */
	private static function getSectionQuery() {
		return "SELECT id,title FROM #__sections WHERE scope LIKE 'content' AND id NOT IN (" . EXCLUDED_J15_SECTIONS_SET . ") ORDER BY title";
	}
	
	/**
	 * Returns the list of articles for the passed cat/section)
	 */
	public static function getArticlesForCatSec($categorySectionId) {
		$query = self::getArticleQuery($categorySectionId);
	
		return self::executeQuery($query);
	}
	
	/**
	 *
	 * @param int $categorySectionId
	 * @return string
	 */
	private static function getArticleQuery($categorySectionId) {
		return "SELECT id, title, DATE_FORMAT(created,'%a %D, %M %Y') as creation_date
				FROM #__content WHERE sectionid = $categorySectionId
				ORDER BY title";
	}
	
	/**
	 * Returns the list of articles for the passed cat/section)
	 */
	public static function getMostRecentArticles($articleNumber) {
		$query = self::getMostRecentArticlesQuery($articleNumber);
	
		return self::executeQuery($query);
	}
	
	/**
	 *
	 * @param int $articleNumber
	 * @return string
	 */
	private static function getMostRecentArticlesQuery($articleNumber) {
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$excludedCategories = EXCLUDED_J16_CATEGORIES_SET;
		} else {
			$excludedCategories = EXCLUDED_J15_SECTIONS_SET;
		}

		$qu =  "SELECT DISTINCT c.id, c.title, DATE_FORMAT(c.created,'%a %D, %M %Y') as creation_date
				FROM #__daily_stats AS s, #__content as c
				WHERE s.article_id = c.id
				AND c.sectionid NOT IN ($excludedCategories)		
				ORDER BY c.created DESC
				LIMIT $articleNumber";

		return $qu;
	}
	
	public static function getLastAndTotalHitsAndDownloadsArr($chartMode, $id = NULL) {
		switch ($chartMode) {
			case CHART_MODE_ARTICLE:
				$qu = self::getLastAndTotalHitsAndDownloadsForArticleQuery($id);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			case CHART_MODE_CATEGORY:
				$qu = self::getLastAndTotalHitsAndDownloadsForCategoryQuery($id);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			case CHART_MODE_CATEGORY_ALL:
				if(version_compare(JVERSION,'1.6.0','ge')) {
					$excludedCategories = EXCLUDED_J16_CATEGORIES_SET;
				} else {
					$excludedCategories = EXCLUDED_J15_SECTIONS_SET;
				}
				
				$qu = self::getLastAndTotalHitsAndDownloadsForAllCategoriesQuery($excludedCategories);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			default:
				break;
		}

		return $ret;
	}
	
	/*
	 * Initial query:
	 * 
	 * SELECT DATE_FORMAT(date,'%d-%m') as displ_date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
	 * 		FROM jos_daily_stats
	 * 		WHERE article_id = 502
	 * 		AND date = (
	 * 				SELECT MAX(date)
	 * 				FROM jos_daily_stats t
	 * 				WHERE article_id = t.article_id
	 * 			)
	 * 
	 * Res:
	 * 		
	 * date 		d_hits 	tot_hits_td d_downl  tot_downl_td
	 * 26-04 		  37		106 	0 			1
	 * 26-04 		  37 		106 	0 			1
	 * 26-04 		  37 		106 	5 			17		
	 * 
	 * 
	 * Fixed query:
	 * 		
	 * SELECT T1.date, T1.date_hits, T1.total_hits_to_date, T1.date_downloads, T1.total_downloads_to_date FROM (
	 * 	SELECT date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
	 * 			FROM jos_daily_stats
	 * 			WHERE article_id = 502
	 * 			AND date = (
	 * 					SELECT MAX(date)
	 * 					FROM jos_daily_stats t
	 * 					WHERE article_id = t.article_id
	 * 				)
	 * ) T1
	 * ORDER BY T1.total_downloads_to_date DESC
	 * LIMIT 1
	 * 
	 * Res:
	 * 
	 * date 		d_hits 	tot_hits_td d_downl  tot_downl_td
	 * 2013-04-26 	  37 	   106 		   5			17
	 */
	private static function getLastAndTotalHitsAndDownloadsForArticleQuery($articleId) {
		$qu =  "SELECT DATE_FORMAT(T1.date,'%d-%m') as displ_date, T1.date_hits, T1.total_hits_to_date, T1.date_downloads, T1.total_downloads_to_date FROM (
					SELECT date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
						FROM #__daily_stats ds1
						WHERE ds1.article_id = $articleId
						AND date = (
							SELECT MAX(ds2.date)
							FROM #__daily_stats ds2
							WHERE ds2.article_id = $articleId
						)
				) T1
				ORDER BY T1.total_downloads_to_date DESC
				LIMIT 1";
		return $qu;
	}
	
	private static function getLastAndTotalHitsAndDownloadsForCategoryQuery($categoryId) {
		$qu =  "SELECT DATE_FORMAT(ds1.date,'%d-%m') as displ_date, SUM(ds1.date_hits) date_hits, SUM(ds1.total_hits_to_date) total_hits_to_date, SUM(ds1.date_downloads) date_downloads, SUM(ds1.total_downloads_to_date) total_downloads_to_date
				FROM #__daily_stats ds1, #__content c
				WHERE ds1.article_id = c.id
				AND c.sectionid = $categoryId
				AND ds1.date = (
					SELECT MAX(ds2.date)
					FROM #__daily_stats ds2, #__content c2
					WHERE ds2.article_id = c2.id
					AND c2.sectionid = $categoryId)";	
		return $qu;
	}
	
	private static function getLastAndTotalHitsAndDownloadsForAllCategoriesQuery($excludedCategories) {
		$qu =  "SELECT DATE_FORMAT(s.date,'%d-%m') as displ_date, SUM(s.date_hits) date_hits, SUM(s.total_hits_to_date) total_hits_to_date, SUM(s.date_downloads) date_downloads, SUM(s.total_downloads_to_date) total_downloads_to_date
				FROM #__daily_stats AS s, #__content as c
				WHERE s.article_id = c.id
				AND c.sectionid NOT IN ($excludedCategories)
				AND s.date = (
					SELECT MAX(date)
					FROM #__daily_stats)";
		return $qu;
	}
}

?>