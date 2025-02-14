<?php

namespace ThreadBeanPHP\Adapter;

use ThreadBeanPHP\Observable as Observable;
use ThreadBeanPHP\Adapter as Adapter;
use ThreadBeanPHP\Driver as Driver;

/**
 * DBAdapter (Database Adapter)
 *
 * An adapter class to connect various database systems to ThreadBean
 * Database Adapter Class. The task of the database adapter class is to
 * communicate with the database driver. You can use all sorts of database
 * drivers with ThreadBeanPHP. The default database drivers that ships with
 * the ThreadBeanPHP library is the RPDO driver ( which uses the PHP Data Objects
 * Architecture aka PDO ).
 *
 * @file    ThreadBeanPHP/Adapter/DBAdapter.php
 * @author  Gabor de Mooij and the ThreadBeanPHP Community.
 * @license BSD/GPLv2
 *
 * @copyright
 * (c) copyright G.J.G.T. (Gabor) de Mooij and the ThreadBeanPHP community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class DBAdapter extends Observable implements Adapter
{
	/**
	 * @var Driver
	 */
	private $db = NULL;

	/**
	 * @var string
	 */
	private $sql = '';

	/**
	 * Constructor.
	 *
	 * Creates an instance of the ThreadBean Adapter Class.
	 * This class provides an interface for ThreadBean to work
	 * with ADO compatible DB instances.
	 *
	 * Usage:
	 *
	 * <code>
	 * $database = new RPDO( $dsn, $user, $pass );
	 * $adapter = new DBAdapter( $database );
	 * $writer = new PostgresWriter( $adapter );
	 * $oodb = new OODB( $writer, FALSE );
	 * $bean = $oodb->dispense( 'bean' );
	 * $bean->name = 'coffeeBean';
	 * $id = $oodb->store( $bean );
	 * $bean = $oodb->load( 'bean', $id );
	 * </code>
	 *
	 * The example above creates the 3 ThreadBeanPHP core objects:
	 * the Adapter, the Query Writer and the OODB instance and
	 * wires them together. The example also demonstrates some of
	 * the methods that can be used with OODB, as you see, they
	 * closely resemble their facade counterparts.
	 *
	 * The wiring process: create an RPDO instance using your database
	 * connection parameters. Create a database adapter from the RPDO
	 * object and pass that to the constructor of the writer. Next,
	 * create an OODB instance from the writer. Now you have an OODB
	 * object.
	 *
	 * @param Driver $database ADO Compatible DB Instance
	 */
	public function __construct( $database )
	{
		$this->db = $database;
	}

	/**
	 * Returns a string containing the most recent SQL query
	 * processed by the database adapter, thus conforming to the
	 * interface:
	 *
	 * @see Adapter::getSQL
	 *
	 * Methods like get(), getRow() and exec() cause this SQL cache
	 * to get filled. If no SQL query has been processed yet this function
	 * will return an empty string.
	 *
	 * @return string
	 */
	public function getSQL()
	{
		return $this->sql;
	}

	/**
	 * @see Adapter::exec
	 */
	public function exec( $sql, $bindings = array(), $noevent = FALSE )
	{
		if ( !$noevent ) {
			$this->sql = $sql;
			$this->signal( 'sql_exec', $this );
		}

		return $this->db->Execute( $sql, $bindings );
	}

	/**
	 * @see Adapter::get
	 */
	public function get( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetAll( $sql, $bindings );
	}

	/**
	 * @see Adapter::getRow
	 */
	public function getRow( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetRow( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCol
	 */
	public function getCol( $sql, $bindings = array() )
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetCol( $sql, $bindings );
	}

	/**
	 * @see Adapter::getAssoc
	 */
	public function getAssoc( $sql, $bindings = array() )
	{
		$this->sql = $sql;

		$this->signal( 'sql_exec', $this );

		$rows  = $this->db->GetAll( $sql, $bindings );

		if ( !$rows ) return array();

		$assoc = array();

		foreach ( $rows as $row ) {
			if ( empty( $row ) ) continue;

			$key   = array_shift( $row );
			switch ( count( $row ) ) {
				case 0:
					$value = $key;
					break;
				case 1:
					$value = reset( $row );
					break;
				default:
					$value = $row;
			}

			$assoc[$key] = $value;
		}

		return $assoc;
	}

	/**
	 * @see Adapter::getAssocRow
	 */
	public function getAssocRow($sql, $bindings = array())
	{
		$this->sql = $sql;
		$this->signal( 'sql_exec', $this );

		return $this->db->GetAssocRow( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCell
	 */
	public function getCell( $sql, $bindings = array(), $noSignal = NULL )
	{
		$this->sql = $sql;

		if ( !$noSignal ) $this->signal( 'sql_exec', $this );

		return $this->db->GetOne( $sql, $bindings );
	}

	/**
	 * @see Adapter::getCursor
	 */
	public function getCursor( $sql, $bindings = array() )
	{
		return $this->db->GetCursor( $sql, $bindings );
	}

	/**
	 * @see Adapter::getInsertID
	 */
	public function getInsertID()
	{
		return $this->db->getInsertID();
	}

	/**
	 * @see Adapter::getAffectedRows
	 */
	public function getAffectedRows()
	{
		return $this->db->Affected_Rows();
	}

	/**
	 * @see Adapter::getDatabase
	 */
	public function getDatabase()
	{
		return $this->db;
	}

	/**
	 * @see Adapter::startTransaction
	 */
	public function startTransaction()
	{
		$this->db->StartTrans();
	}

	/**
	 * @see Adapter::commit
	 */
	public function commit()
	{
		$this->db->CommitTrans();
	}

	/**
	 * @see Adapter::rollback
	 */
	public function rollback()
	{
		$this->db->FailTrans();
	}

	/**
	 * @see Adapter::close.
	 */
	public function close()
	{
		$this->db->close();
	}

	/**
	 * Sets initialization code for connection.
	 *
	 * @param callable $code
	 */
	public function setInitCode($code) {
		$this->db->setInitCode($code);
	}

	/**
	 * @see Adapter::setOption
	 */
	public function setOption( $optionKey, $optionValue ) {
		if ( method_exists( $this->db, $optionKey ) ) {
			call_user_func( array( $this->db, $optionKey ), $optionValue );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @see Adapter::getDatabaseServerVersion
	 */
	public function getDatabaseServerVersion()
	{
		return $this->db->DatabaseServerVersion();
	}
}
