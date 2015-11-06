<?php
/**
 * This file implements the Google Maps plugin plugin.
 *
 * For the most recent and complete Plugin API documentation
 * see {@link Plugin} in ../evocore/_plugin.class.php.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package plugins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

class private_menu_link_plugin extends Plugin
{
	var $name = 'Private Menu Link';
	var $code = 'prvmenulk';
	var $priority = 50;
	var $version = '6.6.5';
	var $author = 'The b2evo Group';
	var $help_url = '';

	var $group = 'widget';


	/**
	 * Init: This gets called after a plugin has been registered/instantiated.
	 */
	function PluginInit( & $params )
	{
		$this->short_desc = $this->T_('Short description');
		$this->long_desc = $this->T_('Longer description. You may also remove this.');

		$this->menu_link_widget_link_types = array(
			'home' => T_('Front Page'),
			'recentposts' => T_('Recent posts'),
			'search' => T_('Search page'),
			'arcdir' => T_('Archive directory'),
			'catdir' => T_('Category directory'),
			'tags' => T_('Tags'),
			'postidx' => T_('Post index'),
			'mediaidx' => T_('Photo index'),
			'sitemap' => T_('Site Map'),
			'latestcomments' => T_('Latest comments'),

			'ownercontact' => T_('Blog owner contact form'),
			'owneruserinfo' => T_('Blog owner profile'),

			'users' => T_('User directory'),

			'login' => T_('Log in form'),
			'logout' => T_('Logout link'),
			'register' => T_('Registration form'),
			'myprofile' => T_('My profile'),
			'profile' => T_('Edit profile'),
			'avatar' => T_('Edit profile picture'),

			'item' => T_('Any item (post, page, etc...)'),
			'postnew' => T_('New Item'),

			'admin' => T_('Admin / Back-Office link'),
			'url' => T_('Any URL'),
		);
	}


	/**
	 * Define settings that the plugin uses/provides.
	 */
	function GetDefaultSettings()
	{
		return array();
	}


	/**
	 * Param definitions when added as a widget.
	 *
	 * Plugins used as widget need to implement the SkinTag hook.
	 *
	 * @return array
	 */
	function get_widget_param_definitions( $params )
	{
		$r = array_merge( array(
				'link_type' => array(
					'label' => T_( 'Link Type' ),
					'note' => T_('What do you want to link to?'),
					'type' => 'select',
					'options' => $this->menu_link_widget_link_types,
					'defaultvalue' => 'home',
				),
				'link_text' => array(
					'label' => T_('Link text'),
					'note' => T_( 'Text to use for the link (leave empty for default).' ),
					'type' => 'text',
					'size' => 20,
					'defaultvalue' => '',
				),
				'blog_ID' => array(
					'label' => T_('Collection ID'),
					'note' => T_( 'Leave empty for current collection.' ),
					'type' => 'integer',
					'allow_empty' => true,
					'size' => 5,
					'defaultvalue' => '',
				),
				// fp> TODO: ideally we would have a link icon to go click on the destination...
				'item_ID' => array(
					'label' => T_('Item ID'),
					'note' => T_( 'ID of post, page, etc. for "Item" type links.' ),
					'type' => 'integer',
					'allow_empty' => true,
					'size' => 5,
					'defaultvalue' => '',
				),
				'link_href' => array(
					'label' => T_('URL'),
					'note' => T_( 'Destination URL for "URL" type links.' ),
					'type' => 'text',
					'size' => 30,
					'defaultvalue' => '',
				),
			), parent::get_widget_param_definitions( $params ) );

		return $r;
	}


	function SkinTag( $params ) 
	{
		if( is_logged_in() ) 
		{ // Can be extended with more complex access rules based on the configuration
			return $this->display_widget( $params );
		}
	}


	function display_widget( $params ) 
	{	
		/**
		* @var Blog
		*/
		global $Blog;
		global $disp;

		// Default link class
		$link_class = $params['link_default_class'];

		$blog_ID = intval( $params['blog_ID'] );
		if( $blog_ID > 0 )
		{ // Try to use blog from widget setting
			$BlogCache = & get_BlogCache();
			$current_Blog = & $BlogCache->get_by_ID( $blog_ID, false, false );
		}

		if( empty( $current_Blog ) )
		{ // Blog is not defined in setting or it doesn't exist in DB
			// Use current blog
			$current_Blog = & $Blog;
		}

		if( empty( $current_Blog ) )
		{ // We cannot use this widget without a current collection:
			return false;
		}

		switch( $params['link_type'] )
		{
			case 'recentposts':
				$url = $current_Blog->get( 'recentpostsurl' );
				if( is_same_url( $url, $Blog->get( 'url' ) ) )
				{ // This menu item has the same url as front page of blog
					$EnabledWidgetCache = & get_EnabledWidgetCache();
					$Widget_array = & $EnabledWidgetCache->get_by_coll_container( $current_Blog->ID, NT_('Menu') );
					if( !empty( $Widget_array ) )
					{
						foreach( $Widget_array as $Widget )
						{
							$Widget->init_display( $params );
							if( isset( $Widget->param_array, $Widget->param_array['link_type'] ) && $Widget->param_array['link_type'] == 'home' )
							{ // Don't display this menu if 'Blog home' menu item exists with the same url
								return false;
							}
						}
					}
				}

				$text = T_('Recently');
				if( $disp == 'posts' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'search':
				$url = $current_Blog->get( 'searchurl' );
				$text = T_('Search');
				// Is this the current display?
				if( $disp == 'search' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'arcdir':
				$url = $current_Blog->get( 'arcdirurl' );
				$text = T_('Archives');
				if( $disp == 'arcdir' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'catdir':
				$url = $current_Blog->get( 'catdirurl' );
				$text = T_('Categories');
				if( $disp == 'catdir' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'tags':
				$url = $current_Blog->get( 'tagsurl' );
				$text = T_('Tags');
				if( $disp == 'tags' )
				{	// Let's display the link as selected:
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'postidx':
				$url = $current_Blog->get( 'postidxurl' );
				$text = T_('Post index');
				if( $disp == 'postidx' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'mediaidx':
				$url = $current_Blog->get( 'mediaidxurl' );
				$text = T_('Photo index');
				if( $disp == 'mediaidx' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'sitemap':
				$url = $current_Blog->get( 'sitemapurl' );
				$text = T_('Site map');
				if( $disp == 'sitemap' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'latestcomments':
				if( !$current_Blog->get_setting( 'comments_latest' ) )
				{ // This page is disabled
					return false;
				}
				$url = $current_Blog->get( 'lastcommentsurl' );
				$text = T_('Latest comments');
				if( $disp == 'comments' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'owneruserinfo':
				$url = url_add_param( $current_Blog->get( 'userurl' ), 'user_ID='.$current_Blog->owner_user_ID );
				$text = T_('Owner details');
				// Is this the current display?
				global $User;
				if( $disp == 'user' && ! empty( $User ) && $User->ID == $current_Blog->owner_user_ID )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'ownercontact':
				if( ! $url = $current_Blog->get_contact_url( true ) )
				{ // user does not allow contact form:
					return;
				}
				$text = T_('Contact');
				// Is this the current display?
				if( $disp == 'msgform' || ( isset( $_GET['disp'] ) && $_GET['disp'] == 'msgform' ) )
				{ // Let's display the link as selected
					// fp> I think it's interesting to select this link , even if the recipient ID is different from the owner
					// odds are there is no other link to highlight in this case
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'login':
				if( is_logged_in() )
				{ // Don't display this link for already logged in users
					return false;
				}
				global $Settings;
				$url = get_login_url( 'menu link', $Settings->get( 'redirect_to_after_login' ), false, $current_Blog->ID );
				if( isset( $this->BlockCache ) )
				{ // Do NOT cache because some of these links are using a redirect_to param, which makes it page dependent.
					// so this will be cached by the PageCache; there is no added benefit to cache it in the BlockCache
					// (which could have been shared between several pages):
					$this->BlockCache->abort_collect();
				}
				$text = T_('Log in');
				// Is this the current display?
				if( $disp == 'login' )
				{ // Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'logout':
				if( ! is_logged_in() )
				{
					return false;
				}
				$url = get_user_logout_url( $current_Blog->ID );
				$text = T_('Log out');
				break;

			case 'register':
				if( ! $url = get_user_register_url( NULL, 'menu link', false, '&amp;', $current_Blog->ID ) )
				{
					return false;
				}
				if( isset( $this->BlockCache ) )
				{ // Do NOT cache because some of these links are using a redirect_to param, which makes it page dependent.
					// Note: also beware of the source param.
					// so this will be cached by the PageCache; there is no added benefit to cache it in the BlockCache
					// (which could have been shared between several pages):
					$this->BlockCache->abort_collect();
				}
				$text = T_('Register');
				// Is this the current display?
				if( $disp == 'register' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'profile':
				if( ! is_logged_in() ) return false;
				$url = get_user_profile_url( $current_Blog->ID );
				$text = T_('Edit profile');
				// Is this the current display?  (Edit my Profile)
				if( in_array( $disp, array( 'profile', 'avatar', 'pwdchange', 'userprefs', 'subs' ) ) )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'avatar':
				if( ! is_logged_in() ) return false;
				$url = get_user_avatar_url( $current_Blog->ID );
				$text = T_('Profile picture');
				// Note: we never highlight this, it will always highlight 'profile' instead
				break;

			case 'users':
				global $Settings;
				if( ! is_logged_in() && ! $Settings->get( 'allow_anonymous_user_list' ) )
				{	// Don't allow anonymous users to see users list
					return false;
				}
				$url = $current_Blog->get( 'usersurl' );
				$text = T_('User directory');
				// Is this the current display?
				// Note: If $user_ID is not set, it means we are viewing "My Profile" instead
				global $user_ID;
				if( $disp == 'users' || ($disp == 'user' && !empty($user_ID)) )
				{	// Let's display the link as selected
					// Note: we also highlight this for any user profile that is displayed
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'item':
				$ItemCache = & get_ItemCache();
				/**
				* @var Item
				*/
				$item_ID = intval( $params['item_ID'] );
				$disp_Item = & $ItemCache->get_by_ID( $item_ID, false, false );
				if( empty( $disp_Item ) )
				{ // Item not found
					return false;
				}
				$url = $disp_Item->get_permanent_url();
				$text = $disp_Item->title;
				// Is this the current item?
				global $Item;
				if( ! empty( $Item ) && $disp_Item->ID == $Item->ID )
				{ // The current page is currently displaying the Item this link is pointing to
					// Let's display it as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'url':
				if( empty( $params['link_href'] ) )
				{ // Don't display a link if url is empty
					return false;
				}
				$url = $params['link_href'];
				$text = '[URL]';	// should normally be overriden below...
				// Note: we never highlight this link
				break;

			case 'postnew':
				if( ! check_item_perm_create() )
				{	// Don't allow users to create a new post
					return false;
				}
				$url = url_add_param( $current_Blog->get( 'url' ), 'disp=edit' );
				$text = T_('Write a new post');
				// Is this the current display?
				if( $disp == 'edit' )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'myprofile':
				if( ! is_logged_in() )
				{	// Don't show this link for not logged in users
					return false;
				}
				$url = $current_Blog->get( 'userurl' );
				$text = T_('My profile');
				// Is this the current display?  (Edit my Profile)
				global $user_ID, $current_User;
				// If $user_ID is not set, it means we will fall back to the current user, so it's ok
				// If $user_ID is set, it means we are browsing the directory instead
				if( $disp == 'user' && empty( $user_ID ) )
				{	// Let's display the link as selected
					$link_class = $params['link_selected_class'];
				}
				break;

			case 'admin':
				global $current_User;
				if( ! ( is_logged_in() && $current_User->check_perm( 'admin', 'restricted' ) && $current_User->check_status( 'can_access_admin' ) ) )
				{ // Don't allow admin url for users who have no access to backoffice
					return false;
				}
				global $admin_url;
				$url = $admin_url;
				$text = T_('Admin').' &raquo;';
				break;

			case 'home':
			default:
				$url = $current_Blog->get( 'url' );
				$text = T_('Front Page');
				global $is_front;
				if( $disp == 'front' || ! empty( $is_front ) )
				{ // Let's display the link as selected on front page
					$link_class = $params['link_selected_class'];
				}
		}

		// Override default link text?
		if( ! empty( $params['link_text'] ) )
		{ // We have a custom link text:
			$text = $params['link_text'];
		}

		echo $params['block_start'];
		echo $params['block_body_start'];
		echo $params['list_start'];

		if( $link_class == $params['link_selected_class'] )
		{
			echo $params['item_selected_start'];
		}
		else
		{
			echo $params['item_start'];
		}
		echo '<a href="'.$url.'" class="'.$link_class.'">'.$text.'</a>';
		if( $link_class == $params['link_selected_class'] )
		{
			echo $params['item_selected_end'];
		}
		else
		{
			echo $params['item_end'];
		}

		echo $params['list_end'];
		echo $params['block_body_end'];
		echo $params['block_end'];

		return true;
	}
}
?>