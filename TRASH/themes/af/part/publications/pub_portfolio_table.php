<?php
// Generate portfolio table
$arTradeGroups = array();
$portfolio_response = apply_filters( 'af_portfolio_response_build', $portfolio_response );
foreach ($portfolio_response as $stock) {    
	if(!isset($arTradeGroups[$stock['tradeGroupId']]) && $stock['tradeGroupName']){
		$arTradeGroups[$stock['tradeGroupId']] = array(
			"name" => $stock['tradeGroupName'],
			"published" => 1,
			"tab" => 0,
			"stockCount" => 0
		);
	}
}
if(isset($pub_data['portfolio_tradegroups']) && is_array($pub_data['portfolio_tradegroups']) && count($pub_data['portfolio_tradegroups'])){
   foreach($pub_data['portfolio_tradegroups'] as $arAction){
	if(!($arAction['group_name'] && $arAction['action'])) continue;
	$curID=0;
	foreach($arTradeGroups as $id => $arVal){
		if($arVal['name'] == $arAction['group_name']) $curID = $id;
	}
	if(!$curID) continue;
	if('tab' == $arAction['action'] && $curID){
		$arTradeGroups[$curID]['tab'] = 1;
	}elseif('hide' == $arAction['action'] && $curID){
		$arTradeGroups[$curID]['published'] = 0;
	}
   }
}
//$open_button = '';
//$close_button = '';
//$editor_button = '';
//$watchlist_id = '';
//$header_count = 0;
$hide_open = $hide_open ? $hide_open : array();
$hide_closed = $hide_closed ? $hide_closed : array();
$content = '';
$arTabs=array('openContent' => 'Open','closeContent' => 'Closed');

foreach ($portfolio_response as $stock) {    
	//if entire tradegroup set as hidden
	if(isset($arTradeGroups[$stock['tradeGroupId']]['published']) && $arTradeGroups[$stock['tradeGroupId']]['published'] == 0) continue;
	if($stock['tradeGroupName'] != ''){//only the first one has a name value
		if(isset($arTradeGroups[$stock['tradeGroupId']]['tab']) && $arTradeGroups[$stock['tradeGroupId']]['tab'] == 1){
			//set as a tab
			$sendKey = preg_replace("/\W/","",$arTradeGroups[$stock['tradeGroupId']]['name']);
			//$arTabs["$sendKey"] = $stock['tradeGroupName'];
			$arTabs[$stock['tradeGroupId']] = $stock['tradeGroupName'];
			$content .= '<tr class="trade-group-header hidden-filter '.$stock['tradeGroupId'].'" data-id="' . $stock['tradeGroupId'] . '"><th colspan="' . count($portfolio_columns) . '">' . $stock['tradeGroupName'] . '</th></tr>';
		}else{
			$content .= '<tr class="trade-group-header openContent" data-id="' . $stock['tradeGroupId'] . '"><th colspan="' . count($portfolio_columns) . '">' . $stock['tradeGroupName'] . '</th></tr>';
			// Hide this one as default
			$content .= '<tr class="trade-group-header closeContent" data-id="' . $stock['tradeGroupId'] . '" style="display:none;"><th colspan="' . count($portfolio_columns) . '">' . $stock['tradeGroupName'] . '</th></tr>';
		}
	}
	//now that we have tradegroupname we can bounce if it is unpublished
        if(!$stock['published']) continue;

	// Setup table rows
	if(isset($arTradeGroups[$stock['tradeGroupId']]['tab']) && $arTradeGroups[$stock['tradeGroupId']]['tab'] == 1){
		$sendKey = preg_replace("/\W/","",$arTradeGroups[$stock['tradeGroupId']]['name']);
		$content .= '<tr class="hidden-filter '.$sendKey.'" data-id="'.$stock['tradeGroupId'].'" style="display:none">';
	}else{
		$content .= '<tr class="'.$stock['status'].'" data-id="'.$stock['tradeGroupId'].'">';
	}
	// Populate columns
	foreach ($portfolio_columns as $key => $value) {
		$content .= '<td data-label="' . wp_strip_all_tags($value, true) . '" class="' . $key . '">';
		if ($key == 'symbol') {
			$content .= '<a href="/ticker/' . $stock['columns'][$key] . '"><strong>' . $stock['columns'][$key] . '</strong></a>';
		} else {
			$content .= '<span class="ticker-trigger">' . $stock['columns'][$key] . '</span>';
		}
		$content .= '</td>';
	}
	$content .= '</tr>';
}

// Build table if the content exists
if ($content) {
    $buttons = '';
    foreach($arTabs as $slug => $label){
	if($slug != 'openContent' && $slug != 'closeContent'){
		$sendKey = preg_replace("/\W/","",$label);
		$buttons .= '<a id="'.$sendKey.'" href="#" class="button filter-button hidden-filter">'.$label.'</a>';
	}else{
		$buttons .= '<a id="'.$slug.'" href="#" class="button filter-button">'.$label.'</a>';
	}
    }
    $header = '<tr>';
    $header_count=0;
    foreach ($portfolio_columns as $slug => $label) {
        $header .= '<th data-label="' . wp_strip_all_tags($label, true) . '" class="' . $slug . '" sort-column="' . $header_count . '">' . $label . '</th>';
        $header_count++;
    }
    $header .= '</thead>';
    $content = '
        <div class="row">
            <div class="small-12 columns">
                <div class="portfolio-table-wrapper">
                    <div class="tab-filters">' . $buttons . '</div>
                    <table class="table-responsive table-centered table-sortable portfolio-table">
                        <thead>' . $header . '</thead>
                        <tbody>' . $content . '</tbody>
                    </table>
                </div>
            </div>
        </div>
    ';
}

echo $content;

// Merge all hidden
$all_hidden = array_merge($hide_open, $hide_closed);

// Show/hide columns as set per admin for hide/close views
if ($hide_open || $hide_closed) {
?>
    <script>
        (function($){
            <?php
            // Hide specified columns as default
            $class_html = '';
            foreach ($hide_open as $class) {
                $class_html .= "$('.".$class."').hide();";
            }
            echo $class_html;
            ?>

            // Get id from button and use to find rows and show/hide as needed
            $('.filter-button').click(function(e){
                e.preventDefault();

                // Show all columns as reset
                <?php
                $class_html = '';
                foreach ($all_hidden as $class) {
                    $class_html .= "$('.".$class."').show();";
                }
                echo $class_html;
                ?>

                // Hide open columns
                if ($(this).is('#openContent')) {
                    <?php
                    $class_html = '';
                    foreach ($hide_open as $class) {
                        $class_html .= "$('.".$class."').hide();";
                    }
                    echo $class_html;
                    ?>
                }

                // Hide closed columns
                if ($(this).is('#closeContent')) {
                    <?php
                    $class_html = '';
                    foreach ($hide_closed as $class) {
                        $class_html .= "$('.".$class."').hide();";
                    }
                    echo $class_html;
                    ?>
                }
            });
        })(jQuery);
    </script>
<?php
}
