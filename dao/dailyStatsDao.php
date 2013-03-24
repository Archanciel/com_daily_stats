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
				SELECT date, $yValName
				FROM #__daily_stats
				WHERE article_id = $articleId
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
		return "SELECT id, title, DATE_FORMAT(created,'%a %D, %M %Y') as creation_date FROM #__content WHERE sectionid = $categorySectionId ORDER BY title";
	}
	
	public static function getLastAndTotalHitsArr($chartMode, $id = NULL) {
		switch ($chartMode) {
			case CHART_MODE_ARTICLE:
				$qu = self::getLastAndTotalHitsForArticleQuery($id);
				$rows = self::executeQuery($qu);
				
				$ret[DATE_IDX] = $rows[0]->displ_date;
				$ret[LAST_HITS_IDX] = $rows[0]->date_hits;
				$ret[TOTAL_HITS_IDX] = $rows[0]->total_hits_to_date;
				$ret[LAST_DOWNLOADS_IDX] = $rows[0]->date_downloads;
				$ret[TOTAL_DOWNLOADS_IDX] = $rows[0]->total_downloads_to_date;
				break;
			case CHART_MODE_CATEGORY:
				$qu = self::getLastAndTotalHitsForCategoryQuery($id);
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
				
				$qu = self::getLastAndTotalHitsForAllCategoriesQuery($excludedCategories);
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
	
	private static function getLastAndTotalHitsForArticleQuery($articleId) {
		$qu = 	"SELECT DATE_FORMAT(date,'%d-%m') as displ_date, date_hits, total_hits_to_date, date_downloads, total_downloads_to_date
		FROM #__daily_stats
		WHERE article_id = $articleId
		AND date = (
		SELECT MAX(date)
		FROM #__daily_stats t
		WHERE article_id = t.article_id)";
		return $qu;
	}
	
	private static function getLastAndTotalHitsForCategoryQuery($categoryId) {
		$qu = 	"SELECT DATE_FORMAT(s.date,'%d-%m') as displ_date, SUM(s.date_hits) date_hits, SUM(s.total_hits_to_date) total_hits_to_date, SUM(s.date_downloads) date_downloads, SUM(s.total_downloads_to_date) total_downloads_to_date
		FROM #__daily_stats AS s, #__content as c
		WHERE s.article_id = c.id
		AND c.sectionid = $categoryId
		AND s.date = (
		SELECT MAX(date)
		FROM #__daily_stats)";
		return $qu;
	}
	
	private static function getLastAndTotalHitsForAllCategoriesQuery($excludedCategories) {
		$qu = 	"SELECT DATE_FORMAT(s.date,'%d-%m') as displ_date, SUM(s.date_hits) date_hits, SUM(s.total_hits_to_date) total_hits_to_date, SUM(s.date_downloads) date_downloads, SUM(s.total_downloads_to_date) total_downloads_to_date
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