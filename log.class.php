<?php
class BxLog {
	protected $fp;
	protected $mask;
	protected $msg;
	protected $file;
	protected $rotateDays;
	protected $keepLogs;

	public function __construct($file, $rotateDays, $keepLogs) {
		$this->fp			= null;
		$this->mask			= umask();
		$this->msg			= '';

		$this->file			= $file;
		$this->rotateDays	= $rotateDays;	//
		$this->keepLogs		= $keepLogs;	// keep log count

		$oldmask = umask();
		umask(000);

		$this->rotate();

		if (!$this->open()) {
			umask($this->mask);
			echo $this->msg;
			exit;
		}
		umask($oldmask);
	}

	private function open($mode = 'a') {
		try {
			if ($this->fp = @fopen($this->file, $mode)) {
				return true;
			}else{
				throw new Exception('Log file open error. Check directory permission.');
			}
		}catch (Exception $ex) {
			$this->msg = $ex->getMessage();
			return false;
		}
	}

	private function pathinfo($path, $options = null) {
		if (defined('PATHINFO_FILENAME')) {		// php >= 5.2.0
			if ($options == null) {
				$path = pathinfo($path);
			}else{
				$path = pathinfo($path, $options);
			}
		}else{
			if ($options == null) {
				$path = pathinfo($path);
				if ($path['extension']) {
					$path['filename'] = substr($path['basename'], 0, strlen($path['basename']) - strlen($path['extension']) - 1);
				}else{
					$path['filename'] = $path['basename'];
				}
			}else{
				$path = pathinfo($path, $options);
				if ($options >= 8) {
					$basename = pathinfo($path, PATHINFO_BASENAME);
					if (strpos($basename, '.') !== false) {
						$extension = end(explode('.', $path));
						$path['filename'] = substr($basename, 0, strlen($basename) - strlen($extension) - 1);
					}else{
						$extension = '';
						$path['filename'] = $basename;
					}
				}
			}
		}
		return $path;
	}

	private function rotate() {
		if (file_exists($this->file)) {
			if (!$this->open('r')) {
				umask($this->mask);
				echo $this->msg;
				exit;
			}
			$buf = fgetss($this->fp, 4096);
			$this->close();


			if ($buf) {
				$date = strtotime(substr($buf, 0, 20));
//				if ($date !== false && time() - (60*60*24*$this->rotateDays) > $date) {

				$nowDay = strtotime(date("Y-m-d"));
				$checkDay = strtotime(date("Y-m-d" , $date + (60*60*24*($this->rotateDays-1))));

				if ($date !== false && $nowDay > $checkDay) {
					// rotate start

					$fileParts	= $this->pathinfo($this->file);
					$dirName	= $fileParts['dirname'];
					$extension	= $fileParts['extension'];
					$baseName	= $fileParts['basename'];
					$fileName	= $fileParts['filename'];

					if (file_exists("{$dirName}/{$fileName}.{$this->keepLogs}.{$extension}")) $this->del("{$dirName}/{$fileName}.{$this->keepLogs}.{$extension}");

					for ($loop = $this->keepLogs - 1; $loop >= 1; $loop--) {
						$oldName = "{$dirName}/{$fileName}.{$loop}.{$extension}";
						$newNum = $loop + 1;
						$newName = "{$dirName}/{$fileName}.{$newNum}.{$extension}";
						if (file_exists($oldName)) rename($oldName, $newName);
					}

					rename($this->file, "{$dirName}/{$fileName}.1.{$extension}");
				}
			}
		}
	}

	private function del($file) {
		unlink ($file);
	}

	public function close() {
		fclose($this->fp);
	}

	private function puts($type, $logmsg) {
		switch($type) {
			case 1:		// LOG_ALERT
				$typemsg = "ALERT";
				break;
			case 2:		// LOG_CRIT
				$typemsg = "CRITICAL";
				break;
			case 3:		// LOG_ERR
				$typemsg = "ERROR";
				break;
			case 4:		// LOG_WARNING
				$typemsg = "WARNING";
				break;
			case 5:		// LOG_NOTICE
				$typemsg = "NOTICE";
				break;
			case 6:		// LOG_INFO
				$typemsg = "INFO";
				break;
			case 7:		// LOG_DEBUG
				$typemsg = "DEBUG";
				break;
		}
		return fputs($this->fp,  date('M d Y H:i:s')." [{$typemsg}] ". $_SERVER["REMOTE_ADDR"]." - {$logmsg}\r\n");
	}

	public function alert($logmsg) {
		return $this->puts(1, $logmsg);
	}

	public function critlcal($logmsg) {
		return $this->puts(2, $logmsg);
	}

	public function error($logmsg) {
		return $this->puts(3, $logmsg);
	}

	public function warning($logmsg) {
		return $this->puts(4, $logmsg);
	}

	public function notice($logmsg) {
		return $this->puts(5, $logmsg);
	}

	public function info($logmsg) {
		return $this->puts(6, $logmsg);
	}

	public function debug($logmsg) {
		return $this->puts(7, $logmsg);
	}

}
