# Backup.Robot

Use backup robot for regular backup schedules. Features:

- MySQL - backup MySQL/MariaDB databases. Supports multiple servers, one file per database. Automatically backups new databases, blacklist unwanted databases. 

## Prerequisites

Install on a target server the following components:

- PHP >= 5.6 (ex. ` sudo apt-get install php php-cli php-curl php-dev php-gd  php-pear  php-gettext  php-http  php-xdebug  php-xml  php-zip  php-zip php-mbstring php-mysqli`)
- Composer (through system package manager or preferable via https://getcomposer.org/download/)
- MySQL client libraries for MySQL backup (`apt-get install mariadb-client`)

## Setup

1. Checkout this project on a target server (i.e. `git clone https://github.com/eaudeweb/backup.robot.git /opt/backup`) with a regular user account
2. Copy `conf/backup.config.example.yml` to `conf/backup.config.yml` and restrict file permissions (i.e. `chown root:root conf/backup.config.yml && chmod 600 conf/backup.config.yml`)
3. Customize configuration
4. Start a backup using command: `./vendor/bin/robo backup:backup`


## Useful commands


1. `backup:status` - Overview of backup tasks


```
$> ./vendor/bin/robo backup:status

➜  Backup rObOt v.0.0.1 - Crafted with ♥ at www.eaudeweb.ro
➜  ========== Backup summary ==========
➜  MySQL databases:
➜      - mara (root@127.0.0.1)
➜          - Connected successfully (mysqli loaded)
➜          - Backup to /home/cristiroma/work/infrastructure/backup.robot/data/PROJECT1/
➜          - Backup writable: YES
➜          - Backup databases: [holcim_eholcim, iucn_who, mysql, wildlex]

```

1. `backup:prepare` - Trigger preparation for backup tasks

Preparation is also triggered as part of the backup process, but this command triggers separately just the preparation.

**Important notes for preparation:**
- If preparation fails the backup will not run for the respective task;
- For MySQL preparation means ensuring that destination directories where the dumps are made exists and they are writable.

```
$> ./vendor/bin/robo backup:status
```
