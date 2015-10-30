<?php
/**
 * This is the template that displays the media index for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display the archive directory, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=arcdir
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $thumbnail_sizes;

if( ! $cat = param( $Skin->get_setting( 'alt_idx_varname' ) ) ) 
{
	if( empty( $params ) )
	{ // Initialize array with params
		$params = array();
	}

	// Merge the params from current skin
	$params = array_merge( array(
			'mediaidx_thumb_size' => 'fit-80x80'
		), $params );

	$photocell_styles = '';
	if( isset( $thumbnail_sizes[ $params['mediaidx_thumb_size'] ] ) )
	{
		$photocell_styles = ' style="width:'.$thumbnail_sizes[ $params['mediaidx_thumb_size'] ][1].'px;'
			.'height:'.$thumbnail_sizes[ $params['mediaidx_thumb_size'] ][2].'px"';
	}

	// --------------------------------- START OF MEDIA INDEX --------------------------------
	skin_widget( array(
			// CODE for the widget:
			'widget' => 'coll_media_index',
			// Optional display params
			'block_start' => '',
			'block_end' => '',
			'block_display_title' => false,
			'thumb_size' => $params['mediaidx_thumb_size'],
			'thumb_layout' => 'grid',
			'grid_start' => '<div class="image_index">',
			'grid_end' => '</div>',
			'grid_nb_cols' => 8,
			'grid_colstart' => '',
			'grid_colend' => '',
			'grid_cellstart' => '<div><span'.$photocell_styles.'>',
			'grid_cellend' => '</span></div>',
			'order_by' => $Blog->get_setting('orderby'),
			'order_dir' => $Blog->get_setting('orderdir'),
			'limit' => 1000,
		) );
	// ---------------------------------- END OF MEDIA INDEX ---------------------------------
}
else
{
	global $MainList;
	global $BlogCache, $Blog;
	global $Item, $Settings;

	$listBlog = $Blog;

	//load_class( 'items/model/_itemlistlight.class.php', 'ItemListLight' );
	$ItemList = new ItemList2( $listBlog, $listBlog->get_timestamp_min(), $listBlog->get_timestamp_max(), 100, 'ItemCache' );

	$filters = array('cat_array' => array($cat));
	$ItemList->set_filters( $filters, false );
	// Run the query:
	$ItemList->query();

	while( $Item = & $ItemList->get_item() )
	{
		// Default params:
		$params = array_merge( array(
				'feature_block'          => false,
				'item_class'             => 'bPost',
				'item_status_class'      => 'bPost',
				'content_mode'           => 'full', // We want regular "full" content, even in category browsing: i-e no excerpt or thumbnail
				'image_size'             => '', // Do not display images in content block - Image is handled separately
				'url_link_text_template' => '', // link will be displayed (except player if podcast)
			), $params );
		?>

		<div id="<?php $Item->anchor_id() ?>" class="<?php $Item->div_classes( $params ) ?>" lang="<?php $Item->lang() ?>">

			<?php
				$Item->locale_temp_switch(); // Temporarily switch to post locale (useful for multilingual blogs)
			?>

			<?php
				// Display images that are linked to this post:
				$Item->images( array(
						'before' =>              '<div class="bImages">',
						'before_image' =>        '<div class="image_block" style="display: inline-block">',
						'before_image_legend' => '<div class="image_legend">',
						'after_image_legend' =>  '</div>',
						'after_image' =>         '</div>',
						'after' =>               '</div>',
						'image_size' =>          'fit-80x80',
						'restrict_to_image_position' => 'cover,teaser,teaserperm,teaserlink,aftermore',
					) );

					?>
				<h3 class="bTitle linked"><?php $Item->title( array('link_type' => 'permalink') ); ?></h3>
				<?php

				locale_restore_previous();	// Restore previous locale (Blog locale)
			?>

		</div>
		<?php
	}
}