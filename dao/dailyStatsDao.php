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

class DailyStatsDao {
	/**
	 *
	 * @param unknown_type $articleId
	 * @param unknown_type $categoryId
	 * @param unknown_type $yValName
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
				// plotting total site (all categries activity
				$qu =	"SELECT DATE_FORMAT(T1.date,'%d-%m-%Y'), T1.sum AS {$yValName}
				FROM (
				SELECT s.date, SUM(s.{$yValName}) AS sum
				FROM #__daily_stats AS s, #__content as c
				WHERE s.article_id = c.id
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
	 * Adapted to Joomla 1.6 +
	 * 
	 * @return string
	 */
	public static function getCategoryQuery() {
		return "SELECT id,title FROM #__categories WHERE extension LIKE 'com_content' AND level = " . J16_SECTION_LEVEL . " AND id NOT IN (" . EXCLUDED_J16_CATEGORIES_SET . ") ORDER BY title";
	}
	
	/**
	 * Adapted to Joomla 1.5
	 * 
	 * @return string
	 */
	public static function getSectionQuery() {
		return "SELECT id,title FROM #__sections WHERE scope LIKE 'content' AND id NOT IN (" . EXCLUDED_J15_SECTIONS_SET . ") ORDER BY title";
	}

	/**
	 * 
	 * @param int $categorySectionId
	 * @return string
	 */
	public static function getArticleQuery($categorySectionId) {
		return "SELECT id, title, DATE_FORMAT(created,'%a %D, %M %Y') as creation_date FROM #__content WHERE sectionid = $categorySectionId ORDER BY title";
	}
}

?>