(function() {
    'use strict';
    class JsonLogicRuleLoader {
        constructor() {
            this.loadedRules = new Map();
            this.cache = new Map();
        }
        loadRulesFromElement(elementId) {
            if (this.cache.has(elementId)) {
                return this.cache.get(elementId);
            }
            const element = document.getElementById(elementId);
            if (!element) {
                console.error(`JSON Logic rules element not found: ${elementId}`);
                return null;
            }
            try {
                const rulesData = JSON.parse(element.textContent || element.innerHTML);
                this.cache.set(elementId, rulesData);
                return rulesData;
            } catch (error) {
                console.error(`Error parsing JSON Logic rules from element ${elementId}:`, error);
                return null;
            }
        }
        parseFormWithDataJsonLogic(form) {
            const dataJsonLogicAttr = form.getAttribute('data-json-logic');
            if (!dataJsonLogicAttr) {
                return {};
            }
            const elementId = dataJsonLogicAttr.startsWith('#') ? dataJsonLogicAttr.substring(1) : dataJsonLogicAttr;
            const rulesData = this.loadRulesFromElement(elementId);
            if (!rulesData) {
                return {};
            }
            const extractedRules = {};
            Object.keys(rulesData).forEach(fieldName => {
                const fieldRules = rulesData[fieldName];
                if (fieldRules && fieldRules.rules && Array.isArray(fieldRules.rules)) {
                    extractedRules[fieldName] = fieldRules.rules;
                }
            });
            return extractedRules;
        }
        getAllLoadedRules() {
            return Object.fromEntries(this.loadedRules);
        }
        clearCache() {
            this.cache.clear();
        }
        reloadRules(elementId) {
            this.cache.delete(elementId);
            return this.loadRulesFromElement(elementId);
        }
    }
    class DynamicValidationManager {
        constructor() {
            this.ruleLoader = new JsonLogicRuleLoader();
            this.basePatterns = new Map();
            this.knownFields = new Set();
        }
        parseValidationRules(form) {
            const dataJsonLogicRules = this.ruleLoader.parseFormWithDataJsonLogic(form);
            Object.keys(dataJsonLogicRules).forEach(fieldName => {
                this.knownFields.add(fieldName);
                const basePattern = this.extractBasePattern(fieldName);
                if (basePattern) {
                    this.basePatterns.set(basePattern, {
                        originalField: fieldName,
                        rules: dataJsonLogicRules[fieldName]
                    });
                }
            });
            return dataJsonLogicRules;
        }
        extractBasePattern(fieldName) {
            const match = fieldName.match(/^(.+)\.(\d+)\.(.+)$/);
            if (match) {
                return `${match[1]}.*.${match[3]}`;
            }
            return null;
        }
        findRulesForNewField(newFieldName) {
            for (const [pattern, patternData] of this.basePatterns) {
                if (this.matchesPattern(newFieldName, pattern)) {
                    return patternData.rules;
                }
            }
            return null;
        }
        matchesPattern(fieldName, pattern) {
            const patternRegex = pattern.replace(/\*/g, '\\d+');
            return new RegExp(`^${patternRegex}$`).test(fieldName);
        }
        getBasePatterns() {
            return this.basePatterns;
        }
        addKnownField(fieldName) {
            this.knownFields.add(fieldName);
        }
        isKnownField(fieldName) {
            return this.knownFields.has(fieldName);
        }
    }
    class DynamicFormWatcherEnhancer {
        constructor() {
            this.enhancedWatchers = new Map();
            this.mutationObservers = new Map();
            this.debounceTimeouts = new Map();
            this.setupJsonLogicOperations();
        }
        setupJsonLogicOperations() {
            if (typeof jsonLogic === 'undefined') {
                console.warn('JSONLogic library not found.');
                return;
            }
            try {
                jsonLogic.add_operation("debug", function(value) {
                    console.log("JSONLogic Debug:", value);
                    return value;
                });
                jsonLogic.add_operation("length", function(val) {
                    if (!val) return 0;
                    return val.length;
                });
                jsonLogic.add_operation("match", function(val, pattern) {
                    if (!val) return false;
                    return new RegExp(pattern).test(val);
                });

                if (window.FormWatcherCustomOperations) {
                    const customOps = window.FormWatcherCustomOperations;
                    Object.keys(customOps).forEach(opName => {
                        if (typeof customOps[opName] === 'function') {
                            jsonLogic.add_operation(opName, customOps[opName]);
                            console.log(`FormWatcher Dynamic: Registered custom operation "${opName}"`);
                        }
                    });
                }
            } catch (error) {
                console.warn('Error adding JSONLogic operations:', error);
            }
        }

        /**
         * Register a custom JSON Logic operation
         *
         * @param {string} operationName - Name of the operation
         * @param {function} operationFunction - Function to execute for this operation
         * @return {boolean} - True if successfully registered
         */
        registerCustomOperation(operationName, operationFunction) {
            if (typeof jsonLogic === 'undefined') {
                console.warn('JSONLogic library not found. Cannot register custom operation.');
                return false;
            }

            if (typeof operationFunction !== 'function') {
                console.error(`Custom operation "${operationName}" must be a function`);
                return false;
            }

            try {
                jsonLogic.add_operation(operationName, operationFunction);
                console.log(`FormWatcher Dynamic: Registered custom operation "${operationName}"`);
                return true;
            } catch (error) {
                console.error(`Error registering custom operation "${operationName}":`, error);
                return false;
            }
        }
        enhanceFormWatcher(watcher) {
            if (!watcher || !watcher.form) {
                console.warn('Invalid FormWatcher provided');
                return null;
            }
            const formId = watcher.form.id || `form-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            if (this.enhancedWatchers.has(formId)) {
                return this.enhancedWatchers.get(formId);
            }
            const validationManager = new DynamicValidationManager();
            const extractedRules = validationManager.parseValidationRules(watcher.form);
            this.registerValidatorsForExistingFields(watcher, extractedRules);
            const enhancement = {
                watcher,
                validationManager,
                formId
            };
            this.enhancedWatchers.set(formId, enhancement);
            this.setupMutationObserver(watcher.form, formId);
            console.log(`Enhanced FormWatcher for dynamic fields: ${formId}`, {
                basePatterns: Array.from(validationManager.getBasePatterns().keys()),
                existingRules: Object.keys(extractedRules).length
            });
            return enhancement;
        }
        registerValidatorsForExistingFields(watcher, extractedRules) {
            const inputs = watcher.form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!input.name) return;
                const htmlFieldName = input.name;
                const internalFieldName = this.convertHtmlFieldNameToInternal(input.name);
                if (extractedRules[internalFieldName]) {
                    const validator = this.createValidator(internalFieldName, extractedRules[internalFieldName]);
                    if (validator) {
                        watcher.setValidator(htmlFieldName, validator);
                    }
                    return;
                }
                const validationRuleAttr = input.getAttribute('validation-rule');
                if (validationRuleAttr) {
                    try {
                        const validationConfig = JSON.parse(validationRuleAttr);
                        if (validationConfig.type === 'json-logic' && validationConfig.rules) {
                            const validator = this.createValidator(internalFieldName, validationConfig.rules);
                            if (validator) {
                                watcher.setValidator(htmlFieldName, validator);
                            }
                        }
                    } catch (error) {
                        console.error(`Error parsing validation rule for field ${internalFieldName}:`, error);
                    }
                }
            });
        }
        setupMutationObserver(form, formId) {
            const observer = new MutationObserver((mutations) => {
                clearTimeout(this.debounceTimeouts.get(formId));
                this.debounceTimeouts.set(formId, setTimeout(() => {
                    this.handleMutations(mutations, formId);
                }, 300));
            });
            observer.observe(form, {
                childList: true,
                subtree: true
            });
            this.mutationObservers.set(formId, observer);
        }
        handleMutations(mutations, formId) {
            const enhancement = this.enhancedWatchers.get(formId);
            if (!enhancement) return;
            const { watcher, validationManager } = enhancement;
            let newFieldsAdded = false;
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const inputs = node.querySelectorAll ?
                                node.querySelectorAll('input, select, textarea') :
                                (node.matches && node.matches('input, select, textarea') ? [node] : []);
                            inputs.forEach(input => {
                                if (input.name && !validationManager.isKnownField(input.name)) {
                                    this.handleNewField(input, enhancement);
                                    newFieldsAdded = true;
                                }
                            });
                        }
                    });
                }
            });
            if (newFieldsAdded) {
                watcher.updateFormData();
                console.log(`Detected and processed new fields in form ${formId}`);
            }
        }
        handleNewField(input, enhancement) {
            const { watcher, validationManager } = enhancement;
            const htmlFieldName = input.name;
            const internalFieldName = this.convertHtmlFieldNameToInternal(input.name);
            validationManager.addKnownField(internalFieldName);
            const matchingRules = validationManager.findRulesForNewField(internalFieldName);
            if (matchingRules) {
                const validator = this.createValidator(internalFieldName, matchingRules);
                if (validator) {
                    watcher.setValidator(htmlFieldName, validator);
                }
            }
            const validationRuleAttr = input.getAttribute('validation-rule');
            if (validationRuleAttr) {
                try {
                    const validationConfig = JSON.parse(validationRuleAttr);
                    if (validationConfig.type === 'json-logic' && validationConfig.rules) {
                        const validator = this.createValidator(internalFieldName, validationConfig.rules);
                        if (validator) {
                            watcher.setValidator(htmlFieldName, validator);
                        }
                    }
                } catch (error) {
                    console.error(`Error parsing validation rule for field ${htmlFieldName}:`, error);
                }
            }
        }
        convertHtmlFieldNameToInternal(htmlName) {
            return htmlName.replace(/\]\[/g, '.').replace(/\[/g, '.').replace(/\]/g, '');
        }
        createValidator(fieldName, rules) {
            if (!rules || !Array.isArray(rules)) {
                return null;
            }
            return (value, data) => {
                const errors = [];
                for (let i = 0; i < rules.length; i++) {
                    const ruleConfig = rules[i];
                    if (!ruleConfig.rule || !ruleConfig.message) {
                        continue;
                    }
                    try {
                        const result = jsonLogic.apply(ruleConfig.rule, data);
                        if (result == false) {
                            errors.push(ruleConfig.message || 'Validation failed');
                        }
                    } catch (error) {
                        console.error(`Error evaluating rule for ${fieldName}:`, error);
                        errors.push('Validation error occurred');
                    }
                }
                return errors.length > 0 ? errors : true;
            };
        }
        getEnhancement(formId) {
            return this.enhancedWatchers.get(formId);
        }
        getAllEnhancements() {
            return Array.from(this.enhancedWatchers.values());
        }
        destroy(formId) {
            if (formId) {
                const observer = this.mutationObservers.get(formId);
                if (observer) {
                    observer.disconnect();
                    this.mutationObservers.delete(formId);
                }
                clearTimeout(this.debounceTimeouts.get(formId));
                this.debounceTimeouts.delete(formId);
                this.enhancedWatchers.delete(formId);
            } else {
                this.mutationObservers.forEach((observer) => {
                    observer.disconnect();
                });
                this.mutationObservers.clear();
                this.debounceTimeouts.forEach((timeout) => {
                    clearTimeout(timeout);
                });
                this.debounceTimeouts.clear();
                this.enhancedWatchers.clear();
            }
        }
    }
    class FormWatcherAutoWithDynamic {
        constructor(options = {}) {
            this.enhancer = new DynamicFormWatcherEnhancer();
            this.autoWatchers = new Map();
            this.options = {
                showValidationSummary: false,
                summaryAutoRemoveTimeout: 0,
                focusFirstErrorField: true,
                ...options
            };
        }
        initializeForm(form) {
            if (typeof FormWatcher === 'undefined') {
                console.warn('FormWatcher library not found.');
                return null;
            }
            const formId = form.id || `form-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            const hasDataJsonLogic = !!form.getAttribute('data-json-logic');
            const hasValidationAttributes = form.querySelectorAll('[validation-rule]').length > 0;
            if (!hasDataJsonLogic && !hasValidationAttributes) {
                return null;
            }
            const watcher = new FormWatcher(form, {
                debounceTime: 300,
                validateOnChange: true,
                validateOnInit: true,
                onValidationError: (fieldName, errors) => {
                    this.displayFieldError(fieldName, errors);
                },
                onValidationSuccess: (fieldName) => {
                    this.clearFieldError(fieldName);
                }
            });
            const enhancement = this.enhancer.enhanceFormWatcher(watcher);
            if (enhancement) {
                this.autoWatchers.set(formId, enhancement);
                this.setupFormSubmissionPrevention(form, watcher, formId);
                console.log(`Auto-initialized FormWatcher with dynamic support: ${formId}`);
            }
            return watcher;
        }
        setupFormSubmissionPrevention(form, watcher, formId) {
            const submitHandler = (event) => {
                const isValid = watcher.validateAll();
                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                    console.log(`Form submission prevented due to validation errors in form: ${formId}`);
                    if (this.options.showValidationSummary) {
                        this.showValidationSummary(form, watcher.getErrors());
                    }
                    if (this.options.focusFirstErrorField) {
                        this.focusFirstErrorField(form, watcher.getErrors());
                    }
                    return false;
                }
                console.log(`Form validation passed, allowing submission: ${formId}`);
                return true;
            };
            form.addEventListener('submit', submitHandler);
            if (!form._formWatcherSubmitHandler) {
                form._formWatcherSubmitHandler = submitHandler;
            }
        }
        showValidationSummary(form, errors) {
            const errorCount = Object.keys(errors).length;
            if (errorCount === 0) return;
            const existingSummary = form.querySelector('.form-validation-summary');
            if (existingSummary) {
                existingSummary.remove();
            }
            const summary = document.createElement('div');
            summary.className = 'form-validation-summary';
            summary.style.cssText = `
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                padding: 12px;
                margin-bottom: 15px;
                font-size: 14px;
            `;
            const title = document.createElement('strong');
            title.textContent = `Please fix ${errorCount} validation error${errorCount > 1 ? 's' : ''} before submitting:`;
            summary.appendChild(title);
            const errorList = document.createElement('ul');
            errorList.style.cssText = 'margin: 8px 0 0 0; padding-left: 20px;';
            Object.entries(errors).forEach(([fieldName, fieldErrors]) => {
                const fieldErrorArray = Array.isArray(fieldErrors) ? fieldErrors : [fieldErrors];
                fieldErrorArray.forEach(error => {
                    const listItem = document.createElement('li');
                    listItem.textContent = `${fieldName}: ${error}`;
                    errorList.appendChild(listItem);
                });
            });
            summary.appendChild(errorList);
            form.insertBefore(summary, form.firstChild);
            if (this.options.summaryAutoRemoveTimeout > 0) {
                setTimeout(() => {
                    if (summary.parentNode) {
                        summary.remove();
                    }
                }, this.options.summaryAutoRemoveTimeout);
            }
        }
        focusFirstErrorField(form, errors) {
            const firstErrorField = Object.keys(errors)[0];
            console.log(`Focusing on first error field: ${firstErrorField}`);
            if (firstErrorField) {
                const fieldSelectors = [
                    `[name="${firstErrorField}"]`,
                    `[name="${firstErrorField.replace(/\./g, '][')}"]`
                ];
                let inputField = null;
                for (const selector of fieldSelectors) {
                    inputField = form.querySelector(selector);
                    if (inputField) break;
                }
                if (inputField) {
                    inputField.focus();
                    inputField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
        initializeAllForms() {
            const forms = document.querySelectorAll('form');
            let count = 0;
            forms.forEach(form => {
                if (this.initializeForm(form)) {
                    count++;
                }
            });
            return count;
        }
        displayFieldError(fieldName, errors) {
            if (fieldName.includes('*')) {
                this.displayPatternFieldErrors(fieldName, errors);
                return;
            }
            const errorArray = Array.isArray(errors) ? errors : [errors];
            let errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (!errorElement) {
                const htmlFieldName = this.convertInternalFieldNameToHtml(fieldName);
                const inputField = document.querySelector(`[name="${htmlFieldName}"]`);
                if (inputField) {
                    const parentDiv = inputField.parentElement;
                    if (parentDiv) {
                        errorElement = document.createElement('div');
                        errorElement.setAttribute('data-error', fieldName);
                        errorElement.classList.add('error-message', 'text-danger');
                        errorElement.style.marginBottom = '5px';
                        errorElement.style.marginTop = '5px';
                        parentDiv.appendChild(errorElement);
                    }
                }
            }
            if (errorElement) {
                errorElement.innerHTML = errorArray.join('<br/>');
                errorElement.style.display = 'block';
            }
        }
        displayPatternFieldErrors(patternFieldName, errors) {
            const actualFields = this.findFieldsMatchingPattern(patternFieldName);
            actualFields.forEach(actualFieldName => {
                this.displayFieldError(actualFieldName, errors);
            });
        }
        findFieldsMatchingPattern(patternFieldName) {
            const matchingFields = [];
            const currentWatcher = Array.from(this.enhancedWatchers.values()).find(enhancement =>
                enhancement.validationManager.getBasePatterns().has(patternFieldName)
            );
            if (currentWatcher) {
                const form = currentWatcher.watcher.form;
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        const internalFieldName = this.convertHtmlFieldNameToInternal(input.name);
                        if (currentWatcher.validationManager.matchesPattern(internalFieldName, patternFieldName)) {
                            matchingFields.push(internalFieldName);
                        }
                    }
                });
            }
            return matchingFields;
        }
        convertInternalFieldNameToHtml(internalFieldName) {
            if (!internalFieldName.includes('.')) {
                return internalFieldName;
            }
            const parts = internalFieldName.split('.');
            let htmlFieldName = parts[0];
            for (let i = 1; i < parts.length; i++) {
                htmlFieldName += `[${parts[i]}]`;
            }
            return htmlFieldName;
        }
        clearFieldError(fieldName) {
            if (fieldName.includes('*')) {
                this.clearPatternFieldErrors(fieldName);
                return;
            }
            const errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (errorElement) {
                errorElement.innerHTML = '';
                errorElement.style.display = 'none';
            }
        }
        clearPatternFieldErrors(patternFieldName) {
            const actualFields = this.findFieldsMatchingPattern(patternFieldName);
            actualFields.forEach(actualFieldName => {
                this.clearFieldError(actualFieldName);
            });
        }
        getWatcher(formId) {
            const enhancement = this.autoWatchers.get(formId);
            return enhancement ? enhancement.watcher : null;
        }
        getAllWatchers() {
            return Array.from(this.autoWatchers.values()).map(e => e.watcher);
        }
        validateAllForms() {
            const results = {};
            this.autoWatchers.forEach((enhancement, formId) => {
                results[formId] = enhancement.watcher.validateAll();
            });
            return results;
        }
        destroy() {
            this.enhancer.destroy();
            this.autoWatchers.clear();
        }
    }
    function initializeFormWatcherWithDynamic(options = {}) {
        if (typeof window === 'undefined') {
            return;
        }
        const autoInitializer = new FormWatcherAutoWithDynamic(options);
        window.FormWatcherDynamic = {
            autoInitializer,
            enhancer: autoInitializer.enhancer,
            init: () => autoInitializer.initializeAllForms(),
            initForm: (form) => autoInitializer.initializeForm(form),
            validateAll: () => autoInitializer.validateAllForms(),
            getWatcher: (formId) => autoInitializer.getWatcher(formId),
            getAllWatchers: () => autoInitializer.getAllWatchers(),
            enhance: (watcher) => autoInitializer.enhancer.enhanceFormWatcher(watcher),
            destroy: () => autoInitializer.destroy(),
            configure: (newOptions) => {
                Object.assign(autoInitializer.options, newOptions);
                return window.FormWatcherDynamic;
            },
            getOptions: () => ({ ...autoInitializer.options }),
            registerCustomFunction: (name, func) => autoInitializer.enhancer.registerCustomOperation(name, func)
        };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                const count = autoInitializer.initializeAllForms();
                if (count > 0) {
                    console.log(`FormWatcher Dynamic: Initialized ${count} forms with dynamic support`);
                }
            });
        } else {
            const count = autoInitializer.initializeAllForms();
            if (count > 0) {
                console.log(`FormWatcher Dynamic: Initialized ${count} forms with dynamic support`);
            }
        }
        return window.FormWatcherDynamic;
    }
    initializeFormWatcherWithDynamic();
})();
