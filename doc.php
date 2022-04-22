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
<form hidden="hidden" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
    <label>InnerHTML:<textarea id="editor-value" name="contents"></textarea></label>
    <input type="submit" />
</form>
<?php
if (isset($_POST['contents'])) {
    $banner = 'Saving file...';
    fwrite(fopen("files/$name$currentpath.file", "w+"), $_POST['contents']);
    $banner .= "<br />Saved";
}
    ?>
        <div class="banner<?php if (empty($banner)) { ?>" style="display: none;"<?php } else { ?>"<?php } ?>><?php echo $banner; ?></div>
<div>
<div style="position: sticky; top: 0;"><div id="editor-tools" style="border-radius: 3px; background-color: var(--toolbar-selected-bg); padding: 7px;">
    <h1 style="font-size: 20px; display: inline;"><?php echo $fileExists ? htmlspecialchars($currentpath) : exit("File not found</h1><p>You have supplied an invalid filename.</p>"); ?>
    </h1>
<div>
<label>
<span class="hidden2eyes">Menu to show:</span>
<select onchange="for (var i = 0; i < document.querySelectorAll('.toolbarmenu').length; i++) { document.querySelectorAll('.toolbarmenu')[i].style.display = 'none'; } document.getElementById(this.options[this.selectedIndex].value).style.display = 'block';">
<option value="formatting">Formatting</option>
<option value="links">Links</option></select>
</label>
</div>
    <button onclick="handleSave()" class="toolbar-button toolbar-selected"><b>Save</b></button>
    <button onclick="ifrm.document.execCommand('undo');" class="toolbar-button toolbar-selected">Undo</button>
    <button onclick="ifrm.document.execCommand('redo');" class="toolbar-button toolbar-selected">Redo</button>
    <button class="toolbar-button toolbar-selected" onclick="ifrm.print();">Print</button><br />
<div id="formatting" class="toolbarmenu">
    <button id="bold-button" class="toolbar-button toolbar-selected" onclick="ifrm.document.execCommand('bold');"><b>Bold</b></button>
    <button id="italic-button" class="toolbar-button toolbar-selected" onclick="ifrm.document.execCommand('italic');"><i>Italic</i></button>
    <button class="toolbar-button toolbar-selected" onclick="ifrm.document.execCommand('strikeThrough');" id="strike-button"><strike>Strike</strike></button>
    <label>
        <span class="hidden2eyes">More styles</span>
        <select onchange="if (this.selectedIndex > 1) surroundSelection(this.options[this.selectedIndex].value); else ifrm.document.execCommand('removeFormat'); this.selectedIndex = 0;">
            <option selected="selected" disabled="disabled">More options</option>
            <option>Use default formatting   </option>
            <option value="h1">Heading 1</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
            <option value="h4">Heading 4</option>
            <option value="h5">Heading 5</option>
            <option value="h6">Heading 6</option>
            <option value="big">Larger text</option>
             <option value="small">Smaller text</option>
        </select>
    </label>
F: <code id="foreground-color" tabindex="0" onclick="document.getElementById('color-picker').hidden = ''; document.querySelector('[name=thing2style]').value = 'f'; document.getElementById('color').focus();"">#??????</code>
    B: <code id="background-color" tabindex="0" onclick="document.getElementById('color-picker').hidden = ''; document.querySelector('[name=thing2style]').value = 'b'; document.getElementById('color').focus();">#??????</code><br />
    <label>
        <span class="hidden2eyes">Font:</span>
        <input id="fontFamily" size="8" placeholder="Font name" />
    </label>
    <input type="button" onclick="applyFont(document.getElementById('fontFamily').value);" value="Apply font" class="toolbar-button toolbar-selected" style="border: none;" />
    <label>
        <span class="hidden2eyes">Font size (px):</span>
        <input id="fontSize" type="number" placeholder="Font size (px)" size="2" />
    </label>
    <input type="button" onclick="applyFontSize(document.getElementById('fontSize').value);" value="Apply size" class="toolbar-button toolbar-selected" style="border: none;" />
    <form style="display: inline;" action="javascript:;" onsubmit="applyLineHeight(document.getElementById('newLineHeight').value);">
        <label>Line height:
        <input id="newLineHeight" onkeydown="event.stopPropagation()" onkeypress="event.stopPropagation()" onkeyup="event.stopPropagation()" />
        </label>
        <input type="submit" class="toolbar-button toolbar-selected" value="Apply line height" onmousedown="event.stopPropagation()" onmouseup="event.stopPropagation()" />
    </form>
    <button onclick="openAdvancedInsertMenu()" class="toolbar-button toolbar-selected">Advanced insert menu</button>
</div>
    <div id="links" class="toolbarmenu">
        <form style="display: inline;" onsubmit="link(document.getElementById('link-target').value, document.getElementById('link-title').value, ifrm)" action="javascript:;">
            <label>
                <span class="hidden2eyes">Target:</span>
                <input id="link-target" placeholder="Target URL" />
            </label>
            <label>
                <span class="hidden2eyes">Title:</span>
                <input id="link-title" placeholder="Hover text" />
            </label>  
            <input type="submit" value="Make link" class="toolbar-button toolbar-selected" />
        </form>
        <button onclick="ifrm.document.execCommand('unlink');" class="toolbar-button toolbar-selected">Remove link</button>

        &nbsp;
        &nbsp;
    </div>
<div id="insert"></div>
    <style>
#links, #insert { display: none; }</style>
</div></div>

<div style="padding: 7px; border: 1px solid;">
<iframe id="editor" style="border: none; width: 100%; outline: none; display: none; overflow: hidden;" srcdoc="&lt;!DOCTYPE html&gt;&lt;html lang=&quot;en&quot;&gt;&lt;head&gt;&lt;link rel=&quot;stylesheet&quot; href=&quot;themes/<?php 
$skins = json_decode(file_get_contents(__DIR__ . '/skins.json'));
echo $skins->{$_SESSION['login'] ?? ""} ?? "main.css";
?>&quot; /&gt; &lt;link rel=&quot;stylesheet&quot; href=&quot;style.css&quot; /&gt; &lt;style&gt;html, body { margin: 0; padding: 0; } &lt;/style&gt;&lt;title&gt;Contenteditable editor&lt;/title&gt;&lt;meta name=&quot;viewport&quot; content=&quot;width=device-width,initial-scale=1.0&quot; /&gt;&lt;/head&gt;&lt;body&gt;&lt;div contenteditable=&quot;true&quot; style=&quot;outline: none;&quot; id=&quot;editor&quot;&gt;<?php echo htmlspecialchars(file_get_contents("files/$name$currentpath.file")); ?>&lt;/div&gt;&lt;/body&gt;&lt;/html&gt;"></iframe></div>
</div>
<div id="insertDialog" class="dialog" hidden="hidden">
    <form action="javascript:;" method="GET" onsubmit="handleInsert()">
    <select size="5" id="insertItem" style="width: 100%; padding: 0;">
        <option>Insert @ sign</option>
        <option>Insert file link</option>
        <option>Insert time</option>
        <option>Insert obscured e-mail address</option>
        <option>Close</option>
    </select>
        <input type="submit" value="Go" style="width: 100%;" />
    </form>
</div>
<div id="fileInsert" class="dialog" hidden="hidden">
    <form onsubmit="handleFileLink()" action="javascript:;">
        <label>Type the path of a file and hit INSERT:<br />
        <input id="filename" />
        </label>
        <br />
        <input type="submit" value="Insert" />
        <input type="button" value="Cancel" onclick="document.getElementById('fileInsert').hidden = 'hidden'; ifrm.document.querySelector('[contenteditable]').focus();" />
    </form>
</div>
<div id="color-picker" class="dialog" hidden="hidden">
    <form action="javascript:;" method="GET" onsubmit="applyColor()">
        <input type="hidden" name="thing2style" value="f" />
        <label for="color">Hex color (no hashtag): </label><br />
            <nobr>
                <label><span class="hidden2eyes">Or choose a color:</span>
            <input style="height: 32px; width: 32px; display: inline-block;" id="item-preview" type="color" onchange="document.getElementById('color').value = this.value;" />
                </label>
            <input oninput="document.getElementById('item-preview').value = '#' + this.value;" id="color" />
            </nobr></label>
        <br />
        <input type="submit" value="Apply" />
        <input type="button" onclick="document.getElementById('color-picker').hidden = 'hidden';" value="Cancel" />
    </form>
</div>
<div id="nospammail" class="dialog" hidden="hidden">
    <form action="javascript:;" onsubmit="insertMailAddress(document.getElementById('mailaddr').value); this.parentNode.hidden = 'hidden'; ifrm.document.querySelector('[contenteditable]').focus();">
        <label>Type an e-mail address to create an obscured form that will still work when clicked:<br />
        <input type="email" id="mailaddr" required="required" />
            <!-- @ becomes %40 -->
        </label><br />
        <input type="submit" value="Insert" />
    </form>
</div>
<script>
    var ifrm = document.getElementById('editor').contentWindow;
    ifrm.focus();
    ifrm.document.execCommand("defaultParagraphSeparator", false, "p");
    ifrm.document.execCommand("useCSS", false, false);
    function handleSave() {
        var inner = ifrm.document.getElementById('editor').innerHTML;
        document.getElementById('editor-value').value = inner;
        document.querySelector('input[type=submit]').click();
    }
    function applyColor() {
        ifrm.document.getElementById('editor').focus();
        if (document.querySelector('[name="thing2style"]').value == 'f') {
            ifrm.document.execCommand('foreColor', false, "#" + document.getElementById('color').value);
        }
        if (document.querySelector('[name="thing2style"]').value == 'b') {
            console.log(ifrm.document.execCommand('useCSS', true));
            console.log(ifrm.document.execCommand('styleWithCSS', true));
            ifrm.document.execCommand('hiliteColor', false, "#" + document.getElementById('color').value);
        }
        document.getElementById('item-preview').value = '';
            document.getElementById('color-picker').hidden = 'hidden';
    }
    ifrm.document.execCommand('styleWithCSS', false, true);
    function getComputedStyleProperty(el, propName, win) {
    if (win.getComputedStyle) {
        return win.getComputedStyle(el, null)[propName];
    } else if (el.currentStyle) {
        return el.currentStyle[propName];
    }
}

function rgbToHex(r, g, b) {
  return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
}
function getCondition(c, win = window) {
    var containerEl, sel;
    if (win.getSelection) {
        sel = win.getSelection();
        if (!sel) return;
        if (sel.rangeCount) {
            containerEl = sel.getRangeAt(0).commonAncestorContainer;
            // Make sure we have an element rather than a text node
            if (containerEl.nodeType == 3) {
                containerEl = containerEl.parentNode;
            }
        }
    } else if ( (sel = document.selection) && sel.type != "Control") {
        containerEl = sel.createRange().parentElement();
    }

    if (containerEl) {
        return getComputedStyleProperty(containerEl, c, win);  
    }
}
    ifrm.onkeypress = function(ev) {
        if (ev.shiftKey && ev.keyCode == 64) {
            console.log('@ inserted');
            openAdvancedInsertMenu(ev);
            ev.preventDefault();
        }
    }
    function openAdvancedInsertMenu() {
            document.getElementById('insertDialog').hidden = '';
            document.getElementById('insertItem').focus();
            document.getElementById('insertItem').selectedIndex = 0;
    }
    document.getElementById('insertItem').onkeydown = function(ev) {
    if (ev.keyCode === 13) {
        document.querySelector('#insertDialog form input').click();
        ev.preventDefault();
    }
    }
    function handleInsert() {
        var index = document.getElementById('insertItem').selectedIndex;
        document.getElementById('insertDialog').hidden = 'hidden';
        globalThis.noFocus = false;
        switch (index) {
            case 0:
                insert("@");
                break;
            case 1:
                document.getElementById('fileInsert').hidden = '';
                globalThis.noFocus = true;
                document.getElementById('filename').focus();
                break;
            case 2:
                insert(new Date().toLocaleString('en-US'));
                break;
            case 3:
                document.getElementById('nospammail').hidden = '';
                globalThis.nofocus = true;
                document.getElementById('mailaddr').focus();
break;
        }
        if (!globalThis.noFocus)
        ifrm.document.querySelector('[contenteditable]').focus();
    }
    function componentToHex(c) {
  var hex = c.toString(16);
  return hex.length == 1 ? "0" + hex : hex;
}
    function updateButtons() {
        document.getElementById('editor').style.height = (ifrm.getComputedStyle(ifrm.document.querySelector('html')).height);
        if (ifrm.document.queryCommandState('bold')) {
            document.getElementById('bold-button').classList.remove('toolbar-selected');
        } else {
            document.getElementById('bold-button').classList.add('toolbar-selected');
        }
        if (ifrm.document.queryCommandState('italic')) {
            document.getElementById('italic-button').classList.remove('toolbar-selected');
        } else {
            document.getElementById('italic-button').classList.add('toolbar-selected');
        }
        if (ifrm.document.queryCommandState('strikeThrough')) {
            document.getElementById('strike-button').classList.remove('toolbar-selected');
        } else {
            document.getElementById('strike-button').classList.add('toolbar-selected');
        }
        document.getElementById('fontFamily').value = getCondition('fontFamily', ifrm);
document.getElementById('fontSize').value = getCondition('fontSize', ifrm).slice(0, -2);
        document.getElementById('newLineHeight').value = getCondition('lineHeight', ifrm);
        var color = getCondition('color', ifrm).slice(4, -1).split(',');
        for (var i = 0; i < color.length; i++) color[i] = parseInt(color[i]);
        document.getElementById('foreground-color').textContent = rgbToHex(color[0], color[1], color[2]);
        if (!getCondition('backgroundColor', ifrm).startsWith('rgba')) {
                var bgcolor = getCondition('backgroundColor', ifrm).slice(4, -1).split(',');
        for (var i = 0; i < bgcolor.length; i++) bgcolor[i] = parseInt(bgcolor[i]);
        document.getElementById('background-color').textContent = rgbToHex(color[0], color[1], color[2]);
        } else {
            document.getElementById('background-color').textContent = "not set";
        }
    }
    ifrm.document.onkeyup = function() {
        updateButtons();
    }
    ifrm.document.onkeypress = function() {
        updateButtons();
    }
    ifrm.document.onkeydown = function() {
        updateButtons();
    }
    ifrm.document.onmouseup = function() {
        updateButtons();
    }
    function link(target, title, win) {
  if (win.getSelection().toString()) {
    var a = document.createElement('a');
    a.href = target;
    a.title = title;
    win.getSelection().getRangeAt(0).surroundContents(a);
  }
}
    function surroundSelection(tag, HTMLElement = null) {       
    let selection= ifrm.getSelection().getRangeAt(0);
    let selectedContent = selection.extractContents();
    var span = document.createElement(tag);
   if (HTMLElement) span = HTMLElement;
    span.appendChild(selectedContent);
    selection.insertNode(span);
}
    function applyLineHeight(height) {
        var h = document.createElement('span');
        h.style.lineHeight = height;
        surroundSelection('span', h);
    }
    function applyFont(font) {
        var el = document.createElement('span');
        el.style.fontFamily = font;
        surroundSelection("span", el);
    }
function applyFontSize(size) {
    var el = document.createElement('span');
    el.style.fontSize = size + "px";
    surroundSelection("span", el);
}
    function insert(html) {
        ifrm.document.execCommand('insertHTML', false, html);
    }
    function handleFileLink() {
        var filename = document.getElementById('filename').value;
        var link = document.createElement('a');
        link.href = 'file.php?path=' + encodeURIComponent(filename);
        link.textContent = filename;
        insert(link.outerHTML);
        document.getElementById('fileInsert').hidden = 'hidden';
        ifrm.document.querySelector('[contenteditable]').focus();
    }
    document.getElementById('editor').style.display = 'block';
    document.getElementById('editor-tools').style.display = 'block';
    updateButtons(false);
    var editor = {};
    editor.bold = function() {
        updateButtons();
        document.execCommand('bold');
    }
    editor.italic = function() {
        updateButtons();
        document.execCommand('italic');
    }
    editor.strike = function() {
        updateButtons();
        document.execCommand('strikeThrough');
    }
    function insertMailAddress(mail) {
        var urlmail = mail.replace(/@/g, "%40");
        var maila = document.createElement('a');
        maila.setAttribute('href', 'mailto:' + urlmail);
        maila.textContent = mail.replace(/@/g, ' [at] ');
        insert(maila.outerHTML);
    }
    document.getElementById('editor').style.height = (ifrm.getComputedStyle(ifrm.document.querySelector('html')).height);
</script>