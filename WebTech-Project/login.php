```php
<?php

require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    redirect_to_dashboard();
}

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = 'Please fill in all fields.';

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, email, password, role, status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $error = 'Database error. Please try again later.';
        } else {

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                // Check account status
                if ($user['status'] !== 'active') {

                    $error = 'Your account is inactive. Please contact the clinic.';

                } else {

                    /*
                     * First try normal bcrypt password verification.
                     * This is the correct method for hashed passwords.
                     */
                    $passwordValid = password_verify(
                        $password,
                        $user['password']
                    );

                    /*
                     * Backward compatibility:
                     * If old customer password is still stored as plain text,
                     * allow it once and immediately convert it to bcrypt.
                     */
                    if (!$passwordValid && $password === $user['password']) {

                        $newHash = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $updateStmt = $conn->prepare("
                            UPDATE users
                            SET password = ?
                            WHERE id = ?
                        ");

                        if ($updateStmt) {

                            $updateStmt->bind_param(
                                'si',
                                $newHash,
                                $user['id']
                            );

                            $updateStmt->execute();
                            $updateStmt->close();

                            $passwordValid = true;
                        }
                    }

                    if ($passwordValid) {

                        // Login successful
                        $_SESSION['user_id']   = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['email']     = $user['email'];
                        $_SESSION['role']      = $user['role'];

                        redirect_to_dashboard();

                    } else {

                        $error = 'Incorrect password.';
                    }
                }

            } else {

                $error = 'No account found with that email.';
            }

            $stmt->close();
        }
    }
}

require_once __DIR__ . '/includes/site_header.php';

?>

<div class="auth-wrap">

    <div class="auth-box">

        <h2>🦷 Welcome Back</h2>

        <p class="sub">
            Login to your Dental Palace account
        </p>

        <?php if ($error): ?>

            <div class="alert alert-error">
                <?php echo h($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="you@example.com"
                    required
                >

            </div>


            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="••••••••"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn"
                style="width:100%;"
            >
                Login
            </button>

        </form>


        <p class="foot-link">

            Don't have an account?

            <a href="<?php echo BASE_URL; ?>/register.php">
                Register as Customer
            </a>

        </p>


        <p
            class="foot-link"
            style="color:#8aa; font-size:.8rem;"
        >
            Demo Login:<br>

            Admin:
            admin@dentalpalace.com / 123456<br>

            Employee:
            employee@dentalpalace.com / 123456<br>

            Customer:
            customer@gmail.com / 12345

        </p>

    </div>

</div>


<?php

require_once __DIR__ . '/includes/site_footer.php';

?>
```
