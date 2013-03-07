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

/**
 * Sets the hidden draw_chart input field to yes and submit the form causing
 * the chart to be drawn.
 */
function handleSelectArticle() {
	document.forms['dailyStatsForm'].draw_chart.value = 'yes';
	document.dailyStatsForm.submit();
}