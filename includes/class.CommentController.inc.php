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

	public function handle($args)
	{
		$content = new Content();

		$content->set('page_title', 'Top Comments');
		$content->set('body_class', 'inside comments-page');
		$content->set('content_class', 'nest wide');


		$content->set('body', '<article id="comments">');


		$disqus = new DisqusAPI(DISQUS_API_SECRET);

		/*
		 * Get posts.
		 */
		// TODO: Handle pagination.
		$posts = $disqus->posts->listPopular(
			array(
					'forum' => 'sanfranciscocode',
					'interval' => '90d',
					'limit' => 25
			)
		);

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

		$temp_threads = $disqus->threads->list(
			array(
				'forum' => 'sanfranciscocode',
				'thread' => $thread_ids,
				'limit' => 100
			)
		);
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
		 * Loop over posts.
		 */

		if(is_array($posts) && count($posts))
		{
			$content->append('body', '<ol class="post-list" id="post-list">');

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
											<a href="'. $threads[$post->thread]->link . '#disqus_thread">' .
												$threads[$post->thread]->clean_title . '</a>
										</span>
									</div>
									<div class="post-credits">
										<span class="post-byline">
											<span class="author publisher-anchor-color">
												<a href="' . $threads[$post->thread]->link. '#comment-' . $post->id . '">' . $post->author->name . '</a>
											</span>
											<span class="post-meta">
												<span class="bullet time-ago-bullet" aria-hidden="true">•</span>
												<a class="time-ago" href="' . $threads[$post->thread]->link. '#comment-' . $post->id . '" title="' .
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

		$content->append('body', '</article>');

		$this->render($content);
	}

}
