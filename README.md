# Backup.Robot 🤖

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
2. Copy `robo.test.yml` to `robo.yml` and restrict file permissions (i.e. `chown root:root robo.yml && chmod 600 robo.yml`)
3. Customize configuration as stated in the next chapter.
4. Start a backup using command: `./run.php backup:backup`
5. Install a CRON job (TODO) - `backup.sh`

```
30 2 * * * /path/to/backup.sh
```

**Disclaimer: The code above is not tested in production**

## Configuration

Configuration is done in `robo.yml` file and is quite simple. You can define one or more backup projects (for simple deployments one project should be enough). A project consists of a set of actions per project. 
For example dump databases, rsync everything to a target server then send me an email report. The configuration to do this is the following: 

```
backup:
  version: "1.0"
  defaults:
    timezone: 'Europe/Bucharest'
    email:
      enabled: true # Enable sending emails, globally
      per-project: false; # Send emails for each backup project or only one global email with all tasks.
      attachment-threshold: 3MB # If the log file is greater than this size it will be attached to email instead.
      attachment-compress: true
      server:
        debug-level: 2 # 0 - no output (for production), 1 - client messages, 2 - client + server messages
        type: smtp # or 'mail' or 'sendmail' or 'qmail'
        host: mail.company.com
        port: 465 # other ports: 25, 587
        protocol: ssl # 'false' or 'ssl' (deprecated)
        auth: true # use authentication
        username: backup
        password: secret
      from: backup@company.com
      to: destination.email@company.com
      subject:
        success: "[OK][SERVER-PROD01] Backup success"
        fail: "[FAIL][SERVER-PROD01] Backup failed"
  projects:
    project1:
      mysql:
        server1:
          host: silo1.company.com
          port: 3306
          user: root1
          password: pass1
          destination: /tmp/backup-robot-test/test/silo1/
          gzip: true
          blacklist: ["performance_schema", "information_schema", "db1"]
      rsync:
        mysql-dumps:
          from: /tmp/backup-robot-test/test/databases/
          to: /backups/PROJECT1/databases
          user: bofh
          host: backup-push-storage.company.com
          port: 2279
```

## Useful commands

1. `backup:status` - Overview of backup tasks


```
$> ./run.php backup:status

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
$> ./run.php backup:backup
```
