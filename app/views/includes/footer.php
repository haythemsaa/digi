    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner-border text-light" role="status" style="width: 4rem; height: 4rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- PWA Install Prompt -->
    <div id="pwaInstallPrompt" class="alert alert-info position-fixed bottom-0 start-50 translate-middle-x m-3" style="display: none; z-index: 9999;">
        <div class="d-flex align-items-center">
            <i class="fas fa-mobile-alt fa-2x me-3"></i>
            <div class="flex-grow-1">
                <strong>Install DigiParc App</strong>
                <p class="mb-0 small">Install our app for a better experience!</p>
            </div>
            <button id="pwaInstallBtn" class="btn btn-primary btn-sm me-2">Install</button>
            <button id="pwaDismissBtn" class="btn btn-secondary btn-sm">Dismiss</button>
        </div>
    </div>

    <!-- Register Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo APP_URL; ?>/public/service-worker.js')
                .then(registration => {
                    console.log('ServiceWorker registered:', registration);
                })
                .catch(err => {
                    console.log('ServiceWorker registration failed:', err);
                });
        });
    }

    // PWA Install Prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        document.getElementById('pwaInstallPrompt').style.display = 'block';
    });

    document.getElementById('pwaInstallBtn')?.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to install prompt: ${outcome}`);
            deferredPrompt = null;
            document.getElementById('pwaInstallPrompt').style.display = 'none';
        }
    });

    document.getElementById('pwaDismissBtn')?.addEventListener('click', () => {
        document.getElementById('pwaInstallPrompt').style.display = 'none';
    });
    </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script>
        // Show loading spinner
        function showLoading() {
            document.getElementById('loadingSpinner').classList.add('active');
        }

        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loadingSpinner').classList.remove('active');
        }

        // Initialize DataTables
        $(document).ready(function() {
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                    },
                    "pageLength": <?php echo ITEMS_PER_PAGE; ?>,
                    "responsive": true
                });
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            $('.alert:not(.alert-permanent)').fadeOut('slow');
        }, 5000);

        // Confirm delete
        function confirmDelete(message) {
            return Swal.fire({
                title: 'Êtes-vous sûr?',
                text: message || "Cette action est irréversible!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer!',
                cancelButtonText: 'Annuler'
            });
        }

        // Success message
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Succès!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Error message
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur!',
                text: message
            });
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    </script>

    <?php if (isset($data['extra_js'])): ?>
        <?php echo $data['extra_js']; ?>
    <?php endif; ?>

</body>
</html>
