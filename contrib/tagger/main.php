<?php
/**
 * Name: Tagger
 * Description: Advanced Tagging v2
 * Author: Artanis (Erik Youngren) <artanis.00@gmail.com>
 * Do not remove this notice.
 */

class Tagger implements Extension {
	var $theme;

	public function get_priority() {return 50;}

	public function receive_event(Event $event) {
		if(is_null($this->theme))
			$this->theme = get_theme_object($this);

		if($event instanceof DisplayingImageEvent) {
			global $page, $config, $user;

			if($config->get_bool("tag_edit_anon")
				|| ($user->id != $config->get_int("anon_id"))
				&& $config->get_bool("ext_tagger_enabled"))
			{
				$this->theme->build_tagger($page,$event);
			}
		}

		if($event instanceof SetupBuildingEvent) {
			$sb = new SetupBlock("Tagger");
			$sb->add_bool_option("ext_tagger_enabled","Enable Tagger");
			$sb->add_int_option("ext_tagger_search_delay","<br/>Delay queries by ");
			$sb->add_label(" milliseconds.");
			$sb->add_label("<br/>Limit queries returning more than ");
			$sb->add_int_option("ext_tagger_tag_max");
			$sb->add_label(" tags to ");
			$sb->add_int_option("ext_tagger_limit");
			$event->panel->add_block($sb);
		}
	}
}

// Tagger AJAX back-end
class TaggerXML implements Extension {
	public function get_priority() {return 10;}

	public function receive_event(Event $event) {
		if(($event instanceof PageRequestEvent) && $event->page_matches("tagger/tags")) {
			global $page;

			//$match_tags = null;
			//$image_tags = null;
			$tags=null;
			if (isset($_GET['s'])) { // tagger/tags[/...]?s=$string
				// return matching tags in XML form
				$tags = $this->match_tag_list($_GET['s']);
			} else if($event->get_arg(0)) { // tagger/tags/$int
				// return arg[1] AS image_id's tag list in XML form
				$tags = $this->image_tag_list($event->get_arg(0));
			}

			$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
			"<tags>".
				$tags.
			"</tags>";

			$page->set_mode("data");
			$page->set_type("text/xml");
			$page->set_data($xml);
		}
	}

	private function match_tag_list ($s) {
		global $database, $config, $event;

		$max_rows = $config->get_int("ext_tagger_tag_max",30);
		$limit_rows = $config->get_int("ext_tagger_limit",30);

		$values = array();

		// Match
		$p = strlen($s) == 1? " ":"\_";
		$sq = "%".$p.mysql_real_escape_string($s)."%";
		$match = "concat(?,tag) LIKE ?";
		array_push($values,$p,$sq);
		// Exclude
//		$exclude = $event->get_arg(1)? "AND NOT IN ".$this->image_tags($event->get_arg(1)) : null;

		// Hidden Tags
		$hidden = $config->get_string('ext-tagger_show-hidden','N')=='N' ?
			"AND substring(tag,1,1) != '.'" : null;

		$q_where = "WHERE {$match} {$hidden} AND count > 0";

		// FROM based on return count
		$q_from = null;
		$count = $this->count($q_where,$values);
		if ($count > $max_rows) {
			$q_from = "FROM (SELECT * FROM `tags` {$q_where} ".
				"ORDER BY count DESC LIMIT 0, {$limit_rows}) AS `c_tags`";
			$q_where = null;
			$count = array("max"=>$count);
		} else {
			$q_from = "FROM `tags`";
			$count = null;
		}

		$tags = $database->Execute("
			SELECT *
			{$q_from}
			{$q_where}
			ORDER BY tag",
			$values);

		return $this->list_to_xml($tags,"search",$s,$count);
	}

	private function image_tag_list ($image_id) {
		global $database;
		$tags = $database->Execute("
			SELECT tags.*
			FROM image_tags JOIN tags ON image_tags.tag_id = tags.id
			WHERE image_id=? ORDER BY tag", array($image_id));
		return $this->list_to_xml($tags,"image",$image_id);
	}

	private function list_to_xml ($tags,$type,$query,$misc=null) {
		$r = $tags->_numOfRows;

		$s_misc = "";
		if(!is_null($misc))
			foreach($misc as $attr => $val)	$s_misc .= " ".$attr."=\"".$val."\"";

		$result = "<list id=\"$type\" query=\"$query\" rows=\"$r\"{$s_misc}>";
		foreach($tags as $tag) {
			$result .= $this->tag_to_xml($tag);
		}
		return $result."</list>";
	}

	private function tag_to_xml ($tag) {
		return
			"<tag  ".
				"id=\"".$tag['id']."\" ".
				"count=\"".$tag['count']."\">".
				html_escape($tag['tag']).
				"</tag>";
	}

	private function count($query,$values) {
		global $database;
		return $database->Execute(
			"SELECT COUNT(*) FROM `tags` $query",$values)->fields['COUNT(*)'];
	}

	private function image_tags ($image_id) {
		global $database;
		$list = "(";
		$i_tags = $database->Execute(
			"SELECT tag_id FROM `image_tags` WHERE image_id=?",
			array($image_id));

		$b = false;
		foreach($i_tags as $tag) {
			if($b)
				$list .= ",";
			$b = true;
			$list .= $tag['tag_id'];

		}
		$list .= ")";

		return $list;
	}
} 
?>
