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

// categories to exclude from caregory list: aide, uncategorized, en conscience, les incontournables, pensées, site, webmaster
define(EXCLUDED_J16_CATEGORIES_SET,"116, 2, 129, 133, 128, 115, 127");
define(J16_SECTION_LEVEL, 1);
define(EXCLUDED_J15_SECTIONS_SET,"6, 7, 22, 23");

define(CHART_X_SIZE,1290);

// error_reporting(E_ALL | E_STRICT);
// ini_set('display_errors', 'on');

JToolBarHelper::title('Daily Stats', '');

$mainframe = JFactory::getApplication();

// get list of categories

$db	= JFactory::getDBO();

if(version_compare(JVERSION,'1.6.0','ge')) {
	$query = "SELECT id,title FROM #__categories WHERE extension LIKE 'com_content' AND level = " . J16_SECTION_LEVEL . " AND id NOT IN (" . EXCLUDED_J16_CATEGORIES_SET . ") ORDER BY title";
} else {	// Joomla 1.5
	$query = "SELECT id,title FROM #__sections WHERE scope LIKE 'content' AND id NOT IN (" . EXCLUDED_J15_SECTIONS_SET . ") ORDER BY title";
}

$db->setQuery($query);
$rows = $db->loadObjectList();

// get the selected category from the select list (default it to zero)

$categorySectionId = JRequest::getVar('select_category_section',0);
$previouslySelectedCategorySectionId = $mainframe->getUserState( "option.previous_select_category_section", 0 );
// 		echo 'Category: from request ' . $categorySectionId . ' previous from session ' . $previouslySelectedCategorySectionId;

if ($categorySectionId != $previouslySelectedCategorySectionId	||
		$previouslySelectedCategorySectionId == 0) {
	$mainframe->setUserState( "option.previous_select_category_section",$categorySectionId);
	$articleId = 0;
} else {
	$articleId = JRequest::getVar('select_article',0);
	// 			echo ' Article: from request ' . $articleId;
}

if ($categorySectionId == 0) {
	$categorySectionId = $rows[0]->id;		// default to the first row
}

// Build an html select list of categories (include Javascript to submit the form)

foreach ($rows as $row) {
	$category_array[] = JHTML::_('select.option', $row->id, $row->title);
}

$select_category_section_list = JHTML::_('select.genericlist', $category_array, 'select_category_section',
		'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $categorySectionId);

// get list of articles

$db	= JFactory::getDBO();
$query = "SELECT id,title FROM #__content WHERE sectionid = $categorySectionId ORDER BY title";
$db->setQuery($query);
$rows = $db->loadObjectList();

// get the selected article from the select list (default it to zero)

if ($articleId == 0 && sizeof($rows) > 0) {
	// default to the first row
	$articleId = $rows[0]->id;
}

// Build an html select list of articles (include Javascript to submit the form)

foreach ($rows as $row) {
	$article_array[] = JHTML::_('select.option', $row->id, $row->title);
		
	// store selected article title
	if ($row->id == $articleId) {
		$articleTitle = $row->title;
	}
}

if (!isset($articleTitle)) {
	// is the case if the user refreshes the page right after having changed the category
	$articleId = $rows[0]->id;
	$articleTitle = $rows[0]->title;
	// 			echo ' Article: row 0 (refresh) ' . $articleId;
}

$select_article_list = JHTML::_('select.genericlist', $article_array, 'select_article',
		'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $articleId);

// draw the form with the select lists

echo '<form action="index.php" method="post" name="adminForm" method="post">';
echo '<input type="hidden" name="option" value="com_dailystats" />';

if(version_compare(JVERSION,'1.6.0','ge')) {
	echo 'Select a category: ';
} else {
	echo 'Select a section: ';
}

echo $select_category_section_list;
echo 'Select an article: ';
echo $select_article_list;
echo '</form>';

// pull in the Plotalot helper file from the backend helpers directory

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/plotalot.php';

// --- Hits ------

// construct the plot info structure

$plot_info = new stdclass();
$plot_info->id = 1;						// the id must match the html element that the chart will be drawn in
$plot_info->chart_title = "Hits: " . $articleTitle;
$plot_info->chart_type = CHART_TYPE_BAR_V_GROUP;
$plot_info->x_size = CHART_X_SIZE;
$plot_info->y_size = 280;
$plot_info->x_title = "Date";
$plot_info->x_labels = 10;
$plot_info->x_format = FORMAT_DATE_DMY;
// $plot_info->x_start = "SELECT MIN(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
$plot_info->x_start = "";
$plot_info->x_end = "SELECT MAX(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
$plot_info->y_title = "Hits";
$plot_info->y_labels = 7;
$plot_info->y_start = 0;
//	$plot_info->y_end = 30;
$plot_info->legend_type = LEGEND_NONE;
$plot_info->show_grid = 1;
$plot_info->num_plots = 1;
$plot_info->extra_parms = ",chartArea:{left:'3%',top:'10%',width:'90%',height:'75%'}";

// construct the plot array

$plot_info->plot_array = array();
$plot_info->plot_array[0]['enable'] = 1;
$plot_info->plot_array[0]['colour'] = '7C78FF';
$plot_info->plot_array[0]['style'] = LINE_THICK_SOLID;
$plot_info->plot_array[0]['legend'] = 'Hits';
$plot_info->plot_array[0]['query'] = "SELECT DATE_FORMAT(date,'%d-%m-%y'), date_hits FROM #__daily_stats WHERE article_id = $articleId";

// draw the chart

$plotalot = new Plotalot;
$chart = $plotalot->drawChart($plot_info);

if ($chart == '') {
	echo $plotalot->error;
} else {
	$document = JFactory::getDocument();
	$document->addScript("https://www.google.com/jsapi");	// load the Google jsapi
	$document->addCustomTag($chart);						// load the chart script
	echo '<div id="chart_1"></div>';						// create an element for the chart to be drawn in
}

// --- Downloads ------

// construct the plot info structure

$plot_info = new stdclass();
$plot_info->id = 2;						// the id must match the html element that the chart will be drawn in
$plot_info->chart_title = "Downloads: " . $articleTitle;
$plot_info->chart_type = CHART_TYPE_BAR_V_GROUP;
$plot_info->x_size = CHART_X_SIZE;
$plot_info->y_size = 180;
$plot_info->x_title = "Date";
$plot_info->x_labels = 10;
$plot_info->x_format = FORMAT_DATE_DMY;
// $plot_info->x_start = "SELECT MIN(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
$plot_info->x_start = "";
$plot_info->x_end = "SELECT MAX(UNIX_TIMESTAMP(date)) FROM #__daily_stats WHERE article_id = $articleId";
$plot_info->y_title = "Downloads";
$plot_info->y_labels = 7;
$plot_info->y_start = 0;
//	$plot_info->y_end = 30;
$plot_info->legend_type = LEGEND_NONE;
$plot_info->show_grid = 1;
$plot_info->num_plots = 1;
$plot_info->extra_parms = ",chartArea:{left:'3%',top:'10%',width:'90%',height:'75%'}";

// construct the plot array

$plot_info->plot_array = array();
$plot_info->plot_array[0]['enable'] = 1;
$plot_info->plot_array[0]['colour'] = 'FF0000';
$plot_info->plot_array[0]['style'] = LINE_THICK_SOLID;
$plot_info->plot_array[0]['legend'] = 'Downloads';
$plot_info->plot_array[0]['query'] = "SELECT DATE_FORMAT(date,'%d-%m-%y'), date_downloads FROM #__daily_stats WHERE article_id = $articleId";

// draw the chart

$plotalot = new Plotalot;
$chart = $plotalot->drawChart($plot_info);

if ($chart == '') {
	echo $plotalot->error;
} else {
	$document = JFactory::getDocument();
	$document->addScript("https://www.google.com/jsapi");	// load the Google jsapi
	$document->addCustomTag($chart);						// load the chart script
	echo '<div id="chart_2"></div>';						// create an element for the chart to be drawn in
}


?>
