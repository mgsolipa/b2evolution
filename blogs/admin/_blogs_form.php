<?php 
/*
 * b2evolution - http://b2evolution.net/
 *
 * Copyright (c) 2003-2004 by Francois PLANQUE - http://fplanque.net/
 * Released under GNU GPL License - http://b2evolution.net/about/license.html
 */
switch( $next_action )
{
	case 'create':
		$submit = T_('Create new blog!');
		break;
		
	case 'update':
		$submit = T_('Update blog!');
		break;
}
?>
<form class="fform" method="post">
	<input type="hidden" name="action" value="<?php echo $next_action; ?>" />
	<input type="hidden" name="blog" value="<?php echo $blog; ?>" />

	<fieldset>
		<legend><?php echo T_('General parameters') ?></legend>
		<?php 
			form_text( 'blog_name', $blog_name, 50, T_('Full Name') );
			form_text( 'blog_shortname', $blog_shortname, 12, T_('Short Name') );
		?>
		<fieldset>
			<div class="label"><label for="blog_lang"><?php echo T_('Main Language') ?>:</label></div> 
			<div class="input"><select name="blog_lang" id="blog_lang"><?php lang_options( $blog_lang )?></select></div>
		</fieldset>
	</fieldset>

	<fieldset>
		<legend><?php echo T_('Access parameters') ?></legend>
		<fieldset>
			<div class="label"><label for="blog_siteurl"><?php echo T_('Blog Folder URL') ?>: </label></div> 
			<div class="input"><code><?php echo $baseurl ?></code><input type="text" name="blog_siteurl" id="blog_siteurl" size="40" maxlength="120" value="<?php echo format_to_output($blog_siteurl, 'formvalue') ?>"/>
			<span class="notes"><?php echo T_('This is the URL to the directory where the <em>Stub filename</em> and <em>Static filename</em> files live. No trailing slash. (If you don\'t know, leave this field empty.)') ?></span></div>
		</fieldset>
		
		<?php 
			form_text( 'blog_filename', $blog_filename, 30, T_('Stub Filename'), T_('This is the <strong>file</strong>name of the main file (e-g: blog_b.php) used to display this blog. This is used mainly for static page generation, but setting this incorrectly may also cause navigation to fail.') );

			form_text( 'blog_stub', $blog_stub, 30, T_('Stub Urlname'), T_('This is the <strong>url</strong>name of the main file (e-g: blog_b.php) used to display this blog. A typical setting would be setting this to your Filename without the .php extension, if your webserver supports this. <strong>If you are not sure how to set this, use the same as Stub Filename.</strong> This is used by permalinks, trackback, pingback, etc. Setting this incorrectly may cause these to fail.') );

			form_text( 'blog_staticfilename', $blog_staticfilename, 30, T_('Static Filename'), T_('This is the filename that will be used when you generate a static (.html) version of the blog homepage.') );
		?>
	</fieldset>

	<fieldset>
		<legend><?php echo T_('After each new post...') ?></legend>
		<?php 
			form_checkbox( 'blog_pingb2evonet', $blog_pingb2evonet, T_('Ping b2evolution.net'), T_("to get listed on the \"recently updated\" list. PLEASE NOTE: If you removed the b2evolution button and the link to b2evolution from your blog, don't even bother enabling this. You will *not* be approved and your blog will be blacklisted. Also, the Full Name of your blog must be written in ISO 8859-1 (Latin-1) charset, otherwise we cannot display it on b2evolution.net. You can use HTML entities (e-g &amp;Kappa;) for non latin chars.") );
			form_checkbox( 'blog_pingtechnorati', $blog_pingtechnorati, T_('Ping technorati.com'), T_('to give notice of new post.') );
			form_checkbox( 'blog_pingweblogs', $blog_pingweblogs, T_('Ping weblogs.com'), T_('to give notice of new post.') );
			form_checkbox( 'blog_pingblodotgs', $blog_pingblodotgs, T_('Ping blo.gs'), T_('to give notice of new post.') );
		?>
	</fieldset>
			
	<fieldset>
		<legend><?php echo T_('Description') ?></legend>
		<?php 
			form_text( 'blog_tagline', $blog_tagline, 50, T_('Tagline'), T_('This is diplayed under the blog name on the blog template'), 250 );

			form_text( 'blog_description', $blog_description, 60, T_('Short Description'), T_('This is is used in meta tag description and RSS feeds. NO HTML!'), 250, 'large' );

			form_text( 'blog_keywords', $blog_keywords, 60, T_('Keywords'), T_('This is is used in meta tag keywords. NO HTML!'), 250, 'large' );

		?>
		
		<fieldset>
			<div class="label"><label for="blog_longdesc" ><?php echo T_('Long Description') ?>:</label></div> 
			<div class="input"><textarea name="blog_longdesc" id="blog_longdesc" rows="3" cols="50" class="large"><?php echo $blog_longdesc ?></textarea>
			<span class="notes"><?php echo T_('This is displayed on the blog template.') ?></span></div>
		</fieldset>
		
		<fieldset>
			<div class="label"><label for="blog_roll" ><?php echo T_('Blogroll') ?>:</label></div> 
			<div class="input"><textarea name="blog_roll" id="blog_roll" rows="3" cols="50" class="large"><?php echo $blog_roll ?></textarea>
			<span class="notes"><?php echo T_('This is displayed on the blog template.') ?></span></div>
		</fieldset>
	</fieldset>	

	<fieldset>
		<legend><?php echo T_('Advanced options') ?></legend>
		<?php 
			form_checkbox( 'blog_allowtrackbacks', $blog_allowtrackbacks, T_('Allow trackbacks'), T_("Allow other bloggers to send trackbacks to this blog, letting you know when they refer to it. This will also let you send trackbacks to other blogs.") );
			form_checkbox( 'blog_allowpingbacks', $blog_allowpingbacks, T_('Allow pingbacks'), T_("Allow other bloggers to send pingbacks to this blog, letting you know when they refer to it. This will also let you send pingbacks to other blogs.") );
		?>
	</fieldset>
		
	<fieldset>
		<fieldset>
			<div class="input">
				<input type="submit" name="submit" value="<?php echo $submit ?>" class="search">
				<input type="reset" value="Reset" class="search">
			</div>
		</fieldset>
	</fieldset>

	<div class="clear"></div>
</form>
