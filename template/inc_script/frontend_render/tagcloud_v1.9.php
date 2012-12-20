<?php
/******************************************************************
* TagCloud v1.9 for phpwcms --> v.1.3.9 --- v1.4.x
*
* Date: Mar. 12, 2009 by Jensensen
* --> RTFM: http://forum.phpwcms.org/viewtopic.php?f=8&t=16761
*
* Script returns <div MY_class>Your TagCloud here</div> only on pages
* where the RT {TAGCLOUD:...} was found somewhere in the page code.
*
* CREDITS {big 'thank you' to}:
* marcus@localhorst, Heiko H., marketingmensch, flip-flop, OG, claus
*
* USAGE:
*   -> Edit some variables --> see below
*   -> Add RT somewhere in your page code e.g. CP HTML or page template
*      e.g. {TAGCLOUD:I:0,2,17:S:9} or {TAGCLOUD:E:0,1,2,17,152:L:38}
*
*      Rendermode: analyze ALL articles which are located
*      I ==> IN the categories as follow (include category)
*      E ==> NOT IN the categories as follow (exclude these category)
*
*      0,2,17 ==> IDs of Site structure levels / categories to 'cloud'
*
*      Decide where cloud tags are linked to:
*      L ==> LANDING PAGE (separate page outside categories above)
*      S ==> phpwcms SEARCH PAGE
*
*      9 ==> Artikel ID of your LANDING PAGE or SEARCH PAGE!!!
*
*   -> Place this script into
*      /template/inc_script/frontend_render/
*	-> For various TC font sizes copy the CSS file to
*      /template/inc_css/specific/tagcloud.css
*
* LANDING PAGE: only necessary when you use e.g. {    L:7}
*   -> ADD a separate article/page (which must be manually prepared
*      with anchor tags) where tags are linked to and on which
*      further information to each cloud tag can be displayed.
*
*   Add CP HTML or any other and place an anchor on top of each CP
*   for each tag of the Tag Cloud:
*   <a name="TAG" id="TAG"></a><p>Read more about TAG...</p>
*
* TO DO
* any idea?
* ****************************************************************/
// obligate check for phpwcms constants
if (!defined('PHPWCMS_ROOT')) {
   die("You Cannot Access This Script Directly, Have a Nice Day.");
}
// ----------------------------------------------------------------

$content['tagcloud'] = array(

/******************************************************************
* !!!!!!!!!!!!! ### SET UP SOME VARIABLES HERE ### !!!!!!!!!!!! ###
******************************************************************/

	'min'			=> 3,		// Minimum occurrences for words to be displayed within cloud
	'min_chars'		=> 4,		// but only if word lenght is minimum --> characters: x
	'sort'			=> 'asc',	// can be: asc, desc, or random
	'showCount'		=> 1,		// display count next to tag --> can be 0=No or 1=Yes

	// Now choose which elements you want to include into match
	// just --> comment for --> NO include
	'add_t'			=> 1,		// article title
	//'add_st'		=> 1,		// article subtitle
	'add_sm'		=> 1,		// article summary
	
	// and now do the same for other page elements --> Content Parts (CP)
	'add_cpt'		=> 1,		// CP titles
	//'add_cpst'	=> 1,		// CP subtitles
	'add_cptximg'	=> 1,		// CP Text (html) with image
	
	// Add NEWS --> YES =1 or NO =0
	// DEFAULT NEWS is OFF =0
	'news_to_cloud'	=> 0,
	
	// WHEN YOU USE REWRITE THEN SET THE ALIAS OF YOUR LANDING- or SEARCH PAGE!
	// !!! NOT USED ANYMORE {temp} !!!
	// 'rw_alias'	=> 'page_alias',
	// 'rw_alias'	=> '',
	
	
	//charcters to delete out of the cloud
	'del_signs'		=> array(",", ".", ":", ";", "!", "?", "'s", "-", "[BR]", "'", "(", ")"),
	
	// USE EITHER MODE EXCLUDE OR INCLUDE
	'inc_or_ex'		=> 0, 		// --> can be 0=exclude or 1=include
	
	// words to EXCLUDE from the cloud
	'exclude'		=> array("this", "that", "können", "oder", "auch", "eine"),
	
	// words to INCLUDE to the cloud
	'include'		=> array("Lorem", "ipsum", "dolor", "amet", "sunt", "est", "häuslebauer", "ebit"),
	
	// Style and CSS settings
	// class of div wrapped around the cloud
	'class'		=> 'tagcloud',
	// ShowCount wrapper
	'SC_before'		=> '<span>(',
	'SC_after'		=> ')</span>'

/***************************************** !!! END SET UP !!! ***/

);

/************************ !!! WARNING !!! ************************
* ### ************************** ### ************************* ###
* ### !!!!!!!!!!!!!! ### DO NOT EDIT BELOW ### !!!!!!!!!!!!!!! ###
* ### *************** +++++++++++++++++++++ ****************** ###
*****************************************************************/
function make_cloud($matches) {
	
	global $phpwcms, $content;	
	// use $matches for 
	// $rendermode,$which_ID,$setLP,$landing
	
	$rendermode	= trim($matches[1]);
	$which_ID	= trim($matches[2]);
	$setLP		= trim($matches[3]);
	$landing	= isset($matches[4]) ? intval($matches[4]) : 0;

	$conf = & $content['tagcloud'];

	// check integrity of user_settings --- else use defaults +++ OG new style
	if(empty($conf['min'])) {
		$conf['min'] = 4;
	}
	if(empty($conf['min_chars'])) {
		$conf['min_chars'] = 4;
	}
	if(!isset($conf['sort'])) {
		$conf['sort'] = false;
	}
	if(empty($conf['inc_or_ex'])) {
		$conf['inc_or_ex'] = 0;
	}
	
	
	// NOW, FINALLY IT'S TIME TO LET A FRESH BREEZE BLOWING UP PRETTY CLOUDS
	if(!empty($landing)) {

		// check if landing page has an article alias ( only versions > 1.3.5  else use fallback )
		// original code has been deleted because +++ o-ton OG: "so geht es schneller"
		$landalias = _dbGet('phpwcms_article', 'article_alias', "article_id=".$landing." AND article_alias != ''");

		// "So muss weitermachen...". Hey, here we are:
		if(!empty($landalias[0]['article_alias'])) {
			$aliasfound = $landalias[0]['article_alias'];
		}
		// second BREEZE BLOWING
		if(!empty($setLP)) {
		
		  switch ($setLP) {
			 case 'L':
				if(isset($aliasfound)) {
					$landing = 'index.php?'. $aliasfound .'#';
				} else {
					$landing = 'index.php?aid='. $landing .'#'; //fallback for older versions
//$old_style		$landing = 'index.php?id=0,'. $landing . ',0,0,1,0#'; //much older versions
				}
			 break;
			 
			 case 'S':
				 if(isset($aliasfound)) {
					$landing = 'index.php?' . $aliasfound . '&amp;searchwords=';
				 } else {			 
					$landing = 'index.php?aid='. $landing . '&amp;searchwords='; //fallback for older versions
//$old_style		$landing = 'index.php?id=0,'. $landing . ',0,0,1,0&amp;searchwords='; //much older versions
				 }
			 break;
		  }
		} else {
			$landing = 'index.php#';
	   		echo "TagCloud ERROR: Wrong setup of RT! MISSING --> (L) = Landing page OR --> (S) = Search page";
	   }
	} else {
	   //die("TagCloud ERROR: Article_ID of your (L)Landing Page or your (S)Search Page is missing!");
	   echo "TagCloud ERROR: Wrong setup of RT! Article ID of (L) = Landing Page or (S) = Search Page MISSING!";
	   $landing = 'index.php#';
	}
	
	switch ($rendermode) {
	   case 'E':
		  //exclude array stuff by marcus@localhorst
		  $excludeid = explode(',',$which_ID);
		  $struct = array_keys($content['struct']);
		  $only_cat_id = array_diff($struct,$excludeid);
		  break;

	   case 'I':
		  $only_cat_id = explode(',',$which_ID);
		  break;
	   default: echo "TagCloud ERROR: Rendermode not defined! (I) = match all articles withIN named categories or vice versa (E) = exclude categories!";
		  break;
	}
	
	if(is_array($only_cat_id)) {
	   foreach ($only_cat_id as $slid) {
	   $sql = "SELECT SQL_CACHE article_id";
	   if(isset($conf['add_t'])) {$sql .= ",article_title";}
	   if(isset($conf['add_st'])) {$sql .= ",article_subtitle";}
	   if(isset($conf['add_sm'])) {$sql .= ",article_summary";}
	   $sql .= " FROM ".DB_PREPEND."phpwcms_article WHERE article_cid=$slid";
	   $sql .= " AND article_public=1 AND article_aktiv=1 AND article_deleted=0";
	   $sql .= " AND article_begin < NOW() AND article_end > NOW()";
	
	   $result = _dbQuery($sql);
	
		  foreach($result as $row) {
		  $ai = $row['article_id'];
		  $at = $row['article_title'];
		  $ast = $row['article_subtitle'];
		  $asm = $row['article_summary'];
		  
		  $allmyhds .= $at.' '.$ast.' '.$asm.' ';
	
		  $sec_sql  = "SELECT SQL_CACHE acontent_html";
		  if(isset($conf['add_cpt'])) {$sec_sql .= ",acontent_title";}
		  if(isset($conf['add_cpst'])) {$sec_sql .= ",acontent_subtitle";}
		  if(isset($conf['add_cptximg'])) {$sec_sql .= ",acontent_text";}
		  $sec_sql .= " FROM ".DB_PREPEND."phpwcms_articlecontent WHERE acontent_aid=$ai";
		  $sec_sql .= " AND acontent_visible=1 AND acontent_trash=0";
	
		  $scd_result = _dbQuery($sec_sql);
	
			 foreach($scd_result as $scd_row) {
			 $allmycps .= $scd_row['acontent_html'].' ';
			 $allmycps .= $scd_row['acontent_title'].' ';
			 $allmycps .= $scd_row['acontent_subtitle'].' ';
			 $allmycps .= $scd_row['acontent_text'].' ';
			 }
		  }
	   }
	/*****************************************************************
	* JOIN ALL the text of content --> Titles, Summary, CPs
	*****************************************************************/
	$tagtext = $allmyhds . $allmycps;
	
	/*****************************************************************
	* add NEWS when set =1
	*****************************************************************/
	if ($conf['news_to_cloud'] == 1) {
	
		$news_sql  = "SELECT SQL_CACHE cnt_title, ";
		$news_sql .= "cnt_subtitle, cnt_teasertext, cnt_text";
		$news_sql .= " FROM ".DB_PREPEND."phpwcms_content WHERE cnt_status=1";
		$news_sql .= " AND cnt_livedate < NOW() AND cnt_killdate > NOW()";
	
		$news_result = _dbQuery($news_sql);
		
		foreach($news_result as $news_row) {
			$newscontent .= $news_row['cnt_title'].' ';
			$newscontent .= $news_row['cnt_subtitle'].' ';
			$newscontent .= $news_row['cnt_teasertext'].' ';
			$newscontent .= $news_row['cnt_text'].' ';
		}
		//article content plus news
		$tagtext = $tagtext . $newscontent;
	}
	
	
	/*****************************************************************
	and do some convertions
	*****************************************************************/
	$tagtext = clean_replacement_tags($tagtext);
	$tagtext = stripped_cache_content($tagtext);
	
	//delete not wantend and then str_all to lower
	if(seems_utf8($tagtext)) {
		$tagtext = strtolower_utf8( str_replace($conf['del_signs'], '', $tagtext) );
	} else {
		$tagtext = strtolower( str_replace($conf['del_signs'], '', $tagtext) );
	}
	
	$tagtext = explode(' ',$tagtext); //split in separate words
	$anzahl = array_count_values($tagtext); //count the words -- into new array
	$tags = array();
	
	switch ($conf['inc_or_ex']) {
		case '0':
			foreach($anzahl as $key => $tagword) {
				if($tagword >= $conf['min'] && (!in_array($key, $conf['exclude']))) { //look if the word counts the required minimum and is not in the exclude list
					if (strlen($key) >= $conf['min_chars']) { //ignore words on web site that are NOT longer than (chief inspector even longer) defined in: var min_chars
						$tags[$key] = $tagword; //put them in a new array
					} // else { $this_word_out[$key] = $tagword; }
				}
			}
		break;
	
		case '1':
			foreach($anzahl as $key => $tagword) {
				if($tagword >= $conf['min'] && (in_array($key, $conf['include']))) { //look if the word counts the required minimum and is not in the exclude list
					if (strlen($key) >= $conf['min_chars']) { //ignore words on web site that are NOT longer than (chief inspector even longer) defined in: var min_chars
						$tags[$key] = $tagword; //now put them in a new array
					} // else { $this_word_out[$key] = $tagword; }
				}
			}
		break;
	
		default:
		break;
	}
	
	
	if(!empty($tags)){
	   //unset($tags['phpwcms']); //if you want to override the value of words (in this case 'phpwcms'), uncomment it and put in your word
	   //$weight = count($tags);
	   $max_hits = max($tags); //tag with most hits
		 if(!empty($max_hits)) {
		  //$tags['phpwcms']=8; // put in again your deleted word and value from 4 lines above
	
		  switch ($conf['sort']) {
			 case 'asc':
				ksort($tags); //sort them alphabetically
			 break;
			 
			 case 'desc':
				krsort($tags); //sort them reverse alphabetically
			 break;
			 
			 case 'random':
				$keys = array_keys($tags);
				shuffle($keys);
				$random_words  = array();
				foreach ($keys as $key) {
				  $random_words[$key] = $tags[$key];
				}
				$tags = $random_words;
			 break;
			 
			 default:
			 break;
		  }
	
		  // FULL BLOWN CLOUDS LIKE HORNBLOWER
		  $tag_cloud = '<div class="'. $conf['class'] .'">';
			 foreach($tags as $key => $tagword) {
				$key = html_specialchars($key);
				// new maths by Heiko H.
				$percent = round(100 * $tagword / $max_hits,0);
				$size = ceil($percent/10);
				// prepare TC font size for CSS
				$tag_cloud .= '<a class="tcfs' . $size . '" href="' . PHPWCMS_URL . $landing . urlencode($key) . '">' . $key . '</a>';
				if ($conf['showCount']) {
				   $tag_cloud .= $conf['SC_before'] . $tagword . $conf['SC_after'];
				}
				$tag_cloud .= '	' . LF;
			 }
		  $tag_cloud .= '</div>';
		 }
	  }
	}
	return $tag_cloud;
}

function strtolower_utf8($tempString_in) {
	$tempString_out = utf8_decode($tempString_in);
	$tempString_out = strtolower($tempString_out);
	$tempString_out = utf8_encode($tempString_out);
	return $tempString_out;
}

if(!empty($content["all"]) && !(strpos($content["all"],'{TAGCLOUD:')===false)) {
	$content["all"] = preg_replace_callback('/\{TAGCLOUD:(.*?):(.*?):(.*?):{0,1}(\d+){0,1}\}/', 'make_cloud', $content['all']);
	$block['css']['tagcloud'] = 'specific/tagcloud.css';
}

?>