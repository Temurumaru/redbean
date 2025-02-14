<?php

namespace ThreadBeanPHP;

use ThreadBeanPHP\ToolBox as ToolBox;
use ThreadBeanPHP\AssociationManager as AssociationManager;
use ThreadBeanPHP\OODBBean as OODBBean;

/**
 * ThreadBeanPHP Tag Manager.
 *
 * The tag manager offers an easy way to quickly implement basic tagging
 * functionality.
 *
 * Provides methods to tag beans and perform tag-based searches in the
 * bean database.
 *
 * @file       ThreadBeanPHP/TagManager.php
 * @author     Gabor de Mooij and the ThreadBeanPHP community
 * @license    BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the ThreadBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class TagManager
{
	/**
	 * @var ToolBox
	 */
	protected $toolbox;

	/**
	 * @var AssociationManager
	 */
	protected $associationManager;

	/**
	 * @var OODB
	 */
	protected $redbean;

	/**
	 * Checks if the argument is a comma separated string, in this case
	 * it will split the string into words and return an array instead.
	 * In case of an array the argument will be returned 'as is'.
	 *
	 * @param array|string|false $tagList list of tags
	 *
	 * @return array
	 */
	private function extractTagsIfNeeded( $tagList )
	{
		if ( $tagList !== FALSE && !is_array( $tagList ) ) {
			$tags = explode( ',', (string) $tagList );
		} else {
			$tags = $tagList;
		}

		return $tags;
	}

	/**
	 * Finds a tag bean by its title.
	 * Internal method.
	 *
	 * @param string $title title to search for
	 *
	 * @return OODBBean|NULL
	 */
	protected function findTagByTitle( $title )
	{
		$beans = $this->redbean->find( 'tag', array( 'title' => array( $title ) ) );

		if ( $beans ) {
			$bean = reset( $beans );

			return $bean;
		}

		return NULL;
	}

	/**
	 * Constructor.
	 * The tag manager offers an easy way to quickly implement basic tagging
	 * functionality.
	 *
	 * @param ToolBox $toolbox toolbox object
	 */
	public function __construct( ToolBox $toolbox )
	{
		$this->toolbox = $toolbox;
		$this->redbean = $toolbox->getThreadBean();

		$this->associationManager = $this->redbean->getAssociationManager();
	}

	/**
	 * Tests whether a bean has been associated with one or more
	 * of the listed tags. If the third parameter is TRUE this method
	 * will return TRUE only if all tags that have been specified are indeed
	 * associated with the given bean, otherwise FALSE.
	 * If the third parameter is FALSE this
	 * method will return TRUE if one of the tags matches, FALSE if none
	 * match.
	 *
	 * Tag list can be either an array with tag names or a comma separated list
	 * of tag names.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::hasTag( $blog, 'horror,movie', TRUE );
	 * </code>
	 *
	 * The example above returns TRUE if the $blog bean has been tagged
	 * as BOTH horror and movie. If the post has only been tagged as 'movie'
	 * or 'horror' this operation will return FALSE because the third parameter
	 * has been set to TRUE.
	 *
	 * @param  OODBBean     $bean bean to check for tags
	 * @param  array|string $tags list of tags
	 * @param  boolean      $all  whether they must all match or just some
	 *
	 * @return boolean
	 */
	public function hasTag( $bean, $tags, $all = FALSE )
	{
		$foundtags = $this->tag( $bean );

		$tags = $this->extractTagsIfNeeded( $tags );
		$same = array_intersect( $tags, $foundtags );

		if ( $all ) {
			return ( implode( ',', $same ) === implode( ',', $tags ) );
		}

		return (bool) ( count( $same ) > 0 );
	}

	/**
	 * Removes all specified tags from the bean. The tags specified in
	 * the second parameter will no longer be associated with the bean.
	 *
	 * Tag list can be either an array with tag names or a comma separated list
	 * of tag names.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::untag( $blog, 'smart,interesting' );
	 * </code>
	 *
	 * In the example above, the $blog bean will no longer
	 * be associated with the tags 'smart' and 'interesting'.
	 *
	 * @param  OODBBean     $bean    tagged bean
	 * @param  array|string $tagList list of tags (names)
	 *
	 * @return void
	 */
	public function untag( $bean, $tagList )
	{
		$tags = $this->extractTagsIfNeeded( $tagList );

		foreach ( $tags as $tag ) {
			if ( $t = $this->findTagByTitle( $tag ) ) {
				$this->associationManager->unassociate( $bean, $t );
			}
		}
	}

	/**
	 * Part of ThreadBeanPHP Tagging API.
	 * Tags a bean or returns tags associated with a bean.
	 * If $tagList is NULL or omitted this method will return a
	 * comma separated list of tags associated with the bean provided.
	 * If $tagList is a comma separated list (string) of tags all tags will
	 * be associated with the bean.
	 * You may also pass an array instead of a string.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::tag( $meal, "TexMex,Mexican" );
	 * $tags = R::tag( $meal );
	 * </code>
	 *
	 * The first line in the example above will tag the $meal
	 * as 'TexMex' and 'Mexican Cuisine'. The second line will
	 * retrieve all tags attached to the meal object.
	 *
	 * @param OODBBean $bean    bean to tag
	 * @param mixed    $tagList tags to attach to the specified bean
	 *
	 * @return string
	 */
	public function tag( OODBBean $bean, $tagList = NULL )
	{
		if ( is_null( $tagList ) ) {

			$tags = $bean->sharedTag;
			$foundTags = array();

			foreach ( $tags as $tag ) {
				$foundTags[] = $tag->title;
			}

			return $foundTags;
		}

		$this->associationManager->clearRelations( $bean, 'tag' );
		$this->addTags( $bean, $tagList );

		return $tagList;
	}

	/**
	 * Part of ThreadBeanPHP Tagging API.
	 * Adds tags to a bean.
	 * If $tagList is a comma separated list of tags all tags will
	 * be associated with the bean.
	 * You may also pass an array instead of a string.
	 *
	 * Usage:
	 *
	 * <code>
	 * R::addTags( $blog, ["halloween"] );
	 * </code>
	 *
	 * The example adds the tag 'halloween' to the $blog
	 * bean.
	 *
	 * @param OODBBean           $bean    bean to tag
	 * @param array|string|false $tagList list of tags to add to bean
	 *
	 * @return void
	 */
	public function addTags( OODBBean $bean, $tagList )
	{
		$tags = $this->extractTagsIfNeeded( $tagList );

		if ( $tagList === FALSE ) {
			return;
		}

		foreach ( $tags as $tag ) {
			if ( !$t = $this->findTagByTitle( $tag ) ) {
				$t        = $this->redbean->dispense( 'tag' );
				$t->title = $tag;

				$this->redbean->store( $t );
			}

			$this->associationManager->associate( $bean, $t );
		}
	}

	/**
	 * Returns all beans that have been tagged with one or more
	 * of the specified tags.
	 *
	 * Tag list can be either an array with tag names or a comma separated list
	 * of tag names.
	 *
	 * Usage:
	 *
	 * <code>
	 * $watchList = R::tagged(
	 *   'movie',
	 *   'horror,gothic',
	 *   ' ORDER BY movie.title DESC LIMIT ?',
	 *   [ 10 ]
	 * );
	 * </code>
	 *
	 * The example uses R::tagged() to find all movies that have been
	 * tagged as 'horror' or 'gothic', order them by title and limit
	 * the number of movies to be returned to 10.
	 *
	 * @param string       $beanType type of bean you are looking for
	 * @param array|string $tagList  list of tags to match
	 * @param string       $sql      additional SQL (use only for pagination)
	 * @param array        $bindings bindings
	 *
	 * @return array
	 */
	public function tagged( $beanType, $tagList, $sql = '', $bindings = array() )
	{
		$tags       = $this->extractTagsIfNeeded( $tagList );
		$records    = $this->toolbox->getWriter()->queryTagged( $beanType, $tags, FALSE, $sql, $bindings );

		return $this->redbean->convertToBeans( $beanType, $records );
	}

	/**
	 * Returns all beans that have been tagged with ALL of the tags given.
	 * This method works the same as R::tagged() except that this method only returns
	 * beans that have been tagged with all the specified labels.
	 *
	 * Tag list can be either an array with tag names or a comma separated list
	 * of tag names.
	 *
	 * Usage:
	 *
	 * <code>
	 * $watchList = R::taggedAll(
	 *    'movie',
	 *    [ 'gothic', 'short' ],
	 *    ' ORDER BY movie.id DESC LIMIT ? ',
	 *    [ 4 ]
	 * );
	 * </code>
	 *
	 * The example above returns at most 4 movies (due to the LIMIT clause in the SQL
	 * Query Snippet) that have been tagged as BOTH 'short' AND 'gothic'.
	 *
	 * @param string       $beanType type of bean you are looking for
	 * @param array|string $tagList  list of tags to match
	 * @param string       $sql      additional sql snippet
	 * @param array        $bindings bindings
	 *
	 * @return array
	 */
	public function taggedAll( $beanType, $tagList, $sql = '', $bindings = array() )
	{
		$tags  = $this->extractTagsIfNeeded( $tagList );
		$records    = $this->toolbox->getWriter()->queryTagged( $beanType, $tags, TRUE, $sql, $bindings );

		return $this->redbean->convertToBeans( $beanType, $records );
	}

	/**
	 * Like taggedAll() but only counts.
	 *
	 * @see taggedAll
	 *
	 * @param string       $beanType type of bean you are looking for
	 * @param array|string $tagList  list of tags to match
	 * @param string       $sql      additional sql snippet
	 * @param array        $bindings bindings
	 *
	 * @return integer
	 */
	public function countTaggedAll( $beanType, $tagList, $sql = '', $bindings = array() )
	{
		$tags  = $this->extractTagsIfNeeded( $tagList );
		return $this->toolbox->getWriter()->queryCountTagged( $beanType, $tags, TRUE, $sql, $bindings );
	}

	/**
	 * Like tagged() but only counts.
	 *
	 * @see tagged
	 *
	 * @param string       $beanType type of bean you are looking for
	 * @param array|string $tagList  list of tags to match
	 * @param string       $sql      additional sql snippet
	 * @param array        $bindings bindings
	 *
	 * @return integer
	 */
	public function countTagged( $beanType, $tagList, $sql = '', $bindings = array() )
	{
		$tags  = $this->extractTagsIfNeeded( $tagList );
		return $this->toolbox->getWriter()->queryCountTagged( $beanType, $tags, FALSE, $sql, $bindings );
	}
}
