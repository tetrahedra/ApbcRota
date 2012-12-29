<?php
/*
Plugin Name: APBC Rota Widget
Plugin URI: http://tetrahedra.co.uk/ApbcRotaWidget
Description: Plugin and widget to allow display of rota information from Google Spreadsheets
Version: 0.1 BETA
Author: John Adams
Author URI: http://www.tetrahedra.co.uk
*/

/*
APBC Rota Widget (Wordpress Widget Plugin)
Copyright (C) 2010 John Adams
Contact me at http://www.tetrahedra.co.uk

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'apbc_rota_load_widgets' );

/**
 * Register our widget.
 * 'Apbc_Rota_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function apbc_rota_load_widgets() {
	register_widget( 'Apbc_Rota_Widget' );
}

/**
 * Apbc_Rota_Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Apbc_Rota_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Apbc_Rota_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'example', 'description' => __('A widget to display rota information from Google Spreadsheets') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'example-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'example-widget', __('Apbc_Rota'), $widget_ops, $control_ops );
	}
	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$errorMsg = $instance['error'];
		$baseurl = $instance['url'];
		$pageurl = site_url() . "/?p=" . $instance['page'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

           
		//get current/future dates only [parameter?]
		$url = "http://spreadsheets.google.com/feeds/list/0Av9-QyyqzwDidElxOG5GcDY1cDZael92NVdDUGpkUkE/od6/public/values?sq=current=true";
		//full page url [parameter?]
		//$pageurl = "http://spreadsheets.google.com/pub?key=0Av9-QyyqzwDidElxOG5GcDY1cDZael92NVdDUGpkUkE&hl=en_GB&single=true&gid=0&range=A1%3AG99&output=html";
		
		$spreadsheetService = new Zend_Gdata_Spreadsheets();
		$listFeed = $spreadsheetService->getSpreadsheetListFeedContents($url);
		
		if ($listFeed) {
			$this->showThisWeek($listFeed,$pageurl);
		}
		else {
			echo $errorMsg;
		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );
		$instance['page'] = strip_tags( $new_instance['page'] );
		$instance['error'] = strip_tags( $new_instance['error'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */

        $defaults = array( 'title' => __('Recent Attachments'), 'num' => __(5), 'error' => __("No documents found."), 'parent' => null );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Base URL: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e('Base URL for Google spreadsheet:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" value="<?php echo $instance['url']; ?>" />
		</p>

		<!-- Full rota page: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e('Page ID for full Rota:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'page' ); ?>" name="<?php echo $this->get_field_name( 'page' ); ?>" value="<?php echo $instance['page']; ?>" />
		</p>

        <!-- Error message: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'error' ); ?>"><?php _e('Error message:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'error' ); ?>" name="<?php echo $this->get_field_name( 'error' ); ?>" value="<?php echo $instance['error']; ?>" />
		</p>


	<?php

	}


    /*
     * Private widget functions
     *
     */
    
    function showThisWeek($feed,$pageurl) {
	$sunday = date('l jS F Y', $this->ukStrToTime($feed[0]['date']));
	//$sunday = $feed[0]['date'];
	$leader = $feed[0]["leader"];
	$speaker = $feed[0]["speaker"];
	//$topic = $feed[0]["topic"];
    echo("<div id='inline_apbcrota'>");
	echo("<h2>$sunday</h2>");
	echo("<ul class='thisWeek'>");
    echo("11am - 12.15pm <br />");
	echo("Worship leader: <strong>$leader</strong><br />");
	echo("Speaker: <strong>$speaker</strong><br />");
	echo("Coffee and tea afterwards<br />");
	echo("<a href='$pageurl'>Full WT Rota</a>");
    echo("</ul>");
    echo("</div>");

    }
    
    function ukStrToTime($str) {
        return strtotime(preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $str));
    }   

}

    /*
     * General plugin functions
     */

    // [apbcrota date="01/01/1900"]
    function apbcrota_func( $atts ) {
        extract( shortcode_atts( array(
            'foo' => 'something',
            'bar' => 'something else',
        ), $atts ) );

		$url = "http://spreadsheets.google.com/feeds/list/0Av9-QyyqzwDidElxOG5GcDY1cDZael92NVdDUGpkUkE/od6/public/values?sq=current=true";
		$spreadsheetService = new Zend_Gdata_Spreadsheets();
		$listFeed = $spreadsheetService->getSpreadsheetListFeedContents($url);

		if ($listFeed) {
            return showThisWeek($listFeed,$pageurl);
		}
		else {
			echo $errorMsg;
		}
    }

    function showThisWeek($feed,$pageurl) {
        $sunday = date('l jS F Y', ukStrToTime($feed[0]['date']));
        //$sunday = $feed[0]['date'];
        $leader = $feed[0]["leader"];
        $speaker = $feed[0]["speaker"];
        //$topic = $feed[0]["topic"];
        $optionalText = $feed[0]["optionaltext"];

        $output = "<div id='inline_apbcrota'>";
        $output .= "<h2>$sunday</h2>";
        $output .= "11am - 12.15pm <br />";
        $output .= "Worship leader: <strong>$leader</strong><br />";
        $output .= "Speaker: <strong>$speaker</strong><br />";
        if($optionalText != "")
            $output .= "<em>" . $optionalText . "</em><br />";
        $output .= "Join us afterwards for tea and coffee";
        if($pageurl)
            $output .= "<br /><a href='$pageurl'>Full WT Rota</a>";
        $output .= "</div>";

        return $output;

    }

    function ukStrToTime($str) {
        return strtotime(preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $str));
    }

    add_shortcode( 'apbcrota', 'apbcrota_func' );


?>