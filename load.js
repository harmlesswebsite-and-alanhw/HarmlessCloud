// @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later
if (document.getElementById('searchfiles')) {
document.getElementById('searchfiles').size = 12;
    document.getElementById('searchfiles').addEventListener('focus', function() {
        this.removeAttribute('size');
    });
    document.getElementById('searchfiles').addEventListener('blur', function() {
        this.size = 12;
    });
}
if (document.querySelector('#selectall')) {
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="select-file-"]');
    var selectAll = document.querySelector('#selectall');
    document.querySelector('#selectallnojs').hidden = 'hidden';
    document.querySelector('#select-all').style.display = 'block';
    selectAll.addEventListener('change', function() {
        if (this.checked) {
            // Select all boxes
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
          } else {
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
          }
        });
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].addEventListener('change', function() {
            if (determineIfAllIsChecked(checkboxes)) {
                document.querySelector('#selectall').checked = true;
                document.querySelector('#selectall').indeterminate = false;
                return;
            }
            if (determineIfAllIsNotChecked(checkboxes)) {
                document.querySelector('#selectall').checked = false;
                document.querySelector('#selectall').indeterminate = false;
                return;
            }
            document.querySelector('#selectall').checked = false;
            document.querySelector('#selectall').indeterminate = true;
        });
    }
}
function determineIfAllIsChecked(array_of_checkboxes) {
    for (let i = 0; i < array_of_checkboxes.length; i++) {
        if (!array_of_checkboxes[i].checked) {
            return false;
        }
    }
    return true;
}
function determineIfAllIsNotChecked(array_of_checkboxes) {
    for (let i = 0; i < array_of_checkboxes.length; i++) {
        if (array_of_checkboxes[i].checked) {
            return false;
        }
    }
    return true;
}
// @license-end