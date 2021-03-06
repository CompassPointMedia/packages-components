<?php
/* name=Sign In/Account Links (with search); description=This allows a site visitor to create an account or sign in.  If the e-commerce module is installed, it includes a search box for products. */
/* Top Control Widget; created 2010-04-13 by Samuel 
2011-12-26 - v1.40
------------------
* replaced ugly shopping cart icon

2011-05-11
----------
completely parameterized all of the php pages referenced so we can use /101 /102 etc for the Juliet project cgi "quasi" component

2011-03-09: v1.20 by Samuel
---------------------------
* new vars: topCtrlRewrite, topCtrlCartLink, topCtrlAcctLink, topCtrlSignOutLink, topCtrlSignInLink, topCtrlNewAcctLink
* also: topCtrlLabelYourOrder=default:Your order - with orderCount span present

2010-5-10
---------
Parker
	Moved to Components 

CSS:
#topCtrl 
	#search
	.matchrow .bg
	.matchrow
		(ul)
			.topCtrlAcctLink
			.topCtrlSignOutLink
			.topCtrlSignInLink
			.topCtrlNewAcctLink
*/
//settings
if(!isset($topCtrlImplementSearch))$topCtrlImplementSearch=true;
if(!isset($topCtrlImplementShoppingCartLink))$topCtrlImplementShoppingCartLink=false;
if(empty($topCtrlSearchLabel))$topCtrlSearchLabel='Quick Search:';
if(empty($topCtrlLabelSignOut))$topCtrlLabelSignOut='Sign out';
if(!$topCtrlLabelSignIn)$topCtrlLabelSignIn='Sign in';
if(!isset($topCtrlLabelYourOrder))$topCtrlLabelYourOrder='Your order';
if(empty($topCtrlLabelNewAccount))$topCtrlLabelNewAccount='New account';
if(empty($topCtrlLabelOrderLink))$topCtrlLabelOrderLink='Your Order';
if(empty($topCtrlLabelSearchURL))$topCtrlLabelSearchURL='search-page.php';
if(empty($topCtrlLabelSearchButton))$topCtrlLabelSearchButton='GO';
if(empty($topCtrlSwitchboardURL))$topCtrlSwitchboardURL="/cgi/switchboard";
if(empty($topCtrlNewAccountURL))$topCtrlNewAccountURL="/cgi/usemod";
if(empty($topCtrlLoginURL)) $topCtrlLoginURL="/cgi/login";
if(empty($topCtrlCartImage))$topCtrlCartImage="/images/i/findicons.com-shoppingcart01.png";
//topCtrlHideNewAccountOption - default false

if($topCtrlRewrite)ob_start();
if(empty($refreshComponentOnly)){
	ob_start();?>
	<style type="text/css">
	ul.topCtrlListLinks{
		margin:0px;
		}
	.topCtrlListLinks li{
		padding-left:15px;
		}
	.topCtrlListLinks li a{
		color:midnightblue;
		}
	</style>
	<?php
	echo $topCtrlCSS=get_contents();
	ob_start();
	?>
	<script language="javascript" type="text/javascript">
	
	</script>
	<?php
	echo $topCtrlJS=get_contents();
}
?><div id="topCtrl">
	<?php
	if($topCtrlImplementSearch){
		ob_start();
		?><div id="search">
			<form id="searchForm" name="searchForm" method="get" action="<?php echo $topCtrlLabelSearchURL?>">
				<strong><?php echo $topCtrlSearchLabel?></strong>
				<input name="q" type="text" class="inpu1" id="q" />
				<input name="srch" id="srch" value="1.0" type="hidden"  />
				<input type="submit" name="submit" value="<?php echo $topCtrlLabelSearchButton?>" />
				<script language="javascript" type="text/javascript"> g('q').focus(); </script>
			</form>
		</div><?php
		echo $topCtrlSearch=get_contents();
	}
	?>
	<div class="matchrow bg"> </div>
	<div class="matchrow">
		<ul class="topCtrlListLinks">
			<?php if($topCtrlImplementShoppingCartLink){ ?>
			<li>
			<?php
			//---------------------- begin link ------------------------
			ob_start();
			?>
			<span id="myOrder">
			<a title="View my current order" href="<?php echo $shoppingCartURL?>"><?php
			if($topCtrlCartImage){
				?><img src="<?php echo $topCtrlCartImage?>" alt="cart icon" align="absbottom" /> <?php
			}
			?><?php echo $topCtrlLabelYourOrder?> (<span id="orderCount"><?php
			$shopCartTotal=0;
			if($a=$_SESSION['shopCart']['default'])
			foreach($a as $v)$shopCartTotal+=$v['Quantity'];
			echo $shopCartTotal;
			?></span>)
			</a>
			</span>			
			<?php
			$n=ob_get_contents();
			ob_end_clean();
			echo $topCtrlCartLink=$n;
			//----------------------- end link -------------------------
			?>
			</li>
			<?php }?>
			<?php
			if(!empty($topCtrlFixedSigninReturn)){
				$return = $topCtrlFixedSigninReturn;
			}else{
				$qs=explode('?',$_SERVER['REQUEST_URI']);
				$qs=(!empty($qs[1]) ? $qs[1] : '');
				if($thispage=='login' || $thispage=='login.php'){
					$return=$src;
				}else{
					$return = urlencode('/'.( $thisfolder ? $thisfolder . '/' : '').(!empty($thissubfolder) ? $thissubfolder . '/' : '').($thispage == 'index' ? '' : $thispage) . (!empty($qs) ? '?' . $qs : ''));
				}
			}
			if(!empty($_SESSION['cnx'][$cnxKey])){

				//they are signed in
				?>
				<li class="topCtrlAcctLink">
				<?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?>
				Welcome <a title="View and edit your client information" href="<?php echo $topCtrlSwitchboardURL?>"><?php echo $_SESSION['cnx'][$acct]['firstName']. ' ' . $_SESSION['cnx'][$acct]['lastName']?></a>
				<?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlAcctLink=$n;
				//----------------------- end link -------------------------
				?>
				</li>
				
				<li class="topCtrlSignOutLink">
				<?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?>
				<a href="<?php echo $topCtrlLoginURL?>?logout=1&src=<?php echo $return;?>" title="sign out"><?php echo $topCtrlLabelSignOut?></a>
				<?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlSignOutLink=$n;
				//----------------------- end link -------------------------
				?>
				</li>
				<?php
			}else{ 
				
				//not signed in
				?><li class="topCtrlSignInLink">
				<?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?>				
				<a title="Signin - Access your account" href="<?php echo $topCtrlLoginURL?>?src=<?php echo $return;?>"><?php echo $topCtrlLabelSignIn?></a>
				<?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlSignInLink=$n;
				//----------------------- end link -------------------------
				?>				
				</li>
				<?php if(empty($topCtrlHideNewAccountOption)){ ?>
				<li class="topCtrlNewAcctLink">
				<?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?>				
				<a title="Create a new account<?php echo $companyName ? ' with '.$companyName : '';?>" href="<?php echo $topCtrlNewAccountURL;?>"><?php echo $topCtrlLabelNewAccount; ?></a>
				<?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlNewAcctLink=$n;
				//----------------------- end link -------------------------
				?>				
				</li>
				<?php } ?>
				<?php
			}
			if(!empty($topCtrlRSSFeedURL)){
				?><li><?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?><a href="<?php echo $topCtrlRSSFeedURL?>" target="_blank"><img src="/images/i/SNW/rss-30.png" width="30" height="30" alt="rss feed link" /></a><?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlRSSFeedLink=$n;
				//----------------------- end link -------------------------
				?></li><?php
			}
			if(!empty($topCtrlCustomLinks)){
				foreach($topCtrlCustomLinks as $n=>$v){
					?><li><?php
					echo $v['leftspacer'];
					//---------------------- begin link ------------------------
					ob_start(); 
					?>
					<a href="<?php echo $v['link']?>" title="<?php echo h($v['title']);?>" <?php if($v['popup']){ 
					//handle popup window
						if(is_bool($v['popup'])){
							?>onclick="return ow(this.href,'l1_<?php echo $n?>','<?php echo $v['dims'] ? $v['dims'] : '500,600';?>');"<?php
						}else{
							?>onclick="<?php echo $v['popup'];?>"<?php
						}
					} ?>><?php echo $v['label'];?></a>
					<?php
					$out=ob_get_contents();
					ob_end_clean();
					echo $topCtrlCustomLinks[$n]['output']=$out;
					//----------------------- end link -------------------------
					echo $v['rightspacer'];
					?></li><?php
				}
			}
			?>
		</ul>
	</div>
</div><?php
if($topCtrlRewrite){
	$topCtrlOutput=ob_get_contents();
	ob_end_clean();
}
?>