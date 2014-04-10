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
		$content = new Content();

		$content->set('page_title', 'ReimagineSF');
		$content->set('body_class', 'inside comments-page');
		$content->set('content_class', 'nest wide');


		$content->set('body', '<article id="comments">');
		$content->append('body', '
			<p>
			    Change the law. Win $1,000. Make an impact.
			</p>
			<p>

			</p>
			<p>
			    [Embed Supervisor Farrell "pitch" video]
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
					$content->append('body', '<li class="post" id="post- ' . $post->id  .' ">
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
			    <a class="c5" href="http://opengovfoundation.org">The OpenGov Foundation</a>.   Please read the contest rules, terms and conditions.
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

}
