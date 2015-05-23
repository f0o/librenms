#!/usr/bin/env php
<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@librenms.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Daemon
 * @author Daniel Preussker (f0o) <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Daemon
 */

declare(ticks=1);
pcntl_signal(SIGCHLD, 'cleanup');
chdir(dirname($argv[0]));

//Declare the start timestamp and the default step (1minute) for the loop
$ts     = microtime(true);
$step   = 60;
$config = array();

/**
 * (Re)load config
 * @return void
 */
function loadcnf() {
	global $config;
	include("includes/defaults.inc.php");
	include("config.php");
	include("includes/definitions.inc.php");
}

/**
 * Sleep until timestamp
 * @param float $ts Microtime-Target
 * @return true
 */
function sleep_until($ts) {
	while( ($delta=($ts-microtime(true))) > 0 ) {
		usleep(($delta/2)*1000000);
	}
	return true;
}

/**
 * Fork-wrapper
 * @return int|boolean
 */
function fork() {
	$pid = pcntl_fork();
	if( $pid == -1 ) {
		return false;
	} elseif( $pid ) {
		return $pid;
	} else {
		return true;
	}
}

/**
 * Log messages
 * @param string|array $msgs Message/s
 * @param int $lvl Log-Level
 * @return void
 */
function logger($msgs,$lvl=LOG_INFO) {
	global $config;
	openlog('librenms', (LOG_CONS | LOG_NDELAY | LOG_PID), $config['daemon']['facility']);
	if( !is_array($msgs) ) {
		$msgs = explode("\n",$msgs);
	}
	foreach( $msgs as $msg ) {
		if( !empty($msg) ) {
			syslog($lvl,$msg);
		}
	}
	closelog();
}

/**
 * Spawn a job as fork
 * @param array $job Job-Object
 * @return void
 */
function spawn($job) {
	global $config, $argv, $argc;
	$pid = fork();
	if( $pid === true ) {
		$job['file'] = $config['install_dir'].'/'.$job['file'];
		$code = 0;
		if( $job['type'] == 'exec' ) {
			$out  = explode("\n",shell_exec($job['file'].' '.$job['args'].' 2>&1 || echo -n $?'));
			$code = (int) array_pop($out);
		} elseif( $job['type'] == 'include' ) {
			if( !file_exists($job['file']) ) {
				$code = 127;
			} else {
				ob_start();
				require($job['file']);
				$out = ob_get_clean();
			}
		}
		logger($out,LOG_DEBUG);
		exit($code);
	} else {
		logger('Spawned Job '.$job['file'].' PID #'.$pid);
	}
}

/**
 * Cleanup Zombies
 * @param int $sig Signal
 * @param int $pid PID defaults to -1
 * @param int $status Exit-Status
 * @return void
 */
function cleanup($sig, $pid=-1, $status=null) {
	while( ($pid=pcntl_waitpid($pid, $status, WNOHANG)) != -1 ) {
		$status = pcntl_wexitstatus($status);
		logger('PID #'.$pid.' exited with status '.$status);
	}
}

/**
 * Main-loop
 */
loadcnf();
$i=1;
do {
	$ts += $step;
	logger("Interval #".$i);

	// Update config-cache
	loadcnf();

	// Cycle through daemons
	foreach( $config['daemon']['intervals'] as $int => $run ) {
		if( $i%$int == 0 ) {
			foreach( $run as $job ) {
				spawn($job);
			}
		}
	}

	$i++;
} while( sleep_until($ts) == true );
?>
