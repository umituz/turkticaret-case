/**
 * CollectionValidator class for validating Postman collections
 */

export class CollectionValidator {
  /**
   * @param {Object} collection The collection to validate
   */
  constructor(collection) {
    this.collection = collection;
  }

  /**
   * Performs validation of a collection
   * @returns {Object} Validation results with issues found
   */
  validateCollection() {
    const validationResults = {
      hasIssues: false,
      issues: []
    };

    // Process all items recursively to check for issues
    this._validateItems(this.collection.item, '', validationResults, this.collection);

    return validationResults;
  }

  /**
   * Validate items recursively to check for issues
   * @private
   */
  _validateItems(items, currentPath = '', validationResults, collection) {
    if (!Array.isArray(items)) return;

    items.forEach((item, index) => {
      if (!item) return;

      const itemPath = currentPath
        ? `${currentPath} > ${item?.name || index}`
        : `${item?.name || index}`;

      try {
        if (item.item) {
          this._validateItems(item.item, itemPath, validationResults, collection);
        } else if (item.request) {
          this._validateRequest(item, itemPath, validationResults, collection);
        }
      } catch (error) {
        // Silently handle errors
      }
    });
  }

  /**
   * Validate a request for potential issues
   * @private
   */
  _validateRequest(item, itemPath, validationResults, collection) {
    // Check for URL issues
    this._validateUrl(item, itemPath, validationResults, collection);

    // Check for header issues
    this._validateHeaders(item, itemPath, validationResults);

    // Check for body issues in POST/PUT requests
    this._validateRequestBody(item, itemPath, validationResults);

    // Check for content type issues
    this._validateContentType(item, itemPath, validationResults);
  }

  /**
   * Validate URL format and variables
   * @private
   */
  _validateUrl(item, itemPath, validationResults, collection) {
    if (!item.request || !item.request.url) return;

    const url = typeof item.request.url === 'string'
      ? item.request.url
      : (item.request.url.raw || '');

    // Check for undefined or null in URLs
    if (url.includes('undefined') || url.includes('null')) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: 'URL contains undefined or null values'
      });
    }

    // Check for missing variables in URLs
    const missingVarRegex = /{{([^}]+)}}/g;
    const matches = [...url.matchAll(missingVarRegex)];

    if (matches.length > 0) {
      // Get defined variables
      const definedVars = new Set();
      if (Array.isArray(collection?.variable)) {
        collection.variable.forEach(v => definedVars.add(v.key));
      }

      // Add common variables
      definedVars.add('base_url');
      definedVars.add('token');

      // Check for undefined variables
      matches.forEach(match => {
        const varName = match[1];
        if (!definedVars.has(varName)) {
          validationResults.hasIssues = true;
          validationResults.issues.push({
            name: item.name,
            path: itemPath,
            issue: `URL contains undefined variable: ${varName}`
          });
        }
      });
    }
  }

  /**
   * Validate request headers
   * @private
   */
  _validateHeaders(item, itemPath, validationResults) {
    if (!item.request || item.request.method === 'OPTIONS') return;

    const headers = item.request.header || [];
    const headerKeys = headers.map(h => h.key.toLowerCase());

    // Check for Accept header
    if (!headerKeys.includes('accept')) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: 'Missing "Accept" header'
      });
    }

    // For POST/PUT/PATCH requests, check for Content-Type header
    if (['POST', 'PUT', 'PATCH'].includes(item.request.method) && !headerKeys.includes('content-type')) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: `Missing "Content-Type" header for ${item.request.method} request`
      });
    }
  }

  /**
   * Validate request body
   * @private
   */
  _validateRequestBody(item, itemPath, validationResults) {
    if (!item.request || !['POST', 'PUT', 'PATCH'].includes(item.request.method)) return;

    const hasEmptyBody = !item.request.body ||
      (item.request.body.mode === 'raw' && (!item.request.body.raw || item.request.body.raw.trim() === '')) ||
      (item.request.body.mode === 'formdata' && (!item.request.body.formdata || item.request.body.formdata.length === 0));

    if (hasEmptyBody) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: `Empty request body for ${item.request.method} request`
      });
    }
  }

  /**
   * Validate content type headers
   * @private
   */
  _validateContentType(item, itemPath, validationResults) {
    if (!item.request || !['POST', 'PUT', 'PATCH'].includes(item.request.method)) return;

    const headers = item.request.header || [];
    const contentTypeHeader = headers.find(h => h.key.toLowerCase() === 'content-type');

    if (!contentTypeHeader) return;

    const contentType = contentTypeHeader.value;

    // If body is raw JSON, check for proper content type
    if (item.request.body?.mode === 'raw' && item.request.body.raw?.trim().startsWith('{') &&
        !contentType.includes('application/json')) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: `JSON body with incorrect Content-Type: ${contentType}`
      });
    }

    // If body is form data, check for proper content type
    if (item.request.body?.mode === 'formdata' &&
        !contentType.includes('multipart/form-data') &&
        !contentType.includes('application/x-www-form-urlencoded')) {
      validationResults.hasIssues = true;
      validationResults.issues.push({
        name: item.name,
        path: itemPath,
        issue: `Form data with incorrect Content-Type: ${contentType}`
      });
    }
  }
}
