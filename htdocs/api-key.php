<?php

/**
 * The API key registration page.
 *
 * PHP version 5
 *
 * @author		Waldo Jaquith <waldo at jaquith.org>
 * @copyright	2010-2012 Waldo Jaquith
 * @license		http://www.gnu.org/licenses/gpl.html GPL 3
 * @version		0.7
 * @link		http://www.statedecoded.com/
 * @since		0.6
*/

/*
 * Include the PHP declarations that drive this page.
 */
require '../includes/page-head.inc.php';

/*
 * Fire up our templating engine.
 */
$template = new Page;

/*
 * Define some page elements.
 */
$template->field->browser_title = 'Register for an API Key';
$template->field->page_title = 'Register for an API Key';

/*
 * Provide some custom CSS for this form.
 */
$template->field->inline_css = '
	<style>
		#required-note {
			font-size: .85em;
			margin-top: 2em;
		}
		.required {
			color: #f00;
		}
		#api-registration label {
			display: block;
			margin-top: 1em;
		}
		#api-registration input[type=text] {
			width: 35em;
		}
		#api-registration input[type=submit] {
			display: block;
			clear: left;
			margin-top: 1em;
		}
	</style>';

/*
 * Create an instance of the API class.
 */
$api = new API();

/*
 * Define the sidebar.
 */
$sidebar = '<h1>Nota Bene</h1>
	<section>
		<p>'.SITE_TITLE.' is not your database. Cache accordingly.</p>

		<p>Consider whether <a href="/downloads/">a bulk download</a> might be more appropriate
		for your purposes.</p>
	</section>';

/*
 * If the form on this page is being submitted, process the submitted data.
 */
if (isset($_POST['form_data']))
{

	/*
	 * Pass the submitted form data to the API class, as an object rather than as an array.
	 */
	$api->form = (object) $_POST['form_data'];

	/*
	 * If this form hasn't been completed properly, display the errors and re-display the form.
	 */
	if ($api->validate_form() === false)
	{
		$body .= '<p class="error">Error: '.$api->form_errors.'</p>';
		$body .= $api->display_form();
	}

	/*
	 * But if the form has been filled out correctly, then proceed with the registration process.
	 */
	else
	{

		/*
		 * Register this key.
		 */
		try
		{
			$api->register_key();
		}
		catch (Exception $e)
		{
			$body = '<p class="error">Error: ' . $e->getMessage() . '</p>';
		}

		$body .= '<p>You have been sent an e-mail to verify your e-mail address. Please click the
					link in that e-mail to activate your API key.</p>';
	}

}

/*
 *
 */
elseif (isset($_GET['secret']))
{
	/*
	 * If this isn't a five-character string, bail -- something's not right.
	 */
	if (strlen($_GET['secret']) != 5)
	{
		$body .= '<h2>Error</h2>
			<p>Invalid API key.</p>';
	}
	else
	{
		$api->secret = $_GET['secret'];
		$api->activate_key();
		$body .= '<h2>API Key Activated</h2>

				<p>Your API key has been activated. You may now make requests from the API. Your
				key is:</p>

				<p><code>'.$api->key.'</code></p>';
	}
}

/* If this page is being loaded normally (that is, without submitting data), then display the registration
 * form.
 */
else
{
$template->field->page_title = 'API &amp; Downloads';

	$body = '
	<p>
		To use the San Francisco Decoded API, you will need to register for a key below.
		You may want to <a href="https://github.com/statedecoded/statedecoded/wiki/API-Documentation">read
		the documentation</a> first.
	</p>
	<p>
		Note that you will need to use the proper full url
		for each section of the code that you want to interface with - each section has a
		separate subdomain.  E.g., for the Business codes, you should be using the
		<code>business.sanfranciscocode.org</code> domain.
	</p>
	<p>
		Alternately, you may download the bulk data for any section.  A list of those are
		below.
	</p>
	<h3>Bulk Data Downloads</h3>
	<ul class="bulk-data-links">
		' . /*<li><a href="http://administrative.sanfranciscocode.org/downloads/">Administrative</a></li>*/ '
		' . /*<!--li><a href="http://building.sanfranciscocode.org/downloads/">Building</a></li>*/ '
		<li><a href="http://business.sanfranciscocode.org/downloads/">Business</a></li>
		<li><a href="http://campaign.sanfranciscocode.org/downloads/">Campaign</a></li>
		<li><a href="http://charter.sanfranciscocode.org/downloads/">Charter</a></li>
		<li><a href="http://elections.sanfranciscocode.org/downloads/">Elections</a></li>
		<li><a href="http://electrical.sanfranciscocode.org/downloads/">Electrical</a></li>
	</ul>
	<ul class="bulk-data-links">
		<li><a href="http://environment.sanfranciscocode.org/downloads/">Environment</a></li>
		<li><a href="http://fire.sanfranciscocode.org/downloads/">Fire</a></li>
		<li><a href="http://health.sanfranciscocode.org/downloads/">Health</a></li>
		<li><a href="http://housing.sanfranciscocode.org/downloads/">Housing</a></li>
	</ul>
	<ul class="bulk-data-links">
		<li><a href="http://mechanical.sanfranciscocode.org/downloads/">Mechanical</a></li>
		<li><a href="http://park.sanfranciscocode.org/downloads/">Park</a></li>
		<li><a href="http://planning.sanfranciscocode.org/downloads/">Planning</a></li>
		<li><a href="http://plumbing.sanfranciscocode.org/downloads/">Plumbing</a></li>
	</ul>
	<ul class="bulk-data-links">
		<li><a href="http://port.sanfranciscocode.org/downloads/">Port</a></li>
		<li><a href="http://public-works.sanfranciscocode.org/downloads/">Public Works</a></li>
		<li><a href="http://subdivision.sanfranciscocode.org/downloads/">Subdivision</a></li>
		<li><a href="http://transportation.sanfranciscocode.org/downloads/">Transportation</a></li>
	</ul>
	';

	$body .= '<section class="api-register"><h1>Register for an API Key</h1>';
	$body .= $api->display_form();
	$body .= '</section>';
}

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
 * Put the shorthand $sidebar variable into its proper place.
 */
$template->field->sidebar = $sidebar;
unset($sidebar);

/*
 * Parse the template, which is a shortcut for a few steps that culminate in sending the content to
 * the browser.
 */
$template->parse();
