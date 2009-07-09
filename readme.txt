=== Get Custom Field Values ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: custom field, meta, extra, data, post, coffee2code
Requires at least: 2.1
Tested up to: 2.5
Stable tag: 2.5
Version: 2.5

Easily retrieve and control the display of any custom field values/meta data for posts, inside or outside "the loop".

== Description ==

Easily retrieve and control the display of any custom field values/meta data for posts, inside or outside "the loop".

The power of custom fields gives this plugin the potential to be dozens of plugins all rolled into one.

This is a simple plugin that allows you to harness the power of custom fields/meta data.  You can define $before and/or $after text/HTML to bookend your results.  If no matching custom field by the name defined in $field was found, nothing gets displayed (including no $before and $after) (unless $none is defined, in which case the $none text gets used as if it was the match).  If multiple same-named custom fields for a post are defined, only the first will be retrieved unless $between is defined, in which case all are returned, with the $between text/HTML joining them).  If $before_last is defined along with $between, then the text/HTML in $before_last is used prior to the last item in the list (i.e. if you want to add an "and" before the last item).  Head down to the Tip & Examples section to see how this plugin can be cast in dozens of different ways.

You can filter the custom field values that the plugin would display.  Add filters for 'the_meta' to filter custom field data (see the end of the code file for commented out samples you may wish to include).  You can also add per-meta filters by hooking 'the_meta_$sanitized_field'.  `$sanitized_field` is a clean version of the value of `$field` where everything but alphanumeric and underscore characters have been removed.  So to filter the value of the "Related Posts" custom field, you would need to add a filter for 'the_meta_RelatedPosts'.

== Installation ==

1. Unzip `get-custom.zip` inside the `/wp-content/plugins/` directory, or upload `get-custom.php` to `/wp-content/plugins/`
1. (optional) Add filters for 'the_meta' to filter custom field data (see the end of the plugin file for commented out samples you may wish to include).  And/or add per-meta filters by hooking 'the_meta_$field'
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Give post(s) a custom field with a value.
1. Use the function `c2c_get_custom()` somewhere inside "the loop" and/or use the function `c2c_get_recent_custom()` outside "the loop"; use 'echo' to display the contents of the custom field; or use it as an argument to another function

== Template Tags ==

The plugin provides two optional template tags for use in your theme templates.

= Functions =

* `<?php function c2c_get_custom( $field, $before='', $after='', $none='', $between='', $before_last='' ) ?>`
Template tag for use inside "the loop" and applies to the currently listed post.

* `<?php function c2c_get_recent_custom( $field, $before='', $after='', $none='', $between=', ', $before_last='', $limit=1, $unique=false, $order='DESC', $include_pages=true, $show_pass_post=false )  ?>`
Template tag for use outside "the loop" and applies for custom fields regardless of post.

= Arguments =

* `$field`
Required argument.  The custom field key of interest.

* `$before`
Optional argument.  The text to display before all field value(s).

* `$after`
Optional argument.  The text to display after all field value(s).

* `$none`
Optional argument.  The text to display in place of the field value should no field value exists; if defined as '' and no field value exists, then nothing (including no `$before` and `$after`) gets displayed.

* `$between`
Optional argument.  The text to display between multiple occurrences of the custom field; if defined as '', then only the first instance will be used.

* `$before_last`
Optional argument.  The text to display between the next-to-last and last items listed when multiple occurrences of the custom field; `$between` MUST be set to something other than '' for this to take effect.

Arguments that only apply to `c2c_get_recent_custom()`:

* `$limit`
Optional argument.  The limit to the number of custom fields to retrieve.

* `$unique`
Optional argument.  Boolean ('true' or 'false') to indicate if each custom field value in the results should be unique.

* `$order`
Optional argument.  Indicates if the results should be sorted in chronological order ('ASC') (the earliest custom field value listed first), or reverse chronological order ('DESC') (the most recent custom field value listed first).

* `$include_pages`
Optional argument.  Boolean ('true' or 'false') to indicate if pages should be included when retrieving recent custom values; default is 'true'.

* `$show_pass_post`
Optional argument.  Boolean ('true' or 'false') to indicate if password protected posts should be included when retrieving recent custom values; default is 'false'.

= Examples =

* `<?php echo c2c_get_custom('mymood'); ?>  // with this simple invocation, you can echo the value of any metadata field`

* `<?php echo c2c_get_custom('mymood', 'Today's moods: ', '', ', '); ?>`

* `<?php echo c2c_get_recent_custom('mymood', 'Most recent mood: '); ?>`

* `<?php echo c2c_get_custom('mymood', '(Current mood: ', ')', ''); ?>`

* `<?php echo c2c_get_custom('mylisten', 'Listening to : ', '', 'No one at the moment.'); ?>`

* `<?php echo c2c_get_custom('myread', 'I\'ve been reading ', ', if you must know.', 'nothing'); ?>`

* `<?php echo c2c_get_custom('todays_link', '<a class="tlink" href="', '" >Today\'s Link</a>'); ?>`

* `<?php echo c2c_get_custom('related_offsite_links', 
	   'Here\'s a list of offsite links related to this post:<ol><li><a href="',
	   '">Related</a></li></ol>',
	   '',
	   '">Related</a></li><li><a href="'); ?>`

* `<?php echo c2c_get_custom('more_pictures',
	   'Pictures I\'ve taken today:<br /><div class="more_pictures"><img alt="[photo]" src="',
	   '" /></div>',
	   '',
	   '" /> : <img alt="[photo]" src="'); ?>`

* Custom 'more...' link text, by replacing `<?php the_content(); ?>` in index.php with this:
`<?php the_content(c2c_get_custom('more', '<span class="morelink">', '</span>', '(more...)')); ?>`

== Frequently Asked Questions ==

= I added the template tag to my template and the post has the custom field I'm asking for but I don't see anything about it on the page; what gives? =

Did you `echo` the return value of the function, e.g. `<?php echo c2c_get_custom('mood', 'My mood: '); ?>`
