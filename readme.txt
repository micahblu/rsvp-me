=== RSVP ME ===
Contributors: MicahBlu
Donate link: http://micahblu.com/products/rsvp-me
Tags: rsvp plugin, rsvp widget, event, rsvp events, rsvp calendar, Events, Event Calendar, Event Widget, widget, Event, Event plugin, organize, plan, venue, catering, planning, planner, organization, wedding, guestlist, reserve, reservations, reservation, byob
Requires at least: 2.0.2
Tested up to: 4.0
Stable tag: 1.9.9

== Description ==
RSVP ME is a simple yet powerful Wordpress plugin which allows you to create events that your site visitors can RSVP to via the event's page or thorough a calendar widget. 

= Features =
* RSVP Event Calendar widget (single event for that day opens in a clean lightbox overlay)
* RSVP Events are Native Custom Post types that fully embrace the Wordpress Ecosystem
* Specify Venue, address, date, etc.
* Reservers can send an additional message with their response
* Supports multiple events in a single day
* Events are searchable
* Permalinks are supported so http://yoursite.com/events/my-special-event takes you to the single RSVP event page (Permalinks must be properly setup)
* RSVP submissions are done with ajax
* A very nifty calendar widget styler

== Installation ==

This section describes how to install the plugin and get it working.

1. Unpack downloaded zip
2. Upload the whole directory to the /wp-content/plugins/ directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Add the RSVP ME Calendar widget to a sidebar.
5. Add events from the RSVP Events section and your users will be 
able to RSVP from the RSVP Me widget calendar or from the single Event page

== Frequently Asked Questions ==

questions anyone? Or any ideas for improvements/new features then visit 
here: http://micahblu.com and get in touch.

== Screenshots ==
1. ![Admin Screen](screenshot-1.png "Admin Screenshot")
2. ![Calendar Widget](screenshot-2.png "Calendar Widget")


== Changelog ==

04-12-2012
 1. Added ability to modify reservation labels
 2. Tested with 3.8.2


01-31-2014
 1. Fixed a deprecated function bug that conflicted with WordPress 3.8.1

11-28-2013
 1. Fixed runtime errors for undefined variables and fields
 2. Fixed a styling issue with calendar widget that prevented the calendar from being full width
 3. Fixed a missing beginning article tag in one of the event templates
 4. Fixed a shortcode error (Shortcode is still in Beta). Shortcodes are invoked witht the following: [rsvp_event id="even_id"] where event id is the actual integer id of the event. You can find the event Id from the RSVP Events page in the respective event's row.
 5. Added ID column to RSVP Events rows

10-28-2013
 1. Fixed header already output error. Thanks Andy
 2. Fixed a featured image theme support conflict. Thanks Again Andy

10-01-2013
 1. Fixed a javascript error in the event calendar widget
 2. Added a new admin options section that allows for a live preview styling of the event calendar
 3. Got rid of unnecessary css styles

09-28-2013
 1. Fixed an installation database error whereas the respondents table was not being created
 2. Cleaned up the default alert messages
 3. Added plugin banner

09-27-2013
 1. Major update, events are now native custom post types
 2. Supports Featured Image for Events
 3. Supports Event categories and tags
 4. Several bug fixes
 5. Better default event form layout
 6. Please note that if upgrading this new plugin will NOT remember your old event data

09-14-2013
 1. Updated the widget core to extend Wordpress's Widget class
 2. Now allows a user defined title for the event calendar widget
 3. Fixed a jquery dependancy bug caused by themes which place jquery in the footer

12-30-2010 updates by Micah Blu 

09-23-2013

* Event Shortcodes added (Beta)
* Event RSVP forms are now templated
* A cleaner more reliable lightbox is being used
* Several minor bug fixes

09-14-2013

* Updated the widget core to extend Wordpress's Widget class
* Now allows a user defined title for the event calendar widget
* Fixed a jquery dependancy bug caused by themes which place jquery in the footer
* Major code refactoring/cleanup

05-23-2012

* added needed padding on the rsvp form needed for some themes
* added an isset() on an index variable to avoid runtime errors

05-22-2012

* Cleaned up some css styling rules and checked plugin against wordpress 3.5.1

12-30-2010 updates by Micah Blu 

 * Made RSVP ME more widget friendly by encapsulating the widget in <li class="widget"> tags
 * Added better default styles for the calendar widget

12-31-2010

* First stable release 0.9

== Upgrade Notice ==

If you have any issues upgrading please reach me via twitter @micahblu or through my site @ micahblu.com
