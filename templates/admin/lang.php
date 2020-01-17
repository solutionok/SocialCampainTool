<?php
/**
 * @package Social Ninja
 * @version 1.3
 * @author InspiredDev <iamrock68@gmail.com>
 * @copyright 2015
 */
if(!defined('S_NINJA'))exit();
?>

<div class="row">
	<div class="col-lg-12">
    	<h3 class="text-center">Language Manager</h3>
    </div>
</div>
<br/><br/>
<?php
$lang_ll = '';
$lf = file_get_contents(__ROOT__.'/lang/en.php');
$l = preg_match_all('/"(.*)"/m', $lf, $m);
$m[1] = array_filter($m[1], 'strlen');
$lang_ll = implode("\n", $m[1]);

if(!empty($_POST['lang_name']) && !empty($_POST['lang_text'])){
	if(empty( $is_demo )){
		$ll = preg_split('/$\R?^/m', trim($_POST['lang_text']));
		$ll = array_filter($ll, 'strlen');
		$name = $_POST['lang_name'];
		if(preg_match('/[^a-z0-9_]/i', $name)){
			echo '<div class="alert alert-danger">Invalid file name. File name must include a-z 0-9 and _ no other characters allowed</div>';
		}
		else{
			if(count($m[1]) != count($ll)){
				echo '<div class="alert alert-danger">Line count mismatch</div>';	
			}
			else{
				foreach($m[1] as $index => $match){
					$lf = str_replace('"'.$match.'"', '"'.trim(purify_text($ll[$index])).'"', $lf);	
				}			
				file_put_contents(__ROOT__.'/lang/'.$name.'.php', $lf);
				echo '<div class="alert alert-success">Language file created successfully. Please test it before going live. If you see any error, disable the language file right away</div>';
			}	
		}
	}
	else {
		echo '<div class="alert alert-danger">Disabled in demo</div>';		
	}
}
?>
<div class="row">
	<div class="col-lg-6">
    	<h4>Copy language file</h4>
    	<textarea class="form-control" style="height:150px; width:500px"><?php echo $lang_ll?></textarea><br/><br/>
        <h4>Rules of translation</h4>
        <ul>
        	<li>Copy the language file above</li>
            <li>Save the language file into a text file</li>
            <li>Translate it line by line or by translator</li>
            <li>Upload the file into your translator for line by line upload</li>
            <li>Do not alter line breaks</li>
            <li>Paste the translated text into the text box you see into the right --></li>
            <li>Give your language a name</li>
            <li>Click save</li>
            <li>Any new language applies on user front end. Admin panel is always in English</li>
            <li>To edit language files login to ftp and edit php files for respective language</li>
            <li>Do not break lines while editing</li>
            <li><b>If you change any language file, make it default again to update the default language with new changes</b></li>
        </ul>
    </div>
    <div class="col-lg-6">
    	<h4>Import translated language file</h4>
        <form action="" method="post">
        	<label>Name of the language</label>
            <input type="text" name="lang_name" value="<?php echo @purify_text($_POST['lang_name'])?>" class="form-control">
            <label>Paste translated language file here</label>
            <textarea name="lang_text" class="form-control" style="height:150px"><?php echo @purify_text($_POST['lang_text'])?></textarea><br/>
            <button class="btn btn-default">Save</button>
        </form>
    </div>
</div>
<?php
$lfiles = list_lang_files();
?>
<br/><br/>
<div class="row">
	<div class="col-lg-6">
    	<h4>Listed language files</h4>
    	<table class="table">
        <tr><th width="50%">Name</th><th>Make Default</th><th>Delete</th></tr>
		<?php
		foreach($lfiles as $lf){
			echo '<tr rel="'.$lf.'"><td>'.$lf.'</td><td><button class="btn btn-info lang_default">Make default</button></td><td><button class="btn btn-danger lang_del">Delete</button></td></tr>';	
		}
		?>
        </table>
    </div>
</div>