<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="landing-page">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description"
        content="{{ __('Operational control for licenses, devices, package releases, and event logs in one atelier command surface.') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|atkinson-hyperlegible:400,700" rel="stylesheet" />

    @include('components.theme-init-script')

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

</head>

@php
    $launchDate = \Carbon\CarbonImmutable::parse('2026-03-28');
    $launchDateLabel = $launchDate->locale(app()->getLocale())->isoFormat('ll');

    $heroHighlights = [
        [
            'title' => __('License Policy'),
            'description' => __('Laravel-backed license administration for licenses, devices, package releases, sessions, and audit visibility.'),
        ],
        [
            'title' => __('Device Recovery'),
            'description' => __('Client-supplied HWID is stored server-side as a SHA-256 hash for binding and recovery workflows.'),
        ],
        [
            'title' => __('Release Channels'),
            'description' => __('Stable and dev packages stay visible through authenticated update checks and signed release decisions.'),
        ],
        [
            'title' => __('Event Visibility'),
            'description' => __('Heartbeat-driven sessions and audit trails stay visible so operators can explain device and license activity.'),
        ],
    ];

    $signalFeedEntries = [
        [
            'message' => __('Stable package release v2.8.4 cleared for production rollout.'),
            'elapsed' => __('02m'),
        ],
        [
            'message' => __('23 device reset requests resolved with full event trace intact.'),
            'elapsed' => __('09m'),
        ],
        [
            'message' => __('Warning and error logs triaged before session churn escalated.'),
            'elapsed' => __('14m'),
        ],
    ];

    $systemSurfaces = [
        [
            'axis' => __('Axis 01'),
            'title' => __('Licenses'),
            'description' => __('Issue, suspend, extend, and upgrade access while keeping privilege tiers explicit.'),
            'meta' => __('Activation keys · expiry windows'),
        ],
        [
            'axis' => __('Axis 02'),
            'title' => __('Devices'),
            'description' => __('Track hardware bindings, recover identity safely, and watch session behavior with context.'),
            'meta' => __('Bound state · HWID reset · session trace'),
        ],
        [
            'axis' => __('Axis 03'),
            'title' => __('Packages'),
            'description' => __('Ship releases through stable and dev channels with cleaner rollout confidence.'),
            'meta' => __('Release channels · changelog control'),
        ],
        [
            'axis' => __('Axis 04'),
            'title' => __('Logs'),
            'description' => __('Read event trails instantly and preserve compliance-grade visibility under pressure.'),
            'meta' => __('Actors · IPs · warnings · errors'),
        ],
    ];

    $controlHighlights = [
        [
            'title' => __('Operator View'),
            'description' => __('See the highest-risk signals first instead of digging through disconnected pages.'),
        ],
        [
            'title' => __('Audit Readiness'),
            'description' => __('Tie license actions, device changes, and log activity into one operational narrative.'),
        ],
    ];

    $controlSequenceSteps = [
        [
            'label' => __('01 · Issue or Extend'),
            'description' => __('Operators can move from account review to license action without losing privilege or expiry context.'),
        ],
        [
            'label' => __('02 · Bind or Recover'),
            'description' => __('Device resets and binding state changes remain visible, deliberate, and easy to audit.'),
        ],
        [
            'label' => __('03 · Release and Trace'),
            'description' => __('Package promotions and event logs stay visually connected so a release decision can be explained after the fact, not just executed in the moment.'),
            'span' => 'sm:col-span-2',
            'showProgress' => true,
            'meta' => __('11 customer groups synchronized · 37 policy changes propagated in the last 24 hours'),
        ],
    ];

    $trustProtocolCards = [
        [
            'label' => __('Verification Posture'),
            'title' => __('Current signed payload verification'),
            'description' => __('Current response verification uses the returned signature and meta.signature metadata to verify the server\'s current signed payload.'),
            'meta' => __('Success responses sign data. Current signed controller and validation error paths sign null.'),
        ],
        [
            'label' => __('Repository Guide'),
            'title' => __('C++ client verification notes stay with the codebase'),
            'description' => __('Developer guide: CPP_CLIENT_VERIFICATION.md in the repository root'),
            'meta' => __('Visible here for discovery without exposing a public Markdown route or file download link.'),
        ],
    ];

    $verificationNotes = [
        __('POST login, heartbeat, activation, and unbind flows use nonce and timestamp checks where the current requests and controllers implement them.'),
        __('Authenticated update checks stay focused on stable and dev release channels so rollout decisions remain explicit and reviewable.'),
        __('Treat envelope metadata as transport context unless the current signed payload contract explicitly includes it in future approved protocol work.'),
    ];

    $workflowTracks = [
        [
            'label' => __('Admin Workflow'),
            'title' => __('Operators keep licenses, devices, sessions, and logs in one working scene.'),
            'description' => __('Admins can inspect license state, device recovery history, session visibility, package releases, and audit trails from the Laravel web application without splitting the story across separate tools.'),
            'meta' => __('License actions · Device recovery · Session visibility · Audit logs'),
        ],
        [
            'label' => __('Signed-In Workflow'),
            'title' => __('Authenticated teams move from review to action without a dead handoff.'),
            'description' => __('Signed-in operators can move from account review into entitlement action, release checks, and operational follow-up while the public homepage continues to frame the truthful product surface for guests.'),
            'meta' => __('Account review · Entitlement changes · Release follow-up'),
        ],
    ];
@endphp

<body class="landing-body min-h-screen bg-[rgb(var(--landing-bg))] text-[rgb(var(--landing-ink))] antialiased transition-colors duration-300">
    <a href="#main-content"
        class="landing-focus-ring sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-full focus:bg-[rgb(var(--landing-ink))] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-[rgb(var(--landing-bg))]">
        {{ __('Skip to homepage content') }}
    </a>

    <div class="landing-grain landing-hero-mesh relative isolate overflow-hidden">
        <div class="landing-grid-overlay pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -left-20 top-[-8rem] h-80 w-80 rounded-full bg-[rgb(var(--landing-brand)/0.3)] blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 top-12 h-96 w-96 rounded-full bg-[rgb(var(--landing-accent-2)/0.28)] blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-[-7rem] left-1/3 h-96 w-96 rounded-full bg-[rgb(var(--landing-accent)/0.24)] blur-3xl"></div>

        <main id="main-content" class="mx-auto max-w-7xl px-6 pb-14 pt-10 sm:px-10 lg:px-12 lg:pb-20 lg:pt-14">
            <header class="landing-fade-up flex flex-wrap items-center justify-between gap-4" style="animation-delay: 20ms;">
                <div class="inline-flex items-center gap-3">
                    <span class="landing-display text-3xl font-semibold tracking-wide">{{ config('app.name') }}</span>
                    <span class="landing-pill rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[rgb(var(--landing-muted))]">
                        {{ __('License Command Theater') }}
                    </span>
                </div>

                <div class="landing-toggle-shell inline-flex items-center rounded-full p-1.5">
                    <x-dark-mode-toggle />
                </div>
            </header>

            <section class="mt-10 grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                <div class="landing-fade-up" style="animation-delay: 90ms;">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[rgb(var(--landing-glow))]">
                        {{ __('Licenses · Devices · Package Releases · Event Logs') }}
                    </p>
                    <h1 class="landing-display mt-4 max-w-4xl text-[3.1rem] font-semibold leading-[0.92] sm:text-[4.4rem] lg:text-[5.6rem]">
                        {{ __('Operational control for licenses, devices, packages, and logs.') }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-[rgb(var(--landing-muted))] sm:text-lg">
                        {{ __('Monitor active licenses, bound devices, package releases, and event logs before small issues turn into operational failures. The landing page stays atelier, but the hierarchy now points directly at the work this platform actually handles.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="landing-focus-ring landing-action-primary inline-flex items-center gap-2 rounded-full px-8 py-3 text-sm font-semibold text-[rgb(var(--landing-ink))] transition duration-300 hover:-translate-y-0.5">
                                {{ __('Open Dashboard') }}
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}"
                                    class="landing-focus-ring landing-action-primary inline-flex items-center gap-2 rounded-full px-8 py-3 text-sm font-semibold text-[rgb(var(--landing-ink))] transition duration-300 hover:-translate-y-0.5">
                                    {{ __('Sign In') }}
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            @endif

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="landing-focus-ring landing-action-secondary inline-flex items-center gap-2 rounded-full px-8 py-3 text-sm font-medium text-[rgb(var(--landing-muted))] transition duration-300 hover:-translate-y-0.5 hover:text-[rgb(var(--landing-ink))]">
                                    {{ __('Create Account') }}
                                </a>
                            @endif
                        @endauth
                    </div>

                    <dl class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($heroHighlights as $highlight)
                            <div class="landing-panel-soft rounded-2xl p-4">
                                <dt class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ $highlight['title'] }}</dt>
                                <dd class="mt-2 text-sm leading-6 text-[rgb(var(--landing-ink))]">{{ $highlight['description'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <aside class="landing-fade-up landing-section-anchor" style="animation-delay: 170ms;" id="signal"
                    x-data="landingSignalBoard({
                        locale: @js(app()->getLocale()),
                        launchLabel: @js($launchDateLabel),
                    })"
                    aria-labelledby="signal-heading">
                    <div class="landing-panel-strong rounded-[1.9rem] p-6 sm:p-7">
                        <div class="flex items-center justify-between">
                            <div>
                                <p id="signal-heading" class="text-xs font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-muted))]">{{ __('Signal Board') }}</p>
                                <p class="mt-2 text-sm leading-6 text-[rgb(var(--landing-muted))]">{{ __('An illustrative operations snapshot showing the kinds of signals teams watch first: entitlement volume, device pressure, release readiness, and event noise.') }}</p>
                            </div>
                            <span class="landing-pill-accent inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-[rgb(var(--landing-accent))]">
                                <span class="landing-status-dot h-2 w-2 rounded-full bg-[rgb(var(--landing-accent))]" aria-hidden="true"></span>
                                {{ __('Illustrative') }}
                            </span>
                        </div>
                        <div class="mt-5 grid gap-4">
                            <article class="landing-panel-metric rounded-2xl p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ __('Active Licenses') }}</p>
                                    <span class="landing-pill rounded-full px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-glow))]">{{ __('Protected') }}</span>
                                </div>
                                <p class="landing-display mt-1 text-4xl font-semibold text-[rgb(var(--landing-ink))]"
                                    x-text="formatNumber(animated.activeLicenses)">2,314</p>
                                <p class="mt-2 text-xs leading-5 text-[rgb(var(--landing-muted))]">
                                    <span x-text="formatPercent(animated.coverageRate)">84.0%</span>
                                    {{ __('of issued seats are currently active across customer accounts.') }}
                                </p>
                                <div class="landing-progress-track mt-3 h-2 rounded-full">
                                    <div class="h-2 rounded-full bg-[rgb(var(--landing-brand))] transition-[width] duration-1000 ease-out"
                                        :style="`width: ${animated.coverageRate}%`"
                                        style="width: 84%"></div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-muted))]">
                                    <span class="landing-pill rounded-full px-2.5 py-1">
                                        +<span x-text="stats.activeLicenses.todayChange">0</span>
                                        {{ __('today') }}
                                    </span>
                                    <span class="landing-pill rounded-full px-2.5 py-1 text-[rgb(var(--landing-glow))]">
                                        {{ __('Since') }} <span x-text="launchLabel()">{{ $launchDateLabel }}</span>
                                    </span>
                                </div>
                            </article>
                            <div class="grid grid-cols-2 gap-3">
                                <article class="landing-panel-metric rounded-2xl p-4">
                                    <p class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ __('Bound Devices') }}</p>
                                    <p class="landing-display mt-1 text-3xl font-semibold text-[rgb(var(--landing-brand))]"
                                        x-text="formatNumber(animated.boundDevices)">9,847</p>
                                    <p class="mt-2 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-muted))]">
                                        {{ __('today') }} +<span x-text="stats.boundDevices.todayChange">0</span>
                                        · {{ __('midnight') }} +<span x-text="stats.boundDevices.projectedChange">0</span>
                                    </p>
                                </article>
                                <article class="landing-panel-metric rounded-2xl p-4">
                                    <p class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ __('Deploy Success') }}</p>
                                    <p class="landing-display mt-1 text-3xl font-semibold text-[rgb(var(--landing-accent))]"
                                        x-text="formatPercent(animated.deploySuccess)">99.2%</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-muted))]">
                                        <span class="landing-pill-accent rounded-full px-2.5 py-1 transition-colors duration-300"
                                            :class="{
                                                'text-[rgb(var(--landing-accent))]': stats.deploySuccess.direction === 'up',
                                                'text-[rgb(var(--landing-brand))]': stats.deploySuccess.direction === 'down',
                                                'text-[rgb(var(--landing-muted))]': stats.deploySuccess.direction === 'steady',
                                            }"
                                            x-text="driftLabel()">+0.0% drift</span>
                                    </div>
                                </article>
                            </div>
                            <article class="landing-panel rounded-2xl p-4">
                                <p class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ __('Current Command Feed') }}</p>
                                <ul class="mt-3 grid gap-3 text-sm leading-6 text-[rgb(var(--landing-ink))]">
                                    @foreach ($signalFeedEntries as $entry)
                                        <li
                                            @class([
                                                'flex items-start justify-between gap-4',
                                                'border-b border-[rgb(var(--landing-line)/0.4)] pb-3' => ! $loop->last,
                                            ])>
                                            <span>{{ $entry['message'] }}</span>
                                            <span class="shrink-0 text-[rgb(var(--landing-muted))]">{{ $entry['elapsed'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        </div>
                    </div>
                </aside>
            </section>

            <section id="systems" class="landing-section-anchor landing-fade-up mt-10 lg:mt-12" style="animation-delay: 250ms;"
                aria-labelledby="systems-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[rgb(var(--landing-glow))]">{{ __('System Surfaces') }}</p>
                        <h2 id="systems-heading" class="landing-display mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Four surfaces. One command floor.') }}</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-7 text-[rgb(var(--landing-muted))] sm:text-right">{{ __('Every core area is tuned for operational control: entitlement state, device identity, release movement, and the log trail that explains what happened.') }}</p>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($systemSurfaces as $surface)
                        <article class="landing-panel rounded-3xl p-5">
                            <p class="text-xs uppercase tracking-[0.14em] text-[rgb(var(--landing-muted))]">{{ $surface['axis'] }}</p>
                            <p class="landing-display mt-2 text-3xl font-semibold">{{ $surface['title'] }}</p>
                            <p class="mt-2 text-sm leading-6 text-[rgb(var(--landing-muted))]">{{ $surface['description'] }}</p>
                            <p class="mt-4 text-xs uppercase tracking-[0.14em] text-[rgb(var(--landing-glow))]">{{ $surface['meta'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="control"
                class="landing-section-anchor landing-fade-up landing-panel mt-10 rounded-[2.2rem] p-6 lg:mt-12 lg:p-8"
                style="animation-delay: 310ms;" aria-labelledby="control-heading">
                <div class="grid gap-8 lg:grid-cols-[1fr_1.15fr]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[rgb(var(--landing-glow))]">{{ __('Control Sequence') }}</p>
                        <h2 id="control-heading" class="landing-display mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('From entitlement request to release trace, one coherent operational scene.') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-[rgb(var(--landing-muted))] sm:text-base">{{ __('Keep entitlement changes, device recovery, release rollout, and event tracing aligned in one flow so operators can move quickly without losing context.') }}</p>
                        <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($controlHighlights as $highlight)
                                <div class="landing-panel-soft rounded-2xl p-4">
                                    <dt class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ $highlight['title'] }}</dt>
                                    <dd class="mt-2 text-sm leading-6 text-[rgb(var(--landing-ink))]">{{ $highlight['description'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($controlSequenceSteps as $step)
                            <div
                                @class([
                                    'landing-panel-soft rounded-2xl p-4',
                                    $step['span'] ?? null,
                                ])>
                                <p class="text-xs uppercase tracking-[0.12em] text-[rgb(var(--landing-muted))]">{{ $step['label'] }}</p>
                                <p @class([
                                    'mt-2 text-sm leading-6',
                                    'text-[rgb(var(--landing-ink))]' => $step['showProgress'] ?? false,
                                ])>{{ $step['description'] }}</p>

                                @if ($step['showProgress'] ?? false)
                                    <div class="landing-progress-track mt-3 h-2 rounded-full">
                                        <div class="landing-rainbow-bar h-2 w-11/12 rounded-full"></div>
                                    </div>
                                    <p class="mt-2 text-xs text-[rgb(var(--landing-muted))]">{{ $step['meta'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="trust" class="landing-section-anchor landing-fade-up mt-10 lg:mt-12" style="animation-delay: 340ms;"
                aria-labelledby="trust-heading">
                <div class="grid gap-6 lg:grid-cols-[0.92fr_1.08fr] lg:items-start">
                    <div class="landing-panel rounded-[2rem] p-6 sm:p-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[rgb(var(--landing-glow))]">{{ __('Client Trust Protocol') }}</p>
                        <h2 id="trust-heading" class="landing-display mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Truthful verification copy, repository-root guidance, and no hidden protocol promises.') }}</h2>
                        <p class="mt-4 text-sm leading-7 text-[rgb(var(--landing-muted))] sm:text-base">{{ __('Atelier OS exposes the current verification contract as it exists today: signed payload verification, observable release decisions, and visible guidance for teams integrating a native client against the present API shape.') }}</p>
                        <ul class="mt-6 grid gap-3 text-sm leading-6 text-[rgb(var(--landing-ink))]">
                            @foreach ($verificationNotes as $note)
                                <li class="landing-panel-soft rounded-2xl px-4 py-3">{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($trustProtocolCards as $card)
                            <article class="landing-panel-soft rounded-[1.8rem] p-5 sm:p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-glow))]">{{ $card['label'] }}</p>
                                <h3 class="landing-display mt-3 text-3xl font-semibold leading-tight">{{ $card['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-[rgb(var(--landing-ink))] sm:text-base">{{ $card['description'] }}</p>
                                <p class="mt-4 text-xs uppercase tracking-[0.14em] text-[rgb(var(--landing-muted))]">{{ $card['meta'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="workflow" class="landing-section-anchor landing-fade-up mt-10 lg:mt-12" style="animation-delay: 360ms;"
                aria-labelledby="workflow-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[rgb(var(--landing-glow))]">{{ __('Admin and User Workflows') }}</p>
                        <h2 id="workflow-heading" class="landing-display mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Guest story in front, authenticated action behind it.') }}</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-7 text-[rgb(var(--landing-muted))] sm:text-right">{{ __('The public homepage stays guest-readable while the authenticated product keeps operational actions within reach for admins and signed-in teams.') }}</p>
                </div>

                <div class="mt-6 grid gap-3 lg:grid-cols-2">
                    @foreach ($workflowTracks as $track)
                        <article class="landing-panel rounded-[1.9rem] p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[rgb(var(--landing-glow))]">{{ $track['label'] }}</p>
                            <h3 class="landing-display mt-3 text-3xl font-semibold leading-tight">{{ $track['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-[rgb(var(--landing-muted))] sm:text-base">{{ $track['description'] }}</p>
                            <p class="mt-4 text-xs uppercase tracking-[0.14em] text-[rgb(var(--landing-ink))]">{{ $track['meta'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <footer class="landing-fade-up mt-10 flex flex-col gap-4 border-t border-[rgb(var(--landing-line)/0.75)] pt-6 text-sm text-[rgb(var(--landing-muted))] sm:flex-row sm:items-center sm:justify-between lg:mt-12" style="animation-delay: 390ms;">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                <p>{{ __('Public homepage · atelier operational landing page') }}</p>
            </footer>
        </main>
    </div>
</body>

</html>
