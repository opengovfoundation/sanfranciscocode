<?php

/**
 * The site home page.
 *
 * Displays a list of the top-level structural units. May be customized to display introductory
 * text, sidebar content, etc.
 *
 * PHP version 5
 *
 * @author		Waldo Jaquith <waldo at jaquith.org>
 * @copyright	2010-2013 Waldo Jaquith
 * @license		http://www.gnu.org/licenses/gpl.html GPL 3
 * @version		0.7
 * @link		http://www.statedecoded.com/
 * @since		0.1
 *
 */

/*
 * Include the PHP declarations that drive this page.
 */
require $_SERVER['DOCUMENT_ROOT'].'/../includes/page-head.inc.php';

if( ENABLE_DEBUG )  {
	error_log( "index.php Called url = ".$_SERVER['REQUEST_URI'] );
}

/*
 * Fire up our templating engine.
 */
$template = new Page;

$template->field->browser_title = SITE_TITLE.': The '.LAWS_NAME.', for Humans.';

/*
 * Initialize the body variable.
 */
$body = '';

/*
 * Initialize the sidebar variable.
 */
$sidebar = '
	<section>
	<h1>Welcome</h1>
	<p>
		SanFranciscoCode.org provides the San Francisco City laws, rules and
		regulations on one friendly website. No copyright restrictions, a
		modern API and all of the niceties of modern website design. It’s like
		the expensive software lawyers use, but free and wonderful.
	</p>
	<p>
		This is a public beta test of <a href="http:///www.sanfranciscocode.org">SanFranciscoCode.org</a>, which is to say that
		everything is under development. Things may be funny looking, broken, and
		generally under development right now.
	</p>
	</section>
	<section>
		<h1>Stay Updated</h1>
		<p>
			<a href="http://eepurl.com/FUc0b">Click here to join our mailing list</a>
		</p>
		<p>
			Want to open your city or state? <a href="mailto:sayhello@opengovfoundation.org?Subject=Help%20Open%20My%20City%20or%20State">Drop us a line!</a>
		</p>
	</section>';



$body .= '<article>
	<h1>Sections of the '.LAWS_NAME.'</h1>
	<p>These are the sections of the '.LAWS_NAME.'.</p>
	<ul class="level-1">
		<li><a href="http://administrative.sanfranciscocode.org/">Administrative</a></li>
		' . /*<!--li><a href="http://building.sanfranciscocode.org/">Building</a></li>*/ '
		<li><a href="http://business.sanfranciscocode.org/">Business</a></li>
		<li><a href="http://campaign.sanfranciscocode.org/">Campaign</a></li>
		<li><a href="http://charter.sanfranciscocode.org/">Charter</a></li>
		<li><a href="http://elections.sanfranciscocode.org/">Elections</a></li>
		<li><a href="http://electrical.sanfranciscocode.org/">Electrical</a></li>
		<li><a href="http://environment.sanfranciscocode.org/">Environment</a></li>
		<li><a href="http://fire.sanfranciscocode.org/">Fire</a></li>
		<li><a href="http://health.sanfranciscocode.org/">Health</a></li>
		<li><a href="http://housing.sanfranciscocode.org/">Housing</a></li>
		<li><a href="http://mechanical.sanfranciscocode.org/">Mechanical</a></li>
		<li><a href="http://park.sanfranciscocode.org/">Park</a></li>
		<li><a href="http://planning.sanfranciscocode.org/">Planning</a></li>
		<li><a href="http://plumbing.sanfranciscocode.org/">Plumbing</a></li>
		<li><a href="http://port.sanfranciscocode.org/">Port</a></li>
		<li><a href="http://public-works.sanfranciscocode.org/">Public Works</a></li>
		<li><a href="http://subdivision.sanfranciscocode.org/">Subdivision</a></li>
		<li><a href="http://transportation.sanfranciscocode.org/">Transportation</a></li>
	</ul>
</article>';

/*
 * Put the shorthand $body variable into its proper place.
 */
$template->field->body = $body;
unset($body);

/*
 * Put the shorthand $sidebar variable into its proper place.
 */
$template->field->sidebar = $sidebar;
unset($sidebar);

/*
 * Parse the template, which is a shortcut for a few steps that culminate in sending the content
 * to the browser.
 */
$template->parse();
