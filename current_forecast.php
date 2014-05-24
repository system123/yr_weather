<?php
/*
    Plugin Name: yr weather
    Plugin URI: http://www.lloydhughes.co.za
    Description: Stupidly simple hacked together plugin to get just the weather I need (with caching) from yr.no use [yrweather location="<yr.no location string>"]
    Author: Lloyd Hughes
    Version: 1.4
    Author URI: http://www.lloydhughes.co.za
*/

include( plugin_dir_path( __FILE__ ) . "autoload.php");

function get_img_src($img_name) {
	$img = plugins_url('/img/', __FILE__);
	return $img . $img_name;
}

function get_forecasts($location) {
	$yr = Yr::create($location, plugin_dir_path( __FILE__ ) . "/cache");

	$forecast = $yr->getCurrentForecast();

	$today_data = 
	'<div>'.
	'<div style="float:left; width: 100%; text-align: left;">'.
	'<img style="margin-bottom: -10px; float:right;max-width: 50%;" src=" '. get_img_src($forecast->getSymbol("number") . '.svg') .'"" title="'. $forecast->getSymbol() .'" >'.                      
	'<div style="margin: 0px; font-size: 22px; color: rgba(81, 81, 81, 1); margin: 0 0 4px 0;">Current</div>'.
	'<span>'. $forecast->getTemperature() .'°C</span><br>' .
	'<span>Wind :'. $forecast->getWindSpeed() .'mps</span><br>' .
	'<span>Direction : '.$forecast->getWindDirection() .'</span><br>' .
	'<span>Rain : '. $forecast->getPrecipitation() .'mm </span><br>' .
	'<span>	Pressure : '. $forecast->getPressure() . ' ' . $forecast->getPressure('unit') .'</span>' .
	'</div>'.
	'<div style="float:left; width:100%; padding-top:4px;">' .
	'Sunrise : '. $yr->getSunrise()->format("H:i") .' |  Sunset : '. $yr->getSunset()->format("H:i") .'</div>'.
	'</div>';

	$day_ends_wrapper = '<div style="font-size:14px; float:left; width:32%; text-align:center;">';
	$day_middle_wrapper = '<div style="font-size:14px; text-align:center; border-right:2px solid rgba(240, 240, 240, 0.66); border-left:2px solid rgba(240, 240, 240, 0.66);float:left; width:33%;">';
	$days_wrapper = array($day_ends_wrapper, $day_middle_wrapper, $day_ends_wrapper);

	$i = 0;
	$low_temp = "";

	$days_data = '<div style="font-size:14px; overflow:hidden; width:100%; float:left;">';

	foreach($yr->getPeriodicForecasts(strtotime("tomorrow"), strtotime("+3 day")) as $forecast) {
	    $period = $forecast->getPeriod();
	    
	    if ($period != 2) {
	        $low_temp = $forecast->getTemperature();
	        continue;
	    }

	    $days_data = sprintf('%s%s', $days_data, $days_wrapper[$i]);
	    $days_data = sprintf('%s<img style="max-width:%s;" src="%s" title="%s"/><br>', $days_data, "100%" ,get_img_src($forecast->getSymbol("number") . '.svg'), $forecast->getSymbol());
	    $days_data = sprintf('%s%s<br>', $days_data, $forecast->getFrom()->format('D'));
	    $days_data = sprintf('%s<span>%s°</span> | <span>%s°</span>', $days_data, $low_temp, $forecast->getTemperature());
	    $days_data = sprintf('%s</div>',$days_data);
	    $i = $i + 1;

	}

	$days_data = $days_data . '</div>';

	return $today_data . $days_data;
}

// Generate the tabbed content
function generate_forcast( $atts , $content = null ) {
// Attributes
	extract( shortcode_atts(
		array(
			'location' => ''
		), $atts )
	);

	$forecast = get_forecasts($location);

	$weather= '<div style="font-family: inherit; overflow:hidden; padding: 5px;">' . $forecast . '</div>';

	wp_reset_postdata();
	return $weather;
}
add_shortcode( 'yrweather', 'generate_forcast' );

?>