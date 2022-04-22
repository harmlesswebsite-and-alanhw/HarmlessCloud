<?php
if (session_id() == '') {
  session_start();
}
require 'localconfig.php';
$banner = '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	  <script>
        /*    
        @licstart  The following is the entire license notice for the 
        JavaScript code in this page.

        Copyright (C) 2022 weeklyd3

        The JavaScript code in this page is free software: you can
        redistribute it and/or modify it under the terms of the GNU
        General Public License (GNU GPL) as published by the Free Software
        Foundation, either version 3 of the License, or (at your option)
        any later version.  The code is distributed WITHOUT ANY WARRANTY;
        without even the implied warranty of MERCHANTABILITY or FITNESS
        FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.

        As additional permission under GNU GPL version 3 section 7, you
        may distribute non-source (e.g., minimized or compacted) forms of
        that code without the copy of the GNU GPL normally required by
        section 4, provided you include this license notice and a URL
        through which recipients can access the Corresponding Source.   


        @licend  The above is the entire license notice
        for the JavaScript code in this page.
        */
        </script>
      <link rel="stylesheet" href="style.css" />
      <link rel="stylesheet" href="themes/<?php 
$skins = json_decode(file_get_contents(__DIR__ . '/skins.json'));
echo $skins->{$_SESSION['login'] ?? ""} ?? "main.css";
?>" />
    <title>Harmless Cloud</title>
  </head>
  <body>
          <header>
        <div id="header-inner">
            <div><a href="index.php" class="header-link">Harmless Cloud</a></div>
            <?php if (isset($_SESSION['login'])) { ?>
            <form action="index.php" method="GET" align="right">
                <label><span class="hidden2eyes">Search:</span>
                <input type="search" name="search" placeholder="Search your files" class="topsearch" id="searchfiles" value="<?php if (isset($_GET['search'])) { echo htmlspecialchars($_GET['search']); } ?>" />
                </label>
                <?php 
                if (isset($_GET['search'])) { ?> (<a href="index.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>">clear</a>)<?php }
                ?>
            </form>
            <?php } else {
                ?><div>(not logged in)</div><?php
            } ?>
            <div align="right">
                <a href="account.php<?php 
    ?>?returnto=<?php echo htmlspecialchars(urlencode($_SERVER['REQUEST_URI']));
?>">
                    <?php echo isset($_SESSION['login']) ? htmlspecialchars($_SESSION['login']) : "Access your cloud"; ?>
                </a>
            </div>
        </div>
    </header>
<?php
register_shutdown_function(function() {
    if (isset($GLOBALS['nofooter'])) return;
    ?>      <script src="load.js"></script>

        </body></html><?php
});
class user {
    public function __construct(string $username, string $password) {
        $this->username = cleanFilename($username);
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->time = time();
        mkdir("files/" . $this->username, 0777);
    }
}
function cleanFilename($stuff) {
	$illegal = array(" ","?","/","\\","*","|","<",">",'"', '[', ']');
	// legal characters
	$legal = array("-","_","_","_","_","_","_","_","_", "_", "_");
	$cleaned = str_replace($illegal,$legal,$stuff);
	return $cleaned;
}
function custom_substr_count(string $str, array $arr) {
	$i = 0;
    foreach ($arr as $a) {
        $i += substr_count(strtoupper($str), strtoupper($a));
    }
    return $i;
}
function recscan(string $name, string $dir = "") {
    $results = array();
    $scanned = array_diff(scandir("files/$name/$dir", SCANDIR_SORT_NONE), array('.', '..'));
    foreach ($scanned as $thing) {
        if (is_dir("files/$name/$dir/$thing")) {
            array_push($results, "$dir/$thing");
            $newfiles = recscan($name, "$dir/$thing");
            foreach ($newfiles as $file) {
                array_push($results, "$file");
            }
            continue;
        }
        array_push($results, "$dir/$thing");
    }
    return $results;
}