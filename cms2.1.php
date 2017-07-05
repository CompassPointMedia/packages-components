<?php
if(strlen($sessionid)) session_id($sessionid);
session_start();
$sessionid ? '' : $sessionid = session_id();
$bufferDocument=true;


require('./config.php'); //for all

$qx['defCnxMethod']=C_MASTER;


switch($mode){
	case 'emailEmergency':
		mail($developerEmail,'error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		$assumeErrorState=false;
	break;
	case 'CMSBEdit':

		ob_start();
		prn($_SERVER['QUERY_STRING']);
		prn($_POST);
		
		// new shutdown coding
		$assumeErrorState=true;
		register_shutdown_function('iframe_shutdown');
		ob_start('store_html_output');
		$excludePageFromStats=true;

		/* 2009-01-08: added the login feature */
		if($logout){
			unset($_SESSION['special'][$MASTER_DATABASE]['adminMode']);
			?><div id="loginSection">
				<h3>CMSB Editor Sign-in</h3>
				<input name="UN" type="text" id="UN" /><br />
				<input name="PW" type="password" id="PW" /><br />
				<input type="submit" name="Submit" value="Sign In" />
				&nbsp;&nbsp;
				<input type="button" name="Button" value="Cancel" onClick="window.close();" />
			</div>
			<script language="javascript" type="text/javascript">
			window.parent.g('loginSection').innerHTML=document.getElementById('loginSection').innerHTML;
			window.parent.g('loginSection').style.display='block';
			window.parent.g('CMSBSection').style.display='none';
			</script><?php
		}else if(isset($UN)){
			if(strlen($UN) && strlen($PW) && strtolower($UN)==strtolower($MASTER_USERNAME) && stripslashes($PW)==$MASTER_PASSWORD){
				$_SESSION['special'][$MASTER_DATABASE]['adminMode']=1;
				?><script language="javascript" type="text/javascript">
				window.parent.g('loginSection').innerHTML='';
				window.parent.g('loginSection').style.display='none';
				window.parent.g('CMSBSection').style.display='block';
				window.parent.g('logoutLink').style.visibility='visible'
				window.parent.CMSBLoad();
				</script><?php
			}else{
				error_alert('Your user name and password is not correct');
			}
		}else{
			CMSBUpdate();
		}
		$assumeErrorState=false;
		exit;
}

if($logout=='1'){
	unset($_SESSION['special'][$MASTER_DATABASE]['adminMode']);
	header('Location: '.stripslashes($src));
	?>
	redirecting..
	<script>
	window.location='<?php echo stripslashes($src)?>';
	</script><?php
	exit;
}else if($UN==$MASTER_USERNAME && $PW==$MASTER_PASSWORD){
	$_SESSION['special'][$MASTER_DATABASE]['adminMode']=1;
	$location=($src ? stripslashes($src) : '/');
	header('Location: '.$location);
	?><script>window.location='<?php echo $location?>'</script><?php
	exit;
}else if(strlen($UN.$PW)){
	$error=true;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CMS Editor <?php echo !$adminMode ? ' : Sign In':''?></title>

<link href="site-local/undohtml.css" type="text/css" rel="stylesheet" />
<link href="site-local/stl_simple.css" type="text/css" rel="stylesheet" />
<style type="text/css">
body{
	background-image:none;
	margin:5px 20px;
	min-width:600px;
	}
#slideshow{
	float:right;
	padding:0px 0px 15px 15px;
	}
<?php if($adminMode){ ?>
#CMSBSection{
	display:block;
	}
#loginSection{
	display:none;
	}
<?php }else{ ?>
#CMSBSection{
	display:none;
	}
#loginSection{
	display:block;
	margin:0px auto;
	}
<?php } ?>
</style>

<script id="jsglobal" language="JavaScript" type="text/javascript" src="Library/js/global_04_i1.js"></script>
<script id="jscommon" language="JavaScript" type="text/javascript" src="Library/js/common_04_i1.js"></script>
<script id="jscommon" language="JavaScript" type="text/javascript" src="Library/js/forms_04_i1.js"></script>
<script id="3rdpartyfckeditor" type="text/javascript" src="Library/fck6/fckeditor.js"></script>
<?php if(true){ ?>
<script id="jslocal" language="JavaScript" type="text/javascript" src="/site-local/local.js"></script>
<?php }?>
<script language="javascript" type="text/javascript">
//source for editing
var cmsfolder=<?php echo $_GET['thisfolder'] ? "'".$_GET['thisfolder']."'" : 'window.opener.thisfolder'?>;
var cmspage=<?php echo $_GET['thispage'] ? "'".strtolower($_GET['thispage'])."'" : 'window.opener.thispage'?>;
if(cmspage)cmspage==cmspage.toLowerCase();
var cmsquery=<?php echo $_GET['cmsquery'] ? "'".$_GET['cmsquery']."'" : 'window.opener.location+\'\''?>;
cmsquery=cmsquery.toLowerCase();
var cmsquerypassed=<?php echo $_GET['cmsquery'] ? 'true' : 'false'?>;
var cmssection='<?php echo $_GET['thissection']?>';
var cmsOriginalPagePresent=true;


var CMSBGetNativeStyleSheets='popup';
var HTML='';
var editorCreated=false;
var editorEmailsSent=0;
function createEditor(field, container){
	if(editorCreated)return;
	var fck = new FCKeditor(field);
	var sBasePath= '/Library/fck6/';
	fck.BasePath= sBasePath ;
	fck.Value=g(container).innerHTML;
	fck.ToolbarSet = "xTransitional";
	fck.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
	fck.Height = 350 ;
	g(container).innerHTML = fck.CreateHtml();
	g(container).style.visibility='visible';
	editorCreated=true;
}
function CMSBLoad(){
	/*
	coding for Kenai resort - shut down the slideshow
	*/
	try{
		// [no slideshows in editor right now] if(window.opener.running)window.opener.startStop();
	}catch(e){}

	/* --------- I COULDN'T GET THIS CODING TO WORK ---------------
	transferHTML=window.opener.g(cmssection);
	//so we can see what is happening
	g('CMSContainer').style.visibility='visible';
	for(var i=0; i<transferHTML.childNodes.length; i++){
		alert(transferHTML.childNodes[i] + ': '+(transferHTML.childNodes[i].innerHTML?transferHTML.childNodes[i].innerHTML:'blank'));
		g('CMSContainer').appendChild(transferHTML.childNodes[i]);
		alert('done');
		return;
	}
	---------------------------------------------------------------*/
	
	//THIS IS THE INNERHTML METHOD - BUT I CAN'T SKIP NODES I WANT TO GRAPHICALLY REPRESENT ANOTHER WAY
	HTML=window.opener.g(cmssection).innerHTML;
	HTML=HTML.replace(/src="images\//g,'src="/images/');
	g('CMSContainer').innerHTML=HTML;
	createEditor('CMS','CMSContainer');
	setTimeout('CMSUpdater()',2000);
}
var oEditor=null;
function CMSUpdater(){
	if(!oEditor)oEditor=FCKeditorAPI.GetInstance('CMS');
	if(oEditor.IsDirty()){
		detectChange=1;
		g('CMSBUpdate').disabled=false;
		
		try{ //------------------------
		comparepage=(window.opener.thispage ? window.opener.thispage.toLowerCase() : '');
		if(cmspage==comparepage && cmsfolder==window.opener.thisfolder && cmsquery==(cmsquerypassed ? window.opener.cmsquery : (window.opener.location+'').toLowerCase())){
			cmsOriginalPagePresent=true;
			window.opener.g(cmssection).innerHTML=(oEditor.GetHTML(true));
		}else{
			cmsOriginalPagePresent=false;
		}
		}catch(e){ } //-----------------
	}
	setTimeout('CMSUpdater()',350);
}
function CMSBClose(){
	if(oEditor.IsDirty() && !confirm('You have made change and clicking OK will lose those changes. Continue?')){
		return false;
	}
	oEditor.ResetIsDirty();
	try{
		window.opener.g(cmssection).innerHTML=HTML;
	}catch(e){ window.open('cms2.1.php?mode=emailEmergency&src='+escape(window.location),'w4'); }
	// -- 2009-03-16: enabling not working so we are just having it constantly enabled g('CMSBUpdate').disabled=true;
	window.close();
}
function jsToggle(o){
	if(g('tester').style.display=='block'){
		o.src='/images/i/blue_tri_desc.gif';
		g('tester').style.display='none';
	}else{
		o.src='/images/i/blue_tri_asc.gif';
		g('tester').style.display='block';
	}
}
<?php if($adminMode){ ?>
//if not signed in this will be loaded later
window.onload=CMSBLoad;
<?php } ?>
</script>

<script language="JavaScript" type="text/javascript">
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
</script>

</head>
<body>
<form name="form1" id="form1" action="" method="post" target="w2" onSubmit="return beginSubmit();">
	<div id="header">
		<div style="float:right;">
			<div id="logoutLink"><a href="<?php echo $_SERVER['PHP_SELF']?>?mode=CMSBEdit&logout=1" title="Log out of CMSB Editor" onClick="try{ if(oEditor.IsDirty())return confirm('This will log you out of CMSB editor and you will lose your changes. Continue?'); }catch(e){ }" target="w2">Logout</a></div>
			<?php if(!$adminMode){ ?>
			<script language="javascript" type="text/javascript">g('logoutLink').style.visibility='hidden';</script>
			<?php } ?>
		</div>
		<a title="click for CMSB Help" href="http://dev.compasspointmedia.com/mediawiki-1.13.2/index.php?title=CMS_Bridge_Public_Documentation" onClick="return ow(this.href,'l2_CMSBHelp','800,700');"><img src="/images/i/CMSB-logo.gif" width="195" height="69" align="CMSB Logo" /></a>
	</div>
	<div id="CMSBSection">
		<div id="xToolbar" style="min-height:100px">&nbsp;</div>
		<div id="CMSContainer" style="min-height:350px;visibility:hidden;">&nbsp;</div>
		<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
		<input type="hidden" name="mode" id="mode" value="CMSBEdit" />
		<!-- query string passed fields -->
		<?php
		foreach($_GET as $n=>$v){
			?><input type="hidden" name="<?php echo h($n);?>" id="<?php echo h($n);?>" value="<?php echo h($v);?>" /><?php
			echo "\n";
		}
		?>
		<input type="submit" name="Submit" id="CMSBUpdate" value="Update" />
		&nbsp;&nbsp;
		<input type="button" name="Button" value="Close" onClick="CMSBClose();" />
&nbsp;&nbsp;
<input type="button" name="Button" value="View page.." onClick="window.opener.focus();" />
&nbsp;&nbsp;
<input type="button" name="Button" value="Images" onClick="return ow('/admin/file_explorer/?uid=CMSB&view=fullfolder','l2_images','750,700');" />
</div>
	<div id="loginSection">
		<h3>CMSB Editor Sign-in</h3>
		<input name="UN" type="text" id="UN" /><br />
		<input name="PW" type="password" id="PW" /><br />
		<input type="submit" name="Submit" value="Sign In" />
		&nbsp;&nbsp;
		<input type="button" name="Button" value="Cancel" onClick="window.close();" />
	</div>
</form>
<?php
if($adminMode){
	?><script language="javascript" type="text/javascript">
	g('loginSection').innerHTML='';
	</script><?php
}
?>
<br />
<br />
<img src="/images/i/blue_tri_desc.gif" title="show Javascript tester" width="18" height="14" onClick="jsToggle(this);" style="cursor:pointer;" />
<div id="tester" style="display:none;">
	<span style="font-size:large;">Javascript Code Executer</span> (type in javascript and click eval; you can declare functions for this page, or even window.opener.newFunction=function(){ /* code here */ } for the parent if you want!)<br />
	<textarea name="test" cols="65" rows="4" id="test" onFocus="if(this.value=='/* javascript code here */')this.value='';this.select();">/* javascript code here */</textarea><br />
	<input type="button" name="button" value="Execute Code" onClick="jsEval(g('test').value);"><br />
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC?'block':'none'?>">
	<iframe name="w1" src="/blank.htm"></iframe>
	<iframe name="w2" src="/blank.htm"></iframe>
	<iframe name="w3" src="/blank.htm"></iframe>
	<iframe name="w4" src="/blank.htm"></iframe>
</div>

</body>
</html>