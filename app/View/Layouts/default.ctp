<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'BYU InfoHub');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		// echo $this->Html->css('cake.generic');
		echo $this->Html->css('styles');
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href='http://fonts.googleapis.com/css?family=Rokkitt:400,700' rel='stylesheet' type='text/css'>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" />
	<script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
	
	<script>
		var headIn;
		var winOu;
		var headFor;
		var headFinal;

		$(document).ready(function(){
			// Shows mobile menu
			$('#mob-nav').click(function() {
				$('#mainNav').toggle("slide", { direction: "left" }, 300);
			});

			// Help Pop-out functionality
			$("#deskTopHelp").click(function() {
				$(this).hide();
				widenBorder();
				$("#nhContent").show("slide", { direction: "right" }, 500);
			});
			$(".close").click(function() {
				if ($(window).width() > 750) {
					$("#nhContent").hide("slide", { direction: "right" }, 500, function() {
						$("#deskTopHelp").fadeIn("fast");
					});
				}
				else {
					$("#nhContent").slideUp();	
				}
			});

			$("#mobileHelp").click(function() {
				$("#nhContent").slideToggle();
			});

			$('.editQL').click(function() {
				$('.ql-edit').addClass('active');
				$('.ql-list').addClass('ql-active');
				$('.quickLink').addClass('active-link')
			});
			$('.saveEdit').click(function() {
				$('.ql-edit').removeClass('active');
				$('.ql-list').removeClass('ql-active');
				$('.quickLink').removeClass('active-link')
			});
		});

		$(document).ready(resizeFonts);	
		$(document).ready(iniVars);	
		$(window).resize(resizeFonts);	
		$(window).resize(iniVars);	
      		$(window).load(resizeFonts);
      		$(window).load(iniVars);
      		// $(window).resize(function(){location.reload();});

      		//Get values 
      		function iniVars() {
      			headIn = $("#headerInner").width();
			winOut = $(window).width();
			headFor = winOut - headIn;
			headFinal = headFor / 2;
      		}

      		//Sets right border for "Need Help" fly-out on larger screens
      		function widenBorder() {
   			//    iniVars();
   			// 	if (winOut > 1090) {
			// 	$("#nhContent").css("border-right-width", headFinal);
			// }
			// else {
			// 	$("#nhContent").css("border-right-width", 0);
			// }
			$("#nhContent").css("border-right-width", 0);
      		}

		function resizeFonts(){
			var defSize = 10;
			var mobileWidth = 550;

			// reset font size when in mobile view
			if($(window).width()<=mobileWidth){
				$('body').css('fontSize', defSize);
				return false;
			}
			
			var size = $(document).width() / 1230;
			var maxSize =  10;
			var minSize = 6.5;
			size = defSize * size;
			if(size>maxSize) size=maxSize;
			if(size<minSize) size=minSize;
			$('body').css('fontSize', size);
		}

		function searchAutoComplete( e ) {
            if ($.trim($('#searchInput').val()) == ''){
				$('.autoComplete').hide();
			}else if  ( e == true ) {
				$('.autoComplete').hide();
			}else {
				var val = $('#searchInput').val();
                $.get( "/search/autoCompleteTerm", { q: val } )
                    .done(function( data ) {
                        $('.autoComplete .results').html(data);
                        $('.autoComplete li').click(function(){
                            $('#searchInput').val($(this).text());
                            $('#searchInput').parent().submit();
                            $('.autoComplete').hide();
                        });
                        $('.autoComplete').show();
                });
			}
		}

		function addQL(inQL) {	
			var proceed = "0";	
			// var qlAddText = "Added to MyQuick Links";
			$.ajax({
				type: 'GET',
				url: '/ajax/checkQL.php?QL='+inQL,
				data: $(this).serialize()
			})
			.done(function(data){	
			    if (data == "1") {		   				   				  			 
					$.ajax({
						type: 'GET',
						url: '/ajax/addQL.php?QL='+inQL,
						data: $(this).serialize()
					})
					.done(function(data){				 
						// show the response						
						$('#QLContainer').html(data);
						// $('.qlText').css('color', '#929292');
						// $('.addQuickLink .qlText').html(qlAddText);
						$('.addQuickLink img').attr('src', '/img/iconStarBlue.gif');			 
					})
					.fail(function() {			 									 
					});	 
					return false;	
				} else if (data == "2") {											         	
					// $('.qlText').css('color', '#929292');
					// $('.addQuickLink .qlText').html(qlAddText);
					$('.addQuickLink img').attr('src', '/img/iconStarBlue.gif');
					return false;
				}else {
					alert('You may only have up to 9 Quick Links.  Remove 1 or more Quick Links by clicking the "Edit My Quick Links" button below the Quick Links Area before adding new Quick Links.');	
				}			
			})
			.fail(function() {			 							 
			});										
		}
		$(document).on( 'click', function ( e ) {
			if ( $( e.target ).closest('.autoComplete').length === 0 ) {
				$('.autoComplete').hide();
			}
		});

		$(document).on( 'keyup', function ( e ) {
			if ( e.keyCode === 27 ) { // ESC
				searchAutoComplete(true);
			}
		});
	</script>

</head>
<body>
	<div id="container">
		<header>
			<div id="headerInner" class="inner">
				<h1><a href="/" id="logo">BYU InfoHub</a></h1>
				<h2><a href="/" id="site-title">InfoHub</a></h2>
				<div id="headerRight">
					<span class="userInfo">
						<?php echo $this->Html->link('Login', '/login'); ?>
					</span>
					<?php echo $this->Html->link(
							    $this->Html->image("/img/icon-settings.png", array("alt" => "Settings")),
							    "/myaccount",
							    array('escape' => false, 'id' => 'settingsWheel'));
					?>
					<!-- <a id="settingsWheel"><img src="" alt="Settings"></a> -->

					<!-- Below is fixed pos. on destop -->
					<div id="needHelp">
						<a id="mobileHelp"><img src="/img/icon-question.png" alt="Need Help?"></a>
						<a id="deskTopHelp" class="grow">Need <br>Help? <br><span>&nbsp;</span></a>
						<div id="nhContent">
							<a class="close">Close <br>X</a>
							<div id="nhLeft">
								<h3>Have questions? We’re here to help.</h3>
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod veniam, quis nostrud exercitation ullamco laboris nisiut aliquip utexa commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur datat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. </p>
								<a href="">Contact Us</a>
							</div>
							<img src="/img/questionQuote.gif" alt="Have Questions?">
						</div>
					</div>

				</div>
			</div>
		</header>
		<nav>
			<a id="mob-nav" class="box-shadow-menu inner">&nbsp;</a>
			<ul id="mainNav" class="inner">
				<!-- <li><?php echo $this->Html->link('Search', '/search', array('id' => 'searchLink')); ?></li>
				<li><?php echo $this->Html->link('Find People', '/people', array('id' => 'findLink')); ?></li>
				<li><?php echo $this->Html->link('Resources', '/resource', array('id' => 'resourceLink')); ?></li> -->
				<li><a href="/search" id="searchLink">Search</a></li>
				<li><a href="/people" id="findLink">Find People</a></li>
				<li><a href="/resource" id="resourceLink">Resources</a></li>
			</ul>
		</nav>
		<div id="content">

			<?php echo $this->Session->flash(); ?>

			<?php echo $this->fetch('content'); ?>
		</div>
		<footer>
			<div id="footerTop">
				<div class="inner">
					<h4>Univerisity Contact&nbsp;&nbsp;&nbsp;</h4>
					<div class="footerBox">
						<p>Mailing address: <br>Brigham Young University <br> Provo, UT 84602</p>
					</div>
					<div class="footerBox">
						<p>Telephone: <br>801-422-4636 or <br>801-422-1211</p>
					</div>
					<div class="footerBox">
						<p>Web: <br><a href="/contact">Contact Us</a></p>
					</div>
					<div class="footerBox">
						<p>Directions: <br><a href="">Google Maps</a></p>
					</div>
				</div>
			</div>
			<div id="footerBottom">
				<div class="inner">
					<p>
						<a href="http://www.byui.edu/">BYU–Idaho</a> 
						<a href="http://www.byuh.edu/">BYU–Hawaii</a> 
						<a href="http://www.ldsbc.edu/">LDS Business College</a> 
						<a href="http://ce.byu.edu/sl/">Salt Lake Center</a> 
						<a href="http://ce.byu.edu/jc/">Jerusalem Center</a> 
						<a href="http://www.mtc.byu.edu/">Missionary Training Center</a>
					</p>
					<p>
						<a href="http://www.lds.org" style="margin:0 auto">The Church of Jesus Christ of Latter-day Saints</a><br>
						<a href="http://home.byu.edu/home/copyright">Copyright ©2015, All Rights Reserved</a>
					</p>
				</div>
			</div>
		</footer>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
