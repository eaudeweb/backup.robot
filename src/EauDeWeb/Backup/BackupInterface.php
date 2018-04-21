<?php

namespace EauDeWeb\Robo\Plugin\Commands;

/**
 * Interface BackupInterface is implemented by components that are being able to
 * create the effective backup tasks. It has two phases:
 *
 * 1. Preparation phase where it creates the necessary things to successfully
 * achieve backup.
 * 2. Backup phase where the actual backup is made.
 *
 * @package EauDeWeb\Robo\Plugin\Commands
 */
interface BackupInterface {

  const BACKUP_SUCCESS = 'success';
  const BACKUP_PARTIAL = 'partial';
  const BACKUP_ERROR = 'error';

  /**
   * Make backups preparations (i.e. create backup directories).
   *
   * If preparation throws exception the backup is never called.
   *
   * @throws \Exception
   *   Throw error when preparation fails (i.e. cannot create directory).
   */
  function prepare();

  /**
   * Create the actual backup (i.e. Dump databases, push or pull remote etc.)
   * @throws \Exception
   * @return string
   *   A status string represented in constants BACKUP_*
   */
  function backup();
}
