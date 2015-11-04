<?php
/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2015 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */
namespace Opulence\Databases\Pdo\MySql;

use Opulence\Databases\Pdo\Driver as BaseDriver;
use Opulence\Databases\Providers\MySqlProvider;
use Opulence\Databases\Server;

/**
 * Defines the PDO driver for a MySQL database
 */
class Driver extends BaseDriver
{
    /**
     * @inheritdoc
     */
    protected function getDsn(Server $server, array $options = [])
    {
        $dsn = implode(";", [
                "mysql:host=" . $server->getHost(),
                "dbname=" . $server->getDatabaseName(),
                "port=" . $server->getPort(),
                "charset=" . $server->getCharset()
            ]) . ";";

        if (isset($options["unix_socket"])) {
            $dsn .= "unix_socket=" . $options["unix_socket"] . ";";
        }

        return $dsn;
    }

    /**
     * @inheritdoc
     */
    protected function setProvider()
    {
        $this->provider = new MySqlProvider();
    }
} 