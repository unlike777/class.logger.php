<?php

/*
	Класс для ведения файловых логов.
*/

class Logger {
	
	private $file_name = ''; //путь до файла
	private $lines = array(); //массив строк
	private $max_file_size = 1; //максимально допустимый размер файла лога (Мб)
	public $error = 0; //номер ошибки
	
	//инициализация
	public function __construct($file_name) {
		if (empty($file_name)) {
			$this->error = 1;
		}

		$this->file_name = $file_name;
	}
	
	//добавляем в стек строки
	//$data - может быть или строкой, или массивом
	public function add($data) {
		if (is_array($data)) {
			array_merge($this->lines, $data);
		} else {
			$this->lines[] = $data;
		}
		
		return $this;
	}
	
	//очищаем стэк строк
	public function clear() {
		$this->lines = array();
		
		return $this;
	}
	
	
	//сохраняем в файл
	public function save() {
		if (count($this->lines) == 0) {
			$this->error = 2;	
		}
		
		if (empty($this->error)) {
			
			$result = '';
			
			foreach ($this->lines as $line) {
				$line = str_replace(array("\n", "\r", "\r\n"), "", $line);
				$result .= $line."\n";
			}
			
			if ($this->max_file_size > 0) {
				if (@filesize($this->file_name) >= 1024*1024*$this->max_file_size) {
					$i = 1;
					while (file_exists($this->file_name.'.'.$i)) {
						$i++;
					}
					rename($this->file_name, $this->file_name.'.'.$i);
				}
			}
			
			$file = @fopen($this->file_name, "a");
			@fwrite ($file, $result);
			@fclose ($file);
			
			$this->clear();
			
			return true;
		} else {
			return false;
		}
	}
}

?>