/**
 * PostmanCollectionManager class to maintain backward compatibility with the original exports
 */

import { CollectionBuilder } from './CollectionBuilder.js';
import { CollectionValidator } from './CollectionValidator.js';
import { ConfigLoader } from './ConfigLoader.js';
import { CollectionUtils } from './CollectionUtils.js';

export class PostmanCollectionManager {
  /**
   * Combine collections from the specified project path
   * @param {string} projectPath Path to the project directory
   * @returns {Promise<Object>} The combined collection
   */
  static async combineCollections(projectPath) {
    try {
      // Load all configuration
      const configLoader = new ConfigLoader(projectPath);
      const config = await configLoader.loadConfig();

      console.log(`🔄 ${config.projectConfig.name}`);

      // Build the collection
      const builder = new CollectionBuilder(config);
      const collection = await builder.buildCollection();

      // Validate the collection
      if (!config.projectConfig.ignore_validation) {
        const validator = new CollectionValidator(collection);
        const validationResults = validator.validateCollection();

        if (validationResults.hasIssues) {
          console.error('❌ Validation issues:');
          validationResults.issues.forEach(issue => {
            console.error(`- ${issue.path}: ${issue.issue}`);
          });
          process.exit(1);
        }
      } else if (config.projectConfig.ignore_validation) {
        // Check but just warn about issues
        const validator = new CollectionValidator(collection);
        const validationResults = validator.validateCollection();
        if (validationResults.hasIssues) {
          console.warn('⚠️ Validation issues found (ignored)');
        }
      }

      // Write the combined collection to the output file
      const fs = await import('fs');
      fs.writeFileSync(config.outputPath, JSON.stringify(collection, null, 2));
      console.log(`✅ Output: ${config.outputPath}`);

      return collection;
    } catch (error) {
      console.error(`❌ Error: ${error.message}`);
      throw error;
    }
  }

  /**
   * Sanitize a Postman collection by removing null/undefined items
   * @param {Object} collection The collection to sanitize
   * @returns {Object} The sanitized collection
   */
  static sanitizeCollection(collection) {
    const utils = new CollectionUtils();
    utils.sanitizeCollection(collection);
    return collection;
  }
}
