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
            this.basePatterns = new Map(); // Store base patterns like collection.0.* -> rules
            this.knownFields = new Set();
        }

        parseValidationRules(form) {
            const dataJsonLogicRules = this.ruleLoader.parseFormWithDataJsonLogic(form);

            // Store base patterns for collection fields
            Object.keys(dataJsonLogicRules).forEach(fieldName => {
                this.knownFields.add(fieldName);

                // Extract base pattern for collections: collection.0.name -> collection.*.name
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
            // Convert collection.0.name to collection.*.name
            const match = fieldName.match(/^(.+)\.(\d+)\.(.+)$/);
            if (match) {
                return `${match[1]}.*.${match[3]}`;
            }
            return null;
        }

        findRulesForNewField(newFieldName) {
            // Check if this field matches any stored patterns
            for (const [pattern, patternData] of this.basePatterns) {
                if (this.matchesPattern(newFieldName, pattern)) {
                    return patternData.rules;
                }
            }
            return null;
        }

        matchesPattern(fieldName, pattern) {
            // Convert pattern collection.*.name to regex that matches collection.N.name
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

            // Add operations without checking if they exist - safer approach
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
            } catch (error) {
                console.warn('Error adding JSONLogic operations:', error);
            }
        }

        enhanceFormWatcher(watcher) {
            if (!watcher || !watcher.form) {
                console.warn('Invalid FormWatcher provided');
                return null;
            }

            const formId = watcher.form.id || `form-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

            if (this.enhancedWatchers.has(formId)) {
                // Already enhanced
                return this.enhancedWatchers.get(formId);
            }

            const validationManager = new DynamicValidationManager();
            const extractedRules = validationManager.parseValidationRules(watcher.form);

            // Add existing rules to the watcher
            Object.keys(extractedRules).forEach(fieldName => {
                const rules = extractedRules[fieldName];
                if (rules && Array.isArray(rules)) {
                    const validator = this.createValidator(fieldName, rules);
                    if (validator) {
                        watcher.setValidator(fieldName, validator);
                    }
                }
            });

            const enhancement = {
                watcher,
                validationManager,
                formId
            };

            this.enhancedWatchers.set(formId, enhancement);

            // Set up mutation observer with debouncing
            this.setupMutationObserver(watcher.form, formId);

            console.log(`Enhanced FormWatcher for dynamic fields: ${formId}`, {
                basePatterns: Array.from(validationManager.getBasePatterns().keys()),
                existingRules: Object.keys(extractedRules).length
            });

            return enhancement;
        }

        setupMutationObserver(form, formId) {
            const observer = new MutationObserver((mutations) => {
                // Debounce mutation handling
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
                // Update FormWatcher's internal data after new fields are added
                watcher.updateFormData();
                console.log(`Detected and processed new fields in form ${formId}`);
            }
        }

        handleNewField(input, enhancement) {
            const { watcher, validationManager } = enhancement;
            const fieldName = input.name;

            // Convert HTML field name to internal field name (collection[0][name] -> collection.0.name)
            const internalFieldName = this.convertHtmlFieldNameToInternal(fieldName);

            validationManager.addKnownField(internalFieldName);

            // Look for matching rules based on pattern
            const matchingRules = validationManager.findRulesForNewField(internalFieldName);

            if (matchingRules) {
                const validator = this.createValidator(internalFieldName, matchingRules);
                if (validator) {
                    watcher.setValidator(internalFieldName, validator);
                    console.log(`Added validation for new field: ${fieldName} (internal: ${internalFieldName})`);
                }
            }

            // Also check for validation-rule attributes on the field itself
            const validationRuleAttr = input.getAttribute('validation-rule');
            if (validationRuleAttr) {
                try {
                    const validationConfig = JSON.parse(validationRuleAttr);
                    if (validationConfig.type === 'json-logic' && validationConfig.rules) {
                        const validator = this.createValidator(internalFieldName, validationConfig.rules);
                        if (validator) {
                            watcher.setValidator(internalFieldName, validator);
                            console.log(`Added validation from attribute for field: ${fieldName}`);
                        }
                    }
                } catch (error) {
                    console.error(`Error parsing validation rule for field ${fieldName}:`, error);
                }
            }
        }

        convertHtmlFieldNameToInternal(htmlName) {
            // Convert collection[0][name] to collection.0.name
            // This mimics what FormWatcher.getPathFromKey() does
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
                // Clean up specific form
                const observer = this.mutationObservers.get(formId);
                if (observer) {
                    observer.disconnect();
                    this.mutationObservers.delete(formId);
                }
                clearTimeout(this.debounceTimeouts.get(formId));
                this.debounceTimeouts.delete(formId);
                this.enhancedWatchers.delete(formId);
            } else {
                // Clean up all
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

    // Enhanced auto-initialization that works with existing FormWatcher
    class FormWatcherAutoWithDynamic {
        constructor() {
            this.enhancer = new DynamicFormWatcherEnhancer();
            this.autoWatchers = new Map();
        }

        initializeForm(form) {
            if (typeof FormWatcher === 'undefined') {
                console.warn('FormWatcher library not found.');
                return null;
            }

            const formId = form.id || `form-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

            // Check if form has validation rules
            const hasDataJsonLogic = !!form.getAttribute('data-json-logic');
            const hasValidationAttributes = form.querySelectorAll('[validation-rule]').length > 0;

            if (!hasDataJsonLogic && !hasValidationAttributes) {
                return null;
            }

            // Create standard FormWatcher
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

            // Enhance it for dynamic fields
            const enhancement = this.enhancer.enhanceFormWatcher(watcher);

            if (enhancement) {
                this.autoWatchers.set(formId, enhancement);
                console.log(`Auto-initialized FormWatcher with dynamic support: ${formId}`);
            }

            return watcher;
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
            const errorMessages = Array.isArray(errors) ? errors : [errors];

            let errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (!errorElement) {
                const inputField = document.querySelector(`[name="${fieldName}"], [name="${fieldName.replace(/\./g, '][')}"]`);
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
                errorElement.innerHTML = errorMessages.join('<br/>');
                errorElement.style.display = 'block';
            }
        }

        clearFieldError(fieldName) {
            const errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (errorElement) {
                errorElement.innerHTML = '';
                errorElement.style.display = 'none';
            }
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

    function initializeFormWatcherWithDynamic() {
        if (typeof window === 'undefined') {
            return;
        }

        const autoInitializer = new FormWatcherAutoWithDynamic();

        window.FormWatcherDynamic = {
            autoInitializer,
            enhancer: autoInitializer.enhancer,
            init: () => autoInitializer.initializeAllForms(),
            initForm: (form) => autoInitializer.initializeForm(form),
            validateAll: () => autoInitializer.validateAllForms(),
            getWatcher: (formId) => autoInitializer.getWatcher(formId),
            getAllWatchers: () => autoInitializer.getAllWatchers(),
            enhance: (watcher) => autoInitializer.enhancer.enhanceFormWatcher(watcher),
            destroy: () => autoInitializer.destroy()
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

    console.log('Initializing FormWatcher with Dynamic Support');
    initializeFormWatcherWithDynamic();

})();
