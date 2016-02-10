<?php
/**
 * This file implements the ThisDayInHistory plugin.
 *
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * Calendar Plugin
 *
 * This plugin displays
 */
class thisdayinhistory_plugin extends Plugin
{
	/**
	 * Variables below MUST be overriden by plugin implementations,
	 * either in the subclass declaration or in the subclass constructor.
	 */

	var $name;
	var $code = 'evo_ThisDayInHistory';
	var $priority = 96;
	var $version = '0.1';
	var $group = 'widget';
	var $subgroup = 'other';


	/**
	 * Init
	 */
	function PluginInit( & $params )
	{
		$this->name = T_( 'This Day in History' );
		$this->short_desc = T_('This widget displays posts from previous years according to a chosen date.');
		$this->long_desc = T_('Take posts from previous years on a chosen date.');
	}


  /**
   * Get definitions for widget specific editable params
   *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_widget_param_definitions( $params )
	{
		$r = array(
			'title' => array(
				'label' => T_('Block title'),
				'note' => T_('The of the widget.'),
				'type' => 'text',
			),
			'posts_date' => array(
				'label' => T_('Posts date'),
				'note' => T_('Base date of the posts to be displayed. Today: take posts from today - Custom date: set a date a different date (see Custom date field).'),
				'type' => 'select',
				'options' => array('today' => T_('Today'), 'custom' => T_('Custom date')),
			),
			'custom_date' => array(
				'label' => T_('Custom date'),
				'note' => T_('Retrieve posts from this date . Format: dd-mm'),
				'type' => 'text',
				'valid_pattern' => '#^[0-9]{2}-[0-9]{2}$#'
			),
			'years' => array(
				'label' => T_('Years'),
				'note' => T_('How many years will be included in the list.'),
				'type' => 'integer',
				'defaultvalue' => 3,
				'valid_range' => array(
					'min' => 1, // 0 would not make sense.
				),
			),
			'year_title' => array(
				'label' => T_('Year title'),
				'note' => T_('The title of each year. Date: d/m/Y (get value from year_title_format) - Friendly text: 1 year ago, 2 years ago, 3 years ago...'),
				'type' => 'select',
				'options' => array('date' => T_('Date'), 'friendly' => T_('Firendly text')),
			),
			'year_title_format' => array(
				'label' => T_('Year\'s format'),
				'note' => T_('Format to display the title of each year. Accepted values: valid formats of the standard php date() function. Dates are displayed using the server\'s locale settings. b2evolution locales system is not supported yet. '),
				'type' => 'text',
			),
			'excerpt_length' => array(
				'label' => T_('Excerpt length'),
				'note' => T_('Number of characters to display below the title of the post.'),
				'type' => 'text',
			),
			'posts_per_year' => array(
				'label' => T_('Posts per year'),
				'note' => T_('How many posts to display from each year.'),
				'type' => 'integer',
				'defaultvalue' => 3,
				'valid_range' => array(
					'min' => 1, // 0 would not make sense.
				),
			),
		);
		return $r;
	}

	/**
	 * Event handler: SkinTag (widget)
	 *
	 * @param array Associative array of parameters.
	 * @return boolean did we display?
	 */
	function SkinTag( $params )
	{
		global $Plugins;

		if( $Plugins->trigger_event_first_true('CacheIsCollectingContent') )
		{ // A caching plugin collecting the content
			return false;
		}

		// The widget's title
		$title = ( ! empty( $params['title'] ) ) ? $params['title'] : 'This Day in History';

		if ( $posts_per_year = $this->get_posts( $params ) ) 
		{
			echo $params['block_start'];

			echo $params['block_title_start'];
			echo $title;
			echo $params['block_title_end'];

			echo $params['block_body_start'];

			$years_counter = 0;

			// No friendly format for custom_date posts
			if ( $params['posts_date'] == 'custom' )
			{
				$params['year_title'] = 'date';
			}

			foreach ( $posts_per_year as $timestamp => $posts_year )
			{
				$years_counter++;

				switch ( $params['year_title'] ) 
				{
					case 'date':
						// TODO: use the datefmt value for current locale 
						$format = 'd/m/Y';

						if ( ! empty($params['year_title_format']) )
						{
							$format = $params['year_title_format'];
						}
						
						$year_title = date($format, $timestamp);
					break;
					case 'friendly':
						switch ( $params['posts_date'] ) 
						{
							case 'today':
								$sufix = ($years_counter == 1) ? ' year ago' : ' years ago';
								break;

							case 'custom':
								$sufix = ($years_counter == 1) ? ' year before' : ' years before';
								break;
						}

						$year_title = sprintf(T_('%d' . $sufix), $years_counter);
						
					break;
				}

				echo '<h3 class="title">'.$year_title.'</h3>';
				foreach( $posts_year as $timestamp => $post ) 
				{
					echo $post->title();
					echo '<p>' . substr($post->excerpt, 0, $params['excerpt_length']) . '</p>';
				}
			}
			
			echo $params['block_body_end'];

			echo $params['block_end'];
		}

		return true;
	}

	/**
	 * Get posts from previous years according to the widget's configuration
	 */
	function get_posts( $params ) 
	{ 
		global $Plugins;
		global $MainList;
		global $BlogCache, $Blog, $blog;
		global $Item, $Settings;

		// Determine the date of the posts
		switch ( $params['posts_date'] )
		{
			case 'today':
				$posts_date = date( 'Y-m-d' );
				break;

			case 'custom':
				if ( ! $posts_date = $this->get_custom_date( $params ) ) 
				{
					return false;
				}
				break;
		}

		$posts_date_tspm = strtotime($posts_date);

		// Get dates to search posts from

		// Let's start from the first year according to the parameters (today | custom)

		if ( $params['posts_date'] == 'custom' && $posts_date_tspm <= time() )
		{ // Don't want search posts in future
			// When "Good way" is implemented, uncomment the following line and get rid of the next one.
			// $years = array( $year => array('min' => $posts_date, 'max' => date('Y-m-d', strtotime("+1 day", $posts_date_tspm)) ) );
			$years = array( $posts_date_tspm => array('min' => $posts_date_tspm, 'max' => strtotime("+1 day", $posts_date_tspm) - 1 ) );
		}
		
		// Now let's calculate the previous years
		for( $index = 1; $index < $params['years']; $index++ ) 
		{
			$timestamp = strtotime("-".$index." year", $posts_date_tspm);
			
			// When "Good way" is implemented, change the statements below with the commented code.
			$years[$timestamp] = array(
					'min' => $timestamp, // date('Y-m-d', $timestamp),
					'max' => strtotime("+1 day", $timestamp) - 1, // date('Y-m-d', strtotime("+1 day", $timestamp))
				);
		}

		// Now look for the collection
		$blog_ID = $blog;

		$listBlog = ( $blog_ID ? $BlogCache->get_by_ID( $blog_ID, false ) : $Blog );

		$limit = intval( $params['posts_per_year'] ) * intval( $params['years'] );

		load_class( 'items/model/_itemlistlight.class.php', 'ItemListLight' );
		// $ItemList = new ItemListLight( $listBlog, $listBlog->get_timestamp_min(), $listBlog->get_timestamp_max(), $limit, 'ItemCacheLight', $this->code.'_' );

		// Search all dates on the same query

		/* // The good way: compose only one query to retrieve all the records from the database using a single SQL statement.
		$where = array();
		foreach( $years as $year ) {
			$where[] = "(" . $this->dbprefix."datestart >= '".$year['min']."' AND " . $this->dbprefix."datestart < '".$year['max']."')";
		}

		$where = implode(' OR ', $where);

		// We need a filter to manage such situations like this. 
		// Something like:
			$filters['split_dates'] = $where;
		// Or maybe
			$filters['where_clause'] = $where; // Just take any given SQL and add it to the query using WHERE_and

		$ItemList->set_filters( $filters, false );
		$ItemList->query(); 

		if( ! $ItemList->result_num_rows )
		{	// Nothing to display:
			return;
		}
		
		$posts = array();
		while( $Item = & $ItemList->get_item() )
		{ // Display contents of the Item depending on widget params:
			while( $Item = & $ItemList->get_item() )
			{
				$post_timestamp = strtotime(date('Y-m-d', strtotime($post->datestart)));
				$posts[$post_timestamp][] = $Item;
			}
		} */

		// ***************** //

		// Filter list:
		$filters = array(
			'unit'      => 'posts', // We want to advertise all items (not just a page or a day)
			'coll_IDs'  => $blog_ID,
			'visibility_array' => array( 'published' ),
		);

		// The "bad" way (as many queries as years requested):
		$posts = array();
		foreach( $years as $key => $year ) 
		{
			$filters['ts_min'] = $year['min'];
			$filters['ts_max'] = $year['max'];

			$ItemList = new ItemListLight( $listBlog, $listBlog->get_timestamp_min(), $listBlog->get_timestamp_max(), $limit, 'ItemCacheLight', $this->code.'_' );
			$ItemList->set_filters( $filters, false );
			$ItemList->query();

			if( ! $ItemList->result_num_rows ) 
			{
				continue;
			}
			else
			{
				$posts[$key] = array();
				while( $Item = & $ItemList->get_item() )
				{
					$posts[$key][] = $Item;
				}
			}
		}

		if ( !empty($posts) )
		{
			return $posts;
		}

		return false;
	}


	/**
	 * 
	 */
	function get_custom_date( $params ) 
	{

		$custom_date = explode( '-', $params['custom_date'] );

		// Use 2016 (leap year), so even 2016-02-29 will produce a valid date
		if ( checkdate($custom_date[1],$custom_date[0],'2016') ) {
			return date( 'Y-m-d', strtotime( date( 'Y' ).'-'.$custom_date[1].'-'.$custom_date[0] ) );
		}
		
		// TODO: validate the given date on save to avoid a silent error
		return false;
	}
}

?>