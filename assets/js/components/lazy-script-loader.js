export default class LazyScriptsLoader {
    /**
     * @params {Object} events Window events to trigger lazy loading
     * @params {Object} scripts Delayed scripts
     */
    constructor(events, scripts, timeout = 4000) {
        this.loaded = false;
        this.loading = false;
        this.timeout = timeout;
        this.lazyScripts = scripts;
        this.triggerEvents = events;
        this.eventCallback = this.load.bind(this);
    }

    init(t) {
        setTimeout(() => {
            if (!this.loaded && !this.loading) {
                this.load();
            }
        }, this.timeout);

        window.addEventListener('zzzScriptsLoaded', () => this.onLoaded(t));

        this.triggerEvents.forEach(e => window.addEventListener(e, t.eventCallback));

        this.lazyScripts.forEach(script => script.preload && this.preloadScript(script));
    }

    load() {
        if (this.loaded || this.loading) {
            return;
        }

        this.loading = true;

        this.lazyScripts.forEach(script => this.appendScript(script));

        this.loaded = true;
        this.loading = false;

        window.dispatchEvent(new Event('zzzScriptsLoaded'));
    }

    onLoaded(t) {
        this.triggerEvents.forEach(e => window.removeEventListener(e, t.eventCallback));

        console.log("Lazy scripts loaded successfully!");
    }

    preloadScript(script) {
        if (!script.id || document.getElementById(script.id)) {
            return;
        }

        const el = document.createElement('link');

        el.id = script.id;
        el.as = 'script';
        el.rel = 'preload';

        if (script.version) {
            el.href = script.uri + '?ver=' + script.ver;
        } else {
            el.href = script.uri;
        }

        document.head.append(el);
    }

    appendScript(script) {
        const el = document.createElement('script');

        el.src = script.uri;

        if (script.version) {
            el.src += '?ver=' + script.ver;
        }

        if (script.type) {
            el.type = script.type;
        } else {
            el.type = 'text/javascript';
        }

        document.body.append(el);
    }
}
