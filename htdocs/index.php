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
		<li><a href="http://administrative.'.BASE_SITE_DOMAIN.'/">Administrative</a></li>
		' . /*<!--li><a href="http://building.'.BASE_SITE_DOMAIN.'/">Building</a></li>*/ '
		<li><a href="http://business.'.BASE_SITE_DOMAIN.'/">Business</a></li>
		<li><a href="http://campaign.'.BASE_SITE_DOMAIN.'/">Campaign</a></li>
		<li><a href="http://charter.'.BASE_SITE_DOMAIN.'/">Charter</a></li>
		<li><a href="http://elections.'.BASE_SITE_DOMAIN.'/">Elections</a></li>
		<li><a href="http://electrical.'.BASE_SITE_DOMAIN.'/">Electrical</a></li>
		<li><a href="http://environment.'.BASE_SITE_DOMAIN.'/">Environment</a></li>
		<li><a href="http://fire.'.BASE_SITE_DOMAIN.'/">Fire</a></li>
		<li><a href="http://health.'.BASE_SITE_DOMAIN.'/">Health</a></li>
		<li><a href="http://housing.'.BASE_SITE_DOMAIN.'/">Housing</a></li>
		<li><a href="http://mechanical.'.BASE_SITE_DOMAIN.'/">Mechanical</a></li>
		<li><a href="http://park.'.BASE_SITE_DOMAIN.'/">Park</a></li>
		<li><a href="http://planning.'.BASE_SITE_DOMAIN.'/">Planning</a></li>
		<li><a href="http://plumbing.'.BASE_SITE_DOMAIN.'/">Plumbing</a></li>
		<li><a href="http://port.'.BASE_SITE_DOMAIN.'/">Port</a></li>
		<li><a href="http://public-works.'.BASE_SITE_DOMAIN.'/">Public Works</a></li>
		<li><a href="http://subdivision.'.BASE_SITE_DOMAIN.'/">Subdivision</a></li>
		<li><a href="http://transportation.'.BASE_SITE_DOMAIN.'/">Transportation</a></li>
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
