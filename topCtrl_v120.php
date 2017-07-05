<?php
/* Top Control Widget; created 2010-04-13 by Samuel 
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
if(!$topCtrlSearchLabel)$topCtrlSearchLabel='Quick Search:';
if(!$topCtrlLabelSignOut)$topCtrlLabelSignOut='Sign out';
if(!$topCtrlLabelSignIn)$topCtrlLabelSignIn='Sign in';
if(!isset($topCtrlLabelYourOrder))$topCtrlLabelYourOrder='Your order';
if(!$topCtrlLabelNewAccount)$topCtrlLabelNewAccount='New account';
if(!$topCtrlLabelOrderLink)$topCtrlLabelOrderLink='Your Order';
if(!$topCtrlLabelSearchURL)$topCtrlLabelSearchURL='search-page.php';
if(!$topCtrlLabelSearchButton)$topCtrlLabelSearchButton='GO';
if(!$topCtrlSwitchboardURL)$topCtrlSwitchboardURL="/cgi/index.php";
if(!$topCtrlNewAccountURL)$topCtrlNewAccountURL="/cgi/index.php";
if(!isset($topCtrlCartImage))$topCtrlCartImage="/images/i/cart-hamoni.gif";
//topCtrlHideNewAccountOption - default false

if($topCtrlRewrite)ob_start();
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
			<a title="View my current pain relief e-book order" href="<?php echo $shoppingCartURL?>"><?php echo $topCtrlLabelYourOrder?> (<span id="orderCount"><?php
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
			$return=urlencode('../'.($thisfolder?$thisfolder.'/':'').$thispage . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
			if($_SESSION['cnx'][$cnxKey]){ 

				//they are signed in
				?>
				<li class="topCtrlAcctLink">
				<?php
				//---------------------- begin link ------------------------
				ob_start(); 
				?>
				Welcome <a title="View and edit your client information" href="<?php echo $topCtrlSwitchboardURL?>"><?php echo $_SESSION['cnx'][$MASTER_DATABASE]['firstName']. ' ' . $_SESSION['cnx'][$MASTER_DATABASE]['lastName']?></a>
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
				<a href="/cgi/login.php?logout=1&src=<?php echo $return?>" title="sign out"><?php echo $topCtrlLabelSignOut?></a>
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
				<a title="Signin - Access your account" href="/cgi/login.php?src=<?php echo $return;?>"><?php echo $topCtrlLabelSignIn?></a>
				<?php
				$n=ob_get_contents();
				ob_end_clean();
				echo $topCtrlSignInLink=$n;
				//----------------------- end link -------------------------
				?>				
				</li>
				<?php if(!$topCtrlHideNewAccountOption){ ?>
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
			if($topCtrlRSSFeedURL){
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
			?>
		</ul>
	</div>
</div><?php
if($topCtrlRewrite){
	$topCtrlOutput=ob_get_contents();
	ob_end_clean();
}
?>