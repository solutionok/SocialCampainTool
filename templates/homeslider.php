</div>

<?php
$learn_more = '';
if(file_exists(__ROOT__.'/help.php'))$learn_more = makeuri('help.php');
else if(file_exists(__ROOT__.'/docs/'))$learn_more = makeuri('docs/');
?>

<script src="<?php echo site_url().'/js/jssor.slider.mini.js'?>"></script>
<script src="<?php echo site_url().'/js/homepage.slider.js'?>"></script>

<!-- Jssor Slider Begin -->
<!-- To move inline styles to css file/block, please specify a class name for each element. --> 
<div id="slider1_container" style="position: relative; margin: 0 auto;
    top: 0px; left: 0px; width: 1300px; height: 500px; overflow: hidden;margin-top:-29px;">
    <!-- Loading Screen -->
    <div u="loading" style="position: absolute; top: 0px; left: 0px;">
        <div style="filter: alpha(opacity=70); opacity: 0.7; position: absolute; display: block;
            top: 0px; left: 0px; width: 1300px; height: 500px">
        </div>
        <div style="position: absolute; display: block; background: url(<?php echo site_url()?>/images/loading.gif) no-repeat center center;
            top: 0px; left: 0px; width: 1300px; height: 500px">
        </div>
    </div>
    <!-- Slides Container -->
    <div u="slides" id="home_sliders" style="cursor: move; position: absolute; left: 0px; top: 0px; width: 1300px;
        height: 500px; overflow: hidden; display:none">
        <div>
            <img u="image" src="<?php echo site_url()?>/images/red.jpg" />
            
            <!--socialhands-->
            <div u="caption" t="NO" t3="RTT|2" r3="137.5%" du3="2000" d3="2000" style="position: absolute; top: 100px; left: 650px;">
            	 <img src="<?php echo site_url()?>/images/social-media6.png" style="position: absolute; top: 0px; left: 0px;" />  
            </div>
            <!--/socialhands-->
            
            <!--welcome-->
            <div u="caption" t="B" t2="NO" style="position: absolute; width: 500px; height: 120px; top: 50px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 40px;
                    color: #FFFFFF;"><?php echo $lang['index'][0]?> <?php echo $settings['site_name']?>
            </div>
            <!--/welcome-->
            
            <!--buttons-->
            <div u="caption" t="R" t2="NO" style="position: absolute; width: 500px; height: 120px; top: 210px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 20px;
                    color: #FFFFFF;">
            	<a class="btn btn-info btn-lg" href="<?php echo makeuri('login.php')?>">
                	<i class="glyphicon glyphicon-play"></i>&nbsp;&nbsp;<?php echo $lang['index'][3]?>
                </a>
                &nbsp;
    			<a class="btn btn-primary btn-lg" href="<?php echo $learn_more?>">
                	<i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;<?php echo $lang['index'][4]?>
                </a>
            </div>
            <!--/buttons-->
            
            <!--manageall-->
            <div u="caption" t="L" t3="R" du3="1000" d3="3000" style="position: absolute; width: 480px; height: 120px; top: 310px; left: 30px; padding: 5px;
                text-align: left; line-height: 36px; font-size: 30px;
                    color: #FFFFFF;">
                    <?php echo $lang['index'][1]?>
            </div>
            <!--/manageall-->
            
            <!--3logo-->
            <div u="caption" t="RTT|2" r="-75%" du="1500" d="1000" t2="NO" style="position: absolute; width: 470px; height: 220px; top: 120px; left: 650px;">
               <img src="<?php echo site_url()?>/images/fty.png" style="position: absolute; width: 470px; height: 220px; top: 0px; left: 0px;" /> 
            </div>
            <!--/3logo-->
            
            <!--multipleaccs-->
            <div u="caption" t="L" t2="NO" du="1000" d="1000" style="position: absolute; width: 480px; height: 120px; top: 310px; left: 30px; padding: 5px;
                text-align: left; line-height: 36px; font-size: 30px;
                    color: #FFFFFF;">
                    <?php echo $lang['index'][21]?>
            </div>
            <!--/multipleaccs-->
            
        </div>
        
        <div>
            <img u="image" src="<?php echo site_url()?>/images/blue.jpg" />
            
            <div u="caption" t="MCLIP|R" t3="CLIP|LR" du3="1000" d3="3000" style="position: absolute; width: 500px; height: 200px; top: 50px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 30px;
                    color: #FFFFFF;">
					<?php echo $lang['index'][13]?>
            </div>
            
            <div u="caption" t="L" t2="NO" style="position: absolute; width: 500px; height: 120px; top: 350px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 20px;
                    color: #FFFFFF;">
            	<a class="btn btn-info btn-lg" href="<?php echo makeuri('login.php')?>">
                	<i class="glyphicon glyphicon-play"></i>&nbsp;&nbsp;<?php echo $lang['index'][3]?>
                </a>
                &nbsp;
    			<a class="btn btn-primary btn-lg" href="<?php echo $learn_more?>">
                	<i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;<?php echo $lang['index'][4]?>
                </a>
            </div>
            
            <div u="caption" t="NO" t3="RTT|2" r3="137.5%" du3="4000" d3="500" style="position: absolute; top: 100px; left: 650px;">
            	 <img src="<?php echo site_url()?>/images/upload.png" style="position: absolute; top: 0px; left: 0px;" />  
            </div>
            
            <div u="caption" t="MCLIP|B" du="3000" d="1000" t2="NO" style="position: absolute; width: 500px; height: 200px; top: 50px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 30px;
                    color: #FFFFFF;">
					<?php echo $lang['index'][26]?>
            </div>
                 
            <div u="caption" t="RTT|2" r="-75%" du="1600" t2="NO" style="position: absolute; width: 470px; height: 220px; top: 120px; left: 650px;">
               <img src="<?php echo site_url()?>/images/schedule.png" style="position: absolute; top: 0px; left: 0px;" /> 
            </div>
        </div>
        
        <div>
            <img u="image" src="<?php echo site_url()?>/images/purple.jpg" />
           
            <div u="caption" t="MCLIP|L" t3="RTTL|BR" du3="1000" d3="2000" style="position: absolute; width: 500px; height: 250px; top: 50px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 30px;
                    color: #FFFFFF;"><?php echo $lang['index'][30]?>
            </div>
            <div u="caption" t="NO" t3="RTT|2" r3="137.5%" du3="3000" d3="500" style="position: absolute; top: 80px; left: 650px;">
            	 <img src="<?php echo site_url()?>/images/create-content.png" style="position: absolute; top: 0px; left: 0px;" />  
            </div>
            
            <div u="caption" t="T" t2="NO" style="position: absolute; width: 500px; height: 120px; top: 350px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 20px;
                    color: #FFFFFF;">
            	<a class="btn btn-info btn-lg" href="<?php echo makeuri('login.php')?>">
                	<i class="glyphicon glyphicon-play"></i>&nbsp;&nbsp;<?php echo $lang['index'][3]?>
                </a>
                &nbsp;
    			<a class="btn btn-primary btn-lg" href="<?php echo $learn_more?>">
                	<i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;<?php echo $lang['index'][4]?>
                </a>
            </div>
            
            <div u="caption" t="RTTL|BR" t2="NO" du="1600" d="2000" style="position: absolute; width: 500px; height: 120px; top: 50px; left: 30px; padding: 5px;
                text-align: left; line-height: 60px; text-transform: uppercase; font-size: 30px;
                    color: #FFFFFF;"><?php echo $lang['index'][32]?>
            </div>
            <div u="caption" t="RTT|2" r="-75%" du="1600" t2="NO" style="position: absolute; top: 80px; left: 720px;">
               <img src="<?php echo site_url()?>/images/meme.png" style="position: absolute; top: 0px; left: 0px;" /> 
            </div>
        </div>
        
    </div>
            
    <div u="navigator" class="jssorb21" style="bottom: 26px; right: 6px;">
        <div u="prototype"></div>
    </div>
    <!--#endregion Bullet Navigator Skin End -->
    
    <!-- Arrow Left -->
    <span u="arrowleft" class="jssora21l" style="top: 123px; left: 8px;">
    </span>
    <!-- Arrow Right -->
    <span u="arrowright" class="jssora21r" style="top: 123px; right: 8px;">
    </span>
</div>
<!-- Jssor Slider End -->

<div class="container body">