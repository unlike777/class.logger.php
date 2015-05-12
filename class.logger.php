<?php

/**
 * class.logger.php Copyright 2014
 * https://github.com/unlike777/class.logger.php
 * 
 * Класс для ведения файловых логов.
*/

class Logger {
	
	private $file_name = '';      //путь до файла
	private $lines = array();     //массив строк
	private $max_file_size = 1;   //максимально допустимый размер файла лога (Мб)
	private $max_files = 5;   	  //максимально допустимое кол-во файлов (ротация)
	private $errors = array();    //массив ошибок
	
	
	public function __construct($file_name) {
		if (empty($file_name)) {
			$this->errors[] = 'Не задан файл лога';
		}
		
		$file_name = trim($file_name);
		$file_name = str_replace(self::root(), '', $file_name);
		$file_name = trim($file_name);
		
		if (substr($file_name, 0, 1) == '/') {
			$file_name = substr($file_name, 1);
		}
		
		$this->file_name = self::root().'/'.$file_name;
	}
	
	//вернет путь к корню сайта
	private static function root() {
		return getcwd();
	}
	
	/**
	 * добавляем в стек строки
	 * 
	 * @param $data - может быть или строкой, или массивом
	 * @return $this
	 */
	public function add($data) {
		if (is_array($data)) {
			array_merge($this->lines, $data);
		} else {
			$this->lines[] = $data;
		}
		
		return $this;
	}
	
	/**
	 * Доавляет ассоциативный массив в лог
	 * рекурсивно
	 * 
	 * @param $data
	 * @param string $tab - префикс перед строками 
	 */
	public function addArr($data, $tab = '') {
		if (is_array($data)) {
			
			foreach ($data as $key => $val) {
				
				if (is_array($val)) {
					$this->add($tab.$key.': ');
					$this->addArr($val, $tab."\t");
				} else {
					$this->add($tab.$key.': '.$val);
				}
				
			}
			
		} else {
			$this->add($tab.$data);
		}
		
		return $this;
	}

	/**
	 * очищаем стэк строк
	 * @return $this
	 */
	public function clear() {
		$this->lines = array();
		
		return $this;
	}


	/**
	 * Преобразует стек в финальную строку
	 * убирает символы переноса строк
	 * 
	 * @return string
	 */
	public function getResult() {
		if (count($this->lines) == 0) {
			$this->errors[] = 'Нечего сохранять';
			return '';
		}
		
		$result = '';

		foreach ($this->lines as $line) {
			$line = str_replace(array("\n", "\r", "\r\n"), "", $line);
			$result .= $line."\n";
		}
		
		return $result;
	}

	/**
	 * проверяет файл лога, если файл превышает допустимый размер переменовывает его
	 * @return $this
	 */
	public function checkFile() {
		if ($this->max_file_size > 0) {
			if (@filesize($this->file_name) >= 1024*1024*$this->max_file_size) {
				$i = 1;
				while (file_exists($this->file_name.'.'.$i)) {
					$i++;
				}
				
				for ($j = $i; $j > 0; $j--) {
					if (@!rename($this->file_name.'.'.$j, $this->file_name.'.'.($j+1))) {
						$this->errors[] = 'Доступ на редактирования файлов закрыт';
					}
					
					//удаляем файл если перваышем допустимый лимит на кол-во логов
					if ($j > $this->max_files) {
						@unlink($this->file_name.'.'.($j+1));
					}
				}
				
				if (@!rename($this->file_name, $this->file_name.'.1')) {
					$this->errors[] = 'Доступ на редактирования файлов закрыт';
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * сохраняем в файл
	 * @return bool
	 */
	public function save() {

		$result = $this->getResult();
		$this->checkFile();

		if ($this->issetErrors()) {
			return false;
		}
		
		if ( !($file = @fopen($this->file_name, "a")) ) {
			$this->errors[] = 'Доступ на создание файлов закрыт';
			return false;
		}
		
		if (@fwrite ($file, $result) === false) {
			$this->errors[] = 'Записать в файл не удалось';
			return false;
		}
		
		@fclose ($file);
		
		$this->clear();
		
		return true;
	}

	/**
	 * Проверяет наличие ошибок
	 * @return bool
	 */
	public function issetErrors() {
		return $this->errors ? true : false;
	}
	
	/**
	 * Возвращает массив ошибок
	 * @return array
	 */
	public function errors() {
		return $this->errors;
	}

	/**
	 * Возвращает первую ошибку
	 * @return string
	 */
	public function firstError() {
		return $this->issetErrors() ? $this->errors[0] : '';
	}
	
}

?>