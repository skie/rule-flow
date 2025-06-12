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

        createValidatorFromRules(fieldName, fieldRules) {
            if (!fieldRules || !Array.isArray(fieldRules)) {
                return null;
            }

            return (value, data) => {
                for (let i = 0; i < fieldRules.length; i++) {
                    const ruleConfig = fieldRules[i];

                    if (!ruleConfig.rule || !ruleConfig.message) {
                        continue;
                    }

                    try {
                        const result = jsonLogic.apply(ruleConfig.rule, data);

                        if (result == false) {
                            return ruleConfig.message || 'Validation failed';
                        }
                    } catch (error) {
                        console.error(`Error evaluating JSON Logic rule for ${fieldName}:`, error);
                        return 'Validation error occurred';
                    }
                }

                return true;
            };
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

    class AttributeBasedJsonLogicValidator {
        constructor() {
            this.rules = {};
            this.messages = {};
            this.ruleLoader = new JsonLogicRuleLoader();
        }

        parseValidationAttributes(form) {
            const elements = form.querySelectorAll('[validation-rule]');
            const extractedRules = {};

            elements.forEach(element => {
                const fieldName = element.name;
                const validationRuleAttr = element.getAttribute('validation-rule');

                if (!fieldName || !validationRuleAttr) {
                    return;
                }

                try {
                    const validationConfig = JSON.parse(validationRuleAttr);

                    if (validationConfig.type === 'json-logic' && validationConfig.rules) {
                        extractedRules[fieldName] = validationConfig.rules;

                        validationConfig.rules.forEach(ruleConfig => {
                            this.addRule(fieldName, ruleConfig.rule, ruleConfig.message);
                        });
                    }
                } catch (error) {
                    console.error(`Error parsing validation rule for field ${fieldName}:`, error);
                }
            });

            return extractedRules;
        }

        parseDataJsonLogicRules(form) {
            const dataJsonLogicRules = this.ruleLoader.parseFormWithDataJsonLogic(form);
            const extractedRules = {};

            Object.keys(dataJsonLogicRules).forEach(fieldName => {
                const fieldRules = dataJsonLogicRules[fieldName];
                extractedRules[fieldName] = fieldRules;

                fieldRules.forEach(ruleConfig => {
                    this.addRule(fieldName, ruleConfig.rule, ruleConfig.message);
                });
            });

            return extractedRules;
        }

        parseAllValidationRules(form) {
            const attributeRules = this.parseValidationAttributes(form);
            const dataJsonLogicRules = this.parseDataJsonLogicRules(form);

            return {
                ...attributeRules,
                ...dataJsonLogicRules
            };
        }

        addRule(fieldName, rule, errorMessage) {
            if (!this.rules[fieldName]) {
                this.rules[fieldName] = [];
                this.messages[fieldName] = [];
            }

            this.rules[fieldName].push(rule);
            this.messages[fieldName].push(errorMessage);
            return this;
        }

        createValidator(fieldName) {
            const rules = this.rules[fieldName];
            const messages = this.messages[fieldName];

            if (!rules || !messages) {
                return null;
            }

            return (value, data) => {
                const errors = [];

                for (let i = 0; i < rules.length; i++) {
                    const rule = rules[i];
                    const message = messages[i];

                    try {
                        const result = jsonLogic.apply(rule, data);

                        if (result == false) {
                            errors.push(message || 'Validation failed');
                        }
                    } catch (error) {
                        console.error(`Error evaluating rule for ${fieldName}:`, error);
                        errors.push('Validation error occurred');
                    }
                }

                return errors.length > 0 ? errors : true;
            };
        }

        getRules() {
            return this.rules;
        }

        getMessages() {
            return this.messages;
        }

        getRuleLoader() {
            return this.ruleLoader;
        }
    }

    class FormWatcherAutoInitializer {
        constructor() {
            this.watchers = new Map();
            this.validators = new Map();
            this.fieldErrors = new Map();
            this.setupJsonLogicOperations();
        }

        setupJsonLogicOperations() {
            if (typeof jsonLogic === 'undefined') {
                console.warn('JSONLogic library not found. FormWatcher auto-initialization skipped.');
                return;
            }

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
        }

        initializeForm(form) {
            if (typeof FormWatcher === 'undefined') {
                console.warn('FormWatcher library not found. Auto-initialization skipped for form:', form);
                return null;
            }

            const formId = form.id || `form-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

            if (this.watchers.has(formId)) {
                return this.watchers.get(formId);
            }

            const validator = new AttributeBasedJsonLogicValidator();
            const extractedRules = validator.parseAllValidationRules(form);

            if (Object.keys(extractedRules).length === 0) {
                return null;
            }

            const watcher = new FormWatcher(form, {
                debounceTime: 300,
                validateOnChange: true,
                validateOnInit: true,
                onValidationError: (fieldName, errors) => {
                    if (Array.isArray(errors)) {
                        errors.forEach(error => {
                            this.displayFieldError(fieldName, error);
                        });
                    } else {
                        this.displayFieldError(fieldName, errors);
                    }
                },
                onValidationSuccess: (fieldName) => {
                    this.clearFieldError(fieldName);
                }
            });

            for (const fieldName in validator.rules) {
                const validatorFunction = validator.createValidator(fieldName);
                if (validatorFunction) {
                    watcher.setValidator(fieldName, validatorFunction);
                }
            }

            this.watchers.set(formId, watcher);
            this.validators.set(formId, validator);

            console.log(`FormWatcher initialized for form: ${formId}`, {
                extractedRules,
                fieldCount: Object.keys(extractedRules).length
            });

            return watcher;
        }

        displayFieldError(fieldName, error) {
            if (!this.fieldErrors.has(fieldName)) {
                this.fieldErrors.set(fieldName, []);
            }

            const fieldErrorList = this.fieldErrors.get(fieldName);
            if (!fieldErrorList.includes(error)) {
                fieldErrorList.push(error);
            }

            let errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (!errorElement) {
                const inputField = document.querySelector(`[name="${fieldName}"]`);
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
                errorElement.innerHTML = fieldErrorList.join('<br/>');
                errorElement.style.display = 'block';
            }
        }

        clearFieldError(fieldName) {
            this.fieldErrors.delete(fieldName);

            const errorElement = document.querySelector(`[data-error="${fieldName}"]`);
            if (errorElement) {
                errorElement.innerHTML = '';
                errorElement.style.display = 'none';
            }
        }

        initializeAllForms() {
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                this.initializeForm(form);
            });

            return this.watchers.size;
        }

        getWatcher(formId) {
            return this.watchers.get(formId);
        }

        getValidator(formId) {
            return this.validators.get(formId);
        }

        getAllWatchers() {
            return Array.from(this.watchers.values());
        }

        validateAllForms() {
            const results = {};

            this.watchers.forEach((watcher, formId) => {
                results[formId] = watcher.validateAll();
            });

            return results;
        }

        getRulesForForm(formId) {
            const validator = this.validators.get(formId);
            return validator ? validator.getRules() : null;
        }

        getAllRules() {
            const allRules = {};

            this.validators.forEach((validator, formId) => {
                allRules[formId] = validator.getRules();
            });

            return allRules;
        }
    }

    class RuleAnalyzer {
        constructor() {
            this.initializer = null;
        }

        setInitializer(initializer) {
            this.initializer = initializer;
        }

        analyzeForm(formElement) {
            if (!formElement) {
                console.error('Form element is required for analysis');
                return null;
            }

            const validator = new AttributeBasedJsonLogicValidator();
            const extractedRules = validator.parseAllValidationRules(formElement);

            return {
                formId: formElement.id || 'unnamed-form',
                fieldCount: Object.keys(extractedRules).length,
                rules: extractedRules,
                fields: Object.keys(extractedRules),
                hasValidation: Object.keys(extractedRules).length > 0,
                hasDataJsonLogic: !!formElement.getAttribute('data-json-logic'),
                hasValidationRuleAttributes: formElement.querySelectorAll('[validation-rule]').length > 0
            };
        }

        analyzeAllForms() {
            const forms = document.querySelectorAll('form');
            const analysis = [];

            forms.forEach(form => {
                const formAnalysis = this.analyzeForm(form);
                if (formAnalysis) {
                    analysis.push(formAnalysis);
                }
            });

            return analysis;
        }

        getActiveWatchers() {
            if (!this.initializer) {
                return [];
            }

            return this.initializer.getAllWatchers().map(watcher => ({
                form: watcher.form,
                fieldCount: Object.keys(watcher.getData()).length,
                errors: watcher.getErrors(),
                isValid: watcher.validateAll()
            }));
        }

        validateRule(rule, data) {
            if (typeof jsonLogic === 'undefined') {
                console.error('JSONLogic library not available');
                return false;
            }

            try {
                return jsonLogic.apply(rule, data);
            } catch (error) {
                console.error('Rule validation error:', error);
                return false;
            }
        }

        testRule(rule, testData) {
            const results = [];

            if (Array.isArray(testData)) {
                testData.forEach((data, index) => {
                    results.push({
                        index,
                        data,
                        result: this.validateRule(rule, data),
                        valid: this.validateRule(rule, data) === true
                    });
                });
            } else {
                results.push({
                    data: testData,
                    result: this.validateRule(rule, testData),
                    valid: this.validateRule(rule, testData) === true
                });
            }

            return results;
        }
    }

    function initializeFormWatcherAuto() {
        if (typeof window === 'undefined') {
            return;
        }

        const initializer = new FormWatcherAutoInitializer();
        const analyzer = new RuleAnalyzer();

        analyzer.setInitializer(initializer);

        window.FormWatcherAuto = {
            initializer,
            analyzer,
            JsonLogicRuleLoader,
            init: () => initializer.initializeAllForms(),
            initForm: (form) => initializer.initializeForm(form),
            validateAll: () => initializer.validateAllForms(),
            getWatcher: (formId) => initializer.getWatcher(formId),
            getRules: (formId) => initializer.getRulesForForm(formId),
            getAllRules: () => initializer.getAllRules(),
            analyze: (form) => analyzer.analyzeForm(form),
            analyzeAll: () => analyzer.analyzeAllForms(),
            testRule: (rule, data) => analyzer.testRule(rule, data),
            loadRulesFromElement: (elementId) => {
                const loader = new JsonLogicRuleLoader();
                return loader.loadRulesFromElement(elementId);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                const count = initializer.initializeAllForms();
                if (count > 0) {
                    console.log(`FormWatcher Auto: Initialized ${count} forms with validation rules`);
                }
            });
        } else {
            const count = initializer.initializeAllForms();
            if (count > 0) {
                console.log(`FormWatcher Auto: Initialized ${count} forms with validation rules`);
            }
        }

        return window.FormWatcherAuto;
    }

console.log('init');
    initializeFormWatcherAuto();

})();
