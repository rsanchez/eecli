<?php

namespace eecli\Db;

abstract class ConnectionTester
{
    /**
     * @var string|null
     */
    protected $error;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $database;

    public function __construct($hostname, $username, $password, $database)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * Test whether a valid DB connection can
     * be made with the specified credentials
     * @return bool
     */
    abstract public function test();

    /**
     * Set the error message
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * Get the error message
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Create a new DB tester instance depending
     * on the driver specified.
     *
     * @param  string $driver   mysql or mysqli
     * @param  string $hostname
     * @param  string $username
     * @param  string $password
     * @param  string $database
     * @return \eecli\Db\ConnectionTester
     */
    public static function create($driver, $hostname, $username, $password, $database)
    {
        switch ($driver) {
            case 'mysql':
                return new MysqlTester($hostname, $username, $password, $database);
            case 'mysqli':
                return new MysqliTester($hostname, $username, $password, $database);
        }

        throw new \Exception(sprintf('Unsupported DB driver %s', $driver));
    }
}
