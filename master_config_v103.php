<?php
/**
 * 2016-12-02 SF - I want to remove the project juliet elements from this coding
 *
 * v1.03 - updated to sql_update_insert_generic v1.10
 * v1.02 - removed shopCartURL, added wwwredirection back in from v1.00
 *   2011-06-30 cleaned up _ROOT variables
 * v1.01 - Sam did some organization and defaulting, better positioning for function-related vars;
 * v1.00 - by Parker, good start
*/

//time function for benchmarking
if(!function_exists('gmicrotime')){
    /**
     * @param string $marker
     * @param array $options
     * @return void
     */
    function gmicrotime($marker='', $options=[]){
        #version 1.2, 2017-05-13

        extract($options);
        if(!isset($mem)) $mem = true; // || false, don't worry about memory
        if(!isset($format)) $format = 'array'; // || string

        global $mT;
        if($marker=='all') return $mT;

        list($usec, $sec) = explode(' ',microtime());
        $t=round((float)$usec + (float)$sec,6);

        if($format == 'string'){
            $value = $t;
        }else{
            $value = ['time'=>$t];
        }
        if($mem){
            $_mem = memory_get_usage();
            $_max = memory_get_peak_usage();
            if($format == 'string'){
                $value .= ":$mem:$max";
            }else{
                $value['memory'] = $_mem;
                $value['max'] = $_max;
            }
        }

        //store everything in this array
        $mT['all'][]=$value;

        //build associative 1-indexed array
        if(empty($mT['indexed'][$marker])){
            $mT['indexed'][$marker]=$value;
        }else{
            if(is_array($mT['indexed'][$marker])){
                $mT['indexed'][$marker][ count($mT['indexed'][$marker])+1 ]=$value;
            }else{
                $mT['indexed'][$marker][1]=array($mT['indexed'][$marker], $value);
            }
        }
    }
}
gmicrotime('initialize');
//standardize the date and time stamps for long or repeated queries
$dateStamp=date('Y-m-d H:i:s');
$timeStamp=preg_replace('/[^0-9]*/','',$dateStamp);
//server located in Dallas - note I have to flip savings time bit because it appears screwed up
$RelateBaseServerTZDifference = -5 - (date('I')?0:1);
$ctime = time();


//roots/locations
$FUNCTION_ROOT =			$_SERVER['DOCUMENT_ROOT'].'/functions';
$FEX_ROOT =				    $_SERVER['DOCUMENT_ROOT'].'/admin/file_explorer';
$CONSOLE_ROOT =			    $_SERVER['DOCUMENT_ROOT'].'/console';
$MASTER_COMPONENT_ROOT =    $_SERVER['DOCUMENT_ROOT'].'/components';
$COMPONENT_ROOT =           $_SERVER['DOCUMENT_ROOT'].'/components';
$EMAIL_ROOT =			    $_SERVER['DOCUMENT_ROOT'].'/emails';
$JULIET_COMPONENT_ROOT =    $_SERVER['DOCUMENT_ROOT'].'/components-juliet';
$PAGE_ROOT =                $_SERVER['DOCUMENT_ROOT'].'/pages';

//local functions
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
        unset($a['HTTP_SERVER_VARS'], $a['HTTP_ENV_VARS'], $a['HTTP_GET_VARS'], $a['HTTP_COOKIE_VARS'], $a['HTTP_SESSION_VARS'], $a['HTTP_POST_FILES'],$a['HTTP_POST_VARS']);
        print_r($a);
        unset($a);
        $out=ob_get_contents();
        $out=str_replace('[GLOBALS] => Array
 *RECURSION*
  ','',$out);

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
global $excludePageFromStats, $bufferBodyForEditableRegion, $MASTER_PASSWORD, $SUPER_MASTER_HOSTNAME, $monitorToken, $adminMode;
/*
if(!$excludePageFromStats) stats_collection(false,array('cnx'=>$SUPER_MASTER_HOSTNAME));
*/

//clear out the page if isolating editable regions
if($bufferBodyForEditableRegion)ob_end_clean();

//output response to pageload query from monitor
if($monitorToken)echo "\n".$monitorToken;

if($adminMode){
global $pJTemplateBlocksTool, $CMSBx;
if($pJTemplateBlocksTool && $a=$CMSBx['sections']){
$replace=md5($pJTemplateBlocksTool);
foreach($a as $n=>$v){
    $blocks[$v['block']][$n]=$v['method'];
}
foreach($blocks as $block=>$v){
ob_start();
foreach($v as $section=>$w){
?>&nbsp;<a href="#" title="CMSB edit section: <?php echo $section?>" onclick="g('CMSB-<?php echo $section;?>').onclick(); return false;"><img src="/images/i/icon_news.gif" alt="edit" width="8" height="9" align="absbottom" /></a><?php
}
    $out=ob_get_contents();
    ob_end_clean();
    $pJTemplateBlocksTool=str_replace('<!-- pJ.settings_toolbar.php-'.$block.' -->',$out,$pJTemplateBlocksTool);
}
}
    ?>
    <span id="<?php echo $replace?>_fill" style="display:none;"><?php echo $pJTemplateBlocksTool;?></span>
    <script language="javascript" type="text/javascript">
		g('<?php echo $replace;?>').innerHTML=g('<?php echo $replace;?>_fill').innerHTML;
    </script><?php
}
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
        $str = '';
        extract($options);
        $haveNumber = false;
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
if(!function_exists('iframe_shutdown')){
    //shutdown functions
    /* we eval this so any code optimizer doesn't rename the function */
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
					if(<?php echo $parentUnSubControl ? 'true' : 'false' ?>){
						eval('<?php echo $parentUnSubControl?>');
					}else{
						window.parent.document.getElementById('SubmitApplication').disabled=false;
						window.parent.document.getElementById('SubmitStatus1').innerHTML=' ';
					}
				}catch(e){ }
				/*** optional: ***/
				//window.parent.window.body.cursor.style='pointer';
            </script><?php
        }
        if(!$assumeErrorState){
            flush();
            return false; //that's all, folks
        }

        //handle errors
        ?><script>
			//for the end user - you can improve this rather scary-sounding message
			try{
				window.parent.g('ctrlSection').style.display='block';
			}catch(e){ }
			alert('We are sorry but there has been an abnormal error while submitting your information, and staff have been emailed.  Please try refreshing the page and entering your information again');
        </script><?php

        //we also mail that this has happened
        $mail='File: '.__FILE__."\n".'Line: '.__LINE__."\n";
        $mail.="There has been an abnormal shutdown in this page.  Attached are the environment variables:\n\n";
        $mail.=get_globals();
        //Page Output - normally we print out results after each SQL query for example
        if($store_html_output){
            $mail.=$store_html_output . "\n\n";
        }
        //Globals - you may find this unnecessary if your process outputting was good
        $printGlobals=true;

        //send email notification
        mail($developerEmail,'Abnormal shutdown', $mail, $fromHdrBugs);
        return true;
    }
}
if(!function_exists('store_html_output')){
    function store_html_output($buffer){
        //PHP sends the output buffer before shutting down (error or otherwise).  This catches the buffer prior to shutdown
        global $store_html_output;
        //$store_html_output=$buffer;
        return $buffer;
    }
}
if(!function_exists('addslashes_deep')){
    function addslashes_deep($value){
        $value = is_array($value) ?
            array_map('addslashes_deep', $value) :
            addslashes($value);
        return $value;
    }
}
if(!function_exists('stripslashes_deep')){
    function stripslashes_deep($value){
        $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
        return $value;
    }
}
if(!function_exists('get_systemname')){
    function get_systemname($n, $return=''){
        global $systemPageNames;
        $a=$systemPageNames[strtolower($n)];
        return ($return ? $a[$return] : $a);
    }
}
if(!function_exists('sun')){
    function sun($n=''){
        /*
        v1.00 2013-11-11: this is the most advanced version; for cnx, we are agnostic about .identity...
        */
        global $acct;
        if($_SESSION['admin']['userName']){
            extract($_SESSION['admin']);
            switch($n){
                case 'e': return $email;
                case 'fl': return $firstName . ' '. $lastName;
                case 'lf': return $lastName . ', '.$firstName;
                case 'lfi': return $lastName.', '.$firstName.($middleName?' '.substr($middleName,0,1).'.':'');
                default: return $userName;
            }
        }else if(($a=$_SESSION['cnx'][$acct]) && $_SESSION['systemUserName']){
            extract($a);
            switch($n){
                case 'e': return $email;
                case 'fl': return $firstName . ' '. $lastName;
                case 'lf': return $lastName . ', '.$firstName;
                case 'lfi': return $lastName.', '.$firstName.($middleName?' '.substr($middleName,0,1).'.':'');
                default: return $_SESSION['systemUserName'];
            }
        }else{
            return $GLOBALS['PHP_AUTH_USER'];
        }
    }
}

//standard functions - 2017-07-05, checked for duplicates and ordered
foreach( [
             'function_array_alter_table_v100',
             'function_array_merge_accurate_v100',
             'function_array_subkey_sort_v300',
             'function_array_transpose',
             'function_attach_download_v100',
             'function_callback_v101',
             'function_CMSB_v311',
             'function_enhanced_mail_v211',
             'function_enhanced_parse_url_v100',
             'function_generic5t_v100',
             'function_get_contents_v100',
             'function_get_file_assets_v100',
             'function_get_navstats_v110',
             'function_get_table_indexes_v101',
             'function_image_dims_v100',
             'function_is_logical_v100',
             'function_js_email_encryptor_v100',
             'function_metatags_i1_v102',
             'function_mm_v110',
             'function_mysql_declare_field_attributes_rtcs_v100',
             'function_mysql_declare_table_rtcs_v200',
             'function_navigate_v141a',
             'function_parse_query_v200',
             'function_pk_encode_decode',
             'function_prn',
             'function_q_v140',
             'function_quasi_resource_generic_v201',
             'function_rb_vars_v120',
             'function_relatebase_dataobjects_settings_v100',
             'function_replace_form_elements_v100',
             'function_set_priority_v110',
             'function_shopping_cart_v400',
             'function_site_track_v101',
             'function_sql_autoinc_text_v232',
             'function_sql_insert_update_generic_v110',
             'function_stats_collection_v120',
             'function_t_v111',
             'function_tabs_enhanced_v300',
             'function_xml_read_tags_v134',
             'group_pJ_v100',
             'group_text_functions_v100',
             'group_tree_functions_v100',
         ] as $function){
    require_once($FUNCTION_ROOT . '/' . $function . '.php');
}
gmicrotime('after_function_include');

$qx['useRemediation'] = true;
$qx['defCnxMethod'] = C_MASTER;


//2019-06-24 - Note:  CodeIgniter call reverses this, look for "addslashes_deep reversal" in comments in the template file
$extract = ['_POST'=>1, '_GET'=>1];
foreach($extract as $_GROUP => $clean){
    if(empty($GLOBALS[$_GROUP])) continue;
    if($clean){
        foreach($GLOBALS[$_GROUP] as $n => $v){
            $GLOBALS[$_GROUP][$n] = addslashes_deep($v);
        }
    }
    extract($GLOBALS[$_GROUP]);
}

if(false){
    //unused variables, these are still located in components
    $hideMembershipDirectoryLink =
    $hideEventCalendarLink =
    $hideContactUsLink =
    $hideSiteMapLink = true;
}

if(empty($_REQUEST['suppressSessionStart'])){
	if(!empty($sessionid)){
		$PHPSESSID = $sessionid;
		session_id($sessionid);
	}
	ob_start();
	session_start();
	$sessionid ? '' : $sessionid = session_id();
	ob_end_clean();
}
// Note: conflict between Juliet for submissions to index_01_exe.php - mode calls to root page(s) assume things related to session and shopping cart
if(!empty($_REQUEST['mode']) && !in_array($_SERVER['REQUEST_URI'], [ '/index_01_exe.php', '/_juliet_.editor.php', '/_juliet_.posts.php', '_juliet_.settings.php', '/cms3.11.php' ])){
    $mode = $_REQUEST['mode'];
    if($mode == 'retrieveSession'){
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
    }else if($mode == 'destroySession'){
        $_SESSION['shopCart']['default'] = array();
        unset($_SESSION['shopCartModified']);
    }else if($mode == 'modifySession'){
        $_SESSION['shopCartModified'] = $_POST['time'];
        $_SESSION['shopCart']['default'] = unserialize(base64_decode($_POST['modification']));
    }else{
        echo 'Unknown value mode=' . $mode;
    }
    exit;
}

if(isset($suppressWWWRedirection) && !empty($suppressWWWRedirection) && count(explode('.',$_SERVER['SERVER_NAME']))==2 /* substr($_SERVER['SERVER_NAME'],0,3)!=='www' */){
	$redirqs=preg_replace('/__page__=[^&]*/','',$_SERVER['QUERY_STRING']);
	$loc='http'.($secureProtocolPresent?'s':'').'://www.'.$_SERVER['SERVER_NAME'].($__page__?'/'.$__page__ : $_SERVER['PHP_SELF']).($redirqs ? '?'.$redirqs : '');
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$loc);
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
	<html><head>
	<title>301 Moved Permanently</title>
	</head><body>';
	?>
	<h1>Moved Permanently</h1>
	<p>The document has moved <a href="<?php echo $loc;?>">here</a>.</p>
	<?php
	echo '</body></html>';
	exit;
}

// 2017-07-24 SF Migrate everything to acct variable, avoid database username or password
if(empty($cnxKey)) $cnxKey=(isset($acct) ? $acct : $MASTER_USERNAME);
//shorthand
$my_cnx = !empty($_SESSION['cnx'][$cnxKey]) ? $_SESSION['cnx'][$cnxKey] : '';



if(isset($monitorToken) && $monitorToken==md5($MASTER_PASSWORD))echo "\n".$monitorToken."\n";

//thispage, thisfolder
if(!empty($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS']==404){
	//2009-04-24, new method: presumed 404 page masquerading as other page, get page from REQUEST_URI
	$a=explode('/',ltrim($_SERVER['REDIRECT_URL'],'/'));
	$thispage=$a[count($a)-1];
	if(count($a)>1){
		$thisfolder=$a[0];
	}else{
		$thisfolder='';
	}
	//2011-09-01 note implementation of thissubfolder
	if(count($a)>2){
		unset($thissubfolder);
		for($i=1;$i<=count($a)-2;$i++){
			$thissubfolder[]=$a[$i];
		}
		$thissubfolder=implode('/',$thissubfolder);
	}
	parse_str($_SERVER['REDIRECT_QUERY_STRING'],$a);
	//VERY IMPORTANT!! stop passage of _SESSION variables
	foreach(array('GLOBALS', '_SESSION', 'HTTP_SESSION_VARS', '_SERVER', 'HTTP_SERVER_VARS', 'PHP_AUTH_USER', 'PHP_AUTH_PW', '_ENV', 'HTTP_ENV_VARS') as $v)unset($a[$v]);
	@extract($a);
}else{
    //previous page/folder method
	if(!empty($thispage) && $thispage == '403.shtml'){
		mail('sam-git@samuelfullman.com','problem with 403.shtml','was caused when cgi symlink was not resolving to cgi_2.8.6','From: bugreports@'.$_SERVER['SERVER_NAME']);
	}
    if(empty($thispage) || empty($thisfolder)){
		//note the logic here in case page presented is from rewriteengine
        $a=preg_split('/\\\|\//',ltrim( !empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'],'/'));
		$thispage = $a[count($a)-1];
		if(count($a)>1){
			$thisfolder=$a[0];
		}else{
			$thisfolder='';
		}
		//2011-09-01 note implementation of thissubfolder
		if(count($a)>2){
			unset($thissubfolder);
			for($i=1;$i<=count($a)-2;$i++){
				$thissubfolder[]=$a[$i];
			}
			$thissubfolder=implode('/',$thissubfolder);
		}
    }
    //2016-12-02 SF concerned about writing global scope here - check on this
	parse_str($_SERVER['QUERY_STRING'],$a);
	unset($a['__page__']);
	if(!empty($a)){
		$_SERVER['QUERY_STRING']='';
		foreach($a as $n=>$v){
			$_SERVER['QUERY_STRING'].=$n.'='.urlencode(stripslashes($v)).'&';
		}
		$_SERVER['QUERY_STRING']=rtrim($_SERVER['QUERY_STRING'],'&');
	}else{
		$_SERVER['QUERY_STRING']='';
	}
}

$thispage=preg_replace('/\.(php|htm|html)$/i','',$thispage);
$thispage=strtolower($thispage);
if(!empty($langs['recognized'])){
	//2012-01-12: multi-language support - remove fr|de|es language fake folder
	if(in_array($thisfolder, array_keys($langs['recognized'])))$thisfolder='';
}

//redirect on login required pages; loginView=1 is the simplest login view
if(!empty($loginRequired) && $loginRequired == 1 && empty($_SESSION['identity'])){
	header('Location: /cgi/login.php?loginView='.($loginView ? $loginView : 1).'&src='.urlencode($_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '')));
	exit;
}

//useful concept but as of 2011-02-19 not used to my knowledge - SF
if(!empty($_SESSION['special']['requestSetCookie']) && is_array($_SESSION['special']['requestSetCookie'])){
	foreach($a as $n=>$v){
		continue; //not used
		setcookie($n,$v,time()+(3600*24*180*(is_null($v)?-1:1)),'/' /*,preg_replace('/^www\./i','',$_SERVER['HTTP_HOST'])*/);
		$_COOKIE[$n]=$v;
	}
	$_SESSION['special']['requestSetCookie']=array();
}


//THESE ARE TEMPORARY until we have a login system front and back
if(empty($_SESSION['systemUserName']) && !empty($_SERVER['PHP_AUTH_USER'])){
	$_SESSION['systemUserName']=$_SERVER['PHP_AUTH_USER'];
}

//session.special.[account name].adminMode is set separately
define('ADMIN_MODE_EDITOR',1);
define('ADMIN_MODE_DESIGNER',2);
define('ADMIN_MODE_GOD',256);


$adminMode = false;
if(isset($_REQUEST['adminMode']) && $_REQUEST['adminMode'] === '0'){
    unset($_SESSION['special'][$acct]['adminMode']);
}else if(!empty($_SESSION['special'][$acct]['adminMode'])){
    if(isset($_REQUEST['setAdminMode'])){
        /**
        adminMode=1 means editorMode, adminMode=2 means designerMode
         */
        if($_REQUEST['setAdminMode']){
            $_SESSION['special'][$acct]['adminMode'] = $adminMode = $_REQUEST['setAdminMode'];
        }else{
            unset($_SESSION['special'][$acct]['adminMode']);
        }
    }else{
        $adminMode = $_SESSION['special'][$acct]['adminMode'];;
    }
}

//global variables and arrays
$businessTitles = [
    'Owner','President/CEO','CIO','CFO','Marketing Director','Sales Manager','Sales Representative','Administrator','Other'
];
$canada = ['AB','BC','MB','NB','NF','NS','NT','ON','PE','PQ','QC','SK','YT'];
$militaryPOs = ['AA','AE','AP'];
$normalTitles = ['Mr.','Mrs.','Dr.','Rev.','Ms.'];


//function settings
if(!$qx['defCnxMethod'])$qx['defCnxMethod']=C_MASTER;
if(!isset($enhanced_mail['logmail']))$enhanced_mail['logmail']=true; //implement maillog by default

//emails
$siteRootEmailAccount='info';
$a=explode('.',$_SERVER['SERVER_NAME']);
$siteDomain=$a[count($a)-2] . '.' . $a[count($a)-1];
if(empty($fromHdrNormal))	$fromHdrNormal='From: '.$siteRootEmailAccount.'@'.$siteDomain;
if(empty($fromHdrNotices))	$fromHdrNotices='From: notices@'.$siteDomain;
if(empty($fromHdrBugs))		$fromHdrBugs='From: bugreports@'.$siteDomain;
if(empty($developerEmail))	$developerEmail='sam-git@samuelfullman.com';
if(empty($adminEmail))		$adminEmail=$siteRootEmailAccount.'@'.$siteDomain;

//location of shopping cart
/*
Moved to config.php
$shoppingCartURL = 'https://www.relatebase.com/c/cart/en/v410/index.php?sessionid='.($sessionid ? $sessionid : $GLOBALS['PHPSESSID']).'&acct='.$cartAcct.'&mid='.$cartModuleId;*/

