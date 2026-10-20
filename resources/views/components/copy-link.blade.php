@props(['id', 'url', 'message'])

<aside
    id="{{ $id }}"
    x-data="{
        copied: false,
        linkUrl: @js($url),
        async copyLink() {
            let success = false;
            try {
                await navigator.clipboard.writeText(this.linkUrl);
                success = true;
            } catch {
                // navigator.clipboard needs a secure context; fall back for http://*.test
                const textarea = document.createElement('textarea');
                textarea.value = this.linkUrl;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                success = document.execCommand('copy');
                textarea.remove();
            }
            if (success) {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            }
        }
    }"
    class="bg-cream border border-line rounded-lg p-4 sm:p-6 space-y-2"
>
    <p class="font-body text-sm text-muted">
        {{ $message }}
    </p>

    <div class="flex flex-wrap items-center gap-3">
        <p class="font-mono text-sm text-navy truncate flex-1 min-w-0">
            {{ $url }}
        </p>
        <button
            type="button"
            @click="copyLink()"
            aria-label="Salin Tautan"
            title="Salin Tautan"
            class="min-h-[44px] min-w-[44px] p-2.5 shrink-0 bg-navy text-cream rounded-sm border border-line hover:opacity-90 transition-opacity cursor-pointer inline-flex items-center justify-center"
        >
            <svg x-show="!copied" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="9" y="9" width="12" height="12" />
                <path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1" />
            </svg>
            <svg x-show="copied" x-cloak class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 12.5l5 5L20 6" />
            </svg>
        </button>
        <span class="sr-only" aria-live="polite" x-text="copied ? 'Tersalin' : ''"></span>
    </div>
</aside>
