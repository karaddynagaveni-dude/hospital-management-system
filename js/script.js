/* ==========================================================
   Hospital Management System — shared front-end behaviour
   ========================================================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ---- Mobile sidebar toggle (admin & user dashboards) ---- */
    var toggleBtn = document.querySelector('.mobile-toggle');
    var sidebar = document.querySelector('.sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 980 && sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) && e.target !== toggleBtn && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    /* ---- Confirm before destructive delete actions ---- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    /* ---- Auto-dismiss alert banners after a few seconds ---- */
    document.querySelectorAll('.alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

    /* ---- Simple client-side required-field check as a first line
           of validation (server still validates everything again) ---- */
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('[required]').forEach(function (input) {
                if (!input.value || !input.value.trim()) {
                    valid = false;
                    input.style.borderColor = '#FF6B5C';
                } else {
                    input.style.borderColor = '';
                }
            });

            var pass = form.querySelector('input[name="password"]');
            var confirmPass = form.querySelector('input[name="confirm_password"]');
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                valid = false;
                confirmPass.style.borderColor = '#FF6B5C';
                alert('Passwords do not match.');
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    /* ---- Minimum date = today on appointment date pickers ---- */
    document.querySelectorAll('input[type="date"].no-past').forEach(function (input) {
        var today = new Date().toISOString().split('T')[0];
        input.setAttribute('min', today);
    });
});
