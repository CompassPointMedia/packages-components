<?php
/***
Category Table
--------------
2011-08-14 forked over to 0.5 so for use with juliet's output buffering
	
2008-10-13 committed to a component for all sites
Horizontal Looper v2.0 - 2008-04-18
- this looper is record-driven vs. row-column driven.  A row-column driven looper would be much smaller codewise
***/

//main category image location
if(!isset($catPath))$catPath='images/category';
if(!isset($catImgMainArray))$catImgMainArray='categoryimages';
if(!isset($catImgMainOptions))$catImgMainOptions=array(); //for function get_image
if(!$$catImgMainArray)$$catImgMainArray=get_file_assets($catPath,'thumb');

//backup images location (usually products/thumb)
if(!$catBackupPath)$catBackupPath='images/products/thumb';
if(!$catImgBackupArray)$catImgBackupArray='productthumbs';
if(!isset($catImgBackupOptions))$catImgBackupOptions=array(); //for function get_image
if(!$$catImgBackupArray)$$catImgBackupArray=get_file_assets($catBackupPath,'thumb');
if(!$defaultNAImage)$defaultNAImage='spacer.gif';
if(!$defaultNAImageWidth)$defaultNAImageWidth='135';
if(!$defaultNAImageHeight)$defaultNAImageHeight='135';
if(!$defaultNAImagePath)$defaultNAImagePath='images/assets';
if(!isset($maxCatThumbnailWidth))$maxCatThumbnailWidth=175;

//site pages
if(!$pageHandles['categoryPage'])$pageHandles['categoryPage']='category.php';
if(!$pageHandles['subCategoryPage'])$pageHandles['subCategoryPage']='subcategory.php';
if(!$pageHandles['productsPage'])$pageHandles['productsPage']='products.php';
if(!$pageHandles['singlePage'])$pageHandles['singlePage']='single.php';

if(!isset($preserveSpaces))$preserveSpaces=''; #i.e. no, don't preserve; otherwise set to ' ';
if(!isset($preserveDashes))$preserveDashes=''; #i.e. no, don't preserve; otherwise set to '-';
if(!isset($allowCategoryRanking))$allowCategoryRanking=true;

//layout
if(!$cols)$cols=3;
if(!$rows)$rows=1000;
if(!$startRow)$startRow=1;
if(!$startCol)$startCol=1;
//or you can specify this:
#$startPosition=1
if(!$maxRows)$maxRows=1000;
if(!$categorySQL)$categorySQL="SELECT 
i.Category, COUNT(*) AS Count, COUNT(DISTINCT i.SubCategory) AS Subs, c.Caption, c.Description 
FROM
finan_items i LEFT JOIN finan_items_categories c ON i.Category=c.Name
WHERE 
i.Active=1 AND (i.Type='Non-inventory Part' OR i.Type IS NULL OR i.Type='') GROUP BY i.Category ORDER BY IF(c.Priority IS NULL, 1000, c.Priority), i.Category";

if(!$categoryJSDeclared){
	$categoryJSDeclared=true;
	//initial javascript, hidden fields
	?><script language="javascript" type="text/javascript">
	function getPageOffset(point){
		//http://dev.communityserver.com/forums/t/477071.aspx
		if(typeof window.pageXOffset == 'number'){
			//Netscape compliant
			return (point=='left'?window.pageXOffset:window.pageYOffset);
		}else if (document.body && (document.body.scrollLeft || document.body.scrollTop)){
			//DOM compliant
			return (point=='left'?document.body.scrollLeft:document.body.scrollTop);
		}else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)){
			//IE6 standards compliant mode
			return (point=='left'?document.documentElement.scrollLeft:document.documentElement.scrollTop);
		}
	}
	function setID(id, evt){
		/* var loc=g('priority'+id);
		var clickDown=evt.clientY - loc.offsetTop + getPageOffset('top');
		var height=loc.height;
		var dir=(clickDown <= height/2 ? 1 : -1); */
		var url='<?php echo $thispage?>?ID='+id+'&dir='+(evt.ctrlKey ? -1 : 1)+'&absolute='+(evt.shiftKey ? 1 : 0)+'&src='+escape(window.location);
		window.location=url;
	}
	function setID2(id, evt, dir){
		var url='<?php echo $thispage?>?ID='+id+'&dir='+dir+'&absolute='+(evt.ctrlKey ? 1 : 0)+'&src='+escape(window.location);
		window.location=url;
	}
	</script>
	<?php
}
if(!function_exists('get_image')){
	function get_image($name, $images='', $options=array()){
		/*
		2009-01-15: modified output - no longer handles defaultNAImage - global $get_image no longer needed
		2008-06-30: gets image from get_file_assets() array - by precedence
		options
		imagePrefix
		imageSuffix
		externalImageFunction
		*/
		extract($options);
		if($externalImageFunction){
			//Added 2008-10-31 - this allows an external function to process, it must return $get_image - with nodes of name (case-sensitive), width, and height.  The source returned must be the actual path to the image plus name
			return $externalImageFunction($name, $images='', $options);
		}
		//assume image array = $images if not explicitly passed
		if(!$images)global $images;
		switch(true){
			case $a=$images[strtolower($imagePrefix.$name.$imageSuffix).'.jpg']:
				$get_image=$a;
			break;
			case $a=$images[strtolower($imagePrefix.$name.$imageSuffix).'.gif']:
				$get_image=$a;
			break;
			case $a=$images[strtolower($imagePrefix.$name.$imageSuffix).'.png']:
				$get_image=$a;
			break;
		}
		if(!$get_image)return '';
		preg_match('/\.[a-z]+$/i',$get_image['name'],$b);
		$get_image['extension']=str_replace('.','',strtolower($b[0]));
		return $get_image;
	}
}
if(false){
	//sample css for component
	?>
	<style type="text/css">
	table.loop1{
		width:100%;
		border-collapse:collapse;
		}
	.loop1 td.content{
		text-align:center;
		vertical-align:top;
		}
	.loop1 .content h2{
		font-weight:900;
		font-size:129%;
		color:#333;
		}
	</style>
	<?
}
//prelims
if($cols < 1 || !is_int($cols)) exit('Col must be an integer value greater than zero');
if(!$startPosn)$startPosn=($cols * ($startRow-1)) + $startCol;
$count=0;

if($records=q($categorySQL, O_ARRAY)){
	//prn($qr);
	//prn($records);
	?><table class="loop1"><?php
	$col=0;
	$row=0;
	$cells=0;
	$startOffsetCells=0;
	$endOffsetCells=0;
	$count=0;
	while(list(,$v)=each($records)){
		//handle first row(s) and starting offset
		$count++;
		if($count==1 && $addRows = floor(($startPosn-1)/$cols)){
			//these are top offset rows, modify class and content as needed
			for($i=1; $i<=$addRows; $i++){
				$row++;
				$col=0;
				$vPosition=($i==1 ? 'top' : 'mid');
				?><tr><?php
					for($j=1; $j<=$cols; $j++){
						switch($j){
							case 1:
								$hPosition='left';
								break;
							case $cols:
								$hPosition='right';
								break;
							default:
								$hPosition='center';
						}
						$startOffsetCells++; //total number of blank cells
						$col++;
						?><td class="empty <?php echo $hPosition . ' '. $vPosition?>">&nbsp;</td><?php
					}
				?></tr><?php
			}
		}
		//begin a row
		if( $col==0 ){
			$row++;
			/**
			this is inaccurate and needs logic to differentiate further between "mid" and bottom
			**/
			$vPosition=($row==1 ? 'top' : (count($records)-$cells-$startOffsetCells < $cols || $row>=$maxRows ? 'bottom' : 'mid'));
			?><tr><?php
		}
		//add initial padding cells
		if($count==1 && $startPad = ($startPosn - 1) % $cols  ){
			for($i=1; $i<=$startPad; $i++){
				switch(true){
					case $i==1:
						$hPosition='left';
						break;
					default:
						$hPosition='center';
				}
				$startOffsetCells++;
				$col++;
				?><td class="empty <?php echo $hPosition . ' ' . $vPosition?>">&nbsp;</td><?php
			}
		}
		//normal cells
		$col++;
		$cells++;
		$hPosition=($col==1 ? 'left' : ($col % $cols ==0 ? 'right' : 'mid'));
		
		//---------------------------- create the category description record ------------------------
		if($Categories_ID=q("SELECT ID FROM finan_items_categories WHERE Name='".addslashes($v['Category'])."'", O_VALUE)){
			//OK
		}else{
			//add it or we will not be able to sort
			$Categories_ID=q("INSERT INTO finan_items_categories SET CreateDate=NOW(), Creator='system', Name='".addslashes($v['Category'])."'", O_INSERTID);
		}

		?><td class="content <?php echo $hPosition . ' ' . $vPosition?>">
		<?php
		//------------------------------------ content here -------------------------------------------

		//get the number of products in this category && specials available
		$productSKUs=q("SELECT ID,SKU FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_COL_ASSOC);

		//prn($qr);
		$SubCategoryCount=q("SELECT COUNT(DISTINCT SubCategory) FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_VALUE);
		$featured=q("SELECT COUNT(Featured) FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."' AND Featured=1", O_VALUE);
		//prn("$productSKUs:$SubCategoryCount:$featured");
		
		$haveImage=$width=$height='';
		if(count($$catImgMainArray)){
			foreach($$catImgMainArray as $o=>$w){
				if(preg_replace('/[^'.$preserveDashes . $preserveSpaces . 'a-z0-9]*/i','', preg_replace('/\.(gif|jpg|png)$/i','',strtolower(($v['Category']))))==
				   preg_replace('/[^'.$preserveSpaces . $preserveDashes . 'a-z0-9]*/i','', preg_replace('/\.(gif|jpg|png)$/i','',$o))){
					$haveImage=$w['name'];
					$width=$w['width'];
					$height=$w['height'];
					break;
				}
			}
		}
		if(!$haveImage && count($productSKUs)){
			foreach($productSKUs as $y){
				if($img=get_image(strtolower($y), $$catImgBackupArray, $catImgBackupOptions)){
					$width=$img['width'];
					$height=$img['height'];
					$haveImage=preg_replace('/[^'.$preserveDashes.$preserveSpaces.'a-z0-9]*/i','',($v['Category'])).'.'.$img['extension'];
					ob_start();
					if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$catPath)){
						$a=explode('/',trim($catPath,'/'));
						$newFolder=$_SERVER['DOCUMENT_ROOT'];
						//duh.. build the folder if it's not present already
						foreach($a as $v){
							$newFolder.='/'.$v;
							if(is_dir($newFolder))continue;
							if(!mkdir($newFolder))echo 'unable to create new folder '.$newFolder;
						}
					}
					copy($_SERVER['DOCUMENT_ROOT'].'/'.$catBackupPath.'/'.$img['name'], $_SERVER['DOCUMENT_ROOT'].'/'.$catPath.'/'.$haveImage);
					$err=ob_get_contents();
					ob_end_clean();
					if($err){
						$email='Hi there,
						
						For category '.($v['Category']).', I was unable to copy product image '.$catBackupPath.'/'.$img['name'].' to '.$catPath.'/'.$haveImage.', here is the reply from the system:' . "\n". $err;
						$email.='

						Please forward this email to your site developer or point of contact.
						
						Sincerely,
						Compass Point Media Automated System
						[admin email comp_categorytable_03.php:02]';						
					}else{
						$email='Hi there,
						
						You did not have an image for the category "'.($v['Category']).'" on the category-listing page of your site '.$siteName.'.  A temporary image was found and copied from your products folder '.$catBackupPath.'/'.$img['name'].' (item number '.$y.').  This picture may not be the right size and may not be the picture you want to represent this category.  To view the category page, go to:
						
						'.rtrim($siteURL,'/').'/'.$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '').'
						To view File Explorer, go to:
						
						'.rtrim($siteURL,'/').'/admin/file_explorer/?uid=categoryfolder&folder=category&createFolder=1
						
						
						Sincerely,
						Compass Point Media Automated System
						[admin email comp_categorytable_03.php:01]';
					}
					mail($adminEmail,'Image '.($err?'unable to be ':'').'copied from products to category list for category "'.($v['Category']).'"',str_replace("\t",'',$email),$fromHdrBugs);
					break;
				}
			}
			if(!$haveImage){
				ob_start();
				print_r($productSKUs);
				$out=ob_get_contents();
				ob_end_clean();
				$out=preg_replace('/^Array\s*\(\s*/i','',trim($out));
				$out=preg_replace('/\s*\)\s*$/','',$out);
				$out=preg_replace('/ {2,}/','',$out);
				$out=str_replace("\t",'',$out);
				if(strlen($out)>200)$out=substr($out,0,200).' ... (more)';
				$email='Hi there,
				
				Your website is missing a category image for category *'.stripslashes($v['Category']).'*.  An attempt was made to use and copy a picture from products in this category (from folder '.$catBackupPath.') but no pictures were found for the products in this category.  Please sign in and upload a picture for this category.
				If you have pictures for products in this category, it is most likely due to the fact that your picture names do not match the SKU number (item number) of your products.  The products in this category were:
				
				'.$out.'
				
				Sincerely,
				Compass Point Media Automated System
				[admin email comp_categorytable_03.php:03]'."\n\n".get_globals();
				mail($adminEmail,'Image unable to be copied from products to category list for category "'.($v['Category']).'"',str_replace("\t",'',$email),$fromHdrBugs);
			}
		}

		//this works great for single.php - enhance its functionality for subcategory.php - use it for a featured product or one to bring to the top
		$singleProductID=q("SELECT ID FROM finan_items WHERE Category='".addslashes($v['Category'])."'", O_VALUE);

		if(isset($maxCatThumbnailWidth) && $maxCatThumbnailWidth>0 && $width>$maxCatThumbnailWidth){
			$width=$maxCatThumbnailWidth;
			$height='';
		}
		/*
		categoryPage - pilots-supply-products-by-category.php (8001)
		logic below:
		if(one product){
			singlePage - pilots-supply-single.php (8004)
		}else if(1 subcategory){
			productsPage - pilots-supply-product-listing.php (8003)
		}else{
			subCategoryPage - pilots-supply-product-category.php (8002)
		}
		
		*/

		?>
		<a href="/products/<?php echo count($productSKUs)==1?$pageHandles['singlePage']: ($SubCategoryCount==1 ? $pageHandles['productsPage'] : $pageHandles['subCategoryPage'])?>?Category=<?php echo urlencode($v['Category']=='(uncategorized)' ? '' : $v['Category']);?>&SubCategory=<?php echo urlencode(q("SELECT DISTINCT SubCategory FROM finan_items i WHERE i.Active=1 AND (Type='Non-inventory Part' OR Type IS NULL OR Type='') AND Category='".addslashes($v['Category'])."'", O_VALUE));?>&ID=<?php echo $singleProductID;?>" title="See products for <?php echo stripslashes($v['Category'])?>"><?php
		//show product picture
		if($haveImage){
			?><img alt="category image" <?php echo $height?'height="'.$height.'"':''?> width="<?php echo $width?>" src="/<?php echo trim($catPath,'/') . '/'.$haveImage?>" /><?php
		}else if($defaultNAImage && file_exists(rtrim($defaultNAImagePath,'/') . '/'.$defaultNAImage)){
			?><img alt="category image unavailable" height="<?php echo $defaultNAImageHeight?>" width="<?php echo $defaultNAImageWidth?>" src="/<?php echo trim($defaultNAImagePath,'/') . '/'.$defaultNAImage?>" /><?php
		}else{
			//do nothing
			?>&nbsp;<?php
		}
		?>
		<h2><?php
		if($featured && !$hideFeaturedAvailable){
			?><span class="featuredAvailable"><img src="/images/i/star1.jpg" alt="Featured products available" /></span>&nbsp;<?php
		}
		echo $v['Category'];
		?></h2>
		</a>
		<?php
		if($adminMode){
			if($allowCategoryRanking){
				/*
				?><span title="Hold down the shift key to move category to the absolute top or bottom of this list"><img  style="cursor:pointer" id="priority<?php echo $Categories_ID;?>" src="images/i/red up-down toggle.jpg" onclick="setID(<?php echo $Categories_ID;?>,event)" /> Category Rank</span>
				<br /><?php
				*/

				?><div class="_editLink_1"><span class="ctrls">
				<img title="Move category UP (press the control key to move to ABSOLUTE top)" alt="move up" style="cursor:pointer" id="priority<?php echo $Categories_ID;?>+1" src="/images/i/red-up-toggle.jpg" onclick="setID2(<?php echo $Categories_ID;?>,event,1)" />
				<img title="Move category DOWN (press the control key to move to ABSOLUTE bottom)" alt="move down" style="cursor:pointer" id="priority<?php echo $Categories_ID;?>-1" src="/images/i/red-down-toggle.jpg" onclick="setID2(<?php echo $Categories_ID;?>,event,-1)" />
				</span>	Category Rank</div><?php
			}
			?>
			<a class="_editLink_1" title="Edit images folder" onclick="return ow(this.href,'l1_subcategory','700,700');" href="/admin/file_explorer/?folder=category&uid=category"><img src="images/i/edit2.gif" alt="edit images" width="15" height="18">&nbsp;Edit Images</a> <br>
			<?php
		}
		//-----------------------------------------------------------------------------------------------------------
		?>
		
		</td>
		<?php
		//closing cells
		if($count==count($records) && !($cols==$col)){
			$lastCol=$col;
			for($i=1; $i<=($cols-$lastCol); $i++){
				switch($i){
					case $cols-$lastCol:
						$hPosition='right';
						break;
					default:
						$hPosition='mid';
				}
				$endOffsetCells++;
				$col++;
				?><td class="empty <?php echo $hPosition . ' '. $vPosition?>">&nbsp;</td><?php
			}
		}
		//end a row
		if( $col % $cols == 0){
			$col=0;
			?></tr><?php
			if($row>=$maxRows)break;
		}
	}
	?></table><?php
}else{
	?><h4>Currently, no items in this category</h4><?php
}
?>