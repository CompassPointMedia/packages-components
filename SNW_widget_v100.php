<?php
/*
2010-05-12: 
	added 'themes' (SNWTheme) which are style variations.  No css is output here but needs to be pulled out to the external stylesheet
	use these to show/hide divs used for styling
		* SNWBackground
		* SNWSubWrap
		* SNWShowDescription
	use $SNWGallery to control the subfolder in images/i/SNW where the iconset is located
	added SNWAdviceText as an advisory before the loop of links
		
	added mergeAvailableSNWLinks for better control as with dataset -> components
2010-04-23: make SURE you have stats_collection() version 1.2 or greater for this

2010-04-20: simple SNW (social Networking) widget by Samuel

this will display the widget with the SNW bookmakrk icons (located in /images/i/SNW/[node][32x32][style].png) node is lower case; the default icons are 32x32, default style; and we need to build that library over time

things we need to do:
add to the facebook link say "&c=1&v=3482" so we can discern the facebook viewers' click throughs
need to have a "connecting to facebook.." delay to brand the product OR open it in an iframe to really brand our product

*/

//settings
if(!isset($SNWLinks))$SNWLinks=array('FaceBook','Twitter' /*,'StumbleUpon'*/);
if(!$SNWOpenMethod)$SNWOpenMethod='newtab';
if(!isset($SNWLinkTraceParameter))$SNWLinkTraceParameter='c=snw1.00';
if(!isset($SNWAutoLogging))$SNWAutoLogging=false;
if(!isset($SNWHideAddThisCoding))$SNWHideAddThisCoding=false;
if($SNWGallery)$SNWGallery=rtrim($SNWGallery,'/').'/';
if(!$SNWTheme)$SNWTheme='SNWTheme2'; //this is a class
if(!isset($SNWBackground))$SNWBackground=false;
if(!isset($SNWSubWrap))$SNWSubWrap=false;
if(!isset($SNWShowDescription))$SNWShowDescription=false;

//theme library -  you can write CSS differently using the precedent #SNWWidget - either inline or not
/*
Here is the XHTML structure:
===========================
div#SNWWidget
	{div#SNWBackground}
	{div#SNWSubWrap}
		{div.addthis_toolbox}
		 span#facebook.SNWLink
			span.imgWrap
				a -> img
			span.description


*/
if(false){ 
	?><style type="text/css"><?php
	if($SNWTheme=='SNWTheme1'){
		//put this in the external stylesheet for theme1
		?>
		
		<?php
	}else if($SNWTheme=='SNWTheme2'){
		//and so on.. 
		?>
		.SNWTheme2 span{
			display:block;
		}
		.SNWTheme2 .SNWLink{
			clear:both;
			margin-top:5px;
			height:18px;
			float:left;
			/*extras, not needed */
			border-bottom:1px dotted cornsilk;
			}
		.SNWTheme2 .imgWrap{
			float:left;
			}
		.SNWTheme2 .SNWDescription{
			display:inline;
			padding-left:5px;
			}
		<?php
	}
	?></style><?php 
}

/* -- other settings --
$SNWNotifyEmail;
$SNWTwitterInvite;
*/

if(!$availableSNWLinks)$availableSNWLinks=array(
	'facebook'=>array(
		'addlink'=>'http://www.facebook.com/share.php?',
		'linkvar'=>'u',
		'title'=>'share this page on FaceBook',
		'description'=>'Share this page on FaceBook'
	),
	'twitter'=>array(
		'addlink'=>'http://www.twitter.com/home?'.($twitterSourceAccount ? 'source='.$twitterSourceAccount.'&' : ''),
		'linkvar'=>'status',
		'title'=>'tweet this page on Twitter',
		'description'=>'Tweet us on your Twitter Page'
	),
);
if(is_array($mergeAvailableSNWLinks)){
	$availableSNWLinks=array_merge_accurate($availableSNWLinks, $mergeAvailableSNWLinks);
}
if(!$SNWMethod)$SNWMethod='display';

if($SNWMethod=='display'){
	if($snwWidgetPageMethod!='string'){
		if($functionVersions['stats_collection']<1.2){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('this site is using a version of stats_collection prior to 1.2, update this in the config.php file'),$fromHdrBugs);
		}
		ob_start();
		$Page=stats_collection(array( 'pageserved'=>true ));
		ob_end_clean();
		//we don't need to run stats again
		$excludePageFromStats=true;
	}else{
		//we're not ready to do this right now
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		$Page=urlencode(
			($_SERVER['SERVER_PORT']==443 ? 'https' : 'http').
			'://'.$_SERVER['HTTP_HOST'].'/'.
			$_SERVER['REQUEST_URI']
		);
	}
	?>
	<div id="SNWWidget" class="<?php echo $SNWTheme ? $SNWTheme : 'SNWTheme1'?>">
	<?php
	//bg wrap for semi-transparency if desired
	if($SNWBackground){ ?><div id="SNWBackground"> </div><?php } 
	
	//sub-wrap the object if called for
	if($SNWSubWrap){ ?><div id="SNWSubWrap"><?php }
	if(!$SNWHideAddThisCoding){
	?>
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style">
	<a href="http://www.addthis.com/bookmark.php?v=250&amp;username=sfullman" class="addthis_button_compact">Share</a>
	<span class="addthis_separator">|</span>
	<?php } ?>
	<?php
	if($SNWAdviceText){
		?><div id="SNWAdviceText"><?php echo $SNWAdviceText?></div><?php
	}

	//declare size and title attribute
	foreach($SNWLinks as $v){
		if(!($a=$availableSNWLinks[strtolower($v)]))continue;
		?><span id="<?php echo strtolower($v)?>" class="SNWLink">
			<a title="<?php echo $a['title'] ? $a['title'] : $v?>" target="<?php echo $SNWOpenMethod=='newtab'?'_blank':''?>" href="/index_01_exe.php?suppressPrintEnv=1&mode=SNWLink&SNWMethod=process&Page=<?php echo $Page;?>&Network=<?php echo strtolower($v);?><?php if(strtolower($v)=='twitter' && $SNWTwitterInvite)echo '&SNWTwitterInvite='.urlencode($SNWTwitterInvite);?>" <?php if($SNWOpenMethod=='popup'){ ?>onclick="return ow(this.href,'l1_snw','800,600');"<?php } ?>>
			<span class="imgWrap"><img src="/images/i/SNW/<?php echo $SNWGallery?><?php echo strtolower($v)?>.jpg" alt="share button:<?php echo $v?>" /></span>
			<?php
			if($SNWShowDescription){
				?><span class="SNWDescription"><?php
				echo $a['description'];
				?></span><?php
			}
			?>
			</a>
		</span><?php
	}
	if(!$SNWHideAddThisCoding){
	?>
	</div>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=sfullman"></script>
	<!-- AddThis Button END -->
	<?php } ?>
	<?php if($SNWSubWrap){ ?></div><?php }?>
	</div>
	<?php
}else if($SNWMethod=='log' || ($SNWAutoLogging && $SNWBatches_ID)){
	//this component handles its own logging of incoming hits - much better way to handle things
	
	
	$SNWMethod='display';
}else{
	//SNWMethod=process
	if(preg_match('/^[0-9]+$/',$Page)){
		$PageString=q("SELECT CONCAT(
			s.Name,
			d.Name,
			p.Name,
			IF(f.Name='(folder_default)','',f.Name),
			IF(q.Name<>'',CONCAT('?',q.Name),''),
			IF(b.Name<>'',CONCAT('#',b.Name),'')
		)
		FROM 
		stats_pageserved a
		LEFT JOIN stats_files f ON a.Files_ID=f.ID
		LEFT JOIN stats_querystrings q ON a.Querystrings_ID=q.ID
		LEFT JOIN stats_bookmarks b ON a.Fragments_ID=b.ID,
		stats_protocols s,
		stats_domains d,
		stats_paths p
		WHERE
		a.ID=$Page AND
		a.Schemes_ID=s.ID AND
		a.Domains_ID=d.ID AND
		a.Paths_ID=p.ID",O_VALUE);
	}else{
		//we would need the ID of this for tracking response to the URL - DO NOT USE THIS RIGHT NOW
		$PageString=stripslashes($Page);
	}
	if($SNW=$availableSNWLinks[$Network]){

		//add to database
		$SNWBatches_ID=q("INSERT INTO relatebase_content_batches SET
		ContentObject='stats_pageserved',
		ContentKey='$Page',
		FieldHash='".base64_encode(serialize(array('URL'=>$PageString)))."',
		Network='$Network',
		StartTime=NOW(),
		CreateDate=NOW(),
		Creator='system'", O_INSERTID);

		$PageString=explode('#',$PageString);
		$PageString[0].=(strstr($PageString[0],'?') ? '' : '?');
		if(substr($PageString[0],-1)!=='?' && substr($PageString[0],-1)!=='&')$PageString[0].='&';
		//add the reference ID for this visitor's share of the page
		$PageString[0].='SNWBatches_ID='.$SNWBatches_ID;
		//add specified tracing parameter if present, so we can log visits from the linker's friends..
		if($SNWLinkTraceParameter){
			$trace=explode('=',$SNWLinkTraceParameter);
			if(!preg_match('/\b'.$trace[0].'=/',$PageString[0])){
				$PageString[0].='&'.$SNWLinkTraceParameter;
			}	
		}
		$PageString=implode('#',$PageString);

		if(strtolower($Network)=='twitter'){
			//we are converting the url to a crumple and the "PageString" will be {check out this link: }{crumple} where crumple is the API crumple word
			eval('$crumple=`curl -d "mode=crumpleAPIBasic&url='.urlencode(stripslashes($PageString)).'" crumple.me/index_01_exe.php`;');
			//need to check for status=OK
			$crumple=explode('&',$crumple);
			$crumple=preg_replace('/^word=/','',$crumple[1]);
			$PageString=($SNWTwitterInvite ? str_replace(' ','+',trim($SNWTwitterInvite)).'+':'Check+out+this+link:+').'http://crumple.me/'.$crumple;
		}
		?><script language="javascript" type="text/javascript">
		window.location=('<?php echo $SNW['addlink'].$SNW['linkvar'].'='.(strtolower($Network)=='twitter' ? $PageString : urlencode($PageString))?>');
		</script><?php
		if($SNWNotifyEmail){
			mail($SNWNotifyEmail,'Social Network Link Sent',get_globals($PageString),'From: do-not-reply@'.$_SERVER['HTTP_HOST']);
		}
	}else{
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		error_alert('Unable to link to '.$Network);
	}
}
?>
