<div
    x-data="commandPalette(@js($commands))"
    x-on:open-command-palette.window="openPalette()"
    x-on:keydown.window="handleGlobalKeydown($event)"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', open); flatResults; $nextTick(() => window.renderAdminIcons())"
>
    <template x-teleport="body">
        <div x-cloak x-show="open" class="fixed inset-0 z-[70]" role="presentation">
            <div class="absolute inset-0 bg-stone-950/55 backdrop-blur-sm" x-on:click="closePalette()" aria-hidden="true"></div>

            <div class="relative flex min-h-full items-start justify-center px-3 py-[8vh] sm:px-6">
                <section
                    class="flex max-h-[82vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-stone-200 bg-white shadow-2xl shadow-stone-950/25"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="command-palette-title"
                    aria-describedby="command-palette-description"
                    x-on:keydown.tab="trapFocus($event)"
                >
                    <div class="flex items-center gap-3 border-b border-stone-200 px-4 sm:px-5">
                        <i data-lucide="search" class="size-5 shrink-0 text-stone-400" aria-hidden="true"></i>
                        <div class="sr-only">
                            <h2 id="command-palette-title">Command palette</h2>
                            <p id="command-palette-description">Search allowed modules and actions. Use arrow keys to navigate and Enter to open.</p>
                        </div>
                        <input
                            x-ref="input"
                            x-model="query"
                            x-on:input="activeIndex = 0"
                            x-on:keydown="handleDialogKeydown($event)"
                            type="search"
                            class="h-14 min-w-0 flex-1 border-0 bg-transparent px-0 text-base font-semibold text-stone-950 outline-none placeholder:text-stone-400 focus:ring-0"
                            placeholder="Search modules and actions..."
                            aria-label="Search commands"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-controls="command-results"
                            x-bind:aria-expanded="open.toString()"
                            x-bind:aria-activedescendant="activeCommand ? 'command-result-' + activeCommand.id : null"
                            autocomplete="off"
                        >
                        <button type="button" class="grid size-9 shrink-0 place-items-center rounded-md text-stone-500 transition hover:bg-stone-100 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="closePalette()" aria-label="Close command palette">
                            <i data-lucide="x" class="size-5" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div id="command-results" class="min-h-0 flex-1 overflow-y-auto p-2 sm:p-3" role="listbox" aria-label="Command results">
                        <template x-if="flatResults.length === 0">
                            <x-admin.command-empty-state />
                        </template>

                        <template x-for="group in groupedResults" x-bind:key="group.label">
                            <section class="mb-3 last:mb-0" x-bind:aria-labelledby="'command-group-' + group.slug">
                                <h3 class="px-3 py-2 text-xs font-bold uppercase text-stone-500" x-bind:id="'command-group-' + group.slug" x-text="group.label"></h3>
                                <div class="space-y-1">
                                    <template x-for="command in group.commands" x-bind:key="command.id">
                                        <x-admin.command-result />
                                    </template>
                                </div>
                            </section>
                        </template>
                    </div>

                    <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 bg-stone-50 px-4 py-3 text-xs text-stone-500 sm:px-5">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center gap-1"><kbd class="rounded border border-stone-300 bg-white px-1.5 py-0.5 font-bold">↑↓</kbd> Navigate</span>
                            <span class="inline-flex items-center gap-1"><kbd class="rounded border border-stone-300 bg-white px-1.5 py-0.5 font-bold">Enter</kbd> Open</span>
                        </div>
                        <span aria-live="polite" x-text="resultStatus"></span>
                    </footer>
                </section>
            </div>
        </div>
    </template>
</div>
