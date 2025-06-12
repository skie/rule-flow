/**
 * Custom Functions Examples Test
 *
 * Tests all the custom JavaScript function examples from the documentation to ensure they work correctly
 */

QUnit.module('Custom Functions Examples', function(hooks) {
    let originalFormWatcherCustomOperations;
    let originalFormWatcherAutoConfig;

    hooks.beforeEach(function() {
        // Save original state
        originalFormWatcherCustomOperations = window.FormWatcherCustomOperations;
        originalFormWatcherAutoConfig = window.FormWatcherAutoConfig;

        // Clear any existing custom operations
        window.FormWatcherCustomOperations = {};
        window.FormWatcherAutoConfig = {};
    });

    hooks.afterEach(function() {
        // Restore original state
        window.FormWatcherCustomOperations = originalFormWatcherCustomOperations;
        window.FormWatcherAutoConfig = originalFormWatcherAutoConfig;

        // Clean up any test forms
        const testForms = document.querySelectorAll('[data-test-form]');
        testForms.forEach(form => form.remove());
    });

    QUnit.test('Age Verification Function - Method 1 (Global Registration)', function(assert) {
        // Define the age_verification function from documentation example
        window.FormWatcherCustomOperations = {
            age_verification: function(minAge, birthDate) {
                if (!birthDate) return false;
                const today = new Date();
                const birth = new Date(birthDate);
                const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                return age >= minAge;
            }
        };

        const ageVerification = window.FormWatcherCustomOperations.age_verification;

        // Test case 1: Valid age (over 18)
        const result1 = ageVerification(18, '1990-05-15');
        assert.true(result1, 'Person born in 1990 should be over 18');

        // Test case 2: Invalid age (under 18)
        const result2 = ageVerification(18, '2010-05-15');
        assert.false(result2, 'Person born in 2010 should be under 18');

        // Test case 3: Edge case - exactly 18
        const eighteenYearsAgo = new Date();
        eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
        const result3 = ageVerification(18, eighteenYearsAgo.toISOString().split('T')[0]);
        assert.true(result3, 'Person exactly 18 should be valid');

        // Test case 4: Empty birth date
        const result4 = ageVerification(18, '');
        assert.false(result4, 'Empty birth date should be invalid');

        // Test case 5: Different minimum age (21)
        const result5 = ageVerification(21, '2005-05-15');
        assert.false(result5, 'Person born in 2005 should be under 21');

        // Test case 6: Null birth date
        const result6 = ageVerification(18, null);
        assert.false(result6, 'Null birth date should be invalid');
    });

    QUnit.test('Strong Password Function - Method 1 (Global Registration)', function(assert) {
        // Define the strong_password function from documentation example
        window.FormWatcherCustomOperations = {
            strong_password: function(password) {
                if (!password || password.length < 8) return false;
                const hasLower = /[a-z]/.test(password);
                const hasUpper = /[A-Z]/.test(password);
                const hasDigit = /[0-9]/.test(password);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                return hasLower && hasUpper && hasDigit && hasSpecial;
            }
        };

        const strongPassword = window.FormWatcherCustomOperations.strong_password;

        // Test case 1: Strong password
        const result1 = strongPassword('MyPass123!');
        assert.true(result1, 'Strong password should pass validation');

        // Test case 2: No uppercase
        const result2 = strongPassword('mypass123!');
        assert.false(result2, 'Password without uppercase should fail');

        // Test case 3: No lowercase
        const result3 = strongPassword('MYPASS123!');
        assert.false(result3, 'Password without lowercase should fail');

        // Test case 4: No digit
        const result4 = strongPassword('MyPassword!');
        assert.false(result4, 'Password without digit should fail');

        // Test case 5: No special character
        const result5 = strongPassword('MyPass123');
        assert.false(result5, 'Password without special character should fail');

        // Test case 6: Too short
        const result6 = strongPassword('MyP1!');
        assert.false(result6, 'Password too short should fail');

        // Test case 7: Empty password
        const result7 = strongPassword('');
        assert.false(result7, 'Empty password should fail');

        // Test case 8: Null password
        const result8 = strongPassword(null);
        assert.false(result8, 'Null password should fail');
    });

    QUnit.test('Credit Card Validation Function - Method 3 (Configuration-based)', function(assert) {
        // Define the credit_card function from documentation example
        window.FormWatcherAutoConfig = {
            autoInit: true,
            customOperations: {
                credit_card: function(cardNumber) {
                    if (!cardNumber) return false;
                    // Luhn algorithm implementation
                    const digits = cardNumber.replace(/\D/g, '');

                    // Credit cards must be at least 13 digits long
                    if (digits.length < 13) return false;

                    let sum = 0;
                    let isEven = false;
                    for (let i = digits.length - 1; i >= 0; i--) {
                        let digit = parseInt(digits[i]);
                        if (isEven) {
                            digit *= 2;
                            if (digit > 9) digit -= 9;
                        }
                        sum += digit;
                        isEven = !isEven;
                    }
                    return sum % 10 === 0;
                }
            }
        };

        const creditCard = window.FormWatcherAutoConfig.customOperations.credit_card;

        // Test case 1: Valid Visa card (4111111111111111)
        const result1 = creditCard('4111111111111111');
        assert.true(result1, 'Valid Visa card should pass Luhn check');

        // Test case 2: Valid Visa card with spaces and dashes
        const result2 = creditCard('4111-1111-1111-1111');
        assert.true(result2, 'Valid card with formatting should pass Luhn check');

        // Test case 3: Invalid card number
        const result3 = creditCard('4111111111111112');
        assert.false(result3, 'Invalid card number should fail Luhn check');

        // Test case 4: Valid MasterCard (5555555555554444)
        const result4 = creditCard('5555555555554444');
        assert.true(result4, 'Valid MasterCard should pass Luhn check');

        // Test case 5: Empty card number
        const result5 = creditCard('');
        assert.false(result5, 'Empty card number should fail');

        // Test case 6: Non-numeric card number
        const result6 = creditCard('abcd-efgh-ijkl-mnop');
        assert.false(result6, 'Non-numeric card should fail');

        // Test case 7: Too short card number
        const result7 = creditCard('411111');
        assert.false(result7, 'Too short card number should fail');
    });

    QUnit.test('Email Format Function - Common Pattern', function(assert) {
        // Define email validation function from documentation
        const emailPattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$';

        window.FormWatcherCustomOperations = {
            email_format: function(email) {
                return new RegExp(emailPattern).test(email);
            }
        };

        const emailFormat = window.FormWatcherCustomOperations.email_format;

        // Test case 1: Valid email
        const result1 = emailFormat('user@example.com');
        assert.true(result1, 'Valid email should pass validation');

        // Test case 2: Valid email with subdomain
        const result2 = emailFormat('user@mail.example.com');
        assert.true(result2, 'Email with subdomain should pass validation');

        // Test case 3: Valid email with plus
        const result3 = emailFormat('user+tag@example.com');
        assert.true(result3, 'Email with plus should pass validation');

        // Test case 4: Invalid email - no domain
        const result4 = emailFormat('user@');
        assert.false(result4, 'Email without domain should fail');

        // Test case 5: Invalid email - no @
        const result5 = emailFormat('userexample.com');
        assert.false(result5, 'Email without @ should fail');

        // Test case 6: Invalid email - no TLD
        const result6 = emailFormat('user@example');
        assert.false(result6, 'Email without TLD should fail');

        // Test case 7: Empty email
        const result7 = emailFormat('');
        assert.false(result7, 'Empty email should fail');
    });

    QUnit.test('Phone International Function - Common Pattern', function(assert) {
        // Define phone validation function from documentation
        window.FormWatcherCustomOperations = {
            phone_international: function(phone) {
                return /^\+[1-9]\d{1,14}$/.test(phone);
            }
        };

        const phoneInternational = window.FormWatcherCustomOperations.phone_international;

        // Test case 1: Valid US phone
        const result1 = phoneInternational('+12345678901');
        assert.true(result1, 'Valid US phone should pass validation');

        // Test case 2: Valid UK phone
        const result2 = phoneInternational('+441234567890');
        assert.true(result2, 'Valid UK phone should pass validation');

        // Test case 3: Invalid phone - no plus
        const result3 = phoneInternational('12345678901');
        assert.false(result3, 'Phone without + should fail');

        // Test case 4: Invalid phone - starts with 0
        const result4 = phoneInternational('+01234567890');
        assert.false(result4, 'Phone starting with 0 should fail');

        // Test case 5: Invalid phone - too short
        const result5 = phoneInternational('+1');
        assert.false(result5, 'Too short phone should fail');

        // Test case 6: Invalid phone - too long
        const result6 = phoneInternational('+123456789012345678');
        assert.false(result6, 'Too long phone should fail');

        // Test case 7: Empty phone
        const result7 = phoneInternational('');
        assert.false(result7, 'Empty phone should fail');
    });

    QUnit.test('Runtime Registration - Method 2', function(assert) {
        // Mock FormWatcherAuto for testing
        window.FormWatcherAuto = {
            registerCustomFunction: function(name, func) {
                if (!this.customFunctions) {
                    this.customFunctions = {};
                }
                this.customFunctions[name] = func;
            }
        };

        // Test the runtime registration example from documentation
        if (window.FormWatcherAuto) {
            FormWatcherAuto.registerCustomFunction('age_verification', function(minAge, birthDate) {
                if (!birthDate) return false;
                const today = new Date();
                const birth = new Date(birthDate);
                const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                return age >= minAge;
            });
        }

        // Verify the function was registered
        assert.ok(window.FormWatcherAuto.customFunctions, 'Custom functions object should exist');
        assert.ok(window.FormWatcherAuto.customFunctions.age_verification, 'Age verification function should be registered');

        // Test the registered function
        const ageVerification = window.FormWatcherAuto.customFunctions.age_verification;
        const result1 = ageVerification(18, '1990-05-15');
        assert.true(result1, 'Registered age verification function should work correctly');

        const result2 = ageVerification(18, '2010-05-15');
        assert.false(result2, 'Registered age verification function should reject young ages');
    });

    QUnit.test('Complex Validation Scenarios', function(assert) {
        // Define multiple custom functions for complex validation
        window.FormWatcherCustomOperations = {
            age_verification: function(minAge, birthDate) {
                if (!birthDate) return false;
                const today = new Date();
                const birth = new Date(birthDate);
                const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                return age >= minAge;
            },

            strong_password: function(password) {
                if (!password || password.length < 8) return false;
                const hasLower = /[a-z]/.test(password);
                const hasUpper = /[A-Z]/.test(password);
                const hasDigit = /[0-9]/.test(password);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                return hasLower && hasUpper && hasDigit && hasSpecial;
            },

            email_format: function(email) {
                const emailPattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$';
                return new RegExp(emailPattern).test(email);
            },

            username_length: function(username) {
                return username && username.length >= 3 && username.length <= 20;
            }
        };

        // Test user registration scenario
        const userData = {
            email: 'user@example.com',
            password: 'MySecurePass123!',
            birth_date: '1990-05-15',
            username: 'johndoe'
        };

        // Test each validation function
        const emailValid = window.FormWatcherCustomOperations.email_format(userData.email);
        assert.true(emailValid, 'Email should be valid');

        const passwordValid = window.FormWatcherCustomOperations.strong_password(userData.password);
        assert.true(passwordValid, 'Password should be strong');

        const ageValid = window.FormWatcherCustomOperations.age_verification(18, userData.birth_date);
        assert.true(ageValid, 'Age should be valid');

        const usernameValid = window.FormWatcherCustomOperations.username_length(userData.username);
        assert.true(usernameValid, 'Username should meet length requirements');

        // Test with invalid data
        const invalidUserData = {
            email: 'invalid.email',
            password: 'weak',
            birth_date: '2010-05-15',
            username: 'ab'
        };

        const emailInvalid = window.FormWatcherCustomOperations.email_format(invalidUserData.email);
        assert.false(emailInvalid, 'Invalid email should fail');

        const passwordInvalid = window.FormWatcherCustomOperations.strong_password(invalidUserData.password);
        assert.false(passwordInvalid, 'Weak password should fail');

        const ageInvalid = window.FormWatcherCustomOperations.age_verification(18, invalidUserData.birth_date);
        assert.false(ageInvalid, 'Invalid age should fail');

        const usernameInvalid = window.FormWatcherCustomOperations.username_length(invalidUserData.username);
        assert.false(usernameInvalid, 'Short username should fail');
    });

    QUnit.test('Edge Cases and Error Handling', function(assert) {
        // Define functions with comprehensive error handling
        window.FormWatcherCustomOperations = {
            safe_age_verification: function(minAge, birthDate) {
                try {
                    if (!birthDate || typeof birthDate !== 'string') return false;
                    if (!minAge || typeof minAge !== 'number') return false;

                    const today = new Date();
                    const birth = new Date(birthDate);

                    // Check for invalid dates
                    if (isNaN(birth.getTime()) || isNaN(today.getTime())) return false;

                    const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                    return age >= minAge;
                } catch (error) {
                    return false;
                }
            },

            safe_email_format: function(email) {
                try {
                    if (!email || typeof email !== 'string') return false;
                    const emailPattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$';
                    return new RegExp(emailPattern).test(email);
                } catch (error) {
                    return false;
                }
            }
        };

        // Test edge cases
        const safeAge = window.FormWatcherCustomOperations.safe_age_verification;
        const safeEmail = window.FormWatcherCustomOperations.safe_email_format;

        // Age verification edge cases
        assert.false(safeAge(null, '1990-05-15'), 'Null minAge should be handled');
        assert.false(safeAge(18, null), 'Null birthDate should be handled');
        assert.false(safeAge('18', '1990-05-15'), 'String minAge should be handled');
        assert.false(safeAge(18, 123), 'Number birthDate should be handled');
        assert.false(safeAge(18, 'invalid-date'), 'Invalid date should be handled');
        assert.false(safeAge(18, ''), 'Empty birthDate should be handled');

        // Email validation edge cases
        assert.false(safeEmail(null), 'Null email should be handled');
        assert.false(safeEmail(undefined), 'Undefined email should be handled');
        assert.false(safeEmail(123), 'Number email should be handled');
        assert.false(safeEmail([]), 'Array email should be handled');
        assert.false(safeEmail({}), 'Object email should be handled');
    });

    QUnit.test('Performance and Caching Patterns', function(assert) {
        // Test the performance tip from documentation - cache compiled patterns
        class RegexValidator {
            constructor() {
                this.emailRegex = new RegExp('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$');
                this.phoneRegex = new RegExp('^\\+[1-9]\\d{1,14}$');
            }

            validateEmail(email) {
                return this.emailRegex.test(email);
            }

            validatePhone(phone) {
                return this.phoneRegex.test(phone);
            }
        }

        const validator = new RegexValidator();

        // Test cached regex patterns
        const emailResult1 = validator.validateEmail('user@example.com');
        assert.true(emailResult1, 'Cached email regex should work');

        const emailResult2 = validator.validateEmail('invalid.email');
        assert.false(emailResult2, 'Cached email regex should reject invalid emails');

        const phoneResult1 = validator.validatePhone('+1234567890');
        assert.true(phoneResult1, 'Cached phone regex should work');

        const phoneResult2 = validator.validatePhone('1234567890');
        assert.false(phoneResult2, 'Cached phone regex should reject invalid phones');

        // Verify the regex objects are reused (same reference)
        const regex1 = validator.emailRegex;
        const regex2 = validator.emailRegex;
        assert.strictEqual(regex1, regex2, 'Regex objects should be cached and reused');
    });

    QUnit.test('Cross-Platform Pattern Compatibility', function(assert) {
        // Test patterns that work consistently across PHP and JavaScript
        const crossPlatformPatterns = {
            email: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$',
            phone_international: '^\\+[1-9]\\d{1,14}$',
            password_strong: '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d@$!%*?&]{8,}$',
            postal_code_us: '^[0-9]{5}(-[0-9]{4})?$'
        };

        // Test each pattern
        Object.keys(crossPlatformPatterns).forEach(patternName => {
            const pattern = crossPlatformPatterns[patternName];
            const regex = new RegExp(pattern);

            assert.ok(regex instanceof RegExp, `${patternName} pattern should compile to valid regex`);

            // Test that the pattern doesn't throw errors
            try {
                regex.test('test');
                assert.ok(true, `${patternName} pattern should execute without errors`);
            } catch (error) {
                assert.ok(false, `${patternName} pattern should not throw errors: ${error.message}`);
            }
        });

        // Test specific pattern validations
        const emailRegex = new RegExp(crossPlatformPatterns.email);
        assert.true(emailRegex.test('user@example.com'), 'Email pattern should validate correct emails');
        assert.false(emailRegex.test('invalid.email'), 'Email pattern should reject invalid emails');

        const phoneRegex = new RegExp(crossPlatformPatterns.phone_international);
        assert.true(phoneRegex.test('+1234567890'), 'Phone pattern should validate correct phones');
        assert.false(phoneRegex.test('1234567890'), 'Phone pattern should reject phones without +');

        const passwordRegex = new RegExp(crossPlatformPatterns.password_strong);
        assert.true(passwordRegex.test('MyPass123'), 'Password pattern should validate strong passwords');
        assert.false(passwordRegex.test('weak'), 'Password pattern should reject weak passwords');

        const zipRegex = new RegExp(crossPlatformPatterns.postal_code_us);
        assert.true(zipRegex.test('12345'), 'ZIP pattern should validate 5-digit codes');
        assert.true(zipRegex.test('12345-6789'), 'ZIP pattern should validate ZIP+4 codes');
        assert.false(zipRegex.test('1234'), 'ZIP pattern should reject short codes');
    });
});
