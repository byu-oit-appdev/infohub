<?php
	$this->Html->css('secondary', null, array('inline' => false));
	$this->Html->css('search', null, array('inline' => false));
?>
<script>
	$(document).ready(function() {
		$("#searchLink").addClass('active');

		$('li a').click(function (e) {
			$(this).toggleClass('active');
			e.preventDefault();
            
            var community = $(this).attr('data-c');
            $.post("/search/loadChildDomains", { c:community })
                .done(function(data) {
                    var objDomains = JSON.parse(data);
                    alert(objDomains.vocabularyReference[0].name)
                    var ullist = $(this).parent().children('ul:first');
                    ullist.slideToggle();
                    listWidth();
            });
		});
        
        loadCommunityData(null, 'catalogList0');
	});

	$(document).ready(listWidth);
	$(window).resize(listWidth);

	function listWidth() {
		$('.catalogChild').css('width', '100%').css('width', '-=11px');
		$('.grandChild').css('width', '100%').css('width', '-=11px');
		$('.greatGrandChild').css('width', '100%').css('width', '-=11px');
	}
    
    function loadCommunityData(community, listID){
        $.post("/search/loadCommunityData", { c:community })
            .done(function(data) {
                var objDomains = JSON.parse(data);
                var html = '';
                // create community elements
                for(i=0; i<objDomains.aaData[0].Subcommunities.length; i++){
                    var comm = objDomains.aaData[0].Subcommunities[i];
                    html += '<li class="catalogItem" id="c'+comm.subcommunityid+'">';
                    if(comm.hasNonMetaChildren=='true'){
                        html += '   <a href="#" class="hasChildren">'+comm.subcommunity+'</a>'+
                            '   <ul id="categoryList'+comm.subcommunityid+'" class="subList catalogChild">'+
                            '       <li><a href=""><img src="/img/dataLoading-sm.gif" alt="Loading..."></a></li>'+
                            '   </ul>';
                    }else{
                        
                    }
                    html += '</li>';
                }
                
                // create vocabulary elements
                if(objDomains.aaData.length>1){
                    for(i=0; i<objDomains.aaData[1].Vocabularies.length; i++){
                        var vocab = objDomains.aaData[1].Vocabularies[i];
                        html += '<li class="catalogItem">'+
                            '   <a class="vocab" href="/search/listTerms/'+vocab.vocabularyid+'">'+vocab.vocabulary+'</a>'+
                            '</li>';
                    }
                }
                
                // add click event to show/hide and load child data
                $('#'+listID).html(html).find('li a').not('.vocab').click(function (e) {
                    $(this).toggleClass('active');
                    e.preventDefault();
                    
                    // load child communities and vocabularies if they haven't been loaded
                    if($(this).parent().find('li').length==1){
                        var cid = $(this).parent().attr('id').substring(1);
                        loadCommunityData(cid, 'categoryList'+cid);
                    }
                                      
                    var ullist = $(this).parent().children('ul:first');
                    ullist.slideToggle();
                    listWidth();
                });
                //alert(objDomains.aaData[0].Subcommunities[0].subcommunity)
                
        });
    }
</script>

<!-- Background image div -->
<div id="searchBg" class="deskBg">
</div>

<!-- Request list -->
<div id="searchBody" class="innerLower">
	<div id="searchTop">
		<h1 class="headerTab" >Search Information</h1>
		<div class="clear"></div>
		<div id="stLower" class="whiteBox">
			<form action="submit">
				<input type="text" class="inputShade" placeholder="Search keyword, topic, or phrase">
				<input type="submit" value="Search" class="inputButton">
			</form>
			<div class="clear"></div>
		</div>
	</div>

	<div id="searchMain">
		<h2 class="headerTab" >Full Catalog</h2>
		<div class="clear"></div>
		<div id="smLower" class="whiteBox">
			<ul class="catalogParent" id="catalogList0">
                <a href=""><img src="/img/dataLoading-sm.gif" alt="Loading..."></a>
			    <?php
                    /*foreach($communities->communityReference as $c){
                        echo '<li class="catalogItem">'.
                            '   <a data-cid="'.$c->resourceId.'" href="#" class="hasChildren">'.$c->name.'</a>'.
                            '   <ul class="subList catalogChild">'.
                            '       <li class="catalogItem"><a>Lorem</a></li>'.
                            '       <li class="catalogItem"><a>Ipsum</a></li>'.
                            '   </ul>'.
                            '</li>';   
                    }*/
                ?>
				<!--<li class="catalogItem">
					<a class="hasChildren">Academic</a>
					<ul class="subList catalogChild">
					    <li class="catelogItem">
					        <a href=""><img src="/img/dataLoading-sm.gif" alt="Loading..."></a>
					   </li>
						<li class="catalogItem">
							<a class="hasChildren">Academic Leadership</a>
							<ul class="subList grandChild">
								<li class="catalogItem"><a>Lorem</a></li>
								<li class="catalogItem"><a>Ipsum</a></li>
							</ul>
						</li>
						<li class="catalogItem"><a>CES Admissions</a></li>
						<li class="catalogItem"><a>Class Scheduling</a></li>
						<li class="catalogItem"><a>Course and Instructor Ratings</a></li>
					</ul>
				</li>
				<li class="catalogItem">
					<a class="hasChildren">Advancement</a>
					<ul class="subList catalogChild">
						<li class="catalogItem"><a>Lorem</a></li>
						<li class="catalogItem"><a>Ipsum</a></li>
					</ul>
				</li>
				<li class="catalogItem">
					<a class="hasChildren">Financial</a>
					<ul class="subList catalogChild">
						<li class="catalogItem"><a>Lorem</a></li>
					</ul>
				</li>-->
			</ul>
		</div>
	</div>
</div>

<!-- Quick links -->
<?php echo $this->element('quick_links'); ?>
