<?php

/**
 * Comment Controller
 *
 * Controller to interact with Disqus comments
 *
 * PHP version 5
 *
 * @license		http://www.gnu.org/licenses/gpl.html GPL 3
 * @version		0.8
 * @link		http://www.statedecoded.com/
 * @since		0.8
 *
 */

require_once(INCLUDE_PATH . '/disqusapi/disqusapi.php');

class CommentController extends BaseController
{
	public $disqus;
	public $per_page = 25;

	public function __construct()
	{
		parent::__construct();
		$this->disqus = new DisqusAPI(DISQUS_API_SECRET);
	}

	public function handle($args)
	{
		if(isset($args[1]) && strlen($args[1]))
		{
			$fn = $args[1];
			if(substr($fn, -1, 1) == '/')
			{
				$fn = substr($fn, 0, -1);
			}

			$fn = 'handle_' . $fn;

			if(method_exists($this, $fn))
			{
				return $this->$fn($args);
			}
		}

		return $this->handle_index($args);
	}

	public function handle_index($args)
	{
		$content = new Content();

		$content->set('page_title', 'ReimagineSF');
		$content->set('body_class', 'inside comments-page');
		$content->set('content_class', 'nest wide');


		$content->set('body', '<article id="comments">');
		$content->append('body', '
			<p>
			    San Francisco-area students: how would you change the law. Win $1,000. Make an impact.
			</p>
			<p>

			</p>
			<p>
				<div class="video-wrapper">
			    	<iframe src="//player.vimeo.com/video/91737479" width="500" height="282" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
			    </div>
			    <p><a href="http://vimeo.com/91737479">Reimagine SF</a> from <a href="http://vimeo.com/supfarrell">Supervisor Mark Farrell</a> on <a href="https://vimeo.com">Vimeo</a>.</p>
			</p>
			<p>

			</p>
			<p>
			    How would you improve the laws of San Francisco?  This is your chance to redesign the rules of the city, and earn a shot at a $1,000 scholarship.  Here\'s how it works:
			</p>
			<ol class="comment-instructions">
				<li>
					<i class="fa fa-lightbulb-o"></i>
				    Imagine A Better San Francisco:
				    Think about what you like best - and like least - about living in San Francisco.  Odds are, there\'s a law to match.  How would you fix what you don\'t like, or build on what you do?  Sky\'s the limit.
				</li>
				<li>
					<i class="fa fa-search"></i>
				    Find the Laws You Care About:
				    Now that you have a vision for improving city life, visit
				    <a class="c5" href="http://sanfranciscocode.org/">SanFranciscoCode.org</a>.  Browse and search through the laws to find what you care about most.
				</li>
				<li>
					<i class="fa fa-comments-o"></i>
				    Read, React, ReimagineSF:
				    Read the laws.  Decide how you\'d improve them.  Post your changes to each law right at the bottom of the page using Disqus.  Your ideas will go straight to the Board of Supervisors for action.  The top suggestions will be collected right here.
				</li>
				<li>
					<i class="fa fa-share-square-o"></i>
				    Tell Your Friends to Upvote:
				     Share a link to your ideas with your friends, family, and the world.  Ask them to upvote your idea, or leave a comment in support.  That makes your idea float to the top, which is where you want to be to win a $1,000 scholarship.
				</li>
			</ol>
			<p>
			    San Francisco Supervisor Mark Farrell will select the 5 best ideas, turn each into actual legislation, then put them to the Board of Supervisors for a vote.  Oh, and those 5 student-legislators will each earn $1,000 for school.  Make an impact.  Win a scholarship.  ReimagineSF.
			</p>');

		$page = 1;
		if(isset($_GET['page']))
		{
			$page = $_GET['page'];
		}

		$posts = $this->get_posts($page);

		/*
		 * Loop over posts.
		 */

		if(is_array($posts) && count($posts))
		{
			$content->append('body', '<h2>Top Comments</h2><ol class="post-list" id="post-list" start="' .
				($this->get_offset($page) + 1) .'">');

			foreach($posts as $post)
			{
				if(!$post->isFlagged && !$post->isSpam && !$post->isDeleted)
				{
					$content->append('body', '<li class="post" id="post-' . $post->id  .'">
						<div class="post-content">
							<div class="avatar hovercard">
								<a class="user" href="' . $post->author->profileUrl .
									'" title="profile page for ' . $post->author->name .
									'"><img alt="Avatar for ' . $post->author->name .
									'" src="' .$post->author->avatar->small->cache .'"/></a>
							</div>
							<div class="post-body">
								<header>
									<div class="post-title">
										<span class="title">
											<a href="'. $post->thread->link . '#disqus_thread">' .
												$post->thread->clean_title . '</a>
										</span>
									</div>
									<div class="post-credits">
										<span class="post-byline">
											<span class="author publisher-anchor-color">
												<a href="' . $post->thread->link. '#comment-' . $post->id . '">' . $post->author->name . '</a>
											</span>
											<span class="post-meta">
												<span class="bullet time-ago-bullet" aria-hidden="true">•</span>
												<a class="time-ago" href="' . $post->thread->link. '#comment-' . $post->id . '" title="' .
													$post->date . '">' . date('g:ia M j Y', strtotime($post->createdAt)) . '</a>
											</span>
										</span>
									</div>
								</header>
								<div class="post-body-inner">
									<div class="post-message-container">
										<div class="publisher-anchor-color">
											<div class="post-message">
												' . $post->message . '
											</div>
										</div>
									</div>
								</div>
								<footer>
									<menu>
										<ul>
											<li class="voting" data-role="voting">
												<span class="vote-up">
													<span class="count">' . $post->likes . '</span>
													<span class="control"><i aria-hidden="true" class="icon icon-angle-up"></i></span>
												</span>

												<span class="vote-down" title="Vote down">
													<span class="count">' . $post->dislikes . '</span>
													<span class="control"><i aria-hidden="true" class="icon icon-angle-down"></i></span>
												</span>
											</li>
											<!-- No other actions at this time. -->
										</ul>
									</menu>
								</footer>
							</div>
						</div>
					</li>');
				}
			}
			$content->append('body', '</ul>');
		}
		else {
			$content->append('No further posts.');
		}

		$nav = '';

		if($page > 1)
		{
			$nav .= '<li><a href="?page=' . ($page - 1) . '"><i class="icon icon-angle-left"></i> Older</a></li>';
		}
		// We have to fetch the next page to know if there *is* a next page.

		if(count($this->get_posts($page + 1)) > 0)
		{
			$nav .= '<li><a href="?page=' . ($page + 1) . '">Newer <i class="icon icon-angle-right"></i></a></li>';
		}

		if(strlen($nav) > 0)
		{
			$content->append('body', '<menu class="comment-nav"><ul>' .  $nav . '</ul></menu>');
		}

		$content->append('body', '<p>
			    <strong>Need Help?  Questions?  Suggestions?</strong>
			</p>
			<p>
			    If you think something is broken or missing on the site, click the "Feedback &amp; Support" tab on the left side of every page.
			    We\'ll get back to you right away.  For everything else,
			    <a class="c5" href="mailto:sayhello@opengovfoundation.org">send us an email at sayhello@opengovfoundation.org</a>.
			</p>
			<p>
			    <strong>About ReimagineSF</strong>
			</p>
			<p>
			    San Francisco-area students have a stake in our city but too often don\'t have a voice.  Together, we can fix that.  Just like SF resident Gary Rabkin
			    <a class="c5" href="http://www.sfgate.com/bayarea/article/A-push-to-abolish-ridiculous-S-F-laws-5119134.php">fixed a dumb bike law</a>..  Giving young San Francisco residents the chance to have a say in city government is what ReimagineSF is all about.  The ReimagineSF Civic Engagement Scholarship is brought to you by
			    <a class="c5" href="http://www.sfbos.org/index.aspx?page=11323">Supervisor Mark Farrell</a> and
			    <a class="c5" href="http://opengovfoundation.org">The OpenGov Foundation</a>.   Please read the <a href="terms/">contest rules, terms and conditions</a>.
			</p>
			<p>

			</p>
			<p>
			    <strong>About SanFranciscoCode.org</strong>
			</p>
			<p>
			    <a class="c5" href="http://sanfranciscocode.org">SanFranciscoCode.org</a>, part of the
			    <a class="c5" href="http://americadecoded.org">America Decoded</a> network, is the
			    first Internet-Age edition of the city\'s municipal code, running 100%
			    <a class="c5" href="https://github.com/opengovfoundation/sanfranciscocode">open-source software</a>
				and fueled by 100%
			    <a class="c5" href="http://dev.sanfranciscocode.org/downloads/">open law data</a>.
			    <a class="c5" href="http://dev.sanfranciscocode.org/about/">Learn more</a>.
			</p>');

		$content->append('body', '</article>');

		$this->render($content);
	}

	public function get_offset($page)
	{
		return ($page - 1) * $this->per_page;
	}


	public function get_posts($page = 1)
	{
		$offset = $this->get_offset($page);

		/*
		 * Get posts.
		 */
		$post_args = array(
			'forum' => DISQUS_SHORTNAME,
			'interval' => '90d',
			'limit' => $this->per_page + $offset, // This is really weird, @Disqus.
			'offset' => $offset
		);

		$posts = $this->disqus->posts->listPopular($post_args);

		if(is_array($posts) && count($posts) > 0)
		{
			/*
			 * We need to get the threads for those posts, since Disqus
			 * does not include that data.
			 */
			// TODO: Handle more than 100 using pagination.

			$thread_ids = array();
			foreach($posts as $post)
			{
				$thread_ids[] = $post->thread;
			}

			$thread_args = array(
				'forum' => DISQUS_SHORTNAME,
				'thread' => $thread_ids,
				'limit' => 100
			);

			$temp_threads = $this->disqus->threads->list($thread_args);
			$threads = array();

			/*
			 * Shuffle those into a hash.
			 */

			foreach($temp_threads as $thread)
			{
				$threads[$thread->id] = $thread;
			}
			unset($temp_threads);

			/*
			 * Re-set threads.
			 */
			foreach($posts as $key=>$post)
			{
				$post->thread = $threads[$post->thread];
				$posts[$key] = $post;
			}

			return $posts;
		}
		else
		{
			return array();
		}

	}

	public function handle_terms($args)
	{
		$content = new Content();

		$content->set('page_title', 'ReimagineSF Scholarship Contest Rules, Terms &amp; Conditions');
		$content->set('body_class', 'inside comments-page');
		$content->set('content_class', 'nest wide');


		$content->set('body', '<article id="comments">
				<p>NO PURCHASE NECESSARY TO ENTER OR TO WIN. </p>

				<h3>Eligibility</h3>

				<p>Contest open only to undergraduate and graduate
				students enrolled in a San Francisco, CA-based two-year, four-year or technical
				degree program who are age 18 or over.
				The winner may be asked to provide proof of date of birth, proof of
				enrollment, and identification. By participating in this Contest, each entrant
				agrees to be bound by these Official Rules. VOID WHERE PROHIBITED.</p>

				<h3>Promotion
				Period</h3>

				<p>The Contest is open from April 14, 2014 to May
				14, 2014. Entrants must submit their suggested changes to the San Francisco
				Municipal Code, using the Disqus platform on SanFranciscoCode.org, no later
				than 11:59 PM PST on May 14, 2014 to be considered.  Sponsor is not responsible for unsuccessful attempts to
				enter. Sponsor\'s computer is the official clock for this Contest. To be
				eligible for the drawing, entries must be posted to SanFranciscoCode.org only
				during the Promotion Period. There is no limit on number of entries.</p>

				<h3>How to
				Enter</h3>

				<p>Each entrant must complete at least one suggested
				change to a section of the laws of San Francisco as presented on
				SanFranciscoCode.org, using the Disqus commenting feature located at the bottom
				of each web page.  After posting each
				suggested change to the law, the entrant must then solicit upvotes, shares and
				discussion from other website users, friends and family.  Winners will be selected by San
				Francisco Supervisor Mark Farrell based on the amount of discussion and upvotes
				each submission receives.  Any use
				of robotic, automatic, macro, programmed or like entry methods, will disqualify
				all entries by such methods. All Disqus comments are covered by the <a
				href="http://help.disqus.com/customer/portal/topics/215159-terms-and-policies/articles">Disqus Terms and Policies</a>.
				Entries that are obtained through unauthorized sources are void. </p>

				<h3>Winning</h3>

				<p>The 5 $1,000
				scholarship winners will be selected no later than May 21, 2014 by San
				Francisco Supervisor Mark Farrell.
				Winning submissions will also be considered by Farrell
				for formal introduction to the Board of Supervisors as legislation.  </p>

				<p>The winner will be notified no later than May
				31st, and will be notified via their Disqus account email address.  </p>

				<p>The prize winner is solely responsible for all
				taxes, fees, surcharges and shipping on any prize received, including federal
				and state income taxes, and may be asked to furnish the Sponsor with his/her
				social security number. </p>

				<h3>Prize/Value</h3>

				<p>The prizes are 5 USD $1,000 tuition
				scholarships.  The total value of
				the prize is $1,000. Payment will be made by Sponsor directly to the winning
				studentÕs educational institution, and may not be used for any purpose other
				than to help cover the cost of the winnerÕs education, and may not be granted
				in any other form such as cash or check and may not be transferred by the
				winner to another person. The odds of winning depend on the number of
				entrants.  </p>

				<h3>General
				Conditions</h3>

				<p>All federal, state, and local laws and
				regulations apply. Except where prohibited by law, winner grants permission to
				Sponsor, and those acting under Sponsor\'s authority the right to the use of
				his/her name, picture, likeness, voice, biographical information and
				statements, at any time or times, for advertising, trade, publicity and
				promotional purposes without additional compensation, in all media now known or
				hereafter discovered, worldwide and on the Internet and World Wide Web, without
				notice, review or approval. No substitution of prize permitted by winner. Prize
				is not for resale, cannot be redeemed for cash, is not transferable or
				exchangeable, and cannot be used in conjunction with any discount, premium or
				rebate offer. Sponsor reserves the right to substitute the prize(s) with
				prize(s) of equal or greater value if prize is unavailable. If, for any reason,
				the Contest is not capable of running as intended by Sponsor due to, without
				limitation, errors in these Official Rules or advertising for this Contest,
				tampering, unauthorized intervention, fraud, technical failures, human error or
				any other cause beyond the control of Sponsor that, in the sole judgment of
				Sponsor, could corrupt or affect the administration, security, fairness,
				integrity or proper conduct of this Contest, Sponsor reserves the right, at its
				sole discretion, to cancel, terminate, modify or suspend the Contest. SponsorÕs
				decisions are final. No correspondence will be entered into.</p>

				<h3>Liability
				Limitations</h3>

				<p>Sponsor is not responsible for interrupted or
				unavailable satellite, network, server, Internet Service Provider (ISP),
				website, telephone or other connections, availability or accessibility, or
				miscommunications, or failed computer, satellite, telephone or cable
				transmissions, or lines, or technical failure or jumbled, corrupted, scrambled,
				delayed, or misdirected transmissions or computer hardware or software
				malfunctions, failures, or technical errors or difficulties, or other errors of
				any kind whether human, mechanical, electronic or network or the incorrect or
				inaccurate capture of entry or other information or the failure to capture, or
				loss of, any such information. Persons who tamper with or abuse any aspect of
				this Contest, as solely determined by the Sponsors, will be disqualified.
				Should any portion of the Contest be, in Sponsor\'s sole opinion, compromised by
				virus, worms, bugs, non-authorized human intervention, technical failures or
				other causes which, in the sole opinion of the Sponsor, corrupt or impair the
				administration, security, fairness or proper play, or submission of entries,
				Sponsor reserves the right at its sole discretion to suspend, modify or
				terminate the Contest, and select the winners from entries received prior to
				action taken or as otherwise deemed fair and appropriate by the Sponsors.
				Neither Sponsor nor its respective agencies are responsible for any incorrect
				or inaccurate information, whether caused by website users, tampering, hacking,
				or by any equipment or programming associated with or utilized in the Contest,
				and assume no responsibility for any error, interruption, deletion, defect,
				delay in operation, or transmission, communications line failure, theft or
				destruction, or unauthorized access to or use of the entry webpage</p>

				<p>IN NO EVENT WILL SPONSOR, THE OPENGOV FOUNDATION,
				AFFILIATES, SUBSIDIARIES AND RELATED COMPANIES, THEIR RESPECTIVE ADVERTISING OR
				PROMOTION AGENCIES OR ANY OF THEIR RESPECTIVE OFFICERS, DIRECTORS, EMPLOYEES,
				REPRESENTATIVES AND AGENTS BE RESPONSIBLE OR LIABLE FOR (AND ARE HEREBY
				RELEASED WITH RESPECT TO) ANY DAMAGES OR LOSSES OF ANY KIND, INCLUDING DIRECT,
				INDIRECT, INCIDENTAL, CONSEQUENTIAL, PUNITIVE DAMAGES, PROPERTY DAMAGE OR
				PERSONAL INJURY/DEATH ARISING OUT OF THIS CONTEST, USE/ACCEPTANCE OF THE PRIZE,
				AND CONTEST-RELATED ACTIVITIES. SOME JURISDICTIONS MAY NOT ALLOW LIMITATIONS OR
				EXCLUSION OF LIABILITY FOR INCIDENTAL OR CONSEQUENTIAL DAMAGES OR EXCLUSION OF
				IMPLIED WARRANTIES, SO SOME OF THE ABOVE LIMITATIONS OR EXCLUSIONS MAY NOT
				APPLY TO YOU. CHECK YOUR LOCAL LAWS FOR ANY RESTRICTIONS OR LIMITATIONS
				REGARDING THESE LIMITATIONS OR EXCLUSIONS.</p>

				<p>By entering, entrants agree at all times to
				defend, indemnify, release and hold harmless Sponsor, The OpenGov Foundation,
				employees of The OpenGov Foundation and its parent, subsidiaries and affiliated
				companies, advertising and promotion agencies, and all of their respective
				officers, directors, employees, representatives and agents from and against any
				and all claims, actions, liabilities, injuries, death, accidents, losses or
				damages of any kind resulting directly or indirectly from any and all activity
				related to entering and/or participating in this Contest or from the acceptance
				or use of prize. Sponsor reserves the right to disqualify, seek remedies and
				damages from, and criminally prosecute any individual or entrant tampering with
				the entry process.</p>

				<p>This Contest is
				jointly operated by The OpenGov Foundation and the Office of San Francisco
				Supervisor Mark Farrell.</p>

				<p>By entering this Contest, each entrant agrees that
				he/she may receive correspondence from The OpenGov Foundation. For information
				about The OpenGov FoundationÕs policies regarding SanFranciscoCode.org, <a
				href="http://www.sanfranciscocode.org/about/">please read the about page</a>. </p>

				<h3>Opt-In </h3>

				<p>By entering the ReimagineSF
				Scholarship Contest, you are opting-in to receive communications regarding the
				OpenGov Foundation, SanFranciscoCode.org, and other open government
				initiatives.</p>

				<h3>Non-Profit Exemptions</h3>

				<p>OpenGov is not for profit
				and organized under section 501(c)3 of the U.S.
				Internal Revenue Code.  That means
				we must comply with certain U.S. Government policies <a
				href="http://www.irs.gov/Charities-&amp;-Non-Profits/Charitable-Organizations/Exemption-Requirements-Section-501(c)(3)-Organizations">helpfully explained here by the U.S. Internal Revenue Service</a> and <a
				href="http://en.wikipedia.org/wiki/501(c)_organization#501.28c.29.283.29">here by Wikipedians</a>.  One policy is a prohibition on advocating for or against
				candidates for public office.  We
				will make a good faith effort to ensure content on OpenGov websites is
				compliant.  </p>

				<p>Please email questions,
				comments or concerns to The OpenGov Foundation at: <a
				href="mailto:sayhello@opengovfoundation.org">sayhello@opengovfoundation.org</a> or <a href="mailto:jess.montejano@sfgov.org">jess.montejano@sfgov.org</a>. </p>

				<h3>Last Updated: April 9, 2014</h3>
			</article>');

		$this->render($content);
	}

}
