<?php 
require 'harmlesslib.php';
if (!isset($_SESSION['login'])) {
    ?>
    <h1>Cloud storage for developers of harmlesswebsite</h1>
<p>Harmlesscloud is currently in heavy development, and should not be trusted with sensitive data.</p>
<p><a href="account.php#createaccount">Create an account</a></p>
<p>(Have an account?)</p>
<a href="account.php">Log in</a>
    <h2>Features of harmlesscloud:</h2>
<figure style="float: right;">
    <img width="200" src="images/dark-theme-screenshot.png" alt="Harmlesscloud with dark theme screenshot" />
    <figcaption>Harmlesscloud in dark theme</figcaption>
</figure>
<ul>
    <li><strong>Fast page load</strong> - Pages on the site are quick to load.</li>
    <li><strong>Browser compatibility</strong> - Most features, like file uploads, editing, deletion, and creation are available without JavaScript.</li>
</ul>
    <?php
    exit(0);
}
$path = $_GET['path'] ?? "/";
$path = explode("/", $path);
foreach ($path as &$item) $item = cleanFilename($item);
$path = "/" . implode("/", array_filter($path, function($v) { return $v !== "" && $v !== ".."; }));
$currentpath = $path;
$name = $_SESSION['login'];
function recurseRmdir($dir) {
  $files = array_diff(scandir($dir), array('.','..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file") && !is_link("$dir/$file")) ? recurseRmdir("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
}
if (isset($_POST['newfile'])) {
    $newfile = cleanFilename($_POST['newfile']);
    $banner = "Creating file...";
    if (!file_exists("files/$name$currentpath/$newfile.file")) {
        fwrite(fopen("files/$name$currentpath/$newfile.file", "w+"), "");
        $banner .= "<br />Created!";
    } else {
        $banner .= "<br />Faliure: File already exists";
    }
}
    if (isset($_POST['delete'])) {
        $successDel = 0; 
        $faliureDel = 0;
        $banner = 'Deleting selected items...';
        if (count($_POST) == 1) {
            $banner .= '<br />Please select some items.';
        } else {
            $count = 0;
        foreach ($_POST as $key => $v) {
            $count++;
            if ($key === "delete" || $key == 'from' || $key == 'to' || $key == 'rename') {
                continue;
            }
            if (is_dir("files/$name$currentpath/$v")) {
                if (recurseRmdir("files/$name$currentpath/$v")) $successDel++;
                else $faliureDel++;
            } else {
                if (unlink("files/$name$currentpath/$v")) $successDel++;
                else $faliureDel++;
            }
        }
            if ($count > 0)
        $banner .= "<br />Successful: " . $successDel . ", faliure: " . $faliureDel . ", percentage of success: " . (100 * $successDel / ($faliureDel + $successDel)) . "%";
            else $banner .= '<br />Please click the submit button instead of pressing enter.';
    }
    }
    if (isset($_POST['createfolder'])) {
        $folder = $_POST['foldername'];
        $banner = 'Attempting to create folder ' . htmlspecialchars($folder);
        $folder = cleanFilename($folder);
        $banner .= "<br />The name will be " . htmlspecialchars($folder) . ' to prevent traversal attacks.';
        if (is_dir("files/$name$currentpath/$folder")) {
            $banner .= '<br />Faliure: Directory already exists.';
        } else {
            if (mkdir("files/$name$currentpath/$folder", 0777)) {
                $banner .= '<br />Directory created!';
            } else {
                $banner .= "<br />Sorry, this is our fault. We have a faliure here.";
            }
        }
    }
    if (isset($_FILES['files'])) {
        $banner = "Uploading " . count($_FILES['files']['name']) . " file" . (count($_FILES['files']['name']) > 1 ? "s" : "") . ', please wait...';
        $success = 0;
        $faliure = 0;
        foreach ($_FILES['files']['tmp_name'] as $key => $file) {
            if (move_uploaded_file($_FILES['files']['tmp_name'][$key], "files/" . cleanFilename($_SESSION['login']) . '/' . $currentpath . "/" .  cleanFilename($_FILES['files']['name'][$key]) . ".file")) $success++;
            else $faliure++;
        }
        $banner .= "<br />" . $success . ' upload(s) were successful and ' . $faliure . ' upload(s) resulted in faliure, for a success rate of ' . (100 * $success / ($success + $faliure)) . ' percent';
    }
?>
    <div class="banner<?php if (empty($banner)) { ?>" style="display: none;"<?php } else { ?>"<?php } ?>><?php echo $banner; ?></div>
    <table>
        <tr>
            <td valign="top">
            <details>
                <summary class="button">Upload files</summary>
                <div style="padding: 3px; position: absolute; background-color: white; color: black; border: 1px solid;"><h2>Upload files</h2>
                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" enctype="multipart/form-data">
                    <label>Select some files:
                        <input type="file" multiple="multiple" name="files[]" /></label>
                    <input type="submit" value="Let's Go!" />
                </form>
                </div>
            </details>
                <details>
                    <summary class="button">Create...</summary>
                    <div style="padding: 3px; position: absolute; background-color: white; color: black; border: 1px solid;">
                        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                            <fieldset>
                                <legend>Create folder</legend>
                            <label>Folder name: <input name="foldername" /></label>
                                <input type="submit" name="createfolder" value="Create folder" />
                            </fieldset>
                        </form>
                        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                            <fieldset>
                                <legend>Create new file</legend>
                                <label>Filename: <input name="newfile" /></label>
                                <input type="submit" value="Create file" />
                        </form>
                    </div>
                </details>
            </td>
            <td valign="top">
                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
<div>Multi-file actions:
    <?php
if (!isset($_GET['search'])) { ?>
    <details style="display: inline;">
        <summary class="button">Delete selected items</summary>
<div style="padding: 3px; position: absolute; background-color: white; color: black; border: 1px solid;">
    <p>Are you sure you want to permanently delete all of the selected items?</p>
<p>Note: If you select folders, all of their contents will be deleted.</p>
    <input type="submit" value="Confirm deletion" name="delete" />
</div>
    </details>
                              <?php } else { ?> 
    No actions available while searching.
    Open a file to perform an action to it.<?php } ?> 
</div>
    <?php
if (!is_dir("files/$name$currentpath")) {
    ?>AARRRG. that seems to be an invalid directory name.<?php
    exit(0);
}
?><h1><?php echo isset($_GET['search']) ? "Search results" : htmlspecialchars($currentpath); ?></h1><?php

    $myfiles = scandir("files/$name" . $currentpath, SCANDIR_SORT_NONE);
usort($myfiles, function($a, $b) {
    $name = $_SESSION['login'];
    return filemtime("files/$name$currentpath/$b") - filemtime("files/$name$currentpath/$a");
});
    if (!isset($_GET['search']) && $currentpath !== "/") {
        $pathitems = explode("/", $currentpath);
        $pathitems = array_slice($pathitems, 0, count($pathitems) - 1);
        $up = implode("/", $pathitems);
        ?>(<a href="index.php?path=<?php echo htmlspecialchars(urlencode($up)); ?>">parent directory</a>)<?php
    }
if (isset($_GET['search'])) {
    $queries = explode(" ", strtolower($_GET['search']));
    if ($_GET['search'] === "") die("Please specify a search query.</td></tr></table>");
    $myfiles = recscan($name);
    $results = array();
    foreach ($myfiles as $file) {
        if (custom_substr_count($file, $queries) !== 0) array_push($results, $file);
    }
    $GLOBALS['queries'] = $queries;
    usort($results, function($a, $b) {
        return custom_substr_count($b, $GLOBALS['queries']) - custom_substr_count($a, $GLOBALS['queries']);
    });
    $myfiles = $results;
}
   ?><table width="100%">
        <tr>
    <?php if (!isset($_GET['search'])) { ?>
            <th width="0">
                <span hidden="hidden" id="select-all">
                    <label><span class="hidden2eyes">Select all: </span>
                    <input id="selectall" type="checkbox" /></label>
                </span>
                <span id="selectallnojs">Select</span></th>
                                        <?php } ?>
            <th width="0">Type</th>
            <th>Name</th>
            <th width="0">Permissions</th>
            <th width="0">Lastmod</th>
        </tr><?php
if (count($myfiles) == 2 && !isset($_GET['search'])) {
    ?><tr>
    <td colspan="6">
    <p>There are no files or folders in this directory.</p>
        <p>This list may not reflect recent changes (clear your cache to update).</p>
    </td></tr><?php
}
foreach ($myfiles as $key => $file) {
    if ($file == "." || $file == "..") continue;
        if (isset($_GET['search'])) $currentpath = '';
   ?><tr id="explorer-item-<?php echo htmlspecialchars(md5($file)); ?>">
    <?php if (!isset($_GET['search'])) { ?>
    <td><label><input name="select-file-<?php echo $key; ?>" type="checkbox" value="<?php echo htmlspecialchars($file); ?>" /><span class="hidden2eyes">Select this item</span></label></td><?php } ?>
       <td><?php 
    $dir = is_dir("files/$name$currentpath/$file");
    if (is_dir("files/$name$currentpath/$file")) { ?><abbr title="Directory">D</abbr><?php }  else { ?><abbr title="File">F</abbr> <?php } ?></td>
        <td><?php if ($dir) { ?><a href="index.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>/<?php echo htmlspecialchars(urlencode($file)); ?>"><?php echo htmlspecialchars($file); ?></a><?php } else {
        ?><a href="file.php?path=<?php echo htmlspecialchars(urlencode($currentpath)); ?>/<?php echo htmlspecialchars(urlencode(substr($file, 0, -5))); ?>"><?php echo htmlspecialchars(substr($file, 0, -5)); ?></a><?php
        }
        
        ?></td>
       <td><?php echo substr(sprintf('%o', fileperms("files/$name$currentpath/$file")), -4); ?></td>
    <td><?php echo date('M d Y @ H:i', filemtime("files/$name$currentpath/$file")); ?></td>
   </tr><?php
}
?>
   </table>
</form></td></tr></table>