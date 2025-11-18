<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .offline-container {
            max-width: 600px;
            text-align: center;
            padding: 40px;
        }

        .offline-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .offline-icon {
            font-size: 8rem;
            color: #667eea;
            margin-bottom: 30px;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .btn-retry {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .btn-retry:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-card">
            <div class="offline-icon pulse">
                <i class="fas fa-wifi-slash"></i>
            </div>
            <h1 class="mb-3">You're Offline</h1>
            <p class="text-muted mb-4">
                It looks like you've lost your internet connection.
                Some features may not be available until you're back online.
            </p>
            <p class="mb-4">
                <i class="fas fa-info-circle me-2"></i>
                Don't worry, your data is safe and will sync automatically when you reconnect.
            </p>
            <button class="btn btn-retry" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Try Again
            </button>

            <hr class="my-4">

            <div class="text-start">
                <h6><i class="fas fa-check-circle text-success me-2"></i>What you can do offline:</h6>
                <ul class="text-muted">
                    <li>View cached pages</li>
                    <li>Access previously loaded data</li>
                    <li>Record GPS positions (will sync later)</li>
                    <li>Browse your recent activity</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Auto-retry connection
        let retryCount = 0;
        const maxRetries = 5;

        function checkConnection() {
            if (navigator.onLine) {
                location.reload();
            } else if (retryCount < maxRetries) {
                retryCount++;
                setTimeout(checkConnection, 5000);
            }
        }

        window.addEventListener('online', () => {
            location.reload();
        });

        // Start checking connection
        setTimeout(checkConnection, 5000);
    </script>
</body>
</html>
