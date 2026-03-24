/**
 * Clean Form URL Module
 * Automatically cleans empty parameters from filter forms
 * Usage: Add data-clean-form="true" to <form> element
 * Optional configuration:
 * - data-default-params="param1,param2" - Parameters to exclude from URL
 * - data-default-values="param1:value1,param2:value2" - Parameter=value pairs to exclude
 */

class CleanFormURL {
    constructor(formElement) {
        this.form = formElement;
        this.defaultParams = this.getDataset('defaultParams', '');
        this.defaultValues = this.parseDefaultValues();
        this.init();
    }

    getDataset(key, defaultValue) {
        return this.form.dataset[key] || defaultValue;
    }

    parseDefaultValues() {
        const valuesStr = this.getDataset('defaultValues', '');
        if (!valuesStr) return {};

        return valuesStr.split(',').reduce((acc, pair) => {
            const [key, value] = pair.split(':');
            if (key && value !== undefined) {
                acc[key] = value;
            }
            return acc;
        }, {});
    }

    init() {
        // Cleanup URL on page load
        this.cleanupUrl();

        // Cleanup URL on popstate (browser back/forward)
        window.addEventListener('popstate', () => this.cleanupUrl());

        // Handle form submission
        this.form.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    shouldExcludeParam(key, value) {
        // Check if parameter is in defaultParams blacklist
        if (this.defaultParams && this.defaultParams.includes(key)) {
            return true;
        }

        // Check if parameter-value pair is in defaultValues blacklist
        if (this.defaultValues[key] !== undefined && this.defaultValues[key] === value) {
            return true;
        }

        // Skip empty values
        return !value || value.trim() === '';
    }

    handleSubmit(event) {
        if (this.form.method.toUpperCase() !== 'GET') {
            return;
        }

        event.preventDefault();

        const action = this.form.getAttribute('action') || window.location.pathname;
        const actionUrl = new URL(action, window.location.origin);
        const formData = new FormData(this.form);
        const params = new URLSearchParams();

        for (const [key, rawValue] of formData.entries()) {
            if (typeof rawValue !== 'string') {
                continue;
            }

            const value = rawValue.trim();

            if (this.shouldExcludeParam(key, value)) {
                continue;
            }

            params.append(key, value);
        }

        const queryString = params.toString();
        const targetUrl = queryString ? `${actionUrl.pathname}?${queryString}` : actionUrl.pathname;

        window.location.assign(targetUrl);
    }

    cleanupUrl() {
        try {
            const url = new URL(window.location);
            const params = new URLSearchParams(url.search);
            let hasChanges = false;

            // Clean up each parameter
            for (const [key, value] of params.entries()) {
                if (this.shouldExcludeParam(key, value)) {
                    params.delete(key);
                    hasChanges = true;
                }
            }

            if (hasChanges) {
                const queryString = params.toString();
                const newUrl = queryString ? `${url.pathname}?${queryString}` : url.pathname;
                window.history.replaceState({}, '', newUrl);
            }
        } catch (error) {
            console.warn('Unable to cleanup URL:', error);
        }
    }
}

// Initialize all clean-form-url instances on page load
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('[data-clean-form="true"]');
    forms.forEach((form) => {
        new CleanFormURL(form);
    });
});

// Export for manual initialization if needed
window.CleanFormURL = CleanFormURL;

// Suspend modal functions (for accounts page)
let suspendAccountId = null;

window.openSuspendModal = function(accountId) {
    suspendAccountId = accountId;
    const modal = document.getElementById('suspendModal');
    if (modal) {
        modal.classList.remove('hidden');
        const suspendForm = document.getElementById('suspendForm');
        if (suspendForm) {
            suspendForm.action = `/accounts/${accountId}/suspend`;
        }
    }
};

window.closeSuspendModal = function() {
    const modal = document.getElementById('suspendModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    suspendAccountId = null;
};

// Initialize modal event listeners
const modal = document.getElementById('suspendModal');
if (modal) {
    modal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeSuspendModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSuspendModal();
        }
    });
}
