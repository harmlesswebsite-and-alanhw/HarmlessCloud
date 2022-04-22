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
?>
<p>(<a href="index.php?path=<?php echo htmlspecialchars(urlencode($folder)); ?>#explorer-item-<?php echo htmlspecialchars(md5("$file.file")); ?>">View in folder</a> | <a href="download.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>" download="download">download</a> | <a href="download.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>">raw</a> | <a href="edit.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>">edit as plain text</a>)</p>
<?php 
function startsWith($haystack, $needle) {
     $length = strlen($needle);
     return substr($haystack, 0, $length) === $needle;
}
if (startsWith($type, 'image/')) {
    ?>
    <img src="download.php?path=<?php echo htmlspecialchars(urlencode("$currentpath")); ?>" alt="Image" /><?php
    exit(0);
}
function endsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}
if (endsWith($file, ".html")) {
    ?><a class="button" href="doc.php?path=<?php echo htmlspecialchars(urlencode("$currentpath")); ?>">Open in Harmless Documents</a>
    <div style="border: 1px solid; padding: 7px;"><?php echo file_get_contents("files/$name$currentpath.file"); ?></div>
    <?php
    exit(0);
}
if (startsWith($type, "text/")) {
    if (endsWith($file, ".csv")) {
        ?>
<div style="max-width: 100%; max-height: 75vh; overflow: scroll;">        
<table style="max-width: 100%;">
            <caption>CSV file contents</caption><?php
        $csvFile = explode("\n", file_get_contents("files/$name$currentpath.file"));
        $data = array();
        $lengths = array();
        foreach ($csvFile as $line) {
            $data[] = str_getcsv($line);
            array_push($lengths, count(str_getcsv($line)));
        }
        $maxcol = max($lengths);
        ?><tr>
        <th>R</th><?php
        for ($i = 0; $i < $maxcol; $i++) {
            ?><th><?php echo $i + 1; ?></th><?php
        }
        ?></tr><?php
        foreach ($data as $row => $string) {
            ?><tr style="border: 1px solid;">
            <th><?php echo $row + 1; ?></th>
            <?php
            foreach ($string as $item) {
                ?><td style="border: 1px solid; padding: 3px;"><?php echo htmlspecialchars($item); ?></td><?php
            }
            ?></tr><?php
        }
        ?></table></div><?php
        exit(0);
    }
    ?><pre><code><?php echo htmlspecialchars(file_get_contents("files/$name$currentpath.file")); ?></code></pre><?php
    exit(0);
}
if (startsWith($type, "audio/")) {
    ?>
    <audio style="width: 100%;" controls="controls" src="download.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>"></audio>
    <?php
    exit(0);
}
    if (startsWith($type, "video/")) {
        ?><video controls="controls" style="width: 100%;" src="download.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>"></video><?php
        exit(0);
    }
?>Sorry, no peeview available.<?php