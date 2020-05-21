# === VI: Include Post By ===
Contributors: Knighthawk
Tags: shortcode, vars, options, post, params, include
Requires at least: 4.0
Requires PHP: 5.2.4
Tested up to: 5.4
Version: 0.4.200520
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Shortcodes allowing you to display posts inside other posts/pages

## == Description ==

VI: Include Post By - provides your pages and posts with shortcodes allowing you to display other pages and posts inside them either by their ID or by post category. Options to display title,meta,content,thumbnail,excerpt,footer.

## == Coming Soon ==

More display options.
Taxonomy and post type agnostic


## == Instructions ==
Shortcode for including a single post by its ID

    [include-post-by-id
     id="123"
     link="true"
     moretext="Continue Reading"
     card="false"
     display="title,meta,thumbnail,content,excerpt,more,footer,all"
     display_header="title,meta,thumbnail,content,excerpt,more,footer,all"
     display_body="title,meta,thumbnail,content,excerpt,more,footer,all"
     display_footer="title,meta,thumbnail,content,excerpt,more,footer,all"
     class_inner="custom-class-name"
     class_header="custom-class-name"
     class_body="custom-class-name"
     class_footer="custom-class-name"
     class_thumbnail="custom-class-name"
    ]

* id = post to be shown
* link = whether the title/thubmnail are links to the post
* moretext = edit the text of the read-more link
* card = will set class names to bootstrap cards, no further class customization is required
* display[x] = display options as a CSV, order counts
* class[x] = a custom class name that will be added to each container element


Shortcode for including single/multiple posts by their category
Every option required or used in the include-post-by-id will also pass through here.
This function will query the DB and then call include-post-by-id once for each resulting post.
Shown here are only the options which are unique to this function.

    [include-post-by-cat
     cat="123"
     order="DESC"
     orderby="date"
     paginate=true
     perpage="5"
     offset="0"
     class_container="custom-class-name"
    ]

* cat = category to be shown
* order = sort order
* orderby = what to sort by
* paginate = true/false
* perpage = items per page. -1 = all
* offset = how many posts to skip, useful if you are combining multiple includes
* class_container = custom-class-name used in the wrapper element


## == Changelog ==

*0.4.200520*
* Update: display and class
* added: card = true for auto bootstrap class names
* added: display_header, display_body, display_footer. each internal element can be targeted to a header, body, and footer
* added: class for the header,body,footer, and thumbnail
* changed: thumbnail is now displayed as a background image in a container with styling set to a given aspect ratio.
* todo: may add suport for classic thumbnails

*0.4.200417*
* Update: transients
* Only uses a single transient, and lookup. Offset is processed internally rather than putting the weight on a DB query.
* Update: paginate
* Page numbers and "..." now work properly under all tested circumstances.
* re-write was needed after the transient/offset changes.

*0.4.200411*
* Fixed: offset now works as expected.
* Was previously only working for pageination, it now works with both pageination AND a starting offset.


*0.4.200403*
* Updated Class names

*0.3.191125*
* Fixing the 'thumbnail' and 'more' functions

*0.3.191113*
* Added the custom class entry field that will place a class name in the wrapper element

*0.3.191007*
* added the baility for the display input field to actually cause the output to be in that order

*0.2.181219*
* fixed bug in include-post-by-id where display data was being cached between multiple executions

*0.2.181214*
* tested and functional

*0.1.181213*
* self contained code, not reliant on functions from outside WP core

*0.1.181212*
* FPL
