<?php

/** Dump to CSV cp932 DOS format
* @link https://www.adminer.org/plugins/#use
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/

class AdminerDumpExcelCSV {
	
	function dumpFormat() {
		return array('excel-csv' => 'ExcelCSV');
	}

	function dumpTable($table, $style, $is_view = false) {
		if ($_POST["format"] == "excel-csv") { return true; }
	}

	// 参考:https://github.com/vrana/adminer/blob/63f2a041ed70be3ceb234fba684d715555e7551a/adminer/include/functions.inc.php#L1046
	/** Print CSV row
	* @param array
	* @return null
	*/
	function _dump_csv($row) {
		foreach ($row as $key => $val) {
			$val=preg_replace('/(?:\r\n)|(?:\r)|(?:\n)/',"\r\n",$val);
			$row[$key] = '"' . str_replace('"', '""', $val) . '"';
		}
		echo mb_convert_encoding(implode(",", $row),"sjis-win","utf-8") . "\r\n";
	}

	// 参考:https://github.com/vrana/adminer/blob/53dfafd2ea80e318eded7937252d0c1d9b7a2c93/adminer/include/adminer.inc.php#L671	
	/** Export table data
	* @param string
	* @param string
	* @param string
	* @return null prints data
	*/
	function dumpData($table, $style, $query) {
		global $connection, $jush;
		$max_packet = ($jush == "sqlite" ? 0 : 1048576); // default, minimum is 1024
		$connection = connection();
		$result = $connection->query($query, 1); // 1 - MYSQLI_USE_RESULT //! enum and set as numbers
		if ($result) {
			$insert = "";
			$keys = array();
			$fetch_function = ($table != '' ? 'fetch_assoc' : 'fetch_row');
			while ($row = $result->$fetch_function()) {
				if (!$keys) {
					$values = array();
					foreach ($row as $val) {
						$field = $result->fetch_field();
						$keys[] = $field->name;
						$key = idf_escape($field->name);
						$values[] = "$key = VALUES($key)";
					}
				}
				if ($style == "table") {
					$this->_dump_csv($keys);
					$style = "INSERT";
				}
				$this->_dump_csv($row);
			}
		}
		return true;
	}

}
