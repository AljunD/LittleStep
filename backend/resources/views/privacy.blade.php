@php
    $privacy = config('littlestep.privacy');
    $controller = $privacy['controller'];
    $updated = \Carbon\Carbon::parse($privacy['updated'])->format('F j, Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy · LittleStep</title>
    <meta name="description" content="How LittleStep collects, uses and protects the information of children, parents and guardians.">
    <link rel="icon" type="image/png" href="{{ asset('images/littlestep-mark.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-700 antialiased">
    <header class="bg-gradient-to-r from-teal-700 to-emerald-600 px-4 py-10 text-white sm:py-14">
        <div class="mx-auto max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-teal-100">LittleStep</p>
            <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">Privacy Policy</h1>
            <p class="mt-3 text-teal-50">For parents, guardians and teachers of {{ $controller }}.</p>
            <p class="mt-1 text-sm text-teal-100">Last updated {{ $updated }}</p>
        </div>
    </header>

    <main class="mx-auto max-w-3xl space-y-10 px-4 py-10 leading-relaxed">
        <section class="rounded-2xl border border-teal-100 bg-teal-50 p-6" aria-labelledby="simple-words">
            <h2 id="simple-words" class="text-lg font-bold text-teal-950">In simple words (for children)</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-slate-700">
                <li>Your teacher writes down how you are growing and learning, like how you play, talk and draw.</li>
                <li>Only your teacher and your parent or guardian can see what is written about you.</li>
                <li>We keep it safe with passwords, and we never sell it or show it to strangers.</li>
                <li>Your parent or guardian can ask us to fix it or remove it at any time.</li>
            </ul>
        </section>

        <section aria-labelledby="who">
            <h2 id="who" class="text-xl font-bold text-slate-900">1. Who we are</h2>
            <p class="mt-3">
                LittleStep is the system {{ $controller }} uses to follow each child's development with the
                Early Childhood Care and Development (ECCD) Checklist. Teachers use the LittleStep website, and parents and
                guardians use the LittleStep mobile app. {{ ucfirst($controller) }} decides how this information is used and is
                responsible for it under the Data Privacy Act of 2012 (Republic Act No. 10173).
            </p>
        </section>

        <section aria-labelledby="collect">
            <h2 id="collect" class="text-xl font-bold text-slate-900">2. What information we keep</h2>
            <p class="mt-3">Teachers enter this information with the parent's or guardian's consent:</p>
            <ul class="mt-3 list-disc space-y-2 pl-5">
                <li><strong>About the child:</strong> name, sex, date of birth, address, handedness, whether the child is in school
                    and the school's name, the parents' names, ages, occupations and education, number of siblings, birth order,
                    and a photo.</li>
                <li><strong>Development records:</strong> ECCD Checklist results for each evaluation, the teacher's comments,
                    observation notes (for example about the child's background, family and home environment), and the computed
                    scores and their interpretation.</li>
                <li><strong>About the parent or guardian:</strong> name, sex, contact number, address, relationship to the child,
                    and email address.</li>
                <li><strong>About teachers:</strong> name, sex, contact number, address and email address.</li>
                <li><strong>Account and security information:</strong> passwords (stored only in scrambled, unreadable form),
                    email verification, and a security record of actions in LittleStep, such as signing in, viewing or changing
                    records (who did what and when, with the IP address and the device or browser used).</li>
            </ul>
            <p class="mt-3">
                On your phone, the LittleStep app keeps your sign-in only if you choose <em>Remember me</em> (in the phone's secure
                storage), plus your display and language choices. The app has no advertising and no tracking or analytics tools.
            </p>
        </section>

        <section aria-labelledby="use">
            <h2 id="use" class="text-xl font-bold text-slate-900">3. How we use it</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5">
                <li>To record and follow each child's development using the ECCD Checklist and its official scoring.</li>
                <li>To show parents and guardians their own child's progress and what they can practice at home.</li>
                <li>To produce the official ECCD Checklist Child's Record 2 for the child's file.</li>
                <li>To create accounts, verify email addresses and help users reset their passwords.</li>
                <li>To keep the system secure and to check who changed what.</li>
            </ul>
            <p class="mt-3">We do not sell this information, use it for advertising, or use it for any other purpose.</p>
        </section>

        <section aria-labelledby="share">
            <h2 id="share" class="text-xl font-bold text-slate-900">4. Who can see it</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5">
                <li><strong>The child's teacher</strong> sees the records of the children they handle.</li>
                <li><strong>The child's parent or guardian</strong> sees only their own child's records in the app.</li>
                <li><strong>Service providers</strong> we rely on to run the system, the server host and the email service,
                    store or send the information only to provide that service to us.</li>
                <li>We share information with government authorities only when the law requires it.</li>
            </ul>
        </section>

        <section aria-labelledby="protect">
            <h2 id="protect" class="text-xl font-bold text-slate-900">5. How we protect it</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5">
                <li>All connections are encrypted (HTTPS).</li>
                <li>Every account needs a verified email and a password; app sign-ins expire after 12 hours.</li>
                <li>Children's photos are stored privately and are shown only to their teacher and their parent or guardian.</li>
                <li>Every request is checked so users can only reach the records they are allowed to see.</li>
                <li>The database is backed up regularly.</li>
            </ul>
        </section>

        <section aria-labelledby="keep">
            <h2 id="keep" class="text-xl font-bold text-slate-900">6. How long we keep it</h2>
            <p class="mt-3">
                Records are kept while the child is enrolled at {{ $controller }}. Records that are archived stay stored until
                {{ $controller }} deletes them. A parent or guardian may ask for a child's records to be corrected or deleted at
                any time (see section 8).
            </p>
        </section>

        <section aria-labelledby="consent">
            <h2 id="consent" class="text-xl font-bold text-slate-900">7. Consent</h2>
            <p class="mt-3">
                A child's information is entered only with the consent of the child's parent or guardian, usually given when the
                child is enrolled. You may withdraw your consent at any time; we will then stop using the information, except
                where the law requires us to keep it.
            </p>
        </section>

        <section aria-labelledby="rights">
            <h2 id="rights" class="text-xl font-bold text-slate-900">8. Your rights</h2>
            <p class="mt-3">Under the Data Privacy Act of 2012, you have the right to:</p>
            <ul class="mt-3 list-disc space-y-1 pl-5">
                <li>be informed about how your and your child's information is used;</li>
                <li>see a copy of the information we keep;</li>
                <li>have wrong or incomplete information corrected;</li>
                <li>object to its use, and ask for it to be deleted or blocked;</li>
                <li>get a copy of it in a format you can use elsewhere;</li>
                <li>be compensated for damages caused by misuse of your information;</li>
                <li>file a complaint with the National Privacy Commission (<a class="font-semibold text-teal-700 underline" href="https://privacy.gov.ph" rel="noopener">privacy.gov.ph</a>).</li>
            </ul>
            <p class="mt-3">If your information is ever involved in a data breach, we will notify you and the National Privacy Commission.</p>
        </section>

        <section aria-labelledby="contact" class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 id="contact" class="text-xl font-bold text-slate-900">9. Contact us</h2>
            <p class="mt-3">For questions or requests about your information, contact {{ $controller }}:</p>
            <ul class="mt-3 space-y-1">
                @if ($privacy['contact_email'])
                    <li>Email: <a class="font-semibold text-teal-700 underline" href="mailto:{{ $privacy['contact_email'] }}">{{ $privacy['contact_email'] }}</a></li>
                @endif
                @if ($privacy['contact_phone'])
                    <li>Phone: {{ $privacy['contact_phone'] }}</li>
                @endif
                <li>Or speak with your child's teacher at the day care center.</li>
            </ul>
        </section>

        <section aria-labelledby="changes">
            <h2 id="changes" class="text-xl font-bold text-slate-900">10. Changes to this policy</h2>
            <p class="mt-3">If we change this policy, we will update the date at the top of this page and tell parents and guardians about important changes.</p>
        </section>
    </main>

    <footer class="border-t border-slate-200 px-4 py-6 text-center text-sm text-slate-500">
        LittleStep · {{ ucfirst($controller) }}
    </footer>
</body>
</html>
