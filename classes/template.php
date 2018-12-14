<?php
	class Templates {
		private $settings = NULL;
		private $sql = NULL;
		private $func = NULL;
		private $phrases = array();

		public function __construct() {
			$this->settings = new Settings();
			$this->func = new Functions();
			$this->load_phrases("default");
		}

		public function template($template, $ext="html", $folder=false) {
			if ($folder) $this->settings->set('default_template_folder', $folder);
			
			return str_replace("\"", "\\\"", $this->replace_wildcards(file_get_contents($this->settings->get('default_template_folder')."/".$template.".".$ext)));
		}

		public function load_phrases($category, $add=false) {
			if(!$add) $this->phrases = array(); // Loescht das Array "phrases" und es wird so nur eine Kategorie geladen
			$category = str_replace(array('\\','/','.'), '', $category);
			$lang = $this->settings->get('settings_language');
			$deflang = $this->settings->get('default_language');
			
			
			$this->sql = new MySQL();
			$this->sql->query('SELECT * FROM '.$this->sql->prefix.'localization WHERE category = "'.$category.'"');
			
			while ($result = $this->sql->fetchRow()) {
				if (trim($result[$lang]) != "") {
					$this->phrases[$result['wildcard']] = $this->func->decode($result[$lang]);
				} else {
					if (trim($result[$deflang]) != "")
						$this->phrases[$result['wildcard']] = $this->func->decode($result[$deflang]);
						else $this->phrases[$result['wildcard']] = 'String "'.$result['wildcard'].'" not found';
				}
			}
			$this->sql->disconnect();
			
			// BB-Codes einbinden
			$this->phrases = str_replace(
				Array('[br]',  '[b]', '[/b]', '[ ]',    '[ul]', '[/ul]', '[li]', '[/li]'), 
				Array('<br/>', '<b>', '</b>', '&nbsp;', '<ul>', '</ul>', '<li>', '</li>'), 
				$this->phrases
			);
			return true;
		}

		public function phrase($wildcard, $error=true) {
			if (isset($this->phrases[$wildcard])) {
				return $this->phrases[$wildcard];
			} else {
				if ($error) return 'Wildcard "'.$wildcard.'" not found';
				else        return false;
			}
		}

		private function replace_wildcards($template) {
			foreach($this->phrases as $key => $value) {
				$template = str_replace('%'.$key.'%', $value, $template);
			}
			return $template;
		}

	}
?>