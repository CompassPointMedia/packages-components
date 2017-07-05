<?php
/*
2012-06-10
* version 1.20 - forked off for MEG.  purpose is to call this with more data in an array for use wherever desired; even if the comp itself is not visible

2012-01-06
* version 1.10 - removed addthis entirely
* changed default buttons to larger set 
2010-07-27
* Image for the SNW icon, logic now changed.  Default is jpg if in i/SNW but png if any subfolder.  Added var to complement SNWGallery called SNWGallerySize (16,32,64).
* Added var $SNWGalleryImgType but should not be needed; NOT set to png by default to suppor the few legaacy sites
* added a folder /images/i/SNW/km from www.komodomedia.com - see license.txt in that folder

2010-07-12
* oops, my mistake; removed excludePageFromStats=true; we DO need to run stats_collection again after this.  This page's call to stats_collection() is just to get the id of the page served, nothing more.  The page_end() call also gets the visitor and joins the info.  Queries will be short and of course all the atoms' ids will be present.. 
2010-06-02:
	Parker
		Added 	<div id="<?php echo $SNWID ? $SNWID : 'SNWWidget'?>" class="<?php echo $SNWTheme ? $SNWTheme : 'SNWTheme1'?>"> to customize ID for each site 
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
$handle='SNW';
$SNWVersion=1.20;

if(!isset($SNWLinks))$SNWLinks=array('FaceBook','Twitter' /*,'StumbleUpon'*/);
if(!$SNWOpenMethod)$SNWOpenMethod='newtab';
if(!isset($SNWLinkTraceParameter))$SNWLinkTraceParameter='c=snw'.$SNWVersion;
if(!isset($SNWAutoLogging))$SNWAutoLogging=false;
if(!isset($SNWGallery))$SNWGallery='km/';
if(!isset($SNWGallerySize))$SNWGallerySize='32';
if(!$SNWTheme)$SNWTheme='SNWTheme2'; //this is a class
if(!isset($SNWBackground))$SNWBackground=false;
if(!isset($SNWSubWrap))$SNWSubWrap=false;
if(!isset($SNWShowDescription))$SNWShowDescription=false;
if(!$SNWMethod)$SNWMethod='display';

$hideCtrlSection=false;

//for example, passes the node as first parameter, $SNWBlockAttributesFunction='custom_SNW_behavior';

//theme library -  you can write CSS differently using the precedent #SNWWidget - either inline or not
/*
Here is the XHTML structure:
===========================
div#SNWWidget
	{div#SNWBackground}
	{div#SNWSubWrap}
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
	'stumbleupon'=>array(
		'addlink'=>'http://www.stumbleupon.com/submit?',
		'linkvar'=>'url',
		'title'=>'Suggest on stumble upon',
		'description'=>'Stumble us'
	),
	'delicious'=>array(
		'addlink'=>'http://del.icio.us/post?',
		'linkvar'=>'url',
		'title'=>'Add to your Delicious Bookmarks',
		'description'=>'Bookmark this page with Delicious.',
	),
	'digg'=>array(
		'addlink'=>'http://www.digg.com/submit?phase=2&',
		'linkvar'=>'url',
		'title'=>'Can you digg it?',
		'description'=>'Digg it.'
	),
);
?>
<?php
if(is_array($mergeAvailableSNWLinks)){
	if(!function_exists('array_merge_accurate')){
	require($FUNCTION_ROOT.'/function_array_merge_accurate_v100.php');
	}
	$availableSNWLinks=array_merge_accurate($availableSNWLinks, $mergeAvailableSNWLinks);
}

if($SNWMethod=='display'){
	if($componentRewrite)ob_start();

	if($snwWidgetPageMethod!='string'){
		if($functionVersions['stats_collection']<1.2){
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('this site is using a version of stats_collection prior to 1.2, update this in the config.php file'),$fromHdrBugs);
		}
		ob_start();
		$Page=stats_collection(array( 'pageserved'=>true ));
		ob_end_clean();
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
	<div id="<?php echo $SNWID ? $SNWID : 'SNWWidget'?>" class="<?php echo $SNWTheme ? $SNWTheme : 'SNWTheme1'?>">
	<?php
	//bg wrap for semi-transparency if desired
	if($SNWBackground){ ?><div id="SNWBackground"> </div><?php } 
	//sub-wrap the object if called for
	if($SNWSubWrap){ ?><div id="SNWSubWrap"><?php }?>
	<?php
	if($SNWAdviceText){
		?><div id="SNWAdviceText"><?php echo $SNWAdviceText?></div><?php
	}
	//declare size and title attribute
	get_contents_enhanced('start');
	foreach($SNWLinks as $n=>$v){
		if(!($a=$availableSNWLinks[strtolower($v)]))continue;
		
		//develop location of image source
		$imgSrc='/images/i/SNW/'.$SNWGallery;
		$imgSrc.=strtolower($v);
		if($SNWGallerySize)$imgSrc.='_'.$SNWGallerySize;
		$imgSrc.='.';
		$imgSrc.=($SNWGalleryImgType ? $SNWGalleryImgType : ($SNWGallery ? 'png' : 'jpg'));
		
		if(strtolower($v)=='email'){
			if($a['version']==1.0){
				?><a href="mailto:?subject=<?php
				//subject line
				echo h($PageTitle ? $PageTitle : 'Site link');
				?>&body=<?php
				echo h($a['messagebody'] ? $a['messagebody'] : '');
				?>"><?php echo $a['description']?></a><?php
			}else{
			}
		}else{
			?><span id="<?php echo strtolower($v)?>" class="SNWLink" <?php if($SNWBlockAttributesFunction)$SNWBlockAttributesFunction(strtolower($v));?>>
				<a title="<?php echo $a['title'] ? $a['title'] : $v?>" target="<?php echo $SNWOpenMethod=='newtab'?'_blank':''?>" href="/index_01_exe.php?suppressPrintEnv=1&mode=SNWLink&SNWMethod=process&Page=<?php echo $Page;?>&Network=<?php echo strtolower($v);?><?php if(strtolower($v)=='twitter' && $SNWTwitterInvite)echo '&SNWTwitterInvite='.urlencode($SNWTwitterInvite);?>" <?php if($SNWOpenMethod=='popup'){ ?>onclick="return ow(this.href,'l1_snw','800,600');"<?php } ?> class="SNWAnchor">
				<span class="imgWrap"><img src="<?php echo $imgSrc;?>" alt="share button:<?php echo $v?>" <?php if($SNWGallerySize)echo 'width="'.$SNWGallerySize.'" height="'.$SNWGallerySize.'"';?> /></span>
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
		
		$pJ['componentFiles'][$handle]['data']['default']['output'][strtolower($v)]=get_contents_enhanced();
	}
	if($pJ['componentFiles'][$handle]['data']['default']['output'])ob_end_clean();
	?>
	<?php if($SNWSubWrap){ ?></div><?php }?>
	</div>
	<?php
	
	if($componentRewrite){
		$SNWStandardOutput=ob_get_contents();
		ob_end_clean();
	}
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
		//note some networks need urlencoding, others do not
		$url=$SNW['addlink'].$SNW['linkvar'].'='.(strtolower($Network)=='twitter' || strtolower($Network)=='digg' ? $PageString : urlencode($PageString));
		?><script language="javascript" type="text/javascript">
		window.location=('<?php echo $url;?>');
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