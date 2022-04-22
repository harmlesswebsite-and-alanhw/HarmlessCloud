<?php 
session_start();
if (isset($_POST['theme'])) {
    $themes = json_decode(file_get_contents('skins.json'));
    if (isset($_SESSION['login'])) $themes->{$_SESSION['login']} = $_POST['theme'];
    fwrite(fopen('skins.json', 'w+'), json_encode($themes));
}
require 'harmlesslib.php';
?>
<h1>Harmless Cloud Account Management</h1>
<?php
if (isset($_POST['logout'])) {
    session_destroy();
    ?><p>You have been logged out.</p>
    <p><a href="account.php">Continue using harmlesscloud</a></p><?php
    exit(0);
}
if (isset($_POST['login'])) {
    $user = cleanFilename($_POST['username']);
    $pass = $_POST['password'];
    $users = json_decode(file_get_contents('users.json'));
    if (!isset($users->$user)) {
        ?><p>I'm sorry, but <b><?php echo htmlspecialchars($user); ?></b> does not seem to be a username that is registered.</p>
        <p>If you want to create an account with the <b>exact same</b> crededentials, you can click below:</p>
<form method="post">
    <input type="hidden" name="username" value="<?php echo htmlspecialchars($user); ?>" />
    <input type="hidden" name="password" value="<?php echo htmlspecialchars($pass); ?>" />
    <input type="submit" name="signup" value="Create account" />
</form>
<p>Thanks for using harmlesscloud!</p>
        <?php
        exit(0);
    }
    $password = $users->$user->password;
    if (!password_verify($pass, $password)) {
        ?><p>Sorry, you have entered an invalid password.</p> 
<p>Thanks for your interest in harmlesscloud!</p><?php
        exit(0);
    }
        $_SESSION['login'] = $user;
    $_SESSION['eslogin'] = htmlspecialchars($user);
    ?><p>You have been logged in.</p><?php
}
if (isset($_POST['signup'])) {
    $user = cleanFilename($_POST['username']);
    $pass = $_POST['password'];

    if (strlen($pass) < $config['minpasslen']) {
        ?><p>Sorry, your password has to be at least <?php echo $config['minpasslen']; ?> characters long, and yours is <?php echo strlen($pass); ?> characters long.</p>
        <p>Thanks for using harmlesscloud.</p><?php
        exit(0);
    }
    $users = json_decode(file_get_contents('users.json'));
    if (isset($users->$user)) {
        ?><p>Sorry, your username is currently not available.</p>
        <a href="account.php?purge=<?php echo time(); ?>#createaccount">Try again?</a><p>Thank you for using harmlesscloud.</p><?php
        exit(0);
    }
    $userconfig = new user($user, $pass);
    $users->$user = $userconfig;
    fwrite(fopen('users.json', 'w+'), json_encode($users));
    ?><p>Your account has been created. You have been logged in.</p><?php
    $_SESSION['login'] = $user;
    $_SESSION['eslogin'] = htmlspecialchars($user);
}
if (!isset($_SESSION['login'])) {
?>
    <p>You are currently not logged in.</p>
<p>To log in, complete the form below.</p>
<p>To create an account, scroll down or <a href="#createaccount">jump</a>.</p>
<form id="login" method="post">
<label>Enter your username:<br />
&nbsp;&nbsp;<input name="username" /></label><br />  
    <label>Enter your password:<br />
&nbsp;&nbsp;<input name="password" type="password" /></label><br />  
    <input type="submit" name="login" value="Log in" />
</form>
    <p>If you do not have an account, you can create one:</p>
<form id="createaccount" method="post"><label>Enter your username:<br />
&nbsp;&nbsp;<input name="username" /></label><br />  
    <label>Enter your password:<br />
&nbsp;&nbsp;<input name="password" type="password" /></label><br />  
    <input type="submit" name="signup" value="Create your account" /></form>
    <?php
    exit(0);
}
?>
<p>You are currently logged in as <?php echo $_SESSION['eslogin']; ?>.</p>
<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
    <p>You can use the form below to change the appearence of the site.</p>
    <fieldset>
        <legend>Select a theme</legend>
        <?php 
$skins = json_decode(file_get_contents('skins.json'));
if (isset($skins->{$_SESSION['login']})) $currentSkin = $skins->{$_SESSION['login']};
else $currentSkin = 'main.css';
$themes = scandir('themes/');
foreach ($themes as $theme) {
    if ($theme === "." || $theme === "..") continue;
    ?><label><input <?php if ($currentSkin == $theme) { ?>checked="checked" <?php } ?>type="radio" name="theme" value="<?php echo htmlspecialchars($theme); ?>" /> <?php echo htmlspecialchars(pathinfo("themes/$theme", PATHINFO_FILENAME)); ?></label><br /><?php
}
?>
    </fieldset>
    <input type="submit" value="Save" />
</form>
<p><a href="<?php echo isset($_GET['returnto']) ?
htmlspecialchars($_GET['returnto']) : 'index.php'; ?>">Continue to where you left off: <?php echo htmlspecialchars($_GET['returnto'] ?? 'no place specified'); ?></a></p>
<p>You can log out by clicking below:</p>
<form method="post">
    <input name="logout" type="submit" value="Log out" />
</form>