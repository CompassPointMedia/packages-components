<?php
if(false){ 
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

?>