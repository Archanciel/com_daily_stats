<?php 
/********************************************************************
Product    : SimplePlot
Date       : 19 January 2013
Copyright  : Les Arbres Design 2010-2013
Contact    : http://extensions.lesarbresdesign.info
Licence    : GNU General Public License
Description: Simplest possible component to demonstrate embedded Plotalot
             (not intended as an example of how to build a Joomla component!)
*********************************************************************/
defined('_JEXEC') or die('Restricted Access'); 

	JToolBarHelper::title('SimplePlot', '');

// get a list of years from the jos_plot_sample table

	$db	= JFactory::getDBO();
	$query = "SELECT DISTINCT(YEAR(`Date`)) AS year FROM `#__plot_sample`";
	$db->setQuery($query);
	$rows = $db->loadObjectList();

// get the selected year from the select list (default it to zero)

	$year = JRequest::getVar('select_year',0);
	if ($year == 0)
		$year = $rows[0]->year;		// default to the first row

// Build an html select list of years (include Javascript to submit the form)

	foreach ($rows as $row)
		$title_array[] = JHTML::_('select.option', $row->year, $row->year);
	$select_list = JHTML::_('select.genericlist', $title_array, 'select_year', 
		'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $year);

// draw the form with the select list

	echo '<form action="index.php" method="post" name="adminForm" method="post">';
	echo '<input type="hidden" name="option" value="com_simpleplot" />';
	echo 'Select a year: ';
	echo $select_list;
	echo '</form>';
	
// pull in the Plotalot helper file from the backend helpers directory

	require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/plotalot.php';

// construct the plot info structure

	$plot_info = new stdclass();
	$plot_info->id = 1;						// the id must match the html element that the chart will be drawn in
	$plot_info->chart_title = "Temperatures for $year";
	$plot_info->chart_type = CHART_TYPE_LINE;
	$plot_info->x_size = 1200;
	$plot_info->y_size = 400;
	$plot_info->x_labels = 12;
	$plot_info->x_format = FORMAT_DATE_MON;
	$plot_info->x_start = "SELECT UNIX_TIMESTAMP(DATE('$year-01-01'))";
	$plot_info->x_end = "SELECT UNIX_TIMESTAMP(DATE('$year-12-31'))";
	$plot_info->y_labels = 7;
	$plot_info->y_start = 5;
	$plot_info->y_end = 30;
	$plot_info->legend_type = LEGEND_NONE;
	$plot_info->show_grid = 1;
	$plot_info->num_plots = 1;
	$plot_info->extra_parms = ",chartArea:{left:'8%',top:'10%',width:'90%',height:'75%'}";

// construct the plot array

	$plot_info->plot_array = array();
	$plot_info->plot_array[0]['enable'] = 1;
	$plot_info->plot_array[0]['colour'] = '7C78FF';
	$plot_info->plot_array[0]['style'] = LINE_THICK_SOLID;
	$plot_info->plot_array[0]['legend'] = 'Temperature';
	$plot_info->plot_array[0]['query'] = "SELECT UNIX_TIMESTAMP(`Date`), `Average` 
		FROM `#__plot_sample` WHERE YEAR(`Date`) = $year ORDER BY `Date`";

// draw the chart

	$plotalot = new Plotalot;
	$chart = $plotalot->drawChart($plot_info);

	if ($chart == '')
		echo $plotalot->error;
	else
		{
		$document = JFactory::getDocument();
		$document->addScript("https://www.google.com/jsapi");	// load the Google jsapi
		$document->addCustomTag($chart);						// load the chart script
		echo '<div id="chart_1"></div>';						// create an element for the chart to be drawn in
		}

	echo "<br /><br /><b>Simple Plot also works on the front end. </b><br />";
	echo "Create a menu item of type SimplePlot, then go the your site front end and click on the new menu item";
	

?>
