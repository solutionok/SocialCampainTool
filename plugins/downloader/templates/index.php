<?php
/**
 * @package Social Ninja
 * @version 1.0
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12 text-center">
    	<h3><?php echo $lang['dw']['title']?></h3>
        <h4><?php echo $lang['dw']['supp_hosts']?></h4>
    </div>
</div>
<br/>
<div class="row">
	<div class="col-lg-12">
    	<label><?php echo $lang['dw']['inst']?></label>
        <input class="form-control d_url" type="text"/>
        <br/>
        <button class="btn btn-primary sdownload"><i class="glyphicon glyphicon-download"></i>&nbsp;&nbsp;<?php echo $lang['js']['dwn']?></button><br/><br/>
        <small>*<?php echo $lang['dw']['donot']?></small>
    </div>
</div>

<div class="row d_data" style="display:none">	
    <div class="col-lg-6 d_info">
    </div>
    <div class="col-lg-6 d_links">
    </div>
</div>

<input type="hidden" id="d_meta" />
<input type="hidden" id="d_file" />