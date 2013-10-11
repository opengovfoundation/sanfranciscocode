<?php

/**
 * The "About" page, explaining this State Decoded website.
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

# Include the PHP declarations that drive this page.
require $_SERVER['DOCUMENT_ROOT'].'/../includes/page-head.inc.php';

# Fire up our templating engine.
$template = new Page;

# Define some page elements.
$template->field->browser_title = 'About';
$template->field->page_title = 'About';

$body = '

<h2>
<a class="title-only" id="about-introduction">Introduction</a>
</h2>
<p>
<a href="http://sanfranciscocode.org">SanFranciscoCode.org</a>
is a non-profit, non-governmental, non-partisan implementation of
<a href="http://www.statedecoded.com/">The State Decoded</a>
brought to you by the folks at the
<a href="http://opengovfoundation.org/">OpenGov Foundation</a>.  It is part of a broader initiative to bring the laws of San Francisco to
the people in more accessible, modern formats that can be used and reused to
bring down the barriers to accessing, understanding and navigating the laws of
San Francisco.

	  You can read more about SF Open Law
<a href="http://sfmoci.github.io/openlaw">here</a>.


<a href="http://sanfranciscocode.org">SanFranciscoCode.org</a>
provides a platform to display city-level legal information in a friendly,
accessible, modern fashion. San Francisco is the second to municipality to
deploy the software, joining
<a href="http://baltimorecode.org">Baltimore, MD</a>, with more coming soon.
</p>

<h2>
<a class="title-only" id="about-beta-testing">Beta Testing</a>
</h2>
<p>
<a href="http://sanfranciscocode.org">SanFranciscoCode.org</a>
is currently in public beta, which is to say that the site is under active
development, with known shortcomings, but it has reached a point where it
would benefit from being used by the general public (who one hopes will
likewise benefit from it). While every effort is made to ensure that the data
provided on
<a href="http://sanfranciscocode.org">SanFranciscoCode.org</a>
is accurate and up-to-date, it would be gravely unwise to rely on it for any
matter of importance while it is in this beta testing phase.
</p>
<p>
	Many more features are under development, including calculations of the
importance of given laws, inclusion of attorney generals’ opinions, court
rulings, extensive explanatory text, social media integration, significant
navigation enhancements, a vastly expanded built-in glossary of legal terms,
scholarly article citations, and much more.
</p>
<h2>
<a class="title-only" id="about-data-sources">Data Sources</a>
</h2>
<p>
	The data that powers San Francisco is available directly from the Office
of the City Attorney with help from American Legal Publishing.  The currently
available Municipal Codes
<a href="http://www.github.com/SFMOCI/openlaw">are posted to GitHub</a>
in unstructured TXT and RTF formats.

	  They will also be available as a zip archive download from SFData (link
forthcoming). The official code is maintained by American Legal Publishing and
should be the primary reference for any legal questions. Even then, it is
always good to consult with a lawyer when interpreting the law. You can find
more about past, current and upcoming legislation at the
<a href="http://www.sfbos.org/index.aspx?page=9681">San Francisco Legislative Research Center</a>.
</p>
<h2>
<a class="title-only" id="about-api">API</a>
</h2>
<p>
	The site has a RESTful, JSON-based API.
<a href="http://www.sanfranciscocode.org/api-key/">Register for an API key</a>
and
<a href="https://github.com/statedecoded/statedecoded/wiki/API-Documentation">read the documentation</a>
for details.
<h2>
<a class="title-only" id="about-thanks">Thanks</a>
</h2>
<p>
<a href="http://sanfranciscocode.org">SanFranciscoCode.org</a>
wouldn’t be possible without the contributions and years of work by
<a href="http://waldo.jaquith.org/">Waldo Jaquith</a>, and the many dozens of people who participated in private alpha and beta
testing of
<a href="http://vacode.org/about/">Virginia Decoded</a>, the first
<a href="http://www.statedecoded.com/">Decoded</a>
site, over the course of a year and a half, beginning in 2010.  This platform
on which this site is based,
<a href="http://www.statedecoded.com/">The State Decoded</a>, was expanded to function beyond Virginia thanks to a generous grant by the
<a href="http://knightfoundation.org/">John S. and James L. Knight Foundation.</a>
  Special thanks to the Board of Supervisors for formalizing and extending the
Open Data Policy of the City and County of San Francisco. Particular thanks to
Supervisors Chiu and Farrell for their leadership on Open Data and Open Law.
</p>
<h2>
<a class="title-only" id="about-colophon">Colophon</a>
</h2>
<p>
Hosted on
<a href="http://www.centos.org/">CentOS</a>, driven by
<a href="http://httpd.apache.org/">Apache</a>,
<a href="http://www.mysql.com/">MySQL</a>, and
<a href="http://www.php.net/">PHP</a>. Hosting by Rackspace. Search by
<a href="http://lucene.apache.org/solr/">Solr</a>. Comments by
<a href="http://disqus.com/">Disqus</a>.
</p>
<h2>
<a class="title-only" id="about-disclaimer">Disclaimer</a>
</h2>
<p>
This is not the official copy of the City and County of San Francisco’s Code
and should not be relied upon for legal or any other official purposes. Please
refer to the official printed copy available
<a href="http://www.amlegal.com/library/ca/sfrancisco.shtml">here</a>
from the City of San Francisco and American Legal Publishing.
</p>
<p>
<a href="http://www.amlegal.com/library/ca/sfrancisco.shtml">
</a>
</p>
<p>
The
<a href="http://opengovfoundation.org/">OpenGov Foundation</a>, City and County of San Francisco offers Open Law data with no warranty as to
accuracy or completeness.
</p>
';

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
	</section>
';
# Put the shorthand $body variable into its proper place.
$template->field->body = $body;
unset($body);

# Put the shorthand $sidebar variable into its proper place.
$template->field->sidebar = $sidebar;
unset($sidebar);

# Parse the template, which is a shortcut for a few steps that culminate in sending the content
# to the browser.
$template->parse();
