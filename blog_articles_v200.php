<?php
/*
Documentatation
v2.00
	2011-06-30 
		* forked off new mainly for Juliet first
		* changed var blogRewrite to blogReorder
		* added $blogLoginURL=cgi/login by default




2010-05-11
Parker
* Added $blogMemberGroupText $blogPrivateGroupText as options for private articles and how to display to those who are not allowed to see them.
* Added code to make private articles, private.

2010-05-07
Parker
* Added $blogDateFormat to help in formatting the date.  Kept the legacy format.
* Added $blogContinueReadingText for more customization. 
* Added .description for the art

2010-04-26
* moved over from the sites to lib; making everything generic

2010-04-21 - added the ability to ignore category and subcategory entirely
* blogOverrideCategoryQuery (must=md5(master_password))
* blogOverrideSubCategoryQuery 

2010-03-12
----------
* created this initially from LA Classic Estate Coding
* pulled out site-specific references and started creating settings

*/
$componentVersions['blog_articles']=1.12;
if(!$passedBoundingBoxWidth)$passedBoundingBoxWidth=250;
if(!$passedBoundingBoxHeight)$passedBoundingBoxHeight=250;
if(!$blogImgClass)$blogImgClass='fr';

//settings - change as needed
if(!$blogTitle)$blogTitle='<h1>News</h1>';
$blogTypeDateRange='<h2>'.$blogType.' - '.$Month. ' ' .$Year .'</h2>';

define('BLOG_FOCUS_NEVER',0); //only makes sense if we allow all words to show, else truncated articles and nowhere to go
define('BLOG_FOCUS_IF TRUNCATED',1); //articles under the full word count will not have a link to continue
define('BLOG_FOCUS_ALWAYS',2); //articles will always have a link to their own page

if(!isset($blogFocusPage))$blogFocusPage='articles_focus.php';
if(!isset($blogShowAuthorLink))$blogShowAuthorLink=true;
if(!isset($blogAuthorText))$blogAuthorText='By ';
if(!isset($blogUseFeaturedImage))$blogUseFeaturedImage=true;
if(!isset($blogImgClass))$blogImgClass='blogImg';
if(!isset($blogAsLink))$blogAsLink=true;
if(!isset($blogHideImagesOnSummary))$blogHideImagesOnSummary=true;
if(!isset($blogClearFixHackBottom))$blogClearFixHackBottom='<div class="cb"> </div>';
if(!$blogSummaryWordCount)$blogSummaryWordCount=35;
if(!isset($blogFocusMethod))$blogFocusMethod=BLOG_FOCUS_ALWAYS;
if(!$blogListReturnPage)$blogListReturnPage=($return ? $return : 'articles.php');
if(!isset($blogHideCSS))$blogHideCSS=true; //css here for reference only
if(!$blogContinueReadingText)$blogContinueReadingText='[Continue Reading]';
if(!isset($blogMemberGroupText))$blogMemberGroupText=$companyName;//Search $Private && !($_SESSION['identity'] && $_SESSION['cnx'][$acct] to see how to implement
if(!isset($blogPrivateGroupText))$blogPrivateGroupText='This article is a premium resource available to '.$blogMemberGroupText.' members only.<br />';

if(!$blogLoginURL)$blogLoginURL='/cgi/101';

if(!isset($blogDisplayWhenPrivate))$blogDisplayWhenPrivate=array(''=>true,'','','','');

//order that article summary pieces (spots) are presented in
if(!isset($blogSpotOrder))$blogSpotOrder=array(
	'blogSpotDate', 'blogSpotTitle', 'blogSpotCredits', 'blogSpotSubTitle', 'blogSpotContent'
);
if(!function_exists('strip_attributes')){
	function strip_attributes($msg, $tag, $attr, $suffix = ''){
		$lengthfirst = 0;
		while (strstr(substr($msg, $lengthfirst), "<$tag ") != "") {
			$tag_start = $lengthfirst + strpos(substr($msg, $lengthfirst), "<$tag ");
			$partafterwith = substr($msg, $tag_start);
			$img = substr($partafterwith, 0, strpos($partafterwith, ">") + 1);
			$img = str_replace(" =", "=", $img);
			$out = "<$tag";
			for($i = 0; $i < count($attr); $i++) {
				if (empty($attr[$i])) {
					continue;
				}
			}
		}
	}
}

//basic login credentials
if($_SESSION['cnx'][$acct]['primaryKeyValue']){
	$blogLoggedIn=1;
}else{
	$blogLoggedIn=0;
}

if(!$refreshComponentOnly){
	ob_start();
	if(false){ 
		/* ----------------- example ---------------- */
		?><style type="text/css"><?php
		if($blogTheme=='blogTheme1'){
			//put this in the external stylesheet for theme1
			?>
			.blogTheme1{
				margin-bottom:20px;
				}
			.blogTheme1 .title{
				font-family:"Times New Roman", Times, serif;
				/*
				serif:
				font-size:149%;
				font-weight:400;
				
				sans-serif:
				*/
				font-size:134%;
		
				padding:5px 0px 2px 0px;
				margin:0px 0px 3px 0px;
				background-image:url('/images/i/lines/h-400-rosefaderight.png');
				background-position:bottom left;
				background-repeat:no-repeat;
				}
			.blogTheme1 .title a{
				color:#986027;
				color:peru;
				}
			.blogTheme1 a:hover{
				text-decoration:none;
				}
			.blogTheme1 .btm_Nav, #blogArticles p{
				display:inline;
				}
			.blogTheme1 .date{
				font-style:italic;
				}
			<?php
		}else if($blogTheme=='blogTheme2'){
			//and so on.. 
			?>
			.blogTheme2 .blog{
				}
			.blogTheme2 .etc{
				}
			<?php
		}
		?></style><?php 
	}

	?><style type="text/css">
	/*	
	.blog .pdf{
		margin:15px 0px;
		width:50%;
		border:1px dotted #664;
		vertical-align:text-top;
		background-color:#FFFAF0;
		padding:15px 25px;
		}
	.blog .pdf a{
		color:#000;
		text-decoration:none;
		}
	*/
	.blog{
		margin-bottom:35px;
		border-bottom:1px dotted black;
		padding-bottom:0px;
		}
	.blog .title{
		color:darkblue;
		font-weight:400;
		font-size:149%;
		}
	.blog .blogImg{
		float:right;
		}
	.blog .title a{
		color:inherit;
		}
	.blog .title a:hover{
		text-decoration:none;
		}
	.blog .date{
		float:left;
		text-align:center;
		width:40px;
		height:40px;
		vertical-align:top;
		background-color:#336699;
		color:white;
		margin:0px 15px 15px 0px;
		padding-top:5px;
		padding-bottom:5px;
	}
	.blog .date span{
		display:block;
		font-size:107%;
		font-weight:400;
		margin-bottom:10px;
		}
	.blog .credits{
		margin:15px 0px;
		}
	.blog .subtitle{
		font-weight:900;
		font-style:italic;
		margin-bottom:15px;
		}
	.blog .continueReading{
		font-weight:900;
		float:right;
		}
	.blog .btm_Nav{
		clear:both;
		background-color:#E8ECF2;
		margin-top:20px;
		height:20px;
		padding:8px 10px;
		}
	.blog a{
		color:DARKRED;
		}
	.blog .image{
		float:right;
		margin:0px 0px 15px 15px;
		}
	</style>
	<?php
	$blogCSS=get_contents();
	if(!$blogHideCSS)echo $blogCSS;	
}


//declare styles to override stylesheet above
if($blogCustomStyleSheet)echo $blogCustomStyleSheet;

if($blogComponent=='list'){
	if(!$blogType)$blogType='Main Blog';
	if($Year && $Month){
		$articles =	q("SELECT a.*, b.FirstName, b.UserName, b.LastName, b.Email FROM cms1_articles a LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID WHERE DATE_FORMAT(a.PostDate, '%M' )='$Month' AND DATE_FORMAT(a.PostDate, '%Y' )='$Year' AND a.Active=1 ".
		($blogOverrideCategoryQuery==md5($MASTER_PASSWORD) ? $blogCategoryQuery : " AND a.Category='Article'").
		($blogOverrideSubCategoryQuery==md5($MASTER_PASSWORD) ? $blogSubCategoryQuery : " AND a.SubCategory='$blogType'").
		"
		AND
		/* ---- note 2010-03-13: first use of Private field to show or not show ---- */
		IF($blogLoggedIn, 1 /*all articles*/, IF(PrivateShowSummaryPublicly, 1 /*still show all*/, !Private))
		
		ORDER BY Priority, PostDate DESC", O_ARRAY);
		echo $blogTypeDateRange;
		echo "\n";
	}else{
		$articles=q("SELECT a.*, b.FirstName, b.UserName, b.LastName, b.Email FROM cms1_articles a LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID WHERE a.Active=1 ".
		($blogOverrideCategoryQuery==md5($MASTER_PASSWORD) ? $blogCategoryQuery : " AND a.Category='Article'").
		($blogOverrideSubCategoryQuery==md5($MASTER_PASSWORD) ? $blogSubCategoryQuery : " AND a.SubCategory='$blogType'").
		"		
		AND
		/* ---- note 2010-03-13: first use of Private field to show or not show ---- */
		IF($blogLoggedIn, 1 /*all articles*/, IF(PrivateShowSummaryPublicly, 1 /*still show all*/, !Private))
		
		/* AND a.ID!='$ID' */ ORDER BY ".($blogFeaturedArticleFirst ? "IF(LeadArticle=1,1,2),":'')." Priority ASC, PostDate DESC", O_ARRAY);
		echo $blogTitle;
		echo "\n";
	}
	
	?><div id="blogArticles" class="<?php echo $blogTheme ? $blogTheme : 'blogTheme1';?>"><?php
	if($articles){
		$i=0;
		foreach($articles as $v){
			$i++;
			extract($v);
			//new from cms1_articles: specify by-article whether to truncate, and at how many words
			if(isset($BodySummaryTruncate) && !$BodySummaryTruncate){
				$nbrWords=0;
			}else{
				$nbrWords=($BodySummaryWordCount ? $BodySummaryWordCount : $blogSummaryWordCount);
			}
			if($KeywordsTitle){
				$pageHandles['articlesListPage']=urlencode(preg_replace('/\s+/','-',$KeywordsTitle));
				$url=urlencode(preg_replace('/\s+/','-',$KeywordsTitle));
			}else{
				$url=$blogFocusPage.'?return='.$thispage.'&blogType='.$blogType.'&Articles_ID='.$ID;
			}
			//store the blog/article
			if($blogReorder) ob_start(); //#


			?><div id="blog<?php echo $ID?>" class="blog">
			<?php 
			//Title
			ob_start();
			?><h3 class="title">
			<?php if($blogAsLink){ ?>
			<a href="<?php echo $url?>" title="<?php echo h($SubTitle);?>">
			<?php } ?>
			<?php echo $Title?>
			<?php if($blogAsLink){ ?>
			</a>
			<?php } ?>
			<?php
			if($adminMode){
				?><a title="Edit this article" href="/console/focus_articles.php?ID=<?php 
				echo $ID?>" onclick="return ow(this.href,'l1_articles','700,700');"><img src="/images/i/plusminus-plus.gif" alt="edit" /></a><?php
				echo "\n";
			}
			?></h3><?php
			echo "\n";
			echo $blogSpotTitle=get_contents();


			//Credits
			ob_start();
			if($UserName){ ?>
			<div class="credits">
				<?php if($UserName){ ?>By <span class="author"><?php echo $FirstName . ' ' . $LastName?></span><?php } ?><?php 
				if($blogShowAuthorLink && $AuthorLink){
					$a=explode('||',$AuthorLink);
					?>
					of <span class="authorLink"><a href="<?php echo $a[0];?>" title="<?php echo h($a[0]);?>"><?php echo $a[1]?$a[1]:$a[0]?></a></span>
					<?php
				}
				?>
			</div>
			<?php 
			}else if($AuthorName){
				?>
				<?php if($blogAuthorText) echo $blogAuthorText ?><span class="author"><?php echo $AuthorName;?></span><?php
				echo "\n";
				if($blogShowAuthorLink && $AuthorLink){
					$a=explode('||',$AuthorLink);
					?>
					of <span class="authorLink"><a href="<?php echo $a[0];?>" title="<?php echo h($a[0]);?>"><?php echo $a[1]?$a[1]:$a[0]?></a></span><?php echo "\n";
				}
			} ?>
			<?php
			echo $blogSpotCredits=get_contents();

			//Date
			ob_start();
			?>
			<div class="date">
				<?php if(!$blogDateFormat){?>
				<span class="month"><?php echo date('M',strtotime($PostDate));?></span>
				<span class="day"><?php echo date('j',strtotime($PostDate));?></span>
				<span class="year"><?php echo date('Y',strtotime($PostDate));?></span>
				<?php }else{ ?>
				<span><?php echo date($blogDateFormat,strtotime($PostDate));?></span>
				<?php }?>
			</div>
			<?php
			echo $blogSpotDate=get_contents();

			//SubTitle
			ob_start();
			?>
			<?php if($SubTitle){ ?>
			<div class="subtitle">
				<?php echo $SubTitle?>
			</div>
			<?php } 
			echo $blogSpotSubTitle=get_contents();

			//Content
			ob_start(); //#
			?>
			<div class="blogContent">
				<?php
				if($blogUseFeaturedImage){
					unset($dims,$img);
					if($FeaturedImage && file_exists($FeaturedImage)){
						$dims=getimagesize($FeaturedImage);
					}else if(preg_match('/<img\s[^>]*src="([^"]+)"[^>]*>/i',$Body,$a)){
						$img=preg_replace('/(\.\.\/)*/','',$a[1]);
						if(file_exists($img)) $dims=getimagesize($img);
					}
					if($dims){
						$resized=false;
						if($dims[0]>$passedBoundingBoxWidth || $dims[1]>$passedBoundingBoxHeight){
							$_REQUEST['uid']=md5(time());
							$suppressPrintEnv=1;
							$suppressExePageShutdown=true;
							$mode='createCopyResized';
							$a=explode('/',$FeaturedImage);
							$sourceFile=array_pop($a);
							$sourceNode=implode('/',$a);
							$targetNode='images/cms.pieces'; //if left blank will be same as sourceNode
							$targetFile=substr($sourceFile,0,strlen($sourceFile)-4).'(resized-to-'.$passedBoundingBoxWidth.'x'.$passedBoundingBoxHeight.').'.substr($sourceFile,-3);
							require($FEX_ROOT.'/file_manager_01_exe.php');

							//now, back update the article so that the image isn't created each time
							ob_start();
							q("UPDATE cms1_articles SET FeaturedImage='$targetNode/$targetFile' WHERE ID=$ID");
							if($err=get_contents()){
								mail($developerEmail, 'On back-update of article lead image; error file '.__FILE__.', line '.__LINE__,get_globals($err),$fromHdrBugs);
							}else{
								mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals('hey! you have one system of article back-updating in albums {300x300} and another here (resized-to-300x300) in two different folders - standardize this'),$fromHdrBugs);
							}

							//do something with/after return values
							if($FEX[$mode]['errors']){
								mail($developerEmail,'unable to create needed resize of article lead image line '.__LINE__,get_globals(),$fromHdrBugs);
							}else{
								$resized=true;
							}
						}
						ob_start();
						?><div class="<?php echo $blogImgClass?>" title="Featured Image">
						<a title="<?php echo h($SubTitle)?>" href="<?php echo $url?>"><img src="<?php echo $resized ? $targetNode.'/'.$targetFile : ($img?$img:$FeaturedImage);?>" <?php echo $resized ? "width=\"$passedBoundingBoxWidth\" height=\"$passedBoundingBoxHeight\"" : $dims[3];?> align="feaured image" /></a>
						</div><?php
						echo $blogFeaturedImage=get_contents();
					}
				}
				if($EmbedCode){
					?><div class="listVideo"><?php
					if($EmbedCode) echo $EmbedCode;
					?></div><?php
				}
				//show content removed of images
				$n=strip_tags($Body,'<p><a>'.($blogHideImagesOnSummary ? '':'<img>'));
				if($nbrWords){
					$a=explode(' ',$n);
					$j=-1;
					while(true){
						//don't end inside an <a>..
						$j++;
						if(!isset($a[$j]))break;
						$inA=( ($inA || preg_match('/^<a/i',$a[$j]) ) && !preg_match('/<\/a>/i',$a[$j]) 
						? true : false);
						echo $a[$j] .' ';
						
						if($j>$nbrWords && !$inA){
							$blogTruncated=true;
							break;
						}
					}
				}else{
					$blogTruncated=false;
					echo $n;
				}
				//ellipsis at end of text
				if($blogTruncated){
					?><span class="ellipsis">...</span><?php
				}
				if($blogFocusMethod + ($blogTruncated ? 1 : 0)>=2){
					?><div class="btm_Nav">
					<span class="continueReading"><?php
					if($blogContinueReadingLink){
						echo str_replace('{url}',$url,$blogContinueReadingLink);
					}else{
						?><a href="<?php echo $url?>" title="<?php echo h($SubTitle)?>"><?php echo $blogContinueReadingText?></a></span><?php
					}
					?>
					</div><?php
				}
				if($PDFLink && file_exists($PDFLink)){
					?><div class="pdf">
					<a onClick="return ow(this.href,'l1_pdf','700,600');" href="<?php echo $PDFLink;?>"><img src="images/i/pdficon_small.gif" alt="PDF Icon" /> View PDF Version of this Article</a>
					</div>
					<?php
				}
				?>
				<?php echo $blogClearFixHackBottom;?>
			</div><?php
			echo $blogSpotContent=get_contents(); //#

			?></div><?php
			echo "\n";


			if($blogReorder){
				//intercept the output and store here
				ob_end_clean(); //#
				//re-echo or this is lost
				echo "\n<!-- reordered entry blog_articles_v120 - line ".__LINE__." -->\n".'<div id="blog'.$ID.'" class="blog">'."\n";
				foreach($blogSpotOrder as $w){
					//echo that section in this order
					echo $$w;
				}
				echo "\n".'</div>'."\n";
			}
		}
	}else{
		mail($developerEmail, 'SEE NOTE IN EMAIL!! User viewing articles in list view with no articles present, file '.__FILE__.', line '.__LINE__,get_globals('develop where the paragraph below this specifies settings to a person in adminMode, with a link to the console to create an article OF THAT TYPE'),$fromHdrBugs);
		?><p>Currently no articles are listed</p>
		<?php
		if($_SESSION['special'][$acct]['adminMode']){
			?><p>
			<a href="/console/list_articles.php">Click here to view articles in the console</a><br />
			<a href="/console/focus_articles.php">Click here to create a new article</a><br />
			
			</p><?php
		}
	}
	?></div><?php
}else{
	$data = q("SELECT * FROM cms1_articles WHERE ID='$Articles_ID'", O_ROW);
	extract($data);

	ob_start(); //---------------- master buffer for article -----------------
	?>
	<div class="blog">
	<?php 
	ob_start();
	?>
	<h3><?php echo $data['Title']?>
	<?php 
	if($_SESSION['special'][$acct]['adminMode']){
		?> <a title="Edit this article" href="/console/focus_articles.php?ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_articles','700,700');"><img src="/images/i/plusminus-plus.gif" alt="edit" /></a>&nbsp;<?php
	}
	?></h3>
	<?php echo $blogDisplayWhenPrivate['Title']=get_contents();


	ob_start();?>
	<h3 class="date"><?php echo date('F j, Y', strtotime($data['PostDate'])) ?></h3>
	<?php echo $blogDisplayWhenPrivate['Date']=get_contents();

	if($SubTitle){
		ob_start();
		?><p class="subtitle"><em><?php echo $SubTitle?></em></p>
		<?php 
		echo $blogDisplayWhenPrivate['SubTitle']=get_contents();
	}

	if($Description){
		ob_start();
		?><div class="description"><p><?php echo $Description?></p></div><?php
		echo $blogDisplayWhenPrivate['Description']=get_contents();
	}

	if($PDFLink){
		ob_start();
		?>
		<div style="text-align:right">&nbsp;<a href="<?php echo $PDFLink?>" target="PDF"><img src="images/i/pdficon_small.gif" alt="PDF Icon" /> View a PDF Version of this article</a></div>
		<?php
		echo $blogDisplayWhenPrivate['PDFLink']=get_contents();
	}

	if(strlen($FeaturedImage) && file_exists($FeaturedImage)){
		ob_start();		
		?><div style="float:right;margin:0px 0px 15px 15px;">
		<img src="<?php echo $FeaturedImage?>" />
		</div><?php
		echo $blogDisplayWhenPrivate['FeaturedImage']=get_contents();	
	}

	if($EmbedCode){
		ob_start();		
		?><div class="focusVideo"><?php echo $EmbedCode.'<br />';?></div><?php
		echo $blogDisplayWhenPrivate['FeaturedImage']=get_contents();	
	}

	ob_start();
	?><p><?php
	if(stristr($Body,'<br') || preg_match('/<p/i',$Body) || preg_match('/<div/i',$Body)){
	//has HTML code but no breaks
		echo $Body;
	}else{
		echo nl2br($Body);
	}
	?></p><?php 
	echo $blogDisplayWhenPrivate['Body']=get_contents();

	ob_start();?>
	<span><a href="<?php echo $blogListReturnPage ?>" title="<?php echo $blogType?>">Back to <?php echo $blogType ? $blogType : 'Articles'?> Listing...</a></span>
	<?php echo $blogListReturnLink=get_contents();
	?>
	</div>
	<?php
	$standardOutput=get_contents();
	
	if($Private && !($_SESSION['identity'] && $_SESSION['cnx'][$acct])){
		foreach ($blogDisplayWhenPrivate as $v){
			echo $v;
		}
		echo $blogPrivateGroupText ?>
		To sign in, <a href="<?php echo $blogLoginURL?>?src=<?php echo urlencode('/'.($thisfolder ? $thisfolder.'/':'').$thispage.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));?>">click here</a>.  <br />
		<?php	
	}else{
		echo $standardOutput;
	}
}
?> 