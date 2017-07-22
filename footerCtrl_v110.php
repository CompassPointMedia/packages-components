<?php
/* Created 2010-04-13 by Samuel with Parker helping; generic footer control 
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

if(!$footerCtrlURL)$footerCtrlURL='http://www.compasspoint-sw.com/admin_help.php?ref='.$MASTER_DATABASE;
if(!isset($footerCtrlLeftBracket))$footerCtrlLeftBracket='[';
if(!isset($footerCtrlRightBracket))$footerCtrlRightBracket=']';
if(!$footerCtrlLabelEditor)$footerCtrlLabelEditor='Site Editor';
if(!$footerCtrlLabelConsole)$footerCtrlLabelConsole='Admin Console';
if(!$footerCtrlLabelAdminHelp)$footerCtrlLabelAdminHelp='Admin Help';
if(!$footerCtrlLabelSiteMap)$footerCtrlLabelSiteMap='Site Map';
if(!$footerCtrlLabelCopyright)$footerCtrlLabelCopyright='All images and written content on this site &copy;'.$companyName.' 2009-'.date('Y').'.  Copying or reproduction of images or written content, other than brief references, is prohibited except by express permission from '.$companyName;
 
if(false){
	//sample CSS coding, modify as needed by site
	?><style type="text/css">
	#footerCtrls{
		text-align:center;	
		}
	#footerCtrls a{
		color:#7B4B21;
		}
	</style><?php
}
?>
<div id="footerCtrls">
	<?php if(!$hideCopyrightText){
		if(!$companyName)mail($adminEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('No variable $companyName set for the footer controls copyright section'),$fromHdrBugs);
		?><span class="copyright">
			<?php echo $footerCtrlLabelCopyright?>
		</span>
	<?php }?>
	<div id="footerLinks">
		<span class="footerCtrlsTools">
		<?php if(is_array($footerCtrlArray)){
			foreach($footerCtrlArray as $n=>$v){
				?>
				<span id="<?php echo $v['id'] ?>" class="<?php echo $v['class']?>"><a title="<?php echo $n?>" href="<?php echo $v['link']?>"><?php echo $v['text']?></a></span>
				<?php
			}
		}
		?>
		<?php
		if(!$hideSiteEditorLink){
			if($siteEditorLinkType=='cgi'){
				$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/cgi/login.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
			}else if($siteEditorLinkType=='console'){
				$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/console/admin.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
			}else{
				//basic login method
				$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/admin.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
			}
			?>
			<span class="editor"><?php echo $footerCtrlLeftBracket?><a href="<?php echo $link?>" title="<?php echo $siteName?> real-time site editor"><?php echo $adminMode?'Leave ':''?><?php echo $footerCtrlLabelEditor ?></a><?php echo $footerCtrlRightBracket?></span>
		<?php } ?>
		<?php if(!$hideConsoleLink){ ?>
			<span class="console"><?php echo $footerCtrlLeftBracket?><a href="/console/" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelConsole?></a><?php echo $footerCtrlRightBracket?></span>
		<?php } ?>
		<?php if(!$hideAdminHelp){ ?>
			<span class="console"><?php echo $footerCtrlLeftBracket?><a href="<?php echo $footerCtrlURL?>" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelAdminHelp?></a><?php echo $footerCtrlRightBracket?></span>
		<?php } ?>
		<?php if(!$hideSiteMap){?>
			<span class="siteMap"><?php echo $footerCtrlLeftBracket?><a href="../<?php echo strtolower(implode("-",explode(" ",preg_replace('/[^a-zA-Z0-9\s]/','',$companyName))))?>-site-map.php" title="The site map of <?php echo $companyName?>"><?php echo $footerCtrlLabelSiteMap?></a><?php echo $footerCtrlRightBracket?></span>	
		<?php }?>
		</span>
		<?php if(!$hideSiteCredits){ ?><div id="siteCredits">
		Site design by <a href="http://www.compasspoint-sw.com/?ref=<?php echo $_SERVER['HTTP_HOST']?>">Compass Point Media</a>	</div>
		<?php } ?>
	</div>	
</div>