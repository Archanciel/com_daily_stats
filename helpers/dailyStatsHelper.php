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

class DailyStatsHelper {
	
	/**
	 *
	 * @return either CALLED_FROM_BACKEND or CALLED_FROM_FRONTEND
	 */
	public static function determineExecEnv($file) {
		return (strpos($file, 'administrator') != FALSE) ? CALLED_FROM_BACKEND : CALLED_FROM_FRONTEND;
	}
}
?>