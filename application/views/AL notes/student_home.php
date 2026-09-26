<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Browse Advanced Level ICT lesson notes from Unit 1 to Unit 13.">
    <title>AL Notes | Student Home</title>
    <style>
        :root {
            color-scheme: light;
            --navy: #102a43;
            --blue: #1f6feb;
            --teal: #12a8ad;
            --gold: #e6aa23;
            --paper: #f3f7fb;
            --ink: #243b53;
            --muted: #627d98;
            --line: #d9e2ec;
            --white: #fff;
        }

        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at 8% 5%, rgba(18, 168, 173, .12), transparent 25rem),
                radial-gradient(circle at 95% 25%, rgba(230, 170, 35, .12), transparent 24rem),
                var(--paper);
            font: 16px/1.55 Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        a { color: inherit; }
        .page-shell { width: min(1160px, calc(100% - 2rem)); margin: auto; padding: 1.25rem 0 3rem; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
        .brand { color: var(--navy); font-weight: 850; text-decoration: none; letter-spacing: -.03em; }
        .brand span { color: var(--teal); }
        .back-link { color: #165ac0; font-size: .92rem; font-weight: 700; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
            padding: clamp(1.5rem, 5vw, 3.25rem);
            border-radius: 1.35rem;
            color: var(--white);
            background: linear-gradient(125deg, var(--navy), #176b87 68%, var(--teal));
            box-shadow: 0 1.25rem 3rem rgba(16, 42, 67, .16);
        }
        .hero::after {
            position: absolute;
            right: -6rem;
            bottom: -12rem;
            width: 25rem;
            height: 25rem;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 50%;
            box-shadow: 0 0 0 2.5rem rgba(255,255,255,.04), 0 0 0 5rem rgba(255,255,255,.035);
            content: "";
        }
        .eyebrow { margin: 0 0 .55rem; color: #ffe09a; font-size: .76rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        .hero h1 { position: relative; z-index: 1; max-width: 42rem; margin: 0; font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.1; letter-spacing: -.045em; }
        .hero p:last-child { position: relative; z-index: 1; max-width: 43rem; margin: .9rem 0 0; color: #e6f6ff; font-size: 1.06rem; }

        .notes-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) minmax(210px, 300px);
            align-items: center;
            gap: 1.1rem;
            margin-bottom: 1.5rem;
            padding: 1.2rem;
            border: 1px solid rgba(31,111,235,.16);
            border-radius: 1rem;
            background: linear-gradient(110deg, #fff, #f2f8ff);
            box-shadow: 0 .75rem 2rem rgba(16,42,67,.07);
        }
        .note-icon { display: grid; width: 3.5rem; height: 3.5rem; place-items: center; border-radius: 1rem; color: white; background: linear-gradient(140deg, var(--blue), var(--teal)); font-size: 1.5rem; }
        .notes-card h2 { margin: 0 0 .2rem; color: var(--navy); font-size: 1.15rem; }
        .notes-card p { margin: 0; color: var(--muted); font-size: .92rem; }
        .search { width: 100%; padding: .75rem .85rem; border: 1px solid var(--line); border-radius: .7rem; background: white; font: inherit; }
        .search:focus { border-color: var(--blue); outline: 3px solid rgba(31,111,235,.13); }

        .section-heading { display: flex; justify-content: space-between; align-items: end; gap: 1rem; margin: 1.6rem 0 .85rem; }
        .section-heading h2 { margin: 0; color: var(--navy); font-size: 1.35rem; letter-spacing: -.02em; }
        .section-heading p { margin: 0; color: var(--muted); font-size: .9rem; }
        .unit-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .9rem; }
        .unit-card {
            display: flex;
            min-height: 155px;
            flex-direction: column;
            padding: 1rem;
            border: 1px solid var(--line);
            border-radius: .95rem;
            background: var(--white);
            box-shadow: 0 .45rem 1.4rem rgba(16,42,67,.045);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .unit-card.available:hover { transform: translateY(-3px); border-color: rgba(31,111,235,.4); box-shadow: 0 .9rem 1.8rem rgba(16,42,67,.1); }
        .unit-top { display: flex; justify-content: space-between; align-items: center; gap: .5rem; margin-bottom: .65rem; }
        .unit-number { color: var(--teal); font-size: .76rem; font-weight: 850; letter-spacing: .1em; text-transform: uppercase; }
        .status { padding: .2rem .5rem; border-radius: 99px; color: #627d98; background: #edf2f7; font-size: .68rem; font-weight: 750; white-space: nowrap; }
        .status.available { color: #176b54; background: #e5f7ef; }
        .unit-card h3 { margin: 0; color: var(--navy); font-size: 1rem; line-height: 1.35; }
        .unit-card p { margin: .45rem 0 0; color: var(--muted); font-size: .82rem; }
        .unit-card.available { text-decoration: none; }
        .unit-card.unavailable { border-style: dashed; }
        .unit-card[hidden] { display: none; }
        .empty-state { display: none; padding: 2rem; border: 1px dashed var(--line); border-radius: 1rem; color: var(--muted); background: white; text-align: center; }
        .footer { margin-top: 2rem; color: var(--muted); font-size: .82rem; text-align: center; }

        @media (max-width: 820px) { .unit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 600px) {
            .page-shell { width: min(100% - 1.2rem, 1160px); padding-top: .7rem; }
            .notes-card { grid-template-columns: auto 1fr; }
            .notes-card .search { grid-column: 1 / -1; }
            .unit-grid { grid-template-columns: 1fr; gap: .65rem; }
            .unit-card { min-height: auto; }
            .section-heading { align-items: start; flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main class="page-shell">
        <header class="topbar">
            <a class="brand" href="<?php echo site_url('al-notes'); ?>">AL ICT <span>/</span> Student Home</a>
            <a class="back-link" href="<?php echo site_url('guest/loginview'); ?>">Staff sign in</a>
        </header>

        <section class="hero">
            <p class="eyebrow">Advanced Level · Information and Communication Technology</p>
            <h1>Your ICT learning space</h1>
            <p>Browse the lesson units and open available study notes. Lesson 10 is ready to explore.</p>
        </section>

        <section class="notes-card" aria-labelledby="notes-title">
            <div class="note-icon" aria-hidden="true">✦</div>
            <div>
                <h2 id="notes-title">AL Notes</h2>
                <p>Choose a unit below to explore the Advanced Level ICT lesson notes.</p>
            </div>
            <input class="search" id="unit-search" type="search" placeholder="Find a lesson…" aria-label="Search lesson units">
        </section>

        <div class="section-heading">
            <h2>Units 1–13</h2>
            <p id="unit-count" aria-live="polite">13 units</p>
        </div>

        <section class="unit-grid" id="unit-grid" aria-label="AL ICT lesson units">
            <article class="unit-card unavailable" data-search="unit 1 lesson 01 basic concepts of ict">
                <div class="unit-top"><span class="unit-number">Unit 01</span><span class="status">Coming soon</span></div>
                <h3>Lesson 01 — Basic Concepts of ICT</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 2 lesson 02 evolution of computing">
                <div class="unit-top"><span class="unit-number">Unit 02</span><span class="status">Coming soon</span></div>
                <h3>Lesson 02 — Evolution of Computing</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 3 lesson 03 number systems">
                <div class="unit-top"><span class="unit-number">Unit 03</span><span class="status">Coming soon</span></div>
                <h3>Lesson 03 — Number Systems</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 4 lesson 04 logic gates">
                <div class="unit-top"><span class="unit-number">Unit 04</span><span class="status">Coming soon</span></div>
                <h3>Lesson 04 — Logic Gates</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 5 lesson 05 operating systems">
                <div class="unit-top"><span class="unit-number">Unit 05</span><span class="status">Coming soon</span></div>
                <h3>Lesson 05 — Operating Systems</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 6 lesson 06 networking">
                <div class="unit-top"><span class="unit-number">Unit 06</span><span class="status">Coming soon</span></div>
                <h3>Lesson 06 — Networking</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 7 lesson 07">
                <div class="unit-top"><span class="unit-number">Unit 07</span><span class="status">Coming soon</span></div>
                <h3>Lesson 07 — Details not provided</h3>
                <p>This unit was not included in the supplied lesson list.</p>
            </article>
            <article class="unit-card unavailable" data-search="unit 8 lesson 08 database">
                <div class="unit-top"><span class="unit-number">Unit 08</span><span class="status">Coming soon</span></div>
                <h3>Lesson 08 — Database</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 9 lesson 09 python">
                <div class="unit-top"><span class="unit-number">Unit 09</span><span class="status">Coming soon</span></div>
                <h3>Lesson 09 — Python</h3>
            </article>
            <a class="unit-card available" href="<?php echo site_url('al-ict/lesson-10'); ?>" data-search="unit 10 lesson 10 html css php web development">
                <div class="unit-top"><span class="unit-number">Unit 10</span><span class="status available">Available</span></div>
                <h3>Lesson 10 — HTML &amp; CSS, PHP</h3>
                <p>Open the complete web development note.</p>
            </a>
            <article class="unit-card unavailable" data-search="unit 11 lesson 11 internet of things iot">
                <div class="unit-top"><span class="unit-number">Unit 11</span><span class="status">Coming soon</span></div>
                <h3>Lesson 11 — Internet of Things</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 12 lesson 12 e commerce ecommerce">
                <div class="unit-top"><span class="unit-number">Unit 12</span><span class="status">Coming soon</span></div>
                <h3>Lesson 12 — E-Commerce</h3>
            </article>
            <article class="unit-card unavailable" data-search="unit 13 lesson 13 trends and future directions of ict">
                <div class="unit-top"><span class="unit-number">Unit 13</span><span class="status">Coming soon</span></div>
                <h3>Lesson 13 — Trends and Future Directions of ICT</h3>
            </article>
        </section>
        <div class="empty-state" id="empty-state">No matching lesson units. Try a different search.</div>
        <footer class="footer">AL ICT Student Home · Lesson notes are added as they become available.</footer>
    </main>
    <script>
        (function () {
            var search = document.getElementById('unit-search');
            var cards = Array.prototype.slice.call(document.querySelectorAll('.unit-card'));
            var count = document.getElementById('unit-count');
            var empty = document.getElementById('empty-state');

            search.addEventListener('input', function () {
                var query = search.value.trim().toLocaleLowerCase();
                var visible = 0;

                cards.forEach(function (card) {
                    var searchableText = (card.getAttribute('data-search') || card.textContent).toLocaleLowerCase();
                    var matches = !query || searchableText.indexOf(query) !== -1;
                    card.hidden = !matches;
                    if (matches) {
                        visible++;
                    }
                });

                count.textContent = visible + (visible === 1 ? ' unit' : ' units');
                empty.style.display = visible ? 'none' : 'block';
            });
        }());
    </script>
</body>
</html>
