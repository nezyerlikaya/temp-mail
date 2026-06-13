import Alpine from 'alpinejs';
import {
    Activity,
    BadgeCheck,
    BadgeDollarSign,
    Bell,
    Braces,
    Check,
    ChartNoAxesCombined,
    CircleDashed,
    createIcons,
    DatabaseBackup,
    Files,
    Globe2,
    Images,
    Inbox,
    KeyRound,
    Languages,
    LayoutDashboard,
    ListFilter,
    LoaderCircle,
    LogOut,
    LockKeyhole,
    Mail,
    MailCheck,
    MailPlus,
    Menu,
    MessageSquareWarning,
    Minus,
    MessagesSquare,
    NotebookPen,
    Palette,
    PanelsTopLeft,
    PackageCheck,
    PlugZap,
    RefreshCw,
    RotateCcw,
    ScrollText,
    SearchCheck,
    Search,
    SearchX,
    ServerCog,
    Settings2,
    ShieldAlert,
    ShieldBan,
    ShieldCheck,
    Siren,
    SpellCheck2,
    SwatchBook,
    Tags,
    Type,
    TriangleAlert,
    UserRoundPen,
    Users,
    Wrench,
    WifiOff,
    X,
    CornerDownLeft,
    FilePlus,
    FilePlus2,
} from 'lucide';

window.Alpine = Alpine;

const adminIcons = {
    Activity,
    BadgeCheck,
    BadgeDollarSign,
    Bell,
    Braces,
    Check,
    ChartNoAxesCombined,
    CircleDashed,
    CornerDownLeft,
    DatabaseBackup,
    Files,
    FilePlus,
    FilePlus2,
    Globe2,
    Images,
    Inbox,
    KeyRound,
    Languages,
    LayoutDashboard,
    ListFilter,
    LoaderCircle,
    LogOut,
    LockKeyhole,
    Mail,
    MailCheck,
    MailPlus,
    Menu,
    MessageSquareWarning,
    Minus,
    MessagesSquare,
    NotebookPen,
    Palette,
    PanelsTopLeft,
    PackageCheck,
    PlugZap,
    RefreshCw,
    RotateCcw,
    ScrollText,
    Search,
    SearchCheck,
    SearchX,
    ServerCog,
    Settings2,
    ShieldAlert,
    ShieldBan,
    ShieldCheck,
    Siren,
    SpellCheck2,
    SwatchBook,
    Tags,
    Type,
    TriangleAlert,
    UserRoundPen,
    Users,
    Wrench,
    WifiOff,
    X,
};

window.renderAdminIcons = () => createIcons({ icons: adminIcons });

Alpine.store('commandPalette', { open: false });

Alpine.data('commandPalette', (commands) => ({
    open: false,
    query: '',
    activeIndex: 0,
    commands,
    recentIds: [],
    returnFocusTo: null,

    get matchingCommands() {
        const query = this.query.trim().toLocaleLowerCase();

        if (! query) {
            return this.commands;
        }

        return this.commands.filter((command) => {
            const searchable = [
                command.title,
                command.description,
                command.group,
                ...command.keywords,
            ].join(' ').toLocaleLowerCase();

            return searchable.includes(query);
        });
    },

    get groupedResults() {
        const commands = [...this.matchingCommands];
        const groups = [];

        if (! this.query.trim() && this.recentIds.length > 0) {
            const recentCommands = this.recentIds
                .map((id) => commands.find((command) => command.id === id))
                .filter(Boolean);

            if (recentCommands.length > 0) {
                groups.push({ label: 'Recent', slug: 'recent', commands: recentCommands });
            }
        }

        const recentSet = new Set(groups.flatMap((group) => group.commands.map((command) => command.id)));

        commands
            .filter((command) => ! recentSet.has(command.id))
            .forEach((command) => {
                let group = groups.find((candidate) => candidate.label === command.group);

                if (! group) {
                    group = {
                        label: command.group,
                        slug: command.group.toLocaleLowerCase().replace(/[^a-z0-9]+/g, '-'),
                        commands: [],
                    };
                    groups.push(group);
                }

                group.commands.push(command);
            });

        return groups;
    },

    get flatResults() {
        return this.groupedResults.flatMap((group) => group.commands);
    },

    get activeCommand() {
        return this.flatResults[this.activeIndex] ?? null;
    },

    get resultStatus() {
        const count = this.flatResults.length;

        return count === 1 ? '1 command available' : `${count} commands available`;
    },

    openPalette() {
        if (this.open) {
            return;
        }

        this.returnFocusTo = document.activeElement;
        this.query = '';
        this.activeIndex = 0;
        this.loadRecent();
        this.open = true;
        this.$store.commandPalette.open = true;

        this.$nextTick(() => {
            this.$refs.input?.focus();
            window.renderAdminIcons();
        });
    },

    closePalette() {
        if (! this.open) {
            return;
        }

        this.open = false;
        this.$store.commandPalette.open = false;

        this.$nextTick(() => {
            if (this.returnFocusTo instanceof HTMLElement) {
                this.returnFocusTo.focus();
            }
        });
    },

    handleGlobalKeydown(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLocaleLowerCase() === 'k') {
            event.preventDefault();
            this.openPalette();

            return;
        }

        if (event.key === 'Escape' && this.open) {
            event.preventDefault();
            this.closePalette();
        }
    },

    handleDialogKeydown(event) {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            event.stopPropagation();
            this.moveActive(1);

            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            event.stopPropagation();
            this.moveActive(-1);

            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            event.stopPropagation();
            this.runActive();
        }
    },

    moveActive(direction) {
        const count = this.flatResults.length;

        if (count === 0) {
            return;
        }

        this.activeIndex = (this.activeIndex + direction + count) % count;
        this.scrollActiveIntoView();
    },

    setActive(command) {
        const index = this.flatResults.findIndex((candidate) => candidate.id === command.id);

        if (index >= 0) {
            this.activeIndex = index;
        }
    },

    isActive(command) {
        return this.activeCommand?.id === command.id;
    },

    runActive() {
        if (! this.activeCommand) {
            return;
        }

        this.remember(this.activeCommand);
        window.location.assign(this.activeCommand.url);
    },

    remember(command) {
        const ids = [command.id, ...this.recentIds.filter((id) => id !== command.id)].slice(0, 5);
        this.recentIds = ids;

        try {
            window.localStorage.setItem('admin-command-recents', JSON.stringify(ids));
        } catch {
            // Recent commands are optional and must never block navigation.
        }
    },

    loadRecent() {
        try {
            const stored = JSON.parse(window.localStorage.getItem('admin-command-recents') ?? '[]');
            this.recentIds = Array.isArray(stored) ? stored.slice(0, 5) : [];
        } catch {
            this.recentIds = [];
        }
    },

    scrollActiveIntoView() {
        this.$nextTick(() => {
            document.getElementById(`command-result-${this.activeCommand?.id}`)?.scrollIntoView({ block: 'nearest' });
        });
    },

    trapFocus(event) {
        const dialog = event.currentTarget;
        const focusable = [...dialog.querySelectorAll('input, button, a[href], [tabindex]:not([tabindex="-1"])')]
            .filter((element) => ! element.hasAttribute('disabled'));

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
}));

Alpine.data('dashboardLive', (config) => ({
    endpoint: config.endpoint,
    payload: config.initial,
    intervalSeconds: Number(config.initial.default_interval ?? 30),
    allowedIntervals: config.initial.allowed_intervals ?? [15, 30, 60],
    autoRefresh: true,
    refreshing: false,
    connectionUnavailable: false,
    pausedByVisibility: false,
    changedKeys: [],
    timer: null,
    statusMessage: 'Live',

    init() {
        try {
            const storedInterval = Number(window.localStorage.getItem('dashboard-refresh-interval'));
            const storedAutoRefresh = window.localStorage.getItem('dashboard-auto-refresh');

            if (this.allowedIntervals.includes(storedInterval)) {
                this.intervalSeconds = storedInterval;
            }

            if (storedAutoRefresh !== null) {
                this.autoRefresh = storedAutoRefresh === 'true';
            }
        } catch {
            this.intervalSeconds = Number(config.initial.default_interval ?? 30);
        }

        document.addEventListener('visibilitychange', () => {
            this.pausedByVisibility = document.hidden;

            if (document.hidden) {
                this.clearTimer();
                this.statusMessage = 'Paused';
            } else {
                this.statusMessage = this.autoRefresh ? 'Live' : 'Paused';
                this.refresh();
                this.schedule();
            }
        });

        this.schedule();
    },

    get metrics() {
        return this.payload.metrics ?? [];
    },

    get criticalAlerts() {
        return this.payload.alerts ?? [];
    },

    get stale() {
        const updated = Date.parse(this.payload.last_updated);

        if (! updated) {
            return true;
        }

        return (Date.now() - updated) > Number(this.payload.stale_after_seconds ?? 120) * 1000;
    },

    metric(key) {
        return this.metrics.find((metric) => metric.key === key) ?? {};
    },

    metricValue(key) {
        return this.metric(key).value ?? '';
    },

    metricTone(key) {
        return this.metric(key).tone ?? 'neutral';
    },

    metricChanged(key) {
        return this.changedKeys.includes(key);
    },

    setAutoRefresh(value) {
        this.autoRefresh = value;
        this.statusMessage = value ? 'Live' : 'Paused';

        try {
            window.localStorage.setItem('dashboard-auto-refresh', String(value));
        } catch {
            // Dashboard refresh preferences are optional.
        }

        this.schedule();
    },

    setIntervalSeconds(value) {
        this.intervalSeconds = Number(value);

        try {
            window.localStorage.setItem('dashboard-refresh-interval', String(this.intervalSeconds));
        } catch {
            // Dashboard refresh preferences are optional.
        }

        this.schedule();
    },

    async refresh() {
        if (this.refreshing) {
            return;
        }

        this.refreshing = true;
        this.statusMessage = 'Refreshing';

        try {
            const response = await fetch(this.endpoint, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error('Dashboard refresh failed.');
            }

            const next = await response.json();
            this.changedKeys = this.changedMetricKeys(this.payload.metrics ?? [], next.metrics ?? []);
            this.payload = next;
            this.connectionUnavailable = false;
            this.statusMessage = this.autoRefresh && ! this.pausedByVisibility ? 'Live' : 'Paused';

            window.setTimeout(() => {
                this.changedKeys = [];
            }, 1800);
        } catch {
            this.connectionUnavailable = true;
            this.statusMessage = 'Connection unavailable';
        } finally {
            this.refreshing = false;
            this.schedule();
            this.$nextTick(() => window.renderAdminIcons());
        }
    },

    changedMetricKeys(previous, next) {
        return next
            .filter((metric) => {
                const oldMetric = previous.find((candidate) => candidate.key === metric.key);

                return oldMetric && String(oldMetric.value) !== String(metric.value);
            })
            .map((metric) => metric.key);
    },

    schedule() {
        this.clearTimer();

        if (! this.autoRefresh || document.hidden) {
            return;
        }

        this.timer = window.setTimeout(() => this.refresh(), this.intervalSeconds * 1000);
    },

    clearTimer() {
        if (this.timer) {
            window.clearTimeout(this.timer);
            this.timer = null;
        }
    },
}));

Alpine.start();

createIcons({
    icons: {
        ...adminIcons,
    },
});
