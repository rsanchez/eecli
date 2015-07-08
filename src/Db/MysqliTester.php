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
        $connection = @mysqli_connect($this->hostname, $this->username, $this->password, $this->database);

        if ($connection === false) {
            $error = mysqli_connect_error();

            if ($error === 'No such file or directory') {
                $error = sprintf('Could not connect to socket %s', @ini_get('mysqli.default_socket'));
            }

            $this->setError($error);

            return false;
        }

        @mysqli_close($connection);

        return true;
    }
}
