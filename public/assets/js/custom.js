/* ------------------------------------------------------------------------------
 *
 *  # Custom JS code
 *
 *  Place here all your custom js. Make sure it's loaded after app.js
 *
 * ---------------------------------------------------------------------------- */
/* ------------------------------------------------------------------------------
 *
 *  # Dashboard page
 *
 * ---------------------------------------------------------------------------- */


// Setup module
// ------------------------------
function initFlashNotifications() {
    if (typeof PNotify === 'undefined') return;

    const el = document.getElementById('flash-messages');
    if (!el) return;

    const successList = JSON.parse(el.dataset.success || '[]');
    const errorList = JSON.parse(el.dataset.error || '[]');

    if (!successList.length && !errorList.length) return;

    const stackTopRight = {"dir1":"down","dir2":"left","push":"top","spacing1":10,"spacing2":10,"firstpos1":20,"firstpos2":20};

    successList.forEach(msg => new PNotify({
        title: 'Success',
        text: msg,
        addclass: 'bg-success border-success',
        stack: stackTopRight
    }));

    errorList.forEach(msg => new PNotify({
        title: 'Error',
        text: msg,
        addclass: 'bg-danger border-danger',
        stack: stackTopRight
    }));
}


// Initialize module
// ------------------------------
// When page is fully loaded
window.addEventListener('load', function() {
    setTimeout(function () {
        initFlashNotifications();
    }, 1000);
});
