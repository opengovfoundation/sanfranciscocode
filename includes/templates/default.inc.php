<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]> <html lang="en" class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8"/>
	<title>{{browser_title}}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{{meta_tags}}
	<link rel="stylesheet" href="/css/reset.css" type="text/css" media="screen">
	<link rel="stylesheet" href="/css/master.css" type="text/css" media="screen">
	<link rel="stylesheet" href="/css/print.css" type="text/css" media="print">
	<link rel="stylesheet" href="/css/jquery.qtip.css" type="text/css" media="screen">
	<link rel="home" title="Home" href="/" />
	{{link_rel}}
	{{css}}
	{{inline_css}}
	<!-- CSS: Generic print styles -->
	<!--<link rel="stylesheet" media="print" href="/css/print.css"/>-->

	<!-- For the less-enabled mobile browsers like Opera Mini -->
	<!--<link rel="stylesheet" media="handheld" href="/css/handheld.css"/>-->

	<!-- Make MSIE play nice with HTML5 & Media Queries -->
	<script src="/js/modernizr.custom.23612.js"></script>
	<script src="/js/respond.min.js"></script>
	<!--[if lt IE 9]>
	<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
	<![endif]-->

	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
</head>
<body>
	<div id="content">
		<div id="corner-banner">
			<span><a href="{{home_site_url}}about/">Beta</a></span>
		</div>
		<header id="masthead">
			<hgroup>
				<h1><a href="{{home_site_url}}">San Francisco Decoded</a></h1>
				<h2>Accessible, User&#8202;-&#8202;Friendly Law for All People</h2>
			</hgroup>
			<nav id="main_navigation">
				<div id="search">
				</div> <!-- // #search -->
				<ul>
					<li><a href="{{home_site_url}}" class="ir" id="home">Home</a></li>
					<li><a href="{{home_site_url}}about/" class="ir" id="about">About</a></li>
				</ul>
			</nav> <!-- // #main_navigation -->
		</header> <!-- // #masthead -->
		<section id="page">
			<nav id="breadcrumbs">
				{{breadcrumbs}}
			</nav>

			<nav id="intercode">
				{{intercode}}
			</nav> <!-- // #intercode -->

			<h1>{{page_title}}</h1>

			<section id="sidebar">
				{{sidebar}}
				<section>
					<h1>API</h1>
						<p>San Francisco Decoded has an API. <a href="{{home_site_url}}api-key/">Register for a key</a> and
						<a href="https://github.com/statedecoded/statedecoded/wiki/API-Documentation">read the documentation</a>.
						You could be using it in 30 seconds.</p>
					</h1>
				</section>
				<section>
					<p>Powered by <a href="http://www.statedecoded.com/">The State Decoded</a>.</p>
					<p><a href="{{home_site_url}}about/#about-disclaimer">Disclaimer</a>.</p>
				</section>
			</section>

			{{body}}

		</section> <!-- // #page -->

		<footer id="page_footer">
			<p>
				<a href="{{home_site_url}}api-key/">APIs & Bulk Downloads</a> |
				Copyright 2013 the <a href="http://opengovfoundation.org/">OpenGov Foundation</a>.
				Design by <a href="http://www.boboroshi.com/">John Athayde</a>.
				Powered by <a href="http://www.statedecoded.com/">The State Decoded</a>.
				All user-contributed content is, of course, owned by its authors.
				The municipal code, charter and all rules and regulations on this website
				are owned by the citizens of San Francisco and, consequently, they are not
				governed by copyright—so do whatever you want with it! The information on
				this website does not constitute legal advice—nobody here is acting as
				your attorney, and nothing that you read here is a substitute for a
				competent attorney. OpenGov makes no guarantee that this information is
				accurate or up-to-date, although we try our best. Seriously, OpenGov is
				not your attorney. Heck, we’re not attorneys at all.
				<a href="{{home_site_url}}about/#about-disclaimer">Disclaimer</a>.
			</p>
		</footer>
	</div>
	{{javascript_files}}
	<script>
		{{javascript}}
	</script>
	<script src="/js/jquery.qtip.min.js"></script>
	<script src="/js/contactable-init.js"></script>
	<script src="/js/uservoice.js"></script>
</body>
</html>
