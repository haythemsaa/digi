<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            max-width: 550px;
            width: 100%;
            padding: 20px;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .register-body {
            padding: 40px 30px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-register {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2 class="mb-1"><i class="fas fa-user-plus me-2"></i>Create Account</h2>
                <p class="mb-0">Join Pakiparc Fleet Management</p>
            </div>

            <div class="register-body">
                <form action="<?php echo APP_URL; ?>/register" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="first_name"
                                   class="form-control <?php echo (!empty($data['first_name_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo $data['first_name']; ?>"
                                   required>
                            <?php if (!empty($data['first_name_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['first_name_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="last_name"
                                   class="form-control <?php echo (!empty($data['last_name_err'])) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo $data['last_name']; ?>"
                                   required>
                            <?php if (!empty($data['last_name_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['last_name_err']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text"
                               name="username"
                               class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $data['username']; ?>"
                               required>
                        <?php if (!empty($data['username_err'])): ?>
                            <div class="invalid-feedback"><?php echo $data['username_err']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email"
                               name="email"
                               class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $data['email']; ?>"
                               required>
                        <?php if (!empty($data['email_err'])): ?>
                            <div class="invalid-feedback"><?php echo $data['email_err']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel"
                               name="phone"
                               class="form-control"
                               value="<?php echo $data['phone']; ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   name="password"
                                   class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>"
                                   required>
                            <?php if (!empty($data['password_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['password_err']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   name="confirm_password"
                                   class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>"
                                   required>
                            <?php if (!empty($data['confirm_password_err'])): ?>
                                <div class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-register w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>

                    <div class="text-center">
                        <a href="<?php echo APP_URL; ?>/login" class="text-decoration-none">
                            Already have an account? <strong>Login here</strong>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
