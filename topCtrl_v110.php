<?php
/* Top Control Widget; created 2010-04-13 by Samuel 
2010-5-10
Parker
	Moved to Components 

CSS:
#topCtrl 
	#search
	.matchrow .bg
	.matchrow
		(ul)
			.tctrlAcctLink
			.tctrlSignOutLink
			.tctrlSignInLink
			.tctrlNewAcctLink
*/
//settings
if(!isset($topCtrlImplementSearch))$topCtrlImplementSearch=false;
if(!isset($topCtrlImplementShoppingCartLink))$topCtrlImplementShoppingCartLink=false;
if(!$topCtrlSearchLabel)$topCtrlSearchLabel='Quick Search:';
if(!$topCtrlLabelSignOut)$topCtrlLabelSignOut='Sign out';
if(!$topCtrlLabelSignIn)$topCtrlLabelSignIn='Sign in';
if(!$topCtrlLabelNewAccount)$topCtrlLabelNewAccount='New account';
if(!$topCtrlLabelOrderLink)$topCtrlLabelOrderLink='Your Order';
if(!$topCtrlLabelSearchURL)$topCtrlLabelSearchURL='search-page.php';
if(!$topCtrlLabelSearchButton)$topCtrlLabelSearchButton='GO';
if(!$topCtrlSwitchboardURL)$topCtrlSwitchboardURL="/cgi/index.php";
if(!$topCtrlNewAccountURL)$topCtrlNewAccountURL="/cgi/add_modify.php";
if(!isset($topCtrlCartImage))$topCtrlCartImage="/images/i/cart-hamoni.gif";
//topCtrlHideNewAccountOption - default false

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
		<ul class="tctrlListLinks">
			<?php if($topCtrlImplementShoppingCartLink){ ?>
			<li><a href="{shopping-cart-url}"><?php if($topCtrlCartImage){ ?><img src="<?php echo $topCtrlCartImage;?>" alt="cart" width="16" height="16" /><?php } ?><?php echo $topCtrlLabelOrderLink?></a>
			</li>
			<?php }?>
			<?php
			if($topCtrlFixedSigninReturn){
				$return=$topCtrlFixedSigninReturn;
			}else{
				$return=urlencode('/'.($thisfolder?$thisfolder.'/':'').$thispage . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
			}
			if($_SESSION['identity']){ 

				//they are signed in
				?>
				<li class="tctrlAcctLink">Welcome <a title="View and edit your client information" href="<?php echo $topCtrlSwitchboardURL?>"><?php echo $_SESSION['cnx'][$MASTER_DATABASE]['firstName']. ' ' . $_SESSION['cnx'][$MASTER_DATABASE]['lastName']?></a></li>
				<li class="tctrlSignOutLink"><a href="/cgi/login.php?logout=1&src=<?php echo $return?>" title="sign out"><?php echo $topCtrlLabelSignOut?></a>
				</li>
				<?php
			}else{ 
				
				//not signed in
				?><li class="tctrlSignInLink"><a title="Signin - Access your account" href="/cgi/login.php?src=<?php echo $return;?>"><?php echo $topCtrlLabelSignIn?></a></li>
				<?php if(!$topCtrlHideNewAccountOption){ ?>
				<li class="tctrlNewAcctLink"><a title="Create a new account<?php echo $companyName ? ' with '.$companyName : '';?>" href="<?php echo $topCtrlNewAccountURL;?>"><?php echo $topCtrlLabelNewAccount; ?></a></li>
				<?php } ?>
				<?php
			}?>
		</ul>
	</div>
</div>
