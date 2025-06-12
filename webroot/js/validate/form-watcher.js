/**
 * FormWatcher - реактивный наблюдатель за формой с поддержкой валидации
 * @author Evgeny
 */
(function(window) {
    class FormWatcher {
        constructor(formElement, options = {}) {
            this.form = formElement;
            this.data = {};
            this.validators = {};
            this.errors = {};
            this.options = {
                debounceTime: 250,
                debounceEnabled: false,
                validateOnChange: true,
                validateFullForm: true,
                watchProgrammaticChanges: true,
                pollingInterval: 500,
                validateOnInit: false,
                ...options
            };

            this.timeouts = {};
            this.pathCache = new Map();
            this.lastFieldValues = new Map();
            this.initialized = false;

            this.init();
        }

        init() {
            this.updateFormData();
            this.saveCurrentFieldValues();

            this.form.addEventListener('input', this.handleFieldChange.bind(this));
            this.form.addEventListener('change', this.handleFieldChange.bind(this));

            if (window.MutationObserver && this.options.watchProgrammaticChanges) {
                this.domObserver = new MutationObserver(mutations => {
                    let shouldUpdateForm = false;

                    mutations.forEach(mutation => {
                        if (mutation.addedNodes.length) {
                            mutation.addedNodes.forEach(node => {
                                if (node.nodeType === 1 &&
                                    (node.tagName === 'INPUT' ||
                                     node.tagName === 'SELECT' ||
                                     node.tagName === 'TEXTAREA')) {
                                    shouldUpdateForm = true;
                                }
                            });
                        }
                    });

                    if (shouldUpdateForm) {
                        this.updateFormData();
                        this.setupValueObservers();
                    }
                });

                this.domObserver.observe(this.form, {
                    childList: true,
                    subtree: true
                });

                this.setupValueObservers();

                if (this.options.pollingInterval > 0) {
                    this.pollingInterval = setInterval(() => {
                        this.checkFieldValueChanges();
                    }, this.options.pollingInterval);
                }
            }

            if (this.options.validateOnInit) {
                this.validateAll();
            }

            this.initialized = true;
        }

        setupValueObservers() {
            if (!this.valueObserver) {
                this.valueObserver = new MutationObserver(mutations => {
                    let processedElements = new Set();

                    mutations.forEach(mutation => {
                        if (mutation.type === 'attributes' &&
                            (mutation.attributeName === 'value' ||
                             mutation.attributeName === 'checked' ||
                             mutation.attributeName === 'selected')) {

                            const target = mutation.target;
                            if (target.name && !processedElements.has(target.name)) {
                                processedElements.add(target.name);
                                this.updateField(target);
                            }
                        }
                    });
                });
            }

            Array.from(this.form.elements).forEach(element => {
                if (element.name) {
                    this.valueObserver.observe(element, {
                        attributes: true,
                        attributeFilter: ['value', 'checked', 'selected']
                    });

                    // const parent = element.parentNode;
                    // if (parent) {
                    //     this.valueObserver.observe(parent, {
                    //         attributes: true,
                    //         childList: true,
                    //         subtree: true
                    //     });
                    // }
                }
            });

            this.saveCurrentFieldValues();
        }

        saveCurrentFieldValues() {
            Array.from(this.form.elements).forEach(element => {
                if (element.name) {
                    let value;
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        value = element.checked ? element.value : null;
                    } else {
                        value = element.value;
                    }
                    this.lastFieldValues.set(element.name, value);
                }
            });
        }

        checkFieldValueChanges() {
            let hasChanges = false;
            let changedFields = [];

            Array.from(this.form.elements).forEach(element => {
                if (!element.name) return;

                let currentValue;
                if (element.type === 'checkbox' || element.type === 'radio') {
                    currentValue = element.checked ? element.value : null;
                } else {
                    currentValue = element.value;
                }

                const previousValue = this.lastFieldValues.get(element.name);

                if (currentValue !== previousValue) {
                    changedFields.push(element.name);
                    this.updateField(element);
                    this.lastFieldValues.set(element.name, currentValue);
                    hasChanges = true;
                }
            });

            if (hasChanges) {
                this.updateFormData();

                if (this.options.onChange) {
                    this.options.onChange('polling', null, this.data);
                }
            }
        }

        handleFieldChange(event) {
            const field = event.target;
            if (!field.name) return;

            if (this.options.debounceEnabled && this.options.debounceTime > 0 && (
                field.type === 'text' ||
                field.type === 'textarea' ||
                field.type === 'select-one' ||
                field.type === 'email' ||
                field.type === 'password' ||
                field.type === 'number')) {

                clearTimeout(this.timeouts[field.name]);
                this.timeouts[field.name] = setTimeout(() => {
                    this.updateField(field);
                }, this.options.debounceTime);
            } else {
                this.updateField(field);
            }

            if (field.type === 'checkbox' || field.type === 'radio') {
                this.lastFieldValues.set(field.name, field.checked ? field.value : null);
            } else {
                this.lastFieldValues.set(field.name, field.value);
            }
        }

        updateField(field) {
            const now = Date.now();
            const lastUpdate = this.lastUpdateTimes?.get(field.name) || 0;
            const throttleTime = 50;

            if (now - lastUpdate < throttleTime) {
                return;
            }

            if (!this.lastUpdateTimes) {
                this.lastUpdateTimes = new Map();
            }
            this.lastUpdateTimes.set(field.name, now);

            const oldValue = this.getFieldValue(field.name);
            const hadValidator = field.name in this.validators;

            this.updateFormData();

            const newValue = this.getFieldValue(field.name);
            const valueChanged = JSON.stringify(oldValue) !== JSON.stringify(newValue);

            if (this.validators[field.name]) {
                if (valueChanged || !this.initialized || !hadValidator) {
                    if (this.options.validateFullForm) {
                        this.validateAll();
                    } else {
                        this.validateField(field.name);
                    }
                }
            }

            if (valueChanged && this.options.onChange) {
                this.options.onChange(field.name, newValue, this.data);
            }
        }

        updateFieldByName(fieldName) {
            const field = this.form.elements[fieldName] ||
                         this.form.querySelector(`[name="${fieldName}"]`);

            if (field) {
                this.updateField(field);
            } else {
                console.warn(`Field with name "${fieldName}" not found`);
            }
        }

        updateAllFields() {
            this.updateFormData();

            for (const fieldName in this.validators) {
                this.validateField(fieldName);
            }

            if (this.options.onChange) {
                this.options.onChange('all', null, this.data);
            }
        }

        updateFormData() {
            const rawData = {};

            Array.from(this.form.elements).forEach(element => {
                if (!element.name || element.disabled) return;

                if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) return;

                const value = element.type === 'checkbox' && element.checked ? (element.value || true) : element.value;

                if (element.name.endsWith('[]')) {
                    const key = element.name.slice(0, -2);
                    rawData[key] = rawData[key] || [];
                    rawData[key].push(value);
                } else {
                    rawData[element.name] = value;
                }
            });

            this.data = this.structureData(rawData);
            return this.data;
        }

        structureData(rawData) {
            const result = {};

            for (const [key, value] of Object.entries(rawData)) {
                const path = this.getPathFromKey(key);

                let current = result;

                for (let i = 0; i < path.length; i++) {
                    const part = path[i];
                    const isLast = i === path.length - 1;

                    if (isLast) {
                        current[part] = value;
                    } else {
                        const nextPart = path[i + 1];
                        current[part] = current[part] || (isNaN(nextPart) ? {} : []);
                        current = current[part];
                    }
                }
            }

            return result;
        }

        getPathFromKey(key) {
            if (this.pathCache.has(key)) {
                return this.pathCache.get(key);
            }

            const path = key.split(/\]\[|\[|\]/).filter(p => p !== '');
            this.pathCache.set(key, path);
            return path;
        }

        getFieldValue(name) {
            const path = this.getPathFromKey(name);
            let value = this.data;

            for (const part of path) {
                if (value === undefined || value === null) return undefined;
                value = value[part];
            }

            return value;
        }

        setValidator(fieldName, validator) {
            this.validators[fieldName] = validator;

            if (this.initialized && this.options.validateOnInit) {
                this.validateField(fieldName);
            }

            return this;
        }

        validateField(fieldName) {
            const validator = this.validators[fieldName];
            if (!validator) return true;

            const value = this.getFieldValue(fieldName);
            const result = validator(value, this.data);

            if (result === true) {
                delete this.errors[fieldName];

                if (this.options.onValidationSuccess) {
                    this.options.onValidationSuccess(fieldName);
                }

                return true;
            } else {
                this.errors[fieldName] = result;
                if (this.options.onValidationError) {
                    this.options.onValidationError(fieldName, result);
                }
                return false;
            }
        }

        validateAll() {
            if (this.options.onValidationSuccess) {
                for (const fieldName in this.validators) {
                    this.options.onValidationSuccess(fieldName);
                }
            }

            let isValid = true;

            for (const fieldName in this.validators) {
                if (!this.validateField(fieldName)) {
                    isValid = false;
                }
            }

            return isValid;
        }

        getData() {
            return this.data;
        }

        getErrors() {
            return this.errors;
        }

        destroy() {
            if (this.domObserver) {
                this.domObserver.disconnect();
            }

            if (this.valueObserver) {
                this.valueObserver.disconnect();
            }

            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }

            this.form.removeEventListener('input', this.handleFieldChange);
            this.form.removeEventListener('change', this.handleFieldChange);

            for (const timeout of Object.values(this.timeouts)) {
                clearTimeout(timeout);
            }
        }
    }

    window.FormWatcher = FormWatcher;
})(window);
