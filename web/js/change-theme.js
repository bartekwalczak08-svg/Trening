(function () {
    var toggleBtn = document.getElementById('theme-toggle');
    var toggleLabel = document.getElementById('theme-toggle-label');

    function syncLabel() {
        var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        if (toggleLabel) {
            toggleLabel.textContent = current === 'dark' ? 'Jasny' : 'Ciemny';
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
