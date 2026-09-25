<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Staff Portal</title>
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css'; ?>">
    <style>
        :root {
            --navy: #102a43;
            --blue: #1f6feb;
            --teal: #12b5cb;
            --gold: #f6c453;
            --cream: #f7fbff;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            overflow: hidden;
            color: var(--navy);
            background:
                radial-gradient(circle at 12% 18%, rgba(18, 181, 203, .16), transparent 30%),
                radial-gradient(circle at 88% 82%, rgba(246, 196, 83, .2), transparent 32%),
                var(--cream);
        }

        body::before,
        body::after {
            position: fixed;
            z-index: -1;
            width: 22rem;
            height: 22rem;
            border-radius: 50%;
            content: "";
            pointer-events: none;
            animation: float-orb 12s ease-in-out infinite alternate;
        }

        body::before {
            top: -9rem;
            left: -8rem;
            background: rgba(31, 111, 235, .12);
        }

        body::after {
            right: -9rem;
            bottom: -8rem;
            background: rgba(18, 181, 203, .13);
            animation-delay: -4s;
        }

        .portal-shell {
            width: min(100% - 2rem, 32rem);
            animation: rise-in .7s ease-out both;
        }

        .portal-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .85);
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 1.5rem 4rem rgba(16, 42, 67, .15);
            backdrop-filter: blur(14px);
        }

        .portal-card::before {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: .35rem;
            background: linear-gradient(90deg, var(--blue), var(--teal), var(--gold));
            content: "";
        }

        .brand-mark {
            display: inline-grid;
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
            place-items: center;
            border-radius: 1rem;
            color: white;
            background: linear-gradient(135deg, var(--blue), var(--teal));
            box-shadow: 0 .7rem 1.4rem rgba(31, 111, 235, .25);
            font-size: 1.4rem;
        }

        .portal-title {
            margin-bottom: .4rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .portal-subtitle {
            margin-bottom: 1.75rem;
            color: #627d98;
        }

        .form-control {
            min-height: 3rem;
            border: 1px solid #d9e2ec;
            border-radius: .8rem;
            background: rgba(247, 251, 255, .8);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 .2rem rgba(31, 111, 235, .12);
            transform: translateY(-1px);
        }

        .portal-submit {
            min-height: 3.1rem;
            border: 0;
            border-radius: .8rem;
            background: linear-gradient(135deg, var(--blue), var(--teal));
            box-shadow: 0 .7rem 1.2rem rgba(31, 111, 235, .22);
            font-weight: 700;
            letter-spacing: .04em;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .portal-submit:hover {
            box-shadow: 0 1rem 1.5rem rgba(31, 111, 235, .28);
            transform: translateY(-2px);
        }

        .login-form.loading {
            opacity: .7;
            pointer-events: none;
        }

        @keyframes rise-in {
            from { opacity: 0; transform: translateY(1.5rem); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float-orb {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to { transform: translate3d(1rem, -1.5rem, 0) scale(1.08); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="portal-shell">
        <section class="portal-card p-4 p-md-5">
            <div class="text-center">
                <div class="brand-mark">✦</div>
                <h1 class="portal-title h2">Institute Staff Portal</h1>
                <p class="portal-subtitle">Sign in to manage classes, students, attendance, and payments.</p>
            </div>

            <form class="login-form" method="post" action="<?php echo base_url('/index.php/guest/login'); ?>">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">Username</label>
                    <input type="text" name="email" id="email" class="form-control" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="current-password" required>
                </div>
                <button type="submit" class="portal-submit btn btn-primary btn-block">Sign in to portal</button>
            </form>
        </section>
    </main>
    <script>
        (function () {
            var card = document.querySelector('.portal-card');
            var form = document.querySelector('.login-form');

            if (card && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.addEventListener('pointermove', function (event) {
                    var x = (event.clientX / window.innerWidth - .5) * 4;
                    var y = (event.clientY / window.innerHeight - .5) * -4;
                    card.style.transform = 'perspective(1200px) rotateY(' + x + 'deg) rotateX(' + y + 'deg)';
                });
                document.addEventListener('pointerleave', function () {
                    card.style.transform = '';
                });
            }

            if (form) {
                form.addEventListener('submit', function () {
                    form.classList.add('loading');
                    var button = form.querySelector('.portal-submit');
                    if (button) {
                        button.textContent = 'Signing in...';
                    }
                });
            }
        }());
    </script>
</body>
</html>
