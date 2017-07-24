<?php
/*
Documentatation
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

$passedBoundingBoxWidth=250;
$passedBoundingBoxHeight=250;

$blogImgClass='fr';


//settings - change as needed
if(!$blogTitle)$blogTitle='<h1>News</h1>';
$blogTypeDateRange='<h2>'.$blogType.' - '.$Month. ' ' .$Year .'</h2>';
$blogFocusPage='articles_focus.php';

define('BLOG_FOCUS_NEVER',0); //only makes sense if we allow all words to show, else truncated articles and nowhere to go
define('BLOG_FOCUS_IF TRUNCATED',1); //articles under the full word count will not have a link to continue
define('BLOG_FOCUS_ALWAYS',2); //articles will always have a link to their own page

if(!isset($blogShowAuthorLink))$blogShowAuthorLink=true;
if(!isset($blogUseFeaturedImage))$blogUseFeaturedImage=true;
if(!isset($blogImgClass))$blogImgClass='blogImg';
if(!isset($blogAsLink))$blogAsLink=true;
if(!isset($blogHideImagesOnSummary))$blogHideImagesOnSummary=true;
if(!isset($blogClearFixHackBottom))$blogClearFixHackBottom='<div class="cb"> </div>';
if(!$blogSummaryWordCount)$blogSummaryWordCount=35;
if(!isset($blogFocusMethod))$blogFocusMethod=BLOG_FOCUS_ALWAYS;
if(!$blogListReturnPage)$blogListReturnPage=($return ? $return : 'articles.php');
if(!isset($blogHideCSS))$blogHideCSS=true; //css here for reference only

//basic login credentials
if($_SESSION['cnx'][$acct]['primaryKeyValue']){
	$blogLoggedIn=1;
}else{
	$blogLoggedIn=0;
}
?>

<?php
if(!$refreshComponentOnly){
	ob_start();
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
	$blogCSS=ob_get_contents();
	ob_end_clean();
	if(!$blogHideCSS)echo $blogCSS;	
}
?>

<?php
//declare styles to override stylesheet above
if($blogCustomStyleSheet)echo $blogCustomStyleSheet;

if($blogComponent=='list'){
	if(!$blogType){
		$blogType='Main Blog';
	}
	if($Year && $Month){
		$articles =	q("SELECT a.*, b.FirstName, b.UserName, b.LastName, b.Email FROM cms1_articles a LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID WHERE DATE_FORMAT(a.PostDate, '%M' )='$Month' AND DATE_FORMAT(a.PostDate, '%Y' )='$Year' AND a.Active=1 ".
		($blogOverrideCategoryQuery==md5($MASTER_PASSWORD) ? '' : " AND a.Category='Article'").
		($blogOverrideSubCategoryQuery==md5($MASTER_PASSWORD) ? '' : " AND a.SubCategory='$blogType'").
		"
		AND
		/* ---- note 2010-03-13: first use of Private field to show or not show ---- */
		IF($blogLoggedIn, 1 /*all articles*/, IF(PrivateShowSummaryPublicly, 1 /*still show all*/, !Private))
		
		ORDER BY PostDate DESC", O_ARRAY);
		?>
		<?php echo $blogTypeDateRange?>
		<?php
	}else{
		$articles=q("SELECT a.*, b.FirstName, b.UserName, b.LastName, b.Email FROM cms1_articles a LEFT JOIN addr_contacts b ON a.Contacts_ID=b.ID WHERE a.Active=1 ".
		($blogOverrideCategoryQuery==md5($MASTER_PASSWORD) ? '' : " AND a.Category='Article'").
		($blogOverrideSubCategoryQuery==md5($MASTER_PASSWORD) ? '' : " AND a.SubCategory='$blogType'").
		"		
		AND
		/* ---- note 2010-03-13: first use of Private field to show or not show ---- */
		IF($blogLoggedIn, 1 /*all articles*/, IF(PrivateShowSummaryPublicly, 1 /*still show all*/, !Private))
		
		/* AND a.ID!='$ID' */ ORDER BY Priority ASC, PostDate DESC", O_ARRAY);
		?>
		<?php echo $blogTitle?>
		<?php
	}
	
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
				$url=urlencode(preg_replace('/\s+/','-',$KeywordsTitle));
			}else{
				$url=$blogFocusPage.'?return='.$thispage.'&blogType='.$blogType.'&Articles_ID='.$ID;
			}
			?>
			<div id="blog<?php echo $ID?>" class="blog">
				<div class="date">
					<span class="month"><?php echo date('M',strtotime($PostDate));?></span>
					<span class="day"><?php echo date('j',strtotime($PostDate));?></span>
				</div>
				<h3 class="title">
				<?php if($blogAsLink){ ?>
				<a href="<?php echo $url?>" title="<?php echo h($SubTitle);?>">
				<?php } ?>
				<?php echo $Title?>
				<?php if($blogAsLink){ ?>
				</a>
				<?php } ?>
				</h3>
				<?php if($UserName){ ?>
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
				<?php } ?>
				<?php if($SubTitle){ ?>
				<div class="subtitle">
					<?php echo $SubTitle?>
				</div>
				<?php } ?>
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
								$mode='createCopyResized';
								$a=explode('/',$FeaturedImage);
								$sourceFile=array_pop($a);
								$sourceNode=implode('/',$a);
								$targetNode='images/cms.pieces'; //if left blank will be same as sourceNode
								$targetFile=substr($sourceFile,0,strlen($sourceFile)-4).'(resized-to-'.$passedBoundingBoxWidth.'x'.$passedBoundingBoxHeight.').'.substr($sourceFile,-3);
								require($FEX_ROOT.'/file_manager_01_exe.php');
								//do something with/after return values
								if($FEX[$mode]['errors']){
									mail($developerEmail,'unable to create needed resize of article lead image line '.__LINE__,get_globals(),$fromHdrBugs);
								}else{
									$resized=true;
								}
							}
							?><div class="<?php echo $blogImgClass?>" title="Featured Image">
							<a title="<?php echo h($SubTitle)?>" href="<?php echo $url?>"><img src="<?php echo $resized ? $targetNode.'/'.$targetFile : ($img?$img:$FeaturedImage);?>" <?php echo $resized ? "width=\"$passedBoundingBoxWidth\" height=\"$passedBoundingBoxHeight\"" : $dims[3];?> align="feaured image" /></a>
							</div><?php
						}
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
							?>[<a href="<?php echo $url?>" title="<?php echo h($SubTitle)?>">Continue Reading</a>]</span><?php
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
					<?php echo $blogClearFixHackBottom?>
				</div>
			</div>
			<?php
		}
	}
}else{
	$data = q("SELECT * FROM cms1_articles WHERE ID='$Articles_ID'", O_ROW);
	extract($data);
	if($Private && !($_SESSION['identity'] && $_SESSION['cnx'][$acct])){
		echo $blogPrivateGroupText ?>
		To sign in, <a href="/cgi/login.php?src=<?php echo urlencode('../'.$thispage.'?'.$_SERVER['QUERY_STRING']);?>">click here</a>.  <br />
		<?php	
	}else{
		//focus
		?><div class="blog"><?php
		ob_start();
		?>
		<span><a href="<?php echo $blogListReturnPage ?>" title="<?php echo $blogType?>">Back to <?php echo $blogType ? $blogType : 'Articles'?> Listing...</a></span>
		<?php
		$blogListReturnLink=ob_get_contents();
		ob_end_clean();
		
		?>
		<h3><?php 
		if($_SESSION['special'][$acct]['adminMode']){
			?>
		  <input type="button" name="Submit" value="Edit this article.." onClick="return ow('../console/focus_articles.php?ID=<?php echo $_GET['Articles_ID']?>','l0_articles','600,600');"/>
		  &nbsp;&nbsp;
		<?php
		}
		?><?php echo $data['Title']?></h3>
		<h3 class="date"><?php echo date('F j, Y', strtotime($data['PostDate'])) ?></h3>
		<?php
		if($SubTitle){
			?><p><em><?php echo $SubTitle?></em></p><?php
		}
		if($Description){
			?><div class="description"><p><?php echo $Description?></p></div><?php
		}
		if($PDFLink){
			?>
		<div style="text-align:right">&nbsp;<a href="<?php echo $PDFLink?>" target="PDF"><img src="images/i/pdficon_small.gif" alt="PDF Icon" /> View a PDF Version of this article</a></div>
		<?php
		}
		if(strlen($FeaturedImage) && file_exists($FeaturedImage)){
			?><div style="float:right;margin:0px 0px 15px 15px;">
			<img src="<?php echo $FeaturedImage?>" />
			</div><?php
		}
		?><p><?php
		if(stristr($Body,'<br') || preg_match('/<p/i',$Body) || preg_match('/<div/i',$Body)){
		//has HTML code but no breaks
			echo $Body;
		}else{
			echo nl2br($Body);
		}
		?></p>
		<?php
		echo $blogListReturnLink;
		?></div><?php
	}
}
?>