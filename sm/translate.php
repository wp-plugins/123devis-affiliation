<?php
	class sm_translate {
		
		static $translations;
		private $lang;
		private $translations_dir;
		
		public function __construct($lang){
			$this->lang = $lang;
			$this->translations_dir = dirname(__FILE__) . '/translations/';
			self::$translations = array();
		}
		
		public function set_translations_dir($dir){
			$this->translations_dir = $dir;
		}
		
		private function lang_discovered($lang){
			return is_array(self::$translations) && isset(self::$translations[$lang]);
		}
		
		private function discover($lang){
			$transfile = $this->translations_dir . $this->lang . '.txt';
			
			if (!file_exists($transfile)) {
             	self::$translations[$lang] = array();
				return;
			}
			
			$strings = file($transfile);
			foreach ($strings as $line) {
				$p = preg_split("~(?<!\\\):~", $line);//negative look behind so that \: is'nt used as delimeter.

				if (count($p) !== 2) {
					continue;
				}
				
				foreach($p as $k => $v){
					$p[$k] = trim(str_replace("\:", ":", $v));
				}
				
				self::$translations[$lang][$p[0]] = $p[1];
			}
		}
		
		public function trans($word, $lang = null){
			$lang = !is_null($lang) ? $lang : $this->lang;

			if (!$this->lang_discovered($lang)){
				$this->discover($lang);
			}
			
			if (array_key_exists($word, self::$translations[$lang])){
				return self::$translations[$lang][$word];
			} else return $word;
		}
		
		public function write($word, $lang = null){
			print $this->trans($word, $lang);
		}
	}