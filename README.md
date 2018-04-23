# Backup.Robot

Use backup robot for regular backup schedules. Features:

- MySQL - backup MySQL/MariaDB databases. Supports multiple servers, one file per database. Automatically backups new databases, blacklist unwanted databases.
- Rsync - push local dirs to destination server. Supports copying SELinux attributes on target filesystems. Can be used for rsync "pull files" if orchestrated with installation on the backup server.
- Email report - the backup process can be configured to send a backup report to an email with custom subject for failed or success backups

## Prerequisites

Install on a target server the following components:

- PHP >= 5.6 (ex. ` sudo apt-get install php php-cli php-curl php-dev php-gd  php-pear  php-gettext  php-http  php-xdebug  php-xml php-bcmath  php-zip  php-zip php-mbstring php-mysqli`)
- Composer (through system package manager or preferable via https://getcomposer.org/download/)
- MySQL client libraries for MySQL backup (`apt-get install mariadb-client`)

## Setup

1. Checkout this project on a target server (i.e. `git clone https://github.com/eaudeweb/backup.robot.git /opt/backup`) with a regular user account
2. Copy `robo.example.yml` to `robo.yml` and restrict file permissions (i.e. `chown root:root robo.yml && chmod 600 robo.yml`)
3. Customize configuration as stated in the next chapter.
4. Start a backup using command: `./vendor/bin/robo backup:backup`
5. Install a CRON job (TODO) - `backup.sh`

```
30 2 * * * /path/to/backup.sh
```

**Disclaimer: The code above is not tested in production**

## Configuration

Configuration is done in `robo.yml` file and is quite simple:

### MySQL dump configuration

You can specify several MySQL servers at once - if needed. The dumps will be done on local filesystem.

```yml
backup:
  version: "1.0"
  mysql:
    host1:
      type:
      host: 127.0.0.1
      port: 3306
      user: root
      password: secret
      destination: /path/to/dumps/host1/
      gzip: true
      blacklist: ["performance_schema", "information_schema"]
    host2:
      type:
      host: 127.0.0.1
      port: 1306
      user: root
      password: secret
      destination: /path/to/dumps/host2/
      gzip: true
      blacklist: ["performance_schema", "information_schema", "project_test"]
```

### Rsync configuration

You can specify several rsync jobs by declaring them under `rsync` key.

```yml
backup:
  version: "1.0"
  rsync:
    dir1:
      from: /path/to/source/folder1/ending/in/slash/
      to: /path/to/destination/dest1/
      user: john
      host: backup.company.com
    dir2:
      from: /path/to/source/folder2/ending/in/slash/
      to: /path/to/destination/dest2/
      user: john
      host: backup.company.com
```

### Email configuration

The `email` configuration section can be set to send an email report. The underlying library is PHPMailer. Customize
the `subject.success` and `subject.fail` to receive and customized email message.

```
  email:
    server:
      debug-level: 0 # 0 - no output (for production), 1 - client messages, 2 - client + server messages
      type: smtp # or 'mail' or 'sendmail' or 'qmail'
      host: secure.emailserver.com
      port: 465
      protocol: tls # or 'ssl' (deprecated)
      auth: true
      username: email@compay.com
      password: secret
    from: backup@company.com
    to: sysadmin@company.com
    subject:
      success: "[OK][SERVER-PROD01] Backup success"
      fail: "[OK][SERVER-PROD01] Backup failed"
```

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

2. `backup:backup` - Execute the actual backup according to configuration
```
$> ./vendor/bin/robo backup:status
```
