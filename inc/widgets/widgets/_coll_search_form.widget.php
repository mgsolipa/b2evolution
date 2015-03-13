<?php
/**
 * This file implements the xyz Widget class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_class( 'widgets/model/_widget.class.php', 'ComponentWidget' );

/**
 * ComponentWidget Class
 *
 * A ComponentWidget is a displayable entity that can be placed into a Container on a web page.
 *
 * @package evocore
 */
class coll_search_form_Widget extends ComponentWidget
{
	/**
	 * Constructor
	 */
	function coll_search_form_Widget( $db_row = NULL )
	{
		// Call parent constructor:
		parent::ComponentWidget( $db_row, 'core', 'coll_search_form' );
	}


	/**
	 * Get name of widget
	 */
	function get_name()
	{
		return T_('Search Form');
	}


	/**
	 * Get a very short desc. Used in the widget list.
	 */
	function get_short_desc()
	{
		return format_to_output($this->disp_params['title']);
	}


  /**
	 * Get short description
	 */
	function get_desc()
	{
		return T_('Display search form');
	}


  /**
   * Get definitions for editable params
   *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 */
	function get_param_definitions( $params )
	{
		$r = array_merge( array(
				'title' => array(
					'label' => T_('Block title'),
					'note' => T_( 'Title to display in your skin.' ),
					'size' => 40,
					'defaultvalue' => T_('Search'),
				),
				'button' => array(
					'label' => T_('Button name'),
					'note' => T_( 'Button name to submit a search form.' ),
					'size' => 40,
					'defaultvalue' => T_('Go'),
				),
				'disp_search_options' => array(
					'label' => T_( 'Search options' ),
					'note' => T_( 'Display radio buttons for "All Words", "Some Word" and "Entire Phrase"' ),
					'type' => 'checkbox',
					'defaultvalue' => false,
				),
				'use_search_disp' => array(
					'label' => T_( 'Results on search page' ),
					'note' => T_( 'Use advanced search page to display results (disp=search)' ),
					'type' => 'checkbox',
					'defaultvalue' => true,
				),
				'blog_ID' => array(
					'label' => T_('Collection ID'),
					'note' => T_('Leave empty for current collection.'),
					'type' => 'integer',
					'allow_empty' => true,
					'size' => 5,
					'defaultvalue' => '',
				),
			), parent::get_param_definitions( $params )	);

		return $r;
	}


	/**
	 * Display the widget!
	 *
	 * @param array MUST contain at least the basic display params
	 */
	function display( $params )
	{
		$params = array_merge( array(
				'search_input_before'  => '',
				'search_input_after'   => '',
				'search_submit_before' => '',
				'search_submit_after'  => '',
			), $params );

		$this->init_display( $params );

		$blog_ID = intval( $this->disp_params['blog_ID'] );
		if( $blog_ID > 0 )
		{ // Get Blog for widget setting
			$BlogCache = & get_BlogCache();
			$widget_Blog = & $BlogCache->get_by_ID( $blog_ID, false, false );
		}
		if( empty( $widget_Blog ) )
		{ // Use current blog
			global $Blog;
			$widget_Blog = & $Blog;
		}

		// Collection search form:
		echo $this->disp_params['block_start'];

		$this->disp_title();

		echo $this->disp_params['block_body_start'];

		form_formstart( $widget_Blog->gen_blogurl(), 'search', 'SearchForm' );

		if( empty( $this->disp_params['search_class'] ) )
		{ // Class name is not defined, Use class depend on serach options
			$search_form_class = $this->disp_params[ 'disp_search_options' ] ? 'extended_search_form' : 'compact_search_form';
		}
		else
		{ // Use class from params
			$search_form_class = $this->disp_params['search_class'];
		}

		echo '<div class="'.$search_form_class.'">';

		echo $this->disp_params['search_input_before'];
		echo '<input type="text" name="s" size="25" value="'.htmlspecialchars( get_param( 's' ) ).'" class="search_field SearchField form-control" title="'.format_to_output( T_('Enter text to search for'), 'htmlattr' ).'" />';
		echo $this->disp_params['search_input_after'];

		echo $this->disp_params['search_submit_before'];
		echo '<input type="submit" name="submit" class="search_submit submit btn btn-primary" value="'.format_to_output( $this->disp_params['button'], 'htmlattr' ).'" />';
		echo $this->disp_params['search_submit_after'];

		if( $this->disp_params['disp_search_options'] )
		{ // Display the search options
			$sentence = get_param( 'sentence' );
			echo '<div class="search_options">';
			echo '<div class="search_option"><input type="radio" name="sentence" value="AND" id="sentAND" '.( $sentence=='AND' ? 'checked="checked" ' : '' ).'/><label for="sentAND">'.T_('All words').'</label></div>';
			echo '<div class="search_option"><input type="radio" name="sentence" value="OR" id="sentOR" '.( $sentence=='OR' ? 'checked="checked" ' : '' ).'/><label for="sentOR">'.T_('Some word').'</label></div>';
			echo '<div class="search_option"><input type="radio" name="sentence" value="sentence" id="sentence" '.( $sentence=='sentence' ? 'checked="checked" ' : '' ).'/><label for="sentence">'.T_('Entire phrase').'</label></div>';
			echo '</div>';
		}

		echo '</div>';

		if( $this->disp_params['use_search_disp'] )
		{
			echo '<input type="hidden" name="disp" value="search" />';
		}
		echo '</form>';

		echo $this->disp_params['block_body_end'];

		echo $this->disp_params['block_end'];

		return true;
	}
}

?>