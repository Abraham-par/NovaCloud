class LanguageSwitcher {
    constructor() {
        const serverLangMeta = document.querySelector('meta[name="server-language"]');
        const serverLang = serverLangMeta ? serverLangMeta.getAttribute('content') : null;
        this.currentLang = serverLang || localStorage.getItem('nova_lang') || 'en';
        this.translations = {};
        this.initialized = false;
        this.init();
    }

    async init() {
        if (this.initialized) return;

        try {
            await this.loadTranslations();
            this.applyLanguage();
            this.setupEventListeners();
            this.initialized = true;
        } catch (error) {
            console.error('Language switcher initialization failed:', error);
        }
    }

    async loadTranslations() {
        try {
            const response = await fetch('assets/json/languages.json');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            this.translations = await response.json();
        } catch (error) {
            console.error('Error loading translations:', error);
            // Fallback to empty translations
            this.translations = { en: {}, am: {}, om: {} };
        }
    }

    applyLanguage() {
        // Update HTML lang attribute
        document.documentElement.lang = this.currentLang;

        // Update direction for RTL languages
        if (this.currentLang === 'ar' || this.currentLang === 'he') {
            document.documentElement.dir = 'rtl';
        } else {
            document.documentElement.dir = 'ltr';
        }

        // Update all elements with data-key
        this.updateTextContent();

        // Update placeholders
        this.updatePlaceholders();

        // Update select options
        this.updateSelectOptions();

        // Update page title
        this.updatePageTitle();

        // Save to localStorage
        localStorage.setItem('nova_lang', this.currentLang);

        // Update language selector if exists
        this.updateLanguageSelector();

        // Update server-side language preference via AJAX
        this.updateServerLanguage();
    }

    updateTextContent() {
        document.querySelectorAll('[data-key]').forEach(element => {
            if (element.tagName !== 'INPUT' && element.tagName !== 'TEXTAREA' && element.tagName !== 'SELECT') {
                const key = element.getAttribute('data-key');
                if (this.translations[this.currentLang] && this.translations[this.currentLang][key]) {
                    element.textContent = this.translations[this.currentLang][key];
                }
            }
        });
    }

    updatePlaceholders() {
        document.querySelectorAll('[data-key]').forEach(element => {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                const key = element.getAttribute('data-key');
                if (this.translations[this.currentLang] && this.translations[this.currentLang][key]) {
                    element.placeholder = this.translations[this.currentLang][key];
                }
            }
        });
    }

    updateSelectOptions() {
        document.querySelectorAll('select option[data-key]').forEach(option => {
            const key = option.getAttribute('data-key');
            if (this.translations[this.currentLang] && this.translations[this.currentLang][key]) {
                option.textContent = this.translations[this.currentLang][key];
            }
        });
    }

    updatePageTitle() {
        const pageTitle = document.querySelector('title');
        if (pageTitle) {
            const titleKey = pageTitle.getAttribute('data-key');
            if (titleKey && this.translations[this.currentLang] && this.translations[this.currentLang][titleKey]) {
                document.title = this.translations[this.currentLang][titleKey];
            } else if (this.translations[this.currentLang] && this.translations[this.currentLang]['site_name']) {
                document.title = this.translations[this.currentLang]['site_name'];
            }
        }
    }

    updateLanguageSelector() {
        const langSelect = document.getElementById('languageSelect');
        if (langSelect) {
            langSelect.value = this.currentLang;
        }

        // Also update any other language selectors
        document.querySelectorAll('.language-selector select').forEach(select => {
            select.value = this.currentLang;
        });
    }

    setupEventListeners() {
        // Language selector change
        document.addEventListener('change', (e) => {
            if (e.target.id === 'languageSelect' || e.target.classList.contains('language-select')) {
                this.setLanguage(e.target.value);
            }
        });

        // Language selector buttons
        document.querySelectorAll('.language-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const lang = e.target.getAttribute('data-lang');
                if (lang) {
                    this.setLanguage(lang);
                }
            });
        });
    }

    setLanguage(lang) {
        if (this.currentLang !== lang) {
            this.currentLang = lang;
            this.applyLanguage();

            // Dispatch custom event for other components
            document.dispatchEvent(new CustomEvent('languageChanged', {
                detail: { language: lang }
            }));
        }
    }

    async updateServerLanguage() {
        try {
            const formData = new FormData();
            formData.append('language', this.currentLang);
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            formData.append('csrf_token', csrfMeta ? csrfMeta.getAttribute('content') : '');

            await fetch('api/update-language.php', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            console.error('Error updating server language:', error);
        }
    }

    translate(key, params = {}) {
        let translation = (this.translations[this.currentLang] && this.translations[this.currentLang][key]) ? this.translations[this.currentLang][key] : key;

        // Replace parameters
        Object.keys(params).forEach(param => {
            translation = translation.replace(`{${param}}`, params[param]);
        });

        return translation;
    }

    getCurrentLanguage() {
        return this.currentLang;
    }
}

// Initialize globally
window.languageSwitcher = new LanguageSwitcher();

// Make it available immediately
document.addEventListener('DOMContentLoaded', () => {
    window.languageSwitcher.init();
});