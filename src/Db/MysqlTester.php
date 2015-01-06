<?php

namespace eecli\Db;

class MysqlTester extends ConnectionTester
{
    /**
     * {@inheritdoc}
     */
    public function test()
    {
        $connection = mysql_connect($this->hostname, $this->username, $this->password);

        if ($connection === false) {
            $this->setError(mysql_error());

            return false;
        }

        $db = mysql_select_db($this->database, $connection);

        if ($db === false) {
            $this->setError(mysql_error());

            return false;
        }

        mysql_close($connection);

        return true;
    }
}
