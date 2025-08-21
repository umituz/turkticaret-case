/**
 * CollectionUtils class with utility functions for Postman collections
 */

export class CollectionUtils {
  /**
   * Clean up the collection structure by removing null/undefined items
   * @param {Object} collection The collection to clean up
   */
  sanitizeCollection(collection) {
    if (!collection) return;

    if (collection.item && Array.isArray(collection.item)) {
      collection.item = collection.item.filter(item => item !== null && item !== undefined);

      collection.item.forEach(item => {
        if (item.item) {
          this.sanitizeItem(item);
        }
      });
    }
  }

  /**
   * Clean up an item in the collection structure
   * @param {Object} item The item to clean up
   */
  sanitizeItem(item) {
    if (!item) return;

    if (item.item && Array.isArray(item.item)) {
      item.item = item.item.filter(subItem => subItem !== null && subItem !== undefined);

      item.item.forEach(subItem => {
        if (subItem.item) {
          this.sanitizeItem(subItem);
        }
      });
    }
  }

  /**
   * Get a flattened list of all requests in a collection
   * @param {Object} collection The collection to process
   * @returns {Array} Flattened array of all requests
   */
  getFlattenedRequests(collection) {
    const requests = [];

    const processItems = (items, path = '') => {
      if (!items || !Array.isArray(items)) {
        return;
      }

      items.forEach(item => {
        if (!item) return;

        const currentPath = path ? `${path} > ${item.name || 'unnamed'}` : (item.name || 'unnamed');

        if (item.item && Array.isArray(item.item)) {
          processItems(item.item, currentPath);
        } else if (item.request) {
          requests.push({
            name: item.name,
            path: currentPath,
            request: item.request,
            item: item
          });
        }
      });
    };

    if (collection && collection.item) {
      processItems(collection.item);
    }

    return requests;
  }

  /**
   * Find all variables used in a collection
   * @param {Object} collection The collection to process
   * @returns {Set} Set of all variable names used in URLs and request bodies
   */
  findUsedVariables(collection) {
    const variableNames = new Set();
    const variableRegex = /{{([^}]+)}}/g;

    const requests = this.getFlattenedRequests(collection);

    requests.forEach(req => {
      // Check URL for variables
      if (req.request.url) {
        const url = typeof req.request.url === 'string'
          ? req.request.url
          : (req.request.url.raw || '');

        const urlMatches = [...url.matchAll(variableRegex)];
        urlMatches.forEach(match => {
          variableNames.add(match[1]);
        });
      }

      // Check request body for variables
      if (req.request.body && req.request.body.mode === 'raw' && req.request.body.raw) {
        const bodyMatches = [...req.request.body.raw.matchAll(variableRegex)];
        bodyMatches.forEach(match => {
          variableNames.add(match[1]);
        });
      }

      // Check headers for variables
      if (req.request.header && Array.isArray(req.request.header)) {
        req.request.header.forEach(header => {
          if (header.value) {
            const headerMatches = [...header.value.matchAll(variableRegex)];
            headerMatches.forEach(match => {
              variableNames.add(match[1]);
            });
          }
        });
      }
    });

    return variableNames;
  }

  /**
   * Generate a list of undefined variables in a collection
   * @param {Object} collection The collection to process
   * @returns {Array} Array of undefined variable names
   */
  findUndefinedVariables(collection) {
    const usedVariables = this.findUsedVariables(collection);
    const definedVariables = new Set();

    // Get all defined variables from the collection
    if (collection.variable && Array.isArray(collection.variable)) {
      collection.variable.forEach(variable => {
        definedVariables.add(variable.key);
      });
    }

    // Skip common variables like base_url and token
    definedVariables.add('base_url');
    definedVariables.add('token');

    // Return variables that are used but not defined
    return [...usedVariables].filter(varName => !definedVariables.has(varName));
  }

  /**
   * Extract path parameters from URL
   * @param {string} url URL to extract parameters from
   * @returns {Array} Array of path parameter names
   */
  extractPathParameters(url) {
    const pathParamRegex = /:([a-zA-Z0-9_]+)|{([a-zA-Z0-9_]+)}/g;
    const params = [];

    if (!url) return params;

    const matches = [...url.matchAll(pathParamRegex)];
    matches.forEach(match => {
      params.push(match[1] || match[2]);
    });

    return params;
  }

  /**
   * Validate URL format
   * @param {string} url URL to validate
   * @returns {boolean} True if URL is valid
   */
  isValidUrl(url) {
    if (!url) return false;

    // Check for undefined or null in URLs
    if (url.includes('undefined') || url.includes('null')) {
      return false;
    }

    // URLs with variables are considered valid
    if (url.includes('{{') && url.includes('}}')) {
      return true;
    }

    try {
      // Try to create a URL object
      new URL(url);
      return true;
    } catch (error) {
      return false;
    }
  }
}
