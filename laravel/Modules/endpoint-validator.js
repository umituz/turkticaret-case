/**
 * Postman API Endpoint Validator
 *
 * This module provides functionality to validate API endpoints in a Postman collection.
 * It creates a validation collection with test scripts to check:
 * - Authentication requirements
 * - Response format consistency
 * - Performance metrics
 * - Header validation
 * - Server errors (500 responses)
 *
 * Usage: node endpoint-validator.js [projectPath]
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// ES modules equivalent for __dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

class EndpointValidator {
    constructor(projectPath) {
        this.projectPath = projectPath || process.cwd();

        // Load project config if available
        this.config = this.loadProjectConfig();

        // Define paths
        this.COMBINED_COLLECTION_PATH = path.join(this.projectPath, this.config.output_file);
        this.VALIDATION_COLLECTION_PATH = path.join(this.projectPath, this.config.output_file.replace('.json', '-Validation.json'));
        this.VALIDATION_ENV_PATH = path.join(this.projectPath, 'environments/validation-env.json');
        this.REPORTS_DIR = path.join(this.projectPath, 'reports');

        // Check if the combined collection exists
        if (!fs.existsSync(this.COMBINED_COLLECTION_PATH)) {
            throw new Error(`Combined collection not found at ${this.COMBINED_COLLECTION_PATH}`);
        }
    }

    /**
     * Load project configuration from project-config.json
     * @returns {Object} Project configuration
     */
    loadProjectConfig() {
        const configPath = path.join(this.projectPath, 'project-config.json');
        const defaultConfig = {
            name: "API Collection",
            output_file: "API-Collection.postman_collection.json",
            description: "API Documentation",
            base_url: "{{base_url}}",
            token_var: "token"
        };

        try {
            if (fs.existsSync(configPath)) {
                console.log('📑 Loading project configuration...');
                const configContent = fs.readFileSync(configPath, 'utf8');
                const loadedConfig = JSON.parse(configContent);
                return { ...defaultConfig, ...loadedConfig };
            }
        } catch (error) {
            console.warn(`⚠️ Warning: Could not load project config: ${error.message}. Using defaults.`);
        }

        return defaultConfig;
    }

    /**
     * Create validation environment file if it doesn't exist
     */
    createValidationEnvironment() {
        // Create directory if it doesn't exist
        const envDir = path.dirname(this.VALIDATION_ENV_PATH);
        if (!fs.existsSync(envDir)) {
            fs.mkdirSync(envDir, { recursive: true });
        }

        // Check if file already exists
        if (fs.existsSync(this.VALIDATION_ENV_PATH)) {
            console.log(`Validation environment already exists at ${this.VALIDATION_ENV_PATH}`);
            return;
        }

        // Create environment file with test-specific values
        const validationEnv = {
            "id": "validation-env",
            "name": "API Validation Environment",
            "values": [
                {
                    "key": "base_url",
                    "value": this.config.base_url.replace('{{base_url}}', 'http://localhost:8000'),
                    "type": "string",
                    "enabled": true
                },
                {
                    "key": this.config.token_var,
                    "value": "invalid-token-for-testing",
                    "type": "string",
                    "enabled": true
                },
                {
                    "key": "platform",
                    "value": "web",
                    "type": "string",
                    "enabled": true
                },
                {
                    "key": "validation_mode",
                    "value": "true",
                    "type": "string",
                    "enabled": true
                }
            ]
        };

        // Write environment file
        fs.writeFileSync(this.VALIDATION_ENV_PATH, JSON.stringify(validationEnv, null, 2));
        console.log(`Created validation environment at ${this.VALIDATION_ENV_PATH}`);
    }

    /**
     * Generate the validation test collection
     * @returns {Promise<Object>} Validation results
     */
    async generateValidationCollection() {
        console.log('Loading combined collection...');

        try {
            // Make sure the reports directory exists
            if (!fs.existsSync(this.REPORTS_DIR)) {
                fs.mkdirSync(this.REPORTS_DIR, { recursive: true });
            }

            // Load the combined collection
            const combinedCollection = JSON.parse(fs.readFileSync(this.COMBINED_COLLECTION_PATH, 'utf8'));

            // Create a deep copy of the collection for validation
            const validationCollection = JSON.parse(JSON.stringify(combinedCollection));

            // Set a new name for the validation collection
            validationCollection.info.name += ' Validation';

            // Results to track validation issues during generation
            const validationResults = {
                hasServerErrors: false,
                serverErrorEndpoints: []
            };

            // Process all items recursively to add validation test scripts
            console.log('Adding validation test scripts to all requests...');
            this.processItems(validationCollection.item, '', validationResults);

            // Add collection-level test to generate summary report
            this.addCollectionTests(validationCollection);

            // Save the validation collection
            fs.writeFileSync(this.VALIDATION_COLLECTION_PATH, JSON.stringify(validationCollection, null, 2));

            // Create validation environment file if needed
            this.createValidationEnvironment();

            console.log(`Validation collection saved to ${this.VALIDATION_COLLECTION_PATH}`);

            return validationResults;
        } catch (error) {
            console.error(`Error generating validation collection: ${error.message}`);
            throw error;
        }
    }

    /**
     * Add collection-level tests to generate a summary report
     * @param {Object} collection - The collection object to modify
     */
    addCollectionTests(collection) {
        // Initialize collection variables to track validation results
        if (!collection.variable) {
            collection.variable = [];
        }

        // Add variables to store validation results
        const resultVariables = [
            "auth_failures", "format_failures", "performance_failures",
            "headers_failures", "server_error_failures"
        ];

        resultVariables.forEach(varName => {
            collection.variable.push({
                key: varName,
                value: "[]",
                type: "string"
            });
        });

        // Add collection-level test to generate the report
        if (!collection.event) {
            collection.event = [];
        }

        collection.event.push({
            listen: "test",
            script: {
                type: "text/javascript",
                exec: [
                    "// Skip if not the last request",
                    "if (!pm.request.url.toString().includes('LAST_REQUEST_MARKER')) {",
                    "    return;",
                    "}",
                    "",
                    "// Generate validation report",
                    "console.log('\\n\\n=== ENDPOINT VALIDATION REPORT ===\\n');",
                    "",
                    "const authFailures = JSON.parse(pm.collectionVariables.get('auth_failures') || '[]');",
                    "const formatFailures = JSON.parse(pm.collectionVariables.get('format_failures') || '[]');",
                    "const performanceFailures = JSON.parse(pm.collectionVariables.get('performance_failures') || '[]');",
                    "const headersFailures = JSON.parse(pm.collectionVariables.get('headers_failures') || '[]');",
                    "const serverErrorFailures = JSON.parse(pm.collectionVariables.get('server_error_failures') || '[]');",
                    "",
                    "console.log(`Authentication Issues: ${authFailures.length}`);",
                    "authFailures.forEach(f => console.log(` - ${f.name}: ${f.url}`));",
                    "",
                    "console.log(`\\nResponse Format Issues: ${formatFailures.length}`);",
                    "formatFailures.forEach(f => console.log(` - ${f.name}: ${f.url}`));",
                    "",
                    "console.log(`\\nPerformance Issues: ${performanceFailures.length}`);",
                    "performanceFailures.forEach(f => console.log(` - ${f.name}: ${f.url} (${f.time}ms)`));",
                    "",
                    "console.log(`\\nHeader Issues: ${headersFailures.length}`);",
                    "headersFailures.forEach(f => console.log(` - ${f.name}: ${f.url}`));",
                    "",
                    "console.log(`\\nServer Error Issues: ${serverErrorFailures.length}`);",
                    "serverErrorFailures.forEach(f => console.log(` - ${f.name}: ${f.url} (${f.status})`));",
                    "",
                    "const totalIssues = authFailures.length + formatFailures.length + performanceFailures.length + headersFailures.length + serverErrorFailures.length;",
                    "console.log(`\\nTotal Issues: ${totalIssues}`);",
                    "",
                    "if (totalIssues === 0) {",
                    "    console.log('\\n✅ All endpoints passed validation!');",
                    "} else {",
                    "    console.log('\\n❌ Some endpoints have validation issues.');",
                    "}",
                    "",
                    "console.log('\\n=== END OF REPORT ===');"
                ]
            }
        });
    }

    /**
     * Process items recursively to add validation test scripts
     * @param {Array} items - Collection items to process
     * @param {string} currentPath - Current path in the collection (for debug purposes)
     * @param {Object} validationResults - Object to store validation results
     */
    processItems(items, currentPath = '', validationResults) {
        // Validate items is an array
        if (!Array.isArray(items)) {
            console.warn(`Warning: Invalid items array received at path: ${currentPath}`);
            return;
        }

        // Process each item
        items.forEach((item, index) => {
            const itemPath = currentPath ? `${currentPath} > ${index}` : `${index}`;

            // Skip null or undefined items
            if (!item) {
                console.warn(`Warning: Skipping null or undefined item at index ${index} (path: ${itemPath})`);
                return;
            }

            try {
                // If this is a folder (has items), process recursively
                if (item.item) {
                    this.processItems(item.item, `${itemPath} > ${item.name}`, validationResults);

                    // Check if this is the last folder in the collection for marking the last request
                    if (items.length > 0 && index === items.length - 1 && item.item.length > 0) {
                        // Mark the last request in the collection for reporting
                        const lastItem = item.item[item.item.length - 1];
                        if (lastItem.request && lastItem.request.url) {
                            this.markAsLastRequest(lastItem);
                        }
                    }
                }
                // If this is a request, add validation test scripts
                else if (item.request) {
                    // Check for potential issues in the URL
                    this.checkRequestUrl(item, itemPath, validationResults);

                    // Add validation test scripts
                    this.addValidationScripts(item);
                }
            } catch (error) {
                console.error(`Error processing item at path ${itemPath}: ${error.message}`);
            }
        });
    }

    /**
     * Mark a request as the last one for reporting purposes
     * @param {Object} item - The request item to mark
     */
    markAsLastRequest(item) {
        if (item.request.url && typeof item.request.url === 'object') {
            if (!item.request.url.query) {
                item.request.url.query = [];
            }

            item.request.url.query.push({
                key: 'LAST_REQUEST_MARKER',
                value: 'true',
                disabled: false
            });

            if (item.request.url.raw) {
                item.request.url.raw += '&LAST_REQUEST_MARKER=true';
            }
        }
    }

    /**
     * Check request URL for potential issues
     * @param {Object} item - The request item to check
     * @param {string} itemPath - Path to the item for logging
     * @param {Object} validationResults - Object to store validation results
     */
    checkRequestUrl(item, itemPath, validationResults) {
        const url = typeof item.request.url === 'string'
            ? item.request.url
            : (item.request.url.raw || '');

        // Look for obvious issues in the URL
        if (url.includes('undefined') || url.includes('null')) {
            validationResults.hasServerErrors = true;
            validationResults.serverErrorEndpoints.push({
                name: item.name,
                path: itemPath,
                url: url,
                issue: 'URL contains undefined or null values'
            });
            console.warn(`⚠️ Warning: URL contains undefined or null values in ${itemPath}: ${url}`);
        }
    }

    /**
     * Add validation test scripts to a request
     * @param {Object} item - The request item to add scripts to
     */
    addValidationScripts(item) {
        // Ensure there's an event array
        if (!item.event) {
            item.event = [];
        }

        // Find or create test event
        let testEvent = item.event.find(e => e.listen === 'test');

        if (!testEvent) {
            testEvent = {
                listen: 'test',
                script: {
                    type: 'text/javascript',
                    exec: []
                }
            };
            item.event.push(testEvent);
        }

        // Add test scripts to the existing exec array
        if (!Array.isArray(testEvent.script.exec)) {
            testEvent.script.exec = [];
        }

        // Add collection variable updates for reporting
        testEvent.script.exec.push(
            "// Store test failures for reporting",
            "function trackFailure(type, additionalInfo = {}) {",
            "    const varName = type + '_failures';",
            "    const failures = JSON.parse(pm.collectionVariables.get(varName) || '[]');",
            "    failures.push({",
            "        name: pm.info.requestName,",
            "        url: pm.request.url.toString(),",
            "        time: pm.response.responseTime,",
            "        status: pm.response.code,",
            "        ...additionalInfo",
            "    });",
            "    pm.collectionVariables.set(varName, JSON.stringify(failures));",
            "}",
            ""
        );

        // Check for server errors (500 status code)
        testEvent.script.exec.push(
            "pm.test(\"Endpoint: Should not return server error (500)\", function() {",
            "    try {",
            "        // For all endpoints, the response code should not be a server error",
            "        if (pm.response.code >= 500) {",
            "            // This is a server error, which is a critical issue",
            "            pm.expect.fail('Server error detected: ' + pm.response.code);",
            "            trackFailure('server_error', { status: pm.response.code });",
            "        }",
            "    } catch (e) {",
            "        trackFailure('server_error', { status: pm.response.code });",
            "        throw e;",
            "    }",
            "});"
        );

        // Authentication test - assuming protected endpoints should return 401/403 with invalid token
        testEvent.script.exec.push(
            "pm.test(\"Authenticate: Protected endpoint check\", function() {",
            "    // Skip for auth endpoints (login, register, etc.)",
            "    if (pm.request.url.toString().includes('/auth/') || ",
            "        pm.request.url.toString().includes('/login') ||",
            "        pm.request.url.toString().includes('/register')) {",
            "        return; // Skip authentication test for auth endpoints",
            "    }",
            "    ",
            "    try {",
            "        // If using an invalid token and the endpoint is protected,",
            "        // we should get a 401 or 403 response",
            "        if (pm.environment.get('validation_mode') === 'true') {",
            "            pm.expect(pm.response.code).to.be.oneOf([401, 403]);",
            "        }",
            "    } catch (e) {",
            "        // Only track as a failure if we're in validation mode",
            "        if (pm.environment.get('validation_mode') === 'true') {",
            "            trackFailure('auth');",
            "            throw e;",
            "        }",
            "    }",
            "});"
        );

        // Response format test for JSON responses
        testEvent.script.exec.push(
            "pm.test(\"Format: Response should be valid JSON\", function() {",
            "    if (pm.response.headers.get('Content-Type') && ",
            "        pm.response.headers.get('Content-Type').includes('application/json')) {",
            "        try {",
            "            const jsonData = pm.response.json();",
            "            pm.expect(jsonData).to.be.an('object');",
            "        } catch (e) {",
            "            trackFailure('format');",
            "            throw e;",
            "        }",
            "    }",
            "});"
        );

        // Performance test
        testEvent.script.exec.push(
            "pm.test(\"Performance: Response time should be acceptable\", function() {",
            "    try {",
            "        // Fail if response takes more than 2 seconds",
            "        pm.expect(pm.response.responseTime).to.be.below(2000);",
            "    } catch (e) {",
            "        trackFailure('performance', { time: pm.response.responseTime });",
            "        throw e;",
            "    }",
            "});"
        );
    }

    /**
     * Run the validation tests
     */
    async runValidation() {
        try {
            console.log("Starting API endpoint validation...");

            // Generate the validation collection and get initial results
            const validationResults = await this.generateValidationCollection();

            if (validationResults.hasServerErrors) {
                console.log("\n⚠️ Some endpoints have potential issues:");
                validationResults.serverErrorEndpoints.forEach(endpoint => {
                    console.log(`- ${endpoint.path}: ${endpoint.issue}`);
                    console.log(`  URL: ${endpoint.url}`);
                });
                console.log("\nFix these issues before proceeding with full validation.");
            } else {
                console.log("\n✅ Basic URL validation passed. No obvious issues found.");
            }

            console.log("\nValidation collection created at:", this.VALIDATION_COLLECTION_PATH);
            console.log("You can import this into Postman to run detailed validation tests.");
            console.log("\nTo run tests automatically, install Newman:");
            console.log("npm install -g newman newman-reporter-htmlextra");

            return validationResults;
        } catch (error) {
            console.error(`\n❌ Error during validation: ${error.message}`);
            throw error;
        }
    }
}

// If run directly, generate and run the validation tests
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    const projectPath = process.argv[2] || process.cwd();
    console.log(`Using project path: ${projectPath}`);

    const validator = new EndpointValidator(projectPath);
    validator.runValidation()
        .then(results => {
            if (results.hasServerErrors) {
                process.exit(1);
            }
        })
        .catch(error => {
            console.error(`Error: ${error.message}`);
            process.exit(1);
        });
}

export default EndpointValidator;
