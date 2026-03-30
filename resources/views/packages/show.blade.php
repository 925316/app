<x-app-sidebar-layout>
    <x-slot name="header">
        {{ __('Package Details') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Review release metadata, keep download and admin actions intact, and continue the cinematic package handoff.') }}
    </x-slot>

    @php
        $deletePackageReleaseConfirmation = __('Are you sure you want to delete this package release? This action cannot be undone.');
        $virusDetectionLinks = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $release->virus_detection_url))));
    @endphp

    <div class="space-y-8" data-page="packages-show">
        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Release Snapshot') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Version') }} {{ $release->version }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$release->release_channel === 'stable' ? 'active' : 'info'" :text="ucfirst($release->release_channel) . ' ' . __('Release')" />

                        @if ($release->created_at)
                            <span class="app-shell-body-copy text-sm">
                                {{ __('Published') }} {{ $release->created_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="app-toolbar-actions">
                    @if ($isAdmin ?? false)
                        <a href="{{ route('packages.manage') }}" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="cube" class="h-4 w-4" />
                            {{ __('Manage Packages') }}
                        </a>
                    @endif

                    <a href="{{ route('packages.index') }}" class="btn btn-secondary btn-sm gap-2">
                        <x-icon name="reset" class="h-4 w-4" />
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(18rem,1fr)]">
                <div class="space-y-6">
                    <div class="card-shell-muted space-y-5 p-6">
                        <div>
                            <p class="section-kicker">{{ __('Release details') }}</p>
                            <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Metadata and delivery') }}</h3>
                            <p class="app-shell-body-copy text-sm">{{ __('Keep the same release facts, but present them as stronger cinematic detail surfaces.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Version') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ $release->version }}</p>
                                <p class="app-shell-body-copy text-sm">{{ __('Release ID:') }} {{ $release->id }}</p>
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Channel') }}</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$release->release_channel === 'stable' ? 'active' : 'info'" :text="ucfirst($release->release_channel)" />
                                    @if ($release->release_channel === 'stable')
                                        <span class="badge badge-stable">{{ __('Production ready') }}</span>
                                    @else
                                        <span class="badge badge-info">{{ __('Testing channel') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Released at') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $release->created_at ? $release->created_at->format('Y-m-d H:i:s') : __('Unknown') }}
                                </p>
                                @if ($release->created_at)
                                    <p class="app-shell-body-copy text-sm">{{ $release->created_at->diffForHumans() }}</p>
                                @endif
                            </div>

                            <div class="card-shell-muted space-y-2 p-4">
                                <p class="section-kicker">{{ __('Distribution') }}</p>
                                <p class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ __('Remote package file') }}</p>
                                <p class="app-shell-body-copy text-sm">{{ __('File size and checksum are unchanged and remain unavailable for this remote source.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-shell-muted space-y-5 p-6">
                        <div>
                            <p class="section-kicker">{{ __('Download source') }}</p>
                            <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Package file location') }}</h3>
                        </div>

                        <div class="card-shell-muted p-4">
                            <p class="section-kicker">{{ __('Remote URL') }}</p>
                            <p class="mt-2 break-all text-sm text-gray-700 dark:text-gray-300">{{ $release->download_url }}</p>
                        </div>
                    </div>
                </div>

                <aside class="card-shell-muted flex flex-col justify-between gap-6 p-6">
                    <div class="space-y-5">
                        <div>
                            <p class="section-kicker">{{ __('Primary actions') }}</p>
                            <h3 class="card-heading text-lg font-semibold text-gray-900 dark:text-white">{{ __('Download and maintenance') }}</h3>
                            <p class="app-shell-body-copy text-sm">{{ __('All routes, permissions, and update semantics stay exactly as they are.') }}</p>
                        </div>

                        <div class="grid gap-3">
                            @if ($canDownload ?? false && $release->id)
                                <a href="{{ route('packages.download', ['release' => $release->id]) }}" class="btn btn-primary btn-sm justify-center gap-2">
                                    <x-icon name="cloud" class="h-4 w-4" />
                                    {{ __('Download Package') }}
                                </a>
                            @else
                                <button type="button" disabled class="btn btn-secondary btn-sm cursor-not-allowed justify-center gap-2 opacity-60">
                                    <x-icon name="cloud" class="h-4 w-4" />
                                    {{ __('Download Requires Valid License') }}
                                </button>
                            @endif

                            @if ($isAdmin ?? false)
                                <button type="button" onclick="showChangelogModal()" class="btn btn-secondary btn-sm justify-center gap-2">
                                    <x-icon name="document" class="h-4 w-4" />
                                    {{ __('Edit Changelog') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="card-shell-muted space-y-2 p-4">
                            <p class="section-kicker">{{ __('Security verification') }}</p>
                            @if (count($virusDetectionLinks) > 0)
                                <x-status-badge status="verified" :text="__('Links available')" />
                                <p class="app-shell-body-copy text-sm">{{ __('External verification URLs remain available for this release.') }}</p>
                            @else
                                <span class="badge badge-default">{{ __('Not available') }}</span>
                                <p class="app-shell-body-copy text-sm">{{ __('No external virus detection links were published.') }}</p>
                            @endif
                        </div>

                        @if ($isAdmin ?? false)
                            <div class="card-shell-muted space-y-3 p-4">
                                <p class="section-kicker">{{ __('Admin actions') }}</p>

                                <form action="{{ route('packages.destroy', $release) }}" method="POST" onsubmit="return confirm('{{ $deletePackageReleaseConfirmation }}')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm w-full justify-center gap-2">
                                        <x-icon name="trash" class="h-4 w-4" />
                                        {{ __('Delete Release') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Release notes') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Changelog') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Keep the existing changelog content and update endpoint while aligning the editing experience to the shared modal system.') }}</p>
                </div>

                @if ($isAdmin ?? false)
                    <div class="app-toolbar-actions">
                        <button type="button" onclick="showChangelogModal()" class="btn btn-primary btn-sm gap-2">
                            <x-icon name="document" class="h-4 w-4" />
                            {{ __('Edit Changelog') }}
                        </button>
                    </div>
                @endif
            </div>

            @if ($release->changelog)
                <div class="card-shell-muted p-6">
                    <div class="prose max-w-none text-sm text-gray-700 dark:prose-invert dark:text-gray-300">
                        {!! nl2br(e($release->changelog)) !!}
                    </div>
                </div>
            @else
                <div class="table-empty-state rounded-2xl border border-dashed border-cool-200/80 bg-cool-50/60 px-6 py-12 text-center dark:border-cool-700/80 dark:bg-cool-900/40">
                    <x-icon name="document" class="table-empty-icon" />
                    <p class="table-empty-title">{{ __('No changelog has been published yet.') }}</p>
                    <p class="table-empty-copy">{{ __('Use the existing update action to add release notes for this package.') }}</p>
                </div>
            @endif
        </section>

        <section class="card-shell space-y-6">
            <div class="app-toolbar">
                <div>
                    <p class="section-kicker">{{ __('Security') }}</p>
                    <h2 class="app-toolbar-title">{{ __('Virus detection links') }}</h2>
                    <p class="app-toolbar-subtitle">{{ __('Surface every published verification URL as a stronger shared detail card without changing the links themselves.') }}</p>
                </div>
            </div>

            @if (count($virusDetectionLinks) > 0)
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($virusDetectionLinks as $url)
                        <div class="card-shell-muted space-y-4 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="section-kicker">{{ __('Verification endpoint') }}</p>
                                    <h3 class="card-heading text-base font-semibold text-gray-900 dark:text-white">{{ __('Published scan link') }}</h3>
                                </div>

                                <x-status-badge status="verified" :text="__('Available')" />
                            </div>

                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="block break-all text-sm text-cool-700 transition hover:text-cool-900 hover:underline dark:text-cool-300 dark:hover:text-cool-100">
                                {{ $url }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="table-empty-state rounded-2xl border border-dashed border-cool-200/80 bg-cool-50/60 px-6 py-12 text-center dark:border-cool-700/80 dark:bg-cool-900/40">
                    <x-icon name="shield" class="table-empty-icon" />
                    <p class="table-empty-title">{{ __('No verification links available.') }}</p>
                    <p class="table-empty-copy">{{ __('This release does not currently include external virus detection URLs.') }}</p>
                </div>
            @endif
        </section>

        @if ($isAdmin ?? false)
            <x-modal name="changelog-modal" :show="false" maxWidth="2xl">
                <div class="modal-header">
                    <div class="flex items-start gap-4">
                        <span class="card-icon-container icon-blue shrink-0">
                            <x-icon name="document" class="h-6 w-6" />
                        </span>

                        <div class="space-y-1">
                            <p class="section-kicker">{{ __('Release notes') }}</p>
                            <h3 class="card-heading text-lg font-semibold">{{ __('Edit Changelog') }}</h3>
                        </div>
                    </div>
                </div>

                <form action="{{ route('packages.update-changelog', $release) }}" method="POST">
                    @csrf

                    <div class="modal-body space-y-4">
                        <p class="card-modal-copy text-sm">
                            {{ __('Update the existing changelog content without changing the route, field names, or save behavior.') }}
                        </p>

                        <div class="space-y-2">
                            <label for="changelog" class="form-label">{{ __('Changelog') }}</label>
                            <textarea name="changelog" id="changelog" rows="8" class="form-textarea">{{ old('changelog', $release->changelog) }}</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'changelog-modal')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-primary-button class="gap-2">
                            <x-icon name="document" class="h-4 w-4" />
                            {{ __('Update Changelog') }}
                        </x-primary-button>
                    </div>
                </form>
            </x-modal>

            <script>
                function showChangelogModal() {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'changelog-modal' }));
                }

                function hideChangelogModal() {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'changelog-modal' }));
                }
            </script>
        @endif
    </div>
</x-app-sidebar-layout>
