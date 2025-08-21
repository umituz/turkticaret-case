/**
 * CollectionBuilder class responsible for building and processing Postman collections
 */

import fs from 'fs';
import path from 'path';
import { CollectionUtils } from './CollectionUtils.js';

export class CollectionBuilder {
  /**
   * @param {Object} config Configuration object from ConfigLoader
   */
  constructor(config) {
    this.config = config;
    this.utils = new CollectionUtils();
  }

  /**
   * Build the complete collection by processing all files and configurations
   * @returns {Object} Complete Postman collection
   */
  async buildCollection() {
    try {
      // Load master collection template
      const masterCollectionContent = fs.readFileSync(this.config.masterCollectionPath, 'utf8');
      const masterCollection = JSON.parse(masterCollectionContent);

      // Update collection metadata from project config
      if (masterCollection.info) {
        masterCollection.info.name = this.config.projectConfig.name;
        masterCollection.info.description = this.config.projectConfig.description;
      }

      // Process collection files
      const collectionsMap = this._getCollectionsMap(this.config.collectionsDir);

      // Resolve references in the master collection
      this._resolveReferences(masterCollection.item, collectionsMap);

      // Process items to fix missing names and inject headers/filters
      this._processItems(masterCollection.item, this.config.headers, this.config.filters);

      // Add model variables to collection
      this._addModelVariables(masterCollection, this.config.modelVariables);

      // Add filter variables to collection
      this._addFilterVariables(masterCollection);

      // Clean up the collection structure
      this.utils.sanitizeCollection(masterCollection);

      return masterCollection;
    } catch (error) {
      throw new Error(`Error building collection: ${error.message}`);
    }
  }

  /**
   * Create a map of collection files
   * @param {string} directoryPath Path to collections directory
   * @returns {Object} Map of collection files
   * @private
   */
  _getCollectionsMap(directoryPath) {
    const collectionsMap = {};

    if (!fs.existsSync(directoryPath)) {
      return collectionsMap;
    }

    const processDirectory = (dirPath) => {
      const files = fs.readdirSync(dirPath);

      for (const file of files) {
        const filePath = path.join(dirPath, file);
        const stats = fs.statSync(filePath);

        if (stats.isDirectory()) {
          processDirectory(filePath);
        } else if (file.endsWith('.json')) {
          try {
            const content = fs.readFileSync(filePath, 'utf8');
            const jsonContent = JSON.parse(content);
            const fileName = path.basename(file, '.json');

            if (jsonContent.item && Array.isArray(jsonContent.item)) {
              this._fixItemNames(jsonContent.item);
            }

            collectionsMap[fileName] = jsonContent;
          } catch (error) {
            // Silently handle errors
          }
        }
      }
    };

    processDirectory(directoryPath);
    return collectionsMap;
  }

  /**
   * Fix missing names in collection items
   * @param {Array} items Collection items
   * @param {string} parentPath Path to parent item
   * @private
   */
  _fixItemNames(items, parentPath = '') {
    if (!items) return;

    items.forEach((item, index) => {
      const itemPath = parentPath
          ? `${parentPath}/${item.name || 'unnamed'}`
          : (item.name || 'unnamed');

      if (!item.name || item.name.trim() === '') {
        item.name = item.request?.name ||
            item.request?.method ||
            `Item ${index + 1}`;
      }

      if (item.request && (!item.request.name || item.request.name.trim() === '' || item.request.name === 'Untitled Request')) {
        item.request.name = item.name ||
            `${item.request.method || 'GET'} ${item.request.url?.path?.join('/') || 'Request'}`;
      }

      if (item.item && Array.isArray(item.item)) {
        this._fixItemNames(item.item, itemPath);
      }
    });
  }

  /**
   * Resolve references in collection items
   * @param {Array} items Collection items
   * @param {Object} collectionsMap Map of collection files
   * @private
   */
  _resolveReferences(items, collectionsMap) {
    if (!items || !Array.isArray(items)) {
      return;
    }

    for (let i = 0; i < items.length; i++) {
      const item = items[i];

      if ((item.reference && item.reference.startsWith('collection:')) || (item.$ref && item.$ref.includes('.json'))) {
        let collectionName;
        let itemPath;
        let fullPath;

        if (item.reference) {
          collectionName = item.reference.substring('collection:'.length);
        } else if (item.$ref) {
          const refParts = item.$ref.split('#');
          const refPath = refParts[0];
          const refPointer = refParts[1] || '';
          fullPath = refPath;

          const pathParts = refPath.split('/');
          collectionName = path.basename(pathParts[pathParts.length - 1], '.json');

          itemPath = refPointer;
        }

        if (collectionsMap[collectionName]) {
          let resolvedItem;

          if (itemPath) {
            try {
              const pathSegments = itemPath.split('/').filter(Boolean);
              let current = collectionsMap[collectionName];

              for (const segment of pathSegments) {
                if (segment === 'item' && Array.isArray(current.item)) {
                  current = current.item;
                } else if (!isNaN(parseInt(segment))) {
                  const index = parseInt(segment);
                  if (Array.isArray(current) && index < current.length) {
                    current = current[index];
                  } else {
                    throw new Error(`Index ${index} out of bounds`);
                  }
                } else {
                  throw new Error(`Invalid path segment: ${segment}`);
                }
              }

              resolvedItem = JSON.parse(JSON.stringify(current));
            } catch (error) {
              // Silently handle errors
              resolvedItem = null;
            }
          }

          if (!resolvedItem && collectionsMap[collectionName].item) {
            resolvedItem = {
              name: item.name || collectionsMap[collectionName].info?.name || collectionName,
              item: JSON.parse(JSON.stringify(collectionsMap[collectionName].item))
            };
          }

          if (resolvedItem) {
            items[i] = resolvedItem;

            if (resolvedItem.item && Array.isArray(resolvedItem.item)) {
              this._resolveReferences(resolvedItem.item, collectionsMap);
            }
          }
        } else if (fullPath && fullPath.startsWith('./')) {
          try {
            const relativePath = fullPath.replace('./', '');
            const absolutePath = path.join(this.config.collectionsDir, relativePath);

            if (fs.existsSync(absolutePath)) {
              const content = fs.readFileSync(absolutePath, 'utf8');
              const fileContent = JSON.parse(content);

              resolvedItem = {
                name: item.name || fileContent.info?.name || collectionName,
                description: item.description || fileContent.info?.description || "",
                item: fileContent.item || []
              };

              items[i] = resolvedItem;

              if (resolvedItem.item && Array.isArray(resolvedItem.item)) {
                this._resolveReferences(resolvedItem.item, collectionsMap);
              }
            }
          } catch (error) {
            // Silently handle errors
          }
        }
      } else if (item.item && Array.isArray(item.item)) {
        this._resolveReferences(item.item, collectionsMap);
      }
    }
  }

  /**
   * Process collection items to fix names and inject headers/filters
   * @param {Array} items Collection items
   * @param {Object} commonHeaders Common headers to inject
   * @param {Object} commonFilters Common filters to inject
   * @private
   */
  _processItems(items, commonHeaders, commonFilters) {
    if (!items || !Array.isArray(items)) {
      return;
    }

    for (let i = 0; i < items.length; i++) {
      const item = items[i];

      if (!item) {
        continue;
      }

      if (!item.name || item.name.trim() === '') {
        item.name = item.request?.name ||
            item.request?.url?.path?.join('/') ||
            item.request?.method + ' Request ' + (i + 1);
      }

      if (item.item) {
        this._processItems(item.item, commonHeaders, commonFilters);
      } else if (item.request) {
        if (!item.request.name || item.request.name.trim() === '' || item.request.name === 'Untitled Request') {
          item.request.name = item.name || `${item.request.method} ${item.request.url?.path?.join('/') || 'Request'}`;
        }

        if ((!item.request.name || item.request.name.trim() === '') && item.name) {
          item.request.name = item.name;
        }

        this._injectCommonHeaders(item, commonHeaders);

        if (item.request.method === 'GET' && this._isListEndpoint(item)) {
          this._injectFilters(item, commonFilters);
        }
      }
    }
  }

  /**
   * Determine if an endpoint is a listing endpoint
   * @param {Object} item Request item
   * @returns {boolean} True if this looks like a listing endpoint
   * @private
   */
  _isListEndpoint(item) {
    const name = (item.name || '').toLowerCase();

    // Only apply filters to endpoints that explicitly include "by filters" in their name
    if (name.includes('by filters')) {
      return true;
    }

    // Disable other automatic detection methods
    // const url = item.request.url;
    // if (url && url.path) {
    //   const lastPathSegment = url.path[url.path.length - 1];
    //
    //   if (!/^{[^}]+}$/.test(lastPathSegment) && !/\/:id$/.test(lastPathSegment)) {
    //     return true;
    //   }
    // }

    return false;
  }

  /**
   * Inject common headers into a request
   * @param {Object} item Request item
   * @param {Object} commonHeaders Headers to inject
   * @private
   */
  _injectCommonHeaders(item, commonHeaders) {
    if (!item || !item.request) return;

    const originalName = item.request.name || item.name;

    if (!item.request.header) {
      item.request.header = [];
    }

    const currentHeaderKeys = item.request.header.map(h => h.key.toLowerCase());

    if (commonHeaders && commonHeaders.headers) {
      commonHeaders.headers.forEach(header => {
        if (!currentHeaderKeys.includes(header.key.toLowerCase())) {
          item.request.header.push(header);
        }
      });
    }

    if (originalName && (!item.request.name || item.request.name === 'Untitled Request')) {
      item.request.name = originalName;
    }
  }

  /**
   * Inject filters into a request
   * @param {Object} item Request item
   * @param {Object} filters Filters to inject
   * @returns {Object} Modified item
   * @private
   */
  _injectFilters(item, filters) {
    if (!filters || Object.keys(filters).length === 0) {
      return item;
    }

    // Only inject into requests
    if (item.request && item.request.url && item.request.method === 'GET') {
      // Skip if URL is a string (already optimized) or already has query parameters
      if (typeof item.request.url === 'string') {
        return item;
      }

      const hasExistingParams = item.request.url.query && item.request.url.query.length > 0;

      // Add standard filters to GET requests that look like listing endpoints
      const isListingEndpoint = (!item.request.url.path || !item.request.url.path.some(p => p.includes('{'))) && !hasExistingParams;

      if (isListingEndpoint) {
        // Create query array if it doesn't exist
        if (!item.request.url.query) {
          item.request.url.query = [];
        }

        // Add standard filters from common filters
        if (filters.standard_filters) {
          // Add standard pagination filters
          if (filters.standard_filters.pagination) {
            item.request.url.query.push(...filters.standard_filters.pagination);
          }

          // Add standard sorting filters
          if (filters.standard_filters.sorting) {
            item.request.url.query.push(...filters.standard_filters.sorting);
          }

          // Add standard searching filters
          if (filters.standard_filters.searching) {
            item.request.url.query.push(...filters.standard_filters.searching);
          }

          // Add standard date range filters
          if (filters.standard_filters.date_range) {
            item.request.url.query.push(...filters.standard_filters.date_range);
          }
        }

        // Add project-specific filters in a generic way from project_filters
        if (filters.project_filters) {
          const pathStr = item.request.url.path ? item.request.url.path.join('/').toLowerCase() : '';

          // Iterate through all project filter categories and apply them based on path matching
          Object.entries(filters.project_filters).forEach(([filterCategory, filterGroup]) => {
            // Check if this filter group has path rules
            if (filterGroup.path_rules) {
              // Apply filters if the path matches any of the rules
              if (filterGroup.path_rules.some(rule => pathStr.includes(rule))) {
                // Apply all filter items in this category
                Object.values(filterGroup)
                    .filter(item => Array.isArray(item))
                    .forEach(filterItems => {
                      item.request.url.query.push(...filterItems);
                    });
              }
            } else if (Array.isArray(filterGroup)) {
              // If no path rules defined but it's an array, it's a simple filter group
              // Apply these filters to all endpoints
              item.request.url.query.push(...filterGroup);
            }
          });
        }
      }
    }

    // Process nested folders recursively
    if (item.item && Array.isArray(item.item)) {
      item.item = item.item.map(subitem => this._injectFilters(subitem, filters));
    }

    return item;
  }

  /**
   * Add model variables to a collection
   * @param {Object} collection Collection object
   * @param {Array} modelVars Model variables to add
   * @private
   */
  _addModelVariables(collection, modelVars) {
    if (!collection.variable) {
      collection.variable = [];
    }

    // Add model variables
    if (modelVars && modelVars.length > 0) {
      // Filter out duplicates
      const existingKeys = collection.variable.map(v => v.key);
      const uniqueVariables = modelVars.filter(v => !existingKeys.includes(v.key));

      collection.variable.push(...uniqueVariables);
    }
  }

  /**
   * Add standard filter variables to a collection
   * @param {Object} collection Collection object
   * @private
   */
  _addFilterVariables(collection) {
    if (!collection.variable) {
      collection.variable = [];
    }

    // Add standard filter variables
    const filterVariables = [
      { key: "per_page", value: "15", type: "string" },
      { key: "page", value: "1", type: "string" },
      { key: "orderBy", value: "created_at", type: "string" },
      { key: "order", value: "desc", type: "string" },
      { key: "search", value: "", type: "string" },
      { key: "searchBy", value: "name", type: "string" },
      { key: "start_date", value: "", type: "string" },
      { key: "end_date", value: "", type: "string" }
    ];

    // Add filter variables that don't already exist
    const existingKeys = collection.variable.map(v => v.key);
    const uniqueVariables = filterVariables.filter(v => !existingKeys.includes(v.key));

    collection.variable.push(...uniqueVariables);
  }
}
