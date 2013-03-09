<?php
/* 
Plugin Name: CPK Ultimate Archives
Plugin URI: http://cpkwebsolutions.com
Version: 1.0
Author: CPK Web Solutions
Description: Improves upon the default archives widget by allowing easy filtering of the posts that are listed.
 
License:
  This file is part of CPK Ultimate Archives by CPK Web Solutions.

    CPK Ultimate Archives by CPK Web Solutions is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    CPK Ultimate Archives by CPK Web Solutions is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License.  If not, see <http://www.gnu.org/licenses/>.
*/


if (!class_exists("CPKUltimateArchives")) {
  class CPKUltimateArchives extends WP_Widget {

    /* This is a modified version of the 'WP_Archives' widget released as part of WP 3.5.1 */
    private $datetype = array();
    private $cpksvars = array();
    private $cpkskeys = array();
    private $instanceid = "";
      
    function __construct() {
      $this->datetype = array('monthly', 'yearly', 'weekly', 'daily');
      $widget_ops = array('classname' => 'widget_cpk_archive', 'description' => __( 'CPK Ultimate Archives Widget by CPK Web Solutions') );
      $this->get_cpk_options();
      parent::__construct('cpkultimatearchives', __('CPK Ultimate Archives'), $widget_ops);
    }

    function widget( $args, $instance ) {
      extract($args);
      $c = ! empty( $instance['count'] ) ? '1' : '0';
      $d = ! empty( $instance['dropdown'] ) ? '1' : '0';
      $title = apply_filters('widget_title', empty($instance['title']) ? __('CPK Ultimate Archives') : $instance['title'], $instance, $this->id_base);
      if (!in_array($instance['dtype'], $this->datetype)) {
        $instance['dtype'] = 'monthly';
      }
      $instance['cpkquery'] = strip_tags($instance['cpkquery']);
      
      echo $before_widget;
      if ( $title )
        echo $before_title . $title . $after_title;

      if ( $d ) {
  ?>
      <select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> 
      	<option value=""><?php echo esc_attr(__('Select Date')); ?></option> 
      	<?php cpk_get_archives(apply_filters('widget_archives_dropdown_args', array('type' => $instance['dtype'], 'format' => 'option', 'show_post_count' => $c, 'query_post_string' => $instance['cpkquery'], 'cpkskeys' => $instance['cpkskeys'], 'instanceid' => $instance['instanceid']))); ?> 
      </select>
  <?php
      } else {
  ?>
      <ul>
      <?php cpk_get_archives(apply_filters('widget_archives_args', array('type' => $instance['dtype'], 'show_post_count' => $c, 'query_post_string' => $instance['cpkquery'], 'cpkskeys' => $instance['cpkskeys'], 'instanceid' => $instance['instanceid']))); ?>
      </ul>
  <?php
      }
      echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'dtype' => '', 'cpkquery' => '', 'count' => 0, 'dropdown' => '', 'instanceid' => '', 'cpkskeys' => array()) );
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['dtype'] = strip_tags($new_instance['dtype']);
      $instance['cpkquery'] = strip_tags($new_instance['cpkquery']);
      $instance['count'] = $new_instance['count'] ? 1 : 0;
      $instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;
      $instanceid = strip_tags($new_instance['instanceid']);
      $this->cpksvars[$instanceid] = $instance['cpkquery'];
      $key = array_search($instanceid, $this->cpkskeys);
      if($key === FALSE) {
      	$this->cpkskeys[] = $instanceid;
      }
      $instance['cpkskeys'] = $this->cpkskeys;
      $instance['instanceid'] = $instanceid;


      update_option('CPK_instance_options', $this->cpksvars);
      update_option('CPK_instance_keys', $this->cpkskeys);
      return $instance;
    }

    function form( $instance ) {
      $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'dtype' => '', 'cpkquery' => '', 'count' => 0, 'dropdown' => '', 'instanceid' => '', 'cpkskeys' => array()) );
      $title = strip_tags($instance['title']);
      $dtype = strip_tags($instance['dtype']);
      $cpkquery = strip_tags($instance['cpkquery']);
      $count = $instance['count'] ? 'checked="checked"' : '';
      $dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
      $instanceid = $this->instanceid;
  ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('dtype'); ?>"><?php _e('Date Type:'); ?>
        <select id="<?php echo $this->get_field_id('dtype'); ?>" name="<?php echo $this->get_field_name('dtype'); ?>">
          <?php foreach($this->datetype as $val):?>
          <option value="<?php echo $val; ?>"<?php if(esc_attr($dtype) == $val):?> selected="selected"<?php endif;?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </p>
      <p><label for="<?php echo $this->get_field_id('cpkquery'); ?>"><?php _e('Query:'); ?></label> 
      <textarea class="widefat" rows="4" cols="20" id="<?php echo $this->get_field_id('cpkquery'); ?>" name="<?php echo $this->get_field_name('cpkquery'); ?>"><?php echo esc_textarea($cpkquery); ?></textarea></p>
      <input id="<?php echo $this->get_field_id('instanceid'); ?>" name="<?php echo $this->get_field_name('instanceid'); ?>" type="hidden" value="<?php echo esc_attr($this->get_field_id('instanceid')); ?>" />
      <p>
        <input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
        <br/>
        <input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
      </p>
  <?php
    }

    function get_cpk_options()
    {
    	$cpk_keys = get_option('CPK_instance_keys');
    	$cpk_vars = get_option('CPK_instance_options');
    	if(!is_array($cpk_keys) || !isset($cpk_keys[0])) {
    		$this->cpkskeys = array(0 => 'CPK_instance_options');
    		$this->cpksvars = array();
    		update_option('CPK_instance_keys', $this->cpkskeys);
    		update_option('CPK_instance_options', $this->cpksvars);
    	} else {
    		$this->cpkskeys = $cpk_keys;
    		$this->cpksvars = $cpk_vars;
    	}
    }
  }
}

function cpk_get_archives($args = '') {

	/* This is a modified version of 'wp_get_archives' released as part of WP 3.5.1 */

	global $wpdb, $wp_locale;

	$defaults = array(
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false,
		'echo' => 1, 'order' => 'DESC',
		'query_post_string' => '',
		'cpkskeys' => array(),
		'instanceid' => ""
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	//widget instance id
	$cpkultimatearchive = array_search($instanceid, $cpkskeys); 

	if ( '' == $type )
		$type = 'monthly';

	if ( '' != $limit ) {
		$limit = absint($limit);
		$limit = ' LIMIT '.$limit;
	}

	$order = strtoupper( $order );
	if ( $order !== 'ASC' )
		$order = 'DESC';

	// this is what will separate dates on weekly archive links
	$archive_week_separator = '&#8211;';

	// over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
	$archive_date_format_over_ride = 0;

	// options for daily archive (only if you over-ride the general date format)
	$archive_day_date_format = 'Y/m/d';

	// options for weekly archive (only if you over-ride the general date format)
	$archive_week_start_date_format = 'Y/m/d';
	$archive_week_end_date_format	= 'Y/m/d';

	if ( !$archive_date_format_over_ride ) {
		$archive_day_date_format = get_option('date_format');
		$archive_week_start_date_format = get_option('date_format');
		$archive_week_end_date_format = get_option('date_format');
	}

	//filters
	$where = apply_filters( 'getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'", $r );
	$join = apply_filters( 'getarchives_join', '', $r );

	$output = '';

	# Create a filtered list of posts based on the 'query_post_string'

	if ( !empty( $query_post_string ) ) {
		$query_post_string .= '&fields=ids';
		$post_query = new WP_Query(array(
                    'no_found_rows' => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false
                    ));
	        $post_query->query( $query_post_string );
		$post_list = implode( ',', $post_query->posts );
		wp_reset_postdata( );
	}

	if ( !empty( $post_list ) ) $where .= " AND ID IN ( {$post_list} )";

	if ( 'monthly' == $type ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'cpk_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'cpk_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult ) {
				$url = get_month_link( $arcresult->year, $arcresult->month );
				$urlcount = strlen($url);--$urlcount;
				$urlstart = ($url{$urlcount} == '/') ? '?' : '&';
				$url = $url.$urlstart.'cpkultimatearchive='.$cpkultimatearchive;
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year);
				if ( $show_post_count )
					$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
				$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ('yearly' == $type) {
		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'cpk_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'cpk_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ($arcresults) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult) {
				$url = get_year_link($arcresult->year);
				$urlcount = strlen($url);--$urlcount;
				$urlstart = ($url{$urlcount} == '/') ? '?' : '&';
				$url = $url.$urlstart.'cpkultimatearchive='.$cpkultimatearchive;
				$text = sprintf('%d', $arcresult->year);
				if ($show_post_count)
					$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
				$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ( 'daily' == $type ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'cpk_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'cpk_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult ) {
				$url	= get_day_link($arcresult->year, $arcresult->month, $arcresult->dayofmonth);
				$urlcount = strlen($url);--$urlcount;
				$urlstart = ($url{$urlcount} == '/') ? '?' : '&';
				$url = $url.$urlstart.'cpkultimatearchive='.$cpkultimatearchive;
				$date = sprintf('%1$d-%2$02d-%3$02d 00:00:00', $arcresult->year, $arcresult->month, $arcresult->dayofmonth);
				$text = mysql2date($archive_day_date_format, $date);
				if ($show_post_count)
					$after = '&nbsp;('.$arcresult->posts.')'.$afterafter;
				$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ( 'weekly' == $type ) {
		$week = _wp_mysql_week( '`post_date`' );
		$query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'cpk_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'cpk_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		$arc_w_last = '';
		$afterafter = $after;
		if ( $arcresults ) {
				foreach ( (array) $arcresults as $arcresult ) {
					if ( $arcresult->week != $arc_w_last ) {
						$arc_year = $arcresult->yr;
						$arc_w_last = $arcresult->week;
						$arc_week = get_weekstartend($arcresult->yyyymmdd, get_option('start_of_week'));
						$arc_week_start = date_i18n($archive_week_start_date_format, $arc_week['start']);
						$arc_week_end = date_i18n($archive_week_end_date_format, $arc_week['end']);
						$url  = sprintf('%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $arcresult->week);
						$urlcount = strlen($url);--$urlcount;
						$urlstart = ($url{$urlcount} == '/') ? '?' : '&';
						$url = $url.$urlstart.'cpkultimatearchive='.$cpkultimatearchive;
						$text = $arc_week_start . $archive_week_separator . $arc_week_end;
						if ($show_post_count)
							$after = '&nbsp;('.$arcresult->posts.')'.$afterafter;
						$output .= get_archives_link($url, $text, $format, $before, $after);
					}
				}
		}
	} elseif ( ( 'postbypost' == $type ) || ('alpha' == $type) ) {
		$orderby = ('alpha' == $type) ? 'post_title ASC ' : 'post_date DESC ';
		$query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'cpk_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'cpk_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			foreach ( (array) $arcresults as $arcresult ) {
				if ( $arcresult->post_date != '0000-00-00 00:00:00' ) {
					$url  = get_permalink( $arcresult );
					$urlcount = strlen($url);--$urlcount;
					$urlstart = ($url{$urlcount} == '/') ? '?' : '&';
					$url = $url.$urlstart.'cpkultimatearchive='.$cpkultimatearchive;
					if ( $arcresult->post_title )
						$text = strip_tags( apply_filters( 'the_title', $arcresult->post_title, $arcresult->ID ) );
					else
						$text = $arcresult->ID;
					$output .= get_archives_link($url, $text, $format, $before, $after);
				}
			}
		}
	}
	if ( $echo )
		echo $output;
	else
		return $output;
}

function cpk_register_widgets() {
  register_widget( 'CPKUltimateArchives' );
}

add_action( 'widgets_init', 'cpk_register_widgets' );

function cpk_get_request( $wp ) {
	if(isset($wp->query_vars['cpkultimatearchive']) && $wp->query_vars['cpkultimatearchive']) {
		$cpkultimatearchive = $wp->query_vars['cpkultimatearchive'];
		update_option('CPK_instance_ID', $cpkultimatearchive);
		add_action( 'pre_get_posts', 'cpk_add_query' );
	}
}
function cpk_add_query($query) {
	if ( is_admin() ||  is_home() )
		return;
	// $query->set( 'posts_per_page', '2' );
	if ( $query->is_main_query( ) && $query->is_archive( ) ) {
		$cpk_keys = get_option('CPK_instance_keys');
		$cpk_vars = get_option('CPK_instance_options');
		$cpkultimatearchive = get_option('CPK_instance_ID');
		if(is_array($cpk_keys) && isset($cpk_keys[0]) && $cpkultimatearchive) {
			$query_key = $cpk_keys[$cpkultimatearchive];
			$query_str = $cpk_vars[$query_key];
			$query_arr = wp_parse_args( $query_str, array());
			// $query->set( 'posts_per_page', '2' );//e.g.
			foreach ($query_arr as $cpkkey => $cpkvalue) {
				$query->set( $cpkkey, $cpkvalue );
			}
		}
	}
	return;
}
function cpk_add_qvar ($cpk_add_qvar) {
  $cpk_add_qvar[] = "cpkultimatearchive";
  return $cpk_add_qvar;
}
add_action( 'parse_request', 'cpk_get_request' );
add_filter( 'query_vars', 'cpk_add_qvar' );
