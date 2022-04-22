<?php
require 'harmlesslib.php';
$path = $_GET['path'] ?? "/";
$path = explode("/", $path);
foreach ($path as &$item) $item = cleanFilename($item);
$path = "/" . implode("/", array_filter($path, function($v) { return $v !== "" && $v !== ".."; }));
$currentpath = $path;
$name = $_SESSION['login'];
$fileExists = file_exists("files/$name$currentpath.file");
?>
<h1><?php echo $fileExists ? htmlspecialchars($currentpath) : exit("File not found</h1><p>You have supplied an invalid filename.</p>"); ?></h1>
<?php 
    rename("files/$name$currentpath.file", "files/$name$currentpath");
$type = mime_content_type("files/$name$currentpath");
$filepath = explode("/", "$currentpath");
$file = explode("/", "$currentpath")[count(explode("/", "$currentpath")) - 1];
array_pop($filepath);
$folder = implode("/", $filepath);
rename("files/$name$currentpath", "files/$name$currentpath.file");
if (isset($_POST['saveedits'])) {
    fwrite(fopen("files/$name$currentpath.file", "w+"), $_POST['file-value']);
    ?><p>Changes saved. Click below to return to the file manager:</p><?php
}
?>
(<a href="index.php?path=<?php echo htmlspecialchars(urlencode($folder)); ?>#explorer-item-<?php echo htmlspecialchars(md5("$file.file")); ?>">Return to file (caution, discards all changes!)</a>)<br />
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/ace.min.js" integrity="sha512-hDyKEpCc9jPn3u2VffFjScCtNqZI+BAbThAhhDYqqqZbxMqmTSNIgdU0OU9BRD/8wFxHIWLAo561hh9fW7j6sA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/ext-modelist.min.js" integrity="sha512-u2GSB9nzcw3Rr/sezCxQg1dalIW+k/zEyeQA9z5KgZXnFfU+T9Da9dVFvlm1NQwzoyVByMpJjP1Cl70f5a/mnA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<label>Plain text file:<br />
<textarea id="fallback-textarea" name="file-value"></textarea>
</label><br />
    <input type="submit" onclick="submitAceValue()" value="Save" name="saveedits" />
</form>
<div style="display: none;" id="ace-editor"><?php echo htmlspecialchars(file_get_contents("files/$name$currentpath.file")); ?></div>
<script>
    ace.config.set('basePath', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.14/');
    function getModeByFileExtension(path) {
    var modelist = ace.require("ace/ext/modelist");
        console.log(modelist);
    return modelist.getModeForPath(path).mode;
}
    document.getElementById('ace-editor').style.display = 'block';
    document.getElementById('fallback-textarea').style.display = 'none';
    var editor = ace.edit("ace-editor");
    editor.getSession().setMode(getModeByFileExtension(JSON.parse(<?php echo json_encode(json_encode($file)); ?>)));
    function submitAceValue() {
        document.getElementById('fallback-textarea').value = editor.getSession().getValue();
    }
</script>
<style>
        #ace-editor { 
        width: 100%;
            height: 300px;
            font-size: 16px;
            font-family: Consolas, monospace;
    }
</style>
<?php