<?php

namespace ThreadBeanPHP\Cursor;

use ThreadBeanPHP\Cursor as Cursor;

/**
 * PDO Database Cursor
 * Implementation of PDO Database Cursor.
 * Used by the BeanCollection to fetch one bean at a time.
 * The PDO Cursor is used by Query Writers to support retrieval
 * of large bean collections. For instance, this class is used to
 * implement the findCollection()/BeanCollection functionality.
 *
 * @file    ThreadBeanPHP/Cursor/PDOCursor.php
 * @author  Gabor de Mooij and the ThreadBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) G.J.G.T. (Gabor) de Mooij and the ThreadBeanPHP Community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class PDOCursor implements Cursor
{
	/**
	 * @var \PDOStatement
	 */
	protected $res;

	/**
	 * @var string
	 */
	protected $fetchStyle;

	/**
	 * Constructor, creates a new instance of a PDO Database Cursor.
	 *
	 * @param \PDOStatement $res        the PDO statement
	 * @param string       $fetchStyle fetch style constant to use
	 *
	 * @return void
	 */
	public function __construct( \PDOStatement $res, $fetchStyle )
	{
		$this->res = $res;
		$this->fetchStyle = $fetchStyle;
	}

	/**
	 * @see Cursor::getNextItem
	 */
	public function getNextItem()
	{
		return $this->res->fetch();
	}

	/**
	 * @see Cursor::reset
	 */
	public function reset()
	{
		$this->close();
		$this->res->execute();
	}

	/**
	 * @see Cursor::close
	 */
	public function close()
	{
		$this->res->closeCursor();
	}
}
