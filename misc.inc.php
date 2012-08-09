<?php
/* All functions contained herein will be general use functions */

/* 
Regex to make sure a valid URL is in the config before offering options for contact lookups
http://www.php.net/manual/en/function.preg-match.php#93824

Example Usage:
	if(isValidURL("http://test.com"){//do something}

*/

function isValidURL($url){
	$urlregex="((https?|ftp)\:\/\/)?"; // SCHEME
	$urlregex.="([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
	$urlregex.="([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
	$urlregex.="(\:[0-9]{2,5})?"; // Port
	$urlregex.="(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
	$urlregex.="(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
	$urlregex.="(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 
	if(preg_match("/^$urlregex$/",$url)){return true;}
}

//Convert hex color codes to rgb values
function html2rgb($color){
	if($color[0]=='#'){
		$color=substr($color,1);
	}
	if(strlen($color)==6){
		list($r,$g,$b)=array($color[0].$color[1],$color[2].$color[3],$color[4].$color[5]);
	}elseif(strlen($color)==3){
		list($r,$g,$b)=array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	}else{
		return false;
	}
	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array($r, $g, $b);
}

/*
Used to ensure a properly formatted url in use of instances header("Location")

Example usage:
	header("Location: ".redirect());
	exit;
			- or -
	header("Location: ".redirect('storageroom.php'));
	exit;
			- or -
	$url=redirect("index.php?test=23")
	header("Location: $url");
	exit;
*/
function path(){
	$path=explode("/",$_SERVER['REQUEST_URI']);
	unset($path[(count($path)-1)]);
	$path=implode("/",$path);
	return $path;
}
function redirect($target = null) {
	// No argument was passed.  If a referrer was set, send them back to whence they came.
	if(is_null($target)){
		if(isset($_SERVER["HTTP_REFERER"])){
			return $_SERVER["HTTP_REFERER"];
		}else{
			// No referrer was set so send them to the root application directory
			$target=path();
		}
	}else{
		//Try to ensure that a properly formatted uri has been passed in.
		if(substr($target, 4)!='http'){
			//doesn't start with http or https check to see if it is a path
			if(substr($target, 1)!='/'){
				//didn't start with a slash so it must be a filename
				$target=path()."/".$target;
			}else{
				//started with a slash let's assume they know what they're doing
				$target=path().$target;
			}
		}else{
			//Why the heck did you send a full url here instead of just doing a header?
			return $target;
		}
	}
	if($_SERVER["HTTPS"]=='on'){
		$url = "https://".$_SERVER['HTTP_HOST'].$target;
	}else{
		$url = "http://".$_SERVER['HTTP_HOST'].$target;
	}
	return $url;
}

// search haystack for needle and return an array of the key path,
// FALSE otherwise.
// if NeedleKey is given, return only for this key
// mixed ArraySearchRecursive(mixed Needle,array Haystack[,NeedleKey[,bool Strict[,array Path]]])

function ArraySearchRecursive($Needle,$Haystack,$NeedleKey="",$Strict=false,$Path=array()) {
	if(!is_array($Haystack))
		return false;
	foreach($Haystack as $Key => $Val) {
		if(is_array($Val)&&$SubPath=ArraySearchRecursive($Needle,$Val,$NeedleKey,$Strict,$Path)) {
			$Path=array_merge($Path,Array($Key),$SubPath);
			return $Path;
		}elseif((!$Strict&&$Val==$Needle&&$Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))||($Strict&&$Val===$Needle&&$Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))) {
			$Path[]=$Key;
			return $Path;
		}
	}
	return false;
}

/*
 * Sort multidimentional array
 *
 * $array = sort2d ( $array, 'key to sort on')
 */
function sort2d ($array, $index){
	//Create array of key and label to sort on.
	foreach(array_keys($array) as $key){$temp[$key]=$array[$key][$index];}
	//Case insensative natural sorting of temp array.
	natcasesort($temp);
	//Rebuild original array using the newly sorted order.
	foreach(array_keys($temp) as $key){$sorted[$key]=$array[$key];}
	return $sorted;
}  

/*
 * Language internationalization slated for v2.0
 *
 */
if( isset( $locale ) ) {
/*
	// the menu is included into every page so it will never get called directly
	include_once("languages/english/sidebar.inc.php");
	// test to see if we have added internationalization for a particular page at load and load english first
	if(file_exists("languages/english/".basename($_SERVER['PHP_SELF']))){
		include_once("languages/english/".basename($_SERVER['PHP_SELF']));
	}
	// if language is set to something other than english check for lang files for it then load after
	// this will allow only the values that need to be updated to be done so
	if($lang!="english"){
		if(file_exists("languages/$lang/".basename($_SERVER['PHP_SELF']))){
			include_once("languages/$lang/".basename($_SERVER['PHP_SELF']));
		}
	}
*/

/*	Replacing the above section with gettext compatible locales
*/
	setlocale( LC_ALL, $locale );
	
	bindtextdomain( "openDCIM", "./locale" );
	
	if ( isset( $codeset ) )
		bind_textdomain_codeset( "openDCIM", $codeset );
	
	textdomain( "openDCIM" );
}

/*
	Check if we are doing a new install or an upgrade has been applied.  
	If found then force the user into only running that function.

	To bypass the installer check from running, simply add
	$devMode = true;
	to the db.inc.php file.
*/

if(isset($devMode)&&$devMode){
	// Development mode, so don't apply the upgrades
}else{
	if(file_exists("install.php") && basename($_SERVER['PHP_SELF'])!="install.php" ){
		// new installs need to run the install first.
		header("Location: ".redirect('install.php'));
	}
}
?>
