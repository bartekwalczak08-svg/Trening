(function () {
    var toggleBtn = document.getElementById('theme-toggle');
    var toggleLabel = document.getElementById('theme-toggle-label');
    var lightLabel = toggleBtn ? (toggleBtn.getAttribute('data-light-label') || 'Jasny') : 'Jasny';
    var darkLabel = toggleBtn ? (toggleBtn.getAttribute('data-dark-label') || 'Ciemny') : 'Ciemny';

    function syncLabel() {
        var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        if (toggleLabel) {
            toggleLabel.textContent = current === 'dark' ? lightLabel : darkLabel;
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try {
                localStorage.setItem('theme', next);
            } catch (e) {
                // ignore localStorage errors
            }
            syncLabel();
        });
    }

    syncLabel();
})();
