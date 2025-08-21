/**
 * ConfigLoader class for handling configuration loading
 * Loads project configurations, headers, filters, and model variables
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export class ConfigLoader {
  constructor(projectPath) {
    this.projectPath = projectPath;
    this.modulePath = path.dirname(path.dirname(__filename)); // Go up two levels from src/ConfigLoader.js

    // Common module file paths
    this.commonHeadersPath = path.join(this.modulePath, 'common-headers.json');
    this.commonModelsPath = path.join(this.modulePath, 'common-models.json');
    this.commonFiltersPath = path.join(this.modulePath, 'common-filters.json');

    // Project-specific file paths
    this.collectionsDir = path.join(this.projectPath, 'collections');
    this.masterCollectionPath = path.join(this.projectPath, 'master-collection.json');
    this.projectConfigPath = path.join(this.projectPath, 'project-config.json');
    this.projectModelsPath = path.join(this.projectPath, 'project-models.json');
    this.projectHeadersPath = path.join(this.projectPath, 'project-headers.json');
    this.projectFiltersPath = path.join(this.projectPath, 'project-filters.json');

    // Default project config
    this.defaultProjectConfig = {
      name: "API Collection",
      output_file: "API-Collection.postman_collection.json",
      description: "API Documentation",
      base_url: "{{base_url}}",
      token_var: "token"
    };
  }

  /**
   * Load all configuration files
   * @returns {Object} Configuration object with all settings
   */
  async loadConfig() {
    const projectConfig = this._loadProjectConfig();
    const headers = this._loadHeaders();
    const filters = this._loadFilters();
    const modelVariables = this._loadModelVariables();
    const outputPath = path.join(this.projectPath, projectConfig.output_file || 'API-Collection.postman_collection.json');

    return {
      projectConfig,
      headers,
      filters,
      modelVariables,
      collectionsDir: this.collectionsDir,
      masterCollectionPath: this.masterCollectionPath,
      outputPath
    };
  }

  /**
   * Load project configuration
   * @returns {Object} Project configuration
   * @private
   */
  _loadProjectConfig() {
    if (fs.existsSync(this.projectConfigPath)) {
      try {
        return JSON.parse(fs.readFileSync(this.projectConfigPath, 'utf8'));
      } catch (error) {
        // Silent error, use default config
      }
    }
    return this.defaultProjectConfig;
  }

  /**
   * Load headers from common and project-specific files
   * @returns {Object} Combined headers
   * @private
   */
  _loadHeaders() {
    const headers = { headers: [] };

    // Load common headers (from module)
    if (fs.existsSync(this.commonHeadersPath)) {
      try {
        const commonHeaders = JSON.parse(fs.readFileSync(this.commonHeadersPath, 'utf8'));
        headers.headers.push(...commonHeaders.headers);
      } catch (error) {
        // Silent error, continue with empty headers
      }
    }

    // Load project headers (from project directory)
    if (fs.existsSync(this.projectHeadersPath)) {
      try {
        const projectHeaders = JSON.parse(fs.readFileSync(this.projectHeadersPath, 'utf8'));
        headers.headers.push(...projectHeaders.headers);
      } catch (error) {
        // Silent error, continue with existing headers
      }
    }

    return headers;
  }

  /**
   * Load filters from common and project-specific files
   * @returns {Object} Combined filters
   * @private
   */
  _loadFilters() {
    let filters = {};

    // Load common filters (from module)
    if (fs.existsSync(this.commonFiltersPath)) {
      try {
        const commonFilters = JSON.parse(fs.readFileSync(this.commonFiltersPath, 'utf8'));
        filters = { ...filters, ...commonFilters };
      } catch (error) {
        // Silent error, continue with empty filters
      }
    }

    // Load project-specific filters (from project directory)
    if (fs.existsSync(this.projectFiltersPath)) {
      try {
        const projectFilters = JSON.parse(fs.readFileSync(this.projectFiltersPath, 'utf8'));
        // Merge project filters with common filters
        if (projectFilters.project_filters) {
          filters.project_filters = projectFilters.project_filters;
        }
      } catch (error) {
        // Silent error, continue with existing filters
      }
    }

    return filters;
  }

  /**
   * Load model variables from common and project-specific files
   * @returns {Array} Combined model variables
   * @private
   */
  _loadModelVariables() {
    let modelVariables = [];

    // Load common models (from module)
    if (fs.existsSync(this.commonModelsPath)) {
      try {
        const commonModels = JSON.parse(fs.readFileSync(this.commonModelsPath, 'utf8'));
        if (commonModels.variables) {
          modelVariables.push(...commonModels.variables);
        }
      } catch (error) {
        // Silent error, continue with empty model variables
      }
    }

    // Load project models (from project directory)
    if (fs.existsSync(this.projectModelsPath)) {
      try {
        const projectModels = JSON.parse(fs.readFileSync(this.projectModelsPath, 'utf8'));
        if (projectModels.variables) {
          // Check for duplicates
          const existingKeys = modelVariables.map(v => v.key);
          const uniqueProjectVars = projectModels.variables.filter(v => !existingKeys.includes(v.key));

          modelVariables.push(...uniqueProjectVars);
        }
      } catch (error) {
        // Silent error, continue with existing model variables
      }
    }

    return modelVariables;
  }
}
