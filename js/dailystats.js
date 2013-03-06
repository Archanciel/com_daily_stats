/* form controls manipulation javascript */

/**
 * Sets the hidden draw_chart input field to yes and submit the form causing
 * the chart to be drawn.
 */
function handleSelectArticle() {
	document.forms['dailyStatsForm'].draw_chart.value = 'yes';
	document.dailyStatsForm.submit();
}