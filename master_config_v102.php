<?php
/*
v1.02 - removed shopCartURL, added wwwredirection back in from v1.00
	2011-06-30 cleaned up _ROOT variables
v1.01 - Sam did some organization and defaulting, better positioning for function-related vars; 
v1.00 - by Parker, good start
*/
if(!$cnxKey)$cnxKey=$MASTER_DATABASE;
$hideMembershipDirectoryLink = $hideEventCalendarLink = $hideContactUsLink = $hideSiteMapLink = true;

if(!$suppressSessionStart){
	if(strlen($sessionid)){
		$PHPSESSID=$sessionid;
		session_id($sessionid);
	}
	ob_start();
	session_start();
	$sessionid ? '' : $sessionid = session_id();
	ob_end_clean();
}
if($mode=='retrieveSession'){
    //2010-06-25: added for relatebase.com shopping cart (v3.00) to pull shopping cart remotely.  Note this will now work even if we're on a different physical server.  PHPSuExec makes it impossible to pass session variables from site to site
 	$s=array();
 	$sessionRootVars=array(
	'identity',
	'createDate',
	'creator',
	'editDate',
	'editor',
	'firstName',
	'middleName',
	'lastName',
	'email',
	'loginTime',
	'sessionIP',
	'systemUserName',
	'sessionKey'
	);
	foreach($sessionRootVars as $v) if(isset($_SESSION[$v])) $s[$v]=$_SESSION[$v];
	if(isset($_SESSION['cnx']))			$s['cnx']=$_SESSION['cnx'];
	if(isset($_SESSION['special']))		$s['special']=$_SESSION['special'];
	if(isset($_SESSION['shopCart']['default']))	$s['shopCart']['default']=$_SESSION['shopCart']['default'];
    echo ($_SESSION['shopCartModified'] ? $_SESSION['shopCartModified'] : time()-1).'^'.base64_encode(serialize($s));
    exit;
}else if($mode=='destroySession'){
    $_SESSION['shopCart']['default']=array();
    unset($_SESSION['shopCartModified']);
    exit;
}else if($mode=='modifySession'){
    $_SESSION['shopCartModified']=$_POST['time'];
    $_SESSION['shopCart']['default']=unserialize(base64_decode($_POST['modification']));
    exit;
}
if(!$suppressWWWRedirection && substr($_SERVER['SERVER_NAME'],0,3)!=='www'){
	$redirqs=preg_replace('/__page__=[^&]*/','',$_SERVER['QUERY_STRING']);
	header('Location: http'.($secureProtocolPresent?'s':'').'://www.'.$_SERVER['SERVER_NAME'].($__page__?'/'.$__page__ : $_SERVER['PHP_SELF']).($redirqs ? '?'.$redirqs : ''));
	exit;
}

//shorthand
$my_cnx=$_SESSION['cnx'][$cnxKey];

//time function for benchmarking
if(!function_exists('gmicrotime')){
	function gmicrotime($n=''){
		#version 1.1, 2007-05-09
		//store array of all calls
		global $mT;
		list($usec, $sec) = explode(' ',microtime());
		$t=round((float)$usec + (float)$sec,6);
		$mT['all'][]=$t;
		if($n)$mT[$n][]=$t;
		//return elapsed since last call (in the local array)
		$u=($n?$mT[$n]:$mT['all']);
		if(count($u)>1)return round(1000*($u[count($u)-1]-$u[count($u)-2]),6);
	}
	gmicrotime('initial');
}
$ctime=time();

//standardize the date and time stamps for long or repeated queries
$dateStamp=date('Y-m-d H:i:s');
$timeStamp=preg_replace('/[^0-9]*/','',$dateStamp);
$RelateBaseServerTZDifference= -5 - (date('I')?0:1); //server located in Dallas - note I have to flip savings time bit because it appears screwed up
$SERVER_CITY='Dallas';
$SERVER_STATE='Texas';
if($monitorToken==md5($MASTER_PASSWORD))echo "\n".$monitorToken."\n";

$a=explode('/',__FILE__);
if($useDevLogicForDatabase && $a[3]!=='public_html') $MASTER_DATABASE.='_'.$a[3];
$cognate=$a[3];

//thispage, thisfolder, now thisnode
if($_SERVER['REDIRECT_STATUS']==404){
	//2009-04-24, new method: presumed 404 page masquerading as other page, get page from REQUEST_URI
	$a=explode('/',ltrim($_SERVER['REDIRECT_URL'],'/'));
	$thispage=$a[
		$pJulietImplemented ? (count($a)>1 ? 1 : 0) : count($a)-1
	];
	if(count($a)>1){
		$thisfolder=$a[$pJulietImplemented ? 0 : count($a)-2];
	}else{
		$thisfolder='';
	}
	parse_str($_SERVER['REDIRECT_QUERY_STRING'],$a);
	//VERY IMPORTANT!! stop passage of _SESSION variables
	$securityUnsets=array('GLOBALS', '_SESSION', 'HTTP_SESSION_VARS', '_SERVER', 'HTTP_SERVER_VARS', 'PHP_AUTH_USER', 'PHP_AUTH_PW', '_ENV', 'HTTP_ENV_VARS');
	foreach($securityUnsets as $v)unset($a[$v]);
	@extract($a);
}else{
    //previous page/folder method
    if(!strlen($thispage) || !isset($thisfolder)){
		//note the logic here in case page presented is from rewriteengine
        $a=preg_split('/\\\|\//',ltrim($_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'],'/'));
		$thispage=$a[
			$pJulietImplemented ? (count($a)>1 ? 1 : 0) : count($a)-1
		];
		if(count($a)>1){
			$thisfolder=$a[$pJulietImplemented ? 0 : count($a)-2];
		}else{
			$thisfolder='';
		}
    }
	parse_str($_SERVER['QUERY_STRING'],$a);
	unset($a['__page__']);
	if(!empty($a)){
		$_SERVER['QUERY_STRING']='';
		foreach($a as $n=>$v){
			$_SERVER['QUERY_STRING'].=$n.'='.urlencode(stripslashes($v)).'&';
		}
		$_SERVER['QUERY_STRING']=rtrim($_SERVER['QUERY_STRING'],'&');
	}
}
if($removeThispageExtension)$thispage=preg_replace('/\.(php|htm|html)$/i','',$thispage);
if($lowercaseThispage)$thispage=strtolower($thispage);

//redirect on login required pages; loginView=1 is the simplest login view
if($loginRequired==1 && !$_SESSION['identity']){
	header('Location: /cgi/login.php?loginView='.($loginView ? $loginView : 1).'&src='.urlencode($_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '')));
	exit;
}

//useful concept but as of 2011-02-19 not used to my knowledge - SF
if($a=$_SESSION['special']['requestSetCookie']){
	foreach($a as $n=>$v){
		continue; //not used
		setcookie($n,$v,time()+(3600*24*180*(is_null($v)?-1:1)),'/' /*,preg_replace('/^www\./i','',$_SERVER['HTTP_HOST'])*/);
		$_COOKIE[$n]=$v;
	}
	$_SESSION['special']['requestSetCookie']=array();
}

//THESE ARE TEMPORARY until we have a login system front and back
if(!$_SESSION['systemUserName'] && $_SERVER['PHP_AUTH_USER']){
	$_SESSION['systemUserName']=$_SERVER['PHP_AUTH_USER'];
}

//browser detect
if(preg_match('/^Mozilla\/4/i',$_SERVER['HTTP_USER_AGENT'])){
	$browser='IE';
}else if(preg_match('/^Mozilla\/5/i',$_SERVER['HTTP_USER_AGENT'])){
	$browser='Moz';
}else if(!stristr($_SERVER['HTTP_USER_AGENT'],'Gigabot') && !stristr($_SERVER['HTTP_USER_AGENT'],'msnbot')){
	$browserUnknown=true;
	$browser='Moz'; #assume
}

//session.special.[account name].adminMode is set separately
define('ADMIN_MODE_EDITOR',1);
define('ADMIN_MODE_DESIGNER',2);
if(isset($adminMode) && $adminMode==='0'){
	unset($_SESSION['special'][$MASTER_DATABASE]['adminMode']);
	$adminMode=false;
}else if($n=$_SESSION['special'][$MASTER_DATABASE]['adminMode']){
	if($setAdminMode){
		/*
		2011-07-18
		from now on adminMode=1 means editorMode, adminMode=2 means designerMode
		*/
		$_SESSION['special'][$MASTER_DATABASE]['adminMode']=$adminMode=$setAdminMode;
	}
	$adminMode=$n;
}else{
	$adminMode=false;
}

//global variables and arrays
if(!$businessTitles)$businessTitles=array('Owner','President/CEO','CIO','CFO','Marketing Director','Sales Manager','Sales Representative','Administrator','Other');
$canada = array('AB','BC','MB','NB','NF','NS','NT','ON','PE','PQ','QC','SK','YT');
$militaryPOs = array('AA','AE','AP');
$normalTitles = array('Mr.','Mrs.','Dr.','Rev.','Ms.');

//roots/locations
$FUNCTION_ROOT=			$_SERVER['DOCUMENT_ROOT'].'/functions';
$FEX_ROOT=				$_SERVER['DOCUMENT_ROOT'].'/admin/file_explorer';
$CONSOLE_ROOT=			$_SERVER['DOCUMENT_ROOT'].'/console';
$MASTER_COMPONENT_ROOT=
$COMPONENT_ROOT=		$_SERVER['DOCUMENT_ROOT'].'/components';
$LOCAL_COMPONENT_ROOT=	$_SERVER['DOCUMENT_ROOT'].'/components-local';
$EMAIL_ROOT=			$_SERVER['DOCUMENT_ROOT'].'/emails';
$PROTOCOL_ROOT=			'/home/phplib/public_html/devteam/php/protocols';
$SNIPPET_ROOT=			'/home/phplib/public_html/devteam/php/snippets';
$SQL_ROOT=				'/home/phplib/public_html/devteam/php/sql';

//standard functions
if(!function_exists('CMSB'))
require($FUNCTION_ROOT.'/function_CMSB_v'.($CMSBVersion ? $CMSBVersion : '300').'.php');
if(!function_exists('enhanced_mail'))
require($FUNCTION_ROOT.'/function_enhanced_mail_v210.php');
if(!function_exists('enhanced_parse_url'))
require($FUNCTION_ROOT.'/function_enhanced_parse_url_v100.php');
if(!function_exists('get_file_assets'))
require($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
if(!function_exists('image_dims'))
require($FUNCTION_ROOT.'/function_image_dims_v100.php');
if(!function_exists('js_email_encryptor'))
require($FUNCTION_ROOT.'/function_js_email_encryptor_v100.php');
if(!function_exists('metatags_i1'))
require($FUNCTION_ROOT.'/function_metatags_i1_v101.php');
if(!function_exists('pk_encode'))
require($FUNCTION_ROOT.'/function_pk_encode_decode.php');
if(!function_exists('prn'))
require($FUNCTION_ROOT.'/function_prn.php');
if(!function_exists('q'))
require($FUNCTION_ROOT.'/function_q_v130.php');
if(!function_exists('t'))
require($FUNCTION_ROOT.'/function_t_v111.php');
if(!function_exists('site_track'))
require($FUNCTION_ROOT.'/function_site_track_v101.php');
if(!function_exists('sql_insert_update_generic'))
require($FUNCTION_ROOT.'/function_sql_insert_update_generic_v101.php');
if(!function_exists('stats_collection'))
require($FUNCTION_ROOT.'/function_stats_collection_v120.php');
if(!function_exists('sql_autoinc_text'))
require($FUNCTION_ROOT.'/function_sql_autoinc_text_v232.php');
if(!function_exists('subkey_sort'))
require($FUNCTION_ROOT.'/function_array_subkey_sort_v203.php');
if(!function_exists('mysql_declare_table_rtcs'))
require($FUNCTION_ROOT.'/function_mysql_declare_table_rtcs_v200.php');
if(!function_exists('tree_functions'))
require($FUNCTION_ROOT.'/group_tree_functions_v100.php');
if(!function_exists('get_table_indexes'))
require($FUNCTION_ROOT.'/function_get_table_indexes_v101.php');
if(!function_exists('get_contents'))
require($FUNCTION_ROOT.'/function_get_contents_v100.php');
if(!function_exists('shopping_cart'))
require($FUNCTION_ROOT.'/function_shopping_cart_v400.php');
if(!function_exists('generic5t'))
require($FUNCTION_ROOT.'/function_generic5t_v100.php');
if(!function_exists('q'))
require($FUNCTION_ROOT.'/function_q_v130.php');
if(!function_exists('prn'))
require($FUNCTION_ROOT.'/function_prn.php');
if(!function_exists('xml_read_tags'))
require($FUNCTION_ROOT.'/function_xml_read_tags_v134.php');
if(!function_exists('sql_insert_update_generic'))
require($FUNCTION_ROOT.'/function_sql_insert_update_generic_v100.php');
if(!function_exists('sql_autoinc_text'))
require($FUNCTION_ROOT.'/function_sql_autoinc_text_v232.php');
if(!function_exists('array_transpose'))
require($FUNCTION_ROOT.'/function_array_transpose.php');
if(!function_exists('array_merge_accurate'))
require($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
if(!function_exists('quasi_resource_generic'))
require($FUNCTION_ROOT.'/function_quasi_resource_generic_v201.php');
if(!function_exists('replace_form_elements'))
require($FUNCTION_ROOT.'/function_replace_form_elements_v100.php');
if(!function_exists('enhanced_mail'))
require($FUNCTION_ROOT.'/function_enhanced_mail_v210.php');
if(!function_exists('navigate'))
require($FUNCTION_ROOT.'/function_navigate_v141a.php');
if(!function_exists('callback'))
require($FUNCTION_ROOT.'/function_callback_v101.php');
if(!function_exists('t'))
require($FUNCTION_ROOT.'/function_t_v111.php');
if(!function_exists('get_navstats'))
require($FUNCTION_ROOT.'/function_get_navstats_v110.php');
if(!function_exists('parse_query'))
require($FUNCTION_ROOT.'/function_parse_query_v200.php');
if(!function_exists('relatebase_dataobjects_settings'))
require($FUNCTION_ROOT.'/function_relatebase_dataobjects_settings_v100.php');
if(!function_exists('set_priority'))
require($FUNCTION_ROOT.'/function_set_priority_v110.php');
if(!function_exists('mysql_declare_field_attributes_rtcs'))
require($FUNCTION_ROOT.'/function_mysql_declare_field_attributes_rtcs_v100.php');
if(!function_exists('mysql_declare_table_rtcs'))
require($FUNCTION_ROOT.'/function_mysql_declare_table_rtcs_v200.php');
if(!function_exists('rb_vars'))
require($FUNCTION_ROOT.'/function_rb_vars_v120.php');
if(!function_exists('get_table_indexes'))
require($FUNCTION_ROOT.'/function_get_table_indexes_v101.php');
if(!function_exists('get_file_assets'))
require($FUNCTION_ROOT.'/function_get_file_assets_v100.php');
if(!function_exists('get_contents'))
require($FUNCTION_ROOT.'/function_get_contents_v100.php');
if(!function_exists('tree_functions'))
require($FUNCTION_ROOT.'/group_tree_functions_v100.php');
if(!function_exists('text_functions'))
require($FUNCTION_ROOT.'/group_text_functions_v100.php');
if(!function_exists('array_alter_table'))
require($FUNCTION_ROOT.'/function_array_alter_table_v100.php');
if(!function_exists('subkey_sort'))
require($FUNCTION_ROOT.'/function_array_subkey_sort_v203.php');

//function settings
$MASTER_PASSWORD=generic5t($MASTER_PASSWORD,'decode', array('super'=>1));
if(!$qx['defCnxMethod'])$qx['defCnxMethod']=C_MASTER;
if(!isset($enhanced_mail['logmail']))$enhanced_mail['logmail']=true; //implement maillog by default

//emails
$siteRootEmailAccount='info';
$a=explode('.',$_SERVER['SERVER_NAME']);
$siteDomain=$a[count($a)-2] . '.' . $a[count($a)-1];
if(!$fromHdrNormal)		$fromHdrNormal='From: '.$siteRootEmailAccount.'@'.$siteDomain;
if(!$fromHdrNotices)	$fromHdrNotices='From: notices@'.$siteDomain;
if(!$fromHdrBugs)		$fromHdrBugs='From: bugreports@'.$siteDomain;
if(!$developerEmail)	$developerEmail='sam.fullman@verizon.net';
if(!$adminEmail)		$adminEmail=$siteRootEmailAccount.'@'.$siteDomain;

//location of shopping cart
/*
Moved to config.php
$shoppingCartURL = 'https://www.relatebase.com/c/cart/en/v410/index.php?sessionid='.($sessionid ? $sessionid : $GLOBALS['PHPSESSID']).'&acct='.$cartAcct.'&mid='.$mid;*/


if(!function_exists('broken_integrity')){
	function broken_integrity(){
		global $fl, $ln, $developerEmail, $fromHdrBugs;
		mail($developerEmail,'Broken DB integrity on file='.$fl.', line='.$ln,get_globals(),$fromHdrBugs);
	}
}
if(!function_exists('n')){
	//outputs
	define('intifpossible','_1',false);
	define('_intifpossible',1,false);
	define('blankifzero','_2',false);
	define('_blankifzero',2,false);
	
	function n(){
		$knownConstants=array('_1','_2');
		foreach(func_get_args() as $v){
			if(in_array($v,$knownConstants)){
				$constants[]=(int) str_replace('_','',$v);
			}else if(is_numeric($v)){
				$outputs[]=$v;
			}
		}
		$out=$outputs[0];
		if(in_array(_intifpossible,$constants)){
			$out=preg_replace('/0+$/','',$out);
			$out=preg_replace('/\.$/','',$out);
		}
		if(in_array(_blankifzero,$constants)){
			if(!$out)$out='';
		}
		return $out;
	}
}
if(!function_exists('get_globals')){
	function get_globals($msg=''){
		ob_start();
		//snapshot of globals
		$a=$GLOBALS;
		//unset redundant nodes
		unset($a['HTTP_SERVER_VARS'], $a['HTTP_ENV_VARS'], $a['HTTP_GET_VARS'], $a['HTTP_COOKIE_VARS'], $a['HTTP_SESSION_VARS'], $a['HTTP_POST_FILES'], $a['GLOBALS']);
		print_r($a);
		unset($a);
		$out=ob_get_contents();
		ob_end_clean();
		return $msg.$out;
	}
}
if(!function_exists('error_alert')){
	function error_alert($x,$options=array()){
		global $assumeErrorState;
		@extract($options);
		if(!is_array($options))$continue=$options; //legacy
		?><script language="javascript" type="text/javascript">
		<?php if($callback && !$runAfter){ echo $callback; } ?>
		alert('<?php echo $x?>');
		<?php if($callback && $runAfter){ echo $callback; } ?>
		</script><?php
		if(!$continue){
			$assumeErrorState=false;
			exit;
		}
	}
}
if(!function_exists('error_alert_flashloader')){
	function error_alert_flashloader($msg, $options=array()){
		global $assumeErrorState, $suppressNormalIframeShutdownJS;
		$assumeErrorState=false;
		$suppressNormalIframeShutdownJS=true;
		echo str_replace("\t",'',$msg);
		exit;
	}
}
if(!function_exists('valid_email')){
	function valid_email($x){
		if(preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/',$x))return true;
		return false;
	}
}
if(!function_exists('page_end')){
	function page_end(){
		//2007-01-16: add stats collection version 1.00
		global $excludePageFromStats, $bufferBodyForEditableRegion, $MASTER_PASSWORD, $SUPER_MASTER_HOSTNAME, $monitorToken;
		/*
		if(!$excludePageFromStats) stats_collection(false,array('cnx'=>$SUPER_MASTER_HOSTNAME));
		*/
	
		//clear out the page if isolating editable regions
		if($bufferBodyForEditableRegion)ob_end_clean();
		
		//output response to pageload query from monitor
		if($monitorToken)echo "\n".$monitorToken;
	}
}
if(!function_exists('h')){
	function h($v){
		return htmlspecialchars($v, ENT_COMPAT, 'ISO-8859-15');
	}
}
if(!function_exists('unhtmlentities')){
	function unhtmlentities($string) {
	   $trans_tbl = get_html_translation_table(HTML_ENTITIES);
	   $trans_tbl = array_flip($trans_tbl);
	   return strtr($string, $trans_tbl);
	}
}
if(!function_exists('parse_number')){
	function parse_number($n,$options=array()){
		for($i=0;$i<strlen($n);$i++){
			if(!is_numeric(substr($n,$i,1))){
				if($haveNumber){
					break;
				}else{
					continue;
				}
			}
			$str.=substr($n,$i,1);
			$haveNumber=true;
		}
		return ltrim($str,'0');
	}
}
//shutdown functions
/* we eval this so any code optimizer doesn't rename the function */
eval('
if(!function_exists(\'iframe_shutdown\')){
function iframe_shutdown(){
	/*
	version 1.01 2007-03-21 @6:21AM - cleaned things up and started depending on external fctns like get_globals(); this version was used in jboyce.com
	*/
	global $store_html_output, $assumeErrorState, $parentUnSubControl, $suppressNormalIframeShutdownJS, $developerEmail,$fromHdrBugs;
	if(!$suppressNormalIframeShutdownJS){
		?><script>
		//notify the waiting parent of success, prevent timeout call of function
		window.parent.submitting=false;
		try{
			if(<?php echo $parentUnSubControl ? \'true\' : \'false\' ?>){
				eval(\'<?php echo $parentUnSubControl?>\');
			}else{
				window.parent.document.getElementById(\'SubmitApplication\').disabled=false;
				window.parent.document.getElementById(\'SubmitStatus1\').innerHTML=\' \';
			}
		}catch(e){ }
		/*** optional: ***/
		//window.parent.window.body.cursor.style=\'pointer\';
		</script><?php
	}
	if(!$assumeErrorState){
		flush();
		return false; //that\'s all, folks
	}

	//handle errors
	?><script>
	//for the end user - you can improve this rather scary-sounding message
	try{
		window.parent.g(\'ctrlSection\').style.display=\'block\';
	}catch(e){ }
	alert(\'We are sorry but there has been an abnormal error while submitting your information, and staff have been emailed.  Please try refreshing the page and entering your information again\');
	</script><?php

	//we also mail that this has happened
	$mail=\'File: \'.__FILE__."\n".\'Line: \'.__LINE__."\n";
	$mail.="There has been an abnormal shutdown in this page.  Attached are the environment variables:\n\n";
	$mail.=get_globals();
	//Page Output - normally we print out results after each SQL query for example
	if($store_html_output){
		$mail.=$store_html_output . "\n\n";
	}
	//Globals - you may find this unnecessary if your process outputting was good
	$printGlobals=true;
	
	//send email notification
	mail($developerEmail,\'Abnormal shutdown\', $mail, $fromHdrBugs);
	return true;
}
}
if(!function_exists(\'store_html_output\')){
function store_html_output($buffer){
	//PHP sends the output buffer before shutting down (error or otherwise).  This catches the buffer prior to shutdown
	global $store_html_output;
	//$store_html_output=$buffer;
	return $buffer;
}
}
');

eval('
if(!function_exists(\'stripslashes_deep\')){
function stripslashes_deep($value){
	$value = is_array($value) ? array_map(\'stripslashes_deep\', $value) : stripslashes($value);
	return $value;
}
}
');

?>