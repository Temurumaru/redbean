<?php

namespace ThreadBeanPHP;

/**
 * Database Cursor Interface.
 * A cursor is used by Query Writers to fetch Query Result rows
 * one row at a time. This is useful if you expect the result set to
 * be quite large. This interface describes the API of a database
 * cursor. There can be multiple implementations of the Cursor,
 * by default ThreadBeanPHP offers the PDOCursor for drivers shipping
 * with ThreadBeanPHP and the NULLCursor.
 *
 * @file    ThreadBeanPHP/Cursor.php
 * @author  Gabor de Mooij and the ThreadBeanPHP Community
 * @license BSD/GPLv2
 *
 * @copyright
 * copyright (c) G.J.G.T. (Gabor) de Mooij and the ThreadBeanPHP Community
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
interface Cursor
{
	/**
	 * Should retrieve the next row of the result set.
	 * This method is used to iterate over the result set.
	 *
	 * @return array|NULL
	 */
	public function getNextItem();

	/**
	 * Resets the cursor by closing it and re-executing the statement.
	 * This reloads fresh data from the database for the whole collection.
	 *
	 * @return void
	 */
	public function reset();

	/**
	 * Closes the database cursor.
	 * Some databases require a cursor to be closed before executing
	 * another statement/opening a new cursor.
	 *
	 * @return void
	 */
	public function close();
}
