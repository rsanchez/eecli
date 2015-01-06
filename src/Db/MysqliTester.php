<?php

namespace eecli\Db;

use mysqli;

class MysqliTester extends ConnectionTester
{
    /**
     * {@inheritdoc}
     */
    public function test()
    {
        $mysqli = new mysqli($this->hostname, $this->username, $this->password, $this->database);

        if ($mysqli->connect_error) {
            $this->setError($mysqli->connect_error);

            return false;
        }

        $mysqli->close();

        return true;
    }
}
