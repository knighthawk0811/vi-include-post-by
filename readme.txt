=== VI: Include Post By ===
Contributors: knighthawk0811, Knighthawk
Tags: get, params, shortcode, vars
Requires at least: 4.0
Tested up to: 5.4
Version: 0.4.200411
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

VI: Include Post By - provides your pages and posts with shortcodes allowing you to display other pages and posts inside them either by their ID or by post category. Options to display title,meta,content,thumbnail,excerpt,footer.

== Coming Soon ==

More display options. Bootstrap Cards option. Custom class names with a header/body/footer element.


== Instructions ==

>    [include-post-by-id
>	    id="123"
>	    display="title,meta,thumbnail,content,excerpt,more,footer,all"
>	    class="custom-class-name"
>	    link="true"
>	    moretext="Continue Reading"
>    ]

	    id = post to be shown
	    display = display options CSV, order counts
	    link = whether the title/thubmnail are links to the post
	    moretext = edit the text of the read-more link


>    [include-post-by-cat
>        cat="123"
>        order="DESC"
>        orderby="date"
>        paginate=true
>        perpage="5"
>        offset="0"
>	     display="title,meta,thumbnail,content,excerpt,more,footer,all"
>	     class="custom-class-name"
>	     container="custom-class-name"
>	     link="true"
>        moretext="Continue Reading"
>    ]

	    cat = category to be shown
	    order = sort order
	    orderby = what to sort by
	    paginate = true/false
	    perpage = items per page. -1 = all
	    offset = how many posts to skip, useful if you are combining multiple includes
	    display = from include-post-by-id
	    class= custom-class-name used in the internal element
	    container= custom-class-name used in the wrapper element
	    link = from include-post-by-id
	    moretext = from include-post-by-id


== Changelog ==

= 0.4.200411 =
* Fixed: offset now works as expected. 
* Was previously only working for pageination, it now works with both pageination AND a starting offset.


= 0.4.200403 =
* Updated Class names

= 0.3.191125 =
* Fixing the 'thumbnail' and 'more' functions

= 0.3.191113 =
* Added the custom class entry field that will place a class name in the wrapper element

= 0.3.191007 =
* added the baility for the display input field to actually cause the output to be in that order

= 0.2.181219 =
* fixed bug in include-post-by-id where display data was being cached between multiple executions

= 0.2.181214 =
* tested and functional

= 0.1.181213 =
* self contained code, not reliant on functions from outside WP core

= 0.1.181212 =
FPL