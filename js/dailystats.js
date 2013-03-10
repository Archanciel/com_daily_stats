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
 * Called when an article is selected in the article combo
 */
function handleSelectArticle() {
	drawChart();
}

/**
 * Called when the chart whole category checkbox is checked/unchecked
 */
function handleChartWholeCategory(checkbox) {
	if (checkbox.checked) {
		document.getElementById('select_article').disabled=true;
	} else {
		document.getElementById('select_article').disabled=false;
	}
	
	drawChart();
}

/**
 * Sets the hidden draw_chart input field to yes and submit the form causing
 * the chart to be drawn.
 */
function drawChart() {
	document.forms['dailyStatsForm'].draw_chart.value = 'yes';
	document.dailyStatsForm.submit();
}