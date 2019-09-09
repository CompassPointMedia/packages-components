<?php
/* Created 2010-04-13 by Samuel with Parker helping; generic footer control 
2011-03-09: v1.20 by Samuel
---------------------------
* rewrote many of the id and class names to be more consistent - look through documentation
* default
* div id=footerCtrl(s) - lost the s at the end
* new vars: footerCtrlRewrite, footerCtrlCartLink, footerCtrlAcctLink, footerCtrlignOutLink, footerCtrlignInLink, footerCtrlNewAcctLink

2010-07-25
* SF changed copyright to &copy;
2010-5-07
Parker
* Created "Site Map" link
2010-5-10
Parker
	Moved to Components
2010-5-13
Samuel
	Got rid of square html brackets and put them in the variables to make them linkable
2010-7-26
Parker
	Made an array ($footerCtrlArray) to handle any extra footer links
	Notation is as follows
	$footerCtrlArray=array(
		'link1'=>array(
			'link'=>'index.php',
			'text'=>'[index page]',
			'id'=>'link1', (for css)
			'class'=>'links', (for css)
		'link2'=>...
	);
	It will output as follows
	<span id="link1" class"links"><a href="index.php" title="link1">[index page]</a></span>
		The title attribute is taken from the node of the array (the 'link1' part of "link1=>array")
*/

/* THIS IS A GLOBAL VARIABLE, NOT A FOOTER VARIABLE */
if(!isset($enhancedSitePages))$enhancedSitePages=false; //my-chocolate-company-site-map.php - long page system

if(empty($footerCtrlURL))$footerCtrlURL='http://www.compasspoint-sw.com/admin_help.php?ref='.$MASTER_DATABASE;

if(!isset($footerCtrlLeftBracket))$footerCtrlLeftBracket='[';
if(!isset($footerCtrlRightBracket))$footerCtrlRightBracket=']';
if(empty($footerCtrlLabelEditor))$footerCtrlLabelEditor='Site Editor';
if(empty($footerCtrlLabelConsole))$footerCtrlLabelConsole='Admin Console';
if(empty($footerCtrlLabelAdminHelp))$footerCtrlLabelAdminHelp='Admin Help';
if(empty($footerCtrlSiteMapLabel))$footerCtrlSiteMapLabel='Site Map';
if(!isset($footerCtrlSeperator))$footerCtrlSeperator='|';
if(empty($footerCtrolSiteMapURL))$footerCtrolSiteMapURL=(
	$enhancedSitePages ? 
	strtolower(implode("-",explode(" ",preg_replace('/[^a-zA-Z0-9\s]/','',$companyName)))).'-site-map.php' :
	'site_map.php'
);

if(empty($footerCtrlLabelCopyright)) $footerCtrlLabelCopyright='All images and written content on this site &copy;'.$companyName.' 2009-'.date('Y').'.  Copying or reproduction of images or written content, other than brief references, is prohibited except by express permission from '.$companyName;
 
if(false){
	//sample CSS coding, modify as needed by site
	?><style type="text/css">
	#footerCtrl{
		text-align:center;	
		}
	#footerCtrl a{
		color:#7B4B21;
		}
	</style><?php
}



if(!empty($footerCtrlRewrite)) ob_start();
?>
<div id="footerCtrl">
	<?php 
	//footerLinks
	ob_start();
	?>
	<div id="footerLinks">
		<?php
		//first section - user-defined links
		if(!empty($footerCtrlArray) && is_array($footerCtrlArray)){
			foreach($footerCtrlArray as $n=>$v){
				?>
				<span id="<?php echo $v['id'] ?>" class="<?php echo $v['class']?>"><?php echo $footerCtrlLeftBracket?><a title="<?php echo $n?>" href="<?php echo $v['link']?>"><?php echo $v['text']?></a><?php echo $footerCtrlRightBracket?></span> <?php echo $footerCtrlSeperator;?>
				<?php
			}
		}
		
		//tools
		ob_start();
		?>
		<span id="footerCtrlTools">
		<?php
		//-------- site map --------
		if(empty($hideSiteMap)){?>
			<span id="footerSiteMapLink"><?php echo $footerCtrlLeftBracket?><a href="/<?php echo $footerCtrolSiteMapURL?>" title="The site map of <?php echo $companyName?>"><?php echo $footerCtrlSiteMapLabel?></a><?php echo $footerCtrlRightBracket?></span> <?php echo $footerCtrlSeperator;?>
			<?php 
		}

		//-------- site editor -------
		if(empty($hideSiteEditorLink)){

            $src = ($thispage=='juliet-site-editor' || $thispage == 'login' ? '' : 'src='.urlencode($_SERVER['REQUEST_URI']));
			if(!empty($siteEditorLinkType) && $siteEditorLinkType=='cgi'){
			    $link = '/cgi/login.php?';
			    $link .= ($adminMode ? 'logout=1&' : '');
			    $link .= $src;
			    $link = rtrim($link, '?&');

			}else if(!empty($siteEditorLinkType) && $siteEditorLinkType=='console'){
			    $link = '/console/admin?';
			    $link .= ($adminMode ? 'logout=1&' : '');
                $link .= $src;
                $link = rtrim($link, '?&');

			}else{
				//basic login method
                $link = '/juliet-site-editor?';
                $link .= ($adminMode ? 'logout=1&' : '');
                $link .= $src;
                $link = rtrim($link, '?&');
			}
			?>
			<span id="footerSiteEditorLink"><?php echo $footerCtrlLeftBracket?><a rel="nofollow" href="<?php echo $link?>" title="<?php echo $siteName?> real-time site editor"><?php echo $adminMode?'Leave ':''?><?php echo $footerCtrlLabelEditor ?></a><?php
			if($adminMode){
				?> - <span id="div0exp" onclick="if(typeof div0exp=='function'){ div0exp(); }else{ alert('This is not set up on your site'); }"><?php echo $_COOKIE['layoutMgr']=='none'?'show layout manager':'hide layout manager'?></span><?php
			}
			?><?php echo $footerCtrlRightBracket?></span> <?php echo $footerCtrlSeperator;?>
			<?php 
		}

		//-------- console ---------
		if(empty($hideConsoleLink)){ ?>
			<span id="footerConsoleLink"><?php echo $footerCtrlLeftBracket?><a rel="nofollow" href="/console/" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelConsole?></a><?php echo $footerCtrlRightBracket?></span> <?php echo $footerCtrlSeperator;?>
			<?php
		} 

		//----------- help ----------
		if(empty($hideAdminHelp)){
			?>
			<span id="footerConsoleLink"><?php echo $footerCtrlLeftBracket?><a rel="nofollow" href="<?php echo $footerCtrlURL?>" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelAdminHelp?></a><?php echo $footerCtrlRightBracket?></span>
			<?php  
		}
		?>
		</span>
		<?php
		echo $footerCtrlTools=get_contents();
		?>
	</div><?php
	echo $footerCtrlFooterLinks=get_contents();

	//copyrightText
	if(empty($hideCopyrightText)){
		ob_start();
		if(!$companyName)mail($adminEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('No variable $companyName set for the footer controls copyright section'),$fromHdrBugs);
		?><span class="copyrightText">
			<?php echo $footerCtrlLabelCopyright?>
		</span><?php 
		echo $footerCtrlCopyrightText=get_contents();
	}

	//siteCredits
	if(empty($hideSiteCredits)){
		ob_start();
		?><div id="siteCredits">
		Site design by <a href="http://www.compasspoint-sw.com/?ref=<?php echo $_SERVER['HTTP_HOST']?>">Compass Point Media</a>
		</div>
		<?php 
		echo $footerCtrlSiteCredits=get_contents();
	}
	?>
</div><?php
if(!empty($footerCtrlRewrite)){
	$footerCtrlOutput=ob_get_contents();
	ob_end_clean();
}
