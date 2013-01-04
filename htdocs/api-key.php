<?php

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

	// Pass the submitted form data to the API class, as an object rather than as an array.
	$api->form = (object) $_POST['form_data'];
	
	if ($api->validate_form() === false)
	{
		$body .= '<p class="error">Error: '.$api->form_errors.'</p>';
		$body .= $api->display_form();
	}
	
	try
	{
		$api->register_key();
	}
	catch (Exception $e)
	{
		$body = '<p class="error">Error: ' . $e->getMessage() . '</p>';
	}

	$page_body .= '<p>You have been sent an e-mail to verify your e-mail address. Please click the link in
					that e-mail to activate your API key.</p>';
	
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
		$body .= '<h1>Error</h1>
			<p>Invalid API key.</p>';
	}
	else
	{
		$api->secret = $_GET['secret'];
		$api->activate_key();
		$body .= '<h1>API Key Activated</h1>
				<p>Your API key has been activated.</p>';
	}
}

/* If this page is being loaded normally (that is, without submitting data), then display the registration
 * form.
 */
else
{
	$body = $api->display_form();
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

?>