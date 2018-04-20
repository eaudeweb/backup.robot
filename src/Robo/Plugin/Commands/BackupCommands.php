<?php

namespace Robo\Plugin\Commands;

use EauDeWeb\Backup\Configuration\Configuration;

class BackupCommands extends \Robo\Tasks {
    const VERSION = "0.0.1";

    /**
     * @command backup:status
     * @throws \Exception
     *   When trying to initialize the backup framework
     */
    public function status() {
        echo sprintf("Backup rObOt v.%s - Crafted with ♥ at www.eaudeweb.ro\n", self::VERSION);
        $config = Configuration::get();
        $dbServers = $config->getMySQLServers();
        echo "\nMySQL databases:\n";
        foreach ($dbServers as $k => $server) {
          echo sprintf("    - {$k} (%s@%s)\n", $server['user'], $server['host']);
        }

        $this->taskBowerInstall()
    }
}
