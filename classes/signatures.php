<?php
	class Signatures {
		private $settings = NULL;
		private $func = NULL;
		private $stats = NULL;
		
		private $properties = Array();
		private $types = Array("max", "med", "min");
		private $image = NULL;
		private $id = NULL;
		
		
		public function __construct($id) {
			$this->settings = new Settings();
			$this->func     = new Functions();
			$this->stats    = new Stats($id);
			
			$this->id = $id;
			$this->load_signature_settings();
		}
		
		
		private function load_signature_settings() {
			$data = $this->func->load_settings("signatures");
			
			foreach ($this->types as $type) {
				$this->properties[$type] = Array();
				
				foreach ($data as $key => $value) {
					if (substr($key, 0, strpos($key, "_")) == $type) {
						$key = substr($key, strpos($key, "_")+1);
						$this->properties[$type][$key] = $value;
					}
				}
			}
		}
		
		
		private function check_rgb_color($string) {
			$array = explode(",", $string);
			for ($i = 0; $i < 3; $i++) {
				if (isset($array[$i]) AND is_numeric($array[$i])) {
					if ($array[$i] >= 0 OR $array[$i] <= 127) {
						/* do nothing */
					} else {
						$array[$i] = 0;
					}
				} else {
					$array[$i] = 0;
				}
			}
			return $array;
		}
		
		
		private function create_background($type, $path, $width, $height) {
			/* Variables */
			$src         = $path.$this->properties[$type]['signature_pattern_filename'];
			$bg_color    = $this->check_rgb_color($this->properties[$type]['background_color']);
			$alpha_start = $this->properties[$type]['background_alpha_start'];
			$alpha_end   = $this->properties[$type]['background_alpha_end'];
			$offset      = $this->properties[$type]['background_offset'];
			
			/* Create the picture */
			$this->image = imagecreatetruecolor($width, $height);
			$pattern = imagecreatefrompng($src);
			imagecopyresampled($this->image, $pattern, 0, 0, 0, 0, $width, $height, $width, $height);
			
			/* Create the background gradient */
			$alpha_step = ($alpha_end - $alpha_start) / ($width - $offset);
			for ($i = 0; $i < ($width - $offset); $i++) {
				$color = imagecolorallocatealpha($this->image, trim($bg_color[0]), trim($bg_color[1]), trim($bg_color[2]), ($i*$alpha_step)+$alpha_start);
				imageline($this->image, $i, 0, $i, $height, $color);
			}
		}
		
		
		private function create_rankicon($path, $type) {
			if ($type == "max" OR $type == "x01") {
				$width = 90;
				$x = 20;
				$y = 10;
			}
			
			if ($type == "med") {
				$width = 60;
				$x = 15;
				$y = 10;
			}
			
			if ($type == "min") {
				$width = 30;
				$x = 10;
				$y = 5;
			}
			
			$url = $this->stats->get('basic', 'clan_emblems');
			#$icon = imagecreatefrompng($url->large);
			#imagecopyresampled($this->image, $icon, $x, $y, 0, 0, $width, $width, 64, 64);
		}
		
		
		private function create_text($path, $type) {
			/* Variables */
			$font_file = $path.$this->properties[$type]['font_filename'];
			$size      = $this->properties[$type]['font_size'];
			$color     = $this->check_rgb_color($this->properties[$type]['font_color']);
			$player    = $this->stats->get('player');
			$stats     = $this->stats->get('basic');
			$basic     = $this->stats->get('basic');
			
			/* Allocate image colors */
			$font_color = imagecolorallocate($this->image, trim($color[0]), trim($color[1]), trim($color[2]));
			
			/* Create the texts */
			if ($type == "max") {
				$size1 = $size;
				$size2 = round($size * 1.5);
				$size3 = $size2 + 10;
				$size4 = $size3 + 25;
				$size5 = $size + 6;
				
				/* Name & Rank */
				$clan_role = $basic['clan_role'];
				$pos = imagefttext($this->image, $size2, 0, 130, ($size3)           , $font_color, $font_file, $player['name']);
				$pos = imagefttext($this->image, $size1, 0, ($pos[2] + 10), ($size3), $font_color, $font_file, $clan_role);
				$x = 130;
				
				/* Label 1 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, "Battles:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "XP:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "Frags:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "Damage:");
				$x += 40;
				
				/* Values 1 */
				$pos = imagefttext($this->image, $size1, 0, 190, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'battles', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, 190, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'xp', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, 190, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'frags', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, 190, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'damage_dealt', Array('number')));
				$x += 90;
				
				/* Label 2 */
				$pos = imagefttext($this->image, $size1, 0, 300, ($size4)          , $font_color, $font_file, "EB:");
				$pos = imagefttext($this->image, $size1, 0, 300, ($pos[1] + $size5), $font_color, $font_file, "WB:");
				$pos = imagefttext($this->image, $size1, 0, 300, ($pos[1] + $size5), $font_color, $font_file, "SR:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "HR:");
				$x += 30;
				
				/* Values 2 */
				$pos = imagefttext($this->image, $size1, 0, 350, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'xp', Array('ratio', ($stats['battles']), 0,)));
				$pos = imagefttext($this->image, $size1, 0, 350, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'wins', Array('ratio', ($stats['battles']), 0, '%')));
				$pos = imagefttext($this->image, $size1, 0, 350, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'survived_battles', Array('ratio', ($stats['battles']), 0, '%')));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $stats['hits_percents'].'%');
				$x += 60;
				
				/* Label 3 */
				#$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, "SPT:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "SB:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "DB:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "FB:");
				#$x += 30;
				
				/* Values 3 */
				#$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'spotted', Array('number')));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'spotted', Array('ratio', ($stats['battles']), 2)));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'damage_dealt', Array('ratio', ($stats['battles']), 0)));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'frags', Array('ratio', ($stats['battles']), 2)));
			}
			
			if ($type == "med") {
				$size1 = $size;
				$size2 = round($size * 1.5);
				$size3 = $size2 + 10;
				$size4 = $size3 + 20;
				$size5 = $size + 6;
				
				/* Name & Rank */
				$clan_role = $basic['clan_role'];
				$pos = imagefttext($this->image, $size2, 0, 90, ($size3)            , $font_color, $font_file, $player['name']);
				$pos = imagefttext($this->image, $size1, 0, ($pos[2] + 10), ($size3), $font_color, $font_file, $clan_role);
				$x = 90;
				
				/* Label 1 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, "Battles:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "XP:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "Dmg:");
				$x += 40;
				
				/* Values 1 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'battles', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'xp', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'damage_dealt', Array('number')));
				$x += 80;
				
				/* Label 2 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, "FRG:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "SPT:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "DB:");
				$x += 25;
				
				/* Values 2 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'frags', Array('number')));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'spotted', Array('number')));
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'damage_dealt', Array('ratio', ($stats['battles']), 0)));
				$x += 55;
				
				/* Label 3 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, "WB:");
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "SR:");
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, "HR:");
				$x += 25;
				
				/* Values 3 */
				$pos = imagefttext($this->image, $size1, 0, $x, ($size4)          , $font_color, $font_file, $this->stats->get('basic', 'wins', Array('ratio', ($stats['battles']), 0, '%')));
				$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $this->stats->get('basic', 'survived_battles', Array('ratio', ($stats['battles']), 0, '%')));
				#$pos = imagefttext($this->image, $size1, 0, $x, ($pos[1] + $size5), $font_color, $font_file, $stats['hits_percents'].'%');
			}
			
			if ($type == "min") {
				$size1 = $size;
				$size2 = round($size * 1.4);
				$size3 = $size2 + 7;
				$size4 = $size3 + 15;
				$size5 = round($size * 1.2);
				
				/* Name & Rank */
				$clan_role = $basic['clan_role'];
				$pos = imagefttext($this->image, $size2, 0, 50, ($size3), $font_color, $font_file, $player['name']);
				$pos = imagefttext($this->image, $size1, 0, ($pos[2] + 10), ($size3), $font_color, $font_file, $clan_role);
				$x = 50;
				
				/* Values 1 */
				$pos = imagefttext($this->image, $size5, 0, $x, $size4, $font_color, $font_file, "E/B:");
				$pos = imagefttext($this->image, $size5, 0, $pos[2] + 5, $size4, $font_color, $font_file, $this->stats->get('basic', 'xp', Array('ratio', ($stats['battles']), 0,)));
				$pos = imagefttext($this->image, $size5, 0, $pos[2] + 10, $size4, $font_color, $font_file, "W/B:");
				$pos = imagefttext($this->image, $size5, 0, $pos[2] + 5, $size4, $font_color, $font_file, $this->stats->get('basic', 'wins', Array('ratio', ($stats['battles']), 0, '%')));
				$pos = imagefttext($this->image, $size5, 0, $pos[2] + 10, $size4, $font_color, $font_file, "SR:");
				$pos = imagefttext($this->image, $size5, 0, $pos[2] + 5, $size4, $font_color, $font_file, $this->stats->get('basic', 'survived_battles', Array('ratio', ($stats['battles']), 0, '%')));
				#$pos = imagefttext($this->image, $size5, 0, $pos[2] + 10, $size4, $font_color, $font_file, "HR:");
				#$pos = imagefttext($this->image, $size5, 0, $pos[2] + 5, $size4, $font_color, $font_file, $stats['hits_percents'].'%');
			}
			
		}
		
		
		private function create_picture($path, $type) {
			/* Variables */
			$filename = $this->id."_".$type.".png";
			$filename = $filename;
			
			/* Create the picture */
			imagepng($this->image, $path.'signatures/'.$filename);
			imagedestroy($this->image);
		}
		
		
		public function create_signature($path='../') {
			foreach ($this->types as $type) {
				$width  = $this->properties[$type]['signature_width'];
				$height = $this->properties[$type]['signature_height'];
				
				$this->create_background($type, $path, $width, $height);
				$this->create_rankicon($path, $type);
				$this->create_text($path, $type);
				$this->create_picture($path, $type);
			}
			return true;
		}
	}
?>